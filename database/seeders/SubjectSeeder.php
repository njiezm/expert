<?php

namespace Database\Seeders;

use App\Models\ExamPaper;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Les cinq matières du rattrapage, plus « Rigueur », la matière transversale
 * déduite des annotations du correcteur sur les copies de la session initiale.
 *
 * Les horaires sont ceux communiqués pour les épreuves d'août 2026.
 */
class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'ALO',
                'name' => 'Architectures Logicielles à Objet',
                'slug' => 'architectures-logicielles-objet',
                'tagline' => 'Conception objet, patrons de conception, UML, Java.',
                'color' => '#E8B04B',
                'exam_at' => '2026-08-24 20:00:00',
                'exam_duration_min' => 180,
                'exam_mode' => 'distance_nuit',
                'initial_grade' => 0,
                'initial_session' => 'Janvier 2026',
                'initial_centre' => 'Besançon',
                'priority' => 1,
                'position' => 1,
                'paper' => [
                    'sat_on' => '2026-01-20',
                    'pages' => 8,
                    'appreciation' => null,
                    'copy_file' => 'COPIE_ALO_ZAMON.pdf',
                ],
            ],
            [
                'code' => 'EP',
                'name' => 'Évaluation de Programmes',
                'slug' => 'evaluation-de-programmes',
                'tagline' => 'Calculabilité, machines de Turing, complexité, réductions.',
                'color' => '#4A7FC1',
                'exam_at' => '2026-08-25 14:00:00',
                'exam_duration_min' => 120,
                'exam_mode' => 'amphi',
                'initial_grade' => 7,
                'initial_session' => 'Janvier 2026',
                'initial_centre' => 'Besançon',
                'priority' => 5,
                'position' => 2,
                'paper' => [
                    'sat_on' => '2026-01-20',
                    'pages' => 4,
                    'appreciation' => null,
                    'copy_file' => 'COPIE_EP_ZAMON.pdf',
                ],
            ],
            [
                'code' => 'AGC',
                'name' => 'Algorithmes sur les Graphes et Combinatoire',
                'slug' => 'algorithmes-graphes-combinatoire',
                'tagline' => 'Graphes, programmation dynamique, programmation linéaire.',
                'color' => '#3F9E8C',
                'exam_at' => '2026-08-26 15:00:00',
                'exam_duration_min' => 180,
                'exam_mode' => 'amphi',
                'initial_grade' => 7,
                'initial_session' => 'Janvier 2026',
                'initial_centre' => 'Besançon',
                'priority' => 4,
                'position' => 3,
                'paper' => [
                    'sat_on' => '2026-01-22',
                    'pages' => 6,
                    'appreciation' => null,
                    'score_breakdown' => ['Exercice 1' => 2, 'Exercice 2' => 2, 'Exercice 3' => 3],
                    'copy_file' => 'COPIE_AGC_ZAMON_a.pdf',
                ],
            ],
            [
                'code' => 'SPP',
                'name' => 'Spécification et Preuves de Programmes',
                'slug' => 'specification-preuves-programmes',
                'tagline' => 'Logique, contrats, logique de Hoare, preuves par induction, Why3.',
                'color' => '#C6533F',
                'exam_at' => '2026-08-26 20:00:00',
                'exam_duration_min' => 180,
                'exam_mode' => 'distance_nuit',
                'initial_grade' => 1.5,
                'initial_session' => 'Mai 2026',
                'initial_centre' => 'Le Lamentin (Martinique)',
                'priority' => 2,
                'position' => 4,
                'paper' => [
                    'sat_on' => '2026-05-21',
                    'pages' => 4,
                    'appreciation' => 'Quasiment aucun acquis',
                    'copy_file' => 'COPIE_SPP_ZAMON_a.pdf',
                ],
            ],
            [
                'code' => 'MIA',
                'name' => "Méthodes et Outils pour l'Intelligence Artificielle",
                'slug' => 'methodes-outils-ia',
                'tagline' => 'Prolog, recherche heuristique, A*, jeux, apprentissage.',
                'color' => '#7C6BD6',
                'exam_at' => '2026-08-28 15:00:00',
                'exam_duration_min' => 120,
                'exam_mode' => 'amphi',
                'initial_grade' => 3.34,
                'initial_session' => 'Mai 2026',
                'initial_centre' => 'Greta-CFA Martinique',
                'priority' => 3,
                'position' => 5,
                'paper' => [
                    'sat_on' => '2026-05-22',
                    'pages' => 12,
                    'appreciation' => null,
                    'score_breakdown' => [
                        'Partie I' => 1.25, 'Partie II' => 0.75, 'Partie III' => 0.25,
                        'Partie IV' => 1.25, 'Partie V' => 1.5,
                    ],
                    'copy_file' => 'COPIE_MIA_ZAMON.pdf',
                ],
            ],
            [
                'code' => 'RIG',
                'name' => 'Rigueur de rédaction',
                'slug' => 'rigueur',
                'tagline' => "La compétence transversale qui a coûté des points dans trois copies : justifier, trancher, rester dans le référentiel.",
                'color' => '#8FA3B8',
                'exam_at' => null,
                'exam_duration_min' => null,
                'exam_mode' => 'amphi',
                'initial_grade' => null,
                'initial_session' => null,
                'initial_centre' => null,
                'priority' => 1,
                'is_transversal' => true,
                'position' => 6,
                'paper' => null,
            ],
        ];

        foreach ($subjects as $data) {
            $paper = $data['paper'] ?? null;
            unset($data['paper']);

            $subject = Subject::updateOrCreate(['code' => $data['code']], $data);

            if ($paper) {
                ExamPaper::updateOrCreate(
                    ['subject_id' => $subject->id, 'session_label' => $subject->initial_session],
                    [
                        'sat_on' => $paper['sat_on'],
                        'centre' => $subject->initial_centre,
                        'grade' => $subject->initial_grade,
                        'max_grade' => 20,
                        'appreciation' => $paper['appreciation'],
                        'score_breakdown' => $paper['score_breakdown'] ?? null,
                        'pages' => $paper['pages'],
                        'analysed_pages' => 1,
                    ]
                );
            }
        }
    }
}