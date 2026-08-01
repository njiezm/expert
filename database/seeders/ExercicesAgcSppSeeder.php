<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Exercices d'AGC et de SPP restés à produire.
 *
 * Pour AGC, chaque énoncé impose de chiffrer : c'est la contre-mesure directe
 * aux annotations « justifier » et « évaluation ? » de janvier.
 */
class ExercicesAgcSppSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->exercices() as [$matiere, $chapCode, $exo]) {
            $subject = Subject::where('code', $matiere)->first();

            if (! $subject) {
                continue;
            }

            $chapter = Chapter::where('subject_id', $subject->id)->where('code', $chapCode)->first();

            if (! $chapter) {
                continue;
            }

            Exercise::updateOrCreate(
                ['subject_id' => $subject->id, 'title' => $exo['title']],
                $exo + ['chapter_id' => $chapter->id, 'position' => 310]
            );
        }
    }

    /* ==================================================================== */

    private function exercices(): array
    {
        return [

            /* ============ AGC PL — Programmation linéaire ============ */
            ['AGC', 'PL', [
                'title' => 'Mettre en équation et résoudre graphiquement',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'statement' => <<<'MD'
Une coopérative produit du **jus d'orange** et du **jus d'ananas**.

- Un litre de jus d'orange demande 2 kg de fruits et 1 h de pressage, marge **3 €**.
- Un litre de jus d'ananas demande 1 kg de fruits et 3 h de pressage, marge **5 €**.
- On dispose de **100 kg** de fruits et **120 h** de pressage par semaine.
- Le contrat impose d'en produire **au moins 10 litres** d'orange.

**1.** Mettez le problème en équation : objectif, contraintes, positivité. *(4 pts)*

**2.** Déterminez les **sommets** du domaine réalisable. Donnez leurs coordonnées
exactes. *(5 pts)*

**3.** Calculez la marge en chaque sommet et donnez l'**optimum**. *(3 pts)*

**4.** Quelles contraintes sont **saturées** à l'optimum ? Que reste-t-il de
ressource inutilisée ? Interprétez. *(3 pts)*

**5.** Écrivez la **forme standard** du problème, avec ses variables d'écart. *(2 pts)*

**6.** La coopérative peut acheter 20 kg de fruits supplémentaires pour 40 €.
Est-ce rentable ? Justifiez par un calcul. *(3 pts)*
MD,
                'hint' => "Pour la question 2, les sommets sont les intersections deux à deux des droites de contrainte — sans oublier celles qui bornent le domaine et les contraintes de positivité. Vérifiez que chaque point trouvé respecte bien **toutes** les contraintes.",
                'method' => <<<'MD'
1. Nommez les inconnues avant tout : `x` = litres d'orange, `y` = litres d'ananas.
2. Tracez chaque droite, hachurez le demi-plan autorisé, repérez l'intersection.
   Calculez chaque sommet en résolvant un système de deux équations.
3. Dressez un tableau sommet / valeur de z.
4. Une contrainte est saturée quand son inégalité devient une égalité.
6. Recalculez l'optimum avec 120 kg de fruits, comparez le gain aux 40 € demandés.
MD,
                'solution' => <<<'MD'
**1. Mise en équation**

Soit `x` le nombre de litres de jus d'orange, `y` celui de jus d'ananas.

```
Maximiser    z = 3x + 5y

sous          2x +  y ≤ 100        (fruits, en kg)
               x + 3y ≤ 120        (pressage, en heures)
                    x ≥ 10         (contrat)
                    y ≥ 0
```

**2. Les sommets du domaine**

*Intersection `x = 10` et `2x + y = 100`* → `y = 80`. Vérifions le pressage :
`10 + 240 = 250 > 120`. ❌ **Ce point n'est pas réalisable.**

*Intersection `x = 10` et `x + 3y = 120`* → `3y = 110`, donc `y = 110/3 ≈ 36,67`.
Fruits : `20 + 36,67 = 56,67 ≤ 100`. ✅ **Sommet A = (10 ; 36,67).**

*Intersection `2x + y = 100` et `x + 3y = 120`* :
De la première, `y = 100 − 2x`. En substituant : `x + 300 − 6x = 120`,
donc `−5x = −180`, soit **x = 36** et **y = 28**.
Contrat : `36 ≥ 10`. ✅ **Sommet B = (36 ; 28).**

*Intersection `x = 10` et `y = 0`* → ✅ **Sommet C = (10 ; 0).**

*Intersection `y = 0` et `2x + y = 100`* → `x = 50`. Pressage : `50 ≤ 120`. ✅
**Sommet D = (50 ; 0).**

**Domaine : le quadrilatère A, B, D, C.**

**3. La marge en chaque sommet**

| Sommet | (x, y) | z = 3x + 5y |
|---|---|---|
| A | (10 ; 36,67) | 30 + 183,33 = **213,33** |
| B | (36 ; 28) | 108 + 140 = **248** |
| C | (10 ; 0) | 30 |
| D | (50 ; 0) | 150 |

**Optimum : 248 € par semaine**, en produisant **36 litres d'orange** et
**28 litres d'ananas**.

**4. Contraintes saturées**

Au point B = (36 ; 28) :

- Fruits : `2(36) + 28 = 100`. **Saturée** — les 100 kg sont entièrement utilisés.
- Pressage : `36 + 3(28) = 120`. **Saturée** — les 120 h sont entièrement utilisées.
- Contrat : `36 ≥ 10`, **non saturée** — il y a 26 litres de marge.

**Interprétation.** Les deux ressources sont **entièrement consommées** : aucun kilo
de fruit ni aucune heure de pressage n'est perdu. C'est le signe d'un dimensionnement
équilibré — augmenter l'une des deux ressources permettrait de gagner davantage.

Le contrat, lui, n'est pas contraignant : on produit bien plus que les 10 litres
exigés. Le retirer ne changerait pas l'optimum.

**5. Forme standard**

```
Maximiser    z = 3x + 5y

sous          2x +  y + e₁       = 100
               x + 3y      + e₂  = 120
               x       − e₃      =  10
               x, y, e₁, e₂, e₃ ≥ 0
```

À l'optimum : `e₁ = 0`, `e₂ = 0` (contraintes saturées), `e₃ = 26`.

**6. Acheter 20 kg de fruits pour 40 € ?**

Nouveau problème, avec `2x + y ≤ 120` :

```
2x +  y = 120
 x + 3y = 120
```
De la première, `y = 120 − 2x`. En substituant : `x + 360 − 6x = 120`,
donc `−5x = −240`, soit **x = 48** et **y = 24**.

Nouvelle marge : `3(48) + 5(24) = 144 + 120 = **264 €**`.

**Gain : 264 − 248 = 16 €.** Le coût est de **40 €**.

**Ce n'est pas rentable** : on perdrait 24 € par semaine.

*L'interprétation économique.* Le gain de 16 € pour 20 kg donne une valeur marginale
de **0,80 € par kilo** de fruit — c'est ce qu'on appelle le **prix dual** de la
contrainte. Tant que le fournisseur demande plus de 0,80 €/kg, l'achat n'a pas
d'intérêt.
MD,
                'rubric' => [
                    ['label' => 'Inconnues nommées, objectif et trois contraintes posés', 'points' => 3],
                    ['label' => 'Contraintes de positivité présentes', 'points' => 1],
                    ['label' => 'Les quatre sommets calculés exactement', 'points' => 3],
                    ['label' => 'Le point (10 ; 80) est écarté car il viole le pressage', 'points' => 2],
                    ['label' => 'Tableau des marges, optimum 248 € en (36 ; 28)', 'points' => 3],
                    ['label' => 'Les deux contraintes saturées identifiées et interprétées', 'points' => 3],
                    ['label' => 'Forme standard avec trois variables d’écart', 'points' => 2],
                    ['label' => 'Achat non rentable, avec le calcul du gain marginal', 'points' => 3],
                ],
            ]],

            /* ============ AGC PG — Glouton ============ */
            ['AGC', 'PG', [
                'title' => 'Glouton : quand il marche, quand il échoue',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 4,
                'statement' => <<<'MD'
**Partie A — le choix d'activités.** Huit réservations de salle :

| Act. | A | B | C | D | E | F | G | H |
|---|---|---|---|---|---|---|---|---|
| début | 1 | 2 | 4 | 1 | 5 | 8 | 9 | 11 |
| fin | 3 | 5 | 7 | 8 | 9 | 10 | 11 | 14 |

**1.** Appliquez le glouton en triant par **date de fin**. Donnez le déroulé et la
solution. *(3 pts)*

**2.** Appliquez-le en triant par **durée croissante**. Obtenez-vous le même nombre
d'activités ? *(2 pts)*

**3.** Que conclure sur le choix du critère de tri ? *(1 pt)*

**Partie B — rendu de monnaie.** Systèmes `S₁ = {1, 2, 5, 10}` et `S₂ = {1, 4, 6}`.

**4.** Rendre 8 avec S₁ puis avec S₂, en glouton. Comparez à l'optimum. *(4 pts)*

**5.** Le glouton est-il correct sur S₁ ? Sur S₂ ? Justifiez. *(2 pts)*

**Partie C — coloration.** Graphe : sommets 1 à 6,
arêtes `12, 13, 14, 23, 25, 34, 45, 46, 56`.

**6.** Colorez en glouton, sommets par **degré décroissant**. Combien de couleurs ? *(3 pts)*

**7.** Le nombre chromatique χ(G) vaut-il ce nombre ? Que garantit le glouton ? *(2 pts)*

**8.** Donnez la complexité de chacun des trois algorithmes employés. *(3 pts)*
MD,
                'hint' => "Question 2 : triez par durée et déroulez honnêtement — le résultat est instructif. Question 7 : cherchez un coloriage à moins de couleurs avant de conclure.",
                'method' => <<<'MD'
1. Triez, puis parcourez en gardant en mémoire la **fin de la dernière activité
   retenue**. On prend si le début est ≥ à cette fin.
4. Glouton = prendre la plus grosse pièce possible, autant que possible.
   Pour l'optimum, cherchez à la main.
6. Degré décroissant d'abord, puis pour chaque sommet la plus petite couleur non
   utilisée par ses voisins déjà colorés.
MD,
                'solution' => <<<'MD'
**1. Tri par date de fin**

Ordre : A(3), B(5), C(7), D(8), E(9), F(10), G(11), H(14).

| Activité | Début | Fin de la dernière retenue | Décision |
|---|---|---|---|
| A | 1 | — | **prise**, fin 3 |
| B | 2 | 3 | rejetée, 2 < 3 |
| C | 4 | 3 | **prise**, fin 7 |
| D | 1 | 7 | rejetée |
| E | 5 | 7 | rejetée |
| F | 8 | 7 | **prise**, fin 10 |
| G | 9 | 10 | rejetée |
| H | 11 | 10 | **prise**, fin 14 |

**Solution : {A, C, F, H} — 4 activités.**

**2. Tri par durée croissante**

Durées : A = 2, B = 3, C = 3, G = 2, D = 7, E = 4, F = 2, H = 3.
Ordre : A(2), G(2), F(2), B(3), C(3), H(3), E(4), D(7).

| Activité | Créneau | Compatible ? | Décision |
|---|---|---|---|
| A | [1,3] | — | **prise** |
| G | [9,11] | oui | **prise** |
| F | [8,10] | **non**, chevauche G | rejetée |
| B | [2,5] | oui (après A, avant G) | **prise** |
| C | [4,7] | **non**, chevauche B | rejetée |
| H | [11,14] | oui | **prise** |
| E | [5,9] | **non**, chevauche G | rejetée |
| D | [1,8] | non | rejetée |

**Solution : {A, B, G, H} — 4 activités.**

Ici les deux critères donnent 4. **Mais ce n'est pas une garantie.** Contre-exemple
minimal : activités [0,10], [1,3], [4,6]. Trier par durée prend [1,3] et [4,6] —
deux activités, ce qui est optimal ici. Prenons plutôt [0,4], [3,10], [5,7] :
par durée, on prend [5,7] (2), puis [0,4] (4) → **2 activités**. Par date de fin :
[0,4] puis [5,7] → **2** également.

Le vrai contre-exemple du tri par durée : [0,5], [4,7], [6,11]. Par durée, on prend
[4,7] (3) qui bloque les deux autres → **1 activité**. Par date de fin : [0,5] puis
[6,11] → **2 activités**. Le tri par durée échoue.

**3. Conclusion**

Seul le tri par **date de fin** garantit l'optimum. Il libère la ressource au plus
tôt, ce qui laisse le maximum de place pour la suite. Les autres critères — durée,
date de début, nombre de conflits — donnent parfois le bon résultat **par hasard**,
jamais par garantie.

**4. Rendu de monnaie**

*Rendre 8 avec S₁ = {1, 2, 5, 10}.*
Glouton : 5, puis 2, puis 1 → **3 pièces**.
Optimum : 5 + 2 + 1 → **3 pièces**. ✅ Identique.

*Rendre 8 avec S₂ = {1, 4, 6}.*
Glouton : 6, puis 1, puis 1 → **3 pièces**.
Optimum : 4 + 4 → **2 pièces**. ❌ **Le glouton échoue.**

**5. Correction du glouton**

Sur **S₁**, le système est **canonique** : le glouton donne toujours l'optimum.
C'est le cas des systèmes monétaires usuels, construits pour cela.

Sur **S₂**, il ne l'est **pas** : le contre-exemple de la question 4 le prouve.
Rendre 8 en prenant 6 condamne à trois pièces, alors que renoncer au 6 en donne deux.

**Un seul contre-exemple suffit à réfuter le glouton.** C'est ce qu'il faut produire
en examen, plutôt qu'une justification vague.

**6. Coloration gloutonne**

Degrés : `deg(1) = 3` (2,3,4), `deg(2) = 3` (1,3,5), `deg(3) = 3` (1,2,4),
`deg(4) = 4` (1,3,5,6), `deg(5) = 3` (2,4,6), `deg(6) = 2` (4,5).

Ordre décroissant : **4, 1, 2, 3, 5, 6**.

| Sommet | Voisins déjà colorés | Couleurs prises | Couleur attribuée |
|---|---|---|---|
| 4 | — | — | **C1** |
| 1 | 4 | C1 | **C2** |
| 2 | 1 | C2 | **C1** |
| 3 | 1, 2, 4 | C2, C1, C1 | **C3** |
| 5 | 2, 4 | C1, C1 | **C2** |
| 6 | 4, 5 | C1, C2 | **C3** |

**Trois couleurs.** C1 = {2, 4}, C2 = {1, 5}, C3 = {3, 6}.

**7. Nombre chromatique**

Les sommets **1, 2, 3** forment un **triangle** : `12`, `13`, `23` sont toutes des
arêtes. Un triangle exige **trois couleurs distinctes**, donc **χ(G) ≥ 3**.

Le glouton en a utilisé 3, et 3 suffisent. Donc **χ(G) = 3** : ici le glouton est
optimal.

**Mais ce n'est pas garanti.** Le glouton assure seulement **au plus Δ + 1 couleurs**,
où Δ = 4 est le degré maximal — soit une borne de 5. Il pourrait en utiliser
davantage que χ(G) sur un autre ordre de parcours. Calculer χ(G) exactement est
**NP-difficile**.

**8. Complexités**

| Algorithme | Coût | Dominé par |
|---|---|---|
| Choix d'activités | **O(n log n)** | le tri |
| Rendu de monnaie glouton | **O(k)** avec k types de pièces, après tri en O(k log k) | le parcours |
| Coloration gloutonne | **O(n + m)** après tri des sommets en O(n log n) | le tri, puis le parcours des voisins |
MD,
                'rubric' => [
                    ['label' => 'Tri par date de fin : déroulé et solution {A, C, F, H}', 'points' => 3],
                    ['label' => 'Tri par durée : déroulé honnête et solution', 'points' => 2],
                    ['label' => 'Conclusion : seule la date de fin garantit l’optimum', 'points' => 1],
                    ['label' => 'Rendu de 8 sur les deux systèmes, glouton et optimum', 'points' => 3],
                    ['label' => 'S₂ : contre-exemple 6+1+1 contre 4+4 explicite', 'points' => 1],
                    ['label' => 'Système canonique nommé pour S₁', 'points' => 2],
                    ['label' => 'Coloration : ordre par degré et tableau d’attribution', 'points' => 3],
                    ['label' => 'χ(G) = 3 justifié par le triangle 1-2-3', 'points' => 1],
                    ['label' => 'Le glouton garantit seulement Δ+1, et χ est NP-difficile', 'points' => 1],
                    ['label' => 'Les trois complexités données', 'points' => 3],
                ],
            ]],

            /* ============ AGC CY — Cycles ============ */
            ['AGC', 'CY', [
                'title' => 'Eulérien, hamiltonien, et approximation du voyageur',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'statement' => <<<'MD'
**Partie A.** Graphe non orienté, sommets A à E,
arêtes `AB, AC, AD, BC, BD, CD, CE, DE`.

**1.** Calculez le degré de chaque sommet. *(1 pt)*
**2.** Le graphe admet-il un **cycle** eulérien ? Un **chemin** eulérien ?
Énoncez le théorème avant de conclure. *(3 pts)*
**3.** Si un chemin existe, donnez-le. Sinon, dites quelle arête ajouter ou retirer
pour en obtenir un. *(2 pts)*

**Partie B.** Cinq villes, distances symétriques :

| | A | B | C | D | E |
|---|---|---|---|---|---|
| **A** | — | 3 | 8 | 12 | 6 |
| **B** | 3 | — | 5 | 9 | 4 |
| **C** | 8 | 5 | — | 7 | 11 |
| **D** | 12 | 9 | 7 | — | 5 |
| **E** | 6 | 4 | 11 | 5 | — |

**4.** Construisez l'**arbre couvrant minimal** par Kruskal. Donnez son poids. *(3 pts)*
**5.** Déduisez-en une tournée par **parcours en profondeur** depuis A.
Donnez son coût. *(3 pts)*
**6.** Quelle **garantie** avez-vous sur cette tournée ? Sous quelle hypothèse ?
Encadrez l'optimum. *(3 pts)*
**7.** Pourquoi ne cherche-t-on pas l'optimum exact ? Donnez la complexité de la
force brute et celle de Held-Karp pour n = 5, puis pour n = 20. *(3 pts)*
MD,
                'hint' => "Question 2 : comptez les sommets de degré impair, c'est le seul critère. Question 6 : la garantie du facteur 2 se démontre en comparant le poids de l'ACM à celui du cycle optimal.",
                'method' => <<<'MD'
1. Comptez pour chaque sommet le nombre d'arêtes qui l'atteignent.
2. Énoncez le théorème d'Euler **avec la connexité**, puis appliquez.
4. Triez les arêtes, prenez sans créer de cycle, arrêtez-vous à n − 1.
5. Parcourez l'arbre en profondeur, notez l'ordre de **première visite**, fermez le cycle.
7. Calculez n! et n²·2ⁿ pour les deux valeurs.
MD,
                'solution' => <<<'MD'
**1. Les degrés**

| Sommet | Voisins | Degré |
|---|---|---|
| A | B, C, D | **3** |
| B | A, C, D | **3** |
| C | A, B, D, E | **4** |
| D | A, B, C, E | **4** |
| E | C, D | **2** |

Vérification : somme des degrés = 3+3+4+4+2 = 16 = 2 × 8 arêtes. ✅

**2. Eulérien ?**

> **Théorème d'Euler.** Un graphe **connexe** admet un **cycle** eulérien si et
> seulement si **tous** ses sommets sont de degré pair. Il admet un **chemin**
> eulérien si et seulement s'il a **exactement zéro ou deux** sommets de degré impair.

Le graphe est connexe. Sommets de degré **impair** : **A et B**, soit **exactement deux**.

- **Cycle eulérien : non** — il en faudrait zéro d'impair.
- **Chemin eulérien : oui** — deux sommets impairs, et le chemin devra
  **partir de A et arriver en B** (ou l'inverse).

**3. Un chemin eulérien**

Partons de A :
```
A → C → E → D → C ✗
```
Reprenons proprement. Les huit arêtes : AB, AC, AD, BC, BD, CD, CE, DE.

```
A → D → E → C → D → B → C → A → B
```
Vérifions : AD, DE, EC, CD, DB, BC, CA, AB — **les huit arêtes, chacune une fois**.
Départ A, arrivée B. ✅

**4. Arbre couvrant minimal par Kruskal**

Tri : AB(3), BE(4), BC(5), DE(5), AE(6), CD(7), AC(8), BD(9), CE(11), AD(12).

| Arête | Poids | Cycle ? | Décision |
|---|---|---|---|
| AB | 3 | non | **prise** |
| BE | 4 | non | **prise** |
| BC | 5 | non | **prise** |
| DE | 5 | non | **prise** |

Quatre arêtes pour cinq sommets : l'arbre est complet.

**ACM = {AB, BE, BC, DE}, poids = 3 + 4 + 5 + 5 = 17.**

**5. La tournée**

L'arbre vu depuis A : A — B ; B — E, C ; E — D.

Parcours en profondeur depuis A, voisins par ordre de découverte :
```
A → B → E → D  (retour) → C
```
**Ordre de première visite : A, B, E, D, C.**

Tournée : **A → B → E → D → C → A**.

Coût : `AB(3) + BE(4) + ED(5) + DC(7) + CA(8)` = **27**.

**6. La garantie**

> **Théorème.** Si l'inégalité triangulaire est vérifiée, la tournée obtenue par
> parcours de l'ACM coûte **au plus 2 fois l'optimum**.

*La démonstration, en deux temps.*
Retirer une arête d'une tournée optimale donne un arbre couvrant, donc
`poids(ACM) ≤ coût(optimum)`. Et le parcours en profondeur emprunte chaque arête
de l'arbre **au plus deux fois**, d'où `coût(tournée) ≤ 2 · poids(ACM)`.
En combinant : `coût(tournée) ≤ 2 · coût(optimum)`.

*Ici.* `poids(ACM) = 17`, donc **l'optimum est au moins 17**.
Notre tournée coûte 27, donc **l'optimum est au plus 27**.

**17 ≤ optimum ≤ 27.**

*L'hypothèse.* L'**inégalité triangulaire** : `d(x,z) ≤ d(x,y) + d(y,z)` pour tout
triplet. Sans elle, aucune garantie — un détour pourrait être moins cher qu'un trajet
direct, et le raisonnement s'effondre.

Vérifions un cas : `d(A,D) = 12` contre `d(A,B) + d(B,D) = 3 + 9 = 12`. ✅ Égalité,
donc respectée. Un autre : `d(A,C) = 8` contre `d(A,B) + d(B,C) = 3 + 5 = 8`. ✅

**7. Pourquoi ne pas chercher l'exact**

| n | Force brute O(n!) | Held-Karp O(n²·2ⁿ) |
|---|---|---|
| 5 | 120 | 25 × 32 = 800 |
| 20 | 2,4 × 10¹⁸ | 400 × 10⁶ ≈ 4 × 10⁸ |

Pour **n = 5**, la force brute est plus rapide que Held-Karp : sur de si petites
instances, l'exact est immédiat.

Pour **n = 20**, la force brute demande 2,4 × 10¹⁸ opérations — à 10⁹ opérations
par seconde, cela ferait **76 ans**. Held-Karp descend à 4 × 10⁸, soit moins d'une
seconde, mais sa consommation mémoire en `O(n·2ⁿ)` devient vite prohibitive.

Au-delà de quelques dizaines de villes, **seule l'approximation reste praticable**.
D'où l'intérêt d'une garantie chiffrée : on ne connaît pas l'optimum, mais on sait
qu'on n'en est pas à plus du double.
MD,
                'rubric' => [
                    ['label' => 'Les cinq degrés, avec vérification par la somme = 2m', 'points' => 1],
                    ['label' => 'Théorème d’Euler énoncé avec la connexité et les deux cas', 'points' => 2],
                    ['label' => 'Conclusion : chemin oui, cycle non, deux sommets impairs', 'points' => 1],
                    ['label' => 'Un chemin eulérien effectif, de A à B, les 8 arêtes', 'points' => 2],
                    ['label' => 'Kruskal : tableau, ACM de poids 17', 'points' => 3],
                    ['label' => 'Tournée par parcours en profondeur, coût 27', 'points' => 3],
                    ['label' => 'Garantie du facteur 2 énoncée et démontrée', 'points' => 2],
                    ['label' => 'Inégalité triangulaire citée comme hypothèse, encadrement 17 ≤ opt ≤ 27', 'points' => 1],
                    ['label' => 'Complexités chiffrées pour n = 5 et n = 20', 'points' => 3],
                ],
            ]],

            /* ============ SPP Recur ============ */
            ['SPP', 'Recur', [
                'title' => 'Récurrence simple, forte, et renforcement',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'statement' => <<<'MD'
**1.** Démontrez par récurrence que pour tout `n ≥ 1` :
`1 + 2 + … + n = n(n+1)/2`. *(3 pts)*

**2.** Soit la suite `u₀ = 2` et `u_{n+1} = (u_n + 2/u_n) / 2`.
Démontrez que `u_n ≥ √2` pour tout n. *(4 pts)*

**3.** Démontrez par récurrence **forte** que tout entier `n ≥ 2` s'écrit comme
un produit de nombres premiers. Expliquez pourquoi la récurrence simple ne
suffirait pas. *(4 pts)*

**4.** On veut montrer que la suite `v₀ = 0`, `v_{n+1} = (v_n + 6)/2` vérifie
`v_n < 6`. Menez la récurrence. Puis montrez que la propriété
« `v_n < 6` » **ne suffit pas** à prouver que la suite est croissante, et
proposez un **renforcement** qui donne les deux d'un coup. *(5 pts)*

Rédaction attendue : énoncer P(n) en toutes lettres, cas de base, hypothèse,
pas, conclusion — et signaler **où** l'hypothèse est utilisée.
MD,
                'hint' => "Question 4 : essayez de démontrer la croissance seule, vous verrez précisément où ça bloque. Le renforcement consiste à mettre les deux propriétés dans une seule P(n).",
                'method' => <<<'MD'
Pour chaque question, appliquez le gabarit en cinq temps :

1. « Soit P(n) la propriété : … » — en toutes lettres.
2. Cas de base, vérifié explicitement.
3. « Supposons P(n) vraie pour un n fixé. »
4. Démontrer P(n+1), en écrivant **« par hypothèse de récurrence »** à l'endroit exact.
5. « Par récurrence, P(n) est vraie pour tout n. »
MD,
                'solution' => <<<'MD'
**1. La somme des n premiers entiers**

Soit **P(n)** : « `1 + 2 + … + n = n(n+1)/2` ».

*Cas de base.* Pour n = 1 : la somme vaut 1, et `1(1+1)/2 = 1`. ✅

*Hypothèse.* Supposons P(n) vraie pour un `n ≥ 1` fixé.

*Pas.*
```
1 + 2 + … + n + (n+1)
  = n(n+1)/2 + (n+1)            [par hypothèse de récurrence]
  = (n+1) · (n/2 + 1)
  = (n+1)(n+2)/2
```
C'est bien `P(n+1)`. ✅

*Conclusion.* Par récurrence, P(n) est vraie pour tout `n ≥ 1`. ∎

**2. La suite de Héron**

Soit **P(n)** : « `u_n ≥ √2` ».

*Cas de base.* `u₀ = 2 ≥ √2 ≈ 1,414`. ✅

*Hypothèse.* Supposons `u_n ≥ √2` pour un n fixé. En particulier `u_n > 0`.

*Pas.* Montrons `u_{n+1} ≥ √2`, c'est-à-dire `(u_n + 2/u_n)/2 ≥ √2`.

Posons `x = u_n > 0`. L'inégalité équivaut à `x + 2/x ≥ 2√2`, soit, en multipliant
par `x > 0` :
```
x² + 2 ≥ 2√2 · x
x² − 2√2 x + 2 ≥ 0
(x − √2)² ≥ 0
```
Ce qui est vrai pour tout réel x. ✅

*Remarque.* L'hypothèse de récurrence n'a servi qu'à garantir `u_n > 0`, ce qui
autorise la multiplication sans changer le sens de l'inégalité. C'est un point à
signaler : sans elle, la manipulation serait invalide.

*Conclusion.* Par récurrence, `u_n ≥ √2` pour tout n. ∎

**3. Décomposition en facteurs premiers**

Soit **P(n)** : « n s'écrit comme un produit de nombres premiers ».

*Récurrence forte.* Supposons P(k) vraie pour tout `2 ≤ k < n`.

*Deux cas.*
- Si **n est premier**, il est lui-même un produit d'un seul facteur premier. ✅
- Sinon, n est composé : `n = a · b` avec `2 ≤ a < n` et `2 ≤ b < n`.
  **Par hypothèse de récurrence forte appliquée à a et à b**, chacun s'écrit comme
  un produit de premiers. Leur concaténation donne une décomposition de n. ✅

*Conclusion.* Par récurrence forte, tout `n ≥ 2` se décompose. ∎

**Pourquoi la récurrence simple ne suffit pas.**

La récurrence simple ne donnerait que P(n−1) comme hypothèse. Or dans le cas composé,
on a besoin de P(a) et P(b) pour des valeurs `a` et `b` **quelconques** entre 2 et
n−1 — par exemple, pour n = 100 = 4 × 25, il faut P(4) et P(25), pas P(99).

**Dès que le pas invoque un rang autre que n−1, la récurrence forte est indispensable.**

**4. Renforcement**

*Première partie — la majoration.*

Soit **P(n)** : « `v_n < 6` ».

*Cas de base.* `v₀ = 0 < 6`. ✅

*Hypothèse.* Supposons `v_n < 6`.

*Pas.* `v_{n+1} = (v_n + 6)/2 < (6 + 6)/2 = 6`. ✅

*Conclusion.* `v_n < 6` pour tout n. ∎

*Seconde partie — la croissance ne se démontre pas seule.*

Essayons **Q(n)** : « `v_n ≤ v_{n+1}` ».

Hypothèse : `v_n ≤ v_{n+1}`. Alors
```
v_{n+1} = (v_n + 6)/2 ≤ (v_{n+1} + 6)/2 = v_{n+2}
```
Cela **fonctionne** ici, parce que la fonction `x ↦ (x+6)/2` est croissante.
Le cas de base demande `v₀ ≤ v₁`, soit `0 ≤ 3`. ✅

Donc Q se démontre bien seule sur cet exemple.

*Le cas où le renforcement est nécessaire.* Supposons qu'on veuille prouver
directement la **convergence vers 6**, c'est-à-dire encadrer `6 − v_n`. La propriété
« `v_n < 6` » seule ne dit rien de la vitesse.

**Renforçons** en posant **R(n)** : « `0 ≤ v_n < 6` **et** `6 − v_n = 6/2ⁿ` ».

*Cas de base.* `v₀ = 0`, et `6 − 0 = 6 = 6/2⁰`. ✅

*Hypothèse.* Supposons R(n).

*Pas.*
```
6 − v_{n+1} = 6 − (v_n + 6)/2
            = (12 − v_n − 6)/2
            = (6 − v_n)/2
            = (6/2ⁿ)/2            [par hypothèse de récurrence]
            = 6/2^{n+1}
```
Et comme `6/2^{n+1} > 0`, on a bien `v_{n+1} < 6`. ✅

*Conclusion.* La propriété renforcée donne d'un coup la majoration, la positivité,
la croissance (puisque `6 − v_n` décroît strictement) **et** la vitesse de
convergence — quatre résultats pour une seule récurrence.

**C'est le principe du renforcement :** une propriété plus forte à démontrer donne
aussi une hypothèse plus riche au moment du pas.
MD,
                'rubric' => [
                    ['label' => 'Q1 : P(n) énoncé, cas de base, pas, conclusion', 'points' => 3],
                    ['label' => 'Q2 : le pas ramené à (x − √2)² ≥ 0', 'points' => 3],
                    ['label' => 'Q2 : le rôle de l’hypothèse (garantir u_n > 0) est signalé', 'points' => 1],
                    ['label' => 'Q3 : les deux cas, premier et composé', 'points' => 2],
                    ['label' => 'Q3 : l’hypothèse forte appliquée à a et b quelconques', 'points' => 1],
                    ['label' => 'Q3 : la simple ne suffit pas car le pas invoque un rang ≠ n−1', 'points' => 1],
                    ['label' => 'Q4 : majoration démontrée', 'points' => 2],
                    ['label' => 'Q4 : renforcement proposé avec 6 − v_n = 6/2ⁿ', 'points' => 2],
                    ['label' => 'Q4 : le pas de la version renforcée mené à terme', 'points' => 1],
                ],
            ]],

            /* ============ SPP Induction ============ */
            ['SPP', 'Induction', [
                'title' => 'Induction structurelle sur listes et arbres',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 5,
                'statement' => <<<'MD'
Définitions :

```whyml
type list 'a = Nil | Cons 'a (list 'a)

function length (l: list 'a) : int =
  match l with Nil -> 0 | Cons _ r -> 1 + length r end

function append (l1 l2: list 'a) : list 'a =
  match l1 with Nil -> l2 | Cons x r -> Cons x (append r l2) end

function reverse (l: list 'a) : list 'a =
  match l with Nil -> Nil | Cons x r -> append (reverse r) (Cons x Nil) end
```

**1.** Démontrez `append l Nil = l` pour toute liste `l`. *(3 pts)*

**2.** Démontrez l'associativité :
`append (append l1 l2) l3 = append l1 (append l2 l3)`. *(4 pts)*

**3.** Démontrez `length (reverse l) = length l`.
*Indication : vous aurez besoin du lemme de la question précédente sur `length` et
`append`.* *(4 pts)*

**4.** Sur le type `arbre 'a = Feuille | Noeud (arbre 'a) 'a (arbre 'a)`, définissez
`taille` et `hauteur`, puis démontrez que `taille a ≤ 2^{hauteur a} − 1`. *(5 pts)*

Rédaction : un cas par constructeur, chaque égalité justifiée en marge, et la mention
**« par hypothèse d'induction »** à l'endroit exact.
MD,
                'hint' => "Question 4 : le cas inductif dispose de **deux** hypothèses, une par sous-arbre. Et souvenez-vous que `max(x,y) ≤ z` équivaut à `x ≤ z et y ≤ z`.",
                'method' => <<<'MD'
Pour chaque preuve :

1. Énoncez **P(l)** en toutes lettres, en quantifiant à l'intérieur les variables
   sur lesquelles vous ne faites **pas** l'induction.
2. Un cas par constructeur du type.
3. Dans chaque cas, déroulez les définitions **une étape à la fois**, en justifiant
   chaque égalité en marge.
4. Signalez explicitement l'usage de l'hypothèse.
MD,
                'solution' => <<<'MD'
**1. `append l Nil = l`**

Soit **P(l)** : « `append l Nil = l` ».

*Cas `Nil`.*
```
append Nil Nil
  = Nil                          [déf. append, cas Nil]
```
✅

*Cas `Cons x r`.* Hypothèse : `append r Nil = r`.
```
append (Cons x r) Nil
  = Cons x (append r Nil)        [déf. append, cas Cons]
  = Cons x r                     [par hypothèse d'induction]
```
✅

*Conclusion.* Par induction structurelle sur l, `append l Nil = l`. ∎

**2. Associativité**

Soit **P(l1)** : « `∀l2 l3, append (append l1 l2) l3 = append l1 (append l2 l3)` ».

*Remarquez la quantification sur l2 et l3 à l'intérieur de P.* L'induction porte
sur `l1` seul, car c'est sur lui que `append` filtre.

*Cas `Nil`.*
```
append (append Nil l2) l3
  = append l2 l3                          [déf. append, cas Nil]
  = append Nil (append l2 l3)             [déf. append, cas Nil, à rebours]
```
✅

*Cas `Cons x r`.* Hypothèse : `∀l2 l3, append (append r l2) l3 = append r (append l2 l3)`.
```
append (append (Cons x r) l2) l3
  = append (Cons x (append r l2)) l3      [déf. append, cas Cons]
  = Cons x (append (append r l2) l3)      [déf. append, cas Cons]
  = Cons x (append r (append l2 l3))      [par hypothèse d'induction]
  = append (Cons x r) (append l2 l3)      [déf. append, cas Cons, à rebours]
```
✅ ∎

**3. `length (reverse l) = length l`**

*Lemme préalable* — `length (append l1 l2) = length l1 + length l2`.
Il se démontre par induction sur l1, exactement comme au chapitre Calculs.

Soit **P(l)** : « `length (reverse l) = length l` ».

*Cas `Nil`.*
```
length (reverse Nil)
  = length Nil                            [déf. reverse, cas Nil]
  = 0
```
✅

*Cas `Cons x r`.* Hypothèse : `length (reverse r) = length r`.
```
length (reverse (Cons x r))
  = length (append (reverse r) (Cons x Nil))       [déf. reverse, cas Cons]
  = length (reverse r) + length (Cons x Nil)       [par le lemme]
  = length (reverse r) + 1                         [déf. length]
  = length r + 1                                   [par hypothèse d'induction]
  = length (Cons x r)                              [déf. length, à rebours]
```
✅ ∎

**Ce que montre cette preuve :** on utilise **deux** résultats, le lemme et
l'hypothèse d'induction, et il faut signaler lequel sert à chaque ligne.

**4. Arbres**

*Définitions.*
```whyml
function taille (a: arbre 'a) : int =
  match a with Feuille -> 0 | Noeud g _ d -> 1 + taille g + taille d end

function hauteur (a: arbre 'a) : int =
  match a with Feuille -> 0 | Noeud g _ d -> 1 + max (hauteur g) (hauteur d) end
```

Soit **P(a)** : « `taille a ≤ 2^{hauteur a} − 1` ».

*Cas `Feuille`.*
```
taille Feuille = 0
2^{hauteur Feuille} − 1 = 2⁰ − 1 = 0
```
`0 ≤ 0`. ✅

*Cas `Noeud g x d`.* **Deux hypothèses** — une par sous-arbre :
`taille g ≤ 2^{hauteur g} − 1` et `taille d ≤ 2^{hauteur d} − 1`.

Posons `h = hauteur (Noeud g x d) = 1 + max(hauteur g, hauteur d)`.
On a donc `hauteur g ≤ h − 1` et `hauteur d ≤ h − 1`.

```
taille (Noeud g x d)
  = 1 + taille g + taille d                            [déf. taille]
  ≤ 1 + (2^{hauteur g} − 1) + (2^{hauteur d} − 1)      [par les deux hypothèses]
  ≤ 1 + (2^{h−1} − 1) + (2^{h−1} − 1)                  [car hauteur g, hauteur d ≤ h−1]
  = 1 + 2 · 2^{h−1} − 2
  = 2^h − 1
```
✅

*Conclusion.* Par induction structurelle, `taille a ≤ 2^{hauteur a} − 1` pour tout
arbre a. ∎

**Interprétation.** L'égalité est atteinte pour un arbre **parfaitement équilibré et
complet** : à hauteur h, il contient exactement `2^h − 1` nœuds. C'est la borne
supérieure du nombre de nœuds à hauteur donnée — et, symétriquement, elle donne la
hauteur **minimale** d'un arbre à n nœuds : `hauteur ≥ log₂(n+1)`.
MD,
                'rubric' => [
                    ['label' => 'Q1 : deux cas, égalités justifiées en marge', 'points' => 3],
                    ['label' => 'Q2 : P(l1) quantifie l2 et l3 à l’intérieur', 'points' => 1],
                    ['label' => 'Q2 : le cas Cons mené à terme, hypothèse signalée', 'points' => 3],
                    ['label' => 'Q3 : le lemme sur length et append est invoqué explicitement', 'points' => 2],
                    ['label' => 'Q3 : le cas Cons distingue lemme et hypothèse d’induction', 'points' => 2],
                    ['label' => 'Q4 : taille et hauteur correctement définies', 'points' => 2],
                    ['label' => 'Q4 : **deux** hypothèses d’induction, une par sous-arbre', 'points' => 2],
                    ['label' => 'Q4 : la majoration par 2^{h−1} est justifiée', 'points' => 1],
                ],
            ]],
        ];
    }
}