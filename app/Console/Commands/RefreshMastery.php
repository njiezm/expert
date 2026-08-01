<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Services\MasteryCalculator;
use Illuminate\Console\Command;

class RefreshMastery extends Command
{
    protected $signature = 'meridien:mastery';

    protected $description = 'Recalcule la maîtrise de tous les chapitres';

    public function handle(MasteryCalculator $calculator): int
    {
        $n = $calculator->refreshAll();
        $this->info("{$n} chapitres recalculés.");

        $this->table(
            ['Matière', 'Chapitres', 'Fiches', 'Cartes', 'Exercices', 'Lacunes ouvertes', 'Maîtrise'],
            Subject::with('chapters.progress')->orderBy('position')->get()->map(fn (Subject $s) => [
                $s->code,
                $s->chapters->count(),
                $s->lessons()->count(),
                $s->flashcards()->count(),
                $s->exercises()->count(),
                $s->gaps()->open()->count(),
                $s->mastery.' %',
            ])->all()
        );

        return self::SUCCESS;
    }
}