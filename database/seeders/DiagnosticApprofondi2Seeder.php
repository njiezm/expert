<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\ExamPaper;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Gap;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Diagnostic approfondi, seconde passe : AGC, EP et MIA.
 *
 * Trois découvertes qui redirigent la préparation :
 *
 * 1. AGC. L'exercice 2 était la plus longue sous-séquence commune — un problème
 *    de programmation dynamique — traité en double boucle naïve. Annotation :
 *    « → pas Glouton », « il ne faut pas revenir au début à chaque fois ». 2/6.
 *    Et la question de complexité a été laissée entièrement vide.
 *
 * 2. EP. « 2 boucles imbriquées … Θ(log n) ». Deux boucles imbriquées coûtent
 *    O(n²). Le mot « logarithmique » est employé au sens de « grand » dans deux
 *    exercices distincts. Plusieurs questions sont laissées blanches.
 *
 * 3. MIA. L'exercice 2 portait sur le raisonnement par défaut. « En général, les
 *    managers sont expérimentés » a été formalisé par une implication universelle.
 *    Annotation : « Non on veut des défauts ». 0 point.
 */
class DiagnosticApprofondi2Seeder extends Seeder
{
    public function run(): void
    {
        $this->gaps();
        $this->agcReconnaitreLeProbleme();
        $this->epComplexite();
        $this->miaDefauts();

        foreach (['AGC' => 3, 'EP' => 3, 'MIA' => 4] as $code => $pages) {
            ExamPaper::whereHas('subject', fn ($q) => $q->where('code', $code))
                ->update(['analysed_pages' => $pages]);
        }
    }

    /* ==================================================================== */

    private function gaps(): void
    {
        $entries = [
            'AGC' => [
                [
                    'title' => 'Problème de programmation dynamique traité en glouton',
                    'chapter' => 'PD',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => '→ pas Glouton',
                    'explanation' => "Exercice 2 : recherche de la plus longue sous-séquence commune entre deux chaînes. La réponse propose de parcourir la plus longue chaîne et de comparer élément par élément — une approche gloutonne. Le correcteur a barré et écrit « → pas Glouton ». C'est un problème de programmation dynamique classique. 2 points sur 6.",
                    'remedy' => "Apprendre à reconnaître le déclencheur : dès que le choix optimal à une étape dépend de choix ultérieurs, le glouton échoue et il faut une table. Mémoriser la récurrence de la plus longue sous-séquence commune.",
                ],
                [
                    'title' => 'Question de complexité laissée entièrement vide',
                    'chapter' => 'PD',
                    'kind' => 'rigueur',
                    'severity' => 5,
                    'evidence' => '?',
                    'explanation' => "Question 2.4 : « La complexité de cette solution est » — la phrase s'arrête là. Rien n'a été écrit après. Le correcteur a posé un « ? ». C'est la troisième fois sur la même copie qu'une évaluation manque, après les « justifier » et « évaluation ? » de l'exercice 1.",
                    'remedy' => "Ne jamais laisser une question de complexité vide. Même un encadrement approximatif — « au moins O(n·m) puisqu'on parcourt les deux chaînes » — vaut mieux que rien, et rapporte souvent la moitié des points.",
                ],
                [
                    'title' => 'Pseudo-code rendu sans explication ni jeu de tests',
                    'chapter' => 'G1',
                    'kind' => 'rigueur',
                    'severity' => 4,
                    'evidence' => "pas d'explication = 0 · Où sont les tests !",
                    'explanation' => "Question 1.4 : un pseudo-code `Trier(T)` est fourni sans un mot d'explication. Le correcteur écrit « pas d'explication = 0 » et « Où sont les tests ! ». Question 1.3 : la description du parcours d'arbre ne dit pas comment les nœuds sont initialisés — « comment ces nœuds sont initialisés ? ».",
                    'remedy' => "Tout algorithme rendu doit être accompagné de trois choses : une phrase d'intention, un déroulé sur un petit exemple, et sa complexité. Le code seul ne vaut rien — c'est la même faute qu'à ALO, où le schéma manquait.",
                ],
                [
                    'title' => 'Syntaxe de pseudo-code approximative',
                    'chapter' => 'G1',
                    'kind' => 'methode',
                    'severity' => 3,
                    'evidence' => 'syntaxe : utiliser les tableaux',
                    'explanation' => "Question 2.3 : les chaînes sont manipulées comme des scalaires, avec `pour val1 dans ch1` et des concaténations. Le correcteur demande d'utiliser une indexation de tableau. Et « il ne faut pas revenir au début à chaque fois » signale que la boucle interne repart de zéro — précisément ce que la mémoïsation évite.",
                    'remedy' => "Manipuler les chaînes par indices : `A[i]`, `B[j]`, avec des boucles `pour i de 1 à n`. C'est la notation attendue, et c'est celle qui rend une table de programmation dynamique naturelle.",
                ],
            ],

            'EP' => [
                [
                    'title' => 'Deux boucles imbriquées annoncées en Θ(log n)',
                    'chapter' => 'C7',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'le nombre d\'actions élémentaires sera logarithmiquement grand avec Θ(log n)',
                    'explanation' => "Exercice 2 question 1. Deux boucles imbriquées sur une entrée de taille n coûtent **O(n²)**, jamais Θ(log n). Le logarithme apparaît quand on **divise** l'espace de recherche à chaque étape, pas quand on l'énumère. La même confusion revient à l'exercice 3 : « complexité logarithmique importante ».",
                    'remedy' => "Retenir les quatre motifs de base : une boucle → O(n) ; deux boucles imbriquées → O(n²) ; division par deux à chaque tour → O(log n) ; boucle contenant une division par deux → O(n log n). Le logarithme ne signifie pas « grand », il signifie « on divise ».",
                ],
                [
                    'title' => 'Questions entièrement laissées vides',
                    'chapter' => 'C6',
                    'kind' => 'rigueur',
                    'severity' => 5,
                    'explanation' => "Exercice 2 question 3 : rien, le numéro est barré. Exercice 3 question 3 : rien. Exercice 2 question 2 s'interrompt sur « si chaque calcul dure 10⁻⁶ seconde » sans faire le calcul. Sur une épreuve de 2 heures, trois questions abandonnées représentent une part considérable du barème.",
                    'remedy' => "Aucune question ne se laisse vide. Une définition juste, un début de raisonnement, un ordre de grandeur : tout cela vaut des points. Et quand un calcul numérique est amorcé, il faut le terminer — la valeur finale est ce qui se compte.",
                ],
                [
                    'title' => 'Définition de NP confuse et circulaire',
                    'chapter' => 'C6',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'evidence' => '?',
                    'explanation' => "Exercice 3 question 1 : « NP signifiant Non-déterministe Polynomial donc solvable mais pas dans un temps polynomial car NP est par définition décidable donc p ⊆ NP est décidable ». Le sigle est correctement développé, mais la suite est circulaire et affirme qu'un problème de NP n'est pas résoluble en temps polynomial — ce qui exclurait P de NP, alors que P ⊆ NP.",
                    'remedy' => "Fixer les trois énoncés : NP = décidable en temps polynomial par une machine **non déterministe** ; définition équivalente = solution **vérifiable** en temps polynomial ; et **P ⊆ NP**. Un problème de NP peut parfaitement être polynomial.",
                ],
                [
                    'title' => 'Exercice de réduction réduit à des fragments de notation',
                    'chapter' => 'C5',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'explanation' => "Exercice 4 : les réponses se limitent à des lignes de notation ensembliste — `S ⊆ V`, `(u,v) ∈ S×S`, `D ⊆ V` — sans construction de machine, sans hypothèse de départ, sans contradiction. Le schéma en quatre étapes d'une réduction n'apparaît pas.",
                    'remedy' => "Appliquer le gabarit : supposer l'existence de R, construire S, justifier que S s'arrête, conclure à la contradiction. Quatre étapes numérotées, à écrire même quand la construction est incomplète.",
                ],
            ],

            'MIA' => [
                [
                    'title' => 'Raisonnement par défaut formalisé en logique classique',
                    'chapter' => 'Ch2',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'Non on veut des défauts',
                    'explanation' => "Exercice 2. « En général, les managers sont expérimentés » a été formalisé `∀x manager(x) ⇒ expérimenté(x)`. Le correcteur a écrit « Non on veut des défauts » et mis 0. La locution « en général » signale une règle **révisable**, qui souffre des exceptions : c'est un défaut au sens de Reiter, pas une implication universelle. Avec l'implication classique, l'énoncé « les stagiaires n'ont pas de grandes responsabilités » rendrait la base incohérente.",
                    'remedy' => "Apprendre la notation des défauts et le repérage des locutions déclencheuses : « en général », « normalement », « typiquement », « sauf exception ». Chapitre 2, section « Les logiques non-classiques ».",
                ],
                [
                    'title' => 'Équivalence employée à la place d\'une implication',
                    'chapter' => 'Ch2',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'explanation' => "Toujours à l'exercice 2 : « les stagiaires sont considérés comme des managers » a été formalisé `∀x stagiaire(x) ⟺ manager(x)`. L'équivalence affirme aussi que tout manager est stagiaire, ce que l'énoncé ne dit pas. Une implication suffisait — et, ici encore, sous forme de défaut.",
                    'remedy' => "Le double sens ne s'écrit que si l'énoncé le dit deux fois. « A est considéré comme B » donne A ⇒ B, jamais A ⟺ B.",
                ],
                [
                    'title' => 'Chapitre 2 non couvert alors qu\'il est le plus évalué',
                    'chapter' => 'Ch2',
                    'kind' => 'methode',
                    'severity' => 5,
                    'explanation' => "La matrice examens/chapitres place le chapitre 2 parmi les plus fréquemment évalués depuis 2010, et il est tombé à la session de mai 2026 sous la forme du raisonnement par défaut. La partie correspondante a été notée 0,75 sur 6.",
                    'remedy' => "Traiter le chapitre 2 en priorité, à égalité avec le chapitre 0 (Prolog) et le chapitre 4 (contraintes).",
                ],
            ],
        ];

        foreach ($entries as $code => $list) {
            $subject = Subject::where('code', $code)->first();

            if (! $subject) {
                continue;
            }

            $paper = ExamPaper::where('subject_id', $subject->id)->first();

            foreach ($list as $i => $data) {
                $chapter = Chapter::where('subject_id', $subject->id)
                    ->where('code', $data['chapter'])->first();

                Gap::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $data['title']],
                    [
                        'chapter_id' => $chapter?->id,
                        'exam_paper_id' => $paper?->id,
                        'kind' => $data['kind'],
                        'evidence' => $data['evidence'] ?? null,
                        'explanation' => $data['explanation'],
                        'remedy' => $data['remedy'],
                        'severity' => $data['severity'],
                        'position' => 30 + $i,
                    ]
                );
            }
        }
    }

    /* ==================================================================== */

    private function agcReconnaitreLeProbleme(): void
    {
        $subject = Subject::where('code', 'AGC')->first();
        $chapter = Chapter::where('subject_id', $subject?->id)->where('code', 'PD')->first();

        if (! $chapter) {
            return;
        }

        Lesson::updateOrCreate(
            ['chapter_id' => $chapter->id, 'slug' => 'glouton-ou-programmation-dynamique'],
            [
                'title' => 'Glouton ou programmation dynamique ?',
                'est_minutes' => 20,
                'position' => 0,
                'intuition' => <<<'MD'
L'exercice 2 de janvier demandait la **plus longue sous-séquence commune** entre
deux chaînes. Vous avez proposé de parcourir la plus longue et de comparer élément
par élément. Le correcteur a barré et écrit :

> **« → pas Glouton. »**

Puis, sur le pseudo-code : **« il ne faut pas revenir au début à chaque fois »**,
et **« incomplet »**. Note : 2 sur 6.

La question de complexité qui suivait — « la complexité de cette solution est » —
est restée vide. Le correcteur a mis un « ? ».

Reconnaître **quel type de problème** on a devant soi vaut plus que savoir dérouler
un algorithme. C'est la première décision, et elle commande tout le reste.
MD,
                'formalism' => <<<'MD'
**Le test de décision, en une question**

> *Le meilleur choix à cette étape dépend-il de ce qui viendra après ?*

- **Non** → **glouton**. Choisir localement le meilleur mène à l'optimum global.
- **Oui** → **programmation dynamique**. Il faut explorer et mémoriser.

**Les problèmes du cours, classés**

| Problème | Approche | Pourquoi |
|---|---|---|
| Arbre couvrant de poids minimal | **glouton** (Kruskal, Prim) | la propriété de coupe garantit le choix local |
| Coloration de graphe | **glouton** (approché) | heuristique, pas d'optimalité garantie |
| Choix d'activités | **glouton** | trier par date de fin suffit |
| Plus courts chemins, poids ≥ 0 | **glouton** (Dijkstra) | la distance minimale se fige |
| **Plus longue sous-séquence commune** | **dynamique** | garder un caractère peut coûter plus loin |
| Sac à dos 0/1 | **dynamique** | prendre un objet condamne peut-être un meilleur |
| Distance d'édition | **dynamique** | idem |
| Plus courts chemins, poids négatifs | **dynamique** (Bellman-Ford) | une distance figée peut être améliorée |

**Le signal d'alarme.** Dès que votre boucle interne **repart du début** à chaque
tour de la boucle externe, c'est que vous recalculez. Recalculer, c'est le symptôme
du chevauchement des sous-problèmes — donc de la programmation dynamique.

C'est exactement ce que signifie « il ne faut pas revenir au début à chaque fois ».
MD,
                'worked_example' => <<<'MD'
**La plus longue sous-séquence commune, comme il fallait la traiter.**

*Entrées :* `A` de longueur n, `B` de longueur m.
*Sortie :* la longueur de la plus longue suite de caractères présents dans les deux,
dans le même ordre, pas nécessairement contigus.

**1. Définition.** Soit `T[i][j]` la longueur de la plus longue sous-séquence commune
entre les `i` premiers caractères de `A` et les `j` premiers de `B`.

**2. Récurrence.**

```
T[0][j] = 0                                     pour tout j
T[i][0] = 0                                     pour tout i
T[i][j] = T[i-1][j-1] + 1                       si A[i] = B[j]
T[i][j] = max( T[i-1][j], T[i][j-1] )           sinon
```

**3. Sous-structure optimale.** Si `A[i] = B[j]`, toute solution optimale utilise
cette paire : la remplacer par autre chose ne peut pas allonger la suite. Le reste
est alors une solution optimale sur les préfixes réduits. Sinon, la solution optimale
ignore `A[i]` ou ignore `B[j]`, d'où le `max`.

**4. Ordre de remplissage.** i croissant, puis j croissant : chaque case dépend de
`[i-1][j-1]`, `[i-1][j]` et `[i][j-1]`, toutes déjà calculées.

**5. Complexité.** **Temps O(n·m)**, **espace O(n·m)**, réductible à **O(min(n,m))**
en ne conservant que la ligne précédente.

**Exemple chiffré** — le vôtre : `A = aaaatt`, `B = tcaa`.

| | ∅ | t | c | a | a |
|---|---|---|---|---|---|
| **∅** | 0 | 0 | 0 | 0 | 0 |
| **a** | 0 | 0 | 0 | 1 | 1 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **t** | 0 | 1 | 1 | 1 | 2 |
| **t** | 0 | 1 | 1 | 1 | **2** |

Résultat : **2**, la sous-séquence `aa`. Vous aviez trouvé la bonne réponse par
intuition — mais sans la table, sans la récurrence, et sans la complexité.
MD,
                'pitfalls' => <<<'MD'
- **Proposer un glouton sur un problème de sous-séquence.** Garder un caractère
  maintenant peut empêcher d'en garder deux plus loin : le choix local n'est pas sûr.
- **Une boucle interne qui repart du début.** Symptôme du recalcul, donc de la
  programmation dynamique manquée.
- **Laisser la complexité vide.** Même une borne grossière rapporte. Un « ? » du
  correcteur, c'est zéro assuré.
- **Confondre sous-séquence et sous-chaîne.** Une **sous-séquence** n'est pas
  contiguë ; une **sous-chaîne** l'est. Les récurrences diffèrent.
- **Affirmer l'optimalité sans preuve.** Votre question 2.5 affirmait que la solution
  est toujours optimale : la phrase a été barrée. L'optimalité se démontre par la
  sous-structure optimale.
MD,
                'examiner_expects' => <<<'MD'
Devant un problème d'optimisation :

- [ ] **Annoncer l'approche** et pourquoi : « le choix local ne garantit pas
      l'optimum, donc programmation dynamique ».
- [ ] La **définition** de la variable d'état.
- [ ] La **récurrence** complète avec ses cas de base.
- [ ] La **justification** de la sous-structure optimale.
- [ ] La **complexité en temps et en espace** — jamais vide.
- [ ] Un **déroulé** sur un petit exemple, sous forme de table.
MD,
                'source_refs' => [
                    ['label' => 'COPIE_AGC_ZAMON_a.pdf — exercice 2'],
                    ['label' => 'ExosProgDyn.pdf'],
                ],
            ]
        );

        $cards = [
            [
                'kind' => 'methode',
                'front' => 'Glouton ou programmation dynamique : la question qui tranche ?',
                'back' => "**Le meilleur choix à cette étape dépend-il de ce qui viendra après ?**\n\n**Non** → glouton.\n**Oui** → programmation dynamique.\n\nSur la plus longue sous-séquence commune, garder un caractère peut coûter plus loin : c'est donc dynamique. Le correcteur a écrit « → pas Glouton ».",
                'difficulty' => 5,
            ],
            [
                'kind' => 'formule',
                'front' => 'Récurrence de la plus longue sous-séquence commune ?',
                'back' => "```\nT[0][j] = T[i][0] = 0\nT[i][j] = T[i-1][j-1] + 1          si A[i] = B[j]\nT[i][j] = max(T[i-1][j], T[i][j-1])  sinon\n```\n\nTemps **O(n·m)**, espace O(n·m) réductible à **O(min(n,m))**.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'piege',
                'front' => "Votre boucle interne repart du début à chaque tour de la boucle externe. Que signale ce symptôme ?",
                'back' => "**Que vous recalculez** — donc que les sous-problèmes se chevauchent, donc qu'il faut de la **programmation dynamique**.\n\nAnnotation du correcteur : « il ne faut pas revenir au début à chaque fois ».",
                'difficulty' => 5,
            ],
            [
                'kind' => 'piege',
                'front' => 'Sous-séquence et sous-chaîne : quelle différence ?',
                'back' => "**Sous-séquence** — les caractères gardent l'ordre mais **ne sont pas contigus**. `aa` est une sous-séquence de `aaaatt`.\n\n**Sous-chaîne** — les caractères sont **contigus**.\n\nLes deux problèmes ont des récurrences différentes.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'methode',
                'front' => "Vous ne savez pas calculer la complexité demandée. Que faites-vous ?",
                'back' => "**Vous écrivez une borne, même grossière.**\n\n« Au moins O(n·m) puisque les deux chaînes sont parcourues » vaut des points. Une case vide en vaut zéro — et le correcteur a mis un « ? » à la question 2.4 de votre copie.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'definition',
                'front' => 'Quels problèmes du cours AGC se résolvent en glouton ?',
                'back' => "**Arbre couvrant minimal** (Kruskal, Prim) · **choix d'activités** · **Dijkstra** (poids ≥ 0) · **coloration** (approchée).\n\nEn **dynamique** : plus longue sous-séquence commune, sac à dos 0/1, distance d'édition, Bellman-Ford.",
                'difficulty' => 4,
            ],
        ];

        foreach ($cards as $i => $card) {
            Flashcard::updateOrCreate(
                ['chapter_id' => $chapter->id, 'front' => $card['front']],
                $card + ['position' => 50 + $i]
            );
        }

        Exercise::updateOrCreate(
            ['subject_id' => $subject->id, 'title' => "Exercice 2 de janvier — la sous-séquence commune, à refaire"],
            [
                'chapter_id' => $chapter->id,
                'origin' => 'annale',
                'est_minutes' => 40,
                'difficulty' => 4,
                'position' => 0,
                'statement' => <<<'MD'
Reprise de l'exercice 2 de l'épreuve de janvier, noté 2 sur 6.

Soit deux chaînes `A` et `B`. On cherche leur **plus longue sous-séquence commune** :
la plus longue suite de caractères présents dans les deux, **dans le même ordre**,
pas nécessairement contigus.

**1.** Sur `A = aaaatt` et `B = tcaa`, donnez la plus longue sous-séquence commune.
Une approche gloutonne convient-elle ? Justifiez. *(1 pt)*

**2.** Définissez la variable d'état et posez la relation de récurrence
avec ses cas de base. *(2 pts)*

**3.** Justifiez la sous-structure optimale. *(1 pt)*

**4.** Donnez la complexité **en temps et en espace**. *(1 pt)*

**5.** Déroulez la table complète sur `A = aaaatt` et `B = tcaa`. *(1 pt)*

Rappel de janvier : la question de complexité était restée vide, et le correcteur
avait écrit « ? ». Aucune question ne se laisse blanche.
MD,
                'hint' => "Pour la question 1 : cherchez un cas où garder un caractère maintenant empêche d'en garder deux plus tard. C'est ce qui disqualifie le glouton.",
                'method' => <<<'MD'
1. Testez le glouton mentalement sur un petit contre-exemple avant de conclure.
2. Nommez la table : « Soit T[i][j] la longueur de la plus longue sous-séquence
   commune entre les i premiers caractères de A et les j premiers de B. »
3. Deux cas : les caractères courants coïncident, ou non.
4. La complexité se lit sur la taille de la table et le coût d'une case.
5. Remplissez la table ligne par ligne, en bordant de zéros.
MD,
                'solution' => <<<'MD'
**1.** La plus longue sous-séquence commune est **`aa`**, de longueur 2.

**Le glouton ne convient pas.** Contre-exemple : `A = ab`, `B = ba`.
Un glouton qui prend le premier caractère commun rencontré retiendrait `a`
(position 1 de A, position 2 de B), puis ne pourrait plus rien prendre — longueur 1.
Or `b` seul donne aussi 1, et la réponse optimale est bien 1 ici. Prenons mieux :
`A = abc`, `B = bac`. Le glouton prend `a` (A₁, B₂), puis ne peut plus que prendre
`c` : longueur 2. C'est correct par chance. Le vrai contre-exemple tient au fait que
**garder un caractère tôt contraint tous les choix suivants** : la décision locale
n'est pas séparable du reste, donc **programmation dynamique**.

*(Le correcteur avait simplement écrit « → pas Glouton ».)*

**2.** Soit `T[i][j]` la longueur de la plus longue sous-séquence commune entre
`A[1..i]` et `B[1..j]`.

```
T[0][j] = 0                                pour tout j        (cas de base)
T[i][0] = 0                                pour tout i        (cas de base)
T[i][j] = T[i-1][j-1] + 1                  si A[i] = B[j]
T[i][j] = max( T[i-1][j], T[i][j-1] )      sinon
```

**3.** Si `A[i] = B[j]`, il existe une solution optimale qui apparie ces deux
caractères : sinon, on pourrait l'y ajouter et allonger la suite, contredisant
l'optimalité. Le reste de la solution est alors optimal sur `A[1..i-1]` et `B[1..j-1]`.
Si `A[i] ≠ B[j]`, la solution optimale n'utilise pas `A[i]`, ou n'utilise pas `B[j]` —
d'où le maximum des deux cas.

**4. Complexité.**
- **Temps : O(n · m)** — une case par couple (i, j), chaque case en temps constant.
- **Espace : O(n · m)** pour la table complète, **réductible à O(min(n, m))**
  si l'on ne conserve que la ligne précédente et que l'on n'a pas besoin de
  reconstruire la sous-séquence elle-même.

**5. Table** pour `A = aaaatt` (lignes) et `B = tcaa` (colonnes) :

| | ∅ | t | c | a | a |
|---|---|---|---|---|---|
| **∅** | 0 | 0 | 0 | 0 | 0 |
| **a** | 0 | 0 | 0 | 1 | 1 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **a** | 0 | 0 | 0 | 1 | 2 |
| **t** | 0 | 1 | 1 | 1 | 2 |
| **t** | 0 | 1 | 1 | 1 | **2** |

La case en bas à droite vaut **2** : la plus longue sous-séquence commune est de
longueur 2, c'est `aa`.
MD,
                'rubric' => [
                    ['label' => 'Q1 : réponse `aa` de longueur 2', 'points' => 1],
                    ['label' => 'Q1 : le glouton est écarté avec un argument', 'points' => 1],
                    ['label' => 'Q2 : la variable d’état est définie en une phrase', 'points' => 1],
                    ['label' => 'Q2 : les deux cas de la récurrence et les cas de base', 'points' => 2],
                    ['label' => 'Q3 : sous-structure optimale démontrée dans les deux cas', 'points' => 1],
                    ['label' => 'Q4 : complexité en temps O(n·m) ET en espace, non laissée vide', 'points' => 2],
                    ['label' => 'Q5 : table remplie correctement, bordée de zéros', 'points' => 2],
                ],
            ]
        );
    }

    /* ==================================================================== */

    private function epComplexite(): void
    {
        $subject = Subject::where('code', 'EP')->first();
        $chapter = Chapter::where('subject_id', $subject?->id)->where('code', 'C7')->first();

        if (! $chapter) {
            return;
        }

        Lesson::updateOrCreate(
            ['chapter_id' => $chapter->id, 'slug' => 'les-quatre-motifs-de-complexite'],
            [
                'title' => 'Les quatre motifs de complexité',
                'est_minutes' => 15,
                'position' => 0,
                'intuition' => <<<'MD'
Sur votre copie, exercice 2 question 1 :

> « Ici nous avons **2 boucles imbriquées** avec des conditions. Plus n sera grand,
> plus le nombre d'actions élémentaires sera **logarithmiquement grand** avec **Θ(log n)**. »

Deux boucles imbriquées coûtent **O(n²)**. Jamais Θ(log n).

La confusion revient à l'exercice 3 : « une complexité logarithmique importante ».
Le mot semble employé au sens de « qui grandit beaucoup ». C'est l'inverse :
**le logarithme est la croissance la plus lente qu'on rencontre**, et il apparaît
uniquement quand on **divise**.

Quatre motifs suffisent à couvrir presque tous les cas de l'épreuve.
MD,
                'formalism' => <<<'MD'
**Les quatre motifs**

| Structure du code | Complexité | Pourquoi |
|---|---|---|
| Une boucle de 1 à n | **O(n)** | n itérations |
| Deux boucles imbriquées de 1 à n | **O(n²)** | n × n itérations |
| On **divise** l'espace par 2 à chaque tour | **O(log n)** | il faut log₂ n divisions pour atteindre 1 |
| Une boucle contenant une division par 2 | **O(n log n)** | n × log n |

**Le logarithme n'apparaît que par division.** Recherche dichotomique, hauteur d'un
arbre équilibré, tri fusion, tas binaire. Si votre algorithme **énumère** au lieu
de **diviser**, il n'y a pas de log.

**L'échelle, du plus rapide au plus lent :**

```
O(1) < O(log n) < O(n) < O(n log n) < O(n²) < O(n³) < O(2ⁿ) < O(n!)
```

**Ordres de grandeur pour n = 1 000 000, à 10⁻⁶ seconde par opération** —
c'est exactement le calcul que votre question 2 avait amorcé sans le finir :

| Complexité | Opérations | Temps |
|---|---|---|
| O(log n) | ≈ 20 | 0,00002 s |
| O(n) | 10⁶ | 1 seconde |
| O(n log n) | ≈ 2 × 10⁷ | 20 secondes |
| O(n²) | 10¹² | **11,6 jours** |
| O(2ⁿ) | — | au-delà de l'âge de l'univers |

C'est ce tableau qui répond à « pour de trop grandes valeurs cet algorithme ne
convient pas » — avec un chiffre au lieu d'une impression.
MD,
                'worked_example' => <<<'MD'
**Compter, en trois lignes.**

```
pour i de 1 à n :
    pour j de 1 à n :
        si T[i] > T[j] :
            échanger
```

1. **Boucle interne** : n itérations.
2. **Boucle externe** : n itérations, chacune déclenchant la boucle interne.
3. **Total** : n × n = **n² opérations, soit O(n²)**.

**Le cas où le log apparaît vraiment :**

```
g ← 1 ; d ← n
tant que g <= d :
    m ← (g + d) / 2
    si T[m] = x : renvoyer m
    sinon si T[m] < x : g ← m + 1
    sinon : d ← m - 1
```

L'intervalle `[g, d]` est **divisé par deux** à chaque tour. Partant de n, il faut
**log₂ n** divisions pour arriver à 1. D'où **O(log n)**.

La différence tient en un mot : la première **énumère**, la seconde **divise**.

**Le calcul numérique, terminé.** À 10⁻⁶ seconde par opération et n = 10⁶ :
la version O(n²) demande 10¹² × 10⁻⁶ = **10⁶ secondes**, soit environ **11,6 jours**.
La version O(log n) demande 20 × 10⁻⁶ = **20 microsecondes**.

C'est cette phrase-là — le chiffre, puis l'unité de temps — que la question attendait.
MD,
                'pitfalls' => <<<'MD'
- **Employer « logarithmique » pour dire « grand ».** C'est la croissance la plus
  lente de l'échelle usuelle.
- **Voir un log là où il n'y a qu'une énumération.** Le logarithme vient d'une
  **division** de l'espace de recherche, pas d'une boucle.
- **Confondre O, Ω et Θ.** `O` majore, `Ω` minore, `Θ` encadre. Écrire `Θ(log n)`
  affirme que c'est **exactement** logarithmique — une affirmation bien plus forte
  que `O`.
- **Amorcer un calcul numérique sans le terminer.** « Si chaque calcul dure
  10⁻⁶ seconde… » doit se conclure par un nombre et une unité.
- **Oublier que la complexité se mesure sur la taille de l'entrée en bits.**
  Un algorithme en O(W) où W est une valeur est pseudo-polynomial.
MD,
                'examiner_expects' => <<<'MD'
- [ ] Le **motif reconnu** : combien de boucles, imbriquées ou non, division ou
      énumération.
- [ ] Le **comptage** posé, pas seulement la conclusion.
- [ ] La notation correcte : `O` pour majorer, `Θ` seulement si l'encadrement est
      établi dans les deux sens.
- [ ] Tout calcul numérique **terminé**, avec sa valeur et son unité.
MD,
                'source_refs' => [
                    ['label' => 'COPIE_EP_ZAMON.pdf — exercice 2'],
                    ['label' => 'cours_ep.pdf § 7 — Comparaison d’algorithmes'],
                ],
            ]
        );

        $cards = [
            [
                'kind' => 'piege',
                'front' => 'Deux boucles imbriquées de 1 à n : quelle complexité ?',
                'back' => "**O(n²)** — n × n itérations.\n\nJamais Θ(log n). Sur votre copie de janvier vous aviez écrit « 2 boucles imbriquées… Θ(log n) ».\n\nLe logarithme vient d'une **division**, jamais d'une énumération.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'methode',
                'front' => 'Les quatre motifs de complexité ?',
                'back' => "| Code | Coût |\n|---|---|\n| une boucle | **O(n)** |\n| deux boucles imbriquées | **O(n²)** |\n| division par 2 à chaque tour | **O(log n)** |\n| boucle contenant une division par 2 | **O(n log n)** |",
                'difficulty' => 4,
            ],
            [
                'kind' => 'piege',
                'front' => "Quand un logarithme apparaît-il dans une complexité ?",
                'back' => "**Uniquement quand on divise** l'espace de recherche — typiquement par deux à chaque étape.\n\nRecherche dichotomique, hauteur d'arbre équilibré, tri fusion, tas.\n\nUne boucle qui **énumère** ne produit jamais de log.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'definition',
                'front' => 'O, Ω, Θ : quelle différence ?',
                'back' => "**O(g)** — majore : f croît **au plus** comme g.\n**Ω(g)** — minore : f croît **au moins** comme g.\n**Θ(g)** — encadre : les deux à la fois.\n\nÉcrire `Θ` affirme bien plus que `O` : il faut avoir établi les deux bornes.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'formule',
                'front' => "n = 10⁶, une opération dure 10⁻⁶ s. Combien de temps pour un algorithme en O(n²) ?",
                'back' => "**Environ 11,6 jours.**\n\n(10⁶)² × 10⁻⁶ = 10¹² × 10⁻⁶ = 10⁶ secondes ≈ 11,6 jours.\n\nEn O(n) : 1 seconde. En O(log n) : 20 microsecondes.\n\nC'est le calcul que votre copie avait amorcé sans le terminer.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'definition',
                'front' => "L'échelle des complexités, du plus rapide au plus lent ?",
                'back' => "**O(1) < O(log n) < O(n) < O(n log n) < O(n²) < O(n³) < O(2ⁿ) < O(n!)**\n\nLe logarithme est la croissance la **plus lente** après la constante — pas la plus rapide.",
            ],
        ];

        foreach ($cards as $i => $card) {
            Flashcard::updateOrCreate(
                ['chapter_id' => $chapter->id, 'front' => $card['front']],
                $card + ['position' => 50 + $i]
            );
        }
    }

    /* ==================================================================== */

    private function miaDefauts(): void
    {
        $subject = Subject::where('code', 'MIA')->first();
        $chapter = Chapter::where('subject_id', $subject?->id)->where('code', 'Ch2')->first();

        if (! $chapter) {
            return;
        }

        Lesson::updateOrCreate(
            ['chapter_id' => $chapter->id, 'slug' => 'raisonnement-par-defaut'],
            [
                'title' => 'Le raisonnement par défaut',
                'est_minutes' => 22,
                'position' => 1,
                'intuition' => <<<'MD'
À l'exercice 2 de mai, l'énoncé posait quatre phrases :

> F₁ — « **En général**, les managers sont expérimentés. »
> F₂ — « **En général**, les personnes expérimentées ont de grandes responsabilités. »
> F₃ — « **En général**, les stagiaires sont considérés comme des managers. »
> F₄ — « **En général**, les stagiaires n'ont pas de grandes responsabilités. »

Vous avez répondu `∀x manager(x) ⇒ expérimenté(x)`. Le correcteur a écrit :

> **« Non on veut des défauts »** — 0 point.

Et il avait raison de le souligner, car avec des implications classiques la base
est **incohérente** : un stagiaire est manager (F₃), donc expérimenté (F₁), donc
responsable (F₂) — et F₄ dit qu'il ne l'est pas. Contradiction.

« En général » ne se formalise pas par `⇒`. C'est un **défaut** : une règle
révisable, qui admet des exceptions.
MD,
                'formalism' => <<<'MD'
**La logique classique est monotone.** Ajouter une hypothèse ne retire jamais une
conclusion. Or le sens commun ne fonctionne pas ainsi : « les oiseaux volent »,
puis « Titi est un pingouin », et la conclusion tombe.

**La logique des défauts de Reiter** lève cette rigidité. Un **défaut** s'écrit :

```
     P(x) : J(x)
   ───────────────
       C(x)
```

Qui se lit : *si `P(x)` est établi, et s'il est **cohérent** de supposer `J(x)`,
alors conclure `C(x)`.*

| Partie | Nom | Rôle |
|---|---|---|
| `P` | **prérequis** | ce qui doit être démontré |
| `J` | **justification** | ce qu'il doit être cohérent de supposer |
| `C` | **conséquent** | ce qu'on conclut |

Le cas courant est le **défaut normal**, où justification et conséquent coïncident :

```
   manager(x) : expérimenté(x)
   ───────────────────────────
        expérimenté(x)
```

*Si x est manager et que rien ne contredit qu'il soit expérimenté, alors il l'est.*

**Une extension** est un ensemble de conclusions obtenu en appliquant les défauts
jusqu'à saturation, sans jamais se contredire. Une théorie de défauts peut avoir
**plusieurs extensions** — c'est le cas dès qu'il existe un conflit, comme celui
du diamant de Nixon.

**Les locutions déclencheuses** — celles qui doivent faire écrire un défaut plutôt
qu'une implication :

> « en général » · « normalement » · « typiquement » · « habituellement » ·
> « sauf exception » · « par défaut » · « la plupart du temps »
MD,
                'worked_example' => <<<'MD'
**L'exercice de mai, refait.**

```
        manager(x) : expérimenté(x)
d₁ =   ─────────────────────────────
             expérimenté(x)

        expérimenté(x) : responsable(x)
d₂ =   ────────────────────────────────
              responsable(x)

        stagiaire(x) : manager(x)
d₃ =   ───────────────────────────
             manager(x)

        stagiaire(x) : ¬responsable(x)
d₄ =   ────────────────────────────────
             ¬responsable(x)
```

**Question — « Fabrice est manager ». Que conclut-on ?**

Base de faits : `W = { manager(fabrice) }`.

- `d₁` s'applique : `manager(fabrice)` est établi, et `expérimenté(fabrice)` est
  cohérent avec ce qu'on sait. → **`expérimenté(fabrice)`**
- `d₂` s'applique : `expérimenté(fabrice)` est établi, `responsable(fabrice)` est
  cohérent. → **`responsable(fabrice)`**

**Extension unique : { manager, expérimenté, responsable }.**

**Le cas intéressant — « Alice est stagiaire ».**

Base : `W = { stagiaire(alice) }`.

Deux chemins s'ouvrent, et ils se contredisent :

- Par `d₄` : `¬responsable(alice)`.
- Par `d₃` puis `d₁` puis `d₂` : `manager`, `expérimenté`, `responsable(alice)`.

**Deux extensions** :

1. `{ stagiaire, ¬responsable }` — on applique `d₄` d'abord ; `d₂` devient
   inapplicable car `responsable(alice)` n'est plus cohérent.
2. `{ stagiaire, manager, expérimenté, responsable }` — on applique la chaîne
   `d₃, d₁, d₂` d'abord ; `d₄` devient inapplicable.

**C'est précisément ce que la logique classique ne peut pas exprimer.** Avec des
implications, la base serait simplement incohérente et démontrerait n'importe quoi.

En pratique, on privilégie l'extension 1 : le défaut le plus **spécifique**
(stagiaire) l'emporte sur le plus général (manager). C'est le principe de
spécificité, qu'il faut mentionner.
MD,
                'pitfalls' => <<<'MD'
- **Formaliser « en général » par `⇒`.** C'est l'erreur qui a coûté l'exercice.
  L'implication est absolue ; le défaut est révisable.
- **Employer `⟺` pour « est considéré comme ».** Vous aviez écrit
  `∀x stagiaire(x) ⟺ manager(x)`. L'équivalence affirme aussi que tout manager est
  stagiaire, ce que l'énoncé ne dit pas. Une seule direction, et sous forme de défaut.
- **Oublier la justification du défaut.** Les trois parties — prérequis,
  justification, conséquent — doivent apparaître. Le trait de fraction aussi.
- **Ne donner qu'une extension** quand il y en a plusieurs. Le cas conflictuel est
  toujours celui que l'énoncé vise.
- **Ne pas mentionner la spécificité** pour départager deux extensions.
MD,
                'examiner_expects' => <<<'MD'
- [ ] Un **défaut par phrase** commençant par « en général », écrit avec sa barre
      de fraction et ses trois parties.
- [ ] La **base de faits** `W` posée séparément des défauts.
- [ ] Le **déroulé** de l'application des défauts, un par un.
- [ ] **Toutes les extensions** quand il y a conflit, pas seulement une.
- [ ] Le **principe de spécificité** invoqué pour privilégier une extension.
MD,
                'source_refs' => [
                    ['label' => 'COPIE_MIA_ZAMON.pdf — exercice 2'],
                    ['label' => 'mainMOIA.pdf § 2.2 — Les logiques non-classiques'],
                ],
            ]
        );

        $cards = [
            [
                'kind' => 'piege',
                'front' => "« En général, les managers sont expérimentés. » Comment formaliser ?",
                'back' => "**Par un défaut, pas par une implication :**\n\n```\n manager(x) : expérimenté(x)\n────────────────────────────\n     expérimenté(x)\n```\n\nVous aviez écrit `∀x manager(x) ⇒ expérimenté(x)`. Annotation : « **Non on veut des défauts** ». 0 point.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'definition',
                'front' => 'Les trois parties d’un défaut de Reiter ?',
                'back' => "```\n  P(x) : J(x)\n ─────────────\n     C(x)\n```\n\n**P** prérequis — ce qui doit être établi.\n**J** justification — ce qu'il doit être **cohérent** de supposer.\n**C** conséquent — ce qu'on conclut.\n\nDéfaut **normal** : J = C.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'methode',
                'front' => 'Quelles locutions signalent un défaut plutôt qu’une implication ?',
                'back' => "« **en général** » · « normalement » · « typiquement » · « habituellement » · « sauf exception » · « par défaut » · « la plupart du temps »\n\nToutes marquent une règle **révisable**, qui admet des exceptions.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'definition',
                'front' => "Pourquoi la logique classique ne convient-elle pas au raisonnement de sens commun ?",
                'back' => "**Parce qu'elle est monotone** : ajouter une hypothèse ne retire jamais une conclusion.\n\nOr « les oiseaux volent » + « Titi est un pingouin » doit **annuler** la conclusion. C'est ce que permettent les logiques non monotones.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'definition',
                'front' => "Qu'est-ce qu'une extension en logique des défauts ?",
                'back' => "**Un ensemble de conclusions obtenu en appliquant les défauts jusqu'à saturation, sans contradiction.**\n\nUne théorie peut avoir **plusieurs extensions** quand des défauts entrent en conflit. Les donner toutes fait partie de la réponse.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'methode',
                'front' => 'Deux extensions se contredisent. Comment départager ?',
                'back' => "**Par le principe de spécificité** : le défaut le plus **spécifique** l'emporte sur le plus général.\n\n« Les stagiaires ne sont pas responsables » (spécifique) prime sur « les managers sont expérimentés donc responsables » (général).",
                'difficulty' => 5,
            ],
            [
                'kind' => 'piege',
                'front' => "« Les stagiaires sont considérés comme des managers. » — `⇒` ou `⟺` ?",
                'back' => "**Une seule direction**, et sous forme de défaut.\n\n`⟺` affirmerait aussi que tout manager est stagiaire, ce que l'énoncé ne dit pas. Vous aviez écrit l'équivalence sur votre copie.",
                'difficulty' => 4,
            ],
        ];

        foreach ($cards as $i => $card) {
            Flashcard::updateOrCreate(
                ['chapter_id' => $chapter->id, 'front' => $card['front']],
                $card + ['position' => 10 + $i]
            );
        }

        Exercise::updateOrCreate(
            ['subject_id' => $subject->id, 'title' => 'Exercice 2 de mai — les défauts, à refaire'],
            [
                'chapter_id' => $chapter->id,
                'origin' => 'annale',
                'est_minutes' => 30,
                'difficulty' => 4,
                'position' => 0,
                'statement' => <<<'MD'
Reprise de l'exercice 2 de l'épreuve du 22 mai, noté 0.

On dispose des quatre énoncés suivants :

- **F₁** — En général, les managers sont expérimentés.
- **F₂** — En général, les personnes expérimentées ont de grandes responsabilités.
- **F₃** — En général, les stagiaires sont considérés comme des managers
  (pour la gestion des accès).
- **F₄** — En général, les stagiaires n'ont pas de grandes responsabilités.

**1.** Formalisez F₁ à F₄. *(4 pts)*

**2.** On apprend que **Fabrice est manager**. Que peut-on conclure ?
Déroulez le raisonnement. *(2 pts)*

**3.** On apprend qu'**Alice est stagiaire**. Que peut-on conclure ?
Combien d'extensions obtient-on ? Détaillez-les. *(3 pts)*

**4.** Que se passerait-il si l'on formalisait F₁ à F₄ par de simples implications
universelles ? *(1 pt)*
MD,
                'hint' => "La locution « en général » est répétée quatre fois dans l'énoncé. Ce n'est pas un hasard de rédaction : c'est l'indication du formalisme attendu.",
                'method' => <<<'MD'
1. Repérez la locution déclencheuse : « en général » → défaut.
2. Écrivez chaque défaut avec sa barre de fraction et ses trois parties.
3. Posez la base de faits `W` séparément.
4. Appliquez les défauts un par un, en vérifiant à chaque étape que la justification
   reste **cohérente** avec ce qui est déjà conclu.
5. Quand deux chemins se contredisent, donnez **les deux extensions**, puis
   départagez par la spécificité.
MD,
                'solution' => <<<'MD'
**1. Les quatre défauts.**

```
        manager(x) : expérimenté(x)              expérimenté(x) : responsable(x)
d₁ =   ─────────────────────────────      d₂ = ────────────────────────────────
             expérimenté(x)                            responsable(x)

        stagiaire(x) : manager(x)                stagiaire(x) : ¬responsable(x)
d₃ =   ───────────────────────────           d₄ = ──────────────────────────────
             manager(x)                                ¬responsable(x)
```

Ce sont des **défauts normaux** : la justification coïncide avec le conséquent.

**2. Fabrice est manager.** `W = { manager(fabrice) }`.

- `d₁` : le prérequis `manager(fabrice)` est établi ; la justification
  `expérimenté(fabrice)` est cohérente avec W. → **`expérimenté(fabrice)`**
- `d₂` : le prérequis `expérimenté(fabrice)` vient d'être conclu ; la justification
  `responsable(fabrice)` reste cohérente. → **`responsable(fabrice)`**
- `d₃` et `d₄` ne s'appliquent pas : Fabrice n'est pas stagiaire.

**Extension unique :** `{ manager(fabrice), expérimenté(fabrice), responsable(fabrice) }`.

**3. Alice est stagiaire.** `W = { stagiaire(alice) }`.

Deux défauts sont immédiatement applicables, `d₃` et `d₄`, et ils mènent à des
conclusions contradictoires. D'où **deux extensions**.

**Extension E₁** — on applique `d₄` en premier :
- `d₄` → `¬responsable(alice)`
- `d₃` → `manager(alice)`
- `d₁` → `expérimenté(alice)`
- `d₂` **ne s'applique pas** : sa justification `responsable(alice)` contredit
  `¬responsable(alice)` déjà conclu.

`E₁ = { stagiaire, manager, expérimenté, ¬responsable }`

**Extension E₂** — on applique la chaîne `d₃, d₁, d₂` en premier :
- `d₃` → `manager(alice)`
- `d₁` → `expérimenté(alice)`
- `d₂` → `responsable(alice)`
- `d₄` **ne s'applique pas** : sa justification `¬responsable(alice)` contredit
  `responsable(alice)`.

`E₂ = { stagiaire, manager, expérimenté, responsable }`

**Laquelle privilégier ?** `E₁`, par le **principe de spécificité** : le défaut `d₄`
porte directement sur les stagiaires, alors que la conclusion `responsable` de `E₂`
transite par la classe plus générale des managers. Une information spécifique prime
sur une information générale héritée.

**4. Avec des implications classiques.**

On aurait `∀x stagiaire(x) ⇒ manager(x)`, `∀x manager(x) ⇒ expérimenté(x)`,
`∀x expérimenté(x) ⇒ responsable(x)` et `∀x stagiaire(x) ⇒ ¬responsable(x)`.

De `stagiaire(alice)` on déduirait par la chaîne `responsable(alice)`, et par F₄
`¬responsable(alice)`. **La base serait incohérente**, et une base incohérente
démontre n'importe quelle formule.

C'est exactement pourquoi le raisonnement par défaut existe : il permet de tenir
des règles générales **avec exceptions** sans faire s'effondrer la théorie.
MD,
                'rubric' => [
                    ['label' => 'Les quatre énoncés sont écrits comme des défauts, avec barre de fraction', 'points' => 2],
                    ['label' => 'Les trois parties (prérequis, justification, conséquent) apparaissent', 'points' => 2],
                    ['label' => 'Q2 : déroulé d₁ puis d₂, extension unique donnée', 'points' => 2],
                    ['label' => 'Q3 : le conflit entre d₄ et la chaîne d₃-d₁-d₂ est identifié', 'points' => 1],
                    ['label' => 'Q3 : les **deux** extensions sont détaillées', 'points' => 2],
                    ['label' => 'Q3 : le principe de spécificité est invoqué pour départager', 'points' => 1],
                    ['label' => 'Q4 : l’incohérence de la version classique est démontrée', 'points' => 1],
                ],
            ]
        );
    }
}