<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Les treize derniers chapitres sans exercice.
 *
 * Volume calibré sur le poids au barème : un exercice consistant sur MIA Ch3
 * et Ch5, plus court sur les chapitres rares, mais aucun laissé vide — un
 * chapitre sans pratique plafonne à 65 % de maîtrise, quelle que soit sa fiche.
 */
class ExercicesRestantsSeeder extends Seeder
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
                $exo + ['chapter_id' => $chapter->id, 'position' => 320]
            );
        }
    }

    /* ==================================================================== */

    private function exercices(): array
    {
        return [

            /* ============ MIA Ch3 — Recherche (poids 4) ============ */
            ['MIA', 'Ch3', [
                'title' => 'Dérouler largeur, profondeur et A* sur le même graphe',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'statement' => <<<'MD'
Graphe orienté valué, départ **S**, but **G** :

`S→A (2)`, `S→B (5)`, `A→C (4)`, `A→D (1)`, `B→D (2)`,
`C→G (3)`, `D→G (6)`, `B→E (3)`, `E→G (2)`

Heuristique : `h(S)=7, h(A)=6, h(B)=4, h(C)=3, h(D)=5, h(E)=2, h(G)=0`.

**1.** Déroulez le **parcours en largeur**. Ordre de visite, et chemin trouvé
vers G. Est-il de coût minimal ? *(3 pts)*

**2.** Déroulez le **parcours en profondeur**, voisins par ordre alphabétique.
Chemin trouvé, et coût. *(3 pts)*

**3.** Déroulez **A\*** en tableau : OUVERTS avec le détail `g + h = f` pour chaque
nœud, et le nœud extrait à chaque itération. *(5 pts)*

**4.** Chemin optimal et son coût. *(1 pt)*

**5.** L'heuristique est-elle **admissible** ? Vérifiez nœud par nœud, et dites ce
que cela implique. *(4 pts)*

**6.** Que deviendrait A\* si l'on posait `h(n) = 0` partout ? *(2 pts)*
MD,
                'hint' => "Question 5 : pour chaque nœud, calculez le coût réel du plus court chemin restant jusqu'à G, et comparez à h. Un seul nœud où h dépasse suffit à casser l'admissibilité.",
                'method' => <<<'MD'
1. **Largeur** : une file. Le chemin trouvé minimise le nombre d'**arcs**, pas le coût.
2. **Profondeur** : une pile. On descend au plus profond avant de revenir.
3. **A\*** : à chaque tour, extraire le nœud de `f = g + h` minimal. Notez les mises
   à jour quand un meilleur chemin est trouvé vers un nœud déjà connu.
5. Calculez `h*(n)` — le coût réel restant — pour chaque nœud, en partant de G.
MD,
                'solution' => <<<'MD'
**1. Parcours en largeur**

| Niveau | Sommets |
|---|---|
| 0 | S |
| 1 | A, B |
| 2 | C, D, E |
| 3 | G |

**Ordre de visite : S, A, B, C, D, E, G.**

Chemin trouvé : le premier atteignant G. Depuis C : **S → A → C → G**,
coût `2 + 4 + 3 = 9`.

**Non, ce n'est pas le coût minimal.** Le BFS minimise le **nombre d'arcs** (ici 3),
pas la somme des poids. Le graphe étant **pondéré**, le BFS ne garantit rien sur
le coût.

**2. Parcours en profondeur**

Depuis S, voisins par ordre alphabétique :
```
S → A → C → G
```
**Chemin : S → A → C → G**, coût `2 + 4 + 3 = 9`.

Le DFS s'arrête au premier but atteint. Aucune garantie d'optimalité — il aurait
pu tomber sur un chemin bien pire.

**3. A\* déroulé**

| Tour | OUVERTS (g + h = f) | Extrait | Successeurs ajoutés ou mis à jour |
|---|---|---|---|
| 1 | S : 0+7=**7** | **S** | A : 2+6=8 · B : 5+4=9 |
| 2 | A : **8** · B : 9 | **A** | C : 6+3=9 · D : 3+5=8 |
| 3 | D : **8** · B : 9 · C : 9 | **D** | G par D : 9+0=9 |
| 4 | B : 9 · C : 9 · G : 9 | **B** *(premier inséré à égalité)* | D par B : 7+5=12 → écarté, 8 < 12 · E : 8+2=10 |
| 5 | C : **9** · G : 9 · E : 10 | **C** | G par C : 9+0=9 → égal, pas de mise à jour |
| 6 | G : **9** · E : 10 | **G** | **but atteint** |

**4. Chemin optimal**

Deux chemins de coût 9 coexistent :
- **S → A → D → G** : `2 + 1 + 6 = 9`
- **S → A → C → G** : `2 + 4 + 3 = 9`

**Coût optimal : 9.**

*(À vérifier :* `S → B → E → G` = `5 + 3 + 2 = 10`. Plus cher.)

**5. Admissibilité**

Calculons `h*(n)`, le coût réel du plus court chemin de n à G :

| Nœud | h(n) | h*(n) | h ≤ h* ? |
|---|---|---|---|
| S | 7 | 9 | ✅ |
| A | 6 | 7 (A→D→G = 7, A→C→G = 7) | ✅ |
| B | 4 | 5 (B→E→G = 5, B→D→G = 8) | ✅ |
| C | 3 | 3 | ✅ (égalité permise) |
| D | 5 | 6 | ✅ |
| E | 2 | 2 | ✅ |
| G | 0 | 0 | ✅ |

**L'heuristique est admissible** : elle ne surestime jamais le coût réel restant.

**Ce que cela implique : A\* est optimal.** Le chemin de coût 9 qu'il a trouvé est
donc bien le meilleur — et l'on peut l'affirmer sans avoir exploré tout le graphe.

*Sans admissibilité*, A\* pourrait figer prématurément un nœud et manquer l'optimum.
C'est la condition, et elle doit être **vérifiée**, pas supposée.

**6. Avec h(n) = 0 partout**

`f(n) = g(n) + 0 = g(n)`. A\* extrait alors toujours le nœud de **coût réel minimal
depuis le départ** : c'est exactement **l'algorithme de Dijkstra**.

`h = 0` est trivialement admissible (0 ≤ h* toujours), donc **A\* reste optimal**.
Mais il perd tout **guidage** : il explore uniformément dans toutes les directions
au lieu de se diriger vers le but, et visite donc beaucoup plus de nœuds.

C'est le compromis du chapitre : plus l'heuristique est informée, moins on explore —
à condition qu'elle reste admissible.
MD,
                'rubric' => [
                    ['label' => 'BFS : ordre de visite par niveaux', 'points' => 2],
                    ['label' => 'BFS : le chemin trouvé n’est pas de coût minimal, avec la raison', 'points' => 1],
                    ['label' => 'DFS : chemin et coût', 'points' => 3],
                    ['label' => 'A* : tableau avec le détail g + h = f pour chaque nœud', 'points' => 3],
                    ['label' => 'A* : nœud extrait signalé, mises à jour justifiées', 'points' => 2],
                    ['label' => 'Chemin optimal de coût 9', 'points' => 1],
                    ['label' => 'Admissibilité vérifiée nœud par nœud avec h*', 'points' => 3],
                    ['label' => 'Conclusion : A* est optimal parce que h est admissible', 'points' => 1],
                    ['label' => 'h = 0 donne Dijkstra, optimal mais sans guidage', 'points' => 2],
                ],
            ]],

            /* ============ MIA Ch5 — Systèmes experts ============ */
            ['MIA', 'Ch5', [
                'title' => 'Chaînage avant et arrière sur un diagnostic',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 3,
                'statement' => <<<'MD'
Système expert de diagnostic automobile.

```
R1 : batterie_faible ∧ demarreur_ok    →  ne_demarre_pas
R2 : phares_faibles                    →  batterie_faible
R3 : bruit_clic                        →  demarreur_ok
R4 : ne_demarre_pas ∧ garantie         →  appeler_garage
R5 : reservoir_vide                    →  ne_demarre_pas
R6 : appeler_garage                    →  facture_zero
```

Faits initiaux : **{ phares_faibles, bruit_clic, garantie }**. But : **facture_zero**.

**1.** Déroulez le **chaînage avant** en tableau : cycle, ensemble de conflit,
règle choisie, fait ajouté. Précisez votre stratégie de résolution de conflit et la
condition d'arrêt. *(5 pts)*

**2.** Donnez l'**arbre de preuve** du chaînage arrière pour `facture_zero`. *(4 pts)*

**3.** Combien de règles chaque méthode a-t-elle examinées ? Laquelle est la plus
économique ici, et pourquoi ? *(3 pts)*

**4.** On ajoute le fait `reservoir_vide`. Le chaînage avant change-t-il de
conclusion ? Et l'ensemble de conflit ? *(3 pts)*
MD,
                'hint' => "Question 3 : comptez, pour le chaînage avant, toutes les règles testées à chaque cycle — pas seulement celles retenues. Le chaînage arrière, lui, ne regarde que celles qui concluent sur le but courant.",
                'method' => <<<'MD'
1. À chaque cycle : lister **toutes** les règles applicables (ensemble de conflit),
   en choisir une selon une stratégie annoncée, ajouter sa conclusion.
2. Partir du but, chercher les règles qui le concluent, remonter récursivement
   jusqu'aux faits.
4. R5 conclut sur un fait déjà établi : demandez-vous si cela change quelque chose
   à la conclusion, et si cela change l'ensemble de conflit.
MD,
                'solution' => <<<'MD'
**1. Chaînage avant**

Stratégie : **première règle applicable dans l'ordre R1 → R6**.
Base initiale : `{ phares_faibles, bruit_clic, garantie }`.

| Cycle | Ensemble de conflit | Règle choisie | Fait ajouté | Base de faits |
|---|---|---|---|---|
| 1 | R2, R3 | **R2** | batterie_faible | + batterie_faible |
| 2 | R3 | **R3** | demarreur_ok | + demarreur_ok |
| 3 | R1 | **R1** | ne_demarre_pas | + ne_demarre_pas |
| 4 | R4 | **R4** | appeler_garage | + appeler_garage |
| 5 | R6 | **R6** | facture_zero | + **facture_zero** |

**Arrêt au cycle 5** : le but `facture_zero` est atteint.

*Note sur le cycle 1 :* R2 et R3 sont toutes deux applicables. La stratégie
« première dans l'ordre » retient R2. Une stratégie différente donnerait un ordre
différent mais **la même conclusion** — c'est la propriété de confluence de ce
système.

**2. Chaînage arrière**

```
But : facture_zero
└── R6 : appeler_garage
    └── appeler_garage
        └── R4 : ne_demarre_pas ∧ garantie
            ├── ne_demarre_pas
            │   └── R1 : batterie_faible ∧ demarreur_ok
            │       ├── batterie_faible
            │       │   └── R2 : phares_faibles
            │       │       └── phares_faibles  ✓ (fait initial)
            │       └── demarreur_ok
            │           └── R3 : bruit_clic
            │               └── bruit_clic  ✓ (fait initial)
            └── garantie  ✓ (fait initial)
```

**`facture_zero` est démontré** : toutes les feuilles sont des faits initiaux.

*Remarque.* R4 propose aussi R5 comme voie alternative pour `ne_demarre_pas`.
Le moteur essaie R1 en premier (ordre des règles) et réussit ; il n'explore donc
jamais R5. Si R1 avait échoué, il aurait tenté R5 — c'est le **backtracking**.

**3. Nombre de règles examinées**

*Chaînage avant.* À chaque cycle, le moteur teste **les six règles** pour construire
l'ensemble de conflit. Cinq cycles × 6 règles = **30 tests**.

*Chaînage arrière.* Le moteur ne regarde que les règles concluant sur le but courant :

| But | Règles examinées |
|---|---|
| facture_zero | R6 |
| appeler_garage | R4 |
| ne_demarre_pas | R1 (puis R5 si échec — non atteint) |
| batterie_faible | R2 |
| demarreur_ok | R3 |

**Cinq règles examinées** (six en comptant R5 si l'on considère qu'elle est repérée
comme alternative).

**Le chaînage arrière est bien plus économique ici**, parce qu'on a **un but précis**.
Il ne dérive que ce qui sert à le prouver.

Le chaînage avant serait préférable dans la situation inverse : beaucoup de faits,
pas de but particulier, et l'on veut savoir **tout** ce qu'on peut conclure.

**4. Avec `reservoir_vide` en plus**

*La conclusion ne change pas.* `facture_zero` est toujours démontré, et rien de
nouveau n'apparaît au-delà : R5 conclut `ne_demarre_pas`, qui était déjà établi.

*L'ensemble de conflit, lui, change.* Dès le cycle 1, R5 devient applicable :

| Cycle | Ensemble de conflit | Règle choisie | Fait ajouté |
|---|---|---|---|
| 1 | **R2, R3, R5** | R2 | batterie_faible |
| 2 | **R3, R5** | R3 | demarreur_ok |
| 3 | **R1, R5** | R1 | ne_demarre_pas |
| 4 | R4 | R4 | appeler_garage |
| 5 | R6 | R6 | **facture_zero** |

*Un point à signaler :* à partir du cycle 3, R5 devient **inutile** — sa conclusion
est déjà dans la base. Un moteur bien conçu la retire de l'ensemble de conflit :
une règle dont la conclusion est déjà connue n'apporte rien. C'est un critère
classique de résolution de conflit, dit de **nouveauté**.
MD,
                'rubric' => [
                    ['label' => 'Tableau à quatre colonnes, cinq cycles', 'points' => 2],
                    ['label' => "L'ensemble de conflit est donné à chaque cycle", 'points' => 1],
                    ['label' => 'Stratégie de résolution de conflit et arrêt annoncés', 'points' => 2],
                    ['label' => 'Arbre de preuve complet, feuilles = faits initiaux', 'points' => 3],
                    ['label' => 'R5 mentionnée comme voie alternative non explorée', 'points' => 1],
                    ['label' => 'Comptage des règles examinées dans les deux sens', 'points' => 2],
                    ['label' => 'Le chaînage arrière est plus économique, avec la raison', 'points' => 1],
                    ['label' => 'La conclusion ne change pas, l’ensemble de conflit si', 'points' => 3],
                ],
            ]],

            /* ============ MIA Ch6 — Jeux ============ */
            ['MIA', 'Ch6', [
                'title' => 'Minimax et alpha-bêta sur un arbre de jeu',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 4,
                'statement' => <<<'MD'
Arbre de jeu à trois niveaux. La racine est un nœud **MAX**, ses trois fils sont des
nœuds **MIN**, et chaque nœud MIN a trois feuilles.

```
                        MAX
            ╱            │            ╲
        MIN₁           MIN₂           MIN₃
      ╱  │  ╲        ╱  │  ╲        ╱  │  ╲
     3   5   6      7   4   5      8   2   9
```

**1.** Calculez la valeur de chaque nœud MIN, puis celle de la racine, par
**Minimax**. *(3 pts)*

**2.** Déroulez **alpha-bêta** de gauche à droite, en tableau : nœud visité, α, β,
et action. Signalez chaque coupure. *(6 pts)*

**3.** Quelles **feuilles ne sont pas évaluées** ? Combien en économise-t-on ? *(2 pts)*

**4.** L'élagage a-t-il changé le résultat ? Justifiez. *(2 pts)*

**5.** On réordonne les fils de la racine en `MIN₃, MIN₂, MIN₁`. L'élagage
est-il plus ou moins efficace ? Que faut-il en conclure ? *(3 pts)*
MD,
                'hint' => "Question 5 : alpha-bêta coupe d'autant plus qu'il rencontre tôt une bonne valeur. Demandez-vous quel nœud MIN a la plus grande valeur, et ce qui se passe si on le visite en premier.",
                'method' => <<<'MD'
1. Chaque MIN prend le minimum de ses feuilles, la racine le maximum des MIN.
2. Partez de `α = −∞`, `β = +∞`. Dans un nœud MIN, β descend ; dans MAX, α monte.
   Coupure dès que `α ≥ β`.
3. Comparez les feuilles visitées aux neuf feuilles totales.
MD,
                'solution' => <<<'MD'
**1. Minimax**

```
MIN₁ = min(3, 5, 6) = 3
MIN₂ = min(7, 4, 5) = 4
MIN₃ = min(8, 2, 9) = 2

Racine (MAX) = max(3, 4, 2) = 4
```

**Valeur du jeu : 4.**

**2. Alpha-bêta, de gauche à droite**

| Étape | Nœud | α | β | Action |
|---|---|---|---|---|
| 1 | Racine MAX | −∞ | +∞ | descendre vers MIN₁ |
| 2 | MIN₁ | −∞ | +∞ | descendre |
| 3 | feuille 3 | −∞ | **3** | β ← 3 |
| 4 | feuille 5 | −∞ | 3 | 5 > 3, β inchangé |
| 5 | feuille 6 | −∞ | 3 | β inchangé → **MIN₁ = 3** |
| 6 | Racine | **3** | +∞ | α ← 3, descendre vers MIN₂ |
| 7 | MIN₂ | 3 | +∞ | descendre |
| 8 | feuille 7 | 3 | **7** | β ← 7 |
| 9 | feuille 4 | 3 | **4** | β ← 4 |
| 10 | feuille 5 | 3 | 4 | 5 > 4, β inchangé → **MIN₂ = 4** |
| 11 | Racine | **4** | +∞ | α ← 4, descendre vers MIN₃ |
| 12 | MIN₃ | 4 | +∞ | descendre |
| 13 | feuille 8 | 4 | **8** | β ← 8 |
| 14 | feuille 2 | 4 | **2** | β ← 2 → **α ≥ β : COUPURE** |

**3. Feuilles non évaluées**

Une seule : **la feuille 9**, dernier fils de MIN₃.

**Économie : 1 feuille sur 9.**

*Pourquoi la coupure est légitime.* MIN₃ vaudra **au plus 2**, puisqu'il prend le
minimum et qu'il a déjà trouvé 2. Or MAX a déjà **4** garanti par MIN₂. MAX ne
choisira donc jamais MIN₃, quelle que soit la valeur de la dernière feuille.
L'évaluer serait du travail perdu.

**4. Le résultat**

**Inchangé : 4.**

L'élagage alpha-bêta ne modifie **jamais** la valeur calculée par Minimax. Il
supprime uniquement l'exploration de branches dont on peut prouver qu'elles
n'influenceront pas le résultat. C'est une optimisation **exacte**, pas une
approximation.

**5. Réordonner en MIN₃, MIN₂, MIN₁**

| Étape | Nœud | α | β | Action |
|---|---|---|---|---|
| 1-3 | MIN₃ : feuilles 8, 2, 9 | −∞ | 2 | **MIN₃ = 2**, 3 feuilles évaluées |
| 4 | Racine | **2** | +∞ | α ← 2 |
| 5-7 | MIN₂ : feuilles 7, 4, 5 | 2 | 4 | **MIN₂ = 4**, 3 feuilles évaluées |
| 8 | Racine | **4** | +∞ | α ← 4 |
| 9 | MIN₁ : feuille 3 | 4 | **3** | β ← 3 → **α ≥ β : COUPURE** |

**Feuilles évaluées : 7.** Feuilles économisées : **2** (les valeurs 5 et 6 de MIN₁).

**L'élagage est plus efficace** avec cet ordre.

**Ce qu'il faut en conclure.** L'efficacité d'alpha-bêta dépend entièrement de
l'**ordre d'exploration**. Rencontrer tôt un nœud de forte valeur fait monter α
rapidement, ce qui permet de couper davantage ensuite.

C'est pourquoi les programmes de jeu réels **trient les coups** avant de les
explorer, par une évaluation rapide et approximative. Avec un ordonnancement
parfait, la complexité tombe de **O(bᵈ)** à **O(b^{d/2})** — on voit deux fois
plus loin à temps égal.
MD,
                'rubric' => [
                    ['label' => 'Les trois valeurs MIN et la racine = 4', 'points' => 3],
                    ['label' => 'Tableau alpha-bêta avec α et β à chaque étape', 'points' => 4],
                    ['label' => 'La coupure signalée au moment où α ≥ β', 'points' => 2],
                    ['label' => 'Feuille 9 non évaluée, économie de 1 sur 9', 'points' => 2],
                    ['label' => 'Résultat inchangé, l’élagage est exact', 'points' => 2],
                    ['label' => 'Réordonnancement : 7 feuilles, 2 économisées', 'points' => 2],
                    ['label' => 'Conclusion : l’ordre détermine l’efficacité, O(b^{d/2}) au mieux', 'points' => 1],
                ],
            ]],

            /* ============ MIA Ch7 — Planification ============ */
            ['MIA', 'Ch7', [
                'title' => 'Chemin critique et marges d’un projet',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
Projet de développement, huit tâches :

| Tâche | Durée (jours) | Prédécesseurs |
|---|---|---|
| A — cahier des charges | 4 | — |
| B — maquettes | 3 | A |
| C — base de données | 5 | A |
| D — interface | 6 | B |
| E — API | 4 | C |
| F — intégration | 3 | D, E |
| G — tests | 4 | F |
| H — documentation | 2 | C |

**1.** Calculez les **dates au plus tôt** de chaque tâche. *(3 pts)*
**2.** Calculez les **dates au plus tard**. *(3 pts)*
**3.** Dressez le tableau des **marges** et identifiez le **chemin critique**. *(3 pts)*
**4.** Quelle est la **durée minimale** du projet ? *(1 pt)*
**5.** La tâche D prend 3 jours de retard. Le projet est-il retardé ? De combien ? *(2 pts)*
**6.** On peut réduire **une seule** tâche d'un jour. Laquelle choisir pour gagner
un jour sur le projet ? Justifiez. *(3 pts)*
MD,
                'hint' => "Question 6 : réduire une tâche non critique ne sert à rien. Et attention, réduire une tâche critique peut faire apparaître un nouveau chemin critique.",
                'method' => <<<'MD'
1. **Au plus tôt**, de gauche à droite, avec un **max** sur les prédécesseurs.
2. **Au plus tard**, de droite à gauche, avec un **min** sur les successeurs.
3. Marge = `tard − tôt`. Marge nulle = tâche critique.
6. Vérifiez que la tâche choisie est critique, puis recalculez pour confirmer.
MD,
                'solution' => <<<'MD'
**1. Dates au plus tôt** — de gauche à droite, avec un max :

```
tôt(A) = 0
tôt(B) = tôt(A) + 4 = 4
tôt(C) = tôt(A) + 4 = 4
tôt(D) = tôt(B) + 3 = 7
tôt(E) = tôt(C) + 5 = 9
tôt(H) = tôt(C) + 5 = 9
tôt(F) = max( tôt(D)+6, tôt(E)+4 ) = max(13, 13) = 13
tôt(G) = tôt(F) + 3 = 16
```

**Fin du projet : tôt(G) + 4 = 20.**

**2. Dates au plus tard** — de droite à gauche, avec un min, en partant de
`tard(G) = 16` :

```
tard(G) = 16
tard(H) = 20 − 2 = 18        (H n'a pas de successeur : fin du projet)
tard(F) = tard(G) − 3 = 13
tard(E) = tard(F) − 4 = 9
tard(D) = tard(F) − 6 = 7
tard(C) = min( tard(E)−5, tard(H)−5 ) = min(4, 13) = 4
tard(B) = tard(D) − 3 = 4
tard(A) = min( tard(B)−4, tard(C)−4 ) = min(0, 0) = 0
```

**3. Marges et chemin critique**

| Tâche | tôt | tard | marge | critique ? |
|---|---|---|---|---|
| A | 0 | 0 | **0** | ✅ |
| B | 4 | 4 | **0** | ✅ |
| C | 4 | 4 | **0** | ✅ |
| D | 7 | 7 | **0** | ✅ |
| E | 9 | 9 | **0** | ✅ |
| F | 13 | 13 | **0** | ✅ |
| G | 16 | 16 | **0** | ✅ |
| H | 9 | 18 | **9** | |

**Deux chemins critiques :**
- **A → B → D → F → G** : 4 + 3 + 6 + 3 + 4 = **20**
- **A → C → E → F → G** : 4 + 5 + 4 + 3 + 4 = **20**

Seule **H** dispose de marge — **9 jours**. On peut la démarrer n'importe quand
entre le jour 9 et le jour 18 sans conséquence.

**4. Durée minimale : 20 jours.**

**5. La tâche D prend 3 jours de retard**

D est **critique**, sa marge est nulle. Tout retard se répercute intégralement.

Nouveau `tôt(D) = 7`, durée 9 au lieu de 6, donc `tôt(F) = max(7+9, 9+4) = max(16, 13) = 16`.
Puis `tôt(G) = 19`, et fin à **23**.

**Le projet est retardé de 3 jours.** Il durera 23 jours.

*Remarque.* Le second chemin critique passait par E et donnait 13 pour F. Il est
maintenant dépassé : **A → B → D → F → G** devient le seul chemin critique.

**6. Réduire une tâche d'un jour**

*Première observation.* Réduire **H** ne sert à rien : elle a 9 jours de marge,
le projet n'en dépend pas.

*Deuxième observation, décisive.* Il y a **deux** chemins critiques. Réduire une
tâche appartenant à un seul des deux ne gagne rien : l'autre chemin continue
d'imposer 20 jours.

- Réduire **B** ou **D** (chemin 1 seulement) → le chemin 2 reste à 20. **Aucun gain.**
- Réduire **C** ou **E** (chemin 2 seulement) → le chemin 1 reste à 20. **Aucun gain.**

*La réponse.* Il faut réduire une tâche **commune aux deux chemins critiques** :
**A**, **F** ou **G**.

Vérifions avec **F** réduite à 2 jours :
```
tôt(F) = 13, durée 2 → tôt(G) = 15 → fin à 19
```
**Le projet passe à 19 jours. Gain : 1 jour.** ✅

**Conclusion : réduire A, F ou G.** C'est la leçon du chapitre — quand plusieurs
chemins critiques coexistent, seule une tâche commune à tous permet de gagner du temps.
MD,
                'rubric' => [
                    ['label' => 'Dates au plus tôt, avec le max sur les prédécesseurs', 'points' => 3],
                    ['label' => 'Dates au plus tard, avec le min sur les successeurs', 'points' => 3],
                    ['label' => 'Tableau des marges complet', 'points' => 2],
                    ['label' => 'Les **deux** chemins critiques identifiés', 'points' => 1],
                    ['label' => 'Durée minimale 20 jours', 'points' => 1],
                    ['label' => 'Retard de D : 3 jours répercutés, car marge nulle', 'points' => 2],
                    ['label' => 'H écartée car non critique', 'points' => 1],
                    ['label' => 'A, F ou G choisies car communes aux deux chemins critiques', 'points' => 2],
                ],
            ]],

            /* ============ MIA Ch9 — Méthodes incomplètes ============ */
            ['MIA', 'Ch9', [
                'title' => 'Recherche locale sur le problème des n reines',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
On place 4 reines sur un échiquier 4 × 4, une par colonne. Une configuration
s'écrit `[l₁, l₂, l₃, l₄]` où `lᵢ` est la ligne de la reine de la colonne i.

**1.** Définissez précisément la **fonction de qualité** et le **voisinage**.
Combien de voisins a une configuration ? *(3 pts)*

**2.** Partant de `[1, 1, 1, 1]`, comptez les conflits. *(2 pts)*

**3.** Déroulez **trois itérations** de recherche locale, en prenant à chaque fois
le meilleur voisin. *(4 pts)*

**4.** Qu'est-ce qu'un **optimum local** ? Donnez une configuration 4 × 4 qui en est
un, avec des conflits restants. *(3 pts)*

**5.** Citez **trois** méthodes pour s'en échapper, et expliquez le principe de
chacune en une phrase. *(3 pts)*

**6.** Cette méthode peut-elle **prouver** qu'un problème n'a pas de solution ?
Justifiez. *(2 pts)*
MD,
                'hint' => "Question 1 : deux reines de colonnes différentes s'attaquent si elles sont sur la même ligne, ou sur la même diagonale — c'est-à-dire si |lᵢ − lⱼ| = |i − j|.",
                'method' => <<<'MD'
1. La représentation par colonnes élimine d'office les conflits de colonne.
   Reste les lignes et les diagonales.
2. Comptez les **paires** en conflit, pas les reines.
3. À chaque itération, évaluez les voisins et retenez celui de qualité maximale.
MD,
                'solution' => <<<'MD'
**1. Qualité et voisinage**

*Fonction de qualité.* Le **nombre de paires de reines qui s'attaquent**.
On cherche à le **minimiser** ; l'objectif est **0**.

Deux reines des colonnes `i` et `j` (avec i ≠ j) s'attaquent si :
- **même ligne** : `lᵢ = lⱼ` ;
- **même diagonale** : `|lᵢ − lⱼ| = |i − j|`.

La représentation par colonnes élimine d'office les conflits de colonne.

*Voisinage.* Déplacer **une** reine dans sa colonne. Il y a 4 colonnes × 3 autres
lignes = **12 voisins**.

*(Formule générale : `n × (n−1)` voisins pour n reines.)*

**2. Conflits de `[1, 1, 1, 1]`**

Les quatre reines sont sur la **ligne 1**. Toutes les paires s'attaquent.

Nombre de paires : `C(4,2) = 6`.

**6 conflits.**

**3. Trois itérations**

*Itération 1.* Cherchons le meilleur voisin de `[1,1,1,1]`.

Essayons `[1,1,1,3]` — on déplace la reine de la colonne 4 en ligne 3 :
- lignes : (1,2), (1,3), (2,3) → 3 conflits
- diagonales : |1−1|=0 ≠ 1 ✓ · |1−3|=2, |1−4|=3 ✗ · |1−3|=2, |2−4|=2 → **conflit**
- Total : 3 + 1 = **4 conflits**

Essayons `[2,4,1,3]` — hors voisinage (deux reines déplacées). Restons rigoureux.

Le meilleur voisin à une modification : `[1,1,1,3]` ou `[3,1,1,1]`, à **4 conflits**.
Retenons **`[1,1,1,3]`, 4 conflits**.

*Itération 2.* Voisins de `[1,1,1,3]`.

`[1,4,1,3]` : lignes (1,3) → 1 conflit. Diagonales : |1−1|=0≠2 ✓ ·
|1−3|=2, |1−4|=3 ✓ · |4−1|=3, |2−3|=1 ✓ · |4−3|=1, |2−4|=2 ✓ · |1−3|=2, |3−4|=1 ✓
→ **1 conflit**.

Retenons **`[1,4,1,3]`, 1 conflit**.

*Itération 3.* Voisins de `[1,4,1,3]`.

`[2,4,1,3]` : lignes toutes distinctes → 0 conflit de ligne.
Diagonales : |2−4|=2, |1−2|=1 ✓ · |2−1|=1, |1−3|=2 ✓ · |2−3|=1, |1−4|=3 ✓ ·
|4−1|=3, |2−3|=1 ✓ · |4−3|=1, |2−4|=2 ✓ · |1−3|=2, |3−4|=1 ✓
→ **0 conflit.**

**Solution trouvée : `[2, 4, 1, 3]`.** ✅

**4. Optimum local**

**Une configuration dont aucun voisin n'est meilleur, sans être une solution.**

L'algorithme s'y arrête : il ne voit aucune amélioration possible, alors qu'il
reste des conflits.

*Exemple sur 4 × 4.* La configuration `[2, 4, 1, 3]` est une solution (0 conflit),
donc pas un optimum local piégeant. Prenons `[1, 3, 1, 3]` :
- lignes : (1,3) et (2,4) → 2 conflits
- diagonales : |1−3|=2, |1−2|=1 ✓ · |1−1|=0, |1−3|=2 ✓ · |1−3|=2, |1−4|=3 ✓ ·
  |3−1|=2, |2−3|=1 ✓ · |3−3|=0, |2−4|=2 ✓ · |1−3|=2, |3−4|=1 ✓
- **2 conflits**

Sur un échiquier 4 × 4, le voisinage est petit et l'on s'échappe souvent. Les
optima locaux deviennent réellement piégeants à partir de n = 8, où le paysage
comporte de nombreux plateaux — des zones où **tous** les voisins ont la même
qualité, sans direction d'amélioration.

**5. Trois échappatoires**

**Recuit simulé** — accepter parfois une solution **moins bonne**, avec une
probabilité `e^{−Δ/T}` où la température T **décroît** au fil du temps : on explore
largement au début, on se stabilise à la fin.

**Recherche tabou** — interdire de revenir sur les derniers coups joués, en les
conservant dans une **liste tabou** de taille fixe : on force la sortie de la cuvette.

**Algorithmes génétiques** — faire évoluer une **population** de solutions par
croisement et mutation : plusieurs points de départ explorent le paysage en parallèle.

*(Une quatrième, plus simple : le **redémarrage aléatoire** — repartir d'une
configuration au hasard quand on est bloqué.)*

**6. Prouver l'absence de solution ?**

**Non, jamais.**

Une méthode incomplète explore une **partie** de l'espace de recherche, choisie par
des mouvements locaux et parfois du hasard. Ne pas avoir trouvé de solution après
un million d'itérations ne prouve **rien** : peut-être n'a-t-on simplement pas
regardé au bon endroit.

Seule une méthode **complète** — backtracking exhaustif, programmation par
contraintes avec propagation — peut conclure à l'absence de solution, parce qu'elle
garantit d'avoir éliminé **tout** l'espace.

C'est le compromis fondamental du chapitre : on échange la **garantie** contre le
**temps**.
MD,
                'rubric' => [
                    ['label' => 'Fonction de qualité définie : nombre de paires en conflit', 'points' => 1],
                    ['label' => 'Condition de conflit diagonale |lᵢ − lⱼ| = |i − j|', 'points' => 1],
                    ['label' => 'Voisinage défini, 12 voisins', 'points' => 1],
                    ['label' => '[1,1,1,1] : 6 conflits, par C(4,2)', 'points' => 2],
                    ['label' => 'Trois itérations déroulées, conflits recomptés à chaque fois', 'points' => 4],
                    ['label' => 'Optimum local défini correctement', 'points' => 2],
                    ['label' => 'Un exemple avec conflits restants', 'points' => 1],
                    ['label' => 'Trois méthodes citées avec leur principe', 'points' => 3],
                    ['label' => 'Non, l’incomplet ne prouve jamais l’absence de solution', 'points' => 2],
                ],
            ]],

            /* ============ MIA Ch1 — Introduction ============ */
            ['MIA', 'Ch1', [
                'title' => 'Repères et vocabulaire de l’IA',
                'origin' => 'genere',
                'est_minutes' => 15,
                'difficulty' => 2,
                'statement' => <<<'MD'
**1.** Datez et situez la naissance de l'expression « intelligence artificielle ». *(1 pt)*

**2.** Distinguez le **test de Turing** de la **machine de Turing** : date, auteur,
objet, et module du Master où chacun est au programme. *(3 pts)*

**3.** Classez les neuf chapitres du module MIA selon l'approche
**symbolique** ou **connexionniste**. *(4 pts)*

**4.** Pour chacune des deux approches, donnez un **avantage** et un **inconvénient**. *(4 pts)*

**5.** Un système de recommandation prédit vos goûts à partir de vos achats
passés. Est-il symbolique ou connexionniste ? Et de quel type d'apprentissage
relève-t-il ? *(3 pts)*
MD,
                'hint' => "Question 2 : les deux notions sont du même auteur, à quatorze ans d'écart, et sans rapport l'une avec l'autre. L'une est au programme de MIA, l'autre d'EP.",
                'method' => <<<'MD'
2. Pour chacune : quelle année, quelle question elle pose, à quoi elle sert.
3. Une seule des neuf entre dans la seconde catégorie.
5. Deux questions distinctes : quelle approche, et quel type d'apprentissage
   (supervisé, non supervisé, par renforcement).
MD,
                'solution' => <<<'MD'
**1.** L'expression a été forgée en **1956**, à la **conférence de Dartmouth**
(États-Unis), par un groupe réuni autour de John McCarthy.

**Pas 1950** — c'est la date de l'article de Turing, antérieur à l'expression.

**2. Test de Turing contre machine de Turing**

| | Test de Turing | Machine de Turing |
|---|---|---|
| **Date** | 1950 | 1936 |
| **Auteur** | Alan Turing | Alan Turing |
| **Objet** | un **critère d'intelligence** : une machine est intelligente si un humain ne peut la distinguer d'un humain en conversation écrite | un **modèle de calcul** : ruban, tête de lecture, états, transitions |
| **Sert à** | définir ce qu'on entend par « penser » | définir ce qui est **calculable** |
| **Module** | **MIA**, chapitre 1 | **EP**, chapitre 3 |

Même auteur, deux notions **sans rapport**. Les confondre est la faute classique,
d'autant plus facile que les deux tombent — dans deux épreuves différentes,
à un jour d'intervalle cette année.

**3. Classement des chapitres**

| Chapitre | Approche |
|---|---|
| 0 — Prolog | **symbolique** |
| 1 — Introduction | transversal |
| 2 — Représentation des connaissances | **symbolique** |
| 3 — Algorithmes de recherche | **symbolique** |
| 4 — Programmation par contraintes | **symbolique** |
| 5 — Systèmes experts | **symbolique** |
| 6 — Algorithmes des jeux | **symbolique** |
| 7 — Planification | **symbolique** |
| **8 — Apprentissage** | **connexionniste et statistique** |
| 9 — Méthodes incomplètes | métaheuristiques (ni l'un ni l'autre strictement) |

Le module est très majoritairement **symbolique**. C'est cohérent avec le poids de
Prolog et des contraintes dans les annales.

**4. Avantages et inconvénients**

**Approche symbolique**

| Avantage | Inconvénient |
|---|---|
| Le raisonnement est **explicable** : on retrace pourquoi le système a conclu | Il faut **écrire les règles à la main**, ce qui devient impraticable sur des domaines vastes |
| On peut **prouver** des propriétés du système | **Fragile** face à l'incertitude et aux exceptions — d'où les logiques non monotones du chapitre 2 |

**Approche connexionniste**

| Avantage | Inconvénient |
|---|---|
| **Apprend seule** à partir d'exemples, sans règles écrites | **Boîte noire** : on ne sait pas expliquer une décision |
| Robuste au **bruit** et aux données imparfaites | Exige **beaucoup de données** étiquetées, et beaucoup de calcul |

**5. Le système de recommandation**

*Approche.* **Connexionniste et statistique.** Il n'y a aucune règle écrite du type
« qui achète X aime Y » : le système ajuste des paramètres numériques sur l'historique
des achats.

*Type d'apprentissage.* Cela dépend de la formulation exacte, et il faut le dire :

- Si l'on dispose de **notes explicites** données par les utilisateurs — chaque achat
  est étiqueté d'une appréciation — c'est de l'apprentissage **supervisé** : on
  prédit une note.
- Si l'on ne dispose que de l'**historique brut** et que l'on cherche des groupes
  d'utilisateurs aux goûts proches, c'est du **non supervisé** — du *clustering*.

En pratique, les systèmes de recommandation par **filtrage collaboratif** relèvent
plutôt du **non supervisé** : ils regroupent les utilisateurs similaires sans qu'aucune
étiquette ne leur soit fournie.

*Ce que le correcteur attend :* pas une réponse unique, mais la **distinction posée**
et le choix justifié par ce dont on dispose.
MD,
                'rubric' => [
                    ['label' => '1956, conférence de Dartmouth', 'points' => 1],
                    ['label' => 'Test de Turing : 1950, critère d’intelligence, module MIA', 'points' => 1],
                    ['label' => 'Machine de Turing : 1936, modèle de calcul, module EP', 'points' => 1],
                    ['label' => 'Les deux explicitement distinguées', 'points' => 1],
                    ['label' => 'Les neuf chapitres classés, seul le 8 en connexionniste', 'points' => 4],
                    ['label' => 'Un avantage et un inconvénient par approche', 'points' => 4],
                    ['label' => 'Recommandation : connexionniste, avec la raison', 'points' => 1],
                    ['label' => 'Le type d’apprentissage discuté selon les données disponibles', 'points' => 2],
                ],
            ]],

            /* ============ SPP Intro, Theories, Types, Calculs ============ */
            ['SPP', 'Intro', [
                'title' => 'Spécifier avant de coder',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
**1.** Expliquez en trois phrases pourquoi le test ne peut pas remplacer la preuve.
Citez la formule de Dijkstra. *(2 pts)*

**2.** Distinguez **correction partielle**, **terminaison** et **correction totale**.
Que garantit un triplet de Hoare seul ? *(3 pts)*

**3.** Écrivez en WhyML la spécification — **sans le code** — d'une fonction
`division(a, b)` qui rend le quotient entier et le reste. Précisez précondition,
postcondition, et ce qui garantit la terminaison. *(5 pts)*

**4.** Une spécification peut être **fausse** tout en étant prouvée. Donnez un
exemple : une spécification de `maximum` satisfaite par une fonction manifestement
incorrecte. *(3 pts)*

**5.** Qu'est-ce qu'une **obligation de preuve** ? Qui les engendre, qui les
démontre ? *(2 pts)*
MD,
                'hint' => "Question 4 : cherchez une spécification incomplète — une qui oublie de dire que le résultat doit appartenir au tableau, par exemple.",
                'method' => <<<'MD'
3. `requires` pour ce que l'appelant garantit, `ensures` pour ce que la fonction
   rend, `variant` pour la terminaison. Une division a besoin de `b ≠ 0`.
   Et la postcondition doit caractériser **complètement** quotient et reste.
4. Écrivez une spécification incomplète, puis une fonction absurde qui la satisfait.
MD,
                'solution' => <<<'MD'
**1. Test contre preuve**

Le test examine un **nombre fini** de cas et constate que le programme s'y comporte
bien. La preuve établit une propriété **pour toutes les entrées possibles**, y
compris celles qu'on n'a pas imaginées.

Un programme peut passer mille tests et échouer sur le mille-et-unième : le test ne
dit rien de ce qu'il n'a pas essayé.

> **« Le test peut révéler la présence de bugs, jamais leur absence. »** — Dijkstra

**2. Les trois niveaux**

| Niveau | Ce qui est garanti |
|---|---|
| **Correction partielle** | *si* le programme termine, le résultat vérifie la postcondition |
| **Terminaison** | le programme s'arrête sur toute entrée vérifiant la précondition |
| **Correction totale** | les deux à la fois |

**Un triplet de Hoare `{P} S {Q}` ne garantit que la correction partielle.**
Il ne dit rien du cas où S ne termine pas — et un programme qui boucle indéfiniment
satisfait trivialement n'importe quel triplet.

Pour la correction totale, il faut fournir un **variant** en plus.

**3. Spécification de la division**

```whyml
use int.Int
use int.EuclideanDivision

let division (a b: int) : (int, int)
  requires { b <> 0 }
  ensures  { let (q, r) = result in
               a = b * q + r
            /\ 0 <= r < abs b }
= ...
```

*Analyse de la spécification.*

- **Précondition** `b <> 0` — la division par zéro n'a pas de sens ; c'est à
  l'appelant de le garantir.
- **Postcondition, premier conjoint** `a = b * q + r` — c'est l'identité qui définit
  le quotient et le reste.
- **Postcondition, second conjoint** `0 <= r < abs b` — **indispensable**. Sans lui,
  `q = 0, r = a` satisferait la première égalité pour tout a. C'est cette contrainte
  qui rend le couple **unique**.

*Terminaison.* Si l'on implémente par soustractions répétées :
```whyml
variant { abs a - abs (b * q) }
```
Cette quantité est entière, minorée par 0, et décroît strictement à chaque
soustraction. La boucle termine donc.

Si l'on emploie l'opérateur natif `div`, la question ne se pose pas : il n'y a pas
de boucle.

**4. Une spécification fausse mais prouvable**

*La spécification incomplète :*
```whyml
let maximum (a: array int) : int
  requires { length a > 0 }
  ensures  { forall i. 0 <= i < length a -> result >= a[i] }
```

*La fonction absurde qui la satisfait :*
```whyml
= 1000000
```

Cette fonction rend toujours un million. Elle vérifie bien que le résultat est
supérieur ou égal à tous les éléments — pourvu qu'ils soient plus petits. Elle est
**prouvée correcte** au regard de cette spécification, et pourtant elle ne calcule
pas le maximum.

*Ce qui manquait :*
```whyml
  ensures  { exists i. 0 <= i < length a /\ result = a[i] }
```

Le résultat doit être **un élément du tableau**. Avec ce second `ensures`, la
fonction absurde ne passe plus.

**La leçon.** Prouver un programme, c'est démontrer qu'il respecte **une
spécification donnée**. Si la spécification est incomplète, la preuve ne garantit
rien d'intéressant. **Écrire la spécification est la partie difficile** — le code
vient après.

**5. Obligation de preuve**

Une **obligation de preuve** est une formule logique dont la validité garantit un
morceau de la correction du programme. Le programme est prouvé correct quand
**toutes** ses obligations sont démontrées.

*Qui les engendre ?* L'outil — ici **Why3** — les calcule automatiquement à partir
du code et de sa spécification, en appliquant les règles de la logique de Hoare.

*Qui les démontre ?* Des **solveurs automatiques** (Alt-Ergo, Z3, CVC4), appelés par
Why3. Celles qu'aucun solveur ne parvient à décharger sont laissées à l'utilisateur,
qui les démontre à la main ou aide l'outil en ajoutant des assertions intermédiaires.
MD,
                'rubric' => [
                    ['label' => 'Test fini contre preuve universelle, citation de Dijkstra', 'points' => 2],
                    ['label' => 'Les trois niveaux définis', 'points' => 2],
                    ['label' => 'Un triplet seul ne donne que la correction partielle', 'points' => 1],
                    ['label' => 'Précondition b ≠ 0', 'points' => 1],
                    ['label' => 'Postcondition a = b·q + r', 'points' => 2],
                    ['label' => 'Postcondition 0 ≤ r < |b|, avec la raison de son caractère indispensable', 'points' => 2],
                    ['label' => 'Spécification incomplète de maximum et fonction absurde qui la satisfait', 'points' => 2],
                    ['label' => 'Le `exists` manquant est identifié', 'points' => 1],
                    ['label' => 'Obligation de preuve : engendrée par Why3, déchargée par les solveurs', 'points' => 2],
                ],
            ]],

            ['SPP', 'Theories', [
                'title' => 'Théories, modèles, et ce que les solveurs décident',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 4,
                'statement' => <<<'MD'
**1.** Définissez **signature**, **théorie**, **modèle**. *(3 pts)*

**2.** Soit la signature `{ 0, s, + }` et la théorie T composée de :
```
A1 : ∀x. x + 0 = x
A2 : ∀x y. x + s(y) = s(x + y)
```
Démontrez `T ⊨ s(0) + s(0) = s(s(0))`. *(4 pts)*

**3.** Distinguez **cohérente**, **complète**, **décidable**. Donnez pour chacune
un exemple ou un contre-exemple. *(4 pts)*

**4.** L'arithmétique de **Presburger** (entiers avec `+` et `<`, sans multiplication)
est décidable ; celle de **Peano**, non. Qu'est-ce qui change ? *(3 pts)*

**5.** Pourquoi Why3 doit-il importer `use int.Int` avant de prouver `x + 0 = x` ? *(2 pts)*
MD,
                'hint' => "Question 2 : appliquez les axiomes un à un, en indiquant à chaque étape lequel vous utilisez et avec quelle substitution.",
                'method' => <<<'MD'
2. Réécrivez le terme de gauche pas à pas. `s(0) + s(0)` : quel axiome s'applique ?
   Avec quelle substitution ?
3. Trois propriétés indépendantes — ne pas les confondre est l'enjeu de la question.
MD,
                'solution' => <<<'MD'
**1. Les trois notions**

**Signature** — l'ensemble des symboles disponibles, avec leur **arité** : constantes,
symboles de fonction, symboles de prédicat.
*Exemple :* `{ 0 (constante), s (unaire), + (binaire) }`.

**Théorie** — un ensemble de **formules closes** sur cette signature, appelées les
**axiomes**. Elle fixe le sens des symboles.

**Modèle** — une **interprétation** qui associe un domaine aux variables, une valeur
à chaque constante, une fonction à chaque symbole de fonction, et qui rend **tous
les axiomes vrais**.

*Sans théorie, `+` n'est qu'un symbole binaire :* rien ne dit qu'il est commutatif,
ni associatif, ni quoi que ce soit d'autre. C'est l'axiomatisation qui le décide.

**2. Démonstration de `s(0) + s(0) = s(s(0))`**

```
s(0) + s(0)
  = s( s(0) + 0 )        [par A2, avec x := s(0) et y := 0]
  = s( s(0) )            [par A1, avec x := s(0)]
```

Donc `s(0) + s(0) = s(s(0))`, et cette égalité est vraie dans **tout modèle de T**,
puisqu'elle se dérive uniquement des axiomes.

**Donc `T ⊨ s(0) + s(0) = s(s(0))`.** ∎

*Lecture.* Avec l'interprétation usuelle — `0` vaut zéro, `s` est le successeur —
cela dit simplement que `1 + 1 = 2`. Mais la démonstration n'a pas eu besoin de
cette interprétation : elle vaut dans **tous** les modèles.

**3. Cohérente, complète, décidable**

| Propriété | Définition | Exemple |
|---|---|---|
| **Cohérente** | admet **au moins un modèle** ; de façon équivalente, ne démontre pas `faux` | Peano est cohérente |
| **Complète** | pour toute formule close φ, `T ⊨ φ` **ou** `T ⊨ ¬φ` — elle tranche tout | Presburger est complète ; **Peano ne l'est pas** (Gödel) |
| **Décidable** | il existe un **algorithme** qui décide si `T ⊨ φ` | Presburger est décidable ; **Peano ne l'est pas** |

*Le point à ne pas confondre.* Une théorie **incohérente** démontre **tout** — y
compris `faux` — donc elle est trivialement complète, et sans aucun intérêt.

*Et Peano est le contre-exemple à connaître :* **cohérente**, mais **ni complète ni
décidable**. C'est le premier théorème d'incomplétude de Gödel.

**4. Presburger contre Peano : la multiplication**

**La seule différence est la présence de la multiplication.**

- **Presburger** : entiers, `+`, `<`, `=`. **Décidable** — un algorithme existe,
  bien que de complexité doublement exponentielle.
- **Peano** : on ajoute `×`. **Indécidable**, et **incomplète** par Gödel.

*Pourquoi la multiplication change tout.* Avec l'addition seule, on ne peut pas
exprimer la notion de **divisibilité** de façon générale, ni coder les suites finies.
Avec la multiplication, on le peut — et l'on devient capable de coder à l'intérieur
de la théorie des énoncés sur la théorie elle-même. C'est l'**arithmétisation de la
syntaxe**, qui ouvre la porte à la diagonalisation de Gödel et à l'énoncé
autoréférentiel « cette formule n'est pas démontrable ».

Un symbole de plus, et la théorie devient capable de parler d'elle-même — donc
indécidable.

**5. Pourquoi `use int.Int` dans Why3**

Parce que **sans théorie, les symboles n'ont pas de sens**.

`use int.Int` importe la théorie des entiers : les axiomes qui rendent `+`, `*`, `<`
conformes à leur interprétation usuelle — associativité, commutativité, neutralité
de 0, etc.

Sans cet import, `x + 0 = x` n'est **pas démontrable** : rien n'a dit que `0` est
neutre pour `+`. Le solveur ne peut rien en faire, car pour lui `+` est un symbole
binaire quelconque et `0` une constante quelconque.

C'est exactement le rôle d'une théorie : donner un sens aux symboles.
MD,
                'rubric' => [
                    ['label' => 'Signature, théorie, modèle correctement définis', 'points' => 3],
                    ['label' => 'La dérivation en deux étapes, axiomes nommés avec leur substitution', 'points' => 3],
                    ['label' => 'Conclusion : vrai dans tout modèle, donc T ⊨ φ', 'points' => 1],
                    ['label' => 'Les trois propriétés distinguées', 'points' => 3],
                    ['label' => 'Peano : cohérente mais ni complète ni décidable', 'points' => 1],
                    ['label' => 'La multiplication est la seule différence', 'points' => 2],
                    ['label' => 'L’arithmétisation de la syntaxe est évoquée', 'points' => 1],
                    ['label' => 'Sans théorie, les symboles n’ont pas de sens', 'points' => 2],
                ],
            ]],

            ['SPP', 'Types', [
                'title' => 'Définir un type inductif et filtrer exhaustivement',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
**1.** Définissez en WhyML un type `expr` représentant des expressions
arithmétiques : constantes entières, addition, multiplication, et opposé. *(3 pts)*

**2.** Écrivez `eval : expr -> int` par filtrage. *(3 pts)*

**3.** Écrivez `taille : expr -> int`, le nombre de nœuds de l'expression. *(2 pts)*

**4.** Énoncez les **trois propriétés** garanties par un type inductif, et illustrez
chacune sur `expr`. *(3 pts)*

**5.** Que se passe-t-il si un `match` oublie un constructeur ? Pourquoi est-ce
un problème pour la preuve, et pas seulement pour l'exécution ? *(3 pts)*

**6.** Définissez un type `option 'a` — soit une valeur, soit rien — puis
`evalSafe : expr -> option int` qui rend `None` en cas de division par zéro.
*(Vous ajouterez d'abord un constructeur `Div` à `expr`.)* *(4 pts)*
MD,
                'hint' => "Question 5 : un `match` non exhaustif rend la fonction **partielle**. Demandez-vous ce que vaut la fonction sur le cas oublié, et si l'on peut encore raisonner dessus.",
                'method' => <<<'MD'
1. Un constructeur par forme d'expression. Les constructeurs récursifs prennent des
   `expr` en argument.
2. Un cas par constructeur, avec un appel récursif sur chaque sous-expression.
6. `option` a deux constructeurs. Pour `evalSafe`, il faut propager le `None` :
   si un sous-calcul échoue, le tout échoue.
MD,
                'solution' => <<<'MD'
**1. Le type**

```whyml
type expr =
  | Const int
  | Plus expr expr
  | Mult expr expr
  | Neg expr
```

Quatre constructeurs. `Const` prend un entier, `Plus` et `Mult` deux
sous-expressions, `Neg` une seule.

**2. `eval`**

```whyml
function eval (e: expr) : int =
  match e with
  | Const n    -> n
  | Plus a b   -> eval a + eval b
  | Mult a b   -> eval a * eval b
  | Neg a      -> - (eval a)
  end
```

Un cas par constructeur, et un appel récursif par sous-expression. La structure du
`match` reproduit exactement celle de la définition du type — et donc celle de la
preuve par induction qui suivra.

**3. `taille`**

```whyml
function taille (e: expr) : int =
  match e with
  | Const _    -> 1
  | Plus a b   -> 1 + taille a + taille b
  | Mult a b   -> 1 + taille a + taille b
  | Neg a      -> 1 + taille a
  end
```

**4. Les trois propriétés d'un type inductif**

**Exhaustivité** — tout élément du type provient d'un constructeur.
*Sur `expr` :* toute expression est soit une constante, soit une addition, soit une
multiplication, soit un opposé. Il n'y a pas de cinquième forme.

**Disjonction** — deux constructeurs distincts produisent des valeurs distinctes.
*Sur `expr` :* `Const 3` n'est jamais égal à `Plus a b`, quels que soient a et b.

**Injectivité** — un même constructeur appliqué à des arguments différents donne des
valeurs différentes.
*Sur `expr` :* si `Plus a b = Plus c d`, alors `a = c` et `b = d`.

Ces trois propriétés sont ce qui **fonde l'induction structurelle** : puisqu'il n'y a
pas d'autre façon de fabriquer un élément, traiter tous les constructeurs, c'est
tout traiter.

**5. Filtrage non exhaustif**

*À l'exécution*, la fonction plante ou rend une valeur indéfinie sur le cas oublié.

*Pour la preuve*, c'est plus grave : la fonction devient **partielle**, c'est-à-dire
qu'elle n'est **pas définie** sur tout son domaine. Or les fonctions logiques de
Why3 doivent être **totales** — sinon on ne peut plus raisonner dessus.

Concrètement, Why3 **refuse** un `match` non exhaustif dans une `function` : il
engendre une obligation de preuve d'exhaustivité qui échoue.

*La raison profonde.* Si `eval` n'était pas définie sur `Neg`, l'énoncé
`forall e. eval e >= 0` n'aurait pas de sens : que vaudrait-il sur une expression
contenant un `Neg` ? Une logique où certaines formules n'ont pas de valeur de vérité
devient incohérente.

**6. Le type `option` et l'évaluation sûre**

```whyml
type option 'a = None | Some 'a

type expr =
  | Const int
  | Plus expr expr
  | Mult expr expr
  | Neg expr
  | Div expr expr

function evalSafe (e: expr) : option int =
  match e with
  | Const n  -> Some n

  | Plus a b ->
      match evalSafe a, evalSafe b with
      | Some x, Some y -> Some (x + y)
      | _, _           -> None
      end

  | Mult a b ->
      match evalSafe a, evalSafe b with
      | Some x, Some y -> Some (x * y)
      | _, _           -> None
      end

  | Neg a ->
      match evalSafe a with
      | Some x -> Some (- x)
      | None   -> None
      end

  | Div a b ->
      match evalSafe a, evalSafe b with
      | Some _, Some 0 -> None            (* division par zéro *)
      | Some x, Some y -> Some (div x y)
      | _, _           -> None
      end
  end
```

*Le principe.* Le `None` se **propage** : dès qu'un sous-calcul échoue, le calcul
englobant échoue aussi. Le motif `_, _ -> None` capture tous les cas où l'un au
moins des deux vaut `None`.

*Ce que cela apporte.* La fonction est **totale** — elle rend toujours quelque chose,
même sur une division par zéro. On peut donc raisonner dessus sans précondition,
et énoncer par exemple :

```whyml
lemma div_zero :
  forall a: expr. evalSafe (Div a (Const 0)) = None
```

C'est la manière habituelle de rendre totale une fonction naturellement partielle.
MD,
                'rubric' => [
                    ['label' => 'Type `expr` avec les quatre constructeurs et leurs arités', 'points' => 3],
                    ['label' => '`eval` : un cas par constructeur, récursion correcte', 'points' => 3],
                    ['label' => '`taille` correcte', 'points' => 2],
                    ['label' => 'Exhaustivité, disjonction, injectivité définies', 'points' => 2],
                    ['label' => 'Chacune illustrée sur `expr`', 'points' => 1],
                    ['label' => 'Filtrage non exhaustif : fonction partielle, refusée par Why3', 'points' => 2],
                    ['label' => 'La raison logique : les fonctions doivent être totales', 'points' => 1],
                    ['label' => '`option` défini, `evalSafe` propage correctement le `None`', 'points' => 4],
                ],
            ]],

            ['SPP', 'Calculs', [
                'title' => 'Récursion structurelle, accumulateur et variant',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 4,
                'statement' => <<<'MD'
**1.** Écrivez `somme : list int -> int` par récursion structurelle. Pourquoi
n'a-t-elle pas besoin de `variant` ? *(3 pts)*

**2.** Écrivez `sommeAcc : list int -> int -> int`, version **récursive terminale**
avec accumulateur. *(2 pts)*

**3.** Énoncez le lemme reliant `sommeAcc` à `somme`. **Attention à la
quantification.** *(3 pts)*

**4.** Démontrez ce lemme par induction structurelle. Signalez où sert l'hypothèse. *(5 pts)*

**5.** Écrivez `pgcd(a, b)` par l'algorithme d'Euclide. Donnez son `variant` et
justifiez ses trois propriétés. *(4 pts)*

**6.** Pourquoi `somme` n'a-t-elle pas besoin de variant alors que `pgcd` en exige
un ? *(3 pts)*
MD,
                'hint' => "Question 3 : essayez d'énoncer `sommeAcc l 0 = somme l` et voyez si l'induction passe. Elle bloque — il faut quantifier l'accumulateur à l'intérieur de la propriété.",
                'method' => <<<'MD'
1. Un cas par constructeur, appel récursif sur le sous-terme.
3. La propriété doit être `∀l, ∀acc, …` et non `∀l, … 0 …`. Cherchez la forme
   générale en calculant `sommeAcc [1;2] 5` à la main.
5. Le variant doit décroître strictement et rester minoré. Vérifiez sur `mod`.
MD,
                'solution' => <<<'MD'
**1. `somme` par récursion structurelle**

```whyml
function somme (l: list int) : int =
  match l with
  | Nil      -> 0
  | Cons x r -> x + somme r
  end
```

*Pourquoi pas de `variant`.* L'appel récursif porte sur `r`, qui est un **sous-terme
syntaxique strict** de `Cons x r`. Or un type inductif n'admet **aucun élément
infini** : toute liste se construit en un nombre fini d'applications de `Cons`.
La descente est donc nécessairement finie.

Why3 reconnaît ce schéma et accepte la définition **sans variant**.

**2. `sommeAcc`, récursive terminale**

```whyml
function sommeAcc (l: list int) (acc: int) : int =
  match l with
  | Nil      -> acc
  | Cons x r -> sommeAcc r (acc + x)
  end
```

L'appel récursif est la **dernière** opération : rien ne reste à faire au retour.
Un compilateur peut donc le transformer en boucle, avec une pile en O(1) au lieu
de O(n).

**3. Le lemme**

Calculons à la main : `sommeAcc [1;2] 5 = sommeAcc [2] 6 = sommeAcc [] 8 = 8`.
Et `somme [1;2] = 3`. On observe `8 = 3 + 5`.

```whyml
lemma sommeAcc_correct :
  forall l: list int, acc: int.
    sommeAcc l acc = somme l + acc
```

**Le point critique : `acc` doit être quantifié universellement.**

Si l'on énonçait seulement `sommeAcc l 0 = somme l`, l'induction **échouerait** :
dans le cas `Cons x r`, on doit appliquer l'hypothèse à `sommeAcc r (0 + x)`, où
l'accumulateur ne vaut plus 0. L'hypothèse restreinte à 0 ne s'applique pas.

C'est le **renforcement** du chapitre Récurrence, transposé à l'induction
structurelle : une propriété plus générale à démontrer donne une hypothèse plus
riche au moment du pas.

**4. La démonstration**

Soit **P(l)** : « `∀acc, sommeAcc l acc = somme l + acc` ».

*Cas `Nil`.*
```
sommeAcc Nil acc
  = acc                          [déf. sommeAcc, cas Nil]
  = 0 + acc                      [arithmétique]
  = somme Nil + acc              [déf. somme, cas Nil]
```
✅

*Cas `Cons x r`.* Hypothèse : `∀acc, sommeAcc r acc = somme r + acc`.

Soit `acc` quelconque.
```
sommeAcc (Cons x r) acc
  = sommeAcc r (acc + x)              [déf. sommeAcc, cas Cons]
  = somme r + (acc + x)               [par hypothèse d'induction, appliquée à acc + x]
  = (x + somme r) + acc               [commutativité et associativité]
  = somme (Cons x r) + acc            [déf. somme, cas Cons, à rebours]
```
✅

*Conclusion.* Par induction structurelle sur l, la propriété vaut pour toute liste
et tout accumulateur. ∎

**Le corollaire recherché**, en prenant `acc = 0` :
```whyml
lemma sommeAcc_zero :
  forall l: list int. sommeAcc l 0 = somme l
```

*Notez où l'hypothèse a servi :* appliquée à **`acc + x`**, pas à `acc`. C'est
précisément ce que la quantification universelle rend possible.

**5. `pgcd` par Euclide**

```whyml
let rec pgcd (a b: int) : int
  requires { a >= 0 /\ b >= 0 }
  ensures  { result >= 0 }
  variant  { b }
= if b = 0 then a
  else pgcd b (mod a b)
```

*Les trois propriétés du variant `b` :*

1. **À valeurs dans un ensemble bien fondé** — `b` est un entier, et la précondition
   garantit `b ≥ 0`. On est donc dans ℕ, qui est bien fondé.
2. **Minoré** tant que la récursion continue — dans la branche récursive, `b ≠ 0`,
   donc `b ≥ 1 > 0`.
3. **Strictement décroissant** — l'appel récursif passe `mod a b` en second argument.
   Or, par définition du reste euclidien, `0 ≤ mod a b < b` quand `b > 0`.
   Le nouveau variant est donc strictement inférieur à l'ancien.

Les trois conditions étant réunies, **la fonction termine**.

**6. Pourquoi `somme` se passe de variant et `pgcd` non**

`somme` procède par **récursion structurelle** : l'appel porte sur `r`, un
sous-terme **syntaxique** de l'argument. La décroissance est visible dans la
**structure du terme**, et Why3 la vérifie mécaniquement.

`pgcd` procède par **récursion générale** : l'appel porte sur `mod a b`, qui n'est
pas un sous-terme de `b` — c'est une **valeur calculée**. Rien dans la syntaxe ne
garantit qu'elle décroît. Il faut donc **fournir explicitement** la quantité qui
décroît, et Why3 engendre alors une obligation de preuve pour vérifier les trois
propriétés.

**La règle générale :** dès que l'argument de l'appel récursif n'est pas un
sous-terme syntaxique de l'argument d'entrée, un variant est obligatoire.
MD,
                'rubric' => [
                    ['label' => '`somme` par récursion structurelle', 'points' => 2],
                    ['label' => 'Absence de variant justifiée par le sous-terme syntaxique', 'points' => 1],
                    ['label' => '`sommeAcc` récursive terminale', 'points' => 2],
                    ['label' => 'Le lemme quantifie `acc` universellement', 'points' => 2],
                    ['label' => 'La nécessité de ce renforcement est expliquée', 'points' => 1],
                    ['label' => 'Les deux cas de l’induction, égalités justifiées', 'points' => 3],
                    ['label' => 'L’hypothèse est appliquée à `acc + x`, et c’est signalé', 'points' => 2],
                    ['label' => '`pgcd` avec variant `b`', 'points' => 2],
                    ['label' => 'Les trois propriétés du variant vérifiées', 'points' => 2],
                    ['label' => 'Structurelle contre générale : sous-terme syntaxique ou valeur calculée', 'points' => 3],
                ],
            ]],

            /* ============ ALO C2-Coll ============ */
            ['ALO', 'C2-Coll', [
                'title' => 'Collections, flux et JDBC en pratique',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 3,
                'statement' => <<<'MD'
**1.** Pour chaque besoin, dites quelle collection choisir et **pourquoi**. *(5 pts)*

a. La liste ordonnée des opérations d'un compte, doublons possibles
b. Les codes postaux desservis par un transporteur, sans doublon
c. L'annuaire nom → numéro de téléphone
d. Les mots d'un texte triés par ordre alphabétique, sans doublon
e. L'historique de navigation, du plus récent au plus ancien

**2.** Qu'affiche ce code ? Justifiez chaque ligne. *(4 pts)*
```java
List<String> l = new ArrayList<>();
l.add("a"); l.add("b"); l.add("a");
Set<String> s = new HashSet<>(l);
System.out.println(l.size() + " " + s.size());

String x = "bonjour";
String y = "bon" + "jour";
String z = new String("bonjour");
System.out.println((x == y) + " " + (x == z) + " " + x.equals(z));
```

**3.** Écrivez une méthode qui lit un fichier CSV `nom;note` et rend une
`Map<String, Double>` des moyennes par nom. Utilisez le **try-with-resources**. *(6 pts)*

**4.** Écrivez la requête JDBC qui insère un livre. Expliquez pourquoi
`PreparedStatement` plutôt que `Statement`, avec un exemple d'attaque. *(5 pts)*
MD,
                'hint' => "Question 2 : `==` sur des String compare les références. Java met en cache les littéraux compilés, mais `new String(...)` force une nouvelle allocation.",
                'method' => <<<'MD'
1. Trois questions : faut-il un **ordre** ? des **doublons** ? un **accès par clé** ?
3. Une `Map<String, List<Double>>` intermédiaire, ou bien une somme et un compteur.
4. Les `?` séparent la requête des valeurs. Imaginez ce qu'un utilisateur pourrait
   saisir dans un champ concaténé directement.
MD,
                'solution' => <<<'MD'
**1. Le choix de la collection**

| | Collection | Pourquoi |
|---|---|---|
| a. opérations d'un compte | **`ArrayList`** | ordre chronologique à conserver, doublons possibles (deux retraits identiques) |
| b. codes postaux | **`HashSet`** | pas de doublon, ordre sans importance, test d'appartenance en O(1) |
| c. annuaire | **`HashMap<String, String>`** | association clé → valeur |
| d. mots triés sans doublon | **`TreeSet`** | pas de doublon **et** ordre alphabétique maintenu automatiquement |
| e. historique | **`LinkedList`** ou **`Deque`** | insertion en tête en O(1) ; une `ArrayList` exigerait de décaler tous les éléments |

**2. Ce qu'affiche le code**

*Première paire :*
```
3 2
```
- `l.size() = 3` — une `List` accepte les doublons : "a", "b", "a".
- `s.size() = 2` — un `HashSet` les élimine : {"a", "b"}.

*Seconde ligne :*
```
true false true
```
- **`x == y` → `true`.** `"bon" + "jour"` est calculé **à la compilation** :
  le compilateur produit le littéral `"bonjour"`, et Java **interne** les littéraux
  dans un cache commun. `x` et `y` désignent donc **le même objet**.
- **`x == z` → `false`.** `new String(...)` force explicitement une **nouvelle
  allocation** en mémoire. Deux objets distincts, donc deux références différentes.
- **`x.equals(z)` → `true`.** `equals` compare le **contenu**, qui est identique.

**La leçon :** pour comparer des chaînes, toujours `.equals()`. Le `==` fonctionne
parfois **par accident**, à cause du cache, ce qui rend le bug d'autant plus vicieux.

**3. Moyennes par nom**

```java
public Map<String, Double> moyennes(String chemin) throws IOException {

    Map<String, Double> sommes = new HashMap<>();
    Map<String, Integer> compteurs = new HashMap<>();

    try (BufferedReader r = new BufferedReader(new FileReader(chemin))) {
        String ligne;
        while ((ligne = r.readLine()) != null) {
            if (ligne.isBlank()) continue;

            String[] champs = ligne.split(";");
            if (champs.length < 2) continue;          // ligne malformée, on saute

            String nom = champs[0].trim();
            double note = Double.parseDouble(champs[1].trim());

            sommes.put(nom, sommes.getOrDefault(nom, 0.0) + note);
            compteurs.put(nom, compteurs.getOrDefault(nom, 0) + 1);
        }
    }

    Map<String, Double> resultat = new HashMap<>();
    for (Map.Entry<String, Double> e : sommes.entrySet()) {
        resultat.put(e.getKey(), e.getValue() / compteurs.get(e.getKey()));
    }

    return resultat;
}
```

*Trois points à souligner :*
- Le **try-with-resources** ferme le fichier automatiquement, même si une exception
  survient au milieu.
- **`getOrDefault`** évite d'écrire un `if` de présence à chaque ligne.
- Les lignes vides ou malformées sont **ignorées** plutôt que de faire planter la
  méthode.

**4. Insertion JDBC**

```java
public void insererLivre(Connection cn, String titre, String auteur, int annee)
        throws SQLException {

    String sql = "INSERT INTO livre (titre, auteur, annee) VALUES (?, ?, ?)";

    try (PreparedStatement st = cn.prepareStatement(sql)) {
        st.setString(1, titre);
        st.setString(2, auteur);
        st.setInt(3, annee);
        st.executeUpdate();
    }
}
```

**Pourquoi `PreparedStatement` : l'injection SQL**

Avec un `Statement` et une concaténation :

```java
String sql = "INSERT INTO livre (titre) VALUES ('" + titre + "')";
```

Si l'utilisateur saisit comme titre :

```
'); DROP TABLE livre; --
```

la requête envoyée devient :

```sql
INSERT INTO livre (titre) VALUES (''); DROP TABLE livre; --')
```

**La table est détruite.** Le `--` met en commentaire ce qui suit, pour que la
syntaxe reste valide.

Avec un `PreparedStatement`, la requête est **compilée d'abord**, avec ses `?`
comme emplacements. Les valeurs sont transmises **séparément** et ne sont jamais
interprétées comme du SQL : le titre serait simplement enregistré tel quel, guillemets
et point-virgule compris.

*Second avantage :* la requête préparée est compilée une fois et réutilisable,
ce qui accélère les insertions en série.
MD,
                'rubric' => [
                    ['label' => 'Les cinq collections correctement choisies', 'points' => 3],
                    ['label' => 'Chaque choix justifié par ordre / doublons / accès', 'points' => 2],
                    ['label' => '3 et 2 pour les tailles, avec la raison', 'points' => 2],
                    ['label' => 'true false true, chaque valeur justifiée', 'points' => 2],
                    ['label' => 'Méthode CSV avec try-with-resources', 'points' => 3],
                    ['label' => 'Calcul correct des moyennes, cas limites gérés', 'points' => 3],
                    ['label' => 'PreparedStatement avec les trois setters typés', 'points' => 2],
                    ['label' => 'Exemple d’injection SQL concret et expliqué', 'points' => 3],
                ],
            ]],

            /* ============ EP C2 ============ */
            ['EP', 'C2', [
                'title' => 'Problème, algorithme, et ce qui n’est pas calculable',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
**1.** Pour chacun, donnez la forme du problème — décision, recherche ou
optimisation — et reformulez-le dans les deux autres formes. *(4 pts)*

a. Trouver le plus court chemin entre deux villes
b. Le nombre 91 est-il premier ?
c. Trouver une coloration d'un graphe avec 3 couleurs

**2.** Montrez que si l'on sait résoudre la **version décision** d'un problème
d'optimisation en temps T, on résout l'optimisation en `O(T · log k)`. *(4 pts)*

**3.** Cette procédure est-elle un algorithme ? Justifiez avec les trois conditions. *(3 pts)*
```
tant que n ≠ 1 :
    si n est pair : n ← n / 2
    sinon : n ← 3n + 1
renvoyer "terminé"
```

**4.** Démontrez qu'il existe des problèmes sans algorithme, par dénombrement.
Détaillez les deux cardinalités. *(5 pts)*

**5.** Cette démonstration exhibe-t-elle un problème indécidable précis ?
Que faut-il de plus ? *(2 pts)*
MD,
                'hint' => "Question 3 : c'est la conjecture de Syracuse. Personne ne sait si cette boucle termine pour tout n. Qu'est-ce que cela implique pour la définition d'un algorithme ?",
                'method' => <<<'MD'
2. La version décision répond « existe-t-il une solution de coût ≤ k ? ».
   Comment trouver le plus petit k qui donne « oui » ?
4. Comptez les problèmes de décision, puis les programmes. Comparez les cardinalités.
MD,
                'solution' => <<<'MD'
**1. Les trois formes**

**a. Plus court chemin** — énoncé sous forme d'**optimisation**.
- *Décision :* « existe-t-il un chemin de longueur ≤ k entre u et v ? »
- *Recherche :* « donner un chemin de longueur ≤ k entre u et v. »

**b. 91 est-il premier ?** — énoncé sous forme de **décision**.
- *Recherche :* « donner un diviseur non trivial de 91, s'il en existe. »
  *(Réponse : 7, car 91 = 7 × 13. Donc 91 n'est pas premier.)*
- *Optimisation :* « donner le plus petit diviseur non trivial de 91. »

**c. Coloration en 3 couleurs** — énoncé sous forme de **recherche**.
- *Décision :* « le graphe est-il coloriable avec 3 couleurs ? »
- *Optimisation :* « donner le **nombre chromatique** χ(G), c'est-à-dire le nombre
  minimal de couleurs. »

**2. De la décision à l'optimisation**

Soit un problème d'optimisation dont on cherche la valeur minimale `k*`, comprise
entre 0 et une borne connue `K`. Soit `D(k)` la version décision : « existe-t-il une
solution de coût ≤ k ? », résoluble en temps T.

*L'algorithme, par dichotomie :*

```
bas ← 0 ; haut ← K
tant que bas < haut :
    milieu ← (bas + haut) / 2
    si D(milieu) répond oui : haut ← milieu
    sinon : bas ← milieu + 1
renvoyer bas
```

*Correction.* `D` est **monotone** : si une solution de coût ≤ k existe, alors une
solution de coût ≤ k' existe pour tout k' ≥ k. La réponse passe donc de « non » à
« oui » en un unique point, qui est `k*`. La dichotomie le localise.

*Complexité.* L'intervalle `[bas, haut]` est **divisé par deux** à chaque tour :
il faut `log₂ K` itérations. Chacune coûte T.

**Total : O(T · log K).** ∎

*La conséquence théorique.* Décision et optimisation sont **polynomialement
équivalentes**. C'est pourquoi toute la théorie de la complexité se formule sur les
problèmes de décision sans rien perdre en généralité.

**3. La procédure de Syracuse**

Vérifions les trois conditions :

1. **Instructions élémentaires et non ambiguës** — ✅ tester la parité, diviser,
   multiplier et ajouter sont des opérations parfaitement définies.
2. **Terminaison en un nombre fini d'étapes** — ❓ **on ne sait pas.**
3. **Résultat correct** — ✅ si elle termine, elle renvoie bien « terminé ».

C'est la **conjecture de Syracuse** (ou de Collatz) : on conjecture que la suite
atteint 1 pour tout entier de départ, cela a été vérifié pour des valeurs
astronomiques, mais **personne ne l'a démontré**.

**Conclusion : on ne peut pas affirmer que c'est un algorithme.** Tant que la
terminaison n'est pas établie pour toute entrée, la condition 2 n'est pas vérifiée.

C'est un excellent exemple de la raison pour laquelle la terminaison figure dans la
définition : sans elle, on appellerait « algorithme » une procédure dont on ignore
si elle rend jamais un résultat.

**4. Il existe des problèmes sans algorithme**

*Première cardinalité — les problèmes.*

Un problème de décision sur ℕ associe à chaque entier une réponse oui ou non.
C'est donc une fonction `ℕ → {0, 1}`, autrement dit un **sous-ensemble de ℕ**
— celui des entrées auxquelles on répond oui.

L'ensemble des parties de ℕ a pour cardinal `2^{ℵ₀}`, qui est **non dénombrable**
par l'argument diagonal de Cantor.

**Il y a une infinité non dénombrable de problèmes de décision.**

*Seconde cardinalité — les programmes.*

Un programme est une **chaîne finie de caractères** sur un alphabet fini Σ.
L'ensemble Σ* des chaînes finies est **dénombrable** : on peut l'énumérer
en listant d'abord les chaînes de longueur 0, puis celles de longueur 1 dans
l'ordre alphabétique, puis celles de longueur 2, et ainsi de suite. Chaque chaîne
reçoit ainsi un numéro unique.

**Il y a une infinité dénombrable de programmes.**

*Conclusion.* Toute fonction qui associerait un programme à chaque problème serait
une injection d'un ensemble non dénombrable dans un ensemble dénombrable — ce qui
est **impossible**.

**Il existe donc des problèmes de décision qu'aucun programme ne résout.** ∎

En réalité, presque tous : les problèmes calculables forment un sous-ensemble
dénombrable, donc de « mesure nulle » dans l'ensemble de tous les problèmes.

**5. Ce que la démonstration ne fait pas**

**Elle n'exhibe aucun problème indécidable précis.**

C'est une preuve d'**existence** purement cardinale : elle établit qu'il en existe,
sans en désigner un seul. Elle ne permet donc pas de dire « tel problème concret est
indécidable ».

*Ce qu'il faut de plus :* une **construction explicite**, par diagonalisation.
C'est l'objet du chapitre 5, qui construit A_TM et démontre son indécidabilité
en supposant l'existence d'un décideur et en fabriquant une entrée qui le met en
contradiction avec lui-même.

La différence est celle qui sépare « il existe des nombres transcendants » — prouvable
par dénombrement — de « π est transcendant », qui a demandé une démonstration
spécifique.
MD,
                'rubric' => [
                    ['label' => 'Les trois problèmes classés dans leur forme', 'points' => 2],
                    ['label' => 'Chacun reformulé dans les deux autres formes', 'points' => 2],
                    ['label' => 'Dichotomie sur k, avec la monotonie de D justifiée', 'points' => 3],
                    ['label' => 'Complexité O(T · log K) obtenue', 'points' => 1],
                    ['label' => 'Les trois conditions testées sur Syracuse', 'points' => 2],
                    ['label' => 'La terminaison est inconnue : on ne peut pas conclure', 'points' => 1],
                    ['label' => 'Problèmes = parties de ℕ, cardinal 2^ℵ₀, non dénombrable', 'points' => 2],
                    ['label' => 'Programmes = chaînes finies, dénombrable, avec l’énumération', 'points' => 2],
                    ['label' => 'Conclusion : pas d’injection possible', 'points' => 1],
                    ['label' => 'La preuve est non constructive ; il faut la diagonalisation', 'points' => 2],
                ],
            ]],

            /* ============ RIG R3 ============ */
            ['RIG', 'R3', [
                'title' => 'Rester dans le référentiel — auditer son vocabulaire',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 2,
                'statement' => <<<'MD'
Le correcteur d'AGC a rayé une notion de votre copie en écrivant
**« pas vu dans le cours »**. Cet exercice sert à ce que cela ne se reproduise pas.

**1.** Pour chacun des cinq termes, dites s'il figure dans le polycopié de la
matière concernée. Utilisez la **recherche plein texte de la bibliothèque** pour
vérifier, et notez le document et la section. *(5 pts)*

a. « dictionnaire de données » — AGC
b. « matrice d'adjacence » — AGC
c. « inversion de dépendances » — ALO
d. « chaînage arrière » — MIA
e. « invariant de boucle » — SPP

**2.** Pour chaque terme absent, donnez le terme **du cours** qui exprime la même
idée. *(3 pts)*

**3.** Établissez, pour chacune des cinq matières, la **liste de vos documents de
référence** — ceux dont le vocabulaire fait foi. *(3 pts)*

**4.** Relisez votre réponse à l'exercice « Réécrire votre réponse AGC 1.1 » et
vérifiez que **chaque terme employé** figure dans `AGC-cours.pdf`. Listez les
éventuels intrus. *(4 pts)*

**5.** Rédigez la **règle en une phrase** que vous appliquerez le jour J avant
d'employer un terme dont vous n'êtes pas sûr. *(2 pts)*
MD,
                'hint' => "La bibliothèque de Méridien indexe le texte de 120 PDF. Tapez le terme dans la barre de recherche et filtrez par matière : s'il n'apparaît nulle part, il n'est pas au référentiel.",
                'method' => <<<'MD'
1. Bibliothèque → barre de recherche → filtre par matière. La page du document
   affiche les occurrences en contexte.
3. Pour chaque matière, le document de cours principal — celui qui fait plus de
   90 pages — et les recueils d'exercices.
4. Ouvrez votre tentative précédente et passez chaque terme technique au crible.
MD,
                'solution' => <<<'MD'
**1. Vérification des cinq termes**

| Terme | Matière | Présent ? | Où |
|---|---|---|---|
| a. dictionnaire de données | AGC | **Non** | absent de `AGC-cours.pdf` |
| b. matrice d'adjacence | AGC | **Oui** | `AGC-cours.pdf` § 1.2 — Représentation informatique |
| c. inversion de dépendances | ALO | **Non** | `alo_V9.pdf` ne traite que le principe de **Liskov** (§ 1.4.1) |
| d. chaînage arrière | MIA | **Oui** | `mainMOIA.pdf` § 5.2.2 |
| e. invariant de boucle | SPP | **Oui** | `cHoare.pdf` et `cContrats.pdf` |

**Deux intrus : « dictionnaire de données » et « inversion de dépendances ».**

Le premier est exactement celui que le correcteur a rayé sur votre copie de janvier.

**2. Les termes du cours à employer à la place**

**a. « dictionnaire de données »** → selon ce que vous vouliez dire :
- pour une structure associant une clé à une valeur : **table de hachage**, si elle
  figure au programme — sinon, s'en tenir aux **listes d'adjacence** et à la
  **matrice d'adjacence**, les deux seules représentations du § 1.2 ;
- votre intention en janvier semblait être « une structure associative » : le cours
  n'en propose pas pour représenter un graphe. **Le terme n'avait donc pas sa place**
  dans la réponse.

**c. « inversion de dépendances »** → le polycopié ALO ne couvre que le **principe
de substitution de Liskov**. Les autres principes SOLID n'y figurent pas. Pour parler
du fait qu'une classe dépende d'une **interface** plutôt que d'une implémentation,
employez le vocabulaire du cours : *« la classe dépend de l'interface `X` et non de
son implémentation concrète »*.

**3. Vos documents de référence**

| Matière | Document principal | Compléments |
|---|---|---|
| **ALO** | `alo_V9.pdf` (130 p.) | `IntroductionJavaV1.pdf`, les 4 annales corrigées |
| **EP** | `cours_ep.pdf` (93 p.) | `td1_new` à `td5_new` et leurs corrections |
| **AGC** | `AGC-cours.pdf` (125 p.) | `AGC-introduction.pdf`, `ExosAlgoGraphes`, `ExosProgDyn`, `ExosProgLin` |
| **SPP** | les 10 paires `exXxx.pdf` / `cXxx.pdf` | `M1sppEAD.pdf`, les devoirs corrigés 2024-2026 |
| **MIA** | `mainMOIA.pdf` (236 p.) | les 6 animations, la matrice examens/chapitres |

**Rien d'autre ne fait foi.** Un cours suivi ailleurs, une vidéo, un article : le
barème ne les connaît pas.

**4. Audit de votre réponse AGC 1.1**

*Termes employés dans la version corrigée de l'exercice :*

| Terme | Dans `AGC-cours.pdf` ? |
|---|---|
| matrice d'adjacence | ✅ § 1.2 |
| listes d'adjacence | ✅ § 1.2 |
| matrice d'incidence | ✅ § 1.2 |
| degré d'un sommet | ✅ § 1.1 |
| graphe creux / dense | ✅ § 1.1 |
| complexité en O() | ✅ § 1.10 et chapitre 3 |
| parcours | ✅ § 1.3 |

**Aucun intrus.** C'est précisément ce qui distingue la version corrigée de celle
rendue en janvier, qui contenait « dictionnaires de données ».

*Le contraste est instructif.* La version de janvier employait sept termes, dont un
hors référentiel et cinq trop vagues pour être évalués. La version corrigée en
emploie sept également, tous du cours, et chacun accompagné d'un chiffre.

**5. La règle du jour J**

> **Avant d'employer un terme technique, je me demande à quelle page du polycopié
> il se trouve. Si je ne peux pas répondre, je le remplace par un terme dont je le
> sais — ou je décris la chose sans la nommer.**

*Variante courte, à retenir :* **« page ou rien ».**

*Pourquoi cela fonctionne.* Le doute est un signal fiable. Quand un terme vous vient
sans que vous puissiez le situer dans le cours, il vient d'ailleurs — et le barème
ne le connaît pas. Décrire la chose en mots simples rapporte davantage qu'un terme
savant hors référentiel.
MD,
                'rubric' => [
                    ['label' => 'Les cinq termes vérifiés, avec le document et la section', 'points' => 5],
                    ['label' => 'Les deux intrus identifiés', 'points' => 1],
                    ['label' => 'Un terme du cours proposé pour chaque intrus', 'points' => 2],
                    ['label' => 'Les cinq documents de référence listés', 'points' => 3],
                    ['label' => 'Audit de la réponse AGC 1.1, terme par terme', 'points' => 4],
                    ['label' => 'La règle rédigée en une phrase applicable', 'points' => 2],
                ],
            ]],
        ];
    }
}