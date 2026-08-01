<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu de SPP — la matière la plus faible (1,5/20, « quasiment aucun acquis »).
 *
 * Priorité donnée à deux chapitres : la logique propositionnelle, où l'exercice 1
 * de l'épreuve a été manqué aux trois quarts, et la logique de Hoare, qui porte
 * l'essentiel du barème du module.
 */
class SppContentSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'SPP')->first();

        if (! $subject) {
            return;
        }

        foreach ($this->content() as $code => $data) {
            $chapter = Chapter::where('subject_id', $subject->id)->where('code', $code)->first();

            if (! $chapter) {
                continue;
            }

            foreach ($data['lessons'] ?? [] as $i => $lesson) {
                Lesson::updateOrCreate(
                    ['chapter_id' => $chapter->id, 'slug' => Str::slug($lesson['title'])],
                    $lesson + ['position' => $i + 1, 'est_minutes' => $lesson['est_minutes'] ?? 15]
                );
            }

            foreach ($data['cards'] ?? [] as $i => $card) {
                Flashcard::updateOrCreate(
                    ['chapter_id' => $chapter->id, 'front' => $card['front']],
                    $card + ['position' => $i + 1]
                );
            }

            foreach ($data['exercises'] ?? [] as $i => $exo) {
                Exercise::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $exo['title']],
                    $exo + ['chapter_id' => $chapter->id, 'position' => $i + 1]
                );
            }
        }

        $this->mockExam($subject);
    }

    /**
     * Examen blanc calé sur l'épreuve réellement passée en mai 2026,
     * dans la durée et le créneau du rattrapage du 26 août.
     */
    private function mockExam(Subject $subject): void
    {
        $source = Resource::where('subject_id', $subject->id)
            ->where('filename', 'ilike', 'examenSPP25session1.pdf')
            ->first();

        $examen = MockExam::updateOrCreate(
            ['slug' => 'spp-blanc-formalisation-et-hoare'],
            [
                'subject_id' => $subject->id,
                'source_resource_id' => $source?->id,
                'title' => 'SPP blanc n°1 — formalisation et logique de Hoare',
                'instructions' => <<<'MD'
Durée : **3 heures**, comme l'épreuve du 26 août de 20 h à 23 h.

Documents autorisés : aucun. Rédigez comme sur une copie : posez les hypothèses,
nommez chaque règle d'inférence utilisée, et **ne donnez qu'une seule réponse par question**.

L'exercice 1 reprend la structure exacte de celui que vous avez manqué en mai.
MD,
                'duration_min' => 180,
                'total_points' => 20,
                'origin' => 'genere',
                'year' => 2026,
            ]
        );

        $prop = Chapter::where('subject_id', $subject->id)->where('code', 'Prop')->first();
        $hoare = Chapter::where('subject_id', $subject->id)->where('code', 'Hoare')->first();

        $questions = [
            [
                'number' => 'Exercice 1',
                'chapter_id' => $prop?->id,
                'points' => 5,
                'statement' => <<<'MD'
On pose **P** = « le programme termine » et **C** = « le programme est correct ».

Formalisez chacun des énoncés suivants. **Une seule formule par énoncé.**

1. Un programme n'est correct que s'il termine.
2. La terminaison est une condition nécessaire à la correction.
3. Il suffit que le programme termine pour qu'il soit correct.
4. Le programme est incorrect, à moins qu'il ne termine.
5. Malgré sa terminaison, le programme est incorrect.
MD,
                'solution' => <<<'MD'
1. **C ⇒ P** — « ne … que si » introduit une condition nécessaire.
2. **C ⇒ P** — même énoncé reformulé : la terminaison est nécessaire, donc conclusion.
3. **P ⇒ C** — « il suffit que » : la terminaison est ici l'hypothèse.
4. **¬P ⇒ ¬C** — « à moins que » ; contraposée de C ⇒ P, donc équivalente aux énoncés 1 et 2.
5. **P ∧ ¬C** — « malgré » est une conjonction, pas une implication.

Trois énoncés sur cinq vont dans le sens C ⇒ P. Seul le 3 s'écrit dans l'autre sens,
et le 5 n'est pas une implication du tout.
MD,
                'rubric' => [
                    ['label' => '1. C ⇒ P', 'points' => 1],
                    ['label' => '2. C ⇒ P', 'points' => 1],
                    ['label' => '3. P ⇒ C', 'points' => 1],
                    ['label' => '4. ¬P ⇒ ¬C (ou contraposée identifiée)', 'points' => 1],
                    ['label' => '5. P ∧ ¬C — conjonction', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 2',
                'chapter_id' => $hoare?->id,
                'points' => 7,
                'statement' => <<<'MD'
On considère le programme de calcul de la factorielle :

```
i := 0;
f := 1;
while i < n do
    i := i + 1;
    f := f * i
od
```

1. Donnez un invariant de boucle **I** permettant de prouver le triplet
   `{n ≥ 0} … {f = n!}`. **(2 pts)**
2. Justifiez que I est bien un invariant : montrez qu'il est établi avant la boucle
   et préservé par le corps. **(3 pts)**
3. Montrez que la sortie de boucle, combinée à I, donne bien la postcondition. **(1 pt)**
4. Donnez un variant prouvant la terminaison. **(1 pt)**
MD,
                'solution' => <<<'MD'
**1. Invariant.** `I ≡ i ≤ n ∧ f = i!`

**2. Établissement et préservation.**

*Établissement* — après `i := 0; f := 1`, on a i = 0 et f = 1. Comme n ≥ 0, on a i ≤ n ;
et f = 1 = 0! = i!. Donc `n ≥ 0 ∧ i = 0 ∧ f = 1 ⇒ I`.

*Préservation* — supposons I ∧ i < n. Après `i := i + 1`, notons i' = i + 1 :
de i < n on tire i' ≤ n, et f = (i'−1)!. Après `f := f * i'`, on a f' = (i'−1)! · i' = i'!.
Donc I est rétabli. Par la règle **(sequence)** appliquée aux deux affectations,
puis la règle **(while)**, I est bien un invariant.

**3. Sortie de boucle.** À la sortie, on a `¬(i < n) ∧ I`, soit `i ≥ n ∧ i ≤ n ∧ f = i!`.
D'où i = n, et donc **f = n!**, qui est la postcondition.

**4. Variant.** `V = n − i`. Il est entier, positif ou nul tant que la garde i < n est
vraie, et strictement décroissant à chaque tour puisque i augmente de 1. La boucle
termine donc.
MD,
                'rubric' => [
                    ['label' => "L'invariant contient la borne i ≤ n", 'points' => 1],
                    ['label' => "L'invariant contient la relation f = i!", 'points' => 1],
                    ['label' => "L'établissement avant la boucle est démontré (i = 0, f = 1 = 0!)", 'points' => 1],
                    ['label' => 'La préservation par le corps est démontrée pas à pas', 'points' => 1],
                    ['label' => 'Les règles utilisées sont nommées : (assignment), (sequence), (while)', 'points' => 1],
                    ['label' => "La sortie de boucle conclut i = n puis f = n!", 'points' => 1],
                    ['label' => 'Le variant n − i est donné, avec décroissance et minoration', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 3',
                'chapter_id' => $hoare?->id,
                'points' => 4,
                'statement' => <<<'MD'
Complétez le tableau de preuve suivant. Chaque ligne comporte un triplet et sa
justification : soit un axiome, soit un théorème admis, soit le nom d'une règle
d'inférence suivi des numéros des lignes auxquelles elle s'applique.

| N° | Triplet | Justification |
|---|---|---|
| 1 | {i < n ∧ r = i!} i := i+1 {i ≤ n ∧ r = (i−1)!} | (assignment) |
| 2 | {i ≤ n ∧ r = (i−1)!} r := r∗i {i ≤ n ∧ r = i!} | (assignment) |
| 3 | … | … |
| 4 | … | … |
| 5 | … | … |
MD,
                'solution' => <<<'MD'
| N° | Triplet | Justification |
|---|---|---|
| 3 | {i < n ∧ i ≤ n ∧ r = i!} i := i+1; r := r∗i {i ≤ n ∧ r = i!} | **(sequence) 1 2** |
| 4 | n ≥ 0 ∧ i = 0 ∧ r = 1 ⇒ I | **OK** (théorème arithmétique admis) |
| 5 | {I} while i < n do i := i+1; r := r∗i od {¬(i < n) ∧ I} | **(while) 3** |

avec I ≡ i ≤ n ∧ r = i!.

La ligne 3 compose les deux affectations par la règle de séquence. La ligne 4 établit
l'invariant à l'entrée. La ligne 5 applique la règle du while au corps prouvé en 3.
MD,
                'rubric' => [
                    ['label' => 'Ligne 3 : composition des deux affectations', 'points' => 1],
                    ['label' => 'Ligne 3 justifiée par (sequence) avec les numéros 1 et 2', 'points' => 1],
                    ['label' => "Ligne 4 : l'implication établissant I à l'entrée", 'points' => 1],
                    ['label' => 'Ligne 5 : la règle (while) appliquée à la ligne 3', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 4',
                'chapter_id' => $hoare?->id,
                'points' => 4,
                'statement' => <<<'MD'
1. Énoncez la règle d'affectation de la logique de Hoare, et expliquez pourquoi
   la substitution s'opère dans la **postcondition** et non dans la précondition. **(2 pts)**
2. Quelle est la différence entre **correction partielle** et **correction totale** ?
   Que faut-il fournir en plus pour établir la seconde ? **(2 pts)**
MD,
                'solution' => <<<'MD'
**1. Règle d'affectation.**

```
———————————————————
{ Q[x ← E] }  x := E  { Q }
```

Pour que Q soit vraie *après* l'affectation, il faut et il suffit que la propriété
obtenue en remplaçant x par E dans Q soit vraie *avant*. On raisonne à rebours :
la postcondition est connue, et la règle en déduit la précondition la plus faible.

Substituer dans la précondition serait incorrect : après `x := E`, c'est bien x qui
vaut E, donc toute mention de x dans Q doit être ramenée à E dans l'état antérieur.

*Exemple.* `{x + 1 = 5} x := x + 1 {x = 5}` : on a substitué x par x+1 dans « x = 5 ».

**2. Correction partielle et totale.**

La **correction partielle** `{P} S {Q}` signifie : *si* S termine à partir d'un état
vérifiant P, *alors* l'état final vérifie Q. Elle ne dit rien du cas où S ne termine pas.

La **correction totale** ajoute la garantie de terminaison : à partir de tout état
vérifiant P, S termine, et l'état final vérifie Q.

Pour passer de l'une à l'autre, il faut fournir un **variant** : une expression à
valeurs dans un ensemble bien fondé — typiquement les entiers naturels — qui décroît
strictement à chaque tour de boucle et reste minorée. Sa décroissance ne pouvant être
infinie, la boucle termine.
MD,
                'rubric' => [
                    ['label' => 'La règle est écrite avec la substitution Q[x ← E] en précondition', 'points' => 1],
                    ['label' => 'Le raisonnement à rebours est expliqué', 'points' => 1],
                    ['label' => 'La correction partielle est définie avec sa condition « si S termine »', 'points' => 1],
                    ['label' => 'Le variant est nommé, avec décroissance stricte et bonne fondation', 'points' => 1],
                ],
            ],
        ];

        foreach ($questions as $i => $q) {
            MockExamQuestion::updateOrCreate(
                ['mock_exam_id' => $examen->id, 'number' => $q['number']],
                $q + ['position' => $i + 1]
            );
        }
    }

    private function content(): array
    {
        return [

            /* ======================= Logique propositionnelle ======================= */
            'Prop' => [
                'lessons' => [
                    [
                        'title' => 'Formaliser une phrase française',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
La difficulté n'est presque jamais la logique. C'est le français.

« Il faut travailler pour réussir » et « il suffit de travailler pour réussir »
utilisent les mêmes mots, décrivent le même monde, et se formalisent **dans des sens
opposés**. Le français signale la direction de l'implication par des tournures
qu'on lit tous les jours sans y penser — et c'est précisément pour cela qu'on se trompe
sous pression.

À l'épreuve de mai, l'exercice 1 comptait cinq traductions de ce type. Trois ont été
comptées fausses.
MD,
                        'formalism' => <<<'MD'
Une seule règle à retenir, et tout en découle :

> **Le fait qui est *garanti* se place à droite de la flèche.**

Autrement dit, dans `A ⇒ B`, A est ce qu'on suppose et B ce qu'on en déduit.

Le tableau de correspondance complet :

| Tournure française | Formalisation |
|---|---|
| si A alors B | A ⇒ B |
| A si B | B ⇒ A |
| A **seulement si** B | A ⇒ B |
| A **ne** … **que si** B | A ⇒ B |
| B est **nécessaire** à A | A ⇒ B |
| B est **suffisant** pour A | B ⇒ A |
| il **faut** B pour A | A ⇒ B |
| il **suffit** de B pour A | B ⇒ A |
| A **à moins que** B | ¬B ⇒ A |
| **malgré** B, A | B ∧ A |

Les six premières lignes se ramènent toutes à « B est nécessaire », donc à `A ⇒ B`.
Seules « suffisant » et « il suffit » inversent le sens. Et « malgré » n'est pas
une implication du tout.
MD,
                        'worked_example' => <<<'MD'
**Énoncé.** « Un étudiant n'a de bonnes notes que s'il travaille. »
Avec T = « travaille » et N = « a de bonnes notes ».

*Étape 1 — repérer la tournure.* « ne … que si » figure au tableau : c'est une
condition nécessaire.

*Étape 2 — identifier qui garantit quoi.* Le travail est nécessaire aux bonnes notes.
Donc observer de bonnes notes **garantit** qu'il y a eu travail.

*Étape 3 — placer le fait garanti à droite.* Le fait garanti est T.

**Réponse : N ⇒ T.**

*Vérification par le contre-exemple.* Si la réponse était T ⇒ N, alors tout étudiant
qui travaille aurait de bonnes notes — ce que la phrase ne dit pas. Un étudiant qui
travaille et échoue ne contredit pas l'énoncé. En revanche, un étudiant qui a de bonnes
notes sans avoir travaillé le contredirait. C'est bien N ⇒ T.

Ce test du contre-exemple prend quinze secondes et tranche à coup sûr.
MD,
                        'pitfalls' => <<<'MD'
**Le piège principal : « seulement si » lu comme « si ».**

C'est l'erreur commise à la question 1 de votre copie. « C'est seulement si un étudiant
travaille qu'il a de bonnes notes » a été formalisé `T → N`. Le « seulement » inverse
le sens : la réponse est `N ⇒ T`.

**Le second piège : confondre l'équivalence et l'implication.**

`A ⇒ B` et `¬A ⇒ ¬B` ne sont **pas** équivalentes. C'est ce que le correcteur a signalé
par « pas équivalent ». Les deux formes équivalentes sont :

- `A ⇒ B`
- `¬B ⇒ ¬A` (la **contraposée**)

`¬A ⇒ ¬B` est la *réciproque de la contraposée*, c'est-à-dire la **réciproque** — une
tout autre affirmation.

**Le troisième piège : « malgré ».**

« Malgré son travail, un étudiant a de mauvaises notes » n'est pas une implication.
C'est une conjonction : `T ∧ ¬N`. Le mot « malgré » signale une opposition rhétorique,
pas une dépendance logique.
MD,
                        'examiner_expects' => <<<'MD'
- **Une seule formule par énoncé.** Le correcteur a écrit « choisir » sur votre copie.
  Deux formules superposées font perdre les points des deux.
- **La formule, pas le raisonnement.** Une justification en une ligne est un bonus,
  jamais un substitut à la formule elle-même.
- **La notation du cours.** `⇒` pour l'implication, `∧` pour la conjonction,
  `¬` pour la négation. Pas de flèches simples ni de symboles improvisés.
- Si vous mentionnez une forme équivalente, **nommez-la** « contraposée » et
  mettez-la entre parenthèses.
MD,
                        'source_refs' => [
                            ['label' => 'exProp.pdf — énoncés'],
                            ['label' => 'cProp.pdf — corrigés, avec Why3'],
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Formaliser : « A seulement si B ».',
                        'back' => "**A ⇒ B**\n\n« Seulement si » introduit une condition **nécessaire**. Le fait garanti (B) est à droite.\n\n*Erreur commise en mai, question 1 : vous aviez écrit T → N pour « c'est seulement si il travaille qu'il a de bonnes notes ». La réponse était N ⇒ T.*",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'A ⇒ B et ¬A ⇒ ¬B sont-elles équivalentes ?',
                        'back' => "**Non.** C'est l'erreur annotée « pas équivalent » sur votre copie.\n\n- Équivalente à A ⇒ B : la **contraposée** ¬B ⇒ ¬A.\n- ¬A ⇒ ¬B est la **réciproque** : une affirmation différente.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Formaliser : « A à moins que B ».',
                        'back' => "**¬B ⇒ A**\n\nEn l'absence de B, A se produit.\n\n*« Un étudiant a de mauvaises notes, à moins qu'il ne travaille » → ¬T ⇒ ¬N.*",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Formaliser : « malgré B, A ».',
                        'back' => "**B ∧ A** — une **conjonction**, pas une implication.\n\n« Malgré » marque une opposition rhétorique, pas une dépendance logique.\n\n*« Malgré son travail, un étudiant a de mauvaises notes » → T ∧ ¬N.*",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'La règle unique pour orienter une implication ?',
                        'back' => "**Le fait garanti se place à droite de la flèche.**\n\nDans A ⇒ B : A est supposé, B est garanti.\n\nTest de vérification : cherchez le contre-exemple. Quelle situation contredirait la phrase ? Elle doit contredire votre formule.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Contraposée de A ⇒ B ?',
                        'back' => "**¬B ⇒ A** … non : **¬B ⇒ ¬A**\n\nElle est logiquement équivalente à A ⇒ B, et c'est la seule des trois formes dérivées à l'être.\n\n- Réciproque : B ⇒ A — non équivalente.\n- Inverse : ¬A ⇒ ¬B — non équivalente.",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => "Exercice 1 de l'épreuve de mai — à refaire",
                        'origin' => 'annale',
                        'est_minutes' => 20,
                        'difficulty' => 3,
                        'statement' => <<<'MD'
Voici l'exercice 1 tel qu'il est tombé à l'épreuve du 21 mai 2026.
Vous y aviez obtenu 2 réponses justes sur 5.

Avec **T** = « l'étudiant travaille » et **N** = « l'étudiant a de bonnes notes »,
formalisez :

1. C'est seulement si un étudiant travaille qu'il a de bonnes notes.
2. Un étudiant n'a de bonnes notes que s'il travaille.
3. Pour un étudiant, le travail est une condition nécessaire à l'obtention de bonnes notes.
4. Un étudiant a de mauvaises notes, à moins qu'il ne travaille.
5. Malgré son travail, un étudiant a de mauvaises notes.

**Une seule formule par énoncé.**
MD,
                        'hint' => "Quatre de ces cinq énoncés disent la même chose. Identifiez lequel fait exception avant de commencer — et rappelez-vous que « malgré » n'est pas une implication.",
                        'method' => <<<'MD'
Pour chaque énoncé :

1. Repérez la tournure dans le tableau de correspondance.
2. Demandez-vous : quel fait est **garanti** par l'autre ?
3. Placez le fait garanti à droite de la flèche.
4. Vérifiez par le contre-exemple : quelle situation contredirait la phrase ?
   Elle doit contredire votre formule.
MD,
                        'solution' => <<<'MD'
1. **N ⇒ T** — « seulement si » : condition nécessaire.
   *(Vous aviez écrit T → N. Compté faux.)*
2. **N ⇒ T** — « ne … que si » : même tournure que la 1.
   *(Vous aviez écrit ¬T ∨ (T ∧ N). Compté faux.)*
3. **N ⇒ T** — la condition nécessaire est le travail, donc conclusion.
   *(Vous aviez écrit deux formules, T→N et ¬T→¬N. Annotation : « faux, choisir, pas équivalent ».)*
4. **¬T ⇒ ¬N** — « à moins que ». Contraposée de N ⇒ T, donc équivalente aux trois premières.
   *(Vous aviez écrit ¬N ∨ T, ce qui est bien équivalent à N ⇒ T. Compté juste.)*
5. **T ∧ ¬N** — conjonction.
   *(Vous aviez écrit T ∧ ¬N. Compté juste.)*

**Le constat :** les quatre premiers énoncés expriment tous que le travail est
nécessaire. Un seul piège les distingue — la tournure. Vous aviez les bons réflexes
sur les questions 4 et 5, formulées sans « seulement ». Les trois erreurs portent
exactement sur les trois formulations en « seulement si », « ne … que si » et
« condition nécessaire ».
MD,
                        'rubric' => [
                            ['label' => '1. N ⇒ T', 'points' => 1],
                            ['label' => '2. N ⇒ T', 'points' => 1],
                            ['label' => '3. N ⇒ T', 'points' => 1],
                            ['label' => '4. ¬T ⇒ ¬N (ou équivalent identifié)', 'points' => 1],
                            ['label' => '5. T ∧ ¬N', 'points' => 1],
                            ['label' => 'Aucune question ne comporte deux formules', 'points' => 2],
                        ],
                    ],
                ],
            ],

            /* ============================ Logique de Hoare ============================ */
            'Hoare' => [
                'lessons' => [
                    [
                        'title' => 'Le triplet et les quatre règles',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
Un triplet de Hoare `{P} S {Q}` est une promesse conditionnelle :

> *Si* l'état vérifie P avant d'exécuter S, *et si* S termine, *alors* l'état vérifie Q après.

Deux « si ». Le second est celui qu'on oublie : le triplet ne garantit **pas** que S
termine. C'est la distinction entre correction partielle et correction totale, et elle
tombe régulièrement.

Prouver un programme, c'est empiler des triplets élémentaires jusqu'à obtenir celui
qu'on visait. Les règles disent comment empiler.
MD,
                        'formalism' => <<<'MD'
**Affectation.** On raisonne à rebours : la précondition se déduit de la postcondition
par substitution.

```
{ Q[x ← E] }  x := E  { Q }
```

**Séquence.** Deux triplets qui se raccordent en Q se composent.

```
{P} S₁ {Q}      {Q} S₂ {R}
———————————————————————————
      {P} S₁ ; S₂ {R}
```

**Conditionnelle.**

```
{P ∧ b} S₁ {Q}      {P ∧ ¬b} S₂ {Q}
———————————————————————————————————
{P} if b then S₁ else S₂ fi {Q}
```

**Boucle.** I est l'invariant : vrai avant, préservé par chaque tour, encore vrai après.

```
        {I ∧ b} S {I}
———————————————————————————
{I} while b do S od {I ∧ ¬b}
```

**Conséquence.** Renforcer la précondition ou affaiblir la postcondition est toujours
permis.

```
P ⇒ P'      {P'} S {Q'}      Q' ⇒ Q
———————————————————————————————————
            {P} S {Q}
```
MD,
                        'worked_example' => <<<'MD'
**Prouver** `{n ≥ 0} i := 0; f := 1; while i < n do i := i+1; f := f*i od {f = n!}`

*Choix de l'invariant.* `I ≡ i ≤ n ∧ f = i!`

Il capture deux choses : où en est le compteur, et ce que vaut l'accumulateur à ce
stade. C'est toujours la forme d'un invariant de boucle d'accumulation.

*Établissement.* Après `i := 0; f := 1` : i = 0 ≤ n car n ≥ 0, et f = 1 = 0! = i!.
Donc I est vrai à l'entrée.

*Préservation.* Supposons `I ∧ i < n`. Par la règle d'affectation appliquée à
`i := i + 1`, puis à `f := f * i`, et composées par **(sequence)** :

| N° | Triplet | Justification |
|---|---|---|
| 1 | {i < n ∧ f = i!} i := i+1 {i ≤ n ∧ f = (i−1)!} | (assignment) |
| 2 | {i ≤ n ∧ f = (i−1)!} f := f∗i {i ≤ n ∧ f = i!} | (assignment) |
| 3 | {i < n ∧ f = i!} i := i+1; f := f∗i {I} | (sequence) 1 2 |

*Application de (while).* De la ligne 3 on tire `{I} while … od {¬(i < n) ∧ I}`.

*Conclusion.* `¬(i < n) ∧ i ≤ n` donne i = n, d'où `f = i! = n!`.
Par **(consequence)**, on obtient le triplet visé.

*Terminaison.* Variant `V = n − i` : entier, minoré par 0 tant que la garde tient,
strictement décroissant. Le programme termine, donc la correction est **totale**.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier la borne dans l'invariant.** Écrire `f = i!` seul ne suffit pas : sans
  `i ≤ n`, la sortie de boucle ne permet pas de conclure i = n.
- **Substituer dans la précondition.** La règle d'affectation substitue dans la
  **post**condition pour produire la précondition. Le sens inverse est faux.
- **Ne pas nommer les règles.** Un tableau de preuve dont la colonne « justification »
  est vide ou vague ne vaut aucun point. Écrivez `(sequence) 1 2`, avec les numéros.
- **Confondre invariant et variant.** L'invariant est une propriété *préservée*,
  le variant une quantité qui *décroît*. L'un prouve la correction, l'autre la terminaison.
- **Croire qu'un triplet prouve la terminaison.** Il ne prouve que la correction
  partielle, sauf variant fourni.
MD,
                        'examiner_expects' => <<<'MD'
Dans un tableau de preuve, chaque ligne doit porter :

- [ ] Le **triplet complet**, précondition et postcondition écrites en entier.
- [ ] Le **nom exact** de la règle, entre parenthèses, dans la notation du cours :
      `(assignment)`, `(sequence)`, `(while)`, `(consequence)`.
- [ ] Les **numéros des lignes** auxquelles la règle s'applique.

Pour une preuve de boucle, la copie doit contenir les quatre éléments :
l'invariant, son établissement, sa préservation, et la conclusion tirée de la sortie.
Si la terminaison est demandée, ajouter le variant avec sa minoration et sa décroissance.
MD,
                        'source_refs' => [
                            ['label' => 'exHoare.pdf — énoncés'],
                            ['label' => 'cHoare.pdf — corrigés et tableau de preuve'],
                            ['label' => 'annotations.pdf'],
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => "Règle d'affectation de la logique de Hoare ?",
                        'back' => "**{ Q[x ← E] }  x := E  { Q }**\n\nOn substitue x par E dans la **postcondition** pour obtenir la précondition. Raisonnement à rebours.\n\n*Exemple : {x+1 = 5} x := x+1 {x = 5}.*",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Règle de la séquence ?',
                        'back' => "```\n{P} S₁ {Q}   {Q} S₂ {R}\n————————————————————————\n    {P} S₁ ; S₂ {R}\n```\n\nLe raccord se fait sur Q, qui doit être **identique** dans les deux triplets.",
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Règle du while ?',
                        'back' => "```\n      {I ∧ b} S {I}\n———————————————————————————\n{I} while b do S od {I ∧ ¬b}\n```\n\nI est l'invariant. En sortie, on dispose de I **et** de la négation de la garde.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Correction partielle ou correction totale : quelle différence ?',
                        'back' => "**Partielle** — *si* S termine, alors Q. C'est ce que dit un triplet {P} S {Q}.\n\n**Totale** — S termine **et** Q. Il faut fournir un **variant** en plus : une quantité entière, minorée, strictement décroissante à chaque tour.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Invariant pour `i := 0; f := 1; while i < n do i := i+1; f := f*i od` ?',
                        'back' => "**I ≡ i ≤ n ∧ f = i!**\n\nLes deux conjoints sont indispensables : sans `i ≤ n`, la sortie de boucle ne permet pas de conclure i = n, et donc pas f = n!.",
                        'hint' => "Un invariant d'accumulation dit toujours deux choses : où en est le compteur, et ce que vaut l'accumulateur.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Invariant et variant : lequel prouve quoi ?',
                        'back' => "**Invariant** — propriété *préservée* à chaque tour. Prouve la **correction**.\n\n**Variant** — quantité entière qui *décroît* strictement et reste minorée. Prouve la **terminaison**.\n\nPour la factorielle : I ≡ i ≤ n ∧ f = i!, V = n − i.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Les quatre éléments attendus dans une preuve de boucle ?',
                        'back' => "1. **L'invariant** I, énoncé explicitement.\n2. Son **établissement** avant l'entrée dans la boucle.\n3. Sa **préservation** par le corps, sous l'hypothèse I ∧ garde.\n4. La **conclusion** tirée de ¬garde ∧ I.\n\n(+ le **variant** si la terminaison est demandée.)",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Compléter le tableau de preuve de la factorielle',
                        'origin' => 'td',
                        'est_minutes' => 30,
                        'difficulty' => 4,
                        'statement' => <<<'MD'
Reproduisez et complétez le tableau suivant pour qu'il constitue une preuve complète,
sous forme de tableau, du programme de calcul de la factorielle vu en cours.

| N° | Triplet | Justification |
|---|---|---|
| 1 | {i < n ∧ r = i!} i := i+1 {i ≤ n ∧ r = (i−1)!} | (assignment) |
| 2 | {i ≤ n ∧ r = (i−1)!} r := r∗i {i ≤ n ∧ r = i!} | (assignment) |
| 3 | {i < n ∧ i ≤ n ∧ r = i!} i := i+1; r := r∗i {i ≤ n ∧ r = i!} | (sequence) 1 2 |
| 4 | … | … |
| 5 | … | … |
| … | … | … |

On prendra `I ≡ i ≤ n ∧ r = i!` comme invariant, et l'on visera le triplet

`{n ≥ 0} i := 0; r := 1; while i < n do i := i+1; r := r∗i od {r = n!}`.
MD,
                        'hint' => "Après la ligne 3, il reste trois choses à établir : que I est vrai à l'entrée, ce que donne la règle du while, et comment la sortie de boucle mène à r = n!.",
                        'method' => <<<'MD'
1. **Ligne 4** — l'implication qui établit I à l'entrée, à partir de `n ≥ 0 ∧ i = 0 ∧ r = 1`.
   Justification : théorème arithmétique admis.
2. **Ligne 5** — appliquer **(while)** à la ligne 3 pour obtenir le triplet de la boucle.
3. **Lignes suivantes** — l'implication de sortie `¬(i < n) ∧ I ⇒ r = n!`, puis
   **(consequence)** pour recoller au triplet visé, et **(sequence)** pour préfixer
   les deux initialisations.
4. Chaque ligne porte le **nom de la règle** et les **numéros** utilisés. Sans cela,
   la colonne justification ne vaut rien.
MD,
                        'solution' => <<<'MD'
| N° | Triplet | Justification |
|---|---|---|
| 4 | n ≥ 0 ∧ i = 0 ∧ r = 1 ⇒ I | OK (arithmétique) |
| 5 | {I} while i < n do i := i+1; r := r∗i od {¬(i < n) ∧ I} | **(while) 3** |
| 6 | ¬(i < n) ∧ i ≤ n ∧ r = i! ⇒ r = n! | OK (de i ≥ n et i ≤ n on tire i = n) |
| 7 | {I} while … od {r = n!} | **(consequence) 5 6** |
| 8 | {n ≥ 0} i := 0 {n ≥ 0 ∧ i = 0} | (assignment) |
| 9 | {n ≥ 0 ∧ i = 0} r := 1 {n ≥ 0 ∧ i = 0 ∧ r = 1} | (assignment) |
| 10 | {n ≥ 0} i := 0; r := 1 {n ≥ 0 ∧ i = 0 ∧ r = 1} | **(sequence) 8 9** |
| 11 | {n ≥ 0} i := 0; r := 1 {I} | **(consequence) 10 4** |
| 12 | {n ≥ 0} i := 0; r := 1; while … od {r = n!} | **(sequence) 11 7** |

La ligne 12 est le triplet visé.

**Terminaison.** Variant `V = n − i`. Entier ; minoré par 0 tant que la garde i < n
est vraie ; strictement décroissant puisque i augmente de 1 à chaque tour. La boucle
termine donc, et la correction est **totale**.
MD,
                        'rubric' => [
                            ["label" => "Ligne 4 : l'implication établissant I à l'entrée", 'points' => 1],
                            ['label' => 'Ligne 5 : (while) appliquée à la ligne 3, avec le numéro', 'points' => 2],
                            ['label' => "Ligne 6 : de ¬(i<n) et i ≤ n on déduit i = n, puis r = n!", 'points' => 2],
                            ['label' => '(consequence) est utilisée pour recoller au triplet visé', 'points' => 1],
                            ['label' => 'Les deux initialisations sont traitées et composées par (sequence)', 'points' => 2],
                            ['label' => 'Chaque ligne porte le nom de la règle ET les numéros utilisés', 'points' => 2],
                            ['label' => 'Le variant n − i est donné avec minoration et décroissance', 'points' => 2],
                        ],
                    ],
                ],
            ],
        ];
    }
}