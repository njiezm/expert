<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Exercices des chapitres de poids 4 et 5 restés sans pratique.
 *
 * Le calcul de maîtrise pondère la pratique à 35 % contre 15 % pour la lecture :
 * un chapitre sans exercice plafonne, quelle que soit la qualité de sa fiche.
 */
class ExercicesPoidsFortSeeder extends Seeder
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
                $exo + ['chapter_id' => $chapter->id, 'position' => 300]
            );
        }
    }

    /* ==================================================================== */

    private function exercices(): array
    {
        return [

            /* ============ EP C5 — Décidabilité (poids 5) ============ */
            ['EP', 'C5', [
                'title' => 'Trois réductions à mener jusqu’au bout',
                'origin' => 'genere',
                'est_minutes' => 40,
                'difficulty' => 5,
                'statement' => <<<'MD'
Pour chacun des trois problèmes, montrez qu'il est **indécidable** par réduction
depuis **A_TM = { ⟨M, w⟩ | M accepte w }**. Suivez les quatre étapes à chaque fois.

**1.** `TOUT_TM = { ⟨M⟩ | L(M) = Σ* }` — M accepte tous les mots. *(6 pts)*

**2.** `DEUX_TM = { ⟨M⟩ | L(M) contient au moins deux mots }`. *(6 pts)*

**3.** `EGAL_TM = { ⟨M₁, M₂⟩ | L(M₁) = L(M₂) }`. *(5 pts)*
*Indication : réduisez depuis VIDE_TM plutôt que depuis A_TM.*

**4.** Parmi ces trois problèmes, lesquels sont **semi-décidables** ? Justifiez. *(3 pts)*

Rappel du gabarit : supposer R, construire S, justifier que S s'arrête, conclure
à la contradiction.
MD,
                'hint' => "Le mécanisme est toujours le même : fabriquer une machine auxiliaire dont le langage bascule selon que M accepte w ou non. Cherchez d'abord ce que doit valoir L(M_aux) dans chacun des deux cas.",
                'method' => <<<'MD'
Pour chaque problème B :

1. **Supposons** qu'une machine R décide B.
2. **Construisons** une machine auxiliaire `M₂` dont le langage dépend du fait que
   M accepte w. Puis S, qui fabrique ⟨M₂⟩ et interroge R.
3. **Justifions** que S s'arrête : la construction de ⟨M₂⟩ est syntaxique, et R
   s'arrête par hypothèse. **S ne simule jamais M elle-même.**
4. **Contradiction** : A_TM est indécidable, donc R n'existe pas.
MD,
                'solution' => <<<'MD'
**1. TOUT_TM est indécidable.**

*Étape 1.* Supposons que **R décide TOUT_TM**.

*Étape 2.* Sur ⟨M, w⟩, construisons :

> **M₂ = « sur l'entrée x : ignorer x, simuler M sur w, accepter si M accepte. »**

- Si **M accepte w** : M₂ accepte tout x, donc **L(M₂) = Σ\***.
- Sinon : M₂ n'accepte rien, donc **L(M₂) = ∅ ≠ Σ\***.

> **S = « sur ⟨M, w⟩ : construire ⟨M₂⟩, exécuter R dessus, répondre comme R. »**

*Étape 3.* S s'arrête : la construction de ⟨M₂⟩ est purement syntaxique et finie,
et R s'arrête par hypothèse. S ne simule jamais M.

*Étape 4.* S déciderait A_TM, qui est indécidable. **Contradiction : R n'existe pas.** ∎

**2. DEUX_TM est indécidable.**

*Étape 1.* Supposons que **R décide DEUX_TM**.

*Étape 2.* Sur ⟨M, w⟩ :

> **M₂ = « sur x : si x ∉ {a, b}, rejeter ; sinon simuler M sur w et accepter si M accepte. »**

- Si **M accepte w** : `L(M₂) = {a, b}`, deux mots → **dans DEUX_TM**.
- Sinon : `L(M₂) = ∅` → **pas dans DEUX_TM**.

> **S = « construire ⟨M₂⟩, exécuter R, répondre comme R. »**

*Étapes 3 et 4.* Identiques. **DEUX_TM est indécidable.** ∎

**3. EGAL_TM est indécidable**, par réduction depuis **VIDE_TM**.

*Étape 1.* Supposons que **R décide EGAL_TM**.

*Étape 2.* Soit `M∅` une machine qui rejette tout : `L(M∅) = ∅`.

> **S = « sur ⟨M⟩ : exécuter R sur ⟨M, M∅⟩ et répondre comme R. »**

`L(M) = L(M∅) = ∅` **si et seulement si** `L(M) = ∅`. Donc S décide VIDE_TM.

*Étapes 3 et 4.* S s'arrête, et VIDE_TM est indécidable.
**Contradiction : EGAL_TM est indécidable.** ∎

*(La réduction depuis A_TM serait possible mais plus longue : passer par VIDE_TM,
déjà établi indécidable, économise une construction.)*

**4. Semi-décidabilité**

**DEUX_TM est semi-décidable.** On énumère les mots de Σ\* et l'on simule M sur
chacun **en parallèle**, par tranches de temps croissantes — technique du
*dovetailing*. Dès que deux mots sont acceptés, on accepte. Si L(M) a moins de deux
mots, on ne s'arrête jamais, ce qui est permis.

**TOUT_TM n'est pas semi-décidable.** Accepter exigerait de vérifier que **tous**
les mots sont acceptés, ce qui demande une infinité de vérifications. Aucune
observation finie ne permet de conclure.

**EGAL_TM n'est pas semi-décidable** non plus, pour la même raison : établir une
égalité de langages porte sur une infinité de mots.

*Le critère général :* un langage est semi-décidable quand l'appartenance se **certifie
par une observation finie**. Deux mots acceptés, c'est fini. « Tous les mots », non.
MD,
                'rubric' => [
                    ['label' => 'TOUT_TM : construction de M₂ avec les deux cas de L(M₂)', 'points' => 3],
                    ['label' => 'TOUT_TM : les quatre étapes numérotées, conclusion incluse', 'points' => 3],
                    ['label' => 'DEUX_TM : M₂ produit un langage à exactement deux mots', 'points' => 3],
                    ['label' => 'DEUX_TM : les quatre étapes', 'points' => 3],
                    ['label' => 'EGAL_TM : réduction depuis VIDE_TM avec la machine M∅', 'points' => 3],
                    ['label' => 'EGAL_TM : conclusion explicite', 'points' => 2],
                    ['label' => 'DEUX_TM identifié semi-décidable, avec le dovetailing', 'points' => 2],
                    ['label' => 'TOUT_TM et EGAL_TM non semi-décidables, avec la raison', 'points' => 1],
                ],
            ]],

            /* ============ AGC G2 — Parcours (poids 5) ============ */
            ['AGC', 'G2', [
                'title' => 'Parcours, connexité et plus courts chemins',
                'origin' => 'genere',
                'est_minutes' => 40,
                'difficulty' => 4,
                'statement' => <<<'MD'
Graphe orienté, sommets A à G :

`A→B`, `A→C`, `B→D`, `C→D`, `C→E`, `D→F`, `E→F`, `F→G`, `G→C`

**1.** Déroulez un **parcours en largeur** depuis A. Donnez l'ordre de visite et la
distance en nombre d'arcs de chaque sommet. *(3 pts)*

**2.** Déroulez un **parcours en profondeur** depuis A. Donnez l'ordre de visite et
les **dates de fin**. *(3 pts)*

**3.** Le graphe contient-il un **circuit** ? Si oui, lequel, et comment le parcours
en profondeur le détecte-t-il ? *(2 pts)*

**4.** Donnez les **composantes fortement connexes** par l'algorithme de Kosaraju.
Détaillez les trois étapes. *(4 pts)*

**5.** Un **tri topologique** est-il possible ? Justifiez. *(1 pt)*

**6.** Donnez la **complexité** de chaque algorithme employé, en précisant la
structure de données supposée. *(2 pts)*
MD,
                'hint' => "Pour la question 3, cherchez un arc qui remonte vers un sommet encore en cours de traitement — c'est la définition de l'arc arrière. Pour la 5, souvenez-vous de la condition d'existence d'un tri topologique.",
                'method' => <<<'MD'
1. **Largeur** : une file. On sort le plus ancien, on empile ses voisins non marqués.
2. **Profondeur** : une pile, ou la récursion. Notez la date de fin de chaque sommet,
   c'est-à-dire le moment où l'on a fini d'explorer toute sa descendance.
3. Un **arc arrière** pointe vers un sommet gris, c'est-à-dire ouvert mais pas fini.
4. **Kosaraju** : DFS, transposition, second DFS dans l'ordre décroissant des dates
   de fin.
MD,
                'solution' => <<<'MD'
**1. Parcours en largeur depuis A**

| Niveau | Sommets | Distance |
|---|---|---|
| 0 | A | 0 |
| 1 | B, C | 1 |
| 2 | D, E | 2 |
| 3 | F | 3 |
| 4 | G | 4 |

**Ordre de visite : A, B, C, D, E, F, G.**

Le BFS donne les plus courts chemins **en nombre d'arcs** puisque le graphe n'est
pas pondéré.

**2. Parcours en profondeur depuis A**

En prenant les voisins par ordre alphabétique :

```
A → B → D → F → G → C → E   (E n'a que F, déjà fini)
```

| Sommet | Date de découverte | Date de fin |
|---|---|---|
| A | 1 | 14 |
| B | 2 | 13 |
| D | 3 | 12 |
| F | 4 | 11 |
| G | 5 | 10 |
| C | 6 | 9 |
| E | 7 | 8 |

**Ordre de fin croissant : E, C, G, F, D, B, A.**

**3. Circuit**

**Oui : C → E → F → G → C.**

Le parcours en profondeur le détecte par un **arc arrière**. Depuis C (découvert
à l'instant 6, encore ouvert), on descend vers E puis F… mais F était déjà fini
dans cette exécution. Reprenons proprement : depuis G on atteint C, or C est encore
**gris** — découvert mais pas terminé, puisqu'il est un ancêtre dans l'arbre de
parcours. **Un arc vers un sommet gris est un arc arrière, et signale un circuit.**

**4. Composantes fortement connexes — Kosaraju**

*Étape 1.* DFS sur G, ordre de fin décroissant : **A, B, D, F, G, C, E**.

*Étape 2.* Transposer : `B→A`, `C→A`, `D→B`, `D→C`, `E→C`, `F→D`, `F→E`, `G→F`, `C→G`.

*Étape 3.* DFS sur Gᵀ dans l'ordre A, B, D, F, G, C, E :

| Départ | Sommets atteints | Composante |
|---|---|---|
| A | A | **{A}** |
| B | B | **{B}** |
| D | D | **{D}** |
| F | F, E, C, G | **{C, E, F, G}** |

Vérification : dans Gᵀ, depuis F on atteint D (déjà pris), E, puis depuis E on
atteint C, puis depuis C on atteint G, et depuis G on atteint F — la boucle est
fermée.

**Composantes fortement connexes : {A}, {B}, {D}, {C, E, F, G}.**

La grande composante correspond exactement au circuit trouvé en question 3, plus E
qui y participe.

**5. Tri topologique**

**Non, impossible.** Un tri topologique existe **si et seulement si** le graphe est
un DAG — orienté **sans circuit**. Or le circuit C → E → F → G → C interdit tout
ordre : C devrait précéder E, qui devrait précéder F, qui devrait précéder G,
qui devrait précéder C.

**6. Complexités** — toutes en listes d'adjacence :

| Algorithme | Coût |
|---|---|
| BFS | **O(n + m)** |
| DFS | **O(n + m)** |
| Détection de circuit par arc arrière | **O(n + m)** |
| Kosaraju | **O(n + m)** — deux DFS et une transposition |
| Tri topologique | **O(n + m)** |

En matrice d'adjacence, tous passeraient à **O(n²)**.
MD,
                'rubric' => [
                    ['label' => 'BFS : ordre de visite et distances par niveau', 'points' => 3],
                    ['label' => 'DFS : ordre de visite et dates de fin', 'points' => 3],
                    ['label' => 'Circuit C→E→F→G→C identifié', 'points' => 1],
                    ['label' => 'La détection est expliquée par l’arc arrière vers un sommet gris', 'points' => 1],
                    ['label' => 'Kosaraju : les trois étapes détaillées', 'points' => 2],
                    ['label' => 'Composantes {A}, {B}, {D}, {C,E,F,G}', 'points' => 2],
                    ['label' => 'Tri topologique impossible, justifié par le circuit', 'points' => 1],
                    ['label' => 'Complexités O(n+m) données, avec la structure précisée', 'points' => 2],
                ],
            ]],

            /* ============ EP C4 — Variations (poids 4) ============ */
            ['EP', 'C4', [
                'title' => 'Simuler une variante, et chiffrer le surcoût',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 4,
                'statement' => <<<'MD'
**1.** Une machine à **deux rubans** reconnaît `L = { ww | w ∈ {a,b}* }` en temps
linéaire. Décrivez son fonctionnement et comptez ses actions. *(4 pts)*

**2.** Combien coûterait la simulation de cette machine par une machine à **un
ruban** ? Donnez le théorème et l'ordre de grandeur obtenu. *(3 pts)*

**3.** Une machine **non déterministe** décide un langage en temps `n²`.
Quel est le coût de sa simulation déterministe ? Le langage reste-t-il décidable ? *(3 pts)*

**4.** On restreint l'alphabet de ruban à `{0, 1, ␣}`. Quel surcoût, et pourquoi
est-il négligeable ? *(2 pts)*

**5.** Énoncez la **thèse de l'invariance** et dites ce qu'elle garantit sur la
classe **P**. *(3 pts)*
MD,
                'hint' => "Pour la question 1 : avec deux rubans, on peut copier une moitié du mot puis comparer les deux en avançant simultanément. Le problème est de trouver le milieu.",
                'method' => <<<'MD'
1. Décrivez les deux rubans et le rôle de chacun, puis comptez les passages.
2. Appliquez le théorème de simulation, en remplaçant t(n) par la valeur trouvée.
3. Idem, avec le théorème du non-déterminisme. Attention : « décidable » et
   « décidable efficacement » ne sont pas la même question.
5. La thèse porte sur les modèles **raisonnables** et un surcoût **polynomial**.
MD,
                'solution' => <<<'MD'
**1. Machine à deux rubans pour `ww`**

*Principe.* Le mot fait `2k` symboles ; il faut vérifier que la première moitié
égale la seconde.

- **Passe 1** — déterminer la longueur et le milieu. On avance sur le ruban 1 en
  marquant une case sur deux sur le ruban 2. Quand le ruban 1 atteint le blanc,
  la tête du ruban 2 est au milieu. Coût : **O(n)**.
- **Passe 2** — copier la première moitié sur le ruban 2. Coût : **O(n)**.
- **Passe 3** — replacer la tête du ruban 2 au début, puis avancer les deux têtes
  **simultanément** en comparant symbole à symbole. Coût : **O(n)**.

**Total : O(n) actions élémentaires.** Le langage est reconnu en **temps linéaire**.

*Ce que permettent les deux rubans :* comparer deux positions distantes sans
aller-retour. Sur un seul ruban, chaque comparaison exigerait de traverser tout le mot.

**2. Simulation par une machine à un ruban**

> **Théorème.** Toute machine à k rubans en temps t(n) se simule par une machine à
> un ruban en **O(t(n)²)**.

Ici `t(n) = O(n)`, donc la simulation coûte **O(n²)**.

C'est cohérent : sur un ruban, reconnaître `ww` demande effectivement un aller-retour
par symbole, soit n × n opérations.

**Le surcoût est polynomial** — le carré d'un polynôme reste un polynôme. Les deux
machines décident donc le même langage **en temps polynomial** : `L ∈ P` dans les
deux modèles.

**3. Simulation d'une machine non déterministe**

> **Théorème.** Une machine non déterministe en temps t(n) se simule par une machine
> déterministe en **O(2^{O(t(n))})**.

Ici `t(n) = n²`, donc la simulation coûte **O(2^{O(n²)})** — **exponentiel**.

**Le langage reste décidable.** La simulation s'arrête toujours : l'arbre des choix
a une profondeur bornée par `n²` et un facteur de branchement fini, donc un nombre
fini de nœuds. On l'explore **en largeur** — en profondeur, une branche pourrait
poser problème.

Ce qu'on perd, c'est l'**efficacité**, pas la décidabilité. Le langage est dans
**NP** ; on ignore s'il est dans **P**.

**4. Alphabet à trois symboles**

Chaque symbole de Γ se code sur `⌈log₂|Γ|⌉` cases binaires. Lire un symbole d'origine
demande donc de lire ce nombre de cases.

**Surcoût : O(t(n) · log |Γ|)** — **logarithmique**.

Il est négligeable parce que `|Γ|` est une **constante** du problème, indépendante de
la taille de l'entrée. `log |Γ|` est donc lui-même une constante, et le surcoût est
en réalité **O(t(n))** à une constante multiplicative près : la classe de complexité
ne change pas du tout.

**5. Thèse de l'invariance**

> **Tous les modèles de calcul raisonnables se simulent mutuellement avec un surcoût
> polynomial.**

Ce qu'elle garantit : la classe **P** est **robuste**. Qu'on la définisse avec une
machine à un ruban, à k rubans, à alphabet binaire ou avec un langage de
programmation usuel, on obtient **exactement la même classe**.

C'est ce qui rend la notion de « problème traitable » indépendante du modèle, et
donc mathématiquement intéressante.

*Attention :* le non-déterminisme est précisément le cas où le surcoût n'est **pas**
polynomial. C'est pour cela qu'une machine non déterministe n'est pas un modèle
« raisonnable » au sens de la thèse — et que P = NP reste ouvert.
MD,
                'rubric' => [
                    ['label' => 'Machine à deux rubans : les trois passes décrites', 'points' => 2],
                    ['label' => 'Comptage O(n), avec la raison (pas d’aller-retour)', 'points' => 2],
                    ['label' => 'Théorème O(t(n)²) énoncé, appliqué : O(n²)', 'points' => 2],
                    ['label' => 'Le surcoût polynomial préserve l’appartenance à P', 'points' => 1],
                    ['label' => 'Théorème exponentiel énoncé, appliqué à t(n) = n²', 'points' => 2],
                    ['label' => 'Le langage reste décidable, avec le parcours en largeur', 'points' => 1],
                    ['label' => 'Surcoût logarithmique, négligeable car |Γ| est constant', 'points' => 2],
                    ['label' => 'Thèse de l’invariance énoncée, robustesse de P expliquée', 'points' => 3],
                ],
            ]],

            /* ============ EP C6 + C7 — Complexité (poids 4) ============ */
            ['EP', 'C6', [
                'title' => 'Classer un problème : P, NP, NP-complet',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 4,
                'statement' => <<<'MD'
**1.** Pour chacun des problèmes, dites s'il est dans **P**, dans **NP**, et s'il
est connu **NP-complet**. Justifiez en une ligne. *(6 pts)*

a. Décider si un graphe est connexe
b. Décider si un graphe admet un cycle hamiltonien
c. Décider si une formule booléenne est satisfiable (SAT)
d. Trier un tableau de n entiers
e. Décider si un entier de k bits est premier
f. Décider si un graphe admet un cycle eulérien

**2.** Montrez que **P ⊆ NP**. *(2 pts)*

**3.** Définissez **NP-complet**, puis expliquez pourquoi trouver un algorithme
polynomial pour **un seul** problème NP-complet démontrerait **P = NP**. *(4 pts)*

**4.** Un ami affirme : « NP signifie non polynomial, donc aucun problème de NP
n'est résoluble en temps polynomial. » Corrigez-le en deux phrases. *(2 pts)*
MD,
                'hint' => "Pour la question 1, demandez-vous à chaque fois : sait-on résoudre en temps polynomial ? Et sait-on **vérifier** une solution proposée en temps polynomial ?",
                'method' => <<<'MD'
1. **Dans P** : on connaît un algorithme polynomial.
   **Dans NP** : on sait **vérifier** une solution proposée en temps polynomial.
   **NP-complet** : dans NP, et tout problème de NP s'y réduit polynomialement.
2. Si un problème est dans P, quel certificat proposer pour le mettre dans NP ?
3. Utilisez la définition de la NP-complétude et la transitivité des réductions.
MD,
                'solution' => <<<'MD'
**1.**

| | P ? | NP ? | NP-complet ? | Justification |
|---|---|---|---|---|
| a. connexité | **oui** | oui | non | un parcours en O(n+m) suffit |
| b. cycle hamiltonien | inconnu | **oui** | **oui** | vérifier une permutation coûte O(n²) ; NP-complet par réduction depuis SAT |
| c. SAT | inconnu | **oui** | **oui** | vérifier une valuation est linéaire ; **premier problème** prouvé NP-complet (Cook-Levin) |
| d. tri | **oui** | oui | non | O(n log n) |
| e. primalité | **oui** | oui | non | algorithme AKS, polynomial en le nombre de **bits** |
| f. cycle eulérien | **oui** | oui | non | compter les degrés, O(n+m) |

*Le contraste b / f est celui qu'il faut savoir commenter :* toutes les **arêtes**
une fois, c'est trivial ; tous les **sommets** une fois, c'est NP-complet.

*Sur e :* attention à la taille de l'entrée. Un entier de k bits vaut jusqu'à 2ᵏ.
Tester tous les diviseurs jusqu'à √n coûte 2^{k/2} — **exponentiel en k**.
AKS est polynomial **en k**, ce qui est le bon critère.

**2. P ⊆ NP**

Soit `L ∈ P`. Il existe une machine déterministe M qui décide L en temps polynomial.

Pour montrer que `L ∈ NP`, il faut un **vérificateur** polynomial. Prenons le
certificat **vide** : le vérificateur ignore le certificat et exécute simplement M
sur l'entrée. Il répond correctement, en temps polynomial.

Donc **L ∈ NP**. ∎

*Formulation équivalente :* une machine déterministe est un cas particulier de
machine non déterministe — celle qui n'a jamais qu'un seul choix.

**3. NP-complet**

Un problème B est **NP-complet** si :

1. **B ∈ NP** ;
2. **tout** problème A de NP se **réduit** à B en temps polynomial, ce qui s'écrit
   `A ≤ₚ B`.

La seconde condition en fait un problème « au moins aussi dur » que tous les autres
de NP.

**Pourquoi un seul suffirait.** Supposons qu'on trouve un algorithme polynomial pour
un problème NP-complet B. Soit A un problème quelconque de NP.

Par la condition 2, il existe une réduction polynomiale `f` de A vers B. Pour décider
une instance `x` de A, on calcule `f(x)` — en temps polynomial — puis on décide
`f(x)` avec l'algorithme polynomial de B.

Le tout est polynomial, car **la composition de deux polynômes est un polynôme**.
Donc `A ∈ P`. Comme A était quelconque, **NP ⊆ P**, et avec la question 2,
**P = NP**. ∎

C'est ce qui donne leur importance aux problèmes NP-complets : ils sont tous liés,
et un seul suffirait à les faire tous tomber.

**4. La correction**

**NP signifie « non déterministe polynomial »**, pas « non polynomial ». C'est la
classe des problèmes décidables en temps polynomial par une machine **non
déterministe** — de façon équivalente, ceux dont une solution se **vérifie** en
temps polynomial.

Et **P ⊆ NP** : tous les problèmes polynomiaux sont dans NP. Le tri, la connexité
ou la primalité y sont, et ils sont parfaitement résolubles en temps polynomial.
MD,
                'rubric' => [
                    ['label' => 'Les six problèmes correctement classés', 'points' => 3],
                    ['label' => 'Chaque classement justifié en une ligne', 'points' => 3],
                    ['label' => 'P ⊆ NP démontré par le certificat vide (ou le cas particulier)', 'points' => 2],
                    ['label' => 'NP-complet défini par les deux conditions', 'points' => 2],
                    ['label' => 'La composition de deux polynômes reste polynomiale', 'points' => 1],
                    ['label' => 'Conclusion NP ⊆ P puis P = NP', 'points' => 1],
                    ['label' => 'NP = non déterministe polynomial, et P ⊆ NP rappelé', 'points' => 2],
                ],
            ]],

            ['EP', 'C7', [
                'title' => 'Analyser quatre fragments et conclure en temps réel',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'statement' => <<<'MD'
**1.** Donnez la complexité de chaque fragment, **en posant le comptage**. *(8 pts)*

```
(a) somme ← 0                        (b) i ← n
    pour i de 1 à n :                    tant que i > 1 :
        pour j de 1 à i :                    i ← i / 3
            somme ← somme + 1

(c) pour i de 1 à n :                (d) pour i de 1 à n :
        j ← n                             pour j de 1 à n :
        tant que j > 0 :                      pour k de 1 à n :
            j ← j / 2                             x ← x + 1
```

**2.** Une machine effectue 10⁸ opérations par seconde. Pour chaque fragment,
donnez le **temps d'exécution** sur n = 10⁵, avec sa valeur et son **unité**. *(4 pts)*

**3.** Le fragment (d) est trop lent. On dispose d'un ordinateur **1 000 fois plus
rapide**. De combien la taille traitable en une seconde augmente-t-elle ? *(3 pts)*

Rappel : la réponse à la question 3 est la vraie leçon du chapitre.
MD,
                'hint' => "Question 3 : si l'ancien ordinateur traitait n en une seconde, le nouveau traite n' tel que n'³ = 1000 × n³. Résolvez.",
                'method' => <<<'MD'
1. Pour chaque fragment : combien de tours fait chaque boucle, et les boucles
   sont-elles imbriquées ou successives ? Une division répétée donne un logarithme.
2. Nombre d'opérations divisé par 10⁸, puis convertissez en une unité lisible.
3. Posez l'équation et résolvez en n'.
MD,
                'solution' => <<<'MD'
**1.**

**(a) O(n²).** La boucle interne fait `i` tours. Total :
```
1 + 2 + … + n = n(n+1)/2 ≈ n²/2  →  O(n²)
```
Le fait que la boucle interne dépende de `i` ne change que la constante.

**(b) O(log n).** À chaque tour, `i` est **divisé par 3**. Partant de n, il faut
`log₃ n` divisions pour descendre à 1.
```
log₃ n = log₂ n / log₂ 3  →  O(log n)
```
La base du logarithme n'apparaît pas dans la notation O : elle n'est qu'une constante
multiplicative.

**(c) O(n log n).** Boucle externe : n tours. Boucle interne : `j` divisé par 2,
donc `log₂ n` tours. Total : `n · log₂ n`.

**(d) O(n³).** Trois boucles imbriquées de 1 à n : `n × n × n`.

**2. Temps d'exécution** pour n = 10⁵, à 10⁸ opérations par seconde

| Fragment | Opérations | Temps |
|---|---|---|
| (a) O(n²) | (10⁵)²/2 = 5 × 10⁹ | **50 secondes** |
| (b) O(log n) | log₃(10⁵) ≈ 10 | **0,1 microseconde** |
| (c) O(n log n) | 10⁵ × 17 ≈ 1,7 × 10⁶ | **17 millisecondes** |
| (d) O(n³) | (10⁵)³ = 10¹⁵ | 10⁷ s ≈ **116 jours** |

**3. L'ordinateur 1 000 fois plus rapide**

Soit `n` la taille traitée en une seconde par l'ancien, `n'` par le nouveau.

```
n'³ = 1000 × n³
n'  = n × ∛1000
n'  = 10 n
```

**La taille traitable est multipliée par 10, pas par 1 000.**

C'est la leçon du chapitre. Sur un algorithme cubique, mille fois plus de puissance
ne donne que dix fois plus de données traitées. Sur un algorithme en **O(2ⁿ)**,
ce serait pire encore : `2^{n'} = 1000 × 2ⁿ` donne `n' = n + log₂ 1000 ≈ n + 10`.
Mille fois plus rapide, et l'on gagne **dix unités** sur la taille.

**Changer d'algorithme bat toujours changer de machine.** Passer de O(n³) à
O(n log n) sur le même ordinateur ferait bien davantage que multiplier la puissance
par mille.
MD,
                'rubric' => [
                    ['label' => '(a) O(n²) avec la somme 1+2+…+n posée', 'points' => 2],
                    ['label' => '(b) O(log n) justifié par la division par 3', 'points' => 2],
                    ['label' => '(c) O(n log n) : boucle externe × divisions', 'points' => 2],
                    ['label' => '(d) O(n³) : trois boucles imbriquées', 'points' => 2],
                    ['label' => 'Les quatre temps calculés, avec valeur ET unité', 'points' => 4],
                    ['label' => 'n\' = 10 n, obtenu par la racine cubique', 'points' => 2],
                    ['label' => 'La conclusion : changer d’algorithme bat changer de machine', 'points' => 1],
                ],
            ]],

            /* ============ EP C8 — Tris (poids 4) ============ */
            ['EP', 'C8', [
                'title' => 'Compter comparaisons et affectations, séparément',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 3,
                'statement' => <<<'MD'
Tableau `T = [4, 1, 3, 2]`.

**1.** Déroulez le **tri par sélection** en tableau. Comptez **séparément** les
comparaisons et les affectations. *(4 pts)*

**2.** Déroulez le **tri par insertion** sur le même tableau, avec les deux
comptages. *(4 pts)*

**3.** Pour chacun des quatre tris du cours, donnez le nombre de **comparaisons**
au meilleur cas, au pire cas et en moyenne. *(4 pts)*

**4.** Quel tri choisiriez-vous si déplacer un élément coûtait **cent fois** plus
cher que le comparer ? Justifiez par les chiffres. *(2 pts)*

**5.** Démontrez qu'aucun tri par comparaisons ne peut faire mieux que
**Ω(n log n)** comparaisons au pire cas. *(4 pts)*
MD,
                'hint' => "Question 4 : regardez la colonne des affectations, pas celle des comparaisons. Un tri se distingue nettement des autres sur ce critère.",
                'method' => <<<'MD'
1. Pour la sélection : à chaque tour, comptez les comparaisons du parcours, puis
   l'échange éventuel (trois affectations).
2. Pour l'insertion : comptez les décalages, qui sont des affectations.
5. Utilisez l'arbre de décision : nœuds = comparaisons, feuilles = permutations.
MD,
                'solution' => <<<'MD'
**1. Tri par sélection sur `[4, 1, 3, 2]`**

| Tour | Zone examinée | Comparaisons | Min | Échange | Tableau |
|---|---|---|---|---|---|
| 0 | T[1..3] | **3** | 1 (idx 1) | T[0]↔T[1] | [1, 4, 3, 2] |
| 1 | T[2..3] | **2** | 2 (idx 3) | T[1]↔T[3] | [1, 2, 3, 4] |
| 2 | T[3..3] | **1** | 3 (idx 2) | aucun | [1, 2, 3, 4] |

**Comparaisons : 3 + 2 + 1 = 6.** Conforme à `n(n−1)/2 = 6`.
**Échanges : 2**, soit **6 affectations** (trois par échange).

**2. Tri par insertion sur `[4, 1, 3, 2]`**

| Étape | Élément inséré | Comparaisons | Décalages | Tableau |
|---|---|---|---|---|
| 1 | 1 | 1 (contre 4) | 1 | [1, 4, 3, 2] |
| 2 | 3 | 2 (contre 4, puis 1) | 1 | [1, 3, 4, 2] |
| 3 | 2 | 3 (contre 4, 3, puis 1) | 2 | [1, 2, 3, 4] |

**Comparaisons : 6.** **Affectations : 4 décalages + 3 placements = 7.**

Sur ce tableau, l'insertion compare autant que la sélection mais **déplace davantage**.

**3. Comparaisons des quatre tris**

| Tri | Meilleur cas | Pire cas | Moyenne |
|---|---|---|---|
| **Sélection** | n(n−1)/2 → **O(n²)** | n(n−1)/2 → **O(n²)** | **O(n²)** |
| **Bulles** (avec drapeau) | n−1 → **O(n)** | **O(n²)** | **O(n²)** |
| **Insertion** | n−1 → **O(n)** | **O(n²)** | **O(n²)** |
| **Rapide** | **O(n log n)** | **O(n²)** | **O(n log n)** |

Le tri par sélection est le seul dont le nombre de comparaisons **ne dépend jamais
des données**.

**4. Si déplacer coûte cent fois plus cher que comparer**

Regardons les **affectations** :

| Tri | Affectations, pire cas |
|---|---|
| **Sélection** | **3(n−1) → O(n)** |
| Bulles | O(n²) |
| Insertion | O(n²) |
| Rapide | O(n log n) |

**Je choisis le tri par sélection.**

Chiffrons pour n = 1 000, avec un coût de 1 pour une comparaison et 100 pour une
affectation :

- **Sélection** : 500 000 comparaisons + 3 000 affectations
  = 500 000 + 300 000 = **800 000 unités**.
- **Insertion**, pire cas : 500 000 comparaisons + 500 000 affectations
  = 500 000 + 50 000 000 = **50 500 000 unités**.

La sélection est **soixante fois moins chère**. Son handicap habituel — toujours
O(n²) comparaisons — devient négligeable quand ce sont les déplacements qui coûtent.

C'est exactement le cas quand on trie des objets volumineux plutôt que des entiers.

**5. La borne inférieure Ω(n log n)**

*Le modèle.* Un tri par comparaisons se représente par un **arbre de décision
binaire** : chaque nœud interne est une comparaison `T[i] < T[j] ?`, ses deux fils
correspondent aux deux réponses possibles, et chaque **feuille** est une permutation
du tableau d'entrée.

*Étape 1 — le nombre de feuilles.* L'algorithme doit pouvoir produire **n'importe
laquelle** des permutations, sinon il existerait une entrée qu'il trierait mal.
L'arbre a donc **au moins n! feuilles**.

*Étape 2 — la hauteur.* Un arbre binaire de hauteur `h` a au plus `2^h` feuilles.
Donc :
```
2^h ≥ n!      d'où      h ≥ log₂(n!)
```

*Étape 3 — Stirling.* La formule de Stirling donne `n! ≈ (n/e)ⁿ √(2πn)`, d'où :
```
log₂(n!) = n log₂ n − n log₂ e + O(log n) = Θ(n log n)
```

*Conclusion.* La hauteur de l'arbre est la longueur du plus long chemin, c'est-à-dire
le **nombre de comparaisons dans le pire cas**. Elle vaut au moins `Θ(n log n)`.

**Aucun tri par comparaisons ne peut faire mieux.** ∎

*Nuance à mentionner :* les tris **sans comparaison** — tri par comptage, tri radix —
échappent à cette borne parce qu'ils ne rentrent pas dans le modèle. Ils atteignent
O(n), au prix d'hypothèses sur les données.
MD,
                'rubric' => [
                    ['label' => 'Sélection : tableau de déroulement, 6 comparaisons', 'points' => 2],
                    ['label' => 'Sélection : 2 échanges = 6 affectations, comptées séparément', 'points' => 2],
                    ['label' => 'Insertion : déroulement avec comparaisons et décalages distincts', 'points' => 4],
                    ['label' => 'Les quatre tris avec meilleur, pire et moyen cas', 'points' => 4],
                    ['label' => 'Sélection choisie, avec un calcul chiffré à l’appui', 'points' => 2],
                    ['label' => 'Borne inférieure : arbre de décision et n! feuilles', 'points' => 2],
                    ['label' => 'Borne inférieure : hauteur ≥ log₂(n!) puis Stirling', 'points' => 2],
                ],
            ]],
        ];
    }
}