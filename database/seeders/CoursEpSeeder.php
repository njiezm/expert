<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours d'EP, première partie : compter, et savoir ce qu'on compte.
 *
 * Note obtenue en janvier : 7/20 sur le sujet « Graphopolis ».
 *
 * La phrase la plus lourde de conséquence de toute la copie est celle-ci, à
 * l'exercice 2 question 1 :
 *
 *   « Ici nous avons 2 boucles imbriquées avec des conditions. Plus n sera
 *   grand, plus le nombre d'actions élémentaires sera logarithmiquement grand
 *   avec Θ(log n). »
 *
 * Deux erreurs superposées. Deux boucles imbriquées coûtent O(n²), pas O(log n).
 * Et « logarithmiquement grand » est employé au sens de « qui grandit beaucoup »
 * — c'est exactement l'inverse : le logarithme est la fonction croissante la
 * plus lente qu'on rencontre.
 *
 * Ce module entier consiste à mesurer des coûts. Une confusion sur l'échelle de
 * croissance ne coûte pas un point : elle rend chaque réponse fausse. La séance
 * 2 y est donc consacrée en entier, et on repart de « qu'est-ce qu'un
 * logarithme ».
 */
class CoursEpSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'EP')->first();

        if (! $subject) {
            return;
        }

        foreach ($this->seances() as $i => $seance) {
            $chapter = isset($seance['chapitre'])
                ? Chapter::where('subject_id', $subject->id)->where('code', $seance['chapitre'])->first()
                : null;

            unset($seance['chapitre']);

            Seance::updateOrCreate(
                ['subject_id' => $subject->id, 'slug' => Str::slug($seance['title'])],
                $seance + ['chapter_id' => $chapter?->id, 'position' => $i + 1]
            );
        }
    }

    /* ==================================================================== */

    private function seances(): array
    {
        return [

            /* ================= Séance 1 ================= */
            [
                'title' => "Ce que l'épreuve demande",
                'chapitre' => 'C2',
                'duree_min' => 25,
                'prerequis' => "Aucun.",
                'intro' => <<<'MD'
Bonjour.

« Évaluation de programmes » : le titre dit tout. Ce module ne vous apprend pas
à écrire des programmes, il vous apprend à **les mesurer** — combien d'étapes
faut-il, ce problème est-il seulement soluble, et à quel prix.

Une seule notion traverse les sept chapitres : **l'ordre de grandeur**. Et c'est
précisément là que votre copie de janvier a décroché, dès la première question de
complexité.

On regarde d'abord l'épreuve, puis on nomme le problème.
MD,
                'body' => <<<'MD'
## Le sujet de janvier 2026

Deux heures, deux parties.

| | Exercice | Sujet |
|---|---|---|
| **Partie I** | 1 | Une machine de Turing pour l'ensemble stable |
| | 2 | Compter les actions élémentaires d'un algorithme |
| | 3 | P, NP, NP-complet |
| | 4 | Réductions : Ensemble Stable → Couverture → Dominant |
| **Partie II** | — | Tableau dynamique : **analyse amortie** |

Le fil est unique : **le graphe de Graphopolis** sert d'exemple du début à la
fin. Une fois compris, il resservait à chaque exercice.

## Le squelette, sur trois sessions

| | Janvier 2026 | Mai 2025 | Mai 2024 |
|---|---|---|---|
| | Machine de Turing | Machine de Turing | Machine de Turing |
| | P, NP, NP-complet | SAT, P et NP | P et NP |
| | Comptage d'actions | Fonctions mystères | Complexité |
| | Réductions | Coloriage, réduction 3-SAT | Matrices creuses |
| | Analyse amortie | — | — |

**Trois blocs reviennent à chaque session** : machine de Turing, classes de
complexité, comptage. Un quatrième exercice varie — mais il s'agit toujours de
**compter quelque chose**.

## La phrase qui a coûté le plus

Exercice 2, question 1. Voici votre réponse, recopiée :

> « Ici nous avons **2 boucles imbriquées** avec des conditions. Plus n sera
> grand, plus le nombre d'actions élémentaires sera **logarithmiquement grand
> avec Θ(log n)**. »

Il y a deux erreurs, et elles sont indépendantes.

**La première.** Deux boucles imbriquées ne coûtent pas `Θ(log n)`. Elles
coûtent **O(n²)** — pour chacune des `n` valeurs de la boucle externe, on fait
`n` tours de la boucle interne.

**La seconde, plus profonde.** L'expression « logarithmiquement grand » est
employée au sens de « qui grandit beaucoup ». **C'est exactement l'inverse.** Le
logarithme est la fonction croissante **la plus lente** qu'on rencontre en
algorithmique. Un algorithme en `Θ(log n)` sur un milliard d'éléments fait
**trente** opérations.

Autrement dit : vous avez dit « c'est très lent » en employant le mot qui
signifie « c'est extrêmement rapide ».

## Pourquoi ce n'est pas une faute parmi d'autres

Dans un module qui mesure des coûts, une échelle de croissance mal lue **rend
toutes les réponses fausses**, y compris celles où le raisonnement est bon.

Et on en trouve la trace ailleurs sur la copie. À la question 2 suivante, la
réponse s'interrompt sur :

> « si chaque calcul dure 10⁻⁶ seconde »

et s'arrête là. **Le calcul n'est pas fait.** Or c'était toute la question : de
combien de temps parle-t-on pour `n = 10`, `n = 20`, `n = 40` ?

C'est la même difficulté sous une autre forme : **on ne sait pas encore convertir
un ordre de grandeur en quelque chose de concret.**

## La bonne nouvelle

Ce n'est pas un manque de connaissances : c'est **une seule notion mal installée**.
Elle s'installe en une séance, et une fois en place, elle débloque tout le
module — le comptage, les classes de complexité, l'analyse amortie, et jusqu'à la
borne des tris.

C'est pour ça que la séance 2, la prochaine, ne parle que de logarithmes.

## Les autres pertes de janvier

| Question | Ce qui s'est passé |
|---|---|
| Ex. 1 Q1 | paraphrase de l'énoncé — le correcteur a mis « **?** » |
| Ex. 2 Q2 | interrompue avant le calcul |
| Ex. 3 Q2 | définition **circulaire** : « NP est par définition décidable donc p ⊆ NP est décidable » — « **?** » |
| Ex. 3 Q3 | **vide** — c'était « montrer que l'Ensemble Stable est dans NP », six lignes |
| Ex. 4 | quelques lignes de notation ensembliste, **aucune construction** |

Retenez la ligne « Ex. 3 Q3 vide ». Elle demandait de donner un **certificat** et
un **vérificateur**, ce qui tient en six lignes une fois la méthode connue. On la
verra séance 9.

## Le plan des douze séances

| | Séance | Chapitre |
|---|---|---|
| 1 | Ce que l'épreuve demande | — |
| 2 | **Ce qu'est un logarithme** | C7 |
| 3 | Compter les actions élémentaires | C7 |
| 4 | Les notations O, Ω et Θ | C7 |
| 5 | Problème, algorithme, calculabilité | C2 |
| 6 | La machine de Turing | C3 |
| 7 | **Construire une machine de Turing** | C3 · C4 |
| 8 | Décidabilité et indécidabilité | C5 |
| 9 | **P, NP et NP-complet** | C6 |
| 10 | Les réductions polynomiales | C6 |
| 11 | L'analyse amortie | C7 |
| 12 | Les tris, et composer la copie du 25 août | C8 |

## Une consigne imprimée sur le sujet

Le sujet de mai 2025 la donnait explicitement, et elle vaut pour tous :

> Les réponses doivent être **claires, structurées et sans ambiguïté**, avec des
> **raisonnements détaillés**. Chaque affirmation doit être **justifiée
> rigoureusement en utilisant les définitions appropriées**.

« En utilisant les définitions appropriées » : dans ce module, **on cite la
définition avant de s'en servir**. C'est ce qui manquait à l'exercice 3 de
janvier, où une définition approximative a produit un raisonnement circulaire.
MD,
                'recap' => <<<'MD'
- Trois blocs reviennent à chaque session : **machine de Turing**, **classes de
  complexité**, **comptage d'actions**. Le quatrième exercice varie mais consiste
  toujours à compter.
- L'erreur centrale de janvier : « deux boucles imbriquées → Θ(log n) ». Deux
  boucles imbriquées coûtent **O(n²)**.
- Et « logarithmiquement grand » a été employé pour dire « qui grandit beaucoup ».
  **Le logarithme est la fonction croissante la plus lente qu'on rencontre.**
- Dans un module qui mesure des coûts, **une échelle mal lue rend toutes les
  réponses fausses**. C'est une seule notion, et elle s'installe en une séance.
- Le sujet exige de **citer la définition avant de s'en servir**.
MD,
            ],

            /* ================= Séance 2 ================= */
            [
                'title' => "Ce qu'est un logarithme",
                'chapitre' => 'C7',
                'duree_min' => 35,
                'prerequis' => "Aucun. On repart de zéro : cette séance ne suppose rien d'autre que la multiplication.",
                'intro' => <<<'MD'
Une séance sur un seul objet.

Le logarithme a mauvaise réputation parce qu'on le rencontre d'abord comme une
touche de calculatrice, sans jamais dire à quelle question il répond. On va
commencer par cette question, et on n'écrira aucune formule avant de l'avoir
comprise.

À la fin, vous saurez reconnaître d'un coup d'œil si un morceau de code est en
`log n`, en `n`, en `n²` ou en `2ⁿ` — et vous saurez ce que ça veut dire en
secondes.
MD,
                'body' => <<<'MD'
## La question à laquelle le logarithme répond

> **Combien de fois puis-je couper `n` en deux avant d'arriver à 1 ?**

C'est tout. Ce nombre s'appelle **`log₂(n)`**, le logarithme en base 2 de `n`.

Comptons ensemble, à voix haute, pour `n = 1000` :

```
1000 → 500 → 250 → 125 → 62 → 31 → 15 → 7 → 3 → 1
```

**Dix coupes.** Donc `log₂(1000) ≈ 10`.

Refaisons pour un million :

```
1 000 000 → 500 000 → 250 000 → ... → 1
```

**Vingt coupes.** `log₂(1 000 000) ≈ 20`.

Et pour un milliard : **trente coupes**.

Arrêtez-vous une seconde sur ces trois nombres.

| `n` | `log₂(n)` |
|---|---|
| mille | **10** |
| un million | **20** |
| un milliard | **30** |

**Multiplier `n` par mille n'ajoute que dix au logarithme.** Voilà pourquoi le
logarithme est la fonction croissante la plus lente qu'on rencontre : elle est
pratiquement plate.

## L'autre façon de le dire

`log₂(n)` est **l'exposant qu'il faut donner à 2 pour obtenir `n`** :

```
2^k = n     ⟺     k = log₂(n)
```

Vérifions : `2¹⁰ = 1024`, donc `log₂(1024) = 10`. `2²⁰ ≈ 10⁶`, donc
`log₂(10⁶) ≈ 20`.

**Le logarithme et la puissance sont deux fonctions inverses l'une de l'autre.**
La puissance explose ; le logarithme rampe.

## Les valeurs à connaître

Recopiez-les. Elles suffisent à toute l'épreuve.

| `n` | `log₂(n)` |
|---|---|
| 2 | 1 |
| 8 | 3 |
| 16 | 4 |
| 100 | ≈ 7 |
| 1 024 | 10 |
| 10⁶ | ≈ 20 |
| 10⁹ | ≈ 30 |

Et la conversion, si on vous donne un logarithme dans une autre base :

```
log₂(n) = ln(n) / ln(2) = log₁₀(n) / log₁₀(2) ≈ 3,32 × log₁₀(n)
```

En algorithmique, **la base ne change rien à la complexité** : passer de la base
2 à la base 10 multiplie par une constante, et les constantes disparaissent dans
la notation O. On écrit donc souvent `O(log n)` sans préciser la base.

## L'échelle de croissance

Voici la seule échelle à connaître, de la plus lente à la plus rapide :

```
1  <  log n  <  √n  <  n  <  n log n  <  n²  <  n³  <  2ⁿ  <  n!
```

Et voici ce que ça donne en nombre d'opérations :

| `n` | `log n` | `n` | `n log n` | `n²` | `2ⁿ` |
|---|---|---|---|---|---|
| 10 | 3 | 10 | 33 | 100 | 1 024 |
| 100 | 7 | 100 | 664 | 10 000 | 10³⁰ |
| 1 000 | 10 | 1 000 | 9 966 | 10⁶ | 10³⁰¹ |
| 10⁶ | 20 | 10⁶ | 2×10⁷ | 10¹² | — |

Regardez la colonne `2ⁿ` : pour `n = 100`, elle vaut **10³⁰**. Il y a environ
10⁸⁰ atomes dans l'univers observable. Un algorithme en `2ⁿ` sur cent éléments
n'est pas « lent » : il est **impossible**.

## D'où vient chaque forme

C'est la partie qui répond directement à votre erreur de janvier.

| Ce que fait le code | Coût |
|---|---|
| une opération, sans boucle | **O(1)** |
| une boucle qui **divise par 2** à chaque tour | **O(log n)** |
| une boucle qui parcourt `n` éléments | **O(n)** |
| une boucle sur `n` contenant une boucle qui divise par 2 | **O(n log n)** |
| **deux boucles imbriquées** sur `n` | **O(n²)** |
| trois boucles imbriquées | **O(n³)** |
| énumérer tous les **sous-ensembles** de `n` éléments | **O(2ⁿ)** |
| énumérer toutes les **permutations** | **O(n!)** |

**La règle en une phrase :**

> **Le logarithme apparaît quand on DIVISE. La puissance apparaît quand on
> IMBRIQUE.**

Deux boucles imbriquées ne divisent rien. Elles multiplient. **O(n²)**.

## Les trois formes en code

### Celle qui donne `O(n)`

```
pour i ← 1 à n faire
    faire quelque chose
```

`n` tours. Le compteur avance de 1 en 1.

### Celle qui donne `O(n²)`

```
pour i ← 1 à n faire
    pour j ← 1 à n faire
        faire quelque chose
```

`n` tours × `n` tours = **`n²`**. **C'était le cas de votre exercice 2.**

### Celle qui donne `O(log n)`

```
i ← n
tant que i > 1 faire
    i ← i / 2                 % ← LA division
    faire quelque chose
```

Regardez la ligne marquée. **Le compteur est divisé, pas décrémenté.** C'est ça,
et rien d'autre, qui produit un logarithme.

Même forme, écrite dans l'autre sens :

```
i ← 1
tant que i < n faire
    i ← i × 2                 % ← LA multiplication du compteur
```

`i` prend les valeurs 1, 2, 4, 8, 16… Il atteint `n` après `log₂(n)` tours.

## Les trois endroits où le log apparaît vraiment

Retenez-les : ce sont eux qu'on rencontre à l'examen.

1. **La recherche dichotomique** dans un tableau trié. On coupe l'intervalle en
   deux à chaque comparaison. `O(log n)`.
2. **La hauteur d'un arbre binaire équilibré** à `n` sommets. Chaque niveau double
   le nombre de sommets, donc il faut `log₂(n)` niveaux. C'est pourquoi la
   recherche dans un arbre équilibré coûte `O(log n)`.
3. **La profondeur de récursion d'un tri fusion.** On coupe le tableau en deux à
   chaque appel, d'où `log n` niveaux ; à chaque niveau on fait `O(n)` travail,
   d'où **`O(n log n)`** au total.

Dans les trois cas : **on divise par deux**.

## Le sens de la phrase « logarithmiquement grand »

Dans le langage courant, « astronomique » veut dire « énorme ». On pourrait
croire que « logarithmique » suit la même logique. C'est l'inverse.

| Mot | En mathématiques |
|---|---|
| **exponentiel** | qui explose — `2ⁿ` |
| **quadratique** | qui grandit vite — `n²` |
| **linéaire** | qui grandit proportionnellement — `n` |
| **logarithmique** | **qui grandit à peine** — `log n` |

**Dire d'un algorithme qu'il est logarithmique, c'est en faire l'éloge.** C'est
le meilleur qu'on puisse espérer après le temps constant.

## Convertir en secondes

C'était la question 2 de l'exercice 2, celle qui s'est arrêtée en chemin. La
méthode tient en deux lignes.

**Temps = nombre d'opérations × durée d'une opération.**

Et pour rendre le résultat lisible, ces repères :

| Secondes | En clair |
|---|---|
| 1 | une seconde |
| 60 | une minute |
| 3 600 | une heure |
| 86 400 | un jour |
| **3,15 × 10⁷** | **une année** |

**Retenez qu'une année vaut environ 3×10⁷ secondes.** C'est le seul nombre à
mémoriser, et il transforme n'importe quel compte d'opérations en une phrase
compréhensible.

### Un exemple complet

Un algorithme fait `2ⁿ` opérations, chacune en `10⁻⁶` seconde. Que vaut `n = 50` ?

```
2⁵⁰ ≈ 10¹⁵ opérations
10¹⁵ × 10⁻⁶ = 10⁹ secondes
10⁹ / (3×10⁷) ≈ 33 ans
```

**Trente-trois ans.** Voilà ce que veut dire « exponentiel ». Et si on passe à
`n = 60`, on multiplie encore par 2¹⁰ ≈ 1000 : **trente-trois mille ans.**

## Le réflexe de vérification

Un dernier conseil, qui vaut pour toute l'épreuve.

Quand vous obtenez un résultat en secondes, **convertissez-le en unité humaine
et demandez-vous s'il est plausible.** Une erreur d'un facteur mille se voit
immédiatement quand on écrit « trente ans » au lieu de « trente mille ans » ;
elle passe inaperçue quand on laisse « 10⁹ secondes ».

C'est le meilleur garde-fou contre les erreurs d'ordre de grandeur — et on verra
à la séance suivante que même un corrigé officiel peut s'y tromper d'un facteur
un million.
MD,
                'recap' => <<<'MD'
- **`log₂(n)` = combien de fois on peut couper `n` en deux avant d'atteindre 1.**
  Autrement dit : `2^k = n ⟺ k = log₂(n)`.
- **Mille → 10 · un million → 20 · un milliard → 30.** Multiplier `n` par mille
  n'ajoute que dix.
- L'échelle : `1 < log n < √n < n < n log n < n² < n³ < 2ⁿ < n!`
- **Le logarithme apparaît quand on DIVISE. La puissance quand on IMBRIQUE.**
  Deux boucles imbriquées → **O(n²)**, jamais `log n`.
- Le log en code, c'est `i ← i / 2` ou `i ← i × 2` — **le compteur est divisé ou
  multiplié, pas incrémenté**.
- Trois sources réelles : **dichotomie**, **hauteur d'arbre équilibré**,
  **profondeur du tri fusion**.
- **« Logarithmique » veut dire « qui grandit à peine ».** C'est un éloge.
- **Une année ≈ 3 × 10⁷ secondes.** Toujours convertir en unité humaine pour
  vérifier l'ordre de grandeur.
MD,
            ],

            /* ================= Séance 3 ================= */
            [
                'title' => 'Compter les actions élémentaires',
                'chapitre' => 'C7',
                'duree_min' => 35,
                'prerequis' => "La séance 2. On refait l'exercice 2 de janvier en entier, calcul de temps compris.",
                'intro' => <<<'MD'
On applique la séance précédente à l'exercice 2 de janvier, question par
question — y compris la question 2 qui s'était arrêtée avant le calcul.

Le comptage d'actions élémentaires est le geste de base de ce module. Il obéit à
une méthode simple, en trois temps, et une fois qu'on l'a, l'exercice devient
mécanique.

On finira par une vérification qui met en défaut le corrigé officiel — et qui
montre exactement pourquoi le réflexe de la fin de la séance 2 est
indispensable.
MD,
                'body' => <<<'MD'
## La méthode, en trois temps

### 1. Identifier l'action élémentaire

**L'énoncé la donne presque toujours.** Celui de janvier écrit :

> « On considèrera que le test de savoir si `u` est dans `S` est l'opération
> élémentaire de cet algorithme. »

**Recopiez cette phrase dans votre réponse.** Elle fixe ce qu'on compte, et sans
elle le comptage n'a pas de sens.

### 2. Compter de l'intérieur vers l'extérieur

On part de l'instruction la plus profonde, on remonte boucle par boucle, et **on
multiplie**.

### 3. Simplifier en O

On garde le terme dominant, on jette les constantes. C'est la séance 4.

## L'exercice 2 de janvier

```
pour chaque sous-ensemble S de V :
    stable := vrai
    pour chaque arête (u,v) de E :
        si u et v sont tous deux dans S :
            stable := faux
    si stable et |S| >= k :
        retourner vrai
retourner faux
```

### Question 1 — le coût `f(n, m)`

Comptons de l'intérieur vers l'extérieur.

**Le plus profond.** Le test `si u et v sont tous deux dans S` contient **deux**
opérations élémentaires : « `u ∈ S` ? » et « `v ∈ S` ? ».

**La boucle interne.** Elle parcourt les arêtes. Il y en a `m`. Donc
`m × 2` opérations.

**La boucle externe.** Elle parcourt **tous les sous-ensembles de `V`**.
Combien y en a-t-il ?

> Pour construire un sous-ensemble, on décide pour **chacun** des `n` sommets
> s'il est dedans ou dehors. Deux choix, `n` fois. **`2ⁿ` sous-ensembles.**

Ce raisonnement est à savoir refaire : c'est le même qui explique pourquoi la
plus grande sous-séquence a `2ⁿ` candidats, et pourquoi SAT en a `2ⁿ`.

**Le total :**

```
f(n, m) =    2ⁿ      ×     m      ×     2      =  2·m·2ⁿ
          sous-ens.     arêtes      tests/arête
```

**Écrivez la décomposition avec ses trois étiquettes**, comme ci-dessus. Le
correcteur voit d'un coup d'œil que chaque facteur est justifié — et si l'un est
faux, les deux autres restent crédités.

### La complexité asymptotique

`f(n, m) = 2m·2ⁿ`. On jette la constante 2 :

> **O(m · 2ⁿ)**

Et si l'on veut l'exprimer en fonction de `n` seul, sachant que `m ≤ n(n−1)/2` :

> **O(n² · 2ⁿ)**, dominé par le facteur exponentiel.

**Ce n'est ni `O(n²)` ni `O(log n)`. C'est exponentiel**, parce que la boucle
externe énumère des sous-ensembles.

## Question 2 — le caractère « raisonnable »

*« Discuter du caractère raisonnable de cet algorithme pour n = 10, 20, 40. Que
devient le temps de calcul si chaque action atomique dure 10⁻⁶ secondes ? »*

C'est la question qui s'est arrêtée en chemin. Elle se fait en trois colonnes.

On suppose un graphe **dense**, donc `m ≈ n(n−1)/2`.

| `n` | `m` | `f(n,m) = 2·m·2ⁿ` | Temps à 10⁻⁶ s |
|---|---|---|---|
| 10 | 45 | 2 × 45 × 1 024 ≈ **9,2 × 10⁴** | **0,09 seconde** |
| 20 | 190 | 2 × 190 × 10⁶ ≈ **4,0 × 10⁸** | **400 s ≈ 7 minutes** |
| 40 | 780 | 2 × 780 × 10¹² ≈ **1,7 × 10¹⁵** | **1,7 × 10⁹ s ≈ 54 ans** |

Détaillons la dernière ligne, parce que c'est là que tout se joue :

```
f(40, 780) = 2 × 780 × 2⁴⁰
2⁴⁰ ≈ 1,1 × 10¹²
f ≈ 1 560 × 1,1 × 10¹² ≈ 1,7 × 10¹⁵ actions

temps = 1,7 × 10¹⁵ × 10⁻⁶ = 1,7 × 10⁹ secondes
en années : 1,7 × 10⁹ / (3,15 × 10⁷) ≈ 54 ans
```

### L'interprétation

C'est elle qu'on note, pas seulement les chiffres :

> Pour **n = 10**, l'algorithme est parfaitement praticable : moins d'un dixième
> de seconde.
>
> Pour **n = 20**, on est déjà à sept minutes — significatif pour un graphe qui
> ne compte que vingt carrefours.
>
> Pour **n = 40**, cinquante-quatre ans. L'algorithme est **totalement
> impraticable**, et pourtant on n'a fait que doubler `n`.
>
> C'est le propre d'un algorithme **exponentiel** : chaque sommet ajouté
> **double** le temps de calcul. Passer de 40 à 50 sommets multiplierait encore
> par mille.

**Cette dernière phrase vaut des points.** Elle montre qu'on a compris la nature
du phénomène, pas seulement fait une division.

## Un mot sur le corrigé officiel

Le corrigé annonce, pour `n = 40` : **« ≈ 54 millions d'années »**.

Le calcul juste donne **54 ans**. L'écart est d'un facteur exactement 10⁶ : le
corrigé a divisé le nombre d'**actions** par le nombre de secondes dans une
année, **sans appliquer les 10⁻⁶ seconde par action**.

Vérifiez vous-même :

```
54 millions d'années × 3,15 × 10⁷ s = 1,7 × 10¹⁵ secondes
```

C'est le nombre d'**actions**, pas de secondes.

Je ne le relève pas pour chicaner. Je le relève parce que c'est **exactement le
type d'erreur qui vous a coûté des points en janvier**, et parce qu'il montre
que le réflexe de la séance 2 protège tout le monde :

> **Convertissez toujours en unité humaine, et demandez-vous si le nombre est
> plausible.**

Ici, la question portait sur le « caractère raisonnable » d'un algorithme. Que
la réponse soit 54 ans ou 54 millions d'années, la conclusion est la même :
impraticable. **Mais on ne se fie pas à une conclusion juste obtenue par un
calcul faux.**

## Compter dans les autres formes de code

### Boucles imbriquées dépendantes

```
pour i ← 1 à n faire
    pour j ← i à n faire
        opération
```

La boucle interne fait `n − i + 1` tours. Total :

```
n + (n−1) + (n−2) + … + 1 = n(n+1)/2
```

Soit **O(n²)** — la moitié d'un carré reste un carré.

**Retenez la somme `1 + 2 + … + n = n(n+1)/2`.** Elle sort à chaque fois.

### Une récurrence

Le sujet de mai 2025 posait :

```
f(n) = f(n−1) + 2n − 1,   f(0) = 0
```

Pour trouver la forme close, on **déroule** :

```
f(1) = 0 + 1 = 1
f(2) = 1 + 3 = 4
f(3) = 4 + 5 = 9
f(4) = 9 + 7 = 16
```

1, 4, 9, 16 : **`f(n) = n²`**. C'est la somme des `n` premiers nombres impairs.

**La méthode est toujours celle-là : calculer les quatre ou cinq premières
valeurs, reconnaître la suite, puis vérifier par récurrence.**

Et pour compter les **multiplications** de cette fonction récursive : une par
appel, `n` appels → **O(n)**, linéaire.

### Deux appels récursifs

```
Fonction f(n)
    si n ≤ 1 alors retourner 1
    retourner f(n−1) + f(n−2)
```

Chaque appel en engendre deux : **O(2ⁿ)**, exponentiel. C'est Fibonacci naïf.

Mais attention à la forme voisine :

```
Fonction g(n)
    si n ≤ 1 alors retourner 1
    retourner g(n/2) + g(n/2) + travail en O(n)
```

Ici on **divise** : `log n` niveaux, `O(n)` de travail par niveau,
**O(n log n)**. C'est le tri fusion.

**Deux appels sur `n−1` : exponentiel. Deux appels sur `n/2` : `n log n`.** La
différence est la division.

## La grille de comptage

À appliquer telle quelle :

1. **Recopier la phrase de l'énoncé** qui définit l'action élémentaire.
2. **Compter de l'intérieur vers l'extérieur**, en multipliant.
3. **Écrire `f(n, m)` avec ses facteurs étiquetés.**
4. **Simplifier en O** — jeter les constantes, garder le dominant.
5. Si on demande un temps : **multiplier par la durée**, puis **convertir en
   unité humaine**.
6. **Conclure par une phrase d'interprétation.**
MD,
                'recap' => <<<'MD'
- **L'énoncé définit l'action élémentaire.** Recopier sa phrase.
- **Compter de l'intérieur vers l'extérieur, en multipliant.**
- Il y a **`2ⁿ` sous-ensembles** de `n` éléments : deux choix par élément.
- L'exercice de janvier : `f(n,m) = 2ⁿ × m × 2 = 2m·2ⁿ`, soit **O(m·2ⁿ)**.
  **Écrire la décomposition avec ses trois étiquettes.**
- Temps pour n=40 : `1,7×10¹⁵ × 10⁻⁶ = 1,7×10⁹ s`, soit **≈ 54 ans**.
  *(Le corrigé officiel annonce 54 millions d'années : il a oublié les 10⁻⁶.)*
- **Toujours conclure par une phrase d'interprétation** : « chaque sommet ajouté
  double le temps ».
- `1 + 2 + … + n = n(n+1)/2` → **O(n²)**.
- Une récurrence se résout en **déroulant les cinq premières valeurs**.
- **Deux appels sur `n−1` → exponentiel. Deux appels sur `n/2` → `n log n`.**
MD,
            ],

            /* ================= Séance 4 ================= */
            [
                'title' => 'Les notations O, Ω et Θ',
                'chapitre' => 'C7',
                'duree_min' => 30,
                'prerequis' => "Les séances 2 et 3.",
                'intro' => <<<'MD'
Trois lettres grecques, trois sens différents, et le sujet exige qu'on emploie la
bonne.

Vous avez écrit `Θ(log n)` en janvier. Même si la fonction avait été la bonne, le
`Θ` engage plus que le `O` — il affirme une borne **exacte**, pas seulement un
majorant.

Séance courte, formelle, et entièrement mécanique une fois les définitions
posées.
MD,
                'body' => <<<'MD'
## Les trois définitions

Elles se lisent toutes de la même façon : « à partir d'un certain rang, à une
constante près ».

### Grand O — une borne supérieure

> `f(n) = O(g(n))` s'il existe des constantes `c > 0` et `n₀` telles que, pour
> tout `n ≥ n₀` :
> **`f(n) ≤ c · g(n)`**

« `f` ne croît **pas plus vite que** `g`. » C'est un **majorant**.

### Grand Oméga — une borne inférieure

> `f(n) = Ω(g(n))` s'il existe `c > 0` et `n₀` tels que, pour tout `n ≥ n₀` :
> **`f(n) ≥ c · g(n)`**

« `f` croît **au moins aussi vite que** `g`. » C'est un **minorant**.

### Grand Thêta — les deux

> `f(n) = Θ(g(n))` si **`f(n) = O(g(n))` et `f(n) = Ω(g(n))`**.

« `f` croît **exactement comme** `g`, à une constante près. »

## Le tableau à retenir

| Notation | Sens | Analogie |
|---|---|---|
| `f = O(g)` | `f` ne dépasse pas `g` | `f ≤ g` |
| `f = Ω(g)` | `f` vaut au moins `g` | `f ≥ g` |
| `f = Θ(g)` | `f` et `g` sont du même ordre | `f = g` |

## Lequel employer

**En cas de doute, écrivez `O`.** C'est la notation la moins engageante et la
plus attendue : quand un énoncé demande « la complexité », il demande presque
toujours un `O`.

Écrivez `Θ` seulement si vous pouvez **justifier les deux bornes**. Par exemple :

> « Le tri fusion fait toujours `Θ(n log n)` comparaisons : la récursion a
> toujours `log n` niveaux, et chaque niveau coûte exactement `Θ(n)`. »

Ici le `Θ` est légitime parce que le coût ne dépend pas des données.

En revanche :

> « La recherche linéaire est en `O(n)` » — et non `Θ(n)`, parce que dans le
> meilleur cas elle trouve à la première case, en `O(1)`.

## Les règles de simplification

Quatre règles, et elles suffisent à tout.

### 1. Les constantes multiplicatives disparaissent

```
5n² = O(n²)        2m·2ⁿ = O(m·2ⁿ)        n/2 = O(n)
```

### 2. Seul le terme dominant compte

```
n² + n + 1 = O(n²)         3n³ + 100n² = O(n³)
```

Pourquoi ? Parce que pour `n` grand, `n²` écrase `n`. À `n = 1000` : `n² = 10⁶`
contre `n = 10³`. Le second est mille fois plus petit.

### 3. La base du logarithme disparaît

```
log₂ n = O(log₁₀ n)
```

Elles ne diffèrent que d'une constante multiplicative.

**Mais attention : la base d'une exponentielle, elle, compte.**
`2ⁿ` et `3ⁿ` ne sont **pas** du même ordre : `3ⁿ / 2ⁿ = 1,5ⁿ`, qui tend vers
l'infini.

### 4. Dans un produit, on multiplie ; dans une somme, on garde le max

```
O(n) × O(log n) = O(n log n)
O(n) + O(log n) = O(n)
```

## L'échelle, à connaître par cœur

```
O(1) < O(log n) < O(√n) < O(n) < O(n log n) < O(n²) < O(n³) < O(2ⁿ) < O(n!)
```

**Les trois premières sont « rapides », les trois suivantes « raisonnables », les
deux dernières « impraticables ».**

## Le vocabulaire

Une question de cours fréquente, et un mot mal employé se voit.

| Complexité | Adjectif |
|---|---|
| `O(1)` | **constante** |
| `O(log n)` | **logarithmique** |
| `O(n)` | **linéaire** |
| `O(n log n)` | quasi-linéaire |
| `O(n²)` | **quadratique** |
| `O(n³)` | **cubique** |
| `O(nᵏ)` | **polynomiale** |
| `O(2ⁿ)`, `O(kⁿ)` | **exponentielle** |
| `O(n!)` | factorielle |

**« Polynomiale » regroupe tout ce qui est en `nᵏ`** pour un `k` fixe — c'est la
définition de la classe P, séance 9. Et **`2ⁿ` n'est pas polynomial**, parce que
`n` est à l'exposant, pas à la base.

C'est exactement la distinction qui sépare P du reste, et elle repose sur cette
seule différence de place.

## Pire cas, meilleur cas, cas moyen

Trois analyses différentes du même algorithme. Le sujet peut les distinguer.

| | Ce qu'on mesure |
|---|---|
| **pire cas** | le plus grand coût sur toutes les entrées de taille `n` |
| **meilleur cas** | le plus petit |
| **cas moyen** | l'espérance sur une distribution des entrées |

**Sauf mention contraire, « la complexité » désigne le pire cas.** C'est la
convention, et elle est prudente : elle donne une garantie.

Exemple, la recherche linéaire dans un tableau de `n` éléments :

- meilleur cas : `O(1)` — l'élément est en première position ;
- pire cas : `O(n)` — il est en dernier, ou absent ;
- cas moyen : `O(n)` — environ `n/2` comparaisons, ce qui est encore `O(n)`.

Un quatrième type d'analyse existe, l'**analyse amortie**, qui mesure le coût
moyen d'une opération dans une **suite** d'opérations. C'est la partie II de
janvier, et le sujet de la séance 11.
MD,
                'recap' => <<<'MD'
- **`O` = majorant** (`f ≤ c·g`) · **`Ω` = minorant** (`f ≥ c·g`) · **`Θ` = les
  deux**.
- **En cas de doute, écrire `O`.** Le `Θ` engage à justifier les deux bornes.
- Simplification : **constantes multiplicatives jetées**, **terme dominant
  gardé**, **base du log sans importance**.
- **Mais la base d'une exponentielle compte** : `2ⁿ ≠ Θ(3ⁿ)`.
- Produit → on multiplie · somme → on garde le maximum.
- L'échelle : `1 < log n < √n < n < n log n < n² < n³ < 2ⁿ < n!`
- Vocabulaire : constante, logarithmique, linéaire, quadratique, cubique,
  **polynomiale** (`nᵏ`), **exponentielle** (`kⁿ`).
- **`2ⁿ` n'est pas polynomial** : `n` est à l'exposant. C'est ce qui sépare P du
  reste.
- **Sans précision, « la complexité » désigne le pire cas.**
MD,
            ],

            /* ================= Séance 5 ================= */
            [
                'title' => 'Problème, algorithme, calculabilité',
                'chapitre' => 'C2',
                'duree_min' => 30,
                'prerequis' => "Les séances 2 à 4. On change de sujet : on ne mesure plus, on définit.",
                'intro' => <<<'MD'
Changement de registre. Les quatre premières séances portaient sur la mesure ;
les suivantes portent sur la **nature** des problèmes.

La question devient : non plus « combien de temps ? », mais **« est-ce seulement
possible ? »**.

Pour y répondre, il faut d'abord définir proprement ce qu'est un problème, ce
qu'est un algorithme, et pourquoi on a besoin d'un modèle de calcul aussi
austère que la machine de Turing. C'est la séance d'aujourd'hui.
MD,
                'body' => <<<'MD'
## Problème, instance, solution

Trois mots à ne pas confondre, et le correcteur y est attentif.

| Terme | Sens |
|---|---|
| **problème** | la question générale — « un graphe a-t-il un stable de taille `k` ? » |
| **instance** | un cas particulier — le graphe de Graphopolis avec `k = 3` |
| **solution** | la réponse pour cette instance — oui, `{A, C, E}` |

## Les trois familles de problèmes

| Famille | La question posée | Réponse |
|---|---|---|
| **décision** | existe-t-il un stable de taille ≥ `k` ? | **oui / non** |
| **recherche** | donnez-moi un stable de taille ≥ `k` | un objet |
| **optimisation** | quel est le plus grand stable ? | un objet optimal |

**Toute la théorie de la complexité est bâtie sur les problèmes de décision**, et
c'est une question de cours possible : pourquoi ?

> Parce qu'une réponse par oui ou non est le cas le plus simple à formaliser :
> une machine de Turing accepte ou rejette, et rien de plus. Et parce qu'on ne
> perd rien : un problème d'optimisation se ramène à une série de problèmes de
> décision, en faisant varier le seuil `k`.

C'est pour ça que l'énoncé de Graphopolis parle d'un stable **de taille ≥ k**, et
non du plus grand stable.

## Encoder une entrée

Une machine de Turing ne connaît que des **mots** sur un alphabet fini. Il faut
donc transformer le graphe en une chaîne de symboles.

Pour Graphopolis : on numérote les sommets, on écrit la liste des arêtes, on
sépare par des symboles réservés. Par exemple :

```
5 # 1,2 ; 2,3 ; 1,4 ; 2,5 # 3
```

soit `n = 5`, quatre arêtes, `k = 3`.

**La taille de l'entrée est la longueur de ce mot**, pas le nombre de sommets. En
pratique, elle est polynomiale en `n + m`, ce qui autorise à raisonner
directement en `n` et `m` — mais il faut savoir le dire.

Un point de vocabulaire qui tombe régulièrement :

> Un **langage** est un ensemble de mots. À tout problème de décision correspond
> le langage des instances dont la réponse est OUI. « Décider le problème » et
> « décider le langage » sont la même chose.

## Ce qu'est un algorithme

Intuitivement : une suite finie d'instructions non ambiguës qui, sur toute
entrée, produit une réponse en un nombre fini d'étapes.

Cette définition est correcte mais **inutilisable pour une démonstration**. Pour
prouver qu'**aucun** algorithme ne résout un problème donné, il faut une
définition mathématique de ce qu'est un algorithme.

C'est le rôle de la **machine de Turing**.

## Pourquoi ce modèle, et pas Java

C'est la question qu'on se pose légitimement, et elle a une réponse en deux
parties.

**Parce qu'il est simple.** Une machine de Turing n'a qu'un ruban, une tête, un
ensemble fini d'états. On peut raisonner dessus, énumérer toutes les machines,
démontrer des impossibilités. Sur Java, non.

**Parce qu'il est aussi puissant que n'importe quoi d'autre.** C'est la **thèse
de Church-Turing** :

> **Toute fonction calculable par un procédé effectif quelconque est calculable
> par une machine de Turing.**

Deux remarques que le cours souligne :

- C'est une **thèse**, pas un théorème. On ne peut pas la démontrer, parce que
  « procédé effectif » n'est pas une notion mathématique. Mais elle n'a jamais
  été prise en défaut depuis 1936.
- Elle a une conséquence pratique immédiate : **tous les modèles de calcul connus
  sont équivalents.** Machine de Turing, lambda-calcul, fonctions récursives,
  Java, Python — ils calculent exactement les mêmes fonctions.

C'est ce qui autorise, à l'examen, la phrase suivante :

> « Ce problème est décidable, car on peut en écrire un algorithme ; par la thèse
> de Church-Turing, il existe donc une machine de Turing qui le décide. »

**Cette phrase est une réponse valable**, à condition de décrire ensuite
l'algorithme et de justifier qu'il **s'arrête toujours**.

## Calculable, décidable, énumérable

Trois mots proches, trois sens distincts.

| Terme | Définition |
|---|---|
| **fonction calculable** | il existe une MT qui, sur toute entrée, s'arrête et écrit `f(x)` |
| **langage décidable** | il existe une MT qui, sur **toute** entrée, s'arrête et répond OUI ou NON |
| **langage semi-décidable** *(ou récursivement énumérable)* | il existe une MT qui **s'arrête et accepte** si `w ∈ L` — mais qui **peut boucler** si `w ∉ L` |

**La distinction décidable / semi-décidable est le cœur du chapitre 5.** Retenez
la formulation : *décidable = s'arrête toujours ; semi-décidable = s'arrête sur
les OUI seulement.*

Et la relation :

```
décidable  ⊂  semi-décidable  ⊂  tous les langages
```

Les deux inclusions sont **strictes**. Le problème de l'arrêt est
semi-décidable mais pas décidable ; on le verra séance 8.

## La question 1 de janvier

*« Expliquer textuellement s'il peut exister une machine de Turing qui décide des
ensembles stables, étant donné un graphe `G = (V, E)`. »*

Le correcteur a mis « **?** » : la réponse rendue paraphrasait l'énoncé.

Voici ce qu'il fallait écrire — et remarquez que **la question porte sur
l'existence, pas sur l'efficacité** :

> **Oui.** Un problème est décidable s'il existe une machine de Turing qui, sur
> toute entrée, s'arrête et répond OUI ou NON. La question n'est donc pas « est-ce
> rapide ? » mais « est-ce possible ? ».
>
> La machine s'arrête toujours, pour quatre raisons :
>
> 1. L'entrée `(G, k)` est **finie** : `n` sommets, `m` arêtes, un entier `k`.
> 2. Il y a exactement **`2ⁿ` sous-ensembles** de `V`, donc un nombre **fini**.
>    La machine peut tous les énumérer, un par un, sans risque de boucler.
> 3. Pour chaque sous-ensemble `S`, vérifier qu'il est stable demande au plus
>    **`m` comparaisons** — on teste chaque arête. C'est fini.
> 4. Après avoir examiné les `2ⁿ` sous-ensembles, la machine a une réponse
>    définitive : OUI si un stable de taille ≥ `k` a été trouvé, NON sinon.
>
> Le problème est donc **décidable**, même si l'algorithme est exponentiel.

**Le mot « fini » revient quatre fois, et c'est voulu.** Décider, c'est
s'arrêter ; s'arrêter, c'est ne parcourir qu'un nombre fini de possibilités,
chacune en un temps fini.

## Le gabarit d'une réponse « ce problème est-il décidable ? »

Trois temps, toujours :

1. **Citer la définition.** « Un problème est décidable s'il existe une MT qui
   s'arrête sur toute entrée et répond OUI ou NON. »
2. **Décrire l'algorithme**, même s'il est stupide et exponentiel.
3. **Justifier qu'il s'arrête** : l'espace de recherche est fini, chaque
   vérification est finie.

Et la phrase de conclusion, qui montre qu'on n'a pas confondu les deux notions :

> « Le problème est décidable ; sa complexité, en revanche, est exponentielle. La
> décidabilité ne dit rien de l'efficacité. »
MD,
                'recap' => <<<'MD'
- **Problème** (la question générale) · **instance** (un cas) · **solution**.
- Trois familles : **décision** (oui/non), recherche, optimisation. **La théorie
  se bâtit sur la décision**, parce qu'une MT accepte ou rejette — et un problème
  d'optimisation s'y ramène en faisant varier le seuil.
- Un **langage** est un ensemble de mots ; décider un problème = décider le
  langage de ses instances positives.
- **Thèse de Church-Turing** : tout procédé effectif est simulable par une MT.
  C'est une **thèse**, pas un théorème. Elle rend tous les modèles équivalents.
- **Décidable = s'arrête toujours. Semi-décidable = s'arrête sur les OUI
  seulement.** Inclusions strictes.
- Réponse à « ce problème est-il décidable ? » en trois temps : **citer la
  définition · décrire l'algorithme · justifier qu'il s'arrête**.
- Le mot clef est **fini** : espace de recherche fini, vérification finie.
- Conclure par : **« décidable ; la complexité, elle, est exponentielle »**.
MD,
            ],

            /* ================= Séance 6 ================= */
            [
                'title' => 'La machine de Turing',
                'chapitre' => 'C3',
                'duree_min' => 35,
                'prerequis' => "La séance 5. On formalise le modèle.",
                'intro' => <<<'MD'
La machine de Turing tombe à **chaque session**, sans exception depuis qu'on
dispose des archives.

Elle a l'air compliquée parce qu'on la présente d'ordinaire par son
septuplet formel. On va faire l'inverse : d'abord l'image, ensuite le formalisme
— et on verra qu'il n'y a rien dedans qu'on n'ait déjà compris.
MD,
                'body' => <<<'MD'
## L'image

Imaginez :

- un **ruban** infini, découpé en cases, chacune contenant un symbole ;
- une **tête de lecture-écriture** posée sur une case ;
- une **mémoire interne** qui ne peut retenir qu'un état, choisi dans une liste
  finie.

À chaque étape, la machine **lit** le symbole sous la tête, et en fonction de ce
symbole **et** de son état, elle fait trois choses :

1. **écrire** un symbole dans la case ;
2. **déplacer** la tête d'une case à gauche ou à droite ;
3. **changer** d'état.

Et c'est tout. Toute l'informatique tient là-dedans.

## Le septuplet

```
M = (Q, Σ, Γ, δ, q₀, q_accept, q_reject)
```

| Symbole | Nom | Ce que c'est |
|---|---|---|
| `Q` | les **états** | un ensemble **fini** |
| `Σ` | l'alphabet **d'entrée** | les symboles autorisés dans le mot de départ |
| `Γ` | l'alphabet **de ruban** | `Σ` plus le **blanc `B`**, et d'éventuels symboles de travail |
| `δ` | la fonction de **transition** | le programme |
| `q₀` | l'état **initial** | |
| `q_accept` | l'état **acceptant** | |
| `q_reject` | l'état **rejetant** | |

Deux points que le correcteur vérifie :

- **`Q` est fini.** C'est ce qui distingue l'état interne de la mémoire du ruban,
  qui est infinie. Toute la puissance vient du ruban, pas des états.
- **`B ∈ Γ` mais `B ∉ Σ`.** Le blanc remplit le ruban là où rien n'est écrit ; il
  ne peut pas faire partie du mot d'entrée, sinon on ne saurait pas où le mot
  s'arrête.

## La fonction de transition

```
δ : Q × Γ  →  Q × Γ × {G, D}
```

Lisez-la ainsi : **(état courant, symbole lu) → (nouvel état, symbole écrit,
déplacement)**.

On l'écrit ligne par ligne :

```
δ(q₀, a) = (q₁, b, D)
```

> « Dans l'état `q₀`, si je lis un `a` : j'écris un `b`, je vais à droite, et je
> passe dans l'état `q₁`. »

**Une transition fait toujours les trois choses.** On écrit toujours un symbole
— quitte à réécrire celui qu'on vient de lire — et on se déplace toujours.

### La présenter sur une copie

Deux formes, toutes deux acceptées :

**En table**, quand il y a peu d'états :

| δ | `a` | `b` | `B` |
|---|---|---|---|
| `q₀` | (q₀, a, D) | (q₀, b, D) | (q₁, B, G) |
| `q₁` | (q_accept, a, G) | (q_reject, b, G) | — |

**En liste**, quand il y en a beaucoup, avec un commentaire par ligne. C'est ce
que fait le corrigé de janvier, et c'est plus lisible pour une machine complexe.

## Configuration et exécution

Une **configuration** décrit l'état complet de la machine à un instant :

> le contenu du ruban, la position de la tête, et l'état courant.

On la note en insérant l'état juste avant la case lue :

```
a b q₃ a b B B …
```

signifie : le ruban contient `abab`, la tête est sur le troisième symbole, la
machine est dans l'état `q₃`.

L'exécution est la suite des configurations. Elle s'arrête quand on atteint
`q_accept` ou `q_reject` — **et elle peut ne jamais s'arrêter.** C'est cette
possibilité qui rend le problème de l'arrêt intéressant.

## Décider, accepter, reconnaître

Le vocabulaire est piégeux, et il tombe.

| Terme | Sens |
|---|---|
| `M` **accepte** `w` | l'exécution sur `w` atteint `q_accept` |
| `M` **reconnaît** `L` | `M` accepte exactement les mots de `L` — mais peut boucler sur les autres |
| `M` **décide** `L` | `M` reconnaît `L` **et s'arrête sur toute entrée** |

**« Décider » = « reconnaître » + « s'arrête toujours ».** C'est la seule chose à
retenir, et c'est la différence entre décidable et semi-décidable de la séance 5.

## Un exemple complet : décaler un mot vers la droite

C'est l'exercice de mai 2025. Il faut insérer un blanc au début de
`w = w₁ … wₙ`, donc décaler tout le mot d'une case.

**L'idée**, et c'est elle qui vaut les points : on ne peut pas décaler tout le
mot d'un coup, puisqu'on ne voit qu'une case à la fois. On **transporte** donc le
symbole qu'on vient d'effacer **dans l'état**.

Deux états de transport suffisent : `q_a` signifie « je porte un `a` », `q_b`
signifie « je porte un `b` ».

```
Σ = {a, b}          Γ = {a, b, B}          état initial q₀

δ(q₀, a) = (q_a, B, D)      % on écrit B au début, on emporte le a
δ(q₀, b) = (q_b, B, D)

δ(q_a, a) = (q_a, a, D)     % on dépose le a porté, on ramasse le a lu
δ(q_a, b) = (q_b, a, D)     % on dépose le a, on emporte le b
δ(q_b, a) = (q_a, b, D)
δ(q_b, b) = (q_b, b, D)

δ(q_a, B) = (q_f, a, D)     % fin du mot : on dépose le dernier symbole
δ(q_b, B) = (q_f, b, D)
```

**Le mécanisme central : l'état sert de mémoire d'un symbole.** C'est le procédé
le plus utile de tout le chapitre, et il revient dès qu'il faut « se souvenir »
de quelque chose de borné.

### Déroulons sur `ab`

| Étape | Ruban | Tête | État |
|---|---|---|---|
| 0 | `a b B B` | position 1 | `q₀` |
| 1 | `B b B B` | position 2 | `q_a` — je porte `a` |
| 2 | `B a B B` | position 3 | `q_b` — je porte `b` |
| 3 | `B a b B` | position 4 | `q_f` |

Résultat : `B a b`. Le mot a été décalé d'une case. ✓

**Déroulez toujours votre machine sur un petit exemple**, en tableau. C'est ce
qui montre qu'elle fonctionne, et ça rattrape une transition oubliée.

## Le nombre d'actions élémentaires

Pour une machine de Turing, l'action élémentaire est **une transition**. Compter
le coût, c'est compter les transitions.

Pour le décalage ci-dessus : une transition par symbole, plus une pour la fin.
**`n + 1` transitions, soit O(n).**

C'est ce type de comptage que la question 2 de janvier demandait — on le fait à
la séance suivante, sur un problème plus riche.

## Le gabarit d'une réponse « décrire une machine de Turing »

Six points. **Écrivez-les comme une liste à puces** : c'est ce que le corrigé
fait, et ça se corrige vite.

1. **L'entrée et son encodage.** « L'entrée est `(G, k)` encodé sous la forme… »
2. **Les alphabets** `Σ` et `Γ`.
3. **L'idée générale, en français**, en deux ou trois phrases. *C'est le point le
   plus important : sans lui, les transitions ne se comprennent pas.*
4. **Les états**, avec **le rôle de chacun**, dans un tableau.
5. **Les transitions**, ou les phases si la machine est complexe.
6. **Un déroulement sur un petit exemple**, et **le nombre d'actions
   élémentaires**.

Le point 3 est celui que la copie de janvier n'avait pas. Une machine de Turing
décrite sans son idée directrice est illisible — et le correcteur ne peut rien
créditer.
MD,
                'recap' => <<<'MD'
- Un **ruban** infini, une **tête**, un ensemble **fini** d'états. À chaque
  étape : **écrire, se déplacer, changer d'état**.
- `M = (Q, Σ, Γ, δ, q₀, q_accept, q_reject)`. **`B ∈ Γ` mais `B ∉ Σ`.**
- `δ : Q × Γ → Q × Γ × {G, D}` — **(état, symbole lu) → (état, symbole écrit,
  déplacement)**.
- **Décider = reconnaître + s'arrêter toujours.**
- Le procédé clef : **l'état sert de mémoire d'un symbole** (transport lors d'un
  décalage).
- **Toujours dérouler la machine sur un petit exemple, en tableau.**
- L'action élémentaire d'une MT est **une transition**.
- Gabarit en six points : encodage · alphabets · **idée générale en français** ·
  états et leurs rôles · transitions · exemple et coût.
- **C'est l'idée générale qui manquait en janvier.** Sans elle, rien n'est
  créditable.
MD,
            ],

        ];
    }
}