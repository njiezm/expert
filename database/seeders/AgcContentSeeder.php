<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu d'AGC — 7/20 (Ex1 : 2, Ex2 : 2, Ex3 : 3), épreuve du 26 août à 15 h.
 *
 * Le diagnostic est sans ambiguïté : les annotations « justifier », « évaluation ? »
 * et « alors ce n'est plus une matrice » portent toutes sur le même exercice, celui
 * du choix d'une représentation de graphe. Le chapitre G1 est donc traité en premier
 * et en priorité, autour du tableau des coûts.
 */
class AgcContentSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'AGC')->first();

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

    /* ==================================================================== */

    private function content(): array
    {
        return [

            /* ============ G1 — Représentation des graphes ============ */
            'G1' => [
                'lessons' => [
                    [
                        'title' => 'Choisir une représentation, et le prouver',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
C'est l'exercice qui vous a coûté le plus de points en janvier : 2 sur 7.
Trois annotations en marge, toutes sur la même question.

> « justifier » · « évaluation ? » · « alors ce n'est plus une matrice »

Le fond n'était pas faux. Vous aviez identifié la matrice d'adjacence comme candidate
sérieuse. Ce qui manquait, c'était **le chiffre**. La question ne demandait pas quelle
structure vous préférez : elle demandait de **l'évaluer**.

Ce chapitre tient dans un tableau qu'il faut connaître par cœur, et savoir déployer
en trois phrases.
MD,
                        'formalism' => <<<'MD'
Notations : **n** = nombre de sommets, **m** = nombre d'arêtes, **deg(v)** = degré de v.

**Le tableau des coûts — à mémoriser intégralement**

| Opération | Matrice d'adjacence | Listes d'adjacence | Matrice d'incidence |
|---|---|---|---|
| Mémoire | **O(n²)** | **O(n + m)** | **O(n · m)** |
| Test « (u,v) ∈ E ? » | **O(1)** | **O(deg(u))** | O(m) |
| Énumérer les voisins de u | **O(n)** | **O(deg(u))** | O(m) |
| Ajouter une arête | O(1) | O(1) | O(n · m) |
| Supprimer une arête | O(1) | O(deg(u)) | O(n · m) |
| Parcours complet (BFS/DFS) | **O(n²)** | **O(n + m)** | O(n · m) |

**La règle de décision**

- Graphe **creux** (m ≪ n², typiquement m = O(n)) → **listes d'adjacence**.
  La mémoire passe de O(n²) à O(n), et les parcours de O(n²) à O(n).
- Graphe **dense** (m ≈ n²) ou **tests d'arête intensifs** → **matrice d'adjacence**.
  Le test en O(1) devient déterminant.
- **Matrice d'incidence** : réservée aux traitements portant sur les arêtes elles-mêmes.
  Rarement le bon choix.

**Le point que le correcteur a relevé.** Une matrice d'adjacence est un tableau
n × n. « Une matrice d'adjacence sous forme de listes de listes » n'existe pas :
soit c'est un tableau à accès direct — une matrice — soit ce sont des listes chaînées
parcourues séquentiellement. Les deux structures ont des coûts différents, c'est
tout l'intérêt de les distinguer.
MD,
                        'worked_example' => <<<'MD'
**Le gabarit de réponse à appliquer, quelle que soit la formulation de la question.**

> *« On dispose d'un graphe de n sommets et m arêtes, sur lequel l'opération dominante
> est [le parcours / le test d'arête]. Quelle représentation choisir ? »*

**Trois phrases, pas une de plus :**

> La matrice d'adjacence occupe **O(n²)** en mémoire, teste une arête en **O(1)** mais
> énumère les voisins d'un sommet en **O(n)**.
>
> Les listes d'adjacence occupent **O(n + m)**, testent une arête en **O(deg(u))** et
> énumèrent les voisins en **O(deg(u))**, ce qui est optimal.
>
> L'énoncé précise que le graphe est **creux** et que l'opération dominante est le
> **parcours**. Les listes ramènent le parcours complet de O(n²) à O(n + m) et la
> mémoire de O(n²) à O(n). **Je retiens donc les listes d'adjacence.**

Comptez : **sept complexités chiffrées** et **une conclusion rattachée à une propriété
de l'énoncé**. C'est exactement ce que la grille attend, et c'est ce qui manquait
en janvier.

**Comparez avec ce que vous aviez rendu :**

> « Les matrices d'adjacence permettent de modéliser avec des états booléens les arcs
> et arêtes du graphe et un accès plus direct aux données ainsi qu'une meilleure lecture. »

Zéro chiffre. Zéro conclusion. « Une meilleure lecture » n'est pas un critère technique.
MD,
                        'pitfalls' => <<<'MD'
- **Décrire sans chiffrer.** « Accès plus direct », « peu pratique », « meilleure
  lecture » : aucune de ces expressions ne rapporte de point. Le correcteur a écrit
  « évaluation ? » précisément là.
- **Mélanger les structures.** « Matrice sous forme de listes de listes » est une
  contradiction : les deux se distinguent justement par leurs coûts d'accès.
- **Citer des structures hors cours.** Les dictionnaires ne figurent pas dans la
  section 1.2 du polycopié — d'où le « pas vu dans le cours ».
- **Ne pas conclure.** Après avoir comparé, il faut **trancher** et dire pourquoi,
  en s'appuyant sur une donnée de l'énoncé (densité, opération dominante).
- **Oublier la distinction orienté / non orienté.** En non orienté, la matrice est
  symétrique et chaque arête apparaît deux fois dans les listes : la somme des degrés
  vaut **2m**.
MD,
                        'examiner_expects' => <<<'MD'
Pour toute question sur le choix d'une structure :

- [ ] **Le coût mémoire** de chaque candidate.
- [ ] **Le coût des deux opérations principales** : test d'arête, énumération des voisins.
- [ ] Une **conclusion unique**, rattachée explicitement à une propriété de l'énoncé.
- [ ] **Aucune structure hors du polycopié.**

Une réponse de trois phrases contenant sept complexités vaut plus qu'un paragraphe
de quinze lignes sans un seul chiffre.
MD,
                        'source_refs' => [
                            ['label' => 'AGC-cours.pdf § 1.2 — Représentation informatique'],
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => "Matrice d'adjacence : mémoire, test d'arête, énumération des voisins ?",
                        'back' => "**Mémoire : O(n²)**\n**Test (u,v) ∈ E : O(1)**\n**Voisins de u : O(n)**\n\nParcours complet : O(n²).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => "Listes d'adjacence : mémoire, test d'arête, énumération des voisins ?",
                        'back' => "**Mémoire : O(n + m)**\n**Test (u,v) ∈ E : O(deg(u))**\n**Voisins de u : O(deg(u))** — optimal.\n\nParcours complet : O(n + m).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Graphe creux, opération dominante = parcours. Quelle représentation, et pourquoi ?',
                        'back' => "**Listes d'adjacence.**\n\nMémoire O(n+m) au lieu de O(n²) ; parcours complet O(n+m) au lieu de O(n²). Sur un graphe creux, m = O(n), donc le gain est d'un facteur n.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Graphe dense, tests d'arête intensifs. Quelle représentation ?",
                        'back' => "**Matrice d'adjacence.**\n\nLe test en **O(1)** est déterminant, et sur un graphe dense (m ≈ n²) la mémoire O(n²) n'est plus un handicap face à O(n+m) ≈ O(n²).",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "« Une matrice d'adjacence carrée sous forme de listes de listes » — qu'est-ce qui cloche ?",
                        'back' => "**C'est contradictoire.** Une matrice est un tableau à **accès direct O(1)** ; une liste se parcourt **séquentiellement**.\n\nLe correcteur a écrit en marge : « alors ce n'est plus une matrice ».",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Dans un graphe non orienté, que vaut la somme des degrés ?',
                        'back' => "**Σ deg(v) = 2m**\n\nChaque arête contribue au degré de ses deux extrémités. C'est le lemme des poignées de main, et il sert dans toutes les majorations de complexité.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Question « quelle structure choisir ? » : les quatre éléments obligatoires de la réponse ?",
                        'back' => "1. **Coût mémoire** de chaque candidate.\n2. **Coût du test d'arête**.\n3. **Coût de l'énumération des voisins**.\n4. **Une conclusion unique**, rattachée à une propriété de l'énoncé.\n\nSans chiffre, le correcteur écrit « évaluation ? ».",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => "Exercice 1 de janvier — la question à 7 points",
                        'origin' => 'annale',
                        'est_minutes' => 30,
                        'difficulty' => 3,
                        'statement' => <<<'MD'
On souhaite implémenter un graphe pour un réseau routier : **n = 50 000 carrefours**
et **m ≈ 120 000 tronçons**. Les opérations dominantes sont les **parcours** — calcul
d'itinéraires, recherche de composantes connexes.

**1.** Parmi les représentations vues en cours, laquelle retenez-vous ?
Justifiez en évaluant chaque candidate. *(4 pts)*

**2.** Donnez le coût mémoire réel, en ordre de grandeur, de chacune des deux
principales représentations sur ce jeu de données. *(2 pts)*

**3.** L'application évolue : on doit désormais répondre à un très grand nombre de
requêtes « ces deux carrefours sont-ils directement reliés ? ». Votre choix change-t-il ?
Justifiez. *(1 pt)*
MD,
                        'hint' => "Commencez par calculer la densité : m vaut-il l'ordre de n, ou de n² ? Ce seul chiffre commande toute la réponse.",
                        'method' => <<<'MD'
1. Calculez `n²` et comparez à `m`. Concluez : creux ou dense ?
2. Pour chaque candidate, donnez **trois** chiffres : mémoire, test d'arête, voisins.
3. Rattachez la conclusion à l'opération dominante annoncée dans l'énoncé.
4. À la question 3, l'opération dominante change : refaites le raisonnement,
   ne recopiez pas la conclusion précédente.
MD,
                        'solution' => <<<'MD'
**1.** Le graphe est **très creux** : n² = 2,5 × 10⁹ alors que m ≈ 1,2 × 10⁵,
soit un rapport de plus de 20 000. On a m ≈ 2,4 n, donc m = O(n).

> La **matrice d'adjacence** occupe **O(n²)**, teste une arête en **O(1)**, mais
> énumère les voisins d'un carrefour en **O(n)** — il faut balayer une ligne de
> 50 000 cases pour trouver 2 ou 3 voisins.
>
> Les **listes d'adjacence** occupent **O(n + m)**, testent une arête en **O(deg(u))**
> et énumèrent les voisins en **O(deg(u))**, ce qui est optimal.
>
> L'opération dominante étant le parcours, la matrice le ferait en O(n²) = 2,5 × 10⁹
> opérations, les listes en O(n + m) ≈ 1,7 × 10⁵. **Je retiens les listes d'adjacence.**

**2.**

- **Matrice d'adjacence** : 50 000² = 2,5 × 10⁹ cases. Même à 1 bit par case,
  cela fait **environ 312 Mo** ; à 1 octet, **2,5 Go**. Inexploitable.
- **Listes d'adjacence** : n + 2m = 50 000 + 240 000 = 290 000 entrées
  (chaque arête apparaît deux fois en non orienté). À 8 octets par entrée,
  **environ 2,3 Mo**.

Rapport : plus de **1 000 fois moins de mémoire**.

**3.** Oui, le raisonnement change — mais pas forcément la conclusion.

Le test d'arête passe de O(deg(u)) à O(1) avec une matrice. Mais le degré moyen ici
vaut `2m/n ≈ 4,8` : le test en liste coûte donc **environ 5 comparaisons**, contre 1.
Le gain d'un facteur 5 ne justifie pas de passer de 2,3 Mo à 2,5 Go.

**Je conserve les listes d'adjacence**, quitte à trier chaque liste pour obtenir un
test en O(log deg(u)), ou à ajouter une table de hachage des arêtes pour un test en
O(1) sans le coût mémoire quadratique.
MD,
                        'rubric' => [
                            ['label' => 'La densité est calculée : m comparé à n²', 'points' => 1],
                            ['label' => 'Coût mémoire donné pour les deux candidates', 'points' => 1],
                            ['label' => "Coût du test d'arête donné pour les deux", 'points' => 1],
                            ['label' => "Coût de l'énumération des voisins donné pour les deux", 'points' => 1],
                            ['label' => "Conclusion unique, rattachée à l'opération dominante de l'énoncé", 'points' => 1],
                            ['label' => 'Q2 : ordres de grandeur numériques réels (Mo / Go), pas seulement du O()', 'points' => 2],
                            ['label' => 'Q3 : le degré moyen 2m/n est calculé pour arbitrer', 'points' => 1],
                            ['label' => 'Aucune structure hors du polycopié', 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ============ G2 — Parcours et connexité ============ */
            'G2' => [
                'lessons' => [
                    [
                        'title' => 'Parcours, plus courts chemins, connexité',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Tous les parcours suivent le même squelette. Ce qui change tient, là encore, à une
seule ligne : **la structure qui stocke les sommets à traiter**.

Une **file** → parcours en largeur. Une **pile** → parcours en profondeur.
Une **file à priorité** → Dijkstra.

Savoir cela permet de reconstituer les trois algorithmes sans les avoir appris par cœur,
et surtout d'en donner la complexité sans hésiter — ce qui est la moitié des points.
MD,
                        'formalism' => <<<'MD'
**Le squelette commun**

```
marquer(s) ; ajouter s à STRUCTURE
tant que STRUCTURE non vide :
    u ← extraire(STRUCTURE)
    traiter(u)
    pour chaque voisin v de u non marqué :
        marquer(v) ; père[v] ← u
        ajouter v à STRUCTURE
```

| Structure | Algorithme | Complexité (listes d'adjacence) |
|---|---|---|
| File (FIFO) | **Largeur (BFS)** | **O(n + m)** |
| Pile (LIFO) | **Profondeur (DFS)** | **O(n + m)** |
| File à priorité | **Dijkstra** | **O((n + m) log n)** avec un tas |

**Les résultats à citer nommément**

- **BFS** donne le plus court chemin en **nombre d'arêtes** — donc les plus courts
  chemins d'un graphe **non pondéré**.
- **Dijkstra** donne les plus courts chemins depuis une source, **si tous les poids
  sont positifs ou nuls**. Un poids négatif l'invalide.
- **Bellman-Ford** accepte les poids négatifs, en **O(n · m)**, et détecte les circuits
  absorbants.
- **Composantes connexes** : un DFS ou BFS par sommet non encore marqué, le tout en
  **O(n + m)**.
- **Forte connexité** (graphe orienté) : algorithme de **Kosaraju** — un DFS,
  transposition du graphe, un second DFS dans l'ordre inverse des dates de fin.
  **O(n + m)**.
- **Tri topologique** : possible **si et seulement si** le graphe est un DAG.
  Par DFS en ordre postfixe inversé, en **O(n + m)**.
MD,
                        'worked_example' => <<<'MD'
**Dérouler Dijkstra — la présentation attendue**

Graphe : `A→B (4)`, `A→C (2)`, `C→B (1)`, `B→D (5)`, `C→D (8)`, `D→E (3)`.
Source : A.

| Itération | Extrait | d(A) | d(B) | d(C) | d(D) | d(E) |
|---|---|---|---|---|---|---|
| init | — | 0 | ∞ | ∞ | ∞ | ∞ |
| 1 | **A** (0) | 0 | 4 | 2 | ∞ | ∞ |
| 2 | **C** (2) | 0 | **3** | 2 | 10 | ∞ |
| 3 | **B** (3) | 0 | 3 | 2 | **8** | ∞ |
| 4 | **D** (8) | 0 | 3 | 2 | 8 | 11 |
| 5 | **E** (11) | 0 | 3 | 2 | 8 | 11 |

Les valeurs **en gras** sont les relâchements : à l'itération 2, le chemin A→C→B
coûte 3 et remplace A→B qui coûtait 4.

**Ce qui rapporte les points :**

1. Le **tableau** avec une ligne par itération.
2. Le **sommet extrait** et sa distance, signalés.
3. Les **relâchements** mis en évidence, avec la justification (`2 + 1 = 3 < 4`).
4. La **complexité** annoncée en fin de réponse : O((n + m) log n) avec un tas binaire.
MD,
                        'pitfalls' => <<<'MD'
- **Appliquer Dijkstra avec des poids négatifs.** L'algorithme est faux dans ce cas :
  il faut Bellman-Ford. Si l'énoncé mentionne des poids négatifs, c'est le piège.
- **Donner le résultat sans le tableau.** Comme pour la question 1.1 de janvier,
  un résultat sans déroulement ne prouve rien.
- **Oublier la complexité.** Elle est presque toujours demandée, explicitement ou non.
- **Confondre BFS et Dijkstra.** BFS minimise le **nombre d'arêtes**, Dijkstra la
  **somme des poids**. Sur un graphe non pondéré, ils coïncident.
- **Oublier de préciser la structure de données** dans l'énoncé de la complexité :
  Dijkstra est en O(n²) avec un tableau, O((n+m) log n) avec un tas.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Un **tableau de déroulement**, une ligne par itération.
- [ ] Les **relâchements justifiés** par le calcul (`d(u) + w(u,v) < d(v)`).
- [ ] Le **résultat final** : distances et arbre des plus courts chemins.
- [ ] La **complexité**, avec la structure de données supposée.
- [ ] Pour toute question sur Dijkstra : la mention de la **positivité des poids**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => 'BFS, DFS, Dijkstra : quelle est la seule différence dans le squelette ?',
                        'back' => "**La structure qui stocke les sommets à traiter.**\n\n- File (FIFO) → **BFS**\n- Pile (LIFO) → **DFS**\n- File à priorité → **Dijkstra**\n\nTout le reste est identique.",
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Complexité de BFS et DFS en listes d’adjacence ? En matrice ?',
                        'back' => "**Listes : O(n + m)**\n**Matrice : O(n²)**\n\nC'est l'argument décisif pour les listes sur un graphe creux, et le chiffre que la question 1.1 attendait.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Le graphe a une arête de poids −3. Peut-on appliquer Dijkstra ?",
                        'back' => "**Non.** Dijkstra exige des poids **positifs ou nuls** ; il peut figer une distance trop tôt.\n\nIl faut **Bellman-Ford** : O(n · m), et il détecte les circuits absorbants.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Complexité de Dijkstra ?',
                        'back' => "**O((n + m) log n)** avec un tas binaire.\n**O(n²)** avec un simple tableau.\n\nPréciser la structure supposée fait partie de la réponse.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Comment trouver les composantes fortement connexes, et à quel coût ?',
                        'back' => "**Algorithme de Kosaraju**, en **O(n + m)** :\n1. Un DFS, en notant les dates de fin.\n2. Transposer le graphe.\n3. Un second DFS dans l'ordre décroissant des dates de fin.\n\nChaque arbre du second DFS est une composante fortement connexe.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quand un tri topologique existe-t-il, et comment l’obtenir ?',
                        'back' => "**Si et seulement si le graphe est un DAG** — orienté sans circuit.\n\nPar DFS, en prenant les sommets dans l'**ordre postfixe inversé**. Coût **O(n + m)**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'BFS ou Dijkstra pour un plus court chemin ?',
                        'back' => "**BFS** minimise le **nombre d'arêtes** — donc valable sur un graphe **non pondéré**, en O(n+m).\n\n**Dijkstra** minimise la **somme des poids**, en O((n+m) log n).\n\nSur un graphe non pondéré, BFS suffit et coûte moins cher.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ============ PD — Programmation dynamique ============ */
            'PD' => [
                'lessons' => [
                    [
                        'title' => 'Mémoïsation, sous-structure optimale, tableau',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
La programmation dynamique répond à une situation précise : un problème dont la
solution se construit à partir de solutions de **sous-problèmes qui se répètent**.

L'exemple canonique est Fibonacci. La récursion naïve recalcule `fib(3)` un nombre
exponentiel de fois. Mémoriser les résultats fait tomber le coût de O(2ⁿ) à O(n).

Toute la difficulté à l'examen est de **reconnaître** qu'on est dans ce cas, puis
de poser proprement la **relation de récurrence**. Le code vient après, et il est court.
MD,
                        'formalism' => <<<'MD'
**Les deux conditions d'applicabilité** — à citer nommément :

1. **Sous-structure optimale** — la solution optimale du problème contient les
   solutions optimales de ses sous-problèmes.
2. **Chevauchement des sous-problèmes** — les mêmes sous-problèmes reviennent
   plusieurs fois. Sans cela, une simple récursion (diviser pour régner) suffit.

**Les deux mises en œuvre**

| | Mémoïsation | Tableau |
|---|---|---|
| Sens | descendant (*top-down*) | ascendant (*bottom-up*) |
| Forme | récursion + cache | boucles |
| Calcule | seulement les sous-problèmes utiles | tous les sous-problèmes |
| Coût pile | O(profondeur) | O(1) |

**La méthode en cinq temps** — c'est le plan de toute réponse :

1. **Définir** ce que représente la variable d'état. *« Soit `T[i][j]` le … »*
2. **Poser la récurrence**, avec ses cas de base.
3. **Justifier** la sous-structure optimale.
4. **Donner l'ordre de remplissage** du tableau.
5. **Chiffrer** : complexité en temps et en espace.

Le point 5 est celui que vous omettez. C'est aussi celui qui se compte.
MD,
                        'worked_example' => <<<'MD'
**Le sac à dos 0/1.** n objets de poids `w[i]` et valeur `v[i]`, capacité `W`.
Maximiser la valeur emportée sans dépasser `W`.

**1. Définition.** Soit `T[i][c]` la valeur maximale atteignable en n'utilisant que
les i premiers objets, avec une capacité restante `c`.

**2. Récurrence.**

```
T[0][c] = 0                                              pour tout c        (cas de base)
T[i][c] = T[i-1][c]                                      si w[i] > c
T[i][c] = max( T[i-1][c],  v[i] + T[i-1][c - w[i]] )     sinon
```

Le `max` traduit le choix : on laisse l'objet i, ou on le prend.

**3. Sous-structure optimale.** Si la solution optimale sur i objets prend l'objet i,
alors ce qu'elle emporte des i−1 premiers est optimal pour la capacité `c − w[i]` :
sinon, on pourrait la remplacer par une meilleure et améliorer le total, contradiction.

**4. Ordre de remplissage.** i croissant de 1 à n, c croissant de 0 à W.
Chaque case ne dépend que de la ligne précédente.

**5. Complexité.** **Temps O(n · W)**, **espace O(n · W)**, réductible à **O(W)** en
ne gardant que la ligne précédente.

*Remarque importante à mentionner :* O(n·W) est **pseudo-polynomial**, pas polynomial —
W est une valeur, pas une taille d'entrée. Le problème reste NP-difficile.

**Exemple chiffré.** `W = 5`, objets `(w,v)` = `(2,3), (3,4), (4,5)`.

| i \ c | 0 | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| 1 (2,3) | 0 | 0 | 3 | 3 | 3 | 3 |
| 2 (3,4) | 0 | 0 | 3 | 4 | 4 | **7** |
| 3 (4,5) | 0 | 0 | 3 | 4 | 5 | **7** |

Optimum : **7**, en prenant les objets 1 et 2.
MD,
                        'pitfalls' => <<<'MD'
- **Ne pas définir la variable d'état.** « Soit T[i][j] le … » doit être la première
  phrase. Sans elle, la récurrence est illisible et le correcteur ne peut rien valider.
- **Oublier les cas de base.** Une récurrence sans initialisation ne se calcule pas.
- **Oublier la complexité.** C'est la même faute qu'à l'exercice 1 de janvier :
  « évaluation ? ».
- **Confondre pseudo-polynomial et polynomial.** O(n·W) dépend de la **valeur** W,
  pas de sa taille en bits. Le signaler montre qu'on a compris.
- **Ne pas justifier la sous-structure optimale.** C'est une des deux conditions
  d'applicabilité : l'affirmer sans preuve, c'est la faute « justifier ».
MD,
                        'examiner_expects' => <<<'MD'
- [ ] La **définition** explicite de la variable d'état, en une phrase.
- [ ] La **relation de récurrence** avec tous ses cas, base comprise.
- [ ] La **justification** de la sous-structure optimale.
- [ ] L'**ordre de remplissage** du tableau.
- [ ] La **complexité en temps et en espace**, chiffrée.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => "Les deux conditions d'applicabilité de la programmation dynamique ?",
                        'back' => "1. **Sous-structure optimale** — la solution optimale contient les solutions optimales des sous-problèmes.\n2. **Chevauchement des sous-problèmes** — ils reviennent plusieurs fois.\n\nSans le chevauchement, diviser-pour-régner suffit.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Les cinq temps d’une réponse de programmation dynamique ?',
                        'back' => "1. **Définir** la variable d'état : « Soit T[i][j] le… »\n2. **Poser la récurrence** avec ses cas de base.\n3. **Justifier** la sous-structure optimale.\n4. Donner l'**ordre de remplissage**.\n5. **Chiffrer** temps et espace.\n\nLe 5 est celui qu'on oublie, et il se compte.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Sac à dos 0/1 : la récurrence ?',
                        'back' => "```\nT[0][c] = 0\nT[i][c] = T[i-1][c]                        si w[i] > c\nT[i][c] = max(T[i-1][c],\n              v[i] + T[i-1][c-w[i]])      sinon\n```\n\nTemps **O(n·W)**, espace O(n·W) réductible à **O(W)**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Le sac à dos en O(n·W) : est-ce polynomial ?',
                        'back' => "**Non — pseudo-polynomial.**\n\nW est une **valeur**, pas une taille d'entrée : coder W demande log W bits. Le problème reste NP-difficile.\n\nLe signaler montre qu'on a compris la nuance.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Mémoïsation ou tableau : quelle différence pratique ?',
                        'back' => "**Mémoïsation** — descendante, récursion + cache, ne calcule que les sous-problèmes **utiles**, mais consomme la pile.\n\n**Tableau** — ascendante, boucles, calcule **tout**, pile en O(1).",
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */

    private function mockExam(Subject $subject): void
    {
        $examen = MockExam::updateOrCreate(
            ['slug' => 'agc-blanc-graphes-et-programmation-dynamique'],
            [
                'subject_id' => $subject->id,
                'title' => 'AGC blanc n°1 — graphes et programmation dynamique',
                'instructions' => <<<'MD'
Durée : **3 heures**, comme l'épreuve du 26 août de 15 h à 18 h.
Documents autorisés : aucun.

**Consigne de rédaction, tirée de vos annotations de janvier.** Toute affirmation
sur un choix technique doit être accompagnée d'une **complexité chiffrée**. « Plus
rapide », « plus pratique », « meilleur accès » ne valent aucun point : le correcteur
écrit « évaluation ? » et compte zéro.

Rappel : l'épreuve du 26 août est suivie de SPP à 20 h. Entraînez-vous à finir dans
le temps sans vous épuiser.
MD,
                'duration_min' => 180,
                'total_points' => 20,
                'origin' => 'genere',
                'year' => 2026,
            ]
        );

        $ch = fn (string $code) => Chapter::where('subject_id', $subject->id)
            ->where('code', $code)->value('id');

        $questions = [
            [
                'number' => 'Exercice 1 — Représentation et parcours',
                'chapter_id' => $ch('G1'),
                'points' => 7,
                'statement' => <<<'MD'
Un réseau social compte **n = 2 000 000 d'utilisateurs** et **m ≈ 300 000 000 de
liens d'amitié**. On veut calculer, pour un utilisateur donné, l'ensemble de ses
amis situés à **au plus 3 liens** de lui.

**1.** Quelle représentation du graphe retenez-vous ? Évaluez chaque candidate
sur la mémoire, le test d'arête et l'énumération des voisins, puis concluez. *(3 pts)*

**2.** Quel algorithme de parcours répond à la question posée ? Justifiez et donnez
sa complexité. *(2 pts)*

**3.** Le graphe est-il creux ou dense ? Appuyez votre réponse sur un calcul, et
dites en quoi cela change ou non votre choix de la question 1. *(1 pt)*

**4.** On veut maintenant les composantes connexes du réseau entier. Quel algorithme,
quelle complexité ? *(1 pt)*
MD,
                'solution' => <<<'MD'
**1.** Trois candidates du cours :

> **Matrice d'adjacence** : mémoire **O(n²)**, test d'arête **O(1)**, voisins **O(n)**.
> **Listes d'adjacence** : mémoire **O(n + m)**, test **O(deg(u))**, voisins **O(deg(u))**.
> **Matrice d'incidence** : mémoire **O(n · m)** — hors de question ici.

n² = 4 × 10¹² : une matrice d'adjacence demanderait 4 × 10¹² cases, soit **500 Go
même à 1 bit par case**. Les listes demandent n + 2m = 6,02 × 10⁸ entrées,
soit **environ 4,8 Go** à 8 octets. L'opération dominante est l'énumération des
voisins, optimale en liste. **Je retiens les listes d'adjacence.**

**2.** Un **parcours en largeur (BFS)** depuis l'utilisateur, arrêté au niveau 3.
Le BFS explore les sommets par distance croissante en nombre d'arêtes, ce qui est
exactement le critère demandé. Complexité : **O(n + m)** au pire, mais en pratique
bornée par le nombre de sommets atteints en 3 niveaux.

*(Dijkstra serait inutile : le graphe n'est pas pondéré.)*

**3.** Densité : `m / n² = 3 × 10⁸ / 4 × 10¹² = 7,5 × 10⁻⁵`. Le graphe est **très
creux**. Le degré moyen vaut `2m / n = 300`, contre n = 2 000 000 voisins possibles.
Cela **confirme** le choix de la question 1 : les listes stockent 300 entrées par
sommet au lieu de 2 000 000.

**4.** Un **BFS ou DFS depuis chaque sommet non encore marqué**, en **O(n + m)** au
total : chaque sommet et chaque arête n'est visité qu'une fois, quel que soit le
nombre de composantes.
MD,
                'rubric' => [
                    ['label' => 'Coût mémoire donné pour chaque candidate', 'points' => 1],
                    ['label' => "Coût du test d'arête et de l'énumération des voisins donnés", 'points' => 1],
                    ['label' => "Conclusion unique rattachée à l'opération dominante", 'points' => 1],
                    ['label' => 'Q2 : BFS identifié, et le choix justifié par « distance en nombre d’arêtes »', 'points' => 1],
                    ['label' => 'Q2 : complexité O(n + m) donnée', 'points' => 1],
                    ['label' => 'Q3 : densité calculée numériquement, degré moyen 2m/n donné', 'points' => 1],
                    ['label' => 'Q4 : parcours depuis chaque sommet non marqué, O(n + m)', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 2 — Plus courts chemins',
                'chapter_id' => $ch('G2'),
                'points' => 6,
                'statement' => <<<'MD'
Soit le graphe orienté valué :

```
A→B (6)   A→C (2)   C→B (3)   B→D (1)
C→D (7)   D→E (2)   C→E (12)
```

**1.** Déroulez **Dijkstra** depuis A sous forme de tableau : une ligne par itération,
avec le sommet extrait et les distances courantes. Signalez les relâchements. *(3 pts)*

**2.** Donnez les distances finales et l'arbre des plus courts chemins. *(1 pt)*

**3.** Quelle est la complexité de Dijkstra ? Précisez la structure de données
supposée. *(1 pt)*

**4.** On remplace le poids de l'arc `C→B` par **−3**. Dijkstra reste-t-il applicable ?
Justifiez, et donnez l'algorithme adapté avec sa complexité. *(1 pt)*
MD,
                'solution' => <<<'MD'
**1.**

| Itération | Extrait | d(A) | d(B) | d(C) | d(D) | d(E) |
|---|---|---|---|---|---|---|
| init | — | 0 | ∞ | ∞ | ∞ | ∞ |
| 1 | **A** (0) | 0 | 6 | 2 | ∞ | ∞ |
| 2 | **C** (2) | 0 | **5** | 2 | 9 | 14 |
| 3 | **B** (5) | 0 | 5 | 2 | **6** | 14 |
| 4 | **D** (6) | 0 | 5 | 2 | 6 | **8** |
| 5 | **E** (8) | 0 | 5 | 2 | 6 | 8 |

Relâchements :
- itération 2 : `d(C) + w(C,B) = 2 + 3 = 5 < 6` → d(B) passe de 6 à 5.
- itération 3 : `d(B) + w(B,D) = 5 + 1 = 6 < 9` → d(D) passe de 9 à 6.
- itération 4 : `d(D) + w(D,E) = 6 + 2 = 8 < 14` → d(E) passe de 14 à 8.

**2.** Distances : **d(A)=0, d(B)=5, d(C)=2, d(D)=6, d(E)=8.**

Arbre des plus courts chemins : `A → C → B → D → E`.
Pères : père(C)=A, père(B)=C, père(D)=B, père(E)=D.

**3.** **O((n + m) log n)** avec un **tas binaire** : chaque sommet est extrait une
fois en O(log n), chaque arête provoque au plus une diminution de clé en O(log n).
Avec un simple tableau, on retombe à **O(n²)**.

**4.** **Non, Dijkstra n'est plus applicable.** Il repose sur l'hypothèse que les
poids sont positifs ou nuls : il fige la distance d'un sommet dès qu'il l'extrait,
en supposant qu'aucun chemin ultérieur ne pourra faire mieux. Un arc négatif invalide
ce raisonnement.

Il faut **Bellman-Ford** : n−1 passes de relâchement sur toutes les arêtes,
en **O(n · m)**. Une n-ième passe qui améliore encore une distance signale un
**circuit absorbant**.
MD,
                'rubric' => [
                    ['label' => 'Tableau de déroulement, une ligne par itération', 'points' => 1],
                    ['label' => 'Sommet extrait signalé à chaque itération', 'points' => 1],
                    ['label' => 'Relâchements justifiés par le calcul d(u) + w < d(v)', 'points' => 1],
                    ['label' => 'Distances finales et arbre des plus courts chemins', 'points' => 1],
                    ['label' => 'Complexité O((n+m) log n) avec la structure précisée', 'points' => 1],
                    ['label' => 'Q4 : Dijkstra rejeté avec justification, Bellman-Ford en O(n·m)', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 3 — Programmation dynamique',
                'chapter_id' => $ch('PD'),
                'points' => 7,
                'statement' => <<<'MD'
On dispose d'un escalier de **n marches**. À chaque pas, on peut monter **1, 2 ou 3
marches**. Chaque marche `i` porte un coût `c[i]` que l'on paie en s'y posant.
On part du sol (marche 0, coût nul) et on veut atteindre la marche n au **coût total
minimal**.

**1.** Définissez la variable d'état, puis posez la relation de récurrence avec
ses cas de base. *(3 pts)*

**2.** Justifiez la sous-structure optimale. *(1 pt)*

**3.** Donnez l'ordre de remplissage et la complexité en temps et en espace. *(2 pts)*

**4.** Déroulez le calcul pour `n = 5` et `c = [0, 3, 2, 4, 1, 5]`
(indices 0 à 5, le sol vaut 0). *(1 pt)*
MD,
                'solution' => <<<'MD'
**1. Définition.** Soit `T[i]` le **coût minimal pour atteindre la marche i**
depuis le sol.

**Récurrence.**
```
T[0] = c[0] = 0                                             (cas de base : le sol)
T[1] = c[1]
T[2] = c[2] + min(T[0], T[1])
T[i] = c[i] + min(T[i-1], T[i-2], T[i-3])      pour i ≥ 3
```

Le `min` traduit le choix du dernier pas : on est arrivé depuis la marche i−1,
i−2 ou i−3.

**2. Sous-structure optimale.** Supposons un chemin optimal vers la marche i dont
le dernier pas vient de la marche j (avec j ∈ {i−1, i−2, i−3}). La portion de ce
chemin allant du sol à j est nécessairement optimale : s'il existait un chemin
strictement moins cher jusqu'à j, on le substituerait et le coût total vers i
diminuerait, contredisant l'optimalité supposée.

**3. Ordre de remplissage.** i **croissant** de 0 à n : chaque case ne dépend que
des trois précédentes, déjà calculées.

**Complexité.** **Temps O(n)** — une passe, trois comparaisons par case.
**Espace O(n)** pour le tableau, réductible à **O(1)** puisque seules les trois
dernières valeurs sont utiles.

**4. Déroulement** pour `c = [0, 3, 2, 4, 1, 5]` :

| i | c[i] | candidats | T[i] |
|---|---|---|---|
| 0 | 0 | — | **0** |
| 1 | 3 | T[0]=0 | **3** |
| 2 | 2 | min(T[1], T[0]) = min(3, 0) = 0 | **2** |
| 3 | 4 | min(T[2], T[1], T[0]) = min(2, 3, 0) = 0 | **4** |
| 4 | 1 | min(T[3], T[2], T[1]) = min(4, 2, 3) = 2 | **3** |
| 5 | 5 | min(T[4], T[3], T[2]) = min(3, 4, 2) = 2 | **7** |

**Coût minimal : 7**, par le chemin sol → marche 2 → marche 5.
MD,
                'rubric' => [
                    ['label' => 'La variable d’état est définie explicitement en une phrase', 'points' => 1],
                    ['label' => 'La récurrence porte le min sur les trois marches précédentes', 'points' => 1],
                    ['label' => 'Les cas de base T[0], T[1], T[2] sont donnés', 'points' => 1],
                    ['label' => 'La sous-structure optimale est démontrée par l’absurde', 'points' => 1],
                    ['label' => 'Ordre de remplissage : i croissant, avec la raison', 'points' => 1],
                    ['label' => 'Complexité temps O(n) ET espace O(n) réductible à O(1)', 'points' => 1],
                    ['label' => 'Déroulement numérique correct, coût final 7', 'points' => 1],
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
}