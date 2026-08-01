<?php

namespace App\Console\Commands;

use App\Models\ExamPaper;
use App\Models\Resource;
use App\Models\Subject;
use App\Support\DocumentClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class IngestDocuments extends Command
{
    protected $signature = 'meridien:ingest
                            {--fresh : Vide la table des ressources avant de réindexer}
                            {--no-text : Saute l\'extraction du texte (indexation rapide)}';

    protected $description = 'Indexe les polycopiés de public/pdfs et en extrait le texte';

    /** Résultat mis en cache de la détection de Poppler. */
    private ?bool $poppler = null;

    public function handle(DocumentClassifier $classifier): int
    {
        $root = public_path(config('meridien.pdf_root'));

        if (! is_dir($root)) {
            $this->error("Dossier introuvable : {$root}");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            Resource::query()->delete();
            $this->warn('Table des ressources vidée.');
        }

        $subjects = Subject::pluck('id', 'code');
        $files = iterator_to_array((new Finder)->files()->in($root)->sortByName());

        $this->info(count($files).' fichiers trouvés.');
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $stats = ['indexes' => 0, 'texte' => 0, 'scans' => 0];

        foreach ($files as $file) {
            /** @var SplFileInfo $file */
            $relative = config('meridien.pdf_root').'/'.str_replace('\\', '/', $file->getRelativePathname());
            $extension = strtolower($file->getExtension());

            $code = $classifier->subjectCode($file->getRelativePathname());
            $meta = $classifier->classify($file->getRelativePathname());

            $attributes = [
                'subject_id' => $code ? ($subjects[$code] ?? null) : null,
                'kind' => $meta['kind'],
                'is_solution' => $meta['is_solution'],
                'title' => $classifier->title($file->getRelativePathname()),
                'filename' => $file->getFilename(),
                'extension' => $extension,
                'size_bytes' => $file->getSize(),
                'year' => $classifier->year($file->getRelativePathname()),
            ];

            if ($extension === 'pdf') {
                $attributes['page_count'] = $this->pageCount($file->getRealPath());

                if (! $this->option('no-text')) {
                    $text = $this->extractText($file->getRealPath());
                    $isScan = mb_strlen(trim($text)) < config('meridien.scan_threshold');

                    $attributes['text_content'] = $isScan ? null : $text;
                    $attributes['has_text'] = ! $isScan;
                    $attributes['is_scan'] = $isScan;

                    $isScan ? $stats['scans']++ : $stats['texte']++;
                }
            } elseif (in_array($extension, ['txt', 'csv', 'tex', 'mlw'], true)) {
                $text = @file_get_contents($file->getRealPath()) ?: '';
                $attributes['text_content'] = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1');
                $attributes['has_text'] = filled(trim($text));
                $stats['texte']++;
            }

            Resource::updateOrCreate(['relative_path' => $relative], $attributes);
            $stats['indexes']++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->linkExamPapers();

        $this->table(
            ['Indexés', 'Avec texte', 'Scans (image)'],
            [[$stats['indexes'], $stats['texte'], $stats['scans']]]
        );

        $this->newLine();
        $this->table(
            ['Matière', 'Documents', 'Cours', 'TD/Exos', 'Devoirs', 'Annales', 'Corrigés'],
            Subject::orderBy('position')->get()->map(fn (Subject $s) => [
                $s->code,
                $s->resources()->count(),
                $s->resources()->where('kind', 'cours')->count(),
                $s->resources()->whereIn('kind', ['td', 'exercice'])->count(),
                $s->resources()->where('kind', 'devoir')->count(),
                $s->resources()->where('kind', 'annale')->count(),
                $s->resources()->where('is_solution', true)->count(),
            ])->all()
        );

        return self::SUCCESS;
    }

    /** Rattache chaque copie scannée à son enregistrement de diagnostic. */
    private function linkExamPapers(): void
    {
        foreach (ExamPaper::with('subject')->get() as $paper) {
            $copy = Resource::where('kind', 'copie')
                ->where('subject_id', $paper->subject_id)
                ->first();

            if ($copy && $paper->resource_id !== $copy->id) {
                $paper->update(['resource_id' => $copy->id]);
            }
        }
    }

    /**
     * Poppler est-il disponible ?
     *
     * Sur un hébergement mutualisé, pdftotext et pdfinfo sont rarement installés.
     * L'indexation doit rester possible sans eux : on perd l'extraction du texte,
     * pas la bibliothèque.
     */
    private function popplerDisponible(): bool
    {
        if ($this->poppler !== null) {
            return $this->poppler;
        }

        try {
            Process::timeout(10)->run([config('meridien.pdftotext'), '-v']);
            $this->poppler = true;
        } catch (\Throwable) {
            $this->poppler = false;
            $this->warn('pdftotext introuvable : les documents seront indexés sans extraction de texte.');
            $this->line('  La recherche plein texte restera vide tant que le binaire ne sera pas disponible.');
        }

        return $this->poppler;
    }

    private function pageCount(string $path): ?int
    {
        if (! $this->popplerDisponible()) {
            return null;
        }

        try {
            $result = Process::timeout(30)->run([config('meridien.pdfinfo'), $path]);
        } catch (\Throwable) {
            return null;
        }

        if (! $result->successful()) {
            return null;
        }

        return preg_match('/^Pages:\s+(\d+)/m', $result->output(), $m) ? (int) $m[1] : null;
    }

    private function extractText(string $path): string
    {
        if (! $this->popplerDisponible()) {
            return '';
        }

        try {
            // -enc UTF-8 est indispensable : sans lui les accents ressortent en Latin-1.
            $result = Process::timeout(120)->run([
                config('meridien.pdftotext'), '-enc', 'UTF-8', '-layout', '-nopgbrk', $path, '-',
            ]);
        } catch (\Throwable) {
            return '';
        }

        return $result->successful() ? $result->output() : '';
    }
}