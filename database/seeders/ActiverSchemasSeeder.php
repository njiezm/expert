<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\MockExamQuestion;
use Illuminate\Database\Seeder;

/**
 * Active l'éditeur de diagrammes sur les exercices et les questions d'examen
 * qui exigent un schéma.
 *
 * Le critère est celui du barème d'ALO : toute question de conception attend un
 * diagramme de classes annoté. C'est précisément ce qui manquait en janvier.
 */
class ActiverSchemasSeeder extends Seeder
{
    public function run(): void
    {
        // Exercices : ceux d'ALO dont l'intitulé porte sur la conception,
        // plus l'exercice de reprise des trois copies de janvier.
        Exercise::whereHas('subject', fn ($q) => $q->where('code', 'ALO'))
            ->where(function ($q) {
                $q->where('title', 'ilike', '%conception%')
                    ->orWhere('title', 'ilike', '%diagramme%')
                    ->orWhere('title', 'ilike', '%redessiner%');
            })
            ->update(['needs_diagram' => true]);

        // Questions d'examen blanc : toutes les conceptions d'ALO.
        MockExamQuestion::whereHas('mockExam.subject', fn ($q) => $q->where('code', 'ALO'))
            ->where('number', 'ilike', '%conception%')
            ->update(['needs_diagram' => true]);
    }
}