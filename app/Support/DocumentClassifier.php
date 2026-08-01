<?php

namespace App\Support;

/**
 * Déduit la nature d'un document à partir de son chemin.
 *
 * Les polycopiés viennent de cinq enseignants différents, chacun avec ses
 * conventions de nommage : « Correction-AGC-devoir1 », « alo_devoir_2_corrige_V3 »,
 * « td2-new-correction », « examen23-24-1-c ». D'où un jeu de règles ordonnées
 * plutôt qu'une correspondance unique.
 */
class DocumentClassifier
{
    /** Dossier de la matière => code. */
    private const SUBJECT_FOLDERS = [
        'Algorithmes sur les Graphes et Combinatoire' => 'AGC',
        'Architectures logicielles à objet' => 'ALO',
        'Evaluation de Programmes' => 'EP',
        "Méthodes et Outils pour l'Intelligence Artificielle (MIA)" => 'MIA',
        'Spécification et preuves de programmes' => 'SPP',
    ];

    /** Repérage des copies personnelles scannées, par code matière. */
    private const COPY_PREFIX = 'COPIE_';

    public function subjectCode(string $relativePath): ?string
    {
        foreach (self::SUBJECT_FOLDERS as $folder => $code) {
            if (str_contains($relativePath, $folder)) {
                return $code;
            }
        }

        // Les copies d'examen vivent dans « resultats s1 et s2 » : le code est dans le nom.
        if (preg_match('/COPIE_([A-Z]+)_/', basename($relativePath), $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @return array{kind: string, is_solution: bool}
     */
    public function classify(string $relativePath): array
    {
        $name = basename($relativePath);
        $lower = mb_strtolower($name);

        // Un corrigé reste rattaché à sa nature d'origine (un TD corrigé est un TD),
        // mais on le marque pour pouvoir masquer les solutions pendant l'entraînement.
        $isSolution = (bool) preg_match(
            '/(corrig|correction|_c\.pdf$|-c\.pdf$|^cor[A-Za-z]|solution)/i',
            $name
        );

        $kind = match (true) {
            str_starts_with($name, self::COPY_PREFIX) => 'copie',

            // Les copies évaluées par l'enseignant (« evalNjieZamon ») sont aussi des retours.
            (bool) preg_match('/^eval[A-Z]/i', $name) => 'copie',

            (bool) preg_match('/(examen|^exam|session\d)/i', $name) => 'annale',
            (bool) preg_match('/(devoir|^dev\d|_dev\d)/i', $name) => 'devoir',
            (bool) preg_match('/(^td\d|_td\d|-td\d)/i', $lower) => 'td',
            (bool) preg_match('/(exos|^ex[A-Z]|exercice)/i', $name) => 'exercice',
            (bool) preg_match('/(cours|^c[A-Z]|^main|_v\d+\.pdf$|introduction|intro)/i', $name) => 'cours',
            (bool) preg_match('/(anim|presentation|pres |diapo)/i', $lower) => 'annexe',

            default => 'annexe',
        };

        return ['kind' => $kind, 'is_solution' => $isSolution];
    }

    /** Année de l'épreuve, quand elle est lisible dans le nom. */
    public function year(string $relativePath): ?int
    {
        $name = basename($relativePath);

        // « ALO_Examen_2024_05 », « examen23-24-1 », « spp25 », « 2526 »
        if (preg_match('/(20\d{2})/', $name, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/(?:spp|exam|devoir)\D*(\d{2})(\d{2})?/i', $name, $m)) {
            return 2000 + (int) $m[1];
        }

        return null;
    }

    /** Titre lisible : on retire l'extension, les underscores et le bruit de version. */
    public function title(string $relativePath): string
    {
        $name = pathinfo(basename($relativePath), PATHINFO_FILENAME);

        $name = preg_replace('/\s*\(\d+\)\s*/', ' ', $name);        // « (1) », « (2) »
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim(mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'));
    }
}