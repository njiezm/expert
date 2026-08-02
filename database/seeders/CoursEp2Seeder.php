<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours d'EP, seconde partie : construire, classer, réduire, amortir.
 *
 * Les six séances suivent l'ordre du sujet de janvier : la machine de Turing de
 * l'exercice 1, les classes P et NP de l'exercice 3, les réductions de
 * l'exercice 4, l'analyse amortie de la partie II.
 *
 * Deux pertes de janvier sont traitées frontalement. L'exercice 3 question 3 —
 * « montrer que l'Ensemble Stable appartient à NP » — était vide, alors qu'il
 * tient en six lignes : un certificat, un vérificateur, son coût. Et la
 * question 2 contenait une définition circulaire de NP, annotée « ? ».
 *
 * Note de vérification : le corrigé officiel annonce « 54 millions d'années »
 * pour n = 40 là où le calcul donne 54 ans — le facteur 10⁻⁶ par action n'a pas
 * été appliqué. Et sa formule du coût amorti, indexée sur ⌊log₃ N⌋, se trompe
 * pour N = 9 et N = 27 ; la condition juste est « redimensionnement à
 * l'insertion 3^i + 1 ». Les deux ont été vérifiés par simulation.
 */
class CoursEp2Seeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'EP')->first();

        if (! $subject) {
            return;
        }

        // Les six premières séances sont posées par CoursEpSeeder.
        $depart = 6;

        foreach ($this->seances() as $i => $seance) {
            $chapter = isset($seance['chapitre'])
                ? Chapter::where('subject_id', $subject->id)->where('code', $seance['chapitre'])->first()
                : null;

            unset($seance['chapitre']);

            Seance::updateOrCreate(
                ['subject_id' => $subject->id, 'slug' => Str::slug($seance['title'])],
                $seance + ['chapter_id' => $chapter?->id, 'position' => $depart + $i + 1]
            );
        }
    }

    /* ==================================================================== */

    private function seances(): array
    {
        return [

            /* ================= Séance 7 ================= */
            [
                'title' => 'Construire une machine de Turing',
                'chapitre' => 'C3',
                'duree_min' => 35,
                'prerequis' => "La séance 6, et son gabarit en six points. On traite l'exercice 1 question 2 de janvier.",
                'intro' => <<<'MD'
On passe de la théorie à la construction. L'exercice 1 question 2 de janvier
demandait :

> *Décrire et expliciter une machine de Turing `M` qui décide le problème de
> l'ensemble stable. Quels états et transitions utilise-t-elle ? Estimer son
> nombre d'actions élémentaires en fonction de `n = |V|` et `m = |E|`.*

Trois demandes en une : décrire, donner les états, estimer le coût. C'est
exactement le gabarit de la séance 6.

On va voir qu'on n'écrit jamais toutes les transitions d'une machine réaliste —
on décrit des **phases**, et c'est ce qui est attendu.
MD,
                'body' => <<<'MD'
## Le principe : décrire à haut niveau

Une machine de Turing qui décide l'ensemble stable aurait des centaines de
transitions. **Personne ne les écrit.** Ce qu'on attend, c'est une description
**par phases**, chacune expliquée en français, avec les états qui les portent.

Le corrigé le dit lui-même : « description à haut niveau (une solution parmi
d'autres) ». Il y a donc plusieurs réponses correctes, et la vôtre est jugée sur
sa **clarté** et sa **cohérence**, pas sur sa conformité.

## L'idée générale

C'est le point 3 du gabarit, et le plus important :

> La machine énumère **tous les sous-ensembles** de `V`, l'un après l'autre. Pour
> chacun, elle vérifie s'il est **stable**, puis si sa **taille** atteint `k`.
> Elle accepte au premier qui convient, et rejette si elle a épuisé les `2ⁿ`
> sous-ensembles sans succès.

Trois phases, donc. Écrivez cette phrase avant toute autre chose.

## La machine à plusieurs rubans

Une astuce de présentation qui simplifie beaucoup : on s'autorise **deux
rubans**.

- **Ruban 1** — l'entrée : le graphe `G` et l'entier `k`. En lecture seule.
- **Ruban 2** — le sous-ensemble courant, codé sur `n` bits. Le bit `i` vaut 1
  si le sommet `vᵢ` est dans `S`, 0 sinon.

**Vous avez le droit de le faire**, et il faut le justifier en une phrase — c'est
le chapitre 4 du cours, « variations sur la machine de Turing » :

> Une machine à plusieurs rubans peut être simulée par une machine à un seul
> ruban, avec un ralentissement au plus **quadratique**. Les deux modèles
> décident donc exactement les mêmes langages, et l'usage de plusieurs rubans ne
> change ni la décidabilité ni l'appartenance à P.

**Cette phrase vaut des points**, parce qu'elle montre qu'on sait pourquoi on a
le droit de simplifier.

Les autres variations du chapitre 4, à connaître de nom, obéissent au même
principe :

| Variation | Équivalente ? | Coût de la simulation |
|---|---|---|
| plusieurs rubans | oui | ralentissement quadratique |
| ruban infini des deux côtés | oui | facteur constant |
| alphabet plus riche | oui | facteur constant |
| **machine non déterministe** | **oui pour la décidabilité** | **ralentissement exponentiel** |

**La dernière ligne est capitale**, et c'est elle qui fonde la question `P = NP`.
Une machine non déterministe ne décide pas plus de langages, mais on ne sait pas
la simuler efficacement.

## Le codage du sous-ensemble

C'est l'astuce qui rend la machine simple.

Un sous-ensemble de `V` = un mot de `n` bits. Énumérer tous les sous-ensembles =
**compter en binaire de `0…0` à `1…1`**.

```
n = 5
00000  →  ∅
00001  →  {E}
10100  →  {A, C}
10101  →  {A, C, E}
11111  →  V
```

**Incrémenter un compteur binaire est une opération élémentaire pour une machine
de Turing**, et elle couvre exactement les `2ⁿ` sous-ensembles. C'est ce que la
question attend.

## Les trois phases

### Phase 1 — génération

On incrémente le compteur binaire du ruban 2. On part de `0ⁿ`, on va jusqu'à
`1ⁿ`. Quand le compteur déborde, tous les sous-ensembles ont été essayés.

### Phase 2 — vérification de stabilité

Pour le sous-ensemble courant, on parcourt **toutes les arêtes** du ruban 1. Pour
chaque arête `(u, v)`, deux tests : le bit `u` du ruban 2 vaut-il 1 ? Le bit `v`
vaut-il 1 ?

> **Si les deux répondent oui, `S` n'est pas stable** — les deux extrémités de
> l'arête y sont. On abandonne et on retourne en phase 1.
>
> Si l'on a parcouru toutes les arêtes sans conflit, `S` est stable.

### Phase 3 — vérification de la taille

On compte les bits à 1 du ruban 2 : c'est `|S|`. Si `|S| ≥ k`, on entre dans
`q_accept`. Sinon, on retourne en phase 1.

Si le compteur a atteint `1ⁿ` sans acceptation, on entre dans `q_reject`.

## Les états

**Un tableau, un état par ligne, avec son rôle.** C'est ce que la question
demande explicitement.

| État | Rôle |
|---|---|
| `q_init` | initialise le compteur binaire à `0ⁿ` sur le ruban 2 |
| `q_enum` | lit le sous-ensemble courant et lance la vérification |
| `q_check` | parcourt les arêtes ; pour chaque `(u,v)`, lit les bits `u` et `v` |
| `q_size` | compte les bits à 1 et compare à `k` |
| `q_next` | incrémente le compteur binaire d'une unité |
| `q_accept` | état final acceptant — un stable de taille ≥ `k` a été trouvé |
| `q_reject` | état final rejetant — aucun stable valide |

Sept états. **On n'écrit pas les transitions une par une** : on décrit ce que
chaque état fait, et comment on passe de l'un à l'autre.

## Le déroulement sur Graphopolis

Rappel du graphe : `V = {A, B, C, D, E}`, arêtes `(A,B) (B,C) (A,D) (B,E)`,
`k = 3`.

Arrivons au compteur `10101`, c'est-à-dire `S = {A, C, E}` :

| Arête | `u ∈ S` ? | `v ∈ S` ? | Verdict |
|---|---|---|---|
| (A, B) | A ∈ S | **B ∉ S** | ok |
| (B, C) | **B ∉ S** | C ∈ S | ok |
| (A, D) | A ∈ S | **D ∉ S** | ok |
| (B, E) | **B ∉ S** | E ∈ S | ok |

Aucune arête n'a ses deux extrémités dans `S` : **`S` est stable**.

Phase 3 : `|S| = 3 ≥ 3 = k`. **Acceptation.**

**Déroulez toujours sur l'exemple de l'énoncé.** Ici, il vous était donné —
l'énoncé annonçait « des ensembles stables possibles incluent `{A,C,E}` ». C'était
un cadeau : la vérification était offerte.

## Le nombre d'actions élémentaires

La troisième demande de la question. On décompose, comme à la séance 3 :

```
      2ⁿ         ×      m       ×      2         =  2m·2ⁿ
sous-ensembles       arêtes        tests/arête
```

> **O(m · 2ⁿ) actions élémentaires.**

Remarquez que c'est **exactement le coût de l'algorithme de l'exercice 2**. Ce
n'est pas un hasard : la machine de Turing et l'algorithme font la même chose. Le
dire est un bon point de conclusion :

> « La machine réalise le même parcours que l'algorithme de l'exercice 2 : son
> coût est donc identique, `O(m·2ⁿ)`. La décidabilité est acquise, mais le coût
> est exponentiel. »

## Le gabarit, appliqué

Pour mémoire, les six points de la séance 6, avec ce qu'ils donnent ici :

| Point | Ici |
|---|---|
| 1. entrée et encodage | `(G, k)` : `n`, la liste des arêtes, l'entier `k` |
| 2. alphabets | `Σ` = symboles d'encodage du graphe · `Γ = Σ ∪ {0, 1, B}` |
| 3. **idée générale** | énumérer les `2ⁿ` sous-ensembles, tester la stabilité, tester la taille |
| 4. états | le tableau des sept états |
| 5. transitions | décrites **par phases**, pas une par une |
| 6. exemple et coût | `{A,C,E}` déroulé · `O(m·2ⁿ)` |

**Même incomplète, une réponse suivant ce gabarit rapporte.** Le point 3 seul —
trois phrases de français — vaut déjà une part des points, parce qu'il prouve
qu'on a compris le problème.
MD,
                'recap' => <<<'MD'
- On ne détaille **jamais** toutes les transitions : on décrit des **phases**.
- **Deux rubans** : l'entrée en lecture, le sous-ensemble courant en binaire. Le
  justifier — « une MT multi-rubans se simule sur un ruban avec un ralentissement
  quadratique ».
- **Un sous-ensemble = un mot de `n` bits.** Les énumérer = **compter en
  binaire** de `0ⁿ` à `1ⁿ`.
- Trois phases : **génération · stabilité · taille**.
- Un **tableau des états avec leur rôle** — la question le demande.
- **Dérouler sur l'exemple de l'énoncé**, qui vous donnait `{A,C,E}`.
- Coût : `2ⁿ × m × 2 = ` **O(m·2ⁿ)** — le même que l'algorithme de l'exercice 2.
- Variations du chapitre 4 : multi-rubans (quadratique), ruban bi-infini et
  alphabet riche (constante), **non déterminisme — même pouvoir de décision,
  ralentissement exponentiel**. C'est ce dernier point qui fonde `P = NP ?`.
MD,
            ],

            /* ================= Séance 8 ================= */
            [
                'title' => 'Décidabilité et indécidabilité',
                'chapitre' => 'C5',
                'duree_min' => 30,
                'prerequis' => "Les séances 5 et 6.",
                'intro' => <<<'MD'
Jusqu'ici, tous les problèmes rencontrés étaient solubles — coûteux, parfois
absurdement, mais solubles.

Aujourd'hui, on montre qu'il existe des problèmes qu'**aucun** algorithme ne peut
résoudre. Pas « qu'on ne sait pas résoudre » : **qu'on a démontré impossibles**.

C'est le plus beau résultat du module, et il tient en une page.
MD,
                'body' => <<<'MD'
## Les trois niveaux

Reprenons la hiérarchie de la séance 5, et remplissons-la.

| Niveau | Définition | Exemple |
|---|---|---|
| **décidable** | une MT s'arrête sur **toute** entrée et répond OUI ou NON | `aⁿbⁿ`, l'ensemble stable |
| **semi-décidable** | une MT s'arrête et accepte si `w ∈ L` — **peut boucler** sinon | le problème de l'arrêt |
| **ni l'un ni l'autre** | aucune MT ne le reconnaît | le complémentaire de l'arrêt |

## Un langage décidable : `aⁿbⁿ`

```
L = { aⁿbⁿ | n ≥ 0 }
```

Une machine qui le décide : barrer un `a` à gauche, puis un `b` à droite, et
recommencer. Si tout est barré en même temps, accepter ; sinon, rejeter.

**Elle s'arrête toujours** : le mot est fini et il rétrécit à chaque tour. Donc
`L` est décidable.

C'est l'exemple canonique, et il vaut la peine de le savoir : il montre aussi
qu'une MT fait des choses qu'un automate fini ne peut pas faire — **compter**.

## Le problème de l'arrêt

> **ARRÊT** : étant donné une machine de Turing `M` et une entrée `w`, la machine
> `M` s'arrête-t-elle sur `w` ?

C'est une question parfaitement claire, et à laquelle on aimerait beaucoup savoir
répondre — ce serait un détecteur de boucles infinies universel.

> **Théorème (Turing, 1936).** Le problème de l'arrêt est **indécidable**.

### La démonstration

Elle tient en cinq lignes, et elle vaut la peine d'être sue : elle tombe
régulièrement.

**Supposons** qu'il existe une machine `H` qui décide l'arrêt :

```
H(M, w) = OUI  si M s'arrête sur w
          NON  sinon
```

Construisons alors une machine `D` qui prend en entrée le code d'une machine
`M` :

```
D(M) :
    si H(M, M) = OUI   alors  boucler indéfiniment
    sinon                     s'arrêter
```

`D` fait donc **le contraire** de ce que fait `M` sur elle-même.

**Maintenant, exécutons `D` sur son propre code.** Deux cas, et les deux sont
absurdes :

| Hypothèse | Conséquence | Contradiction |
|---|---|---|
| `D(D)` **s'arrête** | alors `H(D,D) = OUI`, donc par construction `D` **boucle** | ✗ |
| `D(D)` **boucle** | alors `H(D,D) = NON`, donc par construction `D` **s'arrête** | ✗ |

Les deux cas sont contradictoires. **Donc `H` n'existe pas.**

C'est un raisonnement par **diagonalisation**, le même que celui de Cantor pour
montrer que les réels ne sont pas dénombrables. Le mot est à employer.

### Pourquoi il est semi-décidable

L'arrêt n'est pas complètement hors de portée :

> **On peut simuler `M` sur `w`.** Si `M` s'arrête, la simulation s'arrête aussi
> et on répond OUI. Si `M` ne s'arrête pas, la simulation ne s'arrête jamais — on
> ne répond jamais NON.

**On sait donc reconnaître les OUI, jamais les NON.** C'est exactement la
définition de semi-décidable.

## Un argument de comptage

Une seconde façon de voir qu'il existe des problèmes indécidables, plus rapide et
souvent appréciée en question de cours :

> Une machine de Turing se décrit par un texte fini. Il y a donc un nombre
> **dénombrable** de machines de Turing.
>
> Un langage est un ensemble de mots. Il y a un nombre **non dénombrable** de
> langages.
>
> Il y a donc « beaucoup plus » de langages que de machines : **presque tous les
> langages sont indécidables.**

L'argument ne dit pas lesquels — c'est ce que fait la diagonalisation.

## Les autres problèmes indécidables

À citer si l'on vous en demande :

| Problème | Question |
|---|---|
| **l'arrêt** | `M` s'arrête-t-elle sur `w` ? |
| l'**arrêt sur ruban vide** | `M` s'arrête-t-elle sur l'entrée vide ? |
| l'**équivalence** | `M₁` et `M₂` reconnaissent-elles le même langage ? |
| la **totalité** | `M` s'arrête-t-elle sur **toute** entrée ? |
| le **problème de correspondance de Post** | — |

Et le résultat qui les regroupe :

> **Théorème de Rice.** Toute propriété **non triviale** du *langage* reconnu par
> une machine de Turing est indécidable.

« Non triviale » signifie : vraie pour certaines machines et fausse pour
d'autres. « Du langage » signifie : sur ce que la machine **calcule**, pas sur sa
structure.

En pratique : « le langage de `M` est-il vide ? », « contient-il `ab` ? », « est-il
infini ? » — **tout cela est indécidable**. Alors que « `M` a-t-elle plus de dix
états ? » est décidable, parce que c'est une propriété de la machine, pas de son
langage.

## Décidabilité et complexité : deux questions distinctes

C'était la **question 3 de l'exercice 4** de janvier, et c'est la conclusion
naturelle de cette séance.

> *En quoi la décidabilité d'un problème diffère-t-elle de sa complexité ?
> Donner un exemple concret dans le cas de Graphopolis.*

| | Décidabilité | Complexité |
|---|---|---|
| La question | **peut-on, en principe, le résoudre ?** | **à quel coût ?** |
| La réponse | oui ou non — une existence | une fonction de `n` |
| Ce qu'elle ignore | le temps, entièrement | l'existence, supposée acquise |

**Ce sont deux questions indépendantes**, et la réponse attendue le dit avec
l'exemple :

> L'**ensemble stable** est **décidable** : la machine de l'exercice 1 s'arrête
> sur tout graphe fini, puisqu'elle n'énumère qu'un nombre fini de
> sous-ensembles. Mais sa **complexité est exponentielle**, `O(m·2ⁿ)` — pour
> quarante carrefours, cinquante-quatre ans de calcul. **La décidabilité ne
> garantit donc pas l'efficacité.**
>
> À l'inverse, le **problème de l'arrêt** est **indécidable** : aucune machine ne
> peut y répondre pour toute entrée. La question de sa complexité **ne se pose
> même pas** — on ne mesure pas le coût d'un algorithme qui n'existe pas.

**La dernière phrase est celle qui montre qu'on a compris.** L'ordre est :
d'abord l'existence, ensuite seulement le coût.
MD,
                'recap' => <<<'MD'
- **Décidable** (s'arrête toujours) ⊂ **semi-décidable** (s'arrête sur les OUI) ⊂
  tout. Inclusions **strictes**.
- `aⁿbⁿ` est décidable : barrer un `a` et un `b`, le mot rétrécit donc ça
  s'arrête.
- **Le problème de l'arrêt est indécidable** (Turing, 1936). La preuve : supposer
  `H`, construire `D` qui fait le contraire, exécuter `D(D)`, les deux cas se
  contredisent. C'est la **diagonalisation**.
- Il est **semi-décidable** : simuler `M` répond OUI si elle s'arrête, ne répond
  jamais NON.
- Argument de comptage : **dénombrable** de machines, **non dénombrable** de
  langages.
- **Théorème de Rice** : toute propriété non triviale **du langage** reconnu est
  indécidable.
- **Décidabilité = peut-on ? · Complexité = à quel coût ?** L'ensemble stable est
  décidable mais exponentiel ; pour l'arrêt, **la question du coût ne se pose
  même pas**.
MD,
            ],

            /* ================= Séance 9 ================= */
            [
                'title' => 'P, NP et NP-complet',
                'chapitre' => 'C6',
                'duree_min' => 35,
                'prerequis' => "Les séances 4 et 8. C'est l'exercice 3 de janvier, dont la question 3 était vide.",
                'intro' => <<<'MD'
Voici le chapitre le plus cité de l'informatique théorique, et celui où la copie
de janvier a produit sa phrase la plus problématique :

> « NP est par définition décidable donc p ⊆ NP est décidable »

Le correcteur a mis « **?** ». La phrase est **circulaire** — elle définit NP par
NP — et elle confond deux notions distinctes, la décidabilité et
l'appartenance à une classe de complexité.

Et la question 3, « montrer que l'Ensemble Stable appartient à NP », est restée
**vide**. Elle tient en six lignes, et à la fin de cette séance vous saurez les
écrire.
MD,
                'body' => <<<'MD'
## Deux définitions, à citer mot pour mot

Le sujet exige de « justifier rigoureusement en utilisant les définitions
appropriées ». Voici les deux à connaître par cœur.

### La classe P

> Un problème de décision `p` appartient à **P** s'il existe un algorithme
> **déterministe** et une constante `k` tels que, pour toute entrée `x` de taille
> `n`, l'algorithme décide `p` en **`O(nᵏ)`** étapes.

En une phrase : **P, ce sont les problèmes qu'on sait résoudre vite.**

Deux précisions qui comptent :

- « Vite » veut dire **polynomial**, pas « rapide en pratique ». Un algorithme en
  `O(n¹⁰⁰)` est dans P.
- **`O(2ⁿ)` n'est pas polynomial** : `n` est à l'exposant. C'est la distinction de
  la séance 4.

### La classe NP

> Un problème `p` appartient à **NP** si, pour toute instance dont la réponse est
> OUI, il existe un **certificat** de taille polynomiale, **vérifiable en temps
> polynomial** par un algorithme déterministe.

En une phrase : **NP, ce sont les problèmes dont on sait vérifier vite une
solution proposée.**

Et la définition équivalente, à mentionner :

> De façon équivalente, `p` est décidable en temps polynomial par une machine de
> Turing **non déterministe** — une machine qui peut « deviner » la bonne
> solution.

C'est ici que sert la séance 7 : le non-déterminisme ne change pas ce qu'on peut
décider, mais on ne sait pas le simuler efficacement. **NP** veut d'ailleurs dire
*Non-deterministic Polynomial*, **pas** « non polynomial ». C'est une confusion
fréquente et elle se voit.

## Ce qui n'allait pas dans la phrase de janvier

> « NP est par définition décidable donc p ⊆ NP est décidable »

Trois problèmes :

1. **Elle est circulaire.** « NP est par définition décidable » n'est pas une
   définition de NP ; c'est une conséquence.
2. **Elle confond deux niveaux.** La décidabilité dit « il existe un algorithme
   qui s'arrête ». NP dit « il existe un **vérificateur polynomial** ». Ce sont
   deux échelles différentes, comme on l'a vu séance 8.
3. **`p ⊆ NP` est un abus.** `p` est un problème, NP un ensemble de problèmes. On
   écrit **`p ∈ NP`**.

La formulation correcte, si l'on veut faire ce lien :

> « Tout problème de NP est décidable : il suffit d'énumérer tous les certificats
> possibles — il y en a un nombre fini, puisqu'ils sont de taille polynomiale —
> et de tester chacun avec le vérificateur. Cette procédure s'arrête toujours,
> même si elle est exponentielle. »

**Notez la structure : on démontre, on ne pose pas.**

## La relation entre P et NP

```
P  ⊆  NP
```

**Pourquoi ?** Si l'on sait **résoudre** un problème en temps polynomial, on sait
aussi **vérifier** une solution en temps polynomial : on ignore le certificat et
on résout soi-même.

Et la question ouverte :

```
P  =?  NP
```

> Sait-on **résoudre** aussi vite qu'on sait **vérifier** ?

Personne ne le sait depuis 1971. La conjecture largement admise est **P ≠ NP**.

**Un piège à éviter**, et le corrigé de mai 2025 le pointe explicitement. Un
raisonnement du type :

> « SAT admet un algorithme exponentiel, donc SAT ∉ P, donc P ≠ NP »

est **faux**. Exhiber un algorithme exponentiel ne prouve pas qu'il n'en existe
pas de polynomial. **Pour montrer qu'un problème n'est pas dans P, il faudrait
démontrer qu'aucun algorithme polynomial n'existe** — et personne n'y est
parvenu.

## Montrer qu'un problème est dans NP

C'est la question 3 de janvier, celle restée vide. **Elle a toujours la même
forme, et elle se répond en six lignes.**

### La recette

1. **Donner le certificat** — l'objet qu'on propose comme preuve.
2. **Donner le vérificateur** — ce qu'on fait pour le contrôler.
3. **Chiffrer son coût** — et conclure qu'il est polynomial.

### Appliquée à l'Ensemble Stable

> **Certificat.** Un sous-ensemble `S ⊆ V`.
>
> **Vérificateur.** Étant donnés `(G, k)` et `S` :
>
> 1. **Test de taille** — calculer `|S|` et vérifier `|S| ≥ k`. Coût **O(n)**.
> 2. **Test de stabilité** — pour chaque arête `(u,v) ∈ E`, vérifier qu'on n'a
>    **pas** à la fois `u ∈ S` et `v ∈ S`. Coût **O(m)**.
>
> **Coût total : O(n + m)**, polynomial en la taille de l'entrée.
>
> **Correction.** Si `S` est bien un stable de taille ≥ `k`, le vérificateur
> accepte ; sinon il rejette. Le certificat est validé **si et seulement si**
> l'instance est positive.
>
> Donc **l'Ensemble Stable ∈ NP.**

Six lignes. **Quatre points de barème pour six lignes**, une fois la recette
connue.

### Le déroulement sur Graphopolis

Ajoutez-le : l'énoncé vous donnait le certificat.

Instance : le graphe de Graphopolis, `k = 3`. Certificat proposé : `S = {A,C,E}`.

- Taille : `|S| = 3 ≥ 3`. ✓
- `(A,B)` : `A ∈ S`, `B ∉ S`. ✓
- `(B,C)` : `B ∉ S`. ✓
- `(A,D)` : `A ∈ S`, `D ∉ S`. ✓
- `(B,E)` : `B ∉ S`. ✓

**Validé en quatre tests.** Instance positive confirmée.

## NP-complet

> Un problème `p` est **NP-complet** si :
>
> 1. **`p ∈ NP`** — il admet un vérificateur polynomial ;
> 2. **`p` est NP-difficile** — tout problème `q ∈ NP` se réduit polynomialement
>    à `p`, noté `q ≤ₚ p`.

**Les deux conditions, toujours.** Oublier la première est l'erreur classique :
sans elle, on définit NP-**difficile**, qui est plus large.

| Terme | Conditions |
|---|---|
| **NP-difficile** | condition 2 seule — au moins aussi dur que tout NP |
| **NP-complet** | conditions 1 **et** 2 — le plus dur **dans** NP |

### Ce que ça veut dire

> Les problèmes NP-complets sont les **plus difficiles de NP**. Si l'on trouvait
> un algorithme polynomial pour **un seul** d'entre eux, on en aurait un pour
> **tous les problèmes de NP**, et on aurait démontré **P = NP**.

C'est pour ça qu'ils sont si étudiés : ils sont tous équivalents entre eux.

### L'histoire, à citer

- **1971, Cook** : SAT est NP-complet. Le premier, démontré directement.
- **1972, Karp** : vingt-et-un problèmes NP-complets, dont **l'ensemble stable**,
  la **couverture par sommets** et l'**ensemble dominant** — les trois de votre
  exercice 4.

## Les quatre questions de janvier

Pour mémoire, ce qu'il fallait répondre à chacune :

| Question | Réponse |
|---|---|
| **1.** Que signifie `p ∈ P` ? | décidable en `O(nᵏ)` par un algorithme déterministe |
| **2.** Que signifie `p ∈ NP` ? | **certificat** polynomial, **vérifiable** en temps polynomial |
| **3.** Montrer que l'Ensemble Stable ∈ NP | certificat `S`, vérificateur en `O(n+m)` |
| **4.** Que signifie NP-complet ? | `p ∈ NP` **et** tout `q ∈ NP` se réduit à `p` |

**Quatre questions, quatre définitions.** C'est l'exercice le plus mécanique du
sujet, et le mieux balisé — à condition d'avoir les définitions exactes en tête.
MD,
                'recap' => <<<'MD'
- **P** : décidable en **`O(nᵏ)`** par un algorithme **déterministe**. « Vite »
  = polynomial, même `n¹⁰⁰`.
- **NP** : il existe un **certificat** de taille polynomiale, **vérifiable en
  temps polynomial**. Équivalent : décidable en temps polynomial par une MT **non
  déterministe**.
- **NP = *Non-deterministic Polynomial*, pas « non polynomial ».**
- On écrit **`p ∈ NP`**, pas `p ⊆ NP`. Et **on démontre**, on ne pose pas.
- **P ⊆ NP** : savoir résoudre implique savoir vérifier. `P = NP ?` reste ouvert.
- **Exhiber un algorithme exponentiel ne prouve pas qu'un problème n'est pas dans
  P.**
- Montrer qu'un problème est dans NP : **certificat · vérificateur · coût**. Pour
  l'ensemble stable : `S`, taille en O(n), stabilité en O(m), total **O(n+m)**.
- **NP-complet = dans NP ET NP-difficile.** Les deux conditions. Sans la
  première, c'est NP-difficile.
- Cook 1971 (SAT), Karp 1972 (21 problèmes, dont les trois de l'exercice 4).
MD,
            ],

            /* ================= Séance 10 ================= */
            [
                'title' => 'Les réductions polynomiales',
                'chapitre' => 'C6',
                'duree_min' => 35,
                'prerequis' => "La séance 9. C'est l'exercice 4 de janvier, rendu sans construction.",
                'intro' => <<<'MD'
Dernière notion théorique, et la plus utile : la **réduction**.

Elle permet de transporter la difficulté d'un problème à un autre. C'est l'outil
qui a produit les vingt-et-un problèmes de Karp, et c'est ce que l'exercice 4 de
janvier demandait — deux fois.

Votre copie contenait « quelques lignes de notation ensembliste, aucune
construction ». C'est justement l'inverse de ce qu'on attend : une réduction est
une **construction**, pas une formule.
MD,
                'body' => <<<'MD'
## La définition

> Une **réduction polynomiale** de `A` vers `B`, notée **`A ≤ₚ B`**, est une
> transformation `f` calculable en temps polynomial qui, à toute instance `x` de
> `A`, associe une instance `f(x)` de `B`, telle que :
>
> **`x` est une instance positive de `A` ⟺ `f(x)` est une instance positive de
> `B`.**

Trois exigences, et il faut les traiter séparément dans la copie :

1. `f` se calcule en **temps polynomial** ;
2. le sens **⟹** : si `x` est un OUI, alors `f(x)` est un OUI ;
3. le sens **⟸** : si `f(x)` est un OUI, alors `x` est un OUI.

**Le troisième point est celui qu'on oublie.** Sans lui, la réduction ne prouve
rien.

## Ce que `A ≤ₚ B` signifie

La notation aide : **`A` est « plus petit » que `B`**, c'est-à-dire **au plus
aussi difficile**.

> Si l'on sait résoudre `B` en temps polynomial, alors on sait résoudre `A` en
> temps polynomial : il suffit de transformer l'instance et d'appeler l'algorithme
> de `B`.

Et la contraposée, celle qu'on utilise :

> Si `A` est difficile, alors `B` l'est aussi.

## Le sens de la flèche — le piège

C'est **l'erreur numéro un** de tout le chapitre. Retenez-la comme une règle
mécanique :

> **Pour montrer que `B` est NP-difficile, on réduit un problème NP-difficile
> connu `A` VERS `B`. On écrit `A ≤ₚ B`.**
>
> **Le problème connu est à GAUCHE. Le nouveau problème est à DROITE.**

Le moyen mnémotechnique : *on transporte la difficulté du connu vers le nouveau.*

Et le contrôle de cohérence, à faire systématiquement : demandez-vous « si je
savais résoudre le problème de droite, saurais-je résoudre celui de gauche ? » Si
la réponse est oui, le sens est bon.

## Question 1 — Ensemble Stable vers Couverture par Sommets

> **CS** : existe-t-il `C ⊆ V` de taille `k` tel que **chaque arête ait au moins
> une extrémité dans `C`** ?

### Le lemme

Tout repose sur une observation, et il faut l'énoncer comme un lemme :

> **`S ⊆ V` est un ensemble stable dans `G` si et seulement si `V \ S` est une
> couverture par sommets de `G`.**

C'est intuitif : un stable ne contient **aucune** arête entière ; donc chaque
arête a au moins une extrémité **en dehors** — donc dans le complémentaire.

### La preuve

**Sens ⟹.** Soit `S` un stable, et `(u,v)` une arête quelconque. Par définition
du stable, on ne peut pas avoir à la fois `u ∈ S` et `v ∈ S`. Donc au moins l'un
des deux appartient à `V \ S`. **Toute arête est couverte** : `V \ S` est une
couverture. ∎

**Sens ⟸.** Soit `C = V \ S` une couverture. Prenons `u, v ∈ S` et supposons par
l'absurde que `(u,v) ∈ E`. Alors `C` doit couvrir cette arête, donc `u ∈ C` ou
`v ∈ C`. Mais `u, v ∈ S = V \ C`, donc ni l'un ni l'autre n'est dans `C` :
**contradiction**. Aucune arête ne relie deux sommets de `S` : `S` est stable. ∎

### La réduction

Elle est d'une simplicité remarquable :

| | |
|---|---|
| **Entrée IS** | `(G = (V,E), k)` |
| **Sortie CS** | `(G' = G, k' = n − k)` |

**On garde le même graphe et on change seulement le seuil.**

**Équivalence.** `IS(G, k)` admet une solution de taille ≥ `k`
⟺ `CS(G, n−k)` admet une solution de taille ≤ `n−k`. C'est le lemme.

**Polynomialité.** La transformation se réduit à un calcul de `n − k` :
**O(1)**. Évidemment polynomial.

**Conclusion.** Si l'on disposait d'un algorithme polynomial pour CS, on
l'appellerait avec `(G, n−k)` pour résoudre `IS(G, k)` en temps polynomial. Donc
**`IS ≤ₚ CS`**.

### Sur Graphopolis

`n = 5`, `k = 3`. La réduction donne `k' = 5 − 3 = 2`.

Le stable `{A, C, E}` correspond à la couverture `V \ {A,C,E} = {B, D}`.
Vérifions que `{B,D}` couvre bien tout :

| Arête | Couverte par |
|---|---|
| (A, B) | **B** |
| (B, C) | **B** |
| (A, D) | **D** |
| (B, E) | **B** |

Les quatre arêtes sont couvertes, avec deux sommets. ✓

## Question 2 — Couverture par Sommets vers Ensemble Dominant

> **ED** : existe-t-il `D ⊆ V` tel que **tout sommet soit dans `D` ou adjacent à
> un sommet de `D`** ?

Ici, pas de lemme miracle : il faut **construire** un nouveau graphe. On emploie
un **gadget**, et c'est le mot du cours.

### L'idée

> Pour chaque arête `e = (u,v)`, on ajoute un **sommet artificiel `wₑ`**, relié
> **uniquement** à `u` et `v`.
>
> Pour que `wₑ` soit dominé, il faut que `u` ou `v` soit dans l'ensemble
> dominant — **c'est exactement la condition de couverture de l'arête `(u,v)`.**

Le gadget transforme donc « couvrir une arête » en « dominer un sommet ». C'est
tout le mécanisme, et il faut l'énoncer avant les formules.

### La construction

```
V' = V ∪ { wₑ | e ∈ E }
E' = E ∪ { (u, wₑ), (v, wₑ) | e = (u,v) ∈ E }
k' = k
```

Chaque arête `(u,v)` devient un **triangle** `u — wₑ — v` dans `G'`.

**Taille.** `|V'| = n + m`, `|E'| = 3m`. La construction est **polynomiale**.

### La preuve, dans les deux sens

*(On suppose `G` sans sommet isolé — précisez-le.)*

**Sens ⟹ (CS donne ED).** Soit `C` une couverture de taille ≤ `k`. Montrons que
`C` domine `G'`.

- *Les gadgets.* Pour chaque `wₑ` avec `e = (u,v)` : `C` couvre l'arête `(u,v)`,
  donc `u ∈ C` ou `v ∈ C`. Or `wₑ` est adjacent aux deux. **`wₑ` est dominé.**
- *Les sommets d'origine.* Soit `v ∈ V \ C`. Comme `G` n'a pas de sommet isolé,
  `v` a au moins une arête ; et comme `C` est une couverture, l'autre extrémité
  est dans `C`. **`v` est dominé.** ∎

**Sens ⟸ (ED donne CS).** Soit `D` un dominant de taille ≤ `k` dans `G'`.

*Étape de nettoyage* — et c'est elle qu'on oublie : si `wₑ ∈ D`, on le remplace
par `u`. Cela ne peut qu'améliorer la domination, puisque `u` a plus de voisins
que `wₑ`, et cela n'augmente pas `|D|`. **On peut donc supposer `D ⊆ V`.**

Pour toute arête `(u,v)`, le gadget `wₑ` n'a que deux voisins : `u` et `v`. Comme
`D` est dominant et que `wₑ ∉ D`, il faut `u ∈ D` ou `v ∈ D`. **`D` couvre
l'arête.** ∎

**Conclusion.** `CS(G,k) = OUI ⟺ ED(G',k) = OUI`, et la construction est en
`O(n+m)`. Donc **`CS ≤ₚ ED`**.

### Sur Graphopolis

`E = {(A,B), (B,C), (A,D), (B,E)}`, `k = 2`. On ajoute `w_AB, w_BC, w_AD, w_BE`.

La couverture `C = {A, B}` doit dominer `G'` :

| Sommet | Dominé par |
|---|---|
| `w_AB` | **A** |
| `w_BC` | **B** |
| `w_AD` | **A** |
| `w_BE` | **B** |
| C, D, E | tous adjacents à A ou B |

`{A, B}` est bien dominant dans `G'`. ✓

## Le gabarit d'une réduction

Cinq points. **Cinq paragraphes sur la copie.**

1. **L'idée en français.** « Pour chaque arête on ajoute un sommet espion qui… »
   *Sans ce paragraphe, la construction est illisible.*
2. **La construction**, formellement : `V'`, `E'`, `k'`.
3. **La polynomialité** : sa taille, et une phrase.
4. **La preuve dans les deux sens**, séparément. ⟹ puis ⟸.
5. **Un exemple**, sur le graphe de l'énoncé.

**C'est le point 1 qui manquait en janvier.** « Quelques lignes de notation
ensembliste » sans l'idée directrice : le correcteur ne peut pas suivre, et ne
peut rien créditer.

Et rappelez-vous que **l'exemple est souvent offert** : ici Graphopolis servait
aux quatre exercices. Le dérouler coûte cinq minutes et prouve que la
construction fonctionne.
MD,
                'recap' => <<<'MD'
- **`A ≤ₚ B`** : une transformation **polynomiale** qui préserve la réponse dans
  **les deux sens**. Le sens ⟸ est celui qu'on oublie.
- `A ≤ₚ B` veut dire **`A` est au plus aussi difficile que `B`**.
- **Le piège du sens** : pour montrer que `B` est NP-difficile, on réduit un
  problème connu `A` **vers** `B`. **Le connu à gauche, le nouveau à droite.**
- **IS ≤ₚ CS** repose sur un lemme : **`S` est stable ⟺ `V \ S` est une
  couverture.** La réduction garde le graphe et pose `k' = n − k`. O(1).
- **CS ≤ₚ ED** utilise un **gadget** : un sommet `wₑ` par arête, relié à ses deux
  extrémités. Dominer `wₑ` = couvrir l'arête. Ne pas oublier l'**étape de
  nettoyage** dans le sens ⟸.
- Gabarit en cinq points : **idée en français** · construction · polynomialité ·
  preuve dans les deux sens · exemple.
- **Une réduction est une construction, pas une formule.**
MD,
            ],

            /* ================= Séance 11 ================= */
            [
                'title' => "L'analyse amortie",
                'chapitre' => 'C7',
                'duree_min' => 35,
                'prerequis' => "Les séances 2 à 4. C'est la partie II de janvier, et elle cache une astuce.",
                'intro' => <<<'MD'
Retour au comptage, avec une nuance nouvelle et très utile.

Certaines opérations sont **presque toujours** bon marché, et **de temps en
temps** très chères. Mesurer leur pire cas donne une image fausse : on conclurait
qu'une insertion dans un `ArrayList` coûte `O(n)`, alors qu'en pratique elle est
gratuite.

L'**analyse amortie** répond à la bonne question : *sur une suite de `N`
opérations, combien coûte chacune en moyenne ?*

Et la partie II de janvier cache une astuce que je vous laisse chercher dix
secondes avant de la donner.
MD,
                'body' => <<<'MD'
## L'énoncé de janvier

> Un tableau dynamique redimensionne automatiquement sa capacité.
>
> - **Capacité initiale : 1.**
> - Quand le tableau est plein et qu'un élément doit être inséré :
>   **si la capacité est paire → capacité × 2 ; si elle est impaire → capacité × 3.**
> - Après redimensionnement, tous les éléments sont recopiés.
>
> **Coût** : insertion simple = 1 ; insertion avec redimensionnement =
> **ancienne capacité + 1**.
>
> 1. Calculer le coût de chaque insertion jusqu'à la 10ᵉ.
> 2. Donner le coût total pour `N` insertions.
> 3. Déterminer la complexité amortie d'une insertion.

## L'astuce

Déroulez les capacités successives, en appliquant la règle :

```
1  est impair  →  ×3  →  3
3  est impair  →  ×3  →  9
9  est impair  →  ×3  →  27
27 est impair  →  ×3  →  81
```

> **Toutes les capacités sont impaires. La règle « paire → ×2 » ne se déclenche
> jamais.**

Les capacités sont exactement les **puissances de 3** : `1, 3, 9, 27, 81, 243…`

**Écrivez cette observation en premier sur votre copie.** Elle vaut des points à
elle seule, et sans elle on s'embrouille dans deux règles au lieu d'une.

*(La raison est immédiate : un nombre impair multiplié par 3 reste impair. Comme
on part de 1, on n'en sort jamais.)*

## Question 1 — les dix premières insertions

Un tableau, une ligne par insertion. **C'est la forme attendue.**

| Insertion | Capacité avant | Éléments avant | Action | Coût |
|---|---|---|---|---|
| 1 | 1 | 0 | insertion directe | 1 |
| 2 | 1 | **1 (plein)** | redimensionne 1 → 3, puis insère | **1 + 1 = 2** |
| 3 | 3 | 2 | insertion directe | 1 |
| 4 | 3 | **3 (plein)** | redimensionne 3 → 9, puis insère | **3 + 1 = 4** |
| 5 | 9 | 4 | insertion directe | 1 |
| 6 | 9 | 5 | insertion directe | 1 |
| 7 | 9 | 6 | insertion directe | 1 |
| 8 | 9 | 7 | insertion directe | 1 |
| 9 | 9 | 8 | insertion directe | 1 |
| 10 | 9 | **9 (plein)** | redimensionne 9 → 27, puis insère | **9 + 1 = 10** |

**Coût total : 1+2+1+4+1+1+1+1+1+10 = 23.**

Deux remarques de méthode :

- **La colonne « éléments avant » est indispensable.** C'est elle qui dit si le
  tableau est plein. Sans elle, on se trompe d'une insertion.
- Les redimensionnements ont lieu aux insertions **2, 4 et 10** — c'est-à-dire
  aux insertions **`3ⁱ + 1`**. On y revient tout de suite.

## Question 2 — le coût total pour `N` insertions

On **sépare** le coût en deux parts. C'est la technique clef.

> **Chaque insertion coûte 1 de base.** Total : `N`.
>
> **Chaque redimensionnement ajoute le coût de recopie**, égal à l'ancienne
> capacité, soit `3ⁱ`.

Quand a lieu le `i`-ième redimensionnement ? Quand le tableau de capacité `3ⁱ`
est plein, donc à l'insertion numéro **`3ⁱ + 1`**. Il a donc lieu si et seulement
si `3ⁱ + 1 ≤ N`.

```
T(N)  =    N     +    Σ  3ⁱ
        coût de       pour tout i tel que 3ⁱ < N
         base
```

### La borne

La somme géométrique se majore :

```
Σ 3ⁱ  =  (3^(j+1) − 1) / 2   ≤  3N / 2       (car 3^j < N)
```

D'où :

```
T(N)  ≤  N + 3N/2  =  5N/2  =  O(N)
```

**Vérification sur `N = 10`** — et il faut toujours vérifier :

Les `i` tels que `3ⁱ + 1 ≤ 10` sont `i = 0, 1, 2` (soit 2, 4 et 10).
Somme des recopies : `1 + 3 + 9 = 13`.
`T(10) = 10 + 13 = 23`. ✓ **C'est bien le total du tableau.**

> **Attention en relisant le corrigé officiel.** Il indexe la somme sur
> `j = ⌊log₃ N⌋`, ce qui donne le bon résultat pour `N = 10` mais pas en général :
> pour `N = 9`, sa formule donne 22 alors que le vrai total est **13** ; pour
> `N = 27`, elle donne 67 au lieu de **40**. La condition juste est celle
> ci-dessus : **un redimensionnement à l'insertion `3ⁱ + 1`, donc pour `3ⁱ < N`.**
> La borne `T(N) ≤ 5N/2` reste valable dans tous les cas, et la conclusion ne
> change pas.

## Question 3 — la complexité amortie

> **Coût amorti = coût total d'une suite d'opérations ÷ nombre d'opérations.**

```
coût amorti  =  T(N) / N  ≤  (5N/2) / N  =  5/2  =  O(1)
```

> **Chaque insertion coûte `O(1)` en amorti**, bien qu'une insertion isolée puisse
> coûter jusqu'à `O(n)`.

### L'interprétation — c'est elle qui est notée

Trois phrases, et il faut les écrire :

> **Pire cas individuel.** Lors d'un redimensionnement à la capacité `c`, le coût
> est `c + 1 = O(c)`, potentiellement grand.
>
> **Mais c'est rare.** Un redimensionnement à la capacité `c` n'arrive qu'après
> `c − 1` insertions à coût 1. Le coût cher se **dilue** sur les insertions bon
> marché qui l'ont précédé.
>
> **Image utile.** Chaque insertion « paie » pour elle-même (coût 1) et « met de
> côté » une petite réserve pour le redimensionnement à venir. La réserve
> accumulée suffit toujours à le financer.

Cette dernière image porte un nom, la **méthode du comptable** *(ou des crédits)*.

## Les trois méthodes d'analyse amortie

À citer si l'on vous les demande :

| Méthode | Principe |
|---|---|
| **agrégat** | calculer le coût total `T(N)`, puis diviser par `N` |
| **comptable** *(crédits)* | chaque opération paie un coût fixe ; l'excédent finance les opérations chères |
| **potentiel** | définir une fonction `Φ` sur l'état ; le coût amorti est le coût réel plus la variation de `Φ` |

**La méthode de l'agrégat est celle du corrigé, et la plus simple.** C'est celle
qu'on emploie en épreuve, sauf mention contraire.

## Amorti, moyen, pire cas — à ne pas confondre

C'est la nuance la plus fine du chapitre, et elle tombe.

| | Ce qu'on mesure | Hypothèse |
|---|---|---|
| **pire cas** | l'opération la plus chère | aucune |
| **cas moyen** | l'espérance sur une **distribution** des entrées | probabiliste |
| **amorti** | la moyenne sur une **suite** d'opérations | **aucune** — c'est une garantie |

**L'analyse amortie n'est pas probabiliste.** Elle ne suppose rien sur les
entrées : elle donne une garantie déterministe sur toute suite de `N` opérations.
C'est ce qui la rend plus forte que le cas moyen.

## La variante classique — le doublement

Si la règle était « capacité × 2 » à chaque fois, on aurait les capacités
`1, 2, 4, 8, 16…` et :

```
T(N) = N + (1 + 2 + 4 + … ) ≤ N + 2N = 3N  =  O(N)
```

**Coût amorti `O(1)` là aussi.** C'est le comportement réel d'un `ArrayList`
Java, et c'est la raison pour laquelle on peut y insérer sans y penser.

Et le contre-exemple, à connaître : si l'on **agrandissait de 1** à chaque
saturation, on recopierait tout à chaque insertion :

```
T(N) = 1 + 2 + 3 + … + N = N(N+1)/2  =  O(N²)
```

**Coût amorti `O(N)`, catastrophique.** C'est ce qui montre que la croissance
**multiplicative** est essentielle : c'est elle qui rend les redimensionnements
géométriquement rares.
MD,
                'recap' => <<<'MD'
- **L'astuce de janvier : 1 est impair, et un impair × 3 reste impair.** La règle
  « paire → ×2 » ne se déclenche **jamais**. Les capacités sont les **puissances
  de 3**. L'écrire en premier.
- Tableau des dix insertions, avec la colonne **« éléments avant »**. Total
  **23**.
- **Séparer le coût** : `N` de base, plus les recopies. Redimensionnement à
  l'insertion **`3ⁱ + 1`**, donc pour `3ⁱ < N`.
- `T(N) = N + Σ3ⁱ ≤ N + 3N/2 = **5N/2** = O(N)`. Vérifier : `T(10) = 10 + 13 = 23`.
- **Coût amorti = T(N)/N ≤ 5/2 = O(1).**
- L'interprétation compte : le coût cher est **rare** et se **dilue** sur les
  insertions bon marché qui l'ont précédé.
- Trois méthodes : **agrégat** (celle à employer), comptable, potentiel.
- **L'amorti n'est pas probabiliste** : c'est une garantie sur toute suite de `N`
  opérations, plus forte que le cas moyen.
- Croissance **multiplicative** → `O(1)` amorti. Croissance **de 1** → `O(N²)` au
  total, donc `O(N)` amorti. La différence est essentielle.
MD,
            ],

            /* ================= Séance 12 ================= */
            [
                'title' => 'Les tris, et composer la copie du 25 août',
                'chapitre' => 'C8',
                'duree_min' => 30,
                'prerequis' => "L'ensemble du cours, et particulièrement la séance 2 sur les logarithmes.",
                'intro' => <<<'MD'
Dernière séance. Un chapitre court, qui referme joliment la boucle ouverte à la
séance 2 — on va démontrer qu'**aucun** tri par comparaisons ne peut faire mieux
que `n log n`, et le logarithme y apparaîtra exactement là où on l'attend.

Puis la stratégie du 25 août.
MD,
                'body' => <<<'MD'
## Le modèle des tris par comparaisons

> Un tri est **par comparaisons** si la seule opération qu'il fait sur les
> éléments est de les **comparer deux à deux**. Il ne regarde jamais leur valeur.

C'est le cas du tri par insertion, du tri fusion, du tri rapide, du tri par tas.
Ce n'est **pas** le cas du tri par comptage ni du tri radix, qui exploitent les
valeurs — et qui peuvent donc être plus rapides.

## Les tris à connaître

| Tri | Pire cas | Moyen | Meilleur | En place | Stable |
|---|---|---|---|---|---|
| Insertion | **O(n²)** | O(n²) | **O(n)** | oui | oui |
| Sélection | O(n²) | O(n²) | O(n²) | oui | non |
| **Fusion** | **Θ(n log n)** | Θ(n log n) | Θ(n log n) | **non** | oui |
| **Rapide** *(quicksort)* | **O(n²)** | **Θ(n log n)** | Θ(n log n) | oui | non |
| Par tas *(heapsort)* | **Θ(n log n)** | Θ(n log n) | Θ(n log n) | oui | non |

Deux lignes à commenter, parce qu'elles sont contre-intuitives :

**Le tri rapide est en `O(n²)` au pire**, quand le pivot est systématiquement le
plus petit ou le plus grand élément — typiquement sur un tableau déjà trié. Il
reste le plus rapide en pratique grâce à ses petites constantes.

**Le tri fusion n'est pas en place** : il demande `O(n)` de mémoire
supplémentaire. C'est son seul défaut.

Et deux mots de vocabulaire :

- **en place** — n'utilise qu'une mémoire supplémentaire `O(1)` ;
- **stable** — préserve l'ordre relatif des éléments égaux.

## Pourquoi le tri fusion est en `n log n`

C'est le raisonnement à savoir refaire, et il illustre la séance 2 :

> On coupe le tableau en deux à chaque appel. Après `log₂ n` coupes, on arrive à
> des tableaux d'un élément — **c'est la définition même du logarithme**.
>
> À chaque niveau de la récursion, la fusion des sous-tableaux coûte `Θ(n)` au
> total.
>
> **`log n` niveaux × `Θ(n)` par niveau = `Θ(n log n)`.**

Voilà le logarithme à sa place : il vient de la **division en deux**, exactement
comme annoncé.

## La borne inférieure

Le résultat le plus élégant du chapitre.

> **Tout tri par comparaisons effectue au moins `Ω(n log n)` comparaisons dans le
> pire cas.**

### La démonstration, en quatre lignes

Elle passe par l'**arbre de décision** : un arbre binaire où chaque nœud est une
comparaison, et chaque feuille une permutation possible du tableau.

1. Il y a **`n!` permutations** possibles de `n` éléments. L'arbre doit donc
   avoir **au moins `n!` feuilles** — sinon deux entrées différentes recevraient
   le même traitement.
2. Un arbre **binaire** de hauteur `h` a au plus **`2^h` feuilles**.
3. Donc `2^h ≥ n!`, d'où **`h ≥ log₂(n!)`**.
4. Or, par la formule de Stirling, **`log₂(n!) = Θ(n log n)`**.

**Donc `h = Ω(n log n)`.** La hauteur de l'arbre est le nombre de comparaisons
dans le pire cas. ∎

### Ce que ça veut dire

> `n log n` **n'est pas une limite de notre ingéniosité : c'est une limite du
> modèle.** Aucun tri par comparaisons, connu ou à découvrir, ne fera mieux.
>
> Le tri fusion et le tri par tas atteignent cette borne : ils sont
> **asymptotiquement optimaux**.

Et remarquez où le logarithme apparaît une troisième fois : dans `2^h ≥ n!`,
c'est-à-dire `h ≥ log₂(n!)`. **Le logarithme est l'inverse de la puissance**, et
c'est exactement l'usage qu'on en fait ici.

## Composer la copie du 25 août

**Deux heures.** C'est court pour deux parties.

### La répartition

| Temps | Quoi |
|---|---|
| 0 – 8 min | **Lire tout le sujet.** Repérer l'exemple fil rouge — il resservira partout. |
| 8 – 30 min | **Les questions de définition.** P, NP, NP-complet, décidable. Elles sont courtes et sûres. |
| 30 – 60 min | **La partie « compter »** : actions élémentaires, complexité, temps de calcul. |
| 60 – 95 min | **La machine de Turing.** La plus longue à rédiger. |
| 95 – 112 min | **Réductions**, ou ce qui reste. |
| 112 – 120 min | **Relire.** Aucune question vide, chaque affirmation justifiée. |

**Commencez par les définitions.** Ce sont quatre questions à quatre définitions,
elles ne demandent aucune invention, et elles mettent en confiance. En janvier
elles ont mal tourné faute de formulations exactes — cette fois vous les avez.

### Les six réflexes

1. **Citer la définition avant de s'en servir.** Le sujet l'exige explicitement.
   « Un problème est décidable s'il existe une MT qui… donc… »
2. **Toujours faire le calcul jusqu'au bout.** En janvier, une réponse s'est
   arrêtée sur « si chaque calcul dure 10⁻⁶ seconde ». Multipliez, divisez, et
   **convertissez en unité humaine**.
3. **Une année ≈ 3 × 10⁷ secondes.** Le seul nombre à mémoriser.
4. **Décomposer les comptages avec des étiquettes** :
   `2ⁿ (sous-ensembles) × m (arêtes) × 2 (tests)`. Si un facteur est faux, les
   autres restent crédités.
5. **Toute machine de Turing, toute réduction commence par son idée en
   français.** Sans ce paragraphe, la suite est illisible et rien n'est
   créditable.
6. **Dérouler sur l'exemple de l'énoncé.** Il est offert, et il prouve que la
   construction marche.

### Le piège du vocabulaire

Un dernier passage sur les mots qui vous ont coûté cher :

| Ne dites pas | Dites |
|---|---|
| « logarithmiquement grand » pour dire « énorme » | **« exponentiel »** |
| `Θ(log n)` pour deux boucles imbriquées | **`O(n²)`** |
| « `p ⊆ NP` » | **« `p ∈ NP` »** |
| « NP = non polynomial » | **« non déterministe polynomial »** |
| « c'est décidable donc c'est dans P » | **deux notions distinctes** |

### Et le lendemain

Le 26 août, vous avez AGC à 15 h puis SPP à 20 h. **EP est votre épreuve la plus
courte et la plus reposante** — deux heures, l'après-midi du 25.

Sortez-en sans y repenser. La journée du 26 est celle qui demandera de l'énergie.

## Le mot de la fin

Ce module tenait à une seule notion mal installée : l'ordre de grandeur. Une
séance a suffi à la remettre en place, et elle a débloqué le reste — le comptage,
les classes de complexité, l'analyse amortie, et jusqu'à la borne des tris qu'on
vient de démontrer.

Vous aviez 7 sur 20 avec cette notion inversée. Vous la connaissez maintenant.
MD,
                'recap' => <<<'MD'
- **Tri par comparaisons** : n'examine jamais les valeurs, seulement les
  comparaisons deux à deux.
- **Fusion et par tas : `Θ(n log n)` garanti.** **Rapide : `Θ(n log n)` en moyenne
  mais `O(n²)` au pire** (pivot toujours extrême). **Fusion : pas en place.**
- Le tri fusion est en `n log n` parce qu'il **coupe en deux** — `log n` niveaux
  × `Θ(n)` par niveau.
- **Borne inférieure `Ω(n log n)`** : `n!` permutations, donc `n!` feuilles ; un
  arbre binaire de hauteur `h` a `2^h` feuilles ; `h ≥ log₂(n!) = Θ(n log n)`.
  **C'est une limite du modèle, pas de notre ingéniosité.**
- Le 25 août, 2 heures : **définitions d'abord**, puis comptage, puis machine de
  Turing.
- **Citer la définition · finir le calcul · convertir en unité humaine ·
  étiqueter les facteurs · commencer par l'idée en français · dérouler sur
  l'exemple.**
- **Une année ≈ 3 × 10⁷ secondes.**
MD,
            ],

        ];
    }
}