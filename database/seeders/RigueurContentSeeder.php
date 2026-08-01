<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Gap;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu de la matière transversale « Rigueur de rédaction ».
 *
 * Intégralement dérivé des annotations portées par les correcteurs sur les
 * copies de janvier et de mai. Ce n'est pas du conseil générique : chaque
 * fiche renvoie à une perte de points constatée.
 */
class RigueurContentSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'RIG')->first();

        if (! $subject) {
            return;
        }

        foreach ($this->content() as $chapterCode => $data) {
            $chapter = Chapter::where('subject_id', $subject->id)
                ->where('code', $chapterCode)
                ->first();

            if (! $chapter) {
                continue;
            }

            foreach ($data['lessons'] ?? [] as $i => $lesson) {
                Lesson::updateOrCreate(
                    ['chapter_id' => $chapter->id, 'slug' => Str::slug($lesson['title'])],
                    $lesson + ['position' => $i + 1, 'est_minutes' => $lesson['est_minutes'] ?? 12]
                );
            }

            foreach ($data['cards'] ?? [] as $i => $card) {
                Flashcard::updateOrCreate(
                    ['chapter_id' => $chapter->id, 'front' => $card['front']],
                    $card + ['position' => $i + 1, 'gap_id' => $this->gapFor($subject, $chapter)]
                );
            }

            foreach ($data['exercises'] ?? [] as $i => $exo) {
                Exercise::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $exo['title']],
                    $exo + [
                        'chapter_id' => $chapter->id,
                        'origin' => 'genere',
                        'position' => $i + 1,
                    ]
                );
            }
        }
    }

    private function gapFor(Subject $subject, Chapter $chapter): ?int
    {
        return Gap::where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->value('id');
    }

    private function content(): array
    {
        return [

            /* ============================ R1 ============================ */
            'R1' => [
                'lessons' => [
                    [
                        'title' => 'Une affirmation sans preuve ne vaut rien',
                        'est_minutes' => 12,
                        'intuition' => <<<'MD'
Sur votre copie d'AGC, à la question 1.1, vous avez écrit que les matrices d'adjacence
« permettent de modéliser avec des états booléens les arcs et arêtes du graphe et un accès
plus direct aux données ainsi qu'une meilleure lecture ».

C'est vrai. Et cela a rapporté zéro point.

Le correcteur a écrit deux mots en marge : **« évaluation ? »** et **« justifier »**.
Vous aviez la bonne intuition, mais vous avez décrit un ressenti là où le barème attend
une mesure. « Un accès plus direct » n'est pas une réponse. « O(1) contre O(deg(v)) »
en est une.
MD,
                        'formalism' => <<<'MD'
Une réponse technique complète tient en trois temps :

1. **L'affirmation** — ce que vous soutenez, en une phrase.
2. **La raison** — pourquoi c'est vrai, en vous appuyant sur une définition du cours.
3. **La preuve chiffrée** — une complexité, un contre-exemple, ou une règle nommée.

Le troisième temps est celui que vous omettez systématiquement. C'est aussi celui
qui porte l'essentiel des points.
MD,
                        'worked_example' => <<<'MD'
**La même réponse, réécrite pour le barème :**

> La matrice d'adjacence stocke n² booléens, soit **O(n²) en mémoire**, indépendamment
> du nombre d'arêtes. Elle teste l'existence d'une arête en **O(1)** par accès direct
> à `M[i][j]`, mais énumère les voisins d'un sommet en **O(n)** puisqu'il faut parcourir
> toute la ligne.
>
> Les listes d'adjacence stockent **O(n + m)**, testent une arête en **O(deg(v))** et
> énumèrent les voisins en **O(deg(v))**.
>
> Pour un graphe creux, où m ≪ n², les listes l'emportent nettement en mémoire comme
> en parcours ; c'est le cas ici puisque l'énoncé précise que le graphe est peu dense.

Même contenu de départ. Mais cette version-là contient six chiffres et une conclusion
argumentée, et chacun de ces éléments est une ligne de la grille de correction.
MD,
                        'pitfalls' => <<<'MD'
Les tournures qui signalent que vous êtes en train de perdre des points :

- « **permet de** … » — sans dire à quel coût.
- « **il sera pertinent de** … » — pertinent au regard de quel critère ?
- « **peu pratique pour** … » — impraticable à partir de quelle taille ?
- « **une meilleure lecture** » — jugement esthétique, pas argument technique.
- « **plus rapide** » — plus rapide d'un facteur combien ?

Chacune de ces formules est apparue dans vos copies. Chacune est un point non pris.
MD,
                        'examiner_expects' => <<<'MD'
Avant de passer à la question suivante, relisez votre réponse et cherchez :

- [ ] **Un chiffre** — complexité, taille, nombre d'opérations, borne.
- [ ] **Un nom** — la règle, le théorème ou la définition du cours que vous appliquez.
- [ ] **Un cas** — un exemple, ou mieux, un contre-exemple qui tranche.

S'il n'y en a aucun, la réponse est incomplète, quelle que soit sa longueur.
Une réponse de trois lignes avec une complexité chiffrée vaut plus qu'un paragraphe
de dix lignes sans.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => "Vous venez d'écrire : « les listes d'adjacence permettent un accès plus direct aux données ». Que manque-t-il ?",
                        'back' => "**Le chiffre.** Il faut écrire le coût : test d'arête en O(deg(v)), mémoire en O(n+m), énumération des voisins en O(deg(v)). Sans complexité, le correcteur écrit « évaluation ? » et ne compte rien.",
                        'hint' => "Le correcteur a effectivement écrit deux mots en marge de votre copie AGC.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Les trois temps d\'une réponse technique complète ?',
                        'back' => "1. **L'affirmation** — ce que vous soutenez.\n2. **La raison** — appuyée sur une définition du cours.\n3. **La preuve** — un chiffre, un contre-exemple, ou une règle nommée.\n\nC'est le troisième qui manque dans vos copies.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Coûts comparés : matrice d\'adjacence contre listes d\'adjacence.',
                        'back' => "| | Matrice | Listes |\n|---|---|---|\n| Mémoire | O(n²) | O(n+m) |\n| Test d'arête | O(1) | O(deg v) |\n| Voisins de v | O(n) | O(deg v) |\n| Ajout d'arête | O(1) | O(1) |\n\nGraphe creux (m ≪ n²) → listes. Graphe dense ou tests d'arête intensifs → matrice.",
                        'hint' => 'Ce tableau est la réponse chiffrée que la question 1.1 attendait.',
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Réécrire votre réponse AGC 1.1 pour le barème',
                        'statement' => <<<'MD'
Voici, mot pour mot, ce que vous avez rendu en janvier à la question 1.1 d'AGC :

> « Les listes et tableaux pour lister les données, peu pertinent pour la modélisation
> directe des arêtes et l'un des arcs. Les dictionnaires de données permettant des
> relations de valeurs mais pouvant ensuite être trop grandes et peu pratique pour
> l'accès aux données. Les matrices d'adjacence permettent de modéliser avec des états
> booléens les arcs et arêtes du graphe et un accès plus direct aux données ainsi qu'une
> meilleure lecture. »

Annotations du correcteur : **« justifier »**, **« évaluation ? »**, **« pas vu dans le cours »**.

**Réécrivez cette réponse** de manière à ce qu'elle prenne tous les points.
Contrainte : chaque structure évoquée doit être accompagnée de son coût en mémoire
et du coût des deux opérations principales.
MD,
                        'hint' => "Commencez par lister les structures effectivement traitées dans AGC-cours.pdf, section 1.2 « Représentation informatique ». Les dictionnaires n'en font pas partie — d'où le « pas vu dans le cours ».",
                        'method' => <<<'MD'
1. Ne retenez que les représentations du cours : matrice d'adjacence, listes d'adjacence,
   et le cas échéant matrice d'incidence.
2. Pour chacune, donnez trois nombres : mémoire, test d'arête, énumération des voisins.
3. Concluez en rattachant le choix à une caractéristique de l'énoncé (densité du graphe,
   opération dominante).
4. Une seule recommandation finale. Pas deux.
MD,
                        'solution' => <<<'MD'
> Trois représentations sont traitées dans le cours.
>
> La **matrice d'adjacence** est un tableau n × n de booléens. Elle occupe **O(n²)** en
> mémoire quel que soit le nombre d'arêtes. Le test « (u,v) est-il une arête ? » se fait
> par accès direct en **O(1)**. En revanche, énumérer les voisins d'un sommet impose de
> parcourir sa ligne entière, soit **O(n)**.
>
> Les **listes d'adjacence** associent à chaque sommet la liste de ses voisins. La mémoire
> est **O(n + m)**, proportionnelle à la taille réelle du graphe. Le test d'arête coûte
> **O(deg(u))** car il faut parcourir la liste, mais l'énumération des voisins est optimale,
> en **O(deg(u))**.
>
> La **matrice d'incidence**, de taille n × m, est **O(n·m)** : plus coûteuse que les deux
> autres, elle n'a d'intérêt que pour les traitements portant sur les arêtes.
>
> Le graphe de l'énoncé étant creux, m est de l'ordre de n et non de n². Les listes
> d'adjacence occupent alors **O(n)** contre **O(n²)** pour la matrice, et les parcours
> — opération dominante de l'exercice — y sont en O(n + m) au lieu de O(n²).
> **Je retiens donc les listes d'adjacence.**

**Ce qui a changé :** neuf complexités chiffrées, plus de dictionnaires hors référentiel,
une conclusion unique et argumentée par une propriété de l'énoncé.
MD,
                        'rubric' => [
                            ['label' => "Seules des structures du cours sont citées (pas de dictionnaires)", 'points' => 2],
                            ['label' => 'Coût mémoire donné pour chaque structure', 'points' => 2],
                            ['label' => "Coût du test d'arête donné pour chaque structure", 'points' => 2],
                            ['label' => "Coût de l'énumération des voisins donné pour chaque structure", 'points' => 2],
                            ['label' => "Le choix final est rattaché à une propriété de l'énoncé (densité)", 'points' => 2],
                            ['label' => 'Une seule recommandation finale, pas deux', 'points' => 2],
                        ],
                        'est_minutes' => 25,
                        'difficulty' => 3,
                    ],
                ],
            ],

            /* ============================ R2 ============================ */
            'R2' => [
                'lessons' => [
                    [
                        'title' => 'Deux réponses valent moins qu\'une',
                        'est_minutes' => 8,
                        'intuition' => <<<'MD'
Sur votre copie de SPP, question 1.3, vous avez écrit deux formules l'une sous l'autre :

```
T → N
¬T → ¬N
```

Le correcteur a barré et annoté : **« faux, choisir, pas équivalent »**.

Le réflexe est compréhensible : dans le doute, on écrit les deux candidates en espérant
que la bonne soit reconnue. Mais un correcteur ne trie pas à votre place. Deux réponses
contradictoires signalent que vous ne savez pas laquelle est juste — et c'est précisément
ce qui est évalué.
MD,
                        'formalism' => <<<'MD'
La règle est sans exception : **une question, une réponse.**

Si l'hésitation persiste au moment de rédiger, la procédure est la suivante :

1. Écrire la réponse retenue, seule.
2. Ajouter une ligne : « je retiens cette formulation parce que … ».
3. Ne jamais laisser visible la candidate écartée.

L'argument d'une ligne coûte dix secondes et transforme une hésitation en démarche.
MD,
                        'worked_example' => <<<'MD'
**Ce que vous avez rendu :**

```
3) Pour un étudiant, le travail est une condition nécessaire
   à l'obtention de bonnes notes :
        T → N
        ¬T → ¬N
```

**Ce qu'il fallait rendre :**

> Le travail est **nécessaire** aux bonnes notes : avoir de bonnes notes impose d'avoir
> travaillé. La bonne note est donc l'hypothèse, le travail la conclusion :
>
> **N ⇒ T**
>
> (La contraposée ¬T ⇒ ¬N est logiquement équivalente ; T ⇒ N ne l'est pas, elle
> exprimerait que travailler *suffit*.)

Une seule formule mise en avant. La parenthèse montre que vous maîtrisez la contraposée
sans laisser d'ambiguïté sur la réponse retenue.
MD,
                        'pitfalls' => <<<'MD'
- Écrire deux formules superposées sans les départager.
- Écrire « ou bien … ou bien … » dans une réponse rédigée.
- Raturer une réponse sans la rendre illisible : le correcteur la lit quand même.
- Laisser un point d'interrogation en marge de sa propre copie.
MD,
                        'examiner_expects' => <<<'MD'
Une réponse, et une seule, clairement identifiable — encadrée, soulignée, ou isolée
sur sa ligne. Si vous voulez montrer que vous connaissez une forme équivalente,
mettez-la entre parenthèses en la nommant explicitement « contraposée » ou
« forme équivalente ». Jamais côte à côte, jamais sans étiquette.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => "À l'examen, vous hésitez entre deux formalisations. Que faites-vous ?",
                        'back' => "**Vous en choisissez une**, vous l'écrivez seule, et vous ajoutez une ligne d'argument.\n\nÉcrire les deux fait perdre les points des deux : sur SPP, le correcteur a annoté « faux, choisir, pas équivalent ».",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => '« A est une condition **nécessaire** à B » — quelle implication ?',
                        'back' => "**B ⇒ A**\n\nB ne peut se produire sans A. Le mot qui suit « nécessaire » est la **conclusion** de l'implication.\n\n*Le travail est nécessaire aux bonnes notes* → N ⇒ T.",
                        'hint' => 'Question 1.3 de votre épreuve de mai. Vous aviez écrit T → N.',
                    ],
                    [
                        'kind' => 'definition',
                        'front' => '« A est une condition **suffisante** pour B » — quelle implication ?',
                        'back' => "**A ⇒ B**\n\nA garantit B. Le mot qui suit « suffisante » est l'**hypothèse**.\n\n*Travailler suffit à avoir de bonnes notes* → T ⇒ N.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Traduire : « c\'est seulement si un étudiant travaille qu\'il a de bonnes notes ».',
                        'back' => "**N ⇒ T**\n\n« Seulement si » introduit une **condition nécessaire**, pas suffisante.\n\nVous aviez répondu T → N à la question 1 : c'est l'erreur inverse, et elle a été comptée fausse.",
                        'hint' => "« Seulement si » et « il faut que » désignent la même chose.",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Les six tournures françaises de l\'implication',
                        'statement' => <<<'MD'
Formalisez chacun des énoncés suivants en logique propositionnelle, avec
T = « l'étudiant travaille » et N = « l'étudiant a de bonnes notes ».

**Une seule formule par énoncé.** Toute réponse comportant deux formules
non départagées est comptée fausse — c'est la règle appliquée à votre copie de mai.

1. C'est seulement si un étudiant travaille qu'il a de bonnes notes.
2. Un étudiant n'a de bonnes notes que s'il travaille.
3. Pour un étudiant, le travail est une condition nécessaire à l'obtention de bonnes notes.
4. Un étudiant a de mauvaises notes, à moins qu'il ne travaille.
5. Malgré son travail, un étudiant a de mauvaises notes.
6. Il suffit qu'un étudiant travaille pour qu'il ait de bonnes notes.
MD,
                        'hint' => "Repérez d'abord, dans chaque phrase, lequel des deux faits est **garanti** par l'autre. Le fait garanti est toujours à droite de la flèche.",
                        'method' => <<<'MD'
Établissez le tableau de correspondance avant de répondre :

| Tournure | Se lit | Formalisation |
|---|---|---|
| « si A alors B » | A suffit | A ⇒ B |
| « A seulement si B » | B est nécessaire | A ⇒ B |
| « A que si B » | B est nécessaire | A ⇒ B |
| « B est nécessaire à A » | — | A ⇒ B |
| « B suffit pour A » | — | B ⇒ A |
| « A à moins que B » | — | ¬B ⇒ A |
| « malgré B, A » | conjonction | B ∧ A |

Appliquez-le mécaniquement, sans reconstruire l'intuition à chaque fois.
MD,
                        'solution' => <<<'MD'
1. **N ⇒ T** — « seulement si » introduit une condition nécessaire.
2. **N ⇒ T** — « ne … que si » est la même tournure. Reformulation identique à la 1.
3. **N ⇒ T** — le travail est nécessaire, il est donc la conclusion.
4. **¬T ⇒ ¬N** — « à moins que » : sans travail, mauvaises notes.
   *(Équivalente à N ⇒ T par contraposition : les quatre premiers énoncés disent la même chose.)*
5. **T ∧ ¬N** — « malgré » n'est pas une implication, c'est une conjonction.
   Le travail a eu lieu **et** les notes sont mauvaises.
6. **T ⇒ N** — « il suffit que » introduit la condition suffisante. C'est le seul
   énoncé de la liste à se formaliser dans ce sens.

**Le piège de l'exercice :** cinq énoncés sur six vont dans le sens N ⇒ T.
Sur votre copie, vous aviez écrit T → N aux questions 1 et 3, et une conjonction
mal orientée à la 5. L'énoncé 6 est le seul où T → N aurait été juste.
MD,
                        'rubric' => [
                            ['label' => '1. N ⇒ T', 'points' => 1],
                            ['label' => '2. N ⇒ T', 'points' => 1],
                            ['label' => '3. N ⇒ T', 'points' => 1],
                            ['label' => '4. ¬T ⇒ ¬N (ou sa contraposée, identifiée comme telle)', 'points' => 1],
                            ['label' => '5. T ∧ ¬N — conjonction, pas implication', 'points' => 1],
                            ['label' => '6. T ⇒ N', 'points' => 1],
                            ['label' => 'Une seule formule par énoncé, aucune paire non départagée', 'points' => 2],
                        ],
                        'est_minutes' => 20,
                        'difficulty' => 3,
                    ],
                ],
            ],

            /* ============================ R3 ============================ */
            'R3' => [
                'lessons' => [
                    [
                        'title' => 'Le barème ne connaît que le polycopié',
                        'est_minutes' => 8,
                        'intuition' => <<<'MD'
En marge de votre copie d'AGC, le correcteur a rayé une notion et écrit :
**« pas vu dans le cours »**.

Ce n'était peut-être pas faux. C'était hors sujet, ce qui revient au même au moment
de compter les points. Une grille de correction énumère des éléments attendus,
formulés dans le vocabulaire de l'enseignant. Un terme équivalent trouvé ailleurs
ne coche aucune case.
MD,
                        'formalism' => <<<'MD'
Trois conséquences pratiques :

1. **Réviser sur les polycopiés**, pas sur des cours trouvés en ligne. Le périmètre
   du barème est exactement celui du document distribué.
2. **Employer le terme de l'enseignant** même s'il vous paraît moins usuel.
   « Programmation gloutonne » et non « algorithme glouton » si c'est ainsi que le
   chapitre s'intitule.
3. **En cas de doute sur une notion**, se demander : à quelle page du polycopié
   se trouve-t-elle ? Si la réponse ne vient pas, ne pas l'utiliser.
MD,
                        'worked_example' => <<<'MD'
Vos cinq référentiels, et rien d'autre :

| Matière | Document de référence | Pages |
|---|---|---|
| ALO | `alo_V9.pdf` | 130 |
| EP | `cours_ep.pdf` | 93 |
| AGC | `AGC-cours.pdf` | 125 |
| SPP | les dix paires `ex*.pdf` / `c*.pdf` | ~1 500 |
| MIA | `mainMOIA.pdf` | 236 |

Tous sont dans la bibliothèque de Méridien, avec recherche plein texte.
Avant d'employer un terme dans une copie, cherchez-le : s'il n'y est pas, il n'existe pas.
MD,
                        'pitfalls' => <<<'MD'
- Réutiliser le vocabulaire d'un cours suivi ailleurs, ou d'une vidéo.
- Employer un synonyme « plus correct » que celui du polycopié.
- Citer un théorème sous un nom différent de celui du cours.
- Introduire une structure de données ou un algorithme non traité, même s'il est meilleur.
MD,
                        'examiner_expects' => <<<'MD'
Le vocabulaire exact du polycopié, et la notation exacte du polycopié.
Si le cours écrit un triplet de Hoare `{P} S {Q}`, ne l'écrivez pas `[P] S [Q]`.
Si le cours parle de « listes d'adjacence », n'écrivez pas « tableau de voisins ».

La correction est un appariement de motifs, pas une conversation.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => 'Vous hésitez à employer une notion dans une copie. Quel test appliquez-vous ?',
                        'back' => "**À quelle page du polycopié se trouve-t-elle ?**\n\nSi vous ne pouvez pas répondre, ne l'utilisez pas. Le correcteur d'AGC a rayé une notion en écrivant « pas vu dans le cours » : elle ne figurait sur aucune ligne du barème.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Les cinq documents de référence, un par matière.',
                        'back' => "- ALO → `alo_V9.pdf` (130 p.)\n- EP → `cours_ep.pdf` (93 p.)\n- AGC → `AGC-cours.pdf` (125 p.)\n- SPP → les dix paires `ex*.pdf` / `c*.pdf`\n- MIA → `mainMOIA.pdf` (236 p.)\n\nLe barème ne va pas au-delà.",
                    ],
                ],
            ],

            /* ============================ R4 ============================ */
            'R4' => [
                'lessons' => [
                    [
                        'title' => 'Le 26 août : six heures d\'épreuve en une journée',
                        'est_minutes' => 10,
                        'intuition' => <<<'MD'
Le mercredi 26 août, vous composez **de 15 h à 18 h en AGC**, puis **de 20 h à 23 h en SPP**.

Six heures d'examen, deux heures de coupure, et la seconde épreuve est celle où vous
avez obtenu 1,5 sur 20.

Ce n'est pas seulement une question de connaissances. À 22 h, après cinq heures de
composition dans la journée, la qualité de rédaction s'effondre bien avant la mémoire.
C'est exactement le moment où l'on écrit deux formules au lieu d'une.
MD,
                        'formalism' => <<<'MD'
**La règle des points par minute.** En début d'épreuve, avant d'écrire quoi que ce soit :

1. Lire le sujet en entier, deux minutes.
2. Reporter le barème en face de chaque exercice.
3. Calculer le temps alloué : `durée × points de l'exercice / total des points`.
4. Écrire ces horaires en marge, et s'y tenir.

Pour une épreuve de 180 minutes sur 20 points, un exercice à 6 points vaut 54 minutes.
Au-delà, on passe à la suite même si ce n'est pas fini.
MD,
                        'worked_example' => <<<'MD'
**Plan type pour AGC, 15 h – 18 h, 3 exercices :**

| | Barème | Temps | Horaire |
|---|---|---|---|
| Lecture du sujet et découpage | — | 5 min | 15 h 00 |
| Exercice 1 | 7 pts | 60 min | 15 h 05 |
| Exercice 2 | 7 pts | 60 min | 16 h 05 |
| Exercice 3 | 6 pts | 50 min | 17 h 05 |
| Relecture ciblée | — | 5 min | 17 h 55 |

En janvier, vous avez obtenu 2, 2 et 3 sur ces trois exercices : la répartition était
équilibrée, le problème n'était pas le temps mais le contenu. Le plan reste utile pour
éviter de s'enliser sur une question bloquante.

**Entre 18 h et 20 h :** manger, ne pas réviser SPP en panique. Une relecture calme
des dix formules de correspondance de la logique propositionnelle suffit. C'est
l'exercice 1 de SPP, et c'est là que vous avez perdu le plus de points en mai.
MD,
                        'pitfalls' => <<<'MD'
- Commencer à rédiger avant d'avoir lu tout le sujet.
- S'enliser sur la première question difficile et perdre l'heure de la troisième.
- Laisser une question entièrement vide alors qu'une définition correcte rapporte déjà.
- Sur un QCM sans pénalité, ne pas répondre. **Votre copie d'ALO comporte des items vides** :
  sans point négatif, cocher au hasard a une espérance strictement positive.
- Relire « en entier » à la fin : la relecture doit être ciblée sur les chiffres et
  les formules, pas sur le style.
MD,
                        'examiner_expects' => <<<'MD'
Une copie où **chaque question a reçu quelque chose**. Une définition juste, un début
de formalisation, un contre-exemple : tout cela vaut mieux qu'un blanc.

Les points ne sont pas attribués à la copie dans son ensemble, mais ligne par ligne.
Trois questions à moitié traitées rapportent plus qu'une question parfaite et deux vides.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => 'Épreuve de 180 minutes sur 20 points. Combien de temps pour un exercice à 6 points ?',
                        'back' => "**54 minutes.**\n\n180 × 6 / 20 = 54.\n\nÀ calculer et à noter en marge dans les cinq premières minutes de l'épreuve, avant de rédiger la moindre ligne.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'QCM sans points négatifs, vous ne savez pas répondre. Que faites-vous ?',
                        'back' => "**Vous cochez.** L'espérance d'une réponse au hasard est strictement positive ; celle d'une case vide est nulle.\n\nVotre copie d'ALO de janvier comporte plusieurs items laissés vides. Note obtenue : 0/20.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Programme du mercredi 26 août ?',
                        'back' => "**15 h – 18 h : AGC** (3 h)\n**18 h – 20 h : coupure**, repas, relecture calme des correspondances logiques\n**20 h – 23 h : SPP** (3 h)\n\nSix heures de composition. SPP, la matière la plus faible, tombe en dernier et en soirée.",
                        'hint' => 'La seule journée du rattrapage à porter deux épreuves.',
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Construire le plan horaire des cinq épreuves',
                        'statement' => <<<'MD'
Pour chacune des cinq épreuves du rattrapage, établissez le plan horaire que vous
appliquerez le jour J.

| Épreuve | Date | Créneau | Durée |
|---|---|---|---|
| ALO | 24 août | 20 h – 23 h | 180 min |
| EP | 25 août | 14 h – 16 h | 120 min |
| AGC | 26 août | 15 h – 18 h | 180 min |
| SPP | 26 août | 20 h – 23 h | 180 min |
| MIA | 28 août | 15 h – 17 h | 120 min |

Pour chacune : consultez l'annale correspondante dans la bibliothèque, relevez le
nombre d'exercices et le barème, puis calculez le temps alloué à chaque exercice
par la règle des points par minute. Réservez cinq minutes de lecture initiale et
cinq minutes de relecture ciblée.

Traitez en dernier le cas particulier du 26 août : que faites-vous entre 18 h et 20 h ?
MD,
                        'hint' => "Les annales sont dans la bibliothèque, filtre « Annale ». Pour MIA, la matrice examens/chapitres indique en plus quels chapitres tombent le plus souvent.",
                        'method' => <<<'MD'
1. Ouvrez l'annale la plus récente de chaque matière.
2. Relevez le nombre d'exercices et, quand il est indiqué, le barème de chacun.
   Sans barème explicite, supposez une répartition égale.
3. Appliquez : `temps = (durée − 10) × points / total`.
4. Écrivez le tableau horaire complet, heure par heure.
5. Pour le 26 août, prévoyez explicitement le repas, le trajet éventuel, et ce que
   vous relisez — ou ne relisez pas — pendant la coupure.
MD,
                        'rubric' => [
                            ['label' => 'Un plan horaire chiffré pour chacune des cinq épreuves', 'points' => 3],
                            ['label' => 'Le barème réel de chaque annale a été relevé, pas supposé', 'points' => 2],
                            ['label' => 'Cinq minutes de lecture initiale réservées partout', 'points' => 1],
                            ['label' => 'Cinq minutes de relecture ciblée réservées partout', 'points' => 1],
                            ['label' => 'La coupure du 26 août entre 18 h et 20 h est planifiée explicitement', 'points' => 2],
                            ['label' => 'Une règle est fixée pour les QCM sans pénalité : ne jamais laisser vide', 'points' => 1],
                        ],
                        'est_minutes' => 35,
                        'difficulty' => 2,
                    ],
                ],
            ],
        ];
    }
}