<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Resource;
use App\Models\Subject;
use App\Models\User;
use App\Services\PlanningEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Parcours complet de l'application avec un jeu de données minimal :
 * chaque écran doit répondre, y compris quand le contenu pédagogique
 * n'a pas encore été généré.
 */
class ParcoursTest extends TestCase
{
    use RefreshDatabase;

    private Subject $subject;

    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SubjectSeeder::class);
        $this->actingAs(User::factory()->create());

        $this->subject = Subject::where('code', 'SPP')->firstOrFail();

        $this->chapter = Chapter::create([
            'subject_id' => $this->subject->id,
            'code' => 'cProp',
            'title' => 'Logique propositionnelle',
            'slug' => 'logique-propositionnelle',
            'summary' => 'Connecteurs, tables de vérité, formalisation.',
            'exam_weight' => 5,
            'position' => 1,
        ]);
    }

    public function test_les_ecrans_principaux_repondent(): void
    {
        // Du contenu doit exister : plusieurs écrans agrègent les cartes par matière,
        // et une base vide masquait un agrégat SQL mal aliasé.
        Flashcard::create([
            'chapter_id' => $this->chapter->id,
            'front' => 'Formaliser « A seulement si B ».',
            'back' => 'A ⇒ B',
        ]);

        Lesson::create([
            'chapter_id' => $this->chapter->id,
            'title' => 'Formaliser une phrase française',
            'slug' => 'formaliser-phrase',
            'intuition' => 'La difficulté est le français, pas la logique.',
            'position' => 1,
        ]);

        Exercise::create([
            'subject_id' => $this->subject->id,
            'chapter_id' => $this->chapter->id,
            'title' => 'Les six tournures',
            'statement' => 'Formaliser cinq énoncés.',
        ]);

        foreach ([
            route('tableau-de-bord'),
            route('diagnostic.index'),
            route('diagnostic.show', $this->subject),
            route('drill.index'),
            route('exercices.index'),
            route('examens.index'),
            route('planning.index'),
            route('planning.creneaux'),
            route('bibliotheque.index'),
            route('matieres.show', $this->subject),
            route('chapitres.show', [$this->subject, $this->chapter]),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_une_fiche_de_cours_se_lit_et_se_valide(): void
    {
        $lesson = Lesson::create([
            'chapter_id' => $this->chapter->id,
            'title' => 'Condition nécessaire et condition suffisante',
            'slug' => 'necessaire-suffisante',
            'intuition' => "« Il faut que » n'est pas « il suffit que ».",
            'formalism' => 'A est nécessaire à B signifie B ⇒ A.',
            'examiner_expects' => 'Une seule formalisation, pas deux.',
            'est_minutes' => 12,
            'position' => 1,
        ]);

        $this->get(route('cours.show', $lesson))
            ->assertOk()
            ->assertSee('Condition nécessaire');

        $this->post(route('cours.lu', $lesson))->assertRedirect();

        $this->assertDatabaseHas('study_events', [
            'kind' => 'lesson_read',
            'chapter_id' => $this->chapter->id,
        ]);
    }

    public function test_le_drill_note_une_carte_et_la_reprogramme(): void
    {
        $carte = Flashcard::create([
            'chapter_id' => $this->chapter->id,
            'kind' => 'piege',
            'front' => 'Formaliser : « le travail est une condition nécessaire aux bonnes notes ».',
            'back' => 'N ⇒ T',
        ]);

        $this->get(route('drill.session'))->assertOk()->assertSee('Retourner');

        $this->post(route('drill.noter', $carte), ['note' => 4])->assertRedirect();

        $etat = $carte->fresh()->state;
        $this->assertNotNull($etat);
        $this->assertSame(1, $etat->repetitions);
        $this->assertTrue($etat->due_on->isFuture());
    }

    public function test_une_carte_ratee_revient_des_le_lendemain(): void
    {
        $carte = Flashcard::create([
            'chapter_id' => $this->chapter->id,
            'front' => 'Règle de la séquence en logique de Hoare ?',
            'back' => '{P} S1 {Q}, {Q} S2 {R} ⊢ {P} S1;S2 {R}',
        ]);

        $this->post(route('drill.noter', $carte), ['note' => 1]);

        $etat = $carte->fresh()->state;
        $this->assertSame(0, $etat->repetitions);
        $this->assertSame(1, $etat->lapses);
        $this->assertSame(today()->addDay()->toDateString(), $etat->due_on->toDateString());
    }

    public function test_un_exercice_penalise_la_solution_devoilee(): void
    {
        $exo = Exercise::create([
            'subject_id' => $this->subject->id,
            'chapter_id' => $this->chapter->id,
            'title' => 'Formalisation de cinq énoncés',
            'statement' => 'Traduire en logique propositionnelle.',
            'solution' => 'T ⇒ N, etc.',
            'rubric' => [
                ['label' => 'Une seule formalisation proposée', 'points' => 2],
                ['label' => 'Sens de l’implication justifié', 'points' => 2],
            ],
        ]);

        $this->get(route('exercices.show', $exo))->assertOk();

        // Tout coché mais solution ouverte : le crédit est plafonné à 40 %.
        $this->post(route('exercices.soumettre', $exo), [
            'reveal_level' => 3,
            'rubric' => [0 => '1', 1 => '1'],
            'answer' => 'N ⇒ T',
        ])->assertRedirect();

        $this->assertSame(40, $exo->attempts()->first()->self_score);
    }

    public function test_un_examen_blanc_se_compose_et_se_corrige(): void
    {
        $examen = MockExam::create([
            'subject_id' => $this->subject->id,
            'title' => 'SPP — session 1 (2026)',
            'slug' => 'spp-session-1-2026',
            'duration_min' => 180,
            'total_points' => 20,
        ]);

        $question = MockExamQuestion::create([
            'mock_exam_id' => $examen->id,
            'chapter_id' => $this->chapter->id,
            'number' => 'Ex 1.3',
            'statement' => 'Le travail est une condition nécessaire à l’obtention de bonnes notes.',
            'points' => 4,
            'rubric' => [
                ['label' => 'Écrit N ⇒ T (et non T ⇒ N)', 'points' => 3],
                ['label' => 'Une seule réponse proposée', 'points' => 1],
            ],
        ]);

        $this->get(route('examens.show', $examen))->assertOk();

        $this->post(route('examens.demarrer', $examen), ['mode' => 'distance_nuit'])->assertRedirect();

        $session = $examen->sessions()->firstOrFail();
        $this->assertSame(180 * 60, $session->allowed_sec);

        $this->get(route('examens.composer', $session))->assertOk();

        $this->post(route('examens.rendre', $session), [
            'reponses' => [$question->id => 'N ⇒ T'],
        ])->assertRedirect(route('examens.correction', $session));

        $this->get(route('examens.correction', $session))->assertOk();

        $this->post(route('examens.noter', $session), [
            'grille' => [$question->id => [0 => '1']],
        ])->assertRedirect();

        $this->assertEquals(3.0, (float) $session->fresh()->score);
    }

    public function test_le_planning_respecte_les_dates_d_epreuve(): void
    {
        $engine = app(PlanningEngine::class);

        $engine->generateSlots(today(), \Illuminate\Support\Carbon::parse('2026-08-28'));
        $res = $engine->rebuild();

        $this->assertGreaterThan(0, $res['blocs']);

        // Aucune matière ne doit être planifiée après son épreuve.
        foreach (Subject::whereNotNull('exam_at')->get() as $matiere) {
            $apres = $matiere->studyBlocks()
                ->whereDate('day', '>', $matiere->exam_at->toDateString())
                ->count();

            $this->assertSame(0, $apres, "{$matiere->code} planifié après son épreuve.");
        }

        // Les créneaux d'épreuve sont verrouillés et vides.
        $verrouilles = \App\Models\AvailabilitySlot::where('is_locked', true)->get();
        $this->assertGreaterThanOrEqual(5, $verrouilles->count());

        foreach ($verrouilles as $slot) {
            $this->assertSame(0, $slot->blocks()->count());
        }
    }

    public function test_la_bibliotheque_cherche_dans_le_texte(): void
    {
        $doc = Resource::create([
            'subject_id' => $this->subject->id,
            'kind' => 'cours',
            'title' => 'Logique de Hoare',
            'filename' => 'cHoare.pdf',
            'relative_path' => 'pdfs/test/cHoare.pdf',
            'extension' => 'pdf',
            'has_text' => true,
            'text_content' => 'Un triplet de Hoare {P} S {Q} exprime la correction partielle.',
        ]);

        $this->get(route('bibliotheque.index', ['q' => 'triplet']))
            ->assertOk()
            ->assertSee('Logique de Hoare');

        $this->get(route('bibliotheque.show', ['resource' => $doc->id, 'q' => 'triplet']))
            ->assertOk()
            ->assertSee('correction partielle', false);
    }

    public function test_une_lacune_refermee_reste_visible_et_se_rouvre(): void
    {
        $lacune = \App\Models\Gap::create([
            'subject_id' => $this->subject->id,
            'chapter_id' => $this->chapter->id,
            'kind' => 'contenu',
            'title' => 'Condition nécessaire confondue avec condition suffisante',
            'evidence' => 'faux, choisir, pas équivalent',
            'severity' => 5,
        ]);

        // On la referme.
        $this->post(route('lacunes.statut', $lacune), ['status' => 'maitrisee'])->assertRedirect();
        $lacune->refresh();
        $this->assertSame('maitrisee', $lacune->status);
        $this->assertNotNull($lacune->resolved_at);

        // Elle doit rester atteignable depuis la matière comme depuis le diagnostic,
        // avec le bouton de réouverture — sinon elle est perdue pour l'utilisateur.
        $this->get(route('matieres.show', $this->subject))->assertOk()->assertSee('Rouvrir');
        $this->get(route('diagnostic.show', $this->subject))->assertOk()->assertSee('Rouvrir');

        // Et la réouverture doit effacer la date de résolution.
        $this->post(route('lacunes.statut', $lacune), ['status' => 'ouverte'])->assertRedirect();
        $lacune->refresh();
        $this->assertSame('ouverte', $lacune->status);
        $this->assertNull($lacune->resolved_at);
    }

    public function test_le_cours_suivi_se_parcourt_dans_l_ordre(): void
    {
        foreach ([1, 2, 3] as $i) {
            \App\Models\Seance::create([
                'subject_id' => $this->subject->id,
                'chapter_id' => $this->chapter->id,
                'position' => $i,
                'title' => "Séance numéro {$i}",
                'slug' => "seance-numero-{$i}",
                'intro' => "Aujourd'hui on va voir la notion {$i}.",
                'body' => "Le corps du cours de la séance {$i}.",
                'recap' => "Ce qu'il faut retenir de la séance {$i}.",
                'duree_min' => 20,
            ]);
        }

        $premiere = \App\Models\Seance::where('position', 1)->firstOrFail();
        $deuxieme = \App\Models\Seance::where('position', 2)->firstOrFail();

        // Le sommaire propose de commencer par la première.
        $this->get(route('cours-suivi.index', $this->subject))
            ->assertOk()
            ->assertSee('Séance numéro 1')
            ->assertSee('Séance numéro 3')
            ->assertSee('Commencer');

        $this->get(route('cours-suivi.show', [$this->subject, $premiere]))
            ->assertOk()
            ->assertSee('Le corps du cours de la séance 1');

        // Terminer une séance enchaîne sur la suivante.
        $this->post(route('cours-suivi.terminer', [$this->subject, $premiere]))
            ->assertRedirect(route('cours-suivi.show', [$this->subject, $deuxieme]));

        $this->assertDatabaseHas('study_events', ['kind' => 'seance_suivie']);
        $this->assertSame([$premiere->id], \App\Models\Seance::suiviesPour($this->subject->id));

        // Et l'on peut la rouvrir pour la revoir.
        $this->post(route('cours-suivi.reprendre', [$this->subject, $premiere]))
            ->assertRedirect(route('cours-suivi.show', [$this->subject, $premiere]));

        $this->assertSame([], \App\Models\Seance::suiviesPour($this->subject->id));
    }

    public function test_la_connexion_est_requise(): void
    {
        auth()->logout();

        $this->get(route('tableau-de-bord'))->assertRedirect(route('connexion'));
        $this->get(route('connexion'))->assertOk()->assertSee('MÉRIDIEN');
    }
}
