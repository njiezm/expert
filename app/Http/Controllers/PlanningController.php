<?php

namespace App\Http\Controllers;

use App\Models\AvailabilitySlot;
use App\Models\StudyBlock;
use App\Models\Subject;
use App\Services\PlanningEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function __construct(private readonly PlanningEngine $engine) {}

    public function index(): View
    {
        $fin = Subject::whereNotNull('exam_at')->max('exam_at');
        $fin = $fin ? Carbon::parse($fin)->endOfDay() : now()->addDays(20);

        $slots = AvailabilitySlot::with(['blocks.subject', 'blocks.chapter'])
            ->whereBetween('day', [today(), $fin])
            ->orderBy('day')->orderBy('starts_at')
            ->get()
            ->groupBy(fn (AvailabilitySlot $s) => $s->day->toDateString());

        // Répartition du temps planifié par matière : le contrôle d'équilibre.
        $repartition = StudyBlock::selectRaw('subject_id, sum(planned_minutes) as minutes')
            ->whereDate('day', '>=', today())
            ->groupBy('subject_id')
            ->pluck('minutes', 'subject_id');

        return view('planning.index', [
            'jours' => $slots,
            'fin' => $fin,
            'subjects' => Subject::orderBy('position')->get(),
            'repartition' => $repartition,
            'totalMinutes' => $repartition->sum(),
            'examens' => Subject::whereNotNull('exam_at')->examOrder()->get(),
        ]);
    }

    public function rebuild(): RedirectResponse
    {
        $fin = Subject::whereNotNull('exam_at')->max('exam_at');

        $this->engine->generateSlots(
            today(),
            $fin ? Carbon::parse($fin) : today()->addDays(20)
        );

        $res = $this->engine->rebuild();

        return redirect()->route('planning.index')->with(
            'succes',
            "Planning recalculé : {$res['blocs']} blocs répartis sur {$res['creneaux']} créneaux."
        );
    }

    /** Écran de saisie du type de chaque journée (entreprise, télétravail, congé…). */
    public function slots(): View
    {
        $fin = Subject::whereNotNull('exam_at')->max('exam_at');
        $fin = $fin ? Carbon::parse($fin)->endOfDay() : now()->addDays(20);

        $existants = AvailabilitySlot::whereBetween('day', [today(), $fin])
            ->where('label', '!=', 'examen')
            ->get()
            ->groupBy(fn (AvailabilitySlot $s) => $s->day->toDateString());

        $jours = [];
        for ($d = today()->copy(); $d->lte($fin); $d->addDay()) {
            $cle = $d->toDateString();
            $duJour = $existants->get($cle);

            $jours[$cle] = [
                'date' => $d->copy(),
                'type' => $duJour?->first()?->label
                    ?? ($d->isWeekend() ? 'weekend' : 'soiree'),
                'minutes' => $duJour?->sum('minutes') ?? 0,
            ];
        }

        return view('planning.creneaux', [
            'jours' => $jours,
            'types' => AvailabilitySlot::LABELS,
            'examens' => Subject::whereNotNull('exam_at')->get()
                ->groupBy(fn (Subject $s) => $s->exam_at->toDateString()),
        ]);
    }

    public function saveSlots(Request $request): RedirectResponse
    {
        $types = $request->validate([
            'type' => ['required', 'array'],
            'type.*' => ['required', 'in:soiree,weekend,teletravail,conge'],
        ])['type'];

        // On repart des créneaux à blanc pour la période, hors épreuves verrouillées.
        AvailabilitySlot::whereDate('day', '>=', today())
            ->where('is_locked', false)
            ->delete();

        $fin = Carbon::parse(array_key_last($types));
        $this->engine->generateSlots(today(), $fin, $types);
        $res = $this->engine->rebuild();

        return redirect()->route('planning.index')->with(
            'succes',
            "Disponibilités enregistrées, {$res['blocs']} blocs replanifiés."
        );
    }

    public function updateBlock(Request $request, StudyBlock $block): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:planifie,en_cours,fait,reporte'],
            'done_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $block->update([
            'status' => $data['status'],
            'done_minutes' => $data['status'] === 'fait'
                ? ($data['done_minutes'] ?? $block->planned_minutes)
                : ($data['done_minutes'] ?? $block->done_minutes),
        ]);

        return back();
    }
}