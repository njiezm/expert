<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * AGC et EP — les sept chapitres restés sans fiche.
 *
 * Même exigence que pour ALO et MIA : partir de l'intuition, nommer ensuite.
 * Et pour AGC, la règle tirée des annotations de janvier : toute affirmation
 * s'accompagne d'un chiffre.
 */
class AgcEpSocleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->content() as $code => $chapitres) {
            $subject = Subject::where('code', $code)->first();

            if (! $subject) {
                continue;
            }

            foreach ($chapitres as $chapCode => $data) {
                $chapter = Chapter::where('subject_id', $subject->id)->where('code', $chapCode)->first();

                if (! $chapter) {
                    continue;
                }

                foreach ($data['lessons'] ?? [] as $i => $lesson) {
                    Lesson::updateOrCreate(
                        ['chapter_id' => $chapter->id, 'slug' => Str::slug($lesson['title'])],
                        $lesson + ['position' => $i, 'est_minutes' => $lesson['est_minutes'] ?? 20]
                    );
                }

                foreach ($data['cards'] ?? [] as $i => $card) {
                    Flashcard::updateOrCreate(
                        ['chapter_id' => $chapter->id, 'front' => $card['front']],
                        $card + ['position' => 100 + $i]
                    );
                }

                foreach ($data['exercises'] ?? [] as $i => $exo) {
                    Exercise::updateOrCreate(
                        ['subject_id' => $subject->id, 'title' => $exo['title']],
                        $exo + ['chapter_id' => $chapter->id, 'position' => 100 + $i]
                    );
                }
            }
        }
    }

    /* ==================================================================== */

    private function content(): array
    {
        return [

            /* =========================== AGC =========================== */
            'AGC' => [

                'G3' => [
                    'lessons' => [[
                        'title' => 'Arbres et arbre couvrant de poids minimal',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
Un **arbre**, en théorie des graphes, n'a rien d'un végétal : c'est un graphe
**connexe** — d'un seul tenant — et **sans cycle** — on ne peut pas partir d'un
sommet et y revenir sans repasser par une arête.

Autrement dit : le minimum de liens pour que tout se tienne. Retirez une arête,
le graphe se casse en deux. Ajoutez-en une, vous créez un cycle.

Le problème typique : relier n villes par un réseau de fibre optique, au moindre coût.
On veut un arbre — tout relié, aucun câble superflu — et le **moins cher possible**.
C'est l'**arbre couvrant de poids minimal**.
MD,
                        'formalism' => <<<'MD'
**Caractérisation d'un arbre** — cinq propriétés équivalentes. Un graphe G à n
sommets est un arbre si et seulement si :

1. Il est connexe et sans cycle.
2. Il est connexe et possède exactement **n − 1 arêtes**.
3. Il est sans cycle et possède exactement **n − 1 arêtes**.
4. Entre deux sommets quelconques il existe **exactement un** chemin.
5. Il est sans cycle, et ajouter une arête crée un cycle.

La propriété 2 est la plus utile en pratique : **compter les arêtes**. Un graphe
connexe à 10 sommets et 9 arêtes est un arbre, sans avoir rien à vérifier d'autre.

**Un arbre couvrant** de G est un sous-graphe qui est un arbre et qui contient
**tous** les sommets de G. « Couvrant » veut dire : il n'oublie personne.

**L'arbre couvrant de poids minimal** est celui dont la somme des poids d'arêtes
est la plus petite.

**Deux algorithmes, tous deux gloutons**

**Kruskal** — on trie les arêtes par poids croissant, puis on les prend une à une,
en sautant celles qui créeraient un cycle. On s'arrête à n − 1 arêtes.

```
trier les arêtes par poids croissant
A ← ∅
pour chaque arête (u,v) dans l'ordre :
    si u et v ne sont pas déjà connectés dans A :
        A ← A ∪ {(u,v)}
```

Complexité : **O(m log m)** — dominée par le tri. La détection de cycle se fait
avec une structure union-find, en temps quasi constant.

**Prim** — on part d'un sommet et l'on fait grossir l'arbre, en ajoutant à chaque
étape l'arête la moins chère qui **sort** de l'arbre déjà construit.

```
A ← {un sommet quelconque}
tant que A ne contient pas tous les sommets :
    prendre l'arête de poids minimal ayant une extrémité dans A et l'autre dehors
    l'ajouter à A
```

Complexité : **O((n + m) log n)** avec un tas.

**Pourquoi le glouton fonctionne-t-il ici ?** À cause de la **propriété de coupe** :
si l'on partage les sommets en deux groupes, l'arête de poids minimal qui les
relie appartient à un arbre couvrant minimal. Le choix local est donc sûr — ce qui
n'est pas le cas pour la plupart des problèmes d'optimisation.
MD,
                        'worked_example' => <<<'MD'
**Kruskal, déroulé.**

Sommets A, B, C, D, E. Arêtes :
`AB(4)`, `AC(2)`, `BC(1)`, `BD(5)`, `CD(8)`, `CE(10)`, `DE(3)`.

*Tri par poids croissant :* BC(1), AC(2), DE(3), AB(4), BD(5), CD(8), CE(10).

| Arête | Poids | Créerait un cycle ? | Décision | Arbre |
|---|---|---|---|---|
| BC | 1 | non | **prise** | {BC} |
| AC | 2 | non | **prise** | {BC, AC} |
| DE | 3 | non | **prise** | {BC, AC, DE} |
| AB | 4 | **oui** — A et B déjà reliés par AC + CB | rejetée | — |
| BD | 5 | non | **prise** | {BC, AC, DE, BD} |

Quatre arêtes pour cinq sommets : **n − 1 = 4**, l'arbre est complet. On s'arrête.

**Poids total : 1 + 2 + 3 + 5 = 11.**

**La présentation qui rapporte les points :** le tableau ci-dessus, avec la colonne
« créerait un cycle ? » **justifiée** — « A et B sont déjà reliés par AC + CB » — et
la complexité annoncée en conclusion : **O(m log m)**.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier de vérifier le cycle.** C'est la seule difficulté de Kruskal, et l'omettre
  donne un graphe qui n'est plus un arbre.
- **S'arrêter au mauvais moment.** L'arbre est complet à **n − 1** arêtes, pas quand
  la liste est épuisée.
- **Confondre arbre couvrant minimal et plus courts chemins.** L'arbre minimise la
  **somme totale** des arêtes ; Dijkstra minimise la **distance depuis une source**.
  Ce ne sont pas les mêmes arbres.
- **Oublier la complexité.** C'est l'annotation « évaluation ? » de janvier.
- **Affirmer que le glouton est optimal sans citer la propriété de coupe.**
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Un **tableau de déroulement**, une ligne par arête examinée.
- [ ] Le **rejet des arêtes** justifié par le cycle qu'elles créeraient.
- [ ] Le **poids total** de l'arbre obtenu.
- [ ] La **complexité** : O(m log m) pour Kruskal, O((n+m) log n) pour Prim.
- [ ] Sur une question d'optimalité : la **propriété de coupe** citée.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'definition',
                            'front' => "Qu'est-ce qu'un arbre, et combien d'arêtes a-t-il ?",
                            'back' => "**Un graphe connexe et sans cycle.**\n\nÀ n sommets, il a exactement **n − 1 arêtes**. C'est le critère le plus rapide à vérifier : connexe + n−1 arêtes ⟹ arbre.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'formule',
                            'front' => 'Complexité de Kruskal ? De Prim ?',
                            'back' => "**Kruskal : O(m log m)** — dominée par le tri des arêtes.\n**Prim : O((n + m) log n)** avec un tas.\n\nSur un graphe creux, Kruskal est souvent préférable ; sur un graphe dense, Prim.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'Arbre couvrant minimal et plus courts chemins : même arbre ?',
                            'back' => "**Non.** L'arbre couvrant minimise la **somme totale** des arêtes.\n\nDijkstra minimise la **distance depuis une source**. Les deux arbres diffèrent en général.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Pourquoi le glouton est-il optimal pour l’arbre couvrant minimal ?',
                            'back' => "**Par la propriété de coupe** : si l'on partage les sommets en deux groupes, l'arête de poids minimal qui les relie appartient à un arbre couvrant minimal.\n\nLe choix local est donc sûr — ce qui est rare.",
                            'difficulty' => 5,
                        ],
                    ],
                    'exercises' => [[
                        'title' => 'Kruskal et Prim sur le même graphe',
                        'origin' => 'genere',
                        'est_minutes' => 30,
                        'difficulty' => 3,
                        'statement' => <<<'MD'
Graphe non orienté valué, sommets A à F :

`AB(7)`, `AC(3)`, `BC(1)`, `BD(4)`, `CD(6)`, `CE(2)`, `DE(5)`, `DF(8)`, `EF(9)`

**1.** Déroulez **Kruskal** en tableau : arête, poids, cycle ou non, décision. *(4 pts)*
**2.** Donnez l'arbre obtenu et son **poids total**. *(1 pt)*
**3.** Déroulez **Prim** en partant de A. Obtenez-vous le même arbre ? *(3 pts)*
**4.** Donnez la **complexité** des deux algorithmes, avec la structure supposée. *(2 pts)*
**5.** Combien d'arêtes comporte nécessairement l'arbre ? Justifiez sans le construire. *(1 pt)*
MD,
                        'hint' => "Question 5 : la réponse ne dépend que du nombre de sommets. Question 3 : Prim ajoute à chaque étape l'arête la moins chère qui **sort** de l'arbre déjà bâti.",
                        'method' => <<<'MD'
1. Triez d'abord les neuf arêtes par poids croissant, puis parcourez.
2. Pour chaque arête, demandez-vous si ses deux extrémités sont **déjà reliées**
   par les arêtes déjà prises. Si oui, elle crée un cycle : on la rejette.
3. Arrêtez-vous à n − 1 arêtes.
4. Pour Prim, tenez à jour l'ensemble des sommets atteints.
MD,
                        'solution' => <<<'MD'
**1. Kruskal**

Tri : BC(1), CE(2), AC(3), BD(4), DE(5), CD(6), AB(7), DF(8), EF(9).

| Arête | Poids | Cycle ? | Décision | Arbre courant |
|---|---|---|---|---|
| BC | 1 | non | **prise** | {BC} |
| CE | 2 | non | **prise** | {BC, CE} |
| AC | 3 | non | **prise** | {BC, CE, AC} |
| BD | 4 | non | **prise** | {BC, CE, AC, BD} |
| DE | 5 | **oui** — D et E reliés par DB+BC+CE | rejetée | — |
| CD | 6 | **oui** — C et D reliés par CB+BD | rejetée | — |
| AB | 7 | **oui** — A et B reliés par AC+CB | rejetée | — |
| DF | 8 | non | **prise** | {BC, CE, AC, BD, DF} |

Cinq arêtes pour six sommets : n − 1 = 5. **On s'arrête.**

**2.** Arbre : **BC, CE, AC, BD, DF**. Poids total : 1 + 2 + 3 + 4 + 8 = **18**.

**3. Prim depuis A**

| Étape | Sommets atteints | Arêtes sortantes candidates | Choisie |
|---|---|---|---|
| 1 | {A} | AB(7), AC(3) | **AC(3)** |
| 2 | {A,C} | AB(7), BC(1), CD(6), CE(2) | **BC(1)** |
| 3 | {A,C,B} | AB✗, BD(4), CD(6), CE(2) | **CE(2)** |
| 4 | {A,C,B,E} | BD(4), CD(6), DE(5), EF(9) | **BD(4)** |
| 5 | {A,C,B,E,D} | CD✗, DE✗, DF(8), EF(9) | **DF(8)** |

Arbre : **AC, BC, CE, BD, DF**. Poids total : 3 + 1 + 2 + 4 + 8 = **18**.

**C'est le même arbre**, obtenu dans un ordre différent. Ce n'est pas un hasard :
quand tous les poids sont **distincts**, l'arbre couvrant minimal est **unique**.
Avec des poids répétés, plusieurs arbres de même poids peuvent exister.

**4. Complexités**

- **Kruskal : O(m log m)** — le tri des m arêtes domine. La détection de cycle par
  union-find coûte O(α(n)) par test, quasi constant.
- **Prim : O((n + m) log n)** avec un tas binaire ; **O(n²)** avec un simple tableau.

**5.** **Cinq arêtes**, sans rien construire.

Un arbre à n sommets a exactement **n − 1** arêtes. Ici n = 6, donc 5. C'est vrai
de tout arbre couvrant de ce graphe, quel que soit son poids.
MD,
                        'rubric' => [
                            ['label' => 'Kruskal : arêtes triées par poids croissant', 'points' => 1],
                            ['label' => 'Kruskal : tableau complet, une ligne par arête examinée', 'points' => 2],
                            ['label' => 'Kruskal : les rejets sont justifiés par le cycle créé', 'points' => 1],
                            ['label' => 'Poids total 18', 'points' => 1],
                            ['label' => 'Prim : déroulé avec les sommets atteints à chaque étape', 'points' => 2],
                            ['label' => 'Le même arbre est obtenu, et l’unicité est justifiée', 'points' => 1],
                            ['label' => 'Les deux complexités, avec la structure supposée', 'points' => 2],
                            ['label' => 'n − 1 = 5 arêtes, justifié sans construction', 'points' => 1],
                        ],
                    ]],
                ],

                'PL' => [
                    'lessons' => [[
                        'title' => 'Programmation linéaire et simplexe',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
Un atelier fabrique deux produits. Chacun consomme du bois, des heures de travail,
et rapporte une marge. Les ressources sont limitées. **Combien fabriquer de chaque
produit pour gagner le plus ?**

C'est un **problème linéaire** : tout — la fonction à maximiser comme les contraintes —
s'écrit avec des additions et des multiplications par des constantes. Pas de carré,
pas de produit de deux inconnues.

Cette restriction paraît sévère, mais elle donne une propriété remarquable : la
solution optimale se trouve toujours à un **sommet** du domaine. On n'a donc pas à
explorer l'infini des possibilités — seulement les coins.
MD,
                        'formalism' => <<<'MD'
**La forme générale**

```
Maximiser    z = c₁x₁ + c₂x₂ + … + cₙxₙ          ← la fonction objectif
sous         a₁₁x₁ + … + a₁ₙxₙ ≤ b₁              ← les contraintes
             a₂₁x₁ + … + a₂ₙxₙ ≤ b₂
                        …
             x₁, …, xₙ ≥ 0                        ← positivité
```

**Le vocabulaire :**

- Une solution **réalisable** respecte toutes les contraintes.
- Le **domaine réalisable** est l'ensemble de ces solutions. C'est un **polyèdre
  convexe** — en dimension 2, un polygone.
- Une solution **optimale** est une solution réalisable qui maximise z.

**Le théorème fondamental.** Si l'optimum existe, il est atteint en au moins un
**sommet** du domaine réalisable.

C'est ce qui rend le problème calculable : au lieu d'examiner une infinité de points,
on visite les sommets, en nombre fini.

**La méthode graphique** — praticable à deux variables seulement :

1. Tracer chaque contrainte comme une droite.
2. Hachurer le demi-plan qu'elle autorise.
3. Le domaine réalisable est l'intersection.
4. Calculer z en chaque sommet.
5. Retenir le meilleur.

**Le simplexe** — la méthode générale. On part d'un sommet, et l'on se déplace de
sommet en sommet **en améliorant z à chaque pas**, jusqu'à ce qu'aucun voisin ne
fasse mieux.

Sa mise en œuvre demande d'abord de passer en **forme standard**, en transformant
les inégalités en égalités par des **variables d'écart** :

```
3x + 2y ≤ 12      devient      3x + 2y + e₁ = 12,  e₁ ≥ 0
```

La variable d'écart `e₁` représente la ressource **non utilisée**. Si elle vaut 0 à
l'optimum, la contrainte est **saturée** — la ressource est entièrement consommée.
MD,
                        'worked_example' => <<<'MD'
**Un atelier de meubles.**

Il fabrique des **tables** et des **chaises**.

- Une table demande 3 h de menuiserie et 1 h de finition, marge **40 €**.
- Une chaise demande 1 h de menuiserie et 2 h de finition, marge **30 €**.
- On dispose de **12 h** de menuiserie et **8 h** de finition par jour.

**Mise en équation.** Soit `x` le nombre de tables et `y` celui de chaises.

```
Maximiser    z = 40x + 30y
sous         3x +  y ≤ 12          (menuiserie)
              x + 2y ≤  8          (finition)
              x, y ≥ 0
```

**Résolution graphique.**

Les deux droites se coupent où `3x + y = 12` et `x + 2y = 8`.
De la première : `y = 12 − 3x`. En substituant : `x + 24 − 6x = 8`,
donc `−5x = −16`, soit **x = 3,2** et **y = 2,4**.

Les sommets du domaine :

| Sommet | (x, y) | z = 40x + 30y |
|---|---|---|
| origine | (0, 0) | 0 |
| axe des x | (4, 0) | 160 |
| intersection | (3,2 ; 2,4) | 128 + 72 = **200** |
| axe des y | (0, 4) | 120 |

**Optimum : 200 € par jour**, avec 3,2 tables et 2,4 chaises.

**L'interprétation, qui vaut des points.** À l'optimum, les deux contraintes sont
**saturées** : `3(3,2) + 2,4 = 12` et `3,2 + 2(2,4) = 8`. Les deux variables d'écart
valent zéro. Autrement dit, **aucune heure n'est perdue** — ni en menuiserie ni en
finition. C'est le signe d'un dimensionnement équilibré.

*Remarque à formuler si l'énoncé le demande :* les quantités sont fractionnaires.
Si l'on exige des entiers, c'est de la **programmation linéaire en nombres entiers**,
un problème bien plus difficile — et l'on ne peut pas se contenter d'arrondir.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier les contraintes de positivité** `x, y ≥ 0`. Elles font partie du système
  et bornent le domaine.
- **Chercher l'optimum à l'intérieur du domaine.** Il est toujours sur un **sommet**.
- **Arrondir une solution fractionnaire.** L'arrondi n'est en général **pas** la
  solution entière optimale. Il faut le signaler.
- **Confondre contrainte saturée et non saturée.** Écart nul = ressource entièrement
  consommée.
- **Oublier d'interpréter.** Un nombre sans phrase ne rapporte que la moitié des points.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] La **mise en équation** complète : objectif, contraintes, positivité.
- [ ] Le **domaine** tracé ou ses sommets calculés.
- [ ] La valeur de z **en chaque sommet**, en tableau.
- [ ] L'optimum **interprété** : que signifient ces nombres pour l'atelier ?
- [ ] Les contraintes **saturées** identifiées.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'definition',
                            'front' => 'Où se trouve l’optimum d’un programme linéaire ?',
                            'back' => "**Toujours sur un sommet** du domaine réalisable.\n\nC'est le théorème fondamental : il rend le problème calculable, puisqu'il suffit de visiter les coins au lieu d'une infinité de points.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'À quoi sert une variable d’écart ?',
                            'back' => "**À transformer une inégalité en égalité** pour passer en forme standard.\n\n`3x + 2y ≤ 12` devient `3x + 2y + e₁ = 12` avec `e₁ ≥ 0`.\n\n`e₁` représente la **ressource non utilisée** : si elle vaut 0, la contrainte est **saturée**.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'La solution optimale vaut x = 3,2 tables. Peut-on arrondir à 3 ?',
                            'back' => "**Non, pas sans le justifier.** L'arrondi n'est en général **pas** la solution entière optimale.\n\nExiger des entiers change de problème : c'est la **programmation linéaire en nombres entiers**, bien plus difficile.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'methode',
                            'front' => 'Comment fonctionne le simplexe, en une phrase ?',
                            'back' => "**On part d'un sommet et l'on se déplace de sommet en sommet en améliorant z à chaque pas**, jusqu'à ce qu'aucun voisin ne fasse mieux.\n\nIl faut d'abord passer en forme standard avec des variables d'écart.",
                            'difficulty' => 4,
                        ],
                    ],
                ],

                'PG' => [
                    'lessons' => [[
                        'title' => 'Programmation gloutonne — et quand elle échoue',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
Un algorithme **glouton** prend, à chaque étape, ce qui paraît le meilleur sur
le moment — sans jamais revenir en arrière.

C'est rapide et simple. Le problème, c'est que ça ne marche pas toujours.

Sur l'exercice 2 de janvier, vous avez proposé une approche gloutonne pour la plus
longue sous-séquence commune. Le correcteur a écrit : **« → pas Glouton. »**

Ce chapitre sert donc à deux choses : savoir appliquer un glouton, et surtout
**savoir quand il est légitime**.
MD,
                        'formalism' => <<<'MD'
**Les deux conditions d'un glouton correct**

1. **Propriété du choix glouton** — le choix localement optimal fait partie d'une
   solution globalement optimale.
2. **Sous-structure optimale** — après ce choix, le sous-problème restant se traite
   de la même façon.

La première est la difficile. Elle se **démontre**, souvent par échange : on montre
qu'une solution optimale qui ne ferait pas le choix glouton peut être transformée en
une autre, aussi bonne, qui le fait.

**Les problèmes gloutons du cours**

| Problème | Critère glouton | Optimal ? |
|---|---|---|
| **Arbre couvrant minimal** (Kruskal, Prim) | l'arête la moins chère | **oui**, par la propriété de coupe |
| **Choix d'activités** | la date de **fin** la plus tôt | **oui** |
| **Plus courts chemins**, poids ≥ 0 (Dijkstra) | le sommet le plus proche | **oui** |
| **Coloration de graphes** | la plus petite couleur libre | **non**, approché |
| **Rendu de monnaie**, système européen | la plus grosse pièce | **oui**, système canonique |
| **Rendu de monnaie**, système {1,3,4} | la plus grosse pièce | **non** |

**Le contre-exemple à connaître par cœur.** Système {1, 3, 4}, rendre 6 centimes.

- Glouton : 4, puis 1, puis 1 → **3 pièces**.
- Optimum : 3 + 3 → **2 pièces**.

Ce seul exemple suffit à justifier le rejet du glouton en examen.

**La coloration de graphes** — le glouton y est **approché**, pas optimal :

```
trier les sommets (par degré décroissant, par exemple)
pour chaque sommet v :
    lui donner la plus petite couleur non utilisée par ses voisins
```

Il utilise au plus **Δ + 1** couleurs, où Δ est le degré maximal. Le **nombre
chromatique** χ(G) — le minimum réel — peut être plus petit, et le calculer
exactement est NP-difficile.

**Le choix d'activités** — le glouton y est optimal, à condition de trier par la
**bonne clé** :

```
trier les activités par date de FIN croissante
sélectionner la première
pour chaque activité suivante :
    si elle commence après la fin de la dernière retenue, la prendre
```

Trier par **durée** ou par **date de début** ne donne pas l'optimum. Seule la date
de fin fonctionne — parce qu'elle libère la ressource au plus tôt.
MD,
                        'worked_example' => <<<'MD'
**Le choix d'activités, déroulé.**

Onze activités, avec leurs créneaux :

| Act. | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 |
|---|---|---|---|---|---|---|---|---|---|---|---|
| début | 1 | 3 | 0 | 5 | 3 | 5 | 6 | 8 | 8 | 2 | 12 |
| fin | 4 | 5 | 6 | 7 | 9 | 9 | 10 | 11 | 12 | 14 | 16 |

Elles sont déjà triées par date de fin croissante.

| Étape | Activité | Début | Fin de la dernière retenue | Décision |
|---|---|---|---|---|
| 1 | 1 | 1 | — | **prise** (fin 4) |
| 2 | 2 | 3 | 4 | rejetée, 3 < 4 |
| 3 | 3 | 0 | 4 | rejetée |
| 4 | **4** | 5 | 4 | **prise** (fin 7) |
| 5 | 5 | 3 | 7 | rejetée |
| 6 | 6 | 5 | 7 | rejetée |
| 7 | 7 | 6 | 7 | rejetée |
| 8 | **8** | 8 | 7 | **prise** (fin 11) |
| 9 | 9 | 8 | 11 | rejetée |
| 10 | 10 | 2 | 11 | rejetée |
| 11 | **11** | 12 | 11 | **prise** (fin 16) |

**Solution : {1, 4, 8, 11}, soit 4 activités.** C'est l'optimum.

**Complexité : O(n log n)** — dominée par le tri. Le parcours ensuite est en O(n).

**Pourquoi la date de fin ?** Parce qu'elle libère la ressource au plus tôt, laissant
le maximum de place pour la suite. Une activité qui finit tôt ne peut jamais nuire :
on peut toujours échanger n'importe quelle solution optimale contre une qui commence
par elle. C'est l'argument d'échange, et c'est lui qu'on attend en justification.
MD,
                        'pitfalls' => <<<'MD'
- **Appliquer un glouton sans vérifier qu'il est légitime.** Cherchez d'abord un
  contre-exemple. Si vous n'en trouvez pas en une minute, démontrez brièvement la
  propriété du choix glouton.
- **Trier par la mauvaise clé** au choix d'activités. Ni la durée, ni la date de
  début : la date de **fin**.
- **Croire que le rendu de monnaie est toujours glouton.** Il dépend du système de
  pièces. {1, 3, 4} le met en défaut.
- **Confondre glouton et programmation dynamique.** Le glouton ne revient jamais en
  arrière ; la programmation dynamique mémorise et compare.
- **Affirmer l'optimalité de la coloration gloutonne.** Elle est **approchée**, en
  Δ + 1 couleurs au plus.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Le **critère glouton** énoncé explicitement — sur quelle clé on trie.
- [ ] Le **déroulé en tableau**, avec les rejets justifiés.
- [ ] La **complexité**, dominée par le tri.
- [ ] Une **justification de l'optimalité** — ou, si le glouton échoue, un
      **contre-exemple chiffré**.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'piege',
                            'front' => 'Système de pièces {1, 3, 4}, rendre 6. Le glouton est-il optimal ?',
                            'back' => "**Non.**\n\nGlouton : 4 + 1 + 1 = **3 pièces**.\nOptimum : 3 + 3 = **2 pièces**.\n\nCe contre-exemple suffit à rejeter le glouton en examen.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'methode',
                            'front' => 'Choix d’activités : sur quelle clé faut-il trier ?',
                            'back' => "**Sur la date de FIN croissante.**\n\nNi la durée, ni la date de début. La date de fin libère la ressource au plus tôt, ce qui laisse le maximum de place pour la suite.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Les deux conditions d’un glouton correct ?',
                            'back' => "1. **Propriété du choix glouton** — le choix localement optimal fait partie d'une solution globalement optimale.\n2. **Sous-structure optimale** — le sous-problème restant se traite de la même façon.\n\nLa première se démontre, souvent par argument d'échange.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'La coloration gloutonne de graphes est-elle optimale ?',
                            'back' => "**Non, elle est approchée.** Elle utilise au plus **Δ + 1** couleurs, où Δ est le degré maximal.\n\nLe nombre chromatique χ(G) réel peut être plus petit, et le calculer est **NP-difficile**.",
                            'difficulty' => 4,
                        ],
                    ],
                ],

                'CY' => [
                    'lessons' => [[
                        'title' => 'Cycles eulériens, hamiltoniens, et voyageur de commerce',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Deux questions qui se ressemblent, et dont les réponses ne pourraient pas être
plus différentes.

**Peut-on parcourir toutes les *arêtes* exactement une fois ?** C'est le problème
des ponts de Königsberg, résolu par Euler en 1736. La réponse tient en une ligne
et se vérifie en O(n + m).

**Peut-on passer par tous les *sommets* exactement une fois ?** C'est le cycle
hamiltonien. Il est **NP-complet** : personne ne connaît d'algorithme efficace.

Un mot change — arête, sommet — et l'on passe du trivial à l'intraitable.
C'est l'une des plus jolies leçons du module, et elle tombe.
MD,
                        'formalism' => <<<'MD'
**Cycle eulérien — parcourir toutes les arêtes**

> **Théorème d'Euler.** Un graphe connexe admet un **cycle** eulérien si et seulement
> si **tous ses sommets sont de degré pair**.
>
> Il admet un **chemin** eulérien (départ et arrivée différents) si et seulement si
> **exactement zéro ou deux sommets sont de degré impair**.

La vérification coûte **O(n + m)** : on compte les degrés. La construction du cycle,
par l'algorithme de Hierholzer, coûte également O(n + m).

**Pourquoi la parité ?** Chaque passage par un sommet consomme deux arêtes — une pour
entrer, une pour sortir. Si le degré est impair, il reste une arête orpheline : on
entre sans pouvoir ressortir.

**Le problème du postier chinois.** Si le graphe a des sommets de degré impair, on
ne peut pas tout parcourir sans répéter. Le postier chinois cherche alors le
parcours de **longueur minimale** passant par toutes les arêtes, en autorisant les
répétitions. On apparie les sommets impairs par des plus courts chemins.

**Cycle hamiltonien — passer par tous les sommets**

Aucune caractérisation simple n'existe. Le problème est **NP-complet**.

Seules des **conditions suffisantes** sont connues :

- **Théorème de Dirac.** Si n ≥ 3 et que tout sommet a un degré ≥ n/2, alors le
  graphe est hamiltonien.
- **Théorème d'Ore.** Si pour toute paire de sommets non adjacents u, v on a
  deg(u) + deg(v) ≥ n, alors le graphe est hamiltonien.

Attention : ce sont des conditions **suffisantes**, pas nécessaires. Un graphe peut
être hamiltonien sans les vérifier.

**Le voyageur de commerce (TSP)**

Trouver le cycle hamiltonien de **poids minimal**. **NP-difficile.**

| Méthode | Coût | Garantie |
|---|---|---|
| Force brute | O(n!) | optimum |
| Programmation dynamique (Held-Karp) | O(n²·2ⁿ) | optimum |
| **Algorithme d'approximation par ACM** | O(m log m) | **≤ 2× l'optimum** |
| Christofides | O(n³) | ≤ 1,5× l'optimum |

**L'approximation par arbre couvrant minimal**, qui tombe régulièrement :

1. Construire un **arbre couvrant de poids minimal** (Kruskal).
2. Le parcourir en **profondeur**, en notant l'ordre de première visite.
3. Ce parcours est le cycle proposé.

**Garantie : au plus 2 fois l'optimum**, à condition que l'**inégalité triangulaire**
soit vérifiée — c'est-à-dire que passer par un intermédiaire ne raccourcisse jamais.

*La démonstration, en deux lignes :* le poids de l'ACM est inférieur à celui du cycle
optimal, puisque retirer une arête d'un cycle hamiltonien donne un arbre couvrant.
Et le parcours en profondeur emprunte chaque arête de l'arbre au plus deux fois.
D'où le facteur 2.
MD,
                        'worked_example' => <<<'MD'
**Le graphe est-il eulérien ?**

Sommets A, B, C, D. Arêtes : AB, AC, AD, BC, BD, CD — le graphe complet K₄.

Degrés : chaque sommet est relié aux trois autres, donc **deg = 3** partout.
Quatre sommets de degré **impair**.

- Cycle eulérien ? **Non** — il en faudrait zéro d'impair.
- Chemin eulérien ? **Non** — il en faudrait au plus deux.

Retirons l'arête CD. Degrés : A = 3, B = 3, C = 2, D = 2.
**Deux sommets impairs** (A et B) → **chemin eulérien**, de A à B. Pas de cycle.

**Approximation du voyageur de commerce.**

Cinq villes, distances :

| | A | B | C | D | E |
|---|---|---|---|---|---|
| **A** | — | 2 | 9 | 10 | 7 |
| **B** | 2 | — | 6 | 4 | 3 |
| **C** | 9 | 6 | — | 8 | 5 |
| **D** | 10 | 4 | 8 | — | 6 |
| **E** | 7 | 3 | 5 | 6 | — |

*Étape 1 — l'arbre couvrant minimal par Kruskal.*

Tri : AB(2), BE(3), BD(4), CE(5), BC(6), DE(6), AE(7), CD(8), AC(9), AD(10).

| Arête | Cycle ? | Décision |
|---|---|---|
| AB(2) | non | **prise** |
| BE(3) | non | **prise** |
| BD(4) | non | **prise** |
| CE(5) | non | **prise** |

Quatre arêtes pour cinq sommets : l'arbre est complet.
**ACM = {AB, BE, BD, CE}**, poids 2 + 3 + 4 + 5 = **14**.

*Étape 2 — parcours en profondeur depuis A.*

L'arbre, vu depuis A : A — B ; B — E, D ; E — C.

Ordre de première visite : **A, B, E, C, D**.

*Étape 3 — le cycle.* A → B → E → C → D → A.

Coût : 2 + 3 + 5 + 8 + 10 = **28**.

*La garantie.* Le poids de l'ACM vaut 14, donc l'optimum est **au moins 14**.
Notre solution vaut 28, soit **exactement 2 × 14** — la borne du théorème.
L'optimum réel est entre 14 et 28.

**Ce qu'il faut écrire en conclusion :** « Cette solution est au plus deux fois
l'optimum, sous réserve de l'inégalité triangulaire. »
MD,
                        'pitfalls' => <<<'MD'
- **Confondre eulérien et hamiltonien.** Eulérien = toutes les **arêtes**, vérifiable
  en O(n+m). Hamiltonien = tous les **sommets**, NP-complet. C'est la question piège
  du chapitre.
- **Oublier la connexité** dans le théorème d'Euler. Les degrés pairs ne suffisent
  pas si le graphe est en morceaux.
- **Confondre cycle et chemin eulérien.** Cycle : **zéro** sommet impair.
  Chemin : **exactement deux**.
- **Présenter Dirac ou Ore comme des conditions nécessaires.** Elles sont seulement
  **suffisantes**.
- **Annoncer la garantie de 2 sans l'inégalité triangulaire.** Sans elle, aucune
  garantie.
- **Oublier de conclure sur le facteur d'approximation.** C'est le point de la question.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Le **théorème d'Euler** énoncé avec la **connexité** et la distinction
      cycle / chemin.
- [ ] Les **degrés comptés** explicitement avant de conclure.
- [ ] Pour le hamiltonien : la mention **NP-complet**, et Dirac ou Ore présentés
      comme **suffisants seulement**.
- [ ] Pour l'approximation : les **trois étapes**, le **facteur 2** et l'**inégalité
      triangulaire**.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'formule',
                            'front' => 'Théorème d’Euler : quand un graphe admet-il un cycle eulérien ?',
                            'back' => "**Si et seulement s'il est connexe et que tous ses sommets sont de degré pair.**\n\nPour un **chemin** eulérien : connexe, avec **exactement zéro ou deux** sommets de degré impair.\n\nVérification en O(n + m).",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'Eulérien ou hamiltonien : quelle différence, et quelle complexité ?',
                            'back' => "**Eulérien** — toutes les **arêtes** une fois. Caractérisation simple, vérifiable en **O(n+m)**.\n\n**Hamiltonien** — tous les **sommets** une fois. **NP-complet**, aucune caractérisation simple.\n\nUn mot change, et l'on passe du trivial à l'intraitable.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'methode',
                            'front' => 'Approximation du voyageur de commerce par ACM : les trois étapes ?',
                            'back' => "1. Construire un **arbre couvrant minimal**.\n2. Le parcourir en **profondeur**, noter l'ordre de première visite.\n3. Ce parcours est le cycle.\n\n**Garantie : ≤ 2 × l'optimum**, si l'**inégalité triangulaire** est vérifiée.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Théorème de Dirac ?',
                            'back' => "**Si n ≥ 3 et que tout sommet a un degré ≥ n/2, le graphe est hamiltonien.**\n\nCondition **suffisante seulement** : un graphe peut être hamiltonien sans la vérifier.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'En quoi consiste le problème du postier chinois ?',
                            'back' => "**Parcourir toutes les arêtes au moindre coût, en autorisant les répétitions.**\n\nUtile quand le graphe n'est pas eulérien : on apparie les sommets de degré impair par des plus courts chemins.",
                            'difficulty' => 4,
                        ],
                    ],
                ],
            ],

            /* =========================== EP =========================== */
            'EP' => [

                'C2' => [
                    'lessons' => [[
                        'title' => 'Problème, algorithme, calculabilité',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
Avant de demander si un problème est **difficile**, il faut demander s'il est
**soluble**. Le module commence par là, et la réponse surprend : certains problèmes
parfaitement bien posés n'admettent **aucun** algorithme. Jamais. Quelle que soit
la puissance de calcul.

Trois questions structurent le chapitre :

1. Qu'est-ce qu'un problème, précisément ?
2. Existe-t-il un algorithme pour chacun ?
3. Ceux qui en ont un sont-ils tous utilisables ?

Les réponses sont : *une question paramétrée*, *non*, et *non plus*.
MD,
                        'formalism' => <<<'MD'
**Un problème** est une question paramétrée. On distingue :

- l'**instance** — un jeu de données concret ;
- la **question** posée sur cette instance.

*Exemple.* Problème : « le graphe G est-il connexe ? »
Instance : un graphe précis. Question : oui ou non.

**Trois formes de problème :**

| Forme | Réponse attendue |
|---|---|
| **Décision** | oui ou non |
| **Recherche** | un objet vérifiant une propriété |
| **Optimisation** | le meilleur objet selon un critère |

La théorie de la calculabilité travaille sur les problèmes de **décision** — c'est
le cadre le plus simple, et les autres s'y ramènent.

**Un algorithme** est une suite finie d'instructions telle que :

1. Chaque instruction est **élémentaire** et **non ambiguë**.
2. L'exécution **termine** après un nombre fini d'étapes.
3. Elle produit le **résultat attendu** pour toute instance valide.

La condition 2 est celle qu'on oublie. Une procédure qui boucle sur certaines
entrées n'est pas un algorithme.

**Les deux mauvaises nouvelles**

*Tous les problèmes n'ont pas d'algorithme.* Un argument de dénombrement le montre
sans rien construire : il y a une **infinité non dénombrable** de problèmes de
décision — autant que de parties de ℕ — mais seulement une **infinité dénombrable**
de programmes, puisqu'un programme est un texte fini sur un alphabet fini.
Il y a donc strictement plus de problèmes que de programmes.

Le problème de l'**arrêt** en est l'exemple concret, démontré au chapitre 5.

*Tous les algorithmes ne sont pas utilisables.* Un algorithme en O(2ⁿ) sur n = 100
demanderait plus d'opérations qu'il n'y a d'atomes dans l'univers observable.
Il existe, il est correct, et il est inutilisable. D'où le second volet du module :
la **complexité**.
MD,
                        'worked_example' => <<<'MD'
**Le même problème, sous ses trois formes.**

*Décision.* « Le graphe G admet-il un chemin de longueur ≤ k entre u et v ? »
→ réponse : oui ou non.

*Recherche.* « Donner un chemin de longueur ≤ k entre u et v. »
→ réponse : la liste des sommets.

*Optimisation.* « Donner le plus court chemin entre u et v. »
→ réponse : le chemin de longueur minimale.

**Elles sont liées.** Si l'on sait résoudre la version décision en temps T, on
résout l'optimisation par **dichotomie** sur k, en O(T · log k). C'est pourquoi la
théorie se concentre sur la décision sans rien perdre.

**L'argument de dénombrement, développé.**

Un problème de décision sur les entiers est une fonction ℕ → {oui, non},
c'est-à-dire un **sous-ensemble de ℕ**. Il y en a donc `2^ℵ₀` — non dénombrable,
par l'argument diagonal de Cantor.

Un programme est une chaîne finie de caractères. L'ensemble des chaînes finies sur
un alphabet fini est **dénombrable** : on peut les énumérer par longueur croissante,
puis par ordre alphabétique.

Il y a donc **strictement plus de problèmes que de programmes**. La quasi-totalité
des problèmes n'a aucun algorithme.

Cet argument prouve l'existence de problèmes indécidables **sans en exhiber aucun**.
Le chapitre 5 en construira un explicitement.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier la terminaison dans la définition d'un algorithme.** Une procédure qui
  boucle n'en est pas un.
- **Confondre problème et instance.** Le problème est la question générale,
  l'instance un cas particulier.
- **Croire que « pas d'algorithme connu » signifie « pas d'algorithme ».**
  L'indécidabilité se **démontre**, elle ne se constate pas.
- **Confondre indécidable et intraitable.** Indécidable : aucun algorithme n'existe.
  Intraitable : il en existe, mais trop lent.
MD,
                        'examiner_expects' => <<<'MD'
Les **trois conditions** d'un algorithme, terminaison comprise. La distinction
**décision / recherche / optimisation**. Et l'**argument de dénombrement** énoncé
correctement : non dénombrable contre dénombrable.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'definition',
                            'front' => 'Les trois conditions pour qu’une procédure soit un algorithme ?',
                            'back' => "1. Chaque instruction est **élémentaire et non ambiguë**.\n2. L'exécution **termine** en un nombre fini d'étapes.\n3. Elle produit le **résultat attendu** pour toute instance valide.\n\nLa terminaison est celle qu'on oublie.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Pourquoi existe-t-il des problèmes sans algorithme ?',
                            'back' => "**Par dénombrement.** Les problèmes de décision sont en quantité **non dénombrable** (autant que de parties de ℕ) ; les programmes, en quantité **dénombrable** (ce sont des textes finis).\n\nIl y a donc strictement plus de problèmes que de programmes.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'Indécidable ou intraitable : quelle différence ?',
                            'back' => "**Indécidable** — aucun algorithme n'existe, jamais. Impossibilité absolue.\n\n**Intraitable** — un algorithme existe, mais il est trop lent (exponentiel). Question de coût.",
                            'difficulty' => 4,
                        ],
                    ],
                ],

                'C4' => [
                    'lessons' => [[
                        'title' => 'Variations sur la machine de Turing',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
La machine de Turing du chapitre 3 est volontairement pauvre : un ruban, une tête,
un déplacement d'une case à la fois.

On peut l'enrichir — plusieurs rubans, plusieurs têtes, du non-déterminisme.
Chaque variante paraît plus puissante. La question du chapitre est : **l'est-elle
vraiment ?**

La réponse est **non**. Toutes ces variantes décident exactement les mêmes langages.
Elles vont seulement plus ou moins vite. C'est ce résultat qui justifie de prendre
la machine la plus simple comme référence, et qui fonde la thèse de Church.
MD,
                        'formalism' => <<<'MD'
**Machine à plusieurs rubans**

k rubans, k têtes indépendantes. La fonction de transition devient :

```
δ : Q × Γᵏ → Q × Γᵏ × {G, D, S}ᵏ
```

Le `S` — *surplace* — permet à une tête de ne pas bouger. C'est une commodité,
pas un gain de puissance : on la simule avec deux transitions.

> **Théorème.** Toute machine à k rubans fonctionnant en temps t(n) peut être simulée
> par une machine à un ruban en **O(t(n)²)**.

*L'idée de la simulation.* On stocke les k rubans les uns après les autres sur le
ruban unique, séparés par un marqueur, et l'on note la position de chaque tête par
un symbole spécial. Simuler **une** étape demande de parcourir tout le ruban pour
lire les k symboles, puis de le reparcourir pour écrire — soit O(t(n)) par étape,
et O(t(n)²) au total.

**Le surcoût est polynomial.** C'est le point capital : les deux modèles décident
les mêmes langages, et **en temps polynomial pour les mêmes**. La classe **P** ne
dépend donc pas du nombre de rubans.

**Machine non déterministe**

La transition ne donne plus **un** état suivant mais un **ensemble** :

```
δ : Q × Γ → P(Q × Γ × {G, D})
```

À chaque étape, la machine peut prendre plusieurs chemins. Elle **accepte** s'il
existe **au moins un** chemin qui mène à q_accept.

C'est une machine qui « devine » la bonne réponse, puis la vérifie.

> **Théorème.** Toute machine non déterministe en temps t(n) se simule par une
> machine déterministe en **O(2^{O(t(n))})**.

*L'idée.* On explore l'arbre des choix **en largeur** — pas en profondeur, car une
branche infinie bloquerait tout. L'arbre a une profondeur t(n) et un facteur de
branchement borné, donc un nombre exponentiel de nœuds.

**Le surcoût est exponentiel.** Même pouvoir de décision, mais on ne sait pas si le
gain de temps est essentiel : c'est très exactement la question **P = NP**.

**Machine à 3 symboles**

On peut restreindre Γ à trois symboles — par exemple {0, 1, ␣} — sans rien perdre :
il suffit de coder chaque symbole d'origine sur plusieurs cases.

Surcoût : **O(t(n) · log |Γ|)**, logarithmique. Négligeable.

**Récapitulatif — le tableau à connaître**

| Variante | Surcoût de simulation | Nature |
|---|---|---|
| k rubans → 1 ruban | O(t(n)²) | **polynomial** |
| non déterministe → déterministe | O(2^{O(t(n))}) | **exponentiel** |
| Γ quelconque → 3 symboles | O(t(n) · log \|Γ\|) | **logarithmique** |

**La thèse de l'invariance** en découle : tous les modèles de calcul raisonnables se
simulent mutuellement avec un surcoût polynomial. C'est ce qui rend la classe **P**
robuste — elle ne dépend pas du modèle.
MD,
                        'worked_example' => <<<'MD'
**Où le multi-ruban fait vraiment gagner.**

Reconnaître `L = { aⁿbⁿ | n ≥ 1 }`.

*À un ruban.* On barre un `a`, on traverse pour barrer un `b`, on revient.
Chaque aller-retour coûte O(n), il y en a n/2. **Total : O(n²).**

*À deux rubans.* On copie les `a` sur le second ruban en une passe, puis on lit les
`b` du premier en dépilant les `a` du second, simultanément. **Total : O(n).**

Le gain est réel — d'un facteur n. Mais il est **polynomial** : les deux machines
décident le même langage en temps polynomial, donc L est dans **P** dans les deux cas.

C'est exactement ce que dit le théorème : la simulation coûte au plus le carré,
donc un polynôme reste un polynôme.

**Où le non-déterminisme fait gagner.**

Décider si un graphe admet un cycle hamiltonien.

*Non déterministe.* La machine **devine** une permutation des n sommets, puis vérifie
en O(n²) que c'est bien un cycle. **Temps polynomial.**

*Déterministe.* On ne connaît rien de mieux que d'essayer les permutations,
soit **O(n!)**.

Le problème est donc dans **NP** — décidable en temps polynomial par une machine non
déterministe — sans qu'on sache s'il est dans **P**. Toute la question P = NP tient
dans cet écart.
MD,
                        'pitfalls' => <<<'MD'
- **Croire qu'une variante décide plus de langages.** Aucune ne le fait. Elles ne
  changent que la vitesse.
- **Confondre surcoût polynomial et exponentiel.** Multi-ruban : carré, donc P est
  préservée. Non-déterminisme : exponentiel, et c'est là que se joue P = NP.
- **Croire qu'une machine non déterministe « essaie tous les chemins en parallèle ».**
  Formellement, elle **accepte s'il existe** un chemin acceptant. La métaphore du
  parallélisme est trompeuse.
- **Simuler le non-déterminisme en profondeur.** Une branche infinie bloquerait tout.
  C'est un parcours **en largeur**.
- **Oublier de conclure sur la classe.** Un surcoût sans conclusion ne rapporte que
  la moitié des points.
MD,
                        'examiner_expects' => <<<'MD'
Le **tableau des trois surcoûts** avec leur nature — polynomial, exponentiel,
logarithmique. L'**idée** de chaque simulation en deux phrases. Et la conclusion :
les variantes **décident les mêmes langages**, seule la vitesse change.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'formule',
                            'front' => 'Coût de simulation d’une machine à k rubans par une machine à un ruban ?',
                            'back' => "**O(t(n)²)** — surcoût **quadratique**, donc polynomial.\n\nConséquence capitale : la classe **P** ne dépend pas du nombre de rubans.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'formule',
                            'front' => "Coût de simulation d'une machine non déterministe par une déterministe ?",
                            'back' => "**O(2^{O(t(n))})** — surcoût **exponentiel**.\n\nMême pouvoir de décision, mais c'est dans cet écart que se loge la question **P = NP**.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'Une machine non déterministe décide-t-elle plus de langages ?',
                            'back' => "**Non.** Aucune variante n'augmente le pouvoir de décision — ni les rubans multiples, ni le non-déterminisme, ni la taille de l'alphabet.\n\nElles ne changent que la **vitesse**.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Quand une machine non déterministe accepte-t-elle un mot ?',
                            'back' => "**S'il existe au moins un chemin de calcul** menant à q_accept.\n\nPas « si tous les chemins acceptent ». Et la simulation déterministe explore l'arbre **en largeur** — en profondeur, une branche infinie bloquerait tout.",
                            'difficulty' => 5,
                        ],
                    ],
                ],

                'C8' => [
                    'lessons' => [[
                        'title' => 'Tris par comparaisons — compter les opérations',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Quatre algorithmes de tri, tous corrects, tous différents en coût. L'intérêt du
chapitre n'est pas de les apprendre par cœur : c'est de savoir **compter**.

Le cours distingue systématiquement deux comptages :

- le nombre de **comparaisons** — combien de fois demande-t-on « lequel est le plus
  grand ? » ;
- le nombre d'**affectations** — combien de fois déplace-t-on une valeur.

Ce n'est pas la même chose, et les deux sont demandés séparément à l'examen.
Comparer deux entiers coûte peu ; déplacer un objet volumineux coûte cher.
MD,
                        'formalism' => <<<'MD'
**Tri par sélection**

On cherche le minimum du reste, on l'échange avec la première case non triée.

```
pour i de 0 à n-2 :
    min ← i
    pour j de i+1 à n-1 :
        si T[j] < T[min] : min ← j
    échanger T[i] et T[min]
```

| | Nombre |
|---|---|
| Comparaisons | `(n-1) + (n-2) + … + 1 = n(n-1)/2` → **O(n²)**, toujours |
| Affectations | **O(n)** — un échange par tour, soit 3(n−1) affectations |

Sa particularité : le nombre de comparaisons **ne dépend pas** des données.
Meilleur cas, pire cas, cas moyen : toujours n(n−1)/2. En revanche, il fait très
peu de déplacements — utile quand déplacer coûte cher.

**Tri à bulles**

On parcourt en échangeant les voisins mal ordonnés, jusqu'à ce que plus rien ne bouge.

| | Nombre |
|---|---|
| Comparaisons | **O(n²)** au pire ; **O(n)** au meilleur avec le drapeau d'arrêt |
| Affectations | **O(n²)** au pire |

**Tri par insertion**

On insère chaque élément à sa place dans la partie déjà triée, comme on trie des
cartes en main.

| | Nombre |
|---|---|
| Comparaisons | **O(n)** si déjà trié ; **O(n²)** au pire |
| Affectations | **O(n²)** au pire |

C'est le meilleur des trois sur des données **presque triées** — cas fréquent en
pratique.

**Tri rapide (quicksort)**

On choisit un **pivot**, on partitionne autour de lui, on recommence sur chaque moitié.

| Cas | Coût |
|---|---|
| Moyen | **O(n log n)** |
| Pire (pivot toujours extrême) | **O(n²)** |
| Espace | O(log n) de pile en moyenne |

**La borne inférieure — le résultat le plus important du chapitre**

> **Théorème.** Tout tri par comparaisons effectue au moins **Ω(n log n)**
> comparaisons dans le pire cas.

*Démonstration en trois lignes.* Un tri par comparaisons se représente par un
**arbre de décision** binaire : chaque nœud est une comparaison, chaque feuille une
permutation possible. Il y a **n!** permutations, donc au moins n! feuilles.
Un arbre binaire à n! feuilles a une hauteur d'au moins **log₂(n!)**.
Or, par la formule de Stirling, `log₂(n!) = Θ(n log n)`. ∎

**Conséquence :** aucun tri par comparaisons ne peut faire mieux que n log n.
Le tri fusion et le tri par tas atteignent cette borne — ils sont **optimaux**.

*Nuance à mentionner :* les tris qui **n'utilisent pas de comparaisons** — tri par
comptage, tri radix — échappent à la borne et peuvent atteindre O(n). Ils exigent
en revanche des hypothèses sur les données.
MD,
                        'worked_example' => <<<'MD'
**Compter exactement sur le tri par sélection.**

Tableau `T = [5, 2, 8, 1]`, n = 4.

| Tour i | Sous-tableau examiné | Comparaisons | Minimum trouvé | Échange | Tableau |
|---|---|---|---|---|---|
| 0 | T[1..3] | **3** | 1 (indice 3) | T[0]↔T[3] | [1, 2, 8, 5] |
| 1 | T[2..3] | **2** | 2 (indice 1) | aucun | [1, 2, 8, 5] |
| 2 | T[3..3] | **1** | 5 (indice 3) | T[2]↔T[3] | [1, 2, 5, 8] |

**Total des comparaisons : 3 + 2 + 1 = 6.**

Vérification par la formule : `n(n−1)/2 = 4×3/2 = 6`. ✓

**Total des échanges : 2**, soit **6 affectations** (chaque échange en demande trois,
avec une variable temporaire).

**La rédaction attendue**, en quatre lignes :

> Le tri par sélection effectue `(n−1) + (n−2) + … + 1 = n(n−1)/2` comparaisons,
> soit **O(n²)**, et ce **quel que soit le contenu du tableau** — meilleur cas et
> pire cas coïncident.
>
> Il effectue au plus n−1 échanges, soit **3(n−1) affectations**, donc **O(n)**.
>
> C'est son intérêt : peu de déplacements, ce qui le rend préférable quand déplacer
> un élément coûte cher.

Notez la structure : le **calcul posé**, la **notation O**, et une **phrase
d'interprétation**. C'est le gabarit de toute réponse de complexité.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre comparaisons et affectations.** Le cours les compte séparément,
  et l'examen les demande séparément.
- **Annoncer O(n log n) pour le tri rapide au pire cas.** Au pire, il est en
  **O(n²)** — quand le pivot est systématiquement le plus petit ou le plus grand.
- **Oublier que le tri par sélection ne dépend pas des données.** Ses comparaisons
  sont les mêmes dans tous les cas.
- **Croire qu'on peut battre n log n.** Impossible **par comparaisons**. Les tris
  par comptage ou radix y échappent, mais ne comparent pas.
- **Oublier la formule de Stirling** dans la démonstration de la borne inférieure.
  C'est elle qui donne `log₂(n!) = Θ(n log n)`.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Le **calcul posé**, pas seulement le résultat : la somme `(n−1)+…+1` avant `n(n−1)/2`.
- [ ] Les deux comptages **séparés** : comparaisons et affectations.
- [ ] La distinction **meilleur cas / pire cas / cas moyen** quand elle existe.
- [ ] Pour la borne inférieure : l'**arbre de décision**, les **n! feuilles**,
      la **hauteur log₂(n!)**, et **Stirling**.
MD,
                    ]],
                    'cards' => [
                        [
                            'kind' => 'formule',
                            'front' => 'Tri par sélection : combien de comparaisons ? d’affectations ?',
                            'back' => "**Comparaisons : n(n−1)/2 → O(n²)**, quelles que soient les données.\n**Affectations : 3(n−1) → O(n)**.\n\nSon intérêt : très peu de déplacements, utile quand déplacer coûte cher.",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'formule',
                            'front' => 'Tri rapide : complexité en moyenne et au pire ?',
                            'back' => "**Moyenne : O(n log n)**\n**Pire cas : O(n²)** — quand le pivot est systématiquement le plus petit ou le plus grand.\n\nAnnoncer O(n log n) au pire cas est une faute.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'definition',
                            'front' => 'Pourquoi aucun tri par comparaisons ne peut-il battre n log n ?',
                            'back' => "**Par l'arbre de décision.** Chaque nœud est une comparaison, chaque feuille une permutation. Il faut au moins **n! feuilles**, donc une hauteur ≥ **log₂(n!)**.\n\nPar Stirling, `log₂(n!) = Θ(n log n)`. ∎",
                            'difficulty' => 5,
                        ],
                        [
                            'kind' => 'piege',
                            'front' => 'Existe-t-il des tris en O(n) ?',
                            'back' => "**Oui, mais sans comparaisons** : tri par comptage, tri radix.\n\nIls échappent à la borne Ω(n log n) précisément parce qu'ils ne comparent pas — au prix d'hypothèses sur les données.",
                            'difficulty' => 4,
                        ],
                        [
                            'kind' => 'methode',
                            'front' => 'Quel tri choisir sur des données presque triées ?',
                            'back' => "**Le tri par insertion** — O(n) comparaisons si le tableau est déjà trié, contre O(n²) systématique pour la sélection.\n\nC'est un cas fréquent en pratique.",
                            'difficulty' => 4,
                        ],
                    ],
                ],
            ],
        ];
    }
}