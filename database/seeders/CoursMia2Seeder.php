<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours de MIA, seconde partie : les quatre autres chapitres qui tombent.
 *
 * D'après la matrice des vingt-quatre sessions depuis 2010 :
 * contraintes 88 %, apprentissage 88 %, représentation des connaissances 75 %,
 * algorithmes classiques 58 %.
 *
 * Deux séances méritent d'être signalées. La douzième porte sur la logique des
 * défauts : c'est l'exercice de mai annoté « Non on veut des défauts », rendu
 * en logique classique. La quatorzième porte sur ID3 : l'exercice a été rendu
 * sans une seule ligne de calcul, alors qu'il est entièrement mécanique et qu'il
 * tombe vingt-et-une fois sur vingt-quatre.
 */
class CoursMia2Seeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'MIA')->first();

        if (! $subject) {
            return;
        }

        // Les sept premières séances sont posées par CoursMiaSeeder.
        $depart = 7;

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

            /* ================= Séance 8 ================= */
            [
                'title' => 'Les contraintes : CLP(FD)',
                'chapitre' => 'Ch4',
                'duree_min' => 35,
                'prerequis' => "La séance 7 : Générer et Tester, et l'énigme Back2Kitchen. On refait le même exercice, autrement.",
                'intro' => <<<'MD'
Le chapitre des contraintes est celui qui tombe le plus souvent : **21 sessions
sur 24**, et à chacune des trois dernières.

On a laissé la séance 7 sur un constat : Générer et Tester essaie 13 824
combinaisons pour une énigme à quatre personnes, parce qu'il ne vérifie rien
avant d'avoir tout affecté.

Aujourd'hui, on renverse la méthode. **On pose d'abord les contraintes, on
affecte ensuite** — et le solveur élimine les valeurs impossibles au fur et à
mesure, avant même de commencer à chercher.

C'est la question 2 de l'exercice 1 de mai, et on la traite en entier.
MD,
                'body' => <<<'MD'
## Ce qu'est un CSP

Un **problème de satisfaction de contraintes** — *Constraint Satisfaction
Problem* — se définit par trois choses, et il faut savoir les nommer :

1. un ensemble de **variables** ;
2. pour chacune, un **domaine** — l'ensemble fini des valeurs qu'elle peut
   prendre ;
3. un ensemble de **contraintes** — des relations qui doivent être vérifiées.

Résoudre le CSP, c'est trouver une affectation de chaque variable dans son
domaine qui satisfait toutes les contraintes.

Le sudoku est un CSP : 81 variables, domaine 1..9, contraintes « tous différents »
sur chaque ligne, chaque colonne, chaque bloc. Un emploi du temps aussi. Une
énigme logique aussi.

## Les trois bibliothèques du cours

| Bibliothèque | Domaine | Usage |
|---|---|---|
| **CLP(FD)** | entiers finis (*Finite Domain*) | énigmes, plannings, sudoku |
| CLP(Q) | rationnels | équations exactes |
| CLP(R) | réels | équations numériques |

**CLP(FD) est celle de l'examen.** Les deux autres sont à connaître de nom.

## La syntaxe, en un tableau

```prolog
:- use_module(library(clpfd)).
```

Cette ligne d'abord. Toujours.

| Écriture | Sens |
|---|---|
| `X in 1..4` | le domaine de X est {1,2,3,4} |
| `[X,Y,Z] ins 1..4` | même chose pour toute une liste (`ins`, avec un s) |
| `X #= Y` | X égale Y |
| `X #\= Y` | X différent de Y |
| `X #< Y`, `X #> Y` | strictement inférieur, supérieur |
| `X #=< Y`, `X #>= Y` | inférieur ou égal, supérieur ou égal |
| `all_distinct(L)` | tous les éléments de L sont deux à deux différents |
| `element(I, L, X)` | le I-ième élément de L vaut X |
| `sum(L, #=, S)` | la somme de L vaut S |
| `labeling([], L)` | **énumère** les solutions |

**Retenez le dièse.** En CLP(FD), tous les opérateurs de comparaison en portent
un : `#=`, `#<`, `#\=`. Sans dièse, c'est de l'arithmétique Prolog ordinaire,
qui exige que tout soit déjà connu. Avec dièse, c'est une **contrainte**, qu'on
peut poser sur des variables encore vides.

C'est toute la différence :

```prolog
?- X < 5.
ERROR: Arguments are not sufficiently instantiated

?- X #< 5.
X in inf..4.
```

La seconde n'échoue pas : elle **restreint le domaine** de X, et attend.

## Le renversement de méthode

| Générer et Tester | Contraintes |
|---|---|
| affecter, puis vérifier | contraindre, puis affecter |
| les contraintes sont des **tests** | les contraintes sont des **filtres** |
| on découvre l'échec à la fin | on élimine les valeurs impossibles d'emblée |
| `=`, `<`, `\=` | `#=`, `#<`, `#\=` |
| `appartient/2` (select) | `ins` + `all_distinct` |
| le retour arrière énumère | `labeling/2` énumère |

Et l'ordre d'écriture s'inverse : en CLP, `labeling` est **la dernière ligne**.
Toutes les contraintes sont posées avant. Si vous mettez `labeling` au milieu,
vous retombez sur Générer et Tester.

## Back2Kitchen en CLP(FD)

Le corrigé de mai emploie une astuce de modélisation qu'il faut comprendre,
parce qu'elle est élégante et déroutante à la fois.

### L'idée : tout est un classement

Au lieu de donner à chaque personne trois attributs, on décide que **toute
variable contient un classement de 1 à 4** :

```prolog
Noms = [Antoine, Fabrice, Louis, Xavier],
Noms ins 1..4, all_distinct(Noms),

Jours = [Lundi, Mardi, Mercredi, Jeudi],
Jours ins 1..4, all_distinct(Jours),

Specs = [Entree, Plat, Dessert, Pain],
Specs ins 1..4, all_distinct(Specs),
```

`Fabrice` contient le classement de Fabrice. `Lundi` contient le classement de
la personne passée lundi. `Pain` contient le classement de celui qui a fait le
pain.

Du coup, **dire que deux choses vont ensemble, c'est dire que leurs classements
sont égaux** :

```prolog
Fabrice #= Lundi,      % Fabrice est passé lundi
Louis   #= Pain,       % Louis a fait le pain
Fabrice #\= Dessert,   % Fabrice n'a pas fait le dessert
```

Relisez ces trois lignes jusqu'à ce qu'elles vous paraissent naturelles. C'est le
cœur de la modélisation.

### Les contraintes de rang

Directes, puisque tout est un rang :

```prolog
Fabrice #\= 1,     % n'a pas gagné
Louis   #< Fabrice, % meilleur classement que Fabrice
Xavier  #> Louis,   % moins bon que Louis
Antoine #= 4,       % pas sur le podium
```

### Les contraintes d'ordre dans la semaine

Là, il faut retrouver **quel jour** une personne est passée. C'est le travail
d'`element/3` :

```prolog
element(Idx_Ant, Jours, Antoine),
element(Idx_Xav, Jours, Xavier),
element(Idx_Lou, Jours, Louis),
Idx_Ant #> Idx_Xav,
Idx_Ant #< Idx_Lou,
```

`element(Idx_Ant, Jours, Antoine)` se lit : « il existe une position `Idx_Ant`
dans la liste `Jours` où l'on trouve le classement d'Antoine ». Comme `Jours`
est rangée dans l'ordre lundi, mardi, mercredi, jeudi, cette position **est**
le numéro du jour.

`element/3` est le grand prédicat de CLP(FD) : il permet d'indexer une liste par
une **variable**. En Prolog ordinaire, `nth1/3` exige que l'indice soit connu ;
`element/3` non.

### La contrainte sur l'entrée et le dessert

```prolog
element(1, Specs, P_Entree),   % 1 = position de Entree dans Specs
element(3, Specs, P_Dessert),  % 3 = position de Dessert dans Specs
P_Dessert #= P_Entree + 1,
```

### L'énumération, en dernier

```prolog
append([Specs, Jours, Noms], LV),
labeling([], LV).
```

On rassemble toutes les variables et on énumère. **Cette ligne est la dernière
du prédicat.**

## La méthode, en cinq temps

Écrivez-la dans votre feuille A4 autorisée :

1. `:- use_module(library(clpfd)).`
2. **Déclarer les variables et leurs domaines** — `ins`.
3. **Poser les contraintes structurelles** — `all_distinct`, en général.
4. **Traduire chaque phrase de l'énoncé** en contraintes `#`, un commentaire par
   phrase.
5. **`labeling([], Vars)` en dernier.**

## Les erreurs qui coûtent des points

| Erreur | Conséquence |
|---|---|
| oublier le `#` : `X = Y` au lieu de `X #= Y` | unification au lieu de contrainte |
| `in` au lieu de `ins` sur une liste | erreur de type |
| `labeling` au milieu | on retombe sur Générer et Tester |
| `<=` au lieu de `#=<` | erreur de syntaxe |
| oublier `all_distinct` | des solutions absurdes, deux personnes premières |

## Pourquoi c'est plus rapide

Dès qu'on pose `Antoine #= 4` et `all_distinct(Noms)`, le solveur retire
immédiatement la valeur 4 du domaine de Fabrice, Louis et Xavier. Il n'essaiera
jamais une combinaison où Louis est quatrième.

Générer et Tester, lui, l'essaie — et découvre l'échec après avoir affecté douze
variables.

Ce retrait automatique des valeurs impossibles porte un nom, la **propagation**,
et c'est le sujet de la séance suivante. On l'y fera à la main, parce que c'est
la forme sous laquelle l'examen le demande.
MD,
                'recap' => <<<'MD'
- Un **CSP** = variables + domaines + contraintes. Savoir le réciter.
- **CLP(FD)** = domaines entiers finis. C'est celle de l'examen. CLP(Q)
  rationnels, CLP(R) réels.
- `:- use_module(library(clpfd)).` en première ligne.
- **Tous les opérateurs portent un dièse** : `#=`, `#\=`, `#<`, `#=<`. Sans
  dièse, ce n'est pas une contrainte.
- `ins` pour une liste, `in` pour une variable. `all_distinct/1`,
  `element/3`, `labeling/2`.
- **`labeling` est la dernière ligne.** Contraindre d'abord, affecter ensuite.
- `element/3` indexe une liste par une **variable** — ce que `nth1/3` ne sait pas
  faire.
- Modélisation de Back2Kitchen : **toute variable contient un classement**, donc
  « aller ensemble » s'écrit `#=`.
MD,
            ],

            /* ================= Séance 9 ================= */
            [
                'title' => 'Consistance, propagation et Branch and Bound',
                'chapitre' => 'Ch4',
                'duree_min' => 35,
                'prerequis' => "La séance 8. Ici on ne programme plus : on déroule les algorithmes à la main, comme l'examen le demande.",
                'intro' => <<<'MD'
La séance précédente vous a appris à écrire des contraintes. Celle-ci vous
apprend ce que la machine en fait — et c'est ça que l'examen demande le plus
souvent, sous forme d'un tableau à remplir à la main.

Trois notions : la **consistance**, la **propagation**, et le **Branch and
Bound** pour les problèmes d'optimisation.

Aucun code aujourd'hui. Du papier, un crayon, et des domaines qu'on rature.
MD,
                'body' => <<<'MD'
## Les trois méthodes de résolution, dans l'ordre de finesse

| Méthode | Quand teste-t-on ? | Coût |
|---|---|---|
| **Générer et Tester** | après affectation **complète** | catastrophique |
| **Backtracking** | après **chaque** affectation | acceptable |
| **Propagation** | **avant** d'affecter, on réduit les domaines | le meilleur |

C'est une progression : chaque méthode teste plus tôt que la précédente.

## Le Backtracking

On affecte les variables une par une, et **dès qu'une contrainte est violée, on
revient en arrière** sans aller plus loin.

Sur Back2Kitchen : dès qu'on pose Fabrice = 1er, la contrainte « Fabrice n'a pas
gagné » échoue. On abandonne immédiatement cette branche — au lieu d'affecter les
onze autres variables pour rien.

C'est ce que fait Prolog nativement. **Le retour arrière de Prolog est un
backtracking.**

## Les consistances

L'idée est plus forte encore : avant même d'affecter, on **retire des domaines
les valeurs qui ne peuvent appartenir à aucune solution**.

### La consistance de nœud

On regarde **une variable et ses contraintes unaires** — celles qui ne portent
que sur elle.

Exemple : `X in 1..10`, contrainte `X #> 6`.
→ On retire 1 à 6. Il reste `X in 7..10`.

C'est le niveau le plus simple. On le fait une fois, au départ.

### La consistance d'arc

On regarde **un couple de variables et la contrainte qui les lie**.

> Une contrainte entre X et Y est **arc-consistante** si, pour chaque valeur du
> domaine de X, il existe **au moins une** valeur du domaine de Y qui satisfait
> la contrainte — et réciproquement.

Autrement dit : une valeur qui n'a aucun partenaire possible en face est
inutile. On la retire.

### Un exemple à dérouler

Trois variables, domaine {1, 2, 3} chacune.
Contraintes : `X #< Y` et `Y #< Z`.

**Arc X → Y (contrainte X < Y)**
Pour chaque valeur de X, existe-t-il un Y plus grand dans {1,2,3} ?

| X | Y possible ? | verdict |
|---|---|---|
| 1 | oui (2 ou 3) | garder |
| 2 | oui (3) | garder |
| 3 | non | **retirer** |

→ `X in {1,2}`

**Arc Y → X (même contrainte, dans l'autre sens)**
Pour chaque Y, existe-t-il un X plus petit ?

| Y | X possible ? | verdict |
|---|---|---|
| 1 | non | **retirer** |
| 2 | oui (1) | garder |
| 3 | oui (1 ou 2) | garder |

→ `Y in {2,3}`

**Arc Y → Z (contrainte Y < Z)**
Pour chaque Y de {2,3}, existe-t-il un Z plus grand ?

| Y | Z possible ? | verdict |
|---|---|---|
| 2 | oui (3) | garder |
| 3 | non | **retirer** |

→ `Y in {2}`

**Voilà la propagation** : le domaine de Y vient de changer, donc **il faut
reprendre tous les arcs qui touchent Y**.

**Arc X → Y de nouveau**, avec `Y in {2}` :

| X | un Y de {2} plus grand ? | verdict |
|---|---|---|
| 1 | oui (2) | garder |
| 2 | non | **retirer** |

→ `X in {1}`

**Arc Z → Y**, avec `Y in {2}` : Z doit être > 2 → `Z in {3}`

**Résultat : X = 1, Y = 2, Z = 3.** Le problème est résolu **sans avoir affecté
quoi que ce soit** — uniquement en retirant des valeurs.

## La règle de la propagation

Retenez-la en une ligne, c'est celle qu'on oublie en épreuve :

> **Dès qu'un domaine change, tous les arcs qui touchent cette variable doivent
> être réexaminés.**

C'est pour ça qu'on tient une file d'arcs à traiter. On la vide, et chaque
réduction y remet des arcs.

## Comment présenter ça sur une copie

Un tableau, une ligne par étape. Le correcteur suit, et vous ne vous perdez pas.

| Étape | Arc examiné | Valeur retirée | Domaines après |
|---|---|---|---|
| 0 | — | — | X{1,2,3} Y{1,2,3} Z{1,2,3} |
| 1 | X→Y | X : 3 | X{1,2} Y{1,2,3} Z{1,2,3} |
| 2 | Y→X | Y : 1 | X{1,2} Y{2,3} Z{1,2,3} |
| 3 | Y→Z | Y : 3 | X{1,2} Y{2} Z{1,2,3} |
| 4 | X→Y (rejoué) | X : 2 | X{1} Y{2} Z{1,2,3} |
| 5 | Z→Y | Z : 1, 2 | X{1} Y{2} Z{3} |

**Le mot « rejoué » à l'étape 4 vaut des points.** Il montre que vous avez
compris que la propagation reboucle.

## Trois issues possibles

Après propagation, trois cas — savoir les nommer :

1. **Un domaine devient vide** → le problème n'a **pas de solution**. On
   s'arrête.
2. **Tous les domaines sont réduits à une valeur** → la solution est trouvée,
   sans recherche.
3. **Des domaines ont plusieurs valeurs** → il faut **énumérer** : on choisit une
   variable, on lui donne une valeur, et on repropage. C'est le rôle de
   `labeling/2`.

## Branch and Bound

Jusqu'ici, on cherchait **une** solution. Le Branch and Bound cherche la
**meilleure**.

Le principe, en deux mots :

- **Branch** — on découpe le problème en sous-problèmes, comme un arbre.
- **Bound** — pour chaque branche, on calcule une **borne** sur ce qu'elle peut
  au mieux rapporter. Si cette borne est moins bonne que la meilleure solution
  déjà trouvée, **on coupe la branche sans l'explorer**.

C'est cette coupe qui fait tout le gain. On n'explore pas ce qui ne peut pas
gagner.

### Un exemple

On maximise. On a déjà trouvé une solution valant **50**.

On arrive sur une branche dont la borne supérieure — le mieux qu'elle puisse
faire — vaut **42**. Inutile de l'explorer : même dans le meilleur des cas, elle
fera moins bien que 50. **On la coupe.**

Si la borne vaut **73**, il faut l'explorer : elle peut contenir mieux.

### Ce qu'il faut retenir pour l'épreuve

- La borne doit être **optimiste** : elle ne doit jamais sous-estimer ce que la
  branche peut donner. Sinon on coupe une branche qui contenait l'optimum.
- Plus la borne est **serrée** — proche du vrai maximum — plus on coupe, plus
  c'est rapide.
- Trouver **vite** une bonne première solution améliore tout, parce que ça donne
  un seuil de coupe élevé dès le départ.

Vous rencontrerez la même idée à la séance 11 avec A\*, et la même encore dans
l'élagage α-β des jeux. C'est un des rares principes qui traverse tout le module.
MD,
                'recap' => <<<'MD'
- Progression : **Générer-Tester** (teste à la fin) → **Backtracking** (teste à
  chaque affectation) → **Propagation** (réduit les domaines avant d'affecter).
- **Consistance de nœud** : contraintes unaires, sur une seule variable.
- **Consistance d'arc** : pour chaque valeur de X, il existe au moins un partenaire
  possible dans le domaine de Y. Sinon on retire la valeur.
- **La règle à ne pas oublier : dès qu'un domaine change, on réexamine tous les
  arcs qui touchent cette variable.** Écrire « rejoué » dans le tableau.
- Trois issues : domaine vide → pas de solution ; tous singletons → solution ;
  sinon → énumérer.
- **Branch and Bound** : découper, borner, **couper les branches dont la borne
  est moins bonne que la meilleure solution connue**.
- La borne doit être **optimiste**, et serrée.
MD,
            ],

            /* ================= Séance 10 ================= */
            [
                'title' => "Parcourir un espace d'états : profondeur et largeur",
                'chapitre' => 'Ch3',
                'duree_min' => 40,
                'prerequis' => "Les séances 4 à 6. On reconstruit l'exercice à 13 points de mai, du départ à l'arrivée.",
                'intro' => <<<'MD'
On arrive à l'exercice qui valait **13 points sur 30** en mai, et qui en a
rapporté 1,25.

Vous aviez pourtant écrit, à la question 9, quelque chose de juste dans son
principe :

```
profondeur(Parcours, Sol) :- arc(Parcours, Sol).
profondeur(Parcours, Sol) :- arc(Parcours, X), profondeur(X, Sol).
```

Un cas de base, un cas récursif. **C'est le bon squelette.** Il a été récité de
mémoire, mais jamais adapté au problème : pas de test d'arrivée, pas de liste de
parcours, pas de garde anti-cycle.

Aujourd'hui, on apprend le squelette **et** les trois choses qu'il faut y
ajouter pour qu'il réponde à une question précise.
MD,
                'body' => <<<'MD'
## Un espace d'états

Beaucoup de problèmes d'IA se ramènent à la même forme :

- un **état initial** ;
- des **actions** qui font passer d'un état à un autre ;
- un **test de but** qui reconnaît un état final ;
- éventuellement un **coût** par action.

Résoudre, c'est trouver un chemin de l'état initial à un état but. Le graphe de
tous les états s'appelle l'**espace d'états** ; il est en général trop grand pour
être construit — on l'explore à la demande.

Pour la Tuyauterie : un état est un tuyau partiel, une action est le placement
et l'orientation de la pièce suivante, le but est d'atteindre la case d'arrivée.

## Les trois stratégies du chapitre

| Stratégie | Ordre d'exploration | Complète ? | Optimale ? | Mémoire |
|---|---|---|---|---|
| **Profondeur** | descend le plus loin possible | non | non | faible |
| **Profondeur bornée** | idem, avec une limite | si la limite suffit | non | faible |
| **Largeur** | niveau par niveau | oui | oui (coûts égaux) | forte |

Trois mots de vocabulaire à savoir définir :

- **complète** : trouve une solution s'il en existe une ;
- **optimale** : trouve la meilleure ;
- et la raison pour laquelle la profondeur n'est ni l'une ni l'autre : elle peut
  s'enfoncer indéfiniment dans une branche infinie, ou renvoyer un chemin long
  alors qu'un court existait ailleurs.

**La largeur est complète mais gourmande en mémoire** : elle garde tout le niveau
courant. C'est le compromis classique, et une question de cours fréquente.

## Le squelette du parcours en profondeur

Le voici, dans sa forme générale :

```prolog
profondeur(Etat, Etat) :-
  but(Etat), !.

profondeur(Etat, Sol) :-
  action(Etat, Suivant),
  profondeur(Suivant, Sol).
```

Comparez avec ce que vous aviez écrit : c'est **la même chose**. Vous aviez le
squelette. Ce qui manquait, ce sont trois ajouts.

### Ajout 1 — le test de but

`arc(Parcours, Sol)` ne teste rien. Il faut un vrai test :

```prolog
profondeur([Piece|R],[Piece|R]) :- arrivee(Piece), !.
```

Le `!` dit : « on est arrivé, n'essaie pas la clause suivante ». Sans lui, Prolog
tenterait aussi de prolonger le tuyau **au-delà** de l'arrivée.

### Ajout 2 — accumuler le chemin

On ne veut pas seulement savoir qu'une solution existe : on veut **le chemin**.
On transporte donc la liste des pièces déjà posées, la plus récente en tête :

```prolog
profondeur([Piece|Tuyau], R) :-
  deplacer(Piece, [X,Y,Dir,Vect]),
  profondeur([[X,Y,Dir,Vect], Piece|Tuyau], R).
```

Chaque appel **empile** la nouvelle pièce. Comme on empile en tête, la liste
finale est à l'envers — d'où le `reverse/2` au moment de rendre :

```prolog
tuyau(Sol) :-
  depart(Piece),
  profondeur([Piece], Los),
  reverse(Los, Sol).
```

### Ajout 3 — la garde anti-cycle

C'est celui qu'on oublie, et sans lui **le programme ne termine pas**.

```prolog
\+ member([X,Y,_,_], [Piece|Tuyau]),
```

« Cette case n'est pas déjà dans le tuyau. » Sans cette ligne, le tuyau
repasserait indéfiniment sur les mêmes cases.

Notez le motif `[X,Y,_,_]` : on compare **les coordonnées seulement**, pas
l'orientation. Une case déjà traversée est interdite quelle que soit la façon
dont on l'a traversée.

### Le prédicat complet

```prolog
profondeur([Piece|R],[Piece|R]) :-
  arrivee(Piece), !.

profondeur([Piece|Tuyau],R) :-
  deplacer(Piece,[X,Y,Dir,Vect]),
  \+ member([X,Y,_,_],[Piece|Tuyau]),
  profondeur([[X,Y,Dir,Vect],Piece|Tuyau],R).
```

Cinq lignes. Vous en aviez deux justes sur trois. **Le squelette + les trois
ajouts**, c'est tout ce qui séparait 0,25 point de 3 ou 4 points.

## Le parcours en largeur

Il change de nature : on ne manipule plus **un** parcours, mais une **liste de
parcours** — la frontière du niveau courant.

### Reconnaître une solution dans la liste

```prolog
etatFinal([[Arrivee|Plateau]|_],[Arrivee|Plateau]) :-
  arrivee(Arrivee).
etatFinal([_|L],R) :-
  etatFinal(L,R).
```

Deux clauses : « le premier parcours de la liste est une solution », ou « la
solution est dans le reste ». C'est le schéma `member/2` de la séance 5, avec un
test.

### Passer au niveau suivant

```prolog
etageSuivant([],[]).
etageSuivant([Tuyau|LT],R) :-
  etatFinal(Tuyau,_), !,
  etageSuivant(LT,R).
etageSuivant([[Piece|Tuyau]|LT],R) :-
  findall([[X,Y,Dir,Vect],Piece|Tuyau],
          ( deplacer(Piece,[X,Y,Dir,Vect]),
            \+ member([X,Y,_,_],[Piece|Tuyau]) ),
          NTuyau),
  etageSuivant(LT,NLT),
  append(NTuyau,NLT,R).
```

**Voilà l'emploi typique de `findall/3`** : pour chaque parcours de la frontière,
on collecte **tous** ses prolongements d'un coup. C'est ce qui produit le niveau
suivant en entier.

### La boucle

```prolog
largeur(P,R) :- etatFinal(P,R).
largeur(LL,R) :-
  etageSuivant(LL,NLL),
  NLL \= [],
  largeur(NLL,R).

tuyauL(Sol) :-
  depart(Piece),
  largeur([[Piece]],Los),
  reverse(Los,Sol).
```

Première clause : y a-t-il une solution dans le niveau courant ? Seconde :
sinon, on construit le niveau suivant et on recommence. Le `NLL \= []` arrête
tout quand il n'y a plus rien à explorer.

Remarquez le double crochet dans `largeur([[Piece]], Los)` : une **liste** de
parcours, dont le seul élément est le parcours `[Piece]`. C'est une source
d'erreur classique.

## Profondeur ou largeur : le tableau de comparaison

| | Profondeur | Largeur |
|---|---|---|
| structure portée | **un** parcours | une **liste** de parcours |
| outil clé | la récursivité | **`findall/3`** |
| trouve toujours ? | non | **oui** |
| chemin le plus court ? | non | **oui** |
| mémoire | la profondeur du chemin | tout le niveau |
| risque | boucle infinie | explosion mémoire |

## La question 13 de mai

*« Donner une solution pour l'exemple Tuyau 2 initiale. »*

Vous aviez répondu : « le parcours le plus adapté serait un chaînage arrière. »
Le correcteur a mis un point d'interrogation.

La question ne demandait pas un avis sur la méthode : elle demandait **la
solution concrète**, la liste des pièces avec leurs orientations.

C'est une erreur de lecture, et elle est fréquente en fin d'épreuve, quand la
fatigue s'installe. La parade tient en un geste : **soulignez le verbe de la
question avant de répondre.** « Donner une solution » — donner, pas discuter.

Les verbes des énoncés de ce module sont peu nombreux, et chacun appelle une
forme de réponse précise :

| Verbe | Ce qu'on attend |
|---|---|
| **Écrire le prédicat** | du code Prolog, avec la signature exacte |
| **Donner la solution** | le résultat concret, chiffré ou listé |
| **Donner le détail des calculs** | le tableau intermédiaire, pas seulement la conclusion |
| **Modéliser** | les variables, les domaines, les contraintes |
| **Calculer les extensions** | la liste des extensions, avec leur justification |
MD,
                'recap' => <<<'MD'
- Un espace d'états : **état initial, actions, test de but**, parfois un coût.
- **Profondeur** : ni complète ni optimale, peu de mémoire.
  **Largeur** : complète et optimale à coûts égaux, mais gourmande.
- Squelette en profondeur = **cas de but + cas récursif**. Vous l'aviez.
  Il fallait y ajouter : **le test d'arrivée avec `!`**, **l'accumulation du
  chemin**, **la garde `\+ member(...)`** — sans laquelle ça ne termine pas.
- On empile en tête, donc on rend avec **`reverse/2`**.
- **La largeur porte une liste de parcours**, et **`findall/3`** construit le
  niveau suivant en un appel.
- Attention au double crochet : `largeur([[Piece]], Sol)`.
- **Soulignez le verbe de la question.** « Donner une solution » demande la
  solution, pas un commentaire sur la méthode.
MD,
            ],

            /* ================= Séance 11 ================= */
            [
                'title' => 'Les heuristiques, A* et l\'élagage',
                'chapitre' => 'Ch3',
                'duree_min' => 30,
                'prerequis' => "La séance 10. On passe des parcours aveugles aux parcours informés.",
                'intro' => <<<'MD'
Profondeur et largeur sont des parcours **aveugles** : ils explorent sans savoir
si un état est prometteur.

Aujourd'hui, on ajoute une information — une **heuristique** — qui estime la
distance restante jusqu'au but. Ça change tout : on explore d'abord ce qui a
l'air proche du but.

On finira par cinq minutes sur les jeux et l'élagage α-β. Ce chapitre n'a jamais
donné lieu à un exercice depuis 2010, mais il tombe au QCM — il y était en mai —
et cinq minutes valent un point.
MD,
                'body' => <<<'MD'
## Ce qu'est une heuristique

Une fonction `h(n)` qui **estime** le coût restant entre l'état `n` et le but.
Estime : elle n'est ni exacte, ni garantie.

L'exemple canonique : pour aller d'une ville à une autre, `h(n)` = la distance à
vol d'oiseau. Elle est facile à calculer et toujours inférieure ou égale à la
distance réelle par la route.

## Les trois fonctions à ne pas confondre

C'est la question de cours la plus fréquente :

| | Définition |
|---|---|
| `g(n)` | le coût **déjà payé** pour arriver en `n` depuis le départ |
| `h(n)` | le coût **estimé** de `n` jusqu'au but |
| `f(n)` | `g(n) + h(n)` — l'estimation du coût **total** du chemin passant par `n` |

## Les trois algorithmes Best-First

Tous les trois prennent le nœud le plus prometteur de la frontière. Ils ne
diffèrent que par la fonction qu'ils minimisent.

| Algorithme | Minimise | Complet ? | Optimal ? |
|---|---|---|---|
| Coût uniforme | `g(n)` | oui | oui |
| **Gourmand** *(greedy)* | `h(n)` | non | **non** |
| **A\*** | `f(n) = g(n) + h(n)` | oui | **oui, si `h` est admissible** |

Le **Gourmand** ne regarde que l'avenir : il fonce vers ce qui paraît proche et
oublie ce qu'il a déjà dépensé. Rapide, souvent mauvais.

**A\*** additionne le passé et l'avenir. C'est le compromis, et c'est
l'algorithme central du chapitre.

## L'admissibilité

> Une heuristique est **admissible** si elle ne **surestime jamais** le coût réel
> restant : `h(n) ≤ h*(n)` pour tout `n`.

Formulation plus simple à retenir : **une heuristique admissible est
optimiste**. Elle peut promettre mieux que la réalité, jamais pire.

C'est la condition qui rend A\* optimal, et il faut savoir dire pourquoi :

> Si `h` surestimait, A\* pourrait attribuer à un nœud situé sur le chemin
> optimal un `f` artificiellement élevé, l'écarter au profit d'un chemin moins
> bon, et rendre celui-ci.

Notez la parenté avec la séance 9 : la borne du Branch and Bound doit être
optimiste, l'heuristique de A\* doit être optimiste. **C'est le même principe.**
On n'a le droit de couper que ce dont on est certain qu'il ne peut pas gagner.

## Dérouler A\* sur une copie

C'est le format d'exercice le plus fréquent. Un tableau, une ligne par
itération :

| Étape | Nœud choisi | g | h | f | Frontière après |
|---|---|---|---|---|---|
| 1 | A (départ) | 0 | 6 | 6 | B(f=7), C(f=8) |
| 2 | B | 2 | 5 | 7 | C(f=8), D(f=9) |
| 3 | C | 3 | 5 | 8 | D(f=9), E(f=8) |

Quatre règles de tenue de copie :

1. **On choisit toujours le plus petit `f` de la frontière.**
2. On écrit `g`, `h` et `f` séparément. Une erreur sur `g` seul reste visible et
   partiellement créditée.
3. En cas d'égalité de `f`, on annonce sa convention — par exemple « à égalité,
   je prends le plus petit `h` » — et on s'y tient.
4. Si un nœud est réatteint par un chemin **moins cher**, on met `g` à jour.

## Profondeur bornée

Une variante de la profondeur, avec une limite de profondeur `L` : au-delà, on
n'explore plus.

- Si `L` est trop petit, on **rate** la solution.
- Si `L` est trop grand, on retrouve les défauts de la profondeur.

L'approfondissement itératif règle le problème : on lance la profondeur bornée
avec L = 1, puis 2, puis 3… On obtient la complétude et l'optimalité de la
largeur, avec la mémoire de la profondeur.

## Les méthodes à base de gradient

Le cours les mentionne, et le principe se résume vite : on part d'une solution
et on se déplace **vers le voisin qui améliore le plus** le critère.

Le défaut est célèbre : on se bloque dans un **optimum local**. On est au sommet
d'une colline, tous les voisins sont plus bas, mais le vrai sommet est ailleurs.

C'est le point commun de toutes les **méthodes incomplètes** (chapitre 9) : elles
sont rapides, elles ne garantissent rien.

## Cinq minutes sur les jeux

Le chapitre 6 n'a **jamais** donné d'exercice depuis 2010. Mais le QCM porte sur
tous les chapitres, et il y avait une question en mai. Voici le strict
nécessaire.

### Minimax

Deux joueurs qui s'opposent. **MAX** cherche à maximiser le score, **MIN** à le
minimiser. On évalue les feuilles, puis on remonte :

- à un nœud **MAX**, on prend le **maximum** des enfants ;
- à un nœud **MIN**, on prend le **minimum**.

### L'élagage α-β

Une amélioration de Minimax qui donne **le même résultat** en explorant moins.

| | Ce que c'est |
|---|---|
| **α** | la valeur **minimale** que MAX est **assuré** d'obtenir |
| **β** | la valeur **maximale** que MIN peut **espérer** obtenir |

Quand `α ≥ β`, on coupe : la suite de la branche ne peut plus rien changer.

La question 6 du QCM de mai portait exactement là-dessus :

> *Dans l'algorithme α-β, que représente le paramètre β ?*
>
> **(b) La valeur maximale que le joueur MIN peut espérer obtenir.**

Un moyen mnémotechnique : **α est du côté de MAX** (α comme *au moins*, ce que
MAX a déjà en poche), **β est du côté de MIN**.

Et là encore, c'est le même principe qu'au Branch and Bound et qu'à A\* : on
coupe ce qui ne peut plus gagner.
MD,
                'recap' => <<<'MD'
- `g(n)` = coût déjà payé · `h(n)` = coût estimé restant · **`f(n) = g + h`**.
- **Gourmand** minimise `h` seul : rapide, non optimal.
  **A\*** minimise `f` : optimal **si `h` est admissible**.
- **Admissible = optimiste** : `h` ne surestime jamais le coût réel restant.
- Dérouler A\* : un tableau, une ligne par itération, `g`/`h`/`f` séparés, on
  prend toujours le plus petit `f`.
- Profondeur bornée + approfondissement itératif = complétude de la largeur avec
  la mémoire de la profondeur.
- Le gradient se bloque dans un **optimum local**.
- Minimax : max aux nœuds MAX, min aux nœuds MIN.
  **α = ce que MAX est assuré d'obtenir · β = ce que MIN peut espérer.**
- **Branch and Bound, A\* et α-β sont le même principe** : ne couper que ce qui
  ne peut plus gagner.
MD,
            ],

            /* ================= Séance 12 ================= */
            [
                'title' => 'La logique des défauts',
                'chapitre' => 'Ch2',
                'duree_min' => 35,
                'prerequis' => "Savoir lire ∀, ⇒, ¬, ∧. Rien de plus. C'est l'exercice annoté « Non on veut des défauts ».",
                'intro' => <<<'MD'
Voici l'exercice de mai que le correcteur a annoté d'un trait :

> **« Non on veut des défauts »** — 0 point.

Quatre phrases commençaient par « **en général** ». Vous avez répondu
`∀x manager(x) ⇒ expérimenté(x)`.

Ce n'était pas une maladresse d'écriture. C'est un contresens sur ce que
l'énoncé demandait, et il annulait les quatre points.

Aujourd'hui, on va voir ce que « en général » veut dire en logique, pourquoi la
logique classique ne peut **pas** l'exprimer, et comment on écrit un défaut au
sens de Reiter. Le chapitre tombe dans **18 sessions sur 24**.
MD,
                'body' => <<<'MD'
## Pourquoi « en général » n'est pas « pour tout »

Commençons par montrer que votre réponse rendait la base **contradictoire**.

Traduisons les quatre phrases en logique classique, comme vous l'avez fait :

```
F1 : ∀x  Manager(x) ⇒ Expérimenté(x)
F2 : ∀x  Expérimenté(x) ⇒ Responsabilité(x)
F3 : ∀x  Stagiaire(x) ⇒ Manager(x)
F4 : ∀x  Stagiaire(x) ⇒ ¬Responsabilité(x)
```

Ajoutons un fait : **Fabrice est stagiaire.**

1. `Stagiaire(fabrice)` — donné.
2. Donc `Manager(fabrice)` — par F3.
3. Donc `Expérimenté(fabrice)` — par F1.
4. Donc `Responsabilité(fabrice)` — par F2.
5. Mais F4 donne `¬Responsabilité(fabrice)`.

**On a démontré une chose et son contraire.** La base est incohérente, et en
logique classique une base incohérente démontre n'importe quoi.

Or les quatre phrases de l'énoncé sont parfaitement raisonnables. Ce n'est pas
le monde qui est contradictoire : c'est la traduction. `∀` est trop fort.

## Ce que dit « en général »

« En général, les managers sont expérimentés » veut dire :

> Si je sais que quelqu'un est manager, et que **rien ne m'empêche** de le croire
> expérimenté, alors je le crois expérimenté — **quitte à changer d'avis** si
> j'apprends le contraire.

Trois ingrédients dans cette phrase :

1. une **condition d'entrée** — être manager ;
2. une **condition de cohérence** — rien ne contredit « expérimenté » ;
3. une **conclusion** — expérimenté.

C'est exactement la structure d'un **défaut** au sens de Reiter.

## La notation

Un défaut s'écrit comme une fraction :

```
     prérequis : justification
     ─────────────────────────
            conséquent
```

- le **prérequis** doit être **démontré** ;
- la **justification** doit être **cohérente** avec ce qu'on croit déjà — on ne
  la démontre pas, on vérifie seulement qu'elle ne se heurte à rien ;
- le **conséquent** est ce qu'on ajoute à nos croyances.

Quand justification et conséquent sont identiques, on parle d'un **défaut
normal**. C'est le cas de toutes les phrases du sujet, et c'est presque toujours
le cas en examen.

## Les quatre phrases de mai

```
        Manager(x) : Expérimenté(x)
F1 :    ───────────────────────────
              Expérimenté(x)

        Expérimenté(x) : Responsabilité(x)
F2 :    ──────────────────────────────────
                Responsabilité(x)

        Stagiaire(x) : Manager(x)
F3 :    ─────────────────────────
              Manager(x)

        Stagiaire(x) : ¬Responsabilité(x)
F4 :    ─────────────────────────────────
              ¬Responsabilité(x)
```

**Sur une copie manuscrite, tracez le trait de fraction.** C'est ce que le
correcteur cherche des yeux. Une flèche `⇒` à la place du trait, et il coche
faux sans lire la suite — c'est précisément ce qui s'est passé.

Si vous manquez de place, la notation en ligne `Manager(x) : Expérimenté(x) /
Expérimenté(x)` est acceptée, mais le trait est plus sûr.

## Une théorie des défauts

Une théorie s'écrit `(D, W)` :

- `W` — les **faits certains** ;
- `D` — les **défauts**.

Ici `W = {Stagiaire(fabrice)}` et `D = {F1, F2, F3, F4}`.

## Une extension

Voici la définition, et c'était la question 5 du QCM :

> Une **extension** est un ensemble cohérent et maximal de croyances qu'on peut
> tirer de la théorie, en appliquant les défauts tant qu'ils restent cohérents.

> *En logique des défauts, comment appelle-t-on l'ensemble cohérent de croyances
> que l'on peut tirer d'une théorie ?* → **(b) Une extension.**

Une théorie peut avoir **une** extension, **plusieurs**, ou **aucune**. Le mot
« plusieurs » est le nerf de l'exercice.

## La méthode de calcul

Quatre temps, à appliquer mécaniquement :

1. Partir des faits certains `W`.
2. Chercher un défaut dont le **prérequis est démontré**.
3. Vérifier que sa **justification ne contredit rien** de ce qu'on a déjà.
4. Ajouter le conséquent. Recommencer jusqu'à ce que plus aucun défaut ne
   s'applique.

**Et si, à une étape, deux défauts s'appliquent et se contredisent, on ouvre deux
branches** : chacune donnera une extension.

## Question 2 — « Fabrice est manager », avec F1 et F2

`W = {Manager(fabrice)}`, `D = {F1, F2}`.

| Étape | Défaut | Prérequis | Justification cohérente ? | On ajoute |
|---|---|---|---|---|
| 1 | F1 | `Manager(fabrice)` ✓ | rien ne contredit `Expérimenté` | `Expérimenté(fabrice)` |
| 2 | F2 | `Expérimenté(fabrice)` ✓ | rien ne contredit `Responsabilité` | `Responsabilité(fabrice)` |

Plus aucun défaut applicable.

> **Une seule extension :**
> E₁ = { Manager(fabrice), Expérimenté(fabrice), Responsabilité(fabrice) }
>
> **Conclusion : Fabrice a de grandes responsabilités.**

Une seule extension, donc la conclusion est ferme.

## Question 3 — « Fabrice est stagiaire », avec les quatre défauts

`W = {Stagiaire(fabrice)}`, `D = {F1, F2, F3, F4}`.

Étapes communes :

| Étape | Défaut | On ajoute |
|---|---|---|
| 1 | F3 | `Manager(fabrice)` |
| 2 | F1 | `Expérimenté(fabrice)` |

Et là, **deux défauts s'appliquent et s'opposent** :

- **F2** conclut `Responsabilité(fabrice)` — son prérequis `Expérimenté` est
  acquis ;
- **F4** conclut `¬Responsabilité(fabrice)` — son prérequis `Stagiaire` est
  acquis.

Chacun est applicable **tant que l'autre n'a pas été appliqué**. On ouvre deux
branches.

> **Extension E₂** (on applique F2) :
> { Stagiaire, Manager, Expérimenté, **Responsabilité** }
>
> **Extension E₃** (on applique F4) :
> { Stagiaire, Manager, Expérimenté, **¬Responsabilité** }

**Deux extensions.** Et il faut savoir dire ce que ça signifie :

> La théorie **ne tranche pas**. Selon l'ordre dans lequel on applique les
> défauts, on croit que Fabrice a des responsabilités, ou qu'il n'en a pas. Ces
> deux ensembles de croyances sont chacun cohérent ; ils sont incompatibles
> entre eux.

Comparez avec la logique classique du début de séance : là-bas, la base
**explosait**. Ici, elle propose deux lectures raisonnables. **C'est exactement à
ça que servent les défauts.**

## Question 4 — on apprend que Fabrice a des responsabilités

On ajoute un fait certain : `W = {Stagiaire(fabrice), Responsabilité(fabrice)}`.

Que devient F4 ? Sa justification est `¬Responsabilité(fabrice)` — et elle
**contredit un fait certain**. La condition de cohérence n'est plus remplie :
**F4 ne peut plus être appliqué**.

| Étape | Défaut | On ajoute |
|---|---|---|
| 1 | F3 | `Manager(fabrice)` |
| 2 | F1 | `Expérimenté(fabrice)` |
| — | F4 | **bloqué** : justification incohérente |

> **Une seule extension :**
> { Stagiaire, Responsabilité, Manager, Expérimenté }

Une information nouvelle a fait **disparaître** une conclusion possible. C'est ce
qu'on appelle un raisonnement **non monotone** : en logique classique, ajouter
une prémisse ne retire jamais un théorème ; ici, si.

Le mot « non monotone » vaut un point s'il apparaît dans votre copie.

## Vos deux erreurs, nommées

**Erreur 1 — l'implication à la place du défaut.** `∀x Manager(x) ⇒
Expérimenté(x)` n'admet aucune exception. « En général » en admet. Le trait de
fraction est là pour dire « sauf indication contraire ».

**Erreur 2 — l'équivalence.** Vous avez écrit :

```
∀x Stagiaire(x) ⟺ Manager(x)
```

L'énoncé dit « les stagiaires sont considérés comme des managers ». Une seule
direction. Le `⟺` affirme en plus que **tout manager est stagiaire**, ce que
personne n'a dit.

Règle générale, valable aussi en SPP : **on n'écrit `⟺` que si l'énoncé dit
explicitement « si et seulement si », ou donne les deux sens séparément.**

## Le signal, en un mot

| L'énoncé écrit | Il faut écrire |
|---|---|
| « en général », « habituellement » | un **défaut** |
| « typiquement », « sauf exception » | un **défaut** |
| « la plupart des », « normalement » | un **défaut** |
| « tout », « tous les », « chaque » | `∀ … ⇒ …` |
| « si et seulement si » | `⟺` |

**« En général » ⟶ trait de fraction.** Si vous ne deviez retenir qu'une ligne
de cette séance, c'est celle-là.

## La logique floue, en trois lignes

Le même chapitre 2 la mentionne, et elle peut tomber au QCM.

Les défauts gèrent l'**exception** : la conclusion est vraie ou fausse, mais
révisable. La logique floue gère le **degré** : « Fabrice est grand » à 0,8.
L'appartenance n'est plus 0 ou 1, mais une valeur dans [0, 1].

- Défauts → incertitude sur **la règle**.
- Flou → imprécision sur **la notion**.
MD,
                'recap' => <<<'MD'
- **« En général » = un défaut, jamais `∀ … ⇒ …`.** Avec des implications, la
  base des quatre phrases devient **incohérente**.
- Un défaut : **prérequis : justification / conséquent**, écrit avec un **trait
  de fraction**. Le correcteur le cherche des yeux.
- Prérequis : à **démontrer**. Justification : seulement **cohérente**.
- Défaut **normal** : justification = conséquent. C'est le cas en examen.
- Une théorie `(D, W)`. Une **extension** = ensemble cohérent et maximal de
  croyances. Il peut y en avoir **plusieurs** — la théorie ne tranche pas.
- Un fait nouveau peut **bloquer** un défaut et supprimer une extension :
  raisonnement **non monotone**.
- **`⟺` seulement si l'énoncé dit « si et seulement si ».**
- Défauts = incertitude sur la règle · logique floue = imprécision sur la notion.
MD,
            ],

            /* ================= Séance 13 ================= */
            [
                'title' => 'Les systèmes experts : chaînage avant, chaînage arrière',
                'chapitre' => 'Ch5',
                'duree_min' => 25,
                'prerequis' => "La séance 12 pour les règles, et la séance 4 pour Prolog.",
                'intro' => <<<'MD'
Chapitre court, et longtemps discret : 6 sessions sur 24. Mais il est revenu
**aux deux dernières**, dont mai 2026 en exercice à part entière. Il vaut donc
une demi-heure.

Bonne nouvelle : vous connaissez déjà l'essentiel sans le savoir. **Prolog est un
système expert**, et son moteur fonctionne en chaînage arrière.

C'était la question 4 du QCM de mai.
MD,
                'body' => <<<'MD'
## Les trois éléments

Un système expert se compose de trois parties, et il faut savoir les nommer :

| Élément | Contenu |
|---|---|
| **La base de faits** | ce qu'on sait du cas courant. Elle **évolue**. |
| **La base de règles** | la connaissance du domaine, `si … alors …`. Elle est **stable**. |
| **Le moteur d'inférence** | le mécanisme qui applique les règles aux faits. |

La séparation est le principe même : **la connaissance est dans les données, pas
dans le programme**. On ajoute une règle sans toucher au moteur.

## Le cycle du moteur d'inférence

Trois phases, qui se répètent :

1. **Sélection** *(ou filtrage)* — quelles règles ont leurs prémisses
   satisfaites ? On obtient l'**ensemble de conflit**.
2. **Résolution de conflit** — laquelle choisir ? Par ordre d'écriture, par
   priorité, par la plus spécifique…
3. **Exécution** *(ou déclenchement)* — on applique la règle et on ajoute sa
   conclusion à la base de faits.

On s'arrête quand plus aucune règle ne s'applique, ou quand le but est atteint.

Le terme **ensemble de conflit** est celui du cours : employez-le.

## Chaînage avant

On part des **faits** et on avance vers les conclusions.

> Tant qu'une règle est applicable, on l'applique et on ajoute sa conclusion.

On dit qu'il est **guidé par les données**. On ne sait pas où l'on va : on
déduit tout ce qu'on peut.

### Un exemple

Règles :
```
R1 : si A et B      alors C
R2 : si C           alors D
R3 : si D et E      alors F
```
Faits : `A, B, E`

| Cycle | Ensemble de conflit | Règle appliquée | Base de faits après |
|---|---|---|---|
| 1 | {R1} | R1 | A, B, E, **C** |
| 2 | {R2} | R2 | A, B, E, C, **D** |
| 3 | {R3} | R3 | A, B, E, C, D, **F** |
| 4 | ∅ | — | arrêt |

**Présentez toujours sous cette forme.** Une colonne « ensemble de conflit », une
colonne « règle appliquée », une colonne « faits après ». C'est ce que le barème
attend.

## Chaînage arrière

On part d'un **but** et on remonte vers les faits.

> Pour démontrer F, je cherche une règle dont F est la conclusion, et je cherche
> à démontrer ses prémisses. Récursivement.

On dit qu'il est **guidé par le but**.

### Le même exemple, but `F`

```
But F
  → R3 : si D et E alors F.  Il faut D et E.
      But D
        → R2 : si C alors D.  Il faut C.
            But C
              → R1 : si A et B alors C.  Il faut A et B.
                  A : fait ✓
                  B : fait ✓
              → C démontré ✓
        → D démontré ✓
      But E
        → fait ✓
  → F démontré ✓
```

**Présentez en arbre indenté.** C'est plus lisible qu'un tableau, et ça montre la
descente récursive.

## Lequel choisir ?

| | Chaînage avant | Chaînage arrière |
|---|---|---|
| Part de | les faits | le but |
| Guidé par | les données | le but |
| Produit | **toutes** les conséquences | la réponse à **une** question |
| Bon quand | peu de faits, beaucoup de conclusions possibles | on a une hypothèse précise à vérifier |
| Exemple | surveillance, alarmes, diagnostic temps réel | diagnostic médical, dépannage |
| Risque | déduire des choses inutiles | explorer des pistes sans rapport |

## La question 4 du QCM

> *Dans un système expert, quel est l'objectif du chaînage arrière ?*
>
> **(c) Partir d'un but ou d'une hypothèse pour vérifier s'il est démontré par
> les faits.**

La réponse (a) — « partir des faits initiaux pour découvrir de nouvelles
conclusions » — est la définition du chaînage **avant**. C'est le distracteur
classique. Lisez les deux avant de cocher.

## Prolog est un moteur à chaînage arrière

Reprenez la séance 2. Quand vous posez `?- grandpere(jean, luc).`, Prolog :

1. prend le but `grandpere(jean, luc)` ;
2. cherche une règle dont la **tête** s'unifie avec ce but ;
3. remplace le but par les buts du **corps** ;
4. recommence, jusqu'à tomber sur des faits.

C'est **exactement** le chaînage arrière. Sa base de règles, ce sont vos clauses ;
sa base de faits, vos faits ; son moteur, l'unification et le retour arrière.

Cette phrase vaut un point si on vous demande de relier les deux chapitres, et
ça arrive.
MD,
                'recap' => <<<'MD'
- Trois éléments : **base de faits** (évolue), **base de règles** (stable),
  **moteur d'inférence**.
- Cycle : **sélection → résolution de conflit → exécution**. L'ensemble des
  règles applicables s'appelle l'**ensemble de conflit**.
- **Chaînage avant** : des faits vers les conclusions, guidé par les **données**.
  Présentation en **tableau** de cycles.
- **Chaînage arrière** : du but vers les faits, guidé par le **but**.
  Présentation en **arbre indenté**.
- QCM : le chaînage arrière **part d'un but pour vérifier s'il est démontré par
  les faits**. Le distracteur est la définition du chaînage avant.
- **Prolog est un moteur à chaînage arrière.**
MD,
            ],

            /* ================= Séance 14 ================= */
            [
                'title' => "ID3 : construire l'arbre de décision au tableau",
                'chapitre' => 'Ch8',
                'duree_min' => 40,
                'prerequis' => "Savoir se servir d'une calculatrice. Aucune notion d'apprentissage n'est supposée.",
                'intro' => <<<'MD'
Dernière séance, et celle qui rapporte le plus par minute investie.

L'apprentissage tombe dans **21 sessions sur 24** — autant que les contraintes —
et l'exercice est presque toujours le même : **construire un arbre de décision
avec ID3**.

C'est un algorithme entièrement **mécanique**. Pas d'astuce, pas de piège de
modélisation : on compte, on calcule, on choisit le meilleur, on recommence.
Avec la méthode, il est difficile de ne pas avoir les points.

En mai, cet exercice valait 4 points et vous en avez eu 0,25. Vous aviez dessiné
un arbre — mais un arbre qui découpait sur **tous** les attributs, sans un seul
calcul. Or l'énoncé demandait explicitement « le détail des calculs ».

On va le refaire en entier, sur les données de mai.
MD,
                'body' => <<<'MD'
## Ce que fait un arbre de décision

On dispose d'exemples décrits par des **attributs**, chacun étiqueté par une
**classe**. On veut un arbre qui, en posant des questions sur les attributs,
retrouve la classe.

À chaque nœud : une question sur un attribut. À chaque feuille : une classe.

Toute la question est : **quel attribut tester en premier ?** ID3 répond : celui
qui sépare le mieux les données.

## L'entropie

L'entropie mesure le **désordre** d'un groupe.

- Un groupe où tout le monde a la même classe : entropie **0**. Parfait.
- Un groupe moitié-moitié : entropie **1**. Le pire.

La formule :

```
H(S) = − Σ  p_i × log₂(p_i)
```

où `p_i` est la proportion de chaque classe.

### Les valeurs à connaître par cœur

Recopiez-les sur votre feuille A4 autorisée. Elles couvrent presque tout, et
elles évitent de manipuler la calculatrice sous pression.

| Répartition | H | | Répartition | H |
|---|---|---|---|---|
| 0 / n (pur) | **0** | | 1/5 – 4/5 | **0,722** |
| 1/2 – 1/2 | **1** | | 2/5 – 3/5 | **0,971** |
| 1/3 – 2/3 | **0,918** | | 1/6 – 5/6 | **0,650** |
| 1/4 – 3/4 | **0,811** | | 2/6 – 4/6 | **0,918** |

Rappel de calculatrice : `log₂(x) = ln(x) / ln(2)`.

## L'entropie d'une découpe

On découpe le groupe selon un attribut. Chaque valeur donne un sous-groupe. On
fait la **moyenne des entropies, pondérée par la taille** :

```
H(S, A) = Σ  (|S_v| / |S|) × H(S_v)
```

**On retient l'attribut dont l'entropie de découpe est la plus PETITE.** Le
moins de désordre restant.

*(Certains présentent la même chose comme le « gain d'information » `H(S) −
H(S,A)`, qu'on **maximise**. C'est strictement équivalent, puisque `H(S)` est le
même pour tous les attributs. Prenez la forme que vous préférez, mais dites
laquelle.)*

## Le critère du majoritaire

Votre énoncé disait : « **table de contingence avec le critère au choix** ».
C'est un cadeau, et il faut savoir le prendre.

Le second critère consiste, pour chaque valeur de l'attribut, à compter la
**classe majoritaire**, puis à sommer. **On retient l'attribut dont le total est
le plus grand** — c'est celui qui classe correctement le plus d'exemples.

**Aucun logarithme.** Rien que des additions.

Mon conseil : **calculez les deux**. Le majoritaire d'abord, en trente secondes,
pour avoir la réponse ; l'entropie ensuite, pour le détail des calculs. Et si le
majoritaire donne une **égalité** entre deux attributs, c'est l'entropie qui
tranche — on en verra un cas plus bas.

## L'exercice de mai, en entier

Douze commentaires. Trois attributs : Longueur (court/moyen/long), Majuscules
(rare/moyenne/fréquente), Récidiviste (oui/non). Classe : Supprimer (oui/non).

| ID | Longueur | Majuscules | Récidiviste | Supprimer |
|---|---|---|---|---|
| 1 | court | rare | non | non |
| 2 | long | fréquente | oui | **oui** |
| 3 | moyen | rare | oui | **oui** |
| 4 | court | moyenne | non | non |
| 5 | long | moyenne | non | non |
| 6 | moyen | fréquente | oui | **oui** |
| 7 | court | fréquente | oui | **oui** |
| 8 | long | rare | oui | non |
| 9 | moyen | rare | non | non |
| 10 | court | rare | oui | non |
| 11 | long | fréquente | non | **oui** |
| 12 | moyen | moyenne | non | non |

Au total : **5 oui, 7 non**.

## Niveau 1 — quel attribut en premier ?

### Longueur

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| court | 1 | 3 | 3 | 0,811 |
| moyen | 2 | 2 | 2 | 1 |
| long | 2 | 2 | 2 | 1 |
| **Bilan** | 5 | 7 | **7** | **0,937** |

Détail du bilan d'entropie :
`(4/12)×0,811 + (4/12)×1 + (4/12)×1 = 0,270 + 0,333 + 0,333 = 0,937`

### Majuscules

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| fréquente | 4 | 0 | 4 | **0** |
| moyenne | 0 | 3 | 3 | **0** |
| rare | 1 | 4 | 4 | 0,722 |
| **Bilan** | 5 | 7 | **11** | **0,301** |

`(4/12)×0 + (3/12)×0 + (5/12)×0,722 = 0,301`

Deux sous-groupes sur trois sont **purs**. C'est excellent.

### Récidiviste

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| oui | 4 | 2 | 4 | 0,918 |
| non | 1 | 5 | 5 | 0,650 |
| **Bilan** | 5 | 7 | **9** | **0,784** |

`(6/12)×0,918 + (6/12)×0,650 = 0,784`

### Le verdict

| Attribut | Majoritaire | Entropie |
|---|---|---|
| Longueur | 7 | 0,937 |
| **Majuscules** | **11** | **0,301** |
| Récidiviste | 9 | 0,784 |

Les deux critères donnent la même réponse. **On retient Majuscules.**

> Note : le corrigé officiel imprime 0,321 pour Majuscules. Le calcul exact
> donne 0,301. Un arrondi de recopie, sans conséquence : Majuscules l'emporte
> largement de toute façon. Si vous trouvez 0,301, vous ne vous êtes pas trompé.

Et c'est justement l'attribut dont vous aviez soupçonné l'énoncé d'être erroné.
Il était non seulement correct, mais **c'était le bon**.

### Où on en est

- **fréquente** → {2, 6, 7, 11} → tous « oui » → **feuille : SUPPRIMER**
- **moyenne** → {4, 5, 12} → tous « non » → **feuille : NE PAS SUPPRIMER**
- **rare** → {1, 3, 8, 9, 10} → mélangé → **on continue**

## Niveau 2 — le sous-groupe « Majuscules = rare »

Cinq exemples : {1, 3, 8, 9, 10}, soit **1 oui, 4 non**.

| ID | Longueur | Récidiviste | Supprimer |
|---|---|---|---|
| 1 | court | non | non |
| 3 | moyen | oui | **oui** |
| 8 | long | oui | non |
| 9 | moyen | non | non |
| 10 | court | oui | non |

L'attribut Majuscules est épuisé : on ne teste plus que Longueur et Récidiviste.

### Longueur

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| court {1,10} | 0 | 2 | 2 | 0 |
| moyen {3,9} | 1 | 1 | 1 | 1 |
| long {8} | 0 | 1 | 1 | 0 |
| **Bilan** | 1 | 4 | **4** | **0,400** |

`(2/5)×0 + (2/5)×1 + (1/5)×0 = 0,400`

### Récidiviste

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| oui {3,8,10} | 1 | 2 | 2 | 0,918 |
| non {1,9} | 0 | 2 | 2 | 0 |
| **Bilan** | 1 | 4 | **4** | **0,551** |

`(3/5)×0,918 + (2/5)×0 = 0,551`

### Le verdict, et pourquoi il est instructif

| Attribut | Majoritaire | Entropie |
|---|---|---|
| **Longueur** | 4 | **0,400** |
| Récidiviste | 4 | 0,551 |

**Le critère du majoritaire donne une égalité : 4 partout.** Il ne tranche pas.
C'est l'entropie qui départage, et elle retient **Longueur**.

Voilà pourquoi il faut savoir faire les deux. Le majoritaire est rapide, mais il
peut rester muet.

### Où on en est

- **court** {1, 10} → deux « non » → **feuille : NE PAS SUPPRIMER**
- **long** {8} → un seul exemple, « non » → **feuille : NE PAS SUPPRIMER**
- **moyen** {3, 9} → un oui, un non → **on continue**

> Note : le corrigé officiel écrit « pour la valeur Long, le sous-groupe {8}
> classé en supprimer ». C'est une inadvertance : l'exemple 8 porte bien
> « non » dans la colonne Supprimer, et la propre table du corrigé indique
> « 0 oui, 1 non ». La feuille est **NE PAS SUPPRIMER**.
>
> Retenez le réflexe qui vous protège : **relisez toujours votre conclusion
> contre votre propre table.** Ici, la table et la phrase se contredisaient.

## Niveau 3 — « rare » et « moyen »

Deux exemples : {3, 9}. Il ne reste que Récidiviste.

| Valeur | Oui | Non | Majoritaire | H |
|---|---|---|---|---|
| oui {3} | 1 | 0 | 1 | 0 |
| non {9} | 0 | 1 | 1 | 0 |
| **Bilan** | 1 | 1 | 2 | **0** |

Entropie nulle : la séparation est parfaite.

- **oui** {3} → **SUPPRIMER**
- **non** {9} → **NE PAS SUPPRIMER**

## L'arbre final

```
                    Majuscules
        ┌───────────────┼───────────────┐
   fréquente         moyenne           rare
        │               │               │
   SUPPRIMER      NE PAS SUPPRIMER   Longueur
   {2,6,7,11}        {4,5,12}     ┌─────┼─────┐
                                court  moyen  long
                                  │      │     │
                            NE PAS SUPPR │  NE PAS SUPPR
                               {1,10}    │     {8}
                                    Récidiviste
                                    ┌────┴────┐
                                   oui       non
                                    │         │
                               SUPPRIMER  NE PAS SUPPR
                                  {3}        {9}
```

**Dessinez l'arbre en entier, et écrivez les identifiants sous chaque feuille.**
C'est ce qui prouve que vous n'avez pas deviné.

## Quand s'arrête-t-on ?

Trois conditions d'arrêt, à savoir citer :

1. tous les exemples du nœud ont la **même classe** → feuille de cette classe ;
2. il n'y a **plus d'attribut** à tester → feuille de la classe **majoritaire** ;
3. le nœud est **vide** → feuille de la classe majoritaire du parent.

## La méthode, en sept temps

1. Compter la répartition globale des classes. *(5 oui, 7 non)*
2. Pour **chaque** attribut, faire un tableau : valeur × classe.
3. Ajouter la colonne **Majoritaire** et la colonne **H**.
4. Calculer le bilan pondéré de chaque attribut.
5. **Retenir la plus petite entropie** (ou le plus grand majoritaire).
6. Découper. Les sous-groupes purs deviennent des feuilles ; les autres
   repartent à l'étape 1, **sans l'attribut déjà utilisé**.
7. Dessiner l'arbre, identifiants compris.

## Les erreurs qui coûtent des points

| Erreur | Conséquence |
|---|---|
| ne pas montrer les tables | *« le détail des calculs »* était demandé — c'est la moitié du barème |
| découper sur tous les attributs | ce n'est pas ID3, c'est une énumération |
| prendre la **plus grande** entropie | on choisit le pire attribut |
| oublier de **pondérer** par la taille | l'attribut choisi est faux |
| réutiliser un attribut déjà testé | sans effet, mais ça fait perdre du temps |
| conclure sans relire sa propre table | voir la note sur l'exemple 8 |

## Cinq minutes sur le Version Space

Le QCM de mai comportait une question dessus. Le strict nécessaire.

Le Version Space est une autre méthode d'apprentissage. Elle maintient **deux
frontières** :

- **S** — les hypothèses les plus **spécifiques** compatibles avec les exemples ;
- **G** — les hypothèses les plus **générales** compatibles.

Toutes les hypothèses valides sont entre les deux. On apprend en resserrant :

| On rencontre | On fait |
|---|---|
| un exemple **positif** | on **généralise S** pour qu'il le couvre |
| un exemple **négatif** | on **spécialise G** pour qu'il l'exclue |

Quand S et G se rejoignent, l'apprentissage est terminé.

> *Dans le cadre du Version Space, quelle action est entreprise lorsqu'un exemple
> négatif est rencontré ?*
>
> **(b) Spécialiser l'ensemble G (Général) pour exclure l'exemple.**

Mnémotechnique : **positif → S grandit · négatif → G rétrécit.** Les deux
frontières se rapprochent l'une de l'autre.

## Le jour de l'épreuve

L'épreuve du 28 août dure **deux heures**, contre trois en mai. Le sujet sera
donc plus court, mais la structure est stable depuis quatorze ans : du Prolog,
des contraintes, un arbre de décision, un exercice de représentation des
connaissances, un QCM.

Une répartition raisonnable sur 120 minutes :

| Temps | Quoi |
|---|---|
| 0 – 5 min | Lire tout le sujet. Repérer les barèmes. **Souligner les verbes.** |
| 5 – 15 min | Le QCM. Il est court et rapporte beaucoup à la minute. |
| 15 – 45 min | **ID3**, s'il y en a un. C'est le plus mécanique et le plus sûr. |
| 45 – 95 min | L'exercice Prolog. **Écrire les signatures de toutes les questions d'abord**, remplir ensuite. |
| 95 – 115 min | Défauts, contraintes, ou ce qui reste. |
| 115 – 120 min | Relire. Vérifier qu'aucune question n'est vide. |

Deux règles qui valent des points, indépendamment de ce que vous savez :

**Aucune question ne doit rester vide.** En mai, cinq questions de l'exercice à
13 points étaient blanches. Même une tête de prédicat avec la bonne signature et
un commentaire rapporte.

**On ne conteste pas l'énoncé.** Si quelque chose paraît faux, on écrit « je
suppose que… » et on traite le sujet tel qu'il est.
MD,
                'recap' => <<<'MD'
- **21 sessions sur 24.** ID3 est l'exercice le plus rentable du module, et il
  est entièrement mécanique.
- `H(S) = −Σ p_i log₂(p_i)`. **0 = pur, 1 = moitié-moitié.** Recopier la table de
  valeurs sur la feuille A4.
- Entropie d'une découpe = **moyenne pondérée par la taille** des sous-groupes.
  **On retient la plus petite.**
- Le **critère du majoritaire** (sommer la classe majoritaire de chaque valeur,
  prendre le plus grand) ne demande **aucun logarithme** — mais il peut donner
  une **égalité**, et c'est alors l'entropie qui tranche.
- On répète sur chaque sous-groupe impur, **sans l'attribut déjà utilisé**.
- Arrêt : classe unique · plus d'attribut → majoritaire · nœud vide → majoritaire
  du parent.
- **Montrer les tables.** « Le détail des calculs » est la moitié du barème.
  **Relire sa conclusion contre sa propre table.**
- Version Space : **positif → généraliser S · négatif → spécialiser G**.
- Le 28 août : 2 heures. QCM d'abord, ID3 ensuite, Prolog en gros bloc.
  **Aucune question vide.**
MD,
            ],

        ];
    }
}