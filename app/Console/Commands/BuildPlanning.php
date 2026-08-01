<?php

namespace App\Console\Commands;

use App\Models\StudyBlock;
use App\Models\Subject;
use App\Services\PlanningEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BuildPlanning extends Command
{
    protected $signature = 'meridien:planning
                            {--jusqu-au= : Date de fin (par défaut, la dernière épreuve)}';

    protected $description = 'Génère les créneaux et répartit le travail jusqu\'à la dernière épreuve';

    public function handle(PlanningEngine $engine): int
    {
        $fin = $this->option('jusqu-au')
            ? Carbon::parse($this->option('jusqu-au'))
            : Carbon::parse(Subject::whereNotNull('exam_at')->max('exam_at'));

        $this->info('Génération des créneaux jusqu\'au '.$fin->translatedFormat('j F Y').'…');
        $crees = $engine->generateSlots(today(), $fin);
        $this->line("  {$crees} créneaux créés.");

        $res = $engine->rebuild();
        $this->line("  {$res['blocs']} blocs répartis sur {$res['creneaux']} créneaux exploitables.");

        $this->newLine();
        $this->table(
            ['Matière', 'Épreuve', 'J−', 'Blocs', 'Heures planifiées'],
            Subject::orderBy('position')->get()->map(function (Subject $s) {
                $min = StudyBlock::where('subject_id', $s->id)->sum('planned_minutes');

                return [
                    $s->code,
                    $s->exam_at?->translatedFormat('D j M H\h') ?? '—',
                    $s->exam_at ? max(0, $s->days_until_exam) : '—',
                    StudyBlock::where('subject_id', $s->id)->count(),
                    floor($min / 60).' h '.str_pad($min % 60, 2, '0', STR_PAD_LEFT),
                ];
            })->all()
        );

        return self::SUCCESS;
    }
}