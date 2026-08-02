<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours de MIA, première partie : Prolog.
 *
 * Note obtenue en mai : 3,34/20, soit 5 points sur 30. Le dépouillement du sujet
 * change la façon de voir ce module : sur les 30 points, 18 portaient sur Prolog
 * — l'exercice 1 « Back2Kitchen » pour 5 points et l'exercice 4 « Tuyauterie »
 * pour 13. Prolog n'est pas un chapitre parmi d'autres, c'est l'épreuve.
 *
 * Or ce qui a été rendu n'était pas du Prolog. Le correcteur l'a écrit en un
 * seul mot, en rouge, en face de la question 2 : « Prolog ». Les réponses
 * contenaient des « if / then / else », un « while cpt < 4 », un « return » —
 * du pseudo-code impératif portant le nom d'un langage déclaratif.
 *
 * C'est la même erreur qu'en ALO, où les patrons étaient compris mais rendus en
 * pseudo-code au lieu d'un schéma. L'idée y était ; la forme annulait tout.
 *
 * Ces sept séances ne supposent donc aucune connaissance de Prolog, et
 * consacrent une séance entière — la quatrième — à la seule chose qui manquait :
 * en Prolog, il n'y a ni « if », ni « while », ni « return ».
 */
class CoursMiaSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'MIA')->first();

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
                'title' => "Ce que l'épreuve demande vraiment",
                'chapitre' => 'Ch1',
                'duree_min' => 25,
                'prerequis' => "Aucun. Cette séance ne contient aucune notion technique : elle sert à savoir où l'on va.",
                'intro' => <<<'MD'
Bonjour.

Avant d'ouvrir le cours, on va regarder l'épreuve. Pas le programme officiel :
l'épreuve. Ce que le sujet demande réellement, année après année.

Vous allez voir que ce module, qui a l'air immense — dix chapitres, de Prolog
aux réseaux de neurones — se réduit à **cinq chapitres** à l'examen, et qu'un
seul d'entre eux pèse la moitié des points.

Cette séance ne s'apprend pas. Elle sert à ne pas réviser au hasard.
MD,
                'body' => <<<'MD'
## Le sujet de mai 2026, point par point

Voici comment les 30 points de votre épreuve étaient répartis.

| Exercice | Sujet | Chapitre | Points |
|---|---|---|---|
| 1 | Back2Kitchen — le concours de cuisine | Prolog + contraintes | **5** |
| 2 | Stagiaire manager | Logique des défauts | **4** |
| 3 | ID Modération — arbre de décision | Apprentissage (ID3) | **4** |
| 4 | Tuyauterie — relier le départ à l'arrivée | Prolog + parcours | **13** |
| 5 | QCM, huit questions | Tous chapitres | **4** |

Regardez la dernière colonne. L'exercice 4 vaut **13 points sur 30**. Presque la
moitié du sujet, sur un seul exercice, et cet exercice est du Prolog.

Ajoutez l'exercice 1, qui est aussi du Prolog : **18 points sur 30 étaient du
Prolog.** Soit 60 % de l'épreuve.

## Ce qui a été rendu

Voici votre réponse à la question 3 de l'exercice 4, recopiée telle quelle :

```
chercherflux(DirIn, Vect, DirOut)
    V = flux(Vect, Dir)
    cpt = 0
    while cpt < 4 :
        if V exist
            return V
```

Le correcteur a écrit un seul mot en rouge, en face de cette page :

> **« Prolog »**

Un mot, et il dit tout. Ce n'est pas faux au sens où l'idée serait mauvaise —
l'idée est même à peu près la bonne : parcourir les quatre directions, et
renvoyer celle qui laisse passer le flux. Mais ce n'est pas écrit en Prolog.
C'est écrit en Python, avec un nom de prédicat Prolog posé devant.

Voici la réponse attendue :

```prolog
chercherFlux(In,Vect,Out):-
  findall(Dir,flux(Vect,Dir),LDir),
  select(In,LDir,[Out]).

flux([1,_,_,_],n).
flux([_,1,_,_],e).
flux([_,_,1,_],s).
flux([_,_,_,1],o).
```

Six lignes. **Aucun `while`. Aucun `if`. Aucun `return`.** Les quatre lignes
`flux` remplacent la boucle. Le motif `[1,_,_,_]` remplace le test. Et le
résultat sort par le troisième paramètre.

## Ce que ça veut dire pour vous

C'est une très bonne nouvelle, et je pèse mes mots.

Vous n'avez pas un problème de compréhension de l'IA. Vous aviez compris qu'il
fallait balayer quatre directions. Vous avez un problème de **langage** : vous ne
savez pas encore écrire ce que vous pensez dans la forme que Prolog exige.

Un problème de compréhension demande des mois. Un problème de langage se règle
en quelques séances, parce que Prolog est un petit langage : il tient en trois
notions — les faits, l'unification, les règles.

C'est exactement ce que valent ces sept premières séances.

## Les cinq chapitres qui tombent

Votre enseignant publie une matrice des épreuves depuis 2010. Vingt-quatre
sessions dépouillées. Voici à quelle fréquence chaque chapitre apparaît :

| Chapitre | Sessions | Fréquence |
|---|---|---|
| Ch. 4 — Programmation par contraintes | 21 / 24 | **88 %** |
| Ch. 8 — Apprentissage | 21 / 24 | **88 %** |
| Ch. 0 — Prolog | 20 / 24 | **83 %** |
| Ch. 2 — Représentation des connaissances | 18 / 24 | **75 %** |
| Ch. 3 — Algorithmes classiques | 14 / 24 | 58 % |
| Ch. 5 — Systèmes experts | 6 / 24 | 25 % |
| Ch. 7 — Planification | 2 / 24 | 8 % |
| Ch. 9 — Méthodes incomplètes | 1 / 24 | 4 % |
| Ch. 6 — Jeux | 0 / 24 | **jamais** |
| Ch. 10 — Linguistique | 0 / 24 | **jamais** |

Deux chapitres n'ont jamais été évalués en quatorze ans. Deux autres l'ont été
une ou deux fois. **Cinq chapitres suffisent** : 0, 2, 3, 4 et 8.

Sur les six dernières sessions, les chapitres 4 et 8 sont là **à chaque fois**.

Une nuance, et elle a son importance : ce tableau recense les **exercices**. Or
chaque épreuve comporte aussi un **QCM qui porte sur tous les chapitres** —
celui de mai contenait une question sur l'élagage α-β, chapitre 6. On ne révise
donc pas les chapitres rares en profondeur, mais on leur accorde les cinq
minutes qui suffisent à répondre à une question de définition. C'est prévu :
séance 11 pour les jeux, séance 13 pour les systèmes experts.

## Le plan des quatorze séances

Les sept premières sont consacrées à Prolog, parce que c'est là que se trouvent
les points et parce que c'est là que se trouvait le naufrage.

| | Séance | Chapitre |
|---|---|---|
| 1 | Ce que l'épreuve demande vraiment | — |
| 2 | Des faits, rien que des faits | Ch. 0 |
| 3 | L'unification, le vrai moteur | Ch. 0 |
| 4 | **Ni « if », ni « while », ni « return »** | Ch. 0 |
| 5 | Les listes et la récursivité | Ch. 0 |
| 6 | Le cut, la négation, l'arithmétique | Ch. 0 |
| 7 | Générer et tester | Ch. 0 + 4 |
| 8 | Les contraintes : CLP(FD) | Ch. 4 |
| 9 | Consistance, propagation, Branch and Bound | Ch. 4 |
| 10 | Parcours d'espace d'états : profondeur et largeur | Ch. 3 + 0 |
| 11 | Les heuristiques et A\* | Ch. 3 |
| 12 | La logique des défauts | Ch. 2 |
| 13 | Les systèmes experts : chaînage avant, chaînage arrière | Ch. 5 |
| 14 | ID3 : construire l'arbre au tableau | Ch. 8 |

## Une dernière chose sur le sujet de mai

Sur votre copie, à l'exercice 3, vous avez écrit :

> « Il y a possiblement une erreur sur le sujet dans la colonne des majuscules
> car les données sont censées être uniquement "rare" et "fréquent", or il y a
> "moyen". »

Il n'y avait pas d'erreur. L'énoncé annonçait bien trois valeurs : *rare,
moyenne, fréquente*. Et c'est justement l'attribut **Majuscules** que l'algorithme
retient en premier — il donne la meilleure séparation des données.

Je le relève sans reproche, mais il faut en tirer la règle : **le jour de
l'épreuve, on ne conteste jamais l'énoncé.** Si quelque chose paraît incohérent,
on écrit « je suppose que… » et on traite le sujet tel qu'il est. Douter du sujet
coûte du temps, et le temps est ce qui vous a le plus manqué : deux exercices
étaient inachevés.
MD,
                'recap' => <<<'MD'
- **Prolog pesait 18 points sur 30** en mai. C'est l'épreuve, pas un chapitre.
- Ce qui a été rendu était du pseudo-code impératif. L'idée était bonne, la
  forme annulait tout. **C'est un problème de langage, et ça se règle vite.**
- Cinq chapitres couvrent l'examen : **0 (Prolog), 2 (représentation), 3
  (algorithmes), 4 (contraintes), 8 (apprentissage)**.
- Les chapitres 6 (jeux) et 10 (linguistique) n'ont jamais été évalués depuis
  2010. On ne les révise pas.
- Le jour J : on ne conteste pas l'énoncé. On écrit « je suppose que… » et on
  avance.
MD,
            ],

            /* ================= Séance 2 ================= */
            [
                'title' => 'Des faits, rien que des faits',
                'chapitre' => 'Ch0',
                'duree_min' => 30,
                'prerequis' => "La séance 1. Aucune connaissance de Prolog n'est supposée : on écrit la première ligne ensemble.",
                'intro' => <<<'MD'
Aujourd'hui, on écrit du Prolog pour la première fois.

Une chose à poser avant de commencer, parce que tout le reste en découle :
**Prolog n'est pas un langage d'instructions.**

En Java, en Python, en C, vous écrivez une suite d'ordres : fais ceci, puis
cela, tant que cette condition tient. Vous dites à la machine **comment** faire.

En Prolog, vous écrivez ce qui **est vrai**. Puis vous posez une question. Et
c'est la machine qui cherche comment y répondre.

Ce renversement est tout le langage. On va le voir sur des exemples minuscules.
MD,
                'body' => <<<'MD'
## Un fait

Voici un programme Prolog complet :

```prolog
pere(jean, marie).
```

Une ligne. Elle se lit : « jean est le père de marie ». Elle affirme quelque
chose de vrai.

Trois choses à remarquer, et les trois comptent :

**Le point final est obligatoire.** Pas un point-virgule, pas rien : un point.
Oublier le point est l'erreur numéro un des débutants, et l'interpréteur vous
répondra par un message incompréhensible.

**`pere` s'écrit en minuscule.** C'est le nom du prédicat.

**`jean` et `marie` s'écrivent en minuscule.** Ce sont des **atomes** : des
constantes, des noms propres du programme.

## Minuscule ou majuscule : la règle qui décide de tout

C'est la règle la plus importante des trente premières minutes.

| Ce qu'on écrit | Ce que c'est | Exemple |
|---|---|---|
| Commence par une **minuscule** | un **atome** — une valeur fixe | `jean`, `lundi`, `coude`, `n` |
| Commence par une **majuscule** | une **variable** — un trou à remplir | `X`, `Type`, `Dir`, `Sol` |
| Le souligné seul `_` | une variable **anonyme** — peu importe | `_` |
| Un nombre | un nombre | `4`, `0`, `2.5` |

En Java, `x` et `X` sont deux variables. En Prolog, `x` est une **constante** et
`X` est une **variable**. Ce n'est pas une question de style : c'est la
grammaire du langage.

## Une base de faits

On empile les faits. Voilà une petite base :

```prolog
pere(jean, marie).
pere(jean, paul).
pere(paul, luc).
mere(anne, marie).
mere(anne, paul).
```

Ce programme ne « fait » rien. Il ne s'exécute pas. Il décrit un monde.

## Poser une question

Dans SWI-Prolog, on charge le fichier, et l'invite `?-` apparaît. On pose une
question, qu'on appelle un **but** :

```prolog
?- pere(jean, marie).
true.
```

Prolog cherche dans la base. Il trouve exactement ce fait. Il répond `true`.

```prolog
?- pere(jean, luc).
false.
```

Il ne trouve pas. Il répond `false`. Attention à ce que ça veut dire : pas
« c'est faux », mais **« je ne peux pas le démontrer avec ce que je sais »**.
On appelle ça l'hypothèse du monde clos, et on y reviendra à la séance 6.

## Poser une question avec une variable

C'est là que ça devient intéressant :

```prolog
?- pere(jean, X).
X = marie ;
X = paul.
```

On a demandé : « existe-t-il un X tel que jean soit le père de X ? » Prolog
parcourt la base **de haut en bas**, trouve `pere(jean, marie)`, et répond
`X = marie`.

Puis on tape `;` — qui se lit « ou bien ? » — et il **repart chercher** la
solution suivante. Il trouve `pere(jean, paul)`.

Retenez ce mot : **Prolog donne les réponses une par une**. Il ne renvoie pas une
liste. Il en donne une, et si on en redemande, il repart en arrière chercher la
suivante. On appelle ça le **retour arrière**, ou *backtracking*.

C'est le mécanisme qui remplace vos boucles. On le verra en détail séance 4.

## La question dans l'autre sens

Rien n'oblige à mettre la variable au même endroit :

```prolog
?- pere(X, marie).
X = jean.
```

Le même prédicat sert dans les deux sens. En Java, il vous faudrait deux
méthodes : `getPere(enfant)` et `getEnfants(pere)`. En Prolog, un seul
prédicat les fait toutes les deux, et même une troisième :

```prolog
?- pere(X, Y).
X = jean, Y = marie ;
X = jean, Y = paul ;
X = paul, Y = luc.
```

Cette réversibilité est ce qui rend Prolog utile en IA : on décrit le problème
une fois, et on l'interroge dans le sens qu'on veut.

## Revenons à votre copie

Question 1 de l'exercice 4 : *« Écrire les prédicats `depart/1`, `arrivee/1`,
`tailleX/1`, `tailleY/1` qui permettent de définir les cases de départ,
d'arrivée et les dimensions de la grille. »*

Vous avez écrit :

```
depart(x).
   dep = x

arrivee(x)
   arr = x

tailleX([],0)
tailleX([_|Q],N) :-
   tailleX(Q,N1),
   N is N1+1.
```

Reprenons ligne par ligne, calmement.

**`depart(x).`** — c'est presque bon ! C'est bien la forme d'un fait. Deux
défauts : le `x` minuscule est un atome nommé « x », pas une variable, et
surtout il ne dit pas *où* est le départ. Il fallait mettre la valeur.

**`dep = x`** — cette ligne n'existe pas en Prolog. Pas de point final, pas de
prédicat, et surtout : `=` n'est pas une affectation. On verra à la prochaine
séance ce que `=` veut dire réellement. Ici, elle ne peut rien vouloir dire.

**`tailleX([],0). tailleX([_|Q],N) :- …`** — c'est le prédicat qui calcule la
**longueur d'une liste**. Il est correctement écrit, d'ailleurs : c'est un vrai
morceau de Prolog, le seul de la copie. Mais il ne répond pas à la question.

Regardez l'énoncé : il demande `tailleX/1`. Le `/1` veut dire **un seul
argument**. Votre prédicat en a deux. L'énoncé vous donnait la réponse.

Voici le corrigé :

```prolog
depart([1,2,e,debut]).
arrivee([4,1,o,sortie]).
tailleX(4).
tailleY(3).
```

Quatre faits. Quatre lignes. La grille fait 4 de large et 3 de haut, donc
`tailleX(4).` et `tailleY(3).` C'est tout.

## La leçon, et elle vaut pour toute l'épreuve

Vous avez reconnu le mot « taille » et récité un prédicat appris par cœur. C'est
un réflexe naturel quand on est en difficulté, et il coûte cher : cette question
valait un point, et elle demandait quatre lignes triviales.

**Avant d'écrire quoi que ce soit, lisez l'arité.** `depart/1`, `tailleX/1`,
`chercherflux/3`, `adjacence/6`. Le nombre après la barre vous dit combien
d'arguments. C'est un cadeau : l'énoncé vous donne la signature exacte.

## La notation nom/arité

On écrit toujours un prédicat sous la forme `nom/arité` :

- `pere/2` — le prédicat `pere` à deux arguments
- `tailleX/1` — un argument
- `flux/2` — deux arguments

Deux prédicats de même nom mais d'arité différente sont **deux prédicats
différents**. `taille/1` et `taille/2` n'ont rien à voir l'un avec l'autre.
MD,
                'recap' => <<<'MD'
- Prolog ne décrit pas **comment** faire, mais **ce qui est vrai**. On pose
  ensuite une question, et la machine cherche.
- **Un fait se termine par un point.** Toujours.
- **Minuscule = atome** (une constante : `jean`, `coude`, `n`).
  **Majuscule = variable** (`X`, `Type`, `Sol`). `_` = « peu importe ».
- Une question à variable renvoie les réponses **une par une** ; `;` demande la
  suivante. C'est le retour arrière.
- Un prédicat marche dans les deux sens : `pere(jean,X)` et `pere(X,marie)`.
- **Lisez l'arité dans l'énoncé.** `tailleX/1` demande un fait à un argument,
  pas un calcul de longueur de liste à deux arguments.
MD,
            ],

            /* ================= Séance 3 ================= */
            [
                'title' => "L'unification, le vrai moteur",
                'chapitre' => 'Ch0',
                'duree_min' => 30,
                'prerequis' => "La séance 2 : faits, atomes, variables, questions.",
                'intro' => <<<'MD'
Une seule notion aujourd'hui, mais c'est **la** notion. Tout Prolog est bâti
dessus, et une fois qu'elle est en place, le reste devient facile.

Le signe `=` en Prolog ne veut pas dire « affecter ». Il ne veut pas dire
« tester l'égalité » non plus. Il veut dire quelque chose de plus riche, et de
plus utile :

> **Peut-on rendre ces deux choses identiques ?**

Ça s'appelle l'**unification**. On va la voir sur une dizaine d'exemples, puis
on verra qu'elle fait, à elle seule, le travail de vos tests et de vos
affectations.
MD,
                'body' => <<<'MD'
## Le mécanisme

Prolog vous présente deux termes et pose une question : existe-t-il une façon de
donner des valeurs aux variables pour que les deux deviennent **exactement le
même terme** ?

- Si oui, l'unification **réussit**, et les variables reçoivent ces valeurs.
- Si non, elle **échoue**.

## Les cas, un par un

```prolog
?- X = 3.
X = 3.
```
X n'avait pas de valeur. On lui donne 3, et les deux côtés deviennent `3`.
Réussite.

```prolog
?- 3 = 3.
true.
```
Déjà identiques. Rien à faire. Réussite.

```prolog
?- 3 = 4.
false.
```
Deux constantes différentes. Aucune valeur à donner ne peut les rendre égales.
Échec.

```prolog
?- jean = X.
X = jean.
```
Le sens n'a pas d'importance. L'unification est symétrique.

```prolog
?- f(X, b) = f(a, Y).
X = a,
Y = b.
```
Voilà le cas intéressant. Prolog compare **de l'extérieur vers l'intérieur** :

1. Les deux sont des termes de nom `f` et d'arité 2. Compatible.
2. Premier argument : `X` contre `a` → `X = a`.
3. Deuxième argument : `b` contre `Y` → `Y = b`.

```prolog
?- f(X, b) = g(a, b).
false.
```
Noms différents, `f` et `g`. Échec immédiat.

```prolog
?- f(a, b) = f(a, b, c).
false.
```
Arités différentes, 2 et 3. Échec.

```prolog
?- X = Y.
X = Y.
```
Deux variables libres. Elles sont désormais **liées entre elles** : si l'une
reçoit une valeur plus tard, l'autre la reçoit aussi.

## Une variable ne se réaffecte pas

Point crucial, et c'est là que les habitudes de Java font mal :

```prolog
?- X = 1, X = 2.
false.
```

En Java, `x = 1; x = 2;` marche : la seconde écrase la première. En Prolog,
**non**. Une fois que `X` vaut 1, `X = 2` demande « peut-on rendre 1 et 2
identiques ? » — non. Échec.

Une variable Prolog n'est pas une case mémoire. C'est un **nom pour une valeur
encore inconnue**, et dès qu'elle est connue, elle ne change plus. On dit qu'elle
est **liée**.

C'est pour ça que `dep = x` sur votre copie ne pouvait rien faire : ce n'est pas
une affectation, et écrit tout seul, ça ne veut rien dire.

## La variable anonyme

```prolog
?- f(_, b) = f(a, b).
true.
```

Le `_` s'unifie avec n'importe quoi et **on ne demande pas sa valeur**. Il sert
à dire : « il y a un argument ici, mais il ne m'intéresse pas ».

Attention : deux `_` dans la même clause sont **deux variables différentes**.

```prolog
?- f(_, _) = f(a, b).
true.
```
Réussit. Alors que :
```prolog
?- f(X, X) = f(a, b).
false.
```
Échoue, parce que le **même** `X` ne peut pas valoir à la fois `a` et `b`.

## Les listes s'unifient aussi

Une liste est un terme comme un autre.

```prolog
?- [X, Y, Z] = [1, 2, 3].
X = 1, Y = 2, Z = 3.
```

```prolog
?- [1, _, _, _] = [1, 0, 0, 1].
true.
```

Cette dernière ligne mérite qu'on s'arrête. Le motif `[1,_,_,_]` demande :
« cette liste a-t-elle quatre éléments, dont le premier vaut 1 ? » Et
l'unification répond, toute seule, sans qu'on ait écrit le moindre test.

## L'unification fait le travail du `if`

Vous vous rappelez le vecteur de flux de l'exercice 4 ? Une pièce est décrite
par `[n,e,s,o]`, où un 1 signifie « le flux passe dans cette direction ».
Question : quelles directions laissent passer le flux ?

En Python, vous écririez une boucle sur quatre indices avec un test à chaque
tour. En Prolog, on écrit quatre faits :

```prolog
flux([1,_,_,_],n).
flux([_,1,_,_],e).
flux([_,_,1,_],s).
flux([_,_,_,1],o).
```

Et on interroge :

```prolog
?- flux([1,0,0,1], D).
D = n ;
D = o.
```

Suivons ce qui se passe, parce que c'est tout le langage en cinq lignes :

1. Prolog essaie le premier fait. Il unifie `[1,0,0,1]` avec `[1,_,_,_]`. Le
   premier élément vaut bien 1. Ça marche. Il unifie `D` avec `n`. **Première
   réponse : `D = n`.**
2. On tape `;`. Prolog revient en arrière et essaie le deuxième fait :
   `[1,0,0,1]` contre `[_,1,_,_]`. Le deuxième élément vaut 0, pas 1. **Échec.**
3. Troisième fait : troisième élément, 0 contre 1. **Échec.**
4. Quatrième fait : quatrième élément, 1 contre 1. Ça marche. **Deuxième
   réponse : `D = o`.**

Le test « est-ce que ça vaut 1 ? » est fait par l'unification. Le balayage des
quatre cas est fait par le retour arrière. **Vous n'avez écrit ni test ni
boucle.**

C'est exactement ce que votre `while cpt < 4 : if V exist` essayait de faire à
la main.

## Comment Prolog utilise l'unification à chaque appel

Voici la boucle centrale de l'interpréteur, en trois lignes :

> Pour résoudre un but, Prolog parcourt la base **de haut en bas** et cherche un
> fait — ou une tête de règle — qui **s'unifie** avec ce but. S'il en trouve un,
> il continue. S'il échoue plus loin, il **revient** au dernier choix et essaie
> le suivant.

Tout Prolog est là. Unification pour choisir, retour arrière pour explorer.

## Le prédicat `\=`

L'inverse de `=` s'écrit `\=` : « ces deux termes ne peuvent pas s'unifier ».

```prolog
?- jean \= marie.
true.
?- X \= 3.
false.
```

La seconde surprend. `X` est libre, donc `X = 3` **peut** réussir, donc `X \= 3`
échoue. Retenez : `\=` ne teste pas « est différent », il teste **« ne peut pas
être rendu égal »**.

Dans le corrigé de Back2Kitchen, on trouve `S_Fab \= dessert` — Fabrice n'a pas
fait le dessert. Ça marche parce qu'à ce moment-là `S_Fab` est déjà lié.
MD,
                'recap' => <<<'MD'
- `=` en Prolog signifie **« peut-on rendre ces deux termes identiques ? »**
  Ni affectation, ni test d'égalité : **unification**.
- Elle compare de l'extérieur vers l'intérieur : même nom, même arité, puis
  argument par argument.
- **Une variable liée ne change plus.** `X = 1, X = 2` échoue.
- `_` s'unifie avec tout et ne retient rien. Deux `_` sont deux variables
  différentes ; deux `X` sont le même.
- Un **motif** comme `[1,_,_,_]` fait le travail d'un test, gratuitement.
- Prolog résout un but en cherchant, de haut en bas, un fait ou une tête de
  règle qui s'unifie ; en cas d'échec, il **revient en arrière**.
- `\=` veut dire « ne peut pas être unifié », pas « est différent ».
MD,
            ],

            /* ================= Séance 4 ================= */
            [
                'title' => 'Ni « if », ni « while », ni « return »',
                'chapitre' => 'Ch0',
                'duree_min' => 40,
                'prerequis' => "Les séances 2 et 3. C'est la séance la plus importante des sept : celle qui explique le mot que le correcteur a écrit en rouge.",
                'intro' => <<<'MD'
Voici la séance qui répond au « Prolog » écrit en rouge sur votre copie.

Vous savez maintenant écrire des faits et vous savez ce qu'est l'unification. Il
manque une pièce : les **règles**. Et avec elles, la réponse à la question que
vous vous posez sûrement depuis deux séances :

> *Mais alors, comment on fait un « si » ? Comment on fait une boucle ? Comment
> on renvoie un résultat ?*

Réponse courte : **on ne les fait pas**. Prolog les remplace par autre chose. Et
ces trois remplacements sont tout ce qui vous a manqué en mai.

On y va doucement, et on finit en réécrivant votre copie ligne par ligne.
MD,
                'body' => <<<'MD'
## Une règle

Un fait affirme quelque chose sans condition. Une règle affirme quelque chose
**sous condition** :

```prolog
grandpere(X, Z) :- pere(X, Y), pere(Y, Z).
```

Le symbole `:-` se lit **« si »**. On lit donc la ligne ainsi :

> X est le grand-père de Z **si** X est le père d'un Y **et** Y est le père de Z.

Trois éléments de vocabulaire :

- ce qui est **avant** `:-` s'appelle la **tête** — ce qu'on affirme ;
- ce qui est **après** s'appelle le **corps** — les conditions ;
- la **virgule** dans le corps se lit **« et »**.

Avec la base de la séance 2 :

```prolog
?- grandpere(jean, luc).
true.
```

Prolog a cherché un `Y` tel que `pere(jean,Y)` et `pere(Y,luc)`. Il a trouvé
`Y = paul`. Vous n'avez pas écrit la recherche : elle est faite pour vous.

## Remplacement n° 1 : le « si » devient plusieurs clauses

Voici le point qui change tout.

En Java, pour distinguer deux cas, on écrit un `if/else`. En Prolog, on écrit
**deux clauses de même nom**, et Prolog les essaie dans l'ordre.

Un exemple. « La valeur absolue de X est Y » :

```prolog
abs(X, X) :- X >= 0.
abs(X, Y) :- X < 0, Y is -X.
```

Deux clauses. La première couvre les positifs, la seconde les négatifs. Il n'y
a pas de `else` : il y a une deuxième clause, essayée si la première échoue.

**Plusieurs clauses de même nom, c'est un « ou ».** C'est le remplacement du
`if/else`, et souvent la condition n'a même pas besoin d'être écrite : elle est
dans le motif de la tête.

Reprenez le corrigé de la question 2 :

```prolog
vecteur(coude,[1,0,0,1]).
vecteur(ligne,[0,1,0,1]).
```

Deux faits. **Pas de règle du tout, et pas l'ombre d'un test.** Si on demande
`vecteur(ligne, V)`, l'unification écarte la première ligne (`ligne` ne s'unifie
pas avec `coude`) et retient la seconde.

Ce que vous aviez écrit :

```
vecteur(Type, Vec) :-
    if Type is "ligne" then [0,1,0,1]
    else then [1,0,0,1]
```

L'intention est juste. Mais le `if` était déjà fait par l'unification, et le
résultat n'avait nulle part où sortir. Deux faits suffisaient.

## Remplacement n° 2 : la boucle devient le retour arrière

En Java, pour parcourir quatre possibilités, on écrit `for (int i=0; i<4; i++)`.

En Prolog, on écrit les quatre possibilités, et **Prolog les parcourt tout
seul** en revenant en arrière à chaque échec.

```prolog
flux([1,_,_,_],n).
flux([_,1,_,_],e).
flux([_,_,1,_],s).
flux([_,_,_,1],o).
```

Quatre faits. Votre `while cpt < 4` est là, mais il est **dans le moteur du
langage**, pas dans votre code. Vous n'avez plus de compteur à gérer, plus de
borne à ne pas dépasser, plus d'indice à décaler.

Et quand la boucle porte sur une structure de taille inconnue — une liste, un
arbre, un graphe — on utilise la **récursivité**, qui est le sujet de la
prochaine séance.

## Remplacement n° 3 : le « return » devient un paramètre

En Java, une méthode calcule et **renvoie**. En Prolog, un prédicat ne renvoie
rien : il réussit ou il échoue. Le résultat sort par un **argument
supplémentaire**, rempli par unification.

| Java | Prolog |
|---|---|
| `int longueur(List l)` | `longueur(L, N)` |
| `Vec vecteur(Type t)` | `vecteur(Type, Vec)` |
| `Dir chercherFlux(Dir in, Vec v)` | `chercherFlux(In, Vect, Out)` |

Regardez la troisième ligne : c'est exactement la signature que l'énoncé vous
donnait, `chercherflux(DirIn,Vect,DirOut)`. **Le troisième argument était le
`return`.** L'énoncé vous l'écrivait noir sur blanc.

Par convention, le ou les résultats sont les **derniers** arguments.

## Le tableau de traduction

Affichez-le mentalement pendant l'épreuve.

| Ce que vous voulez écrire | Ce qu'on écrit en Prolog |
|---|---|
| `if cond then A else B` | deux clauses, ou deux motifs différents dans les têtes |
| `while` / `for` sur des cas fixes | plusieurs faits, et le retour arrière |
| `while` / `for` sur une liste | une clause de base + une clause récursive |
| `return v` | un argument de sortie, unifié |
| `x = 5` (affectation) | `X = 5` (unification, une seule fois) |
| `x = x + 1` | `X1 is X + 1` — **une nouvelle variable** |
| `&&` | la virgule `,` |
| `\|\|` | plusieurs clauses, ou le point-virgule `;` |
| `!cond` | `\+ cond` |
| collecter tous les résultats | `findall/3` |

## Le point-virgule dans un corps

Il existe bien un « ou » à l'intérieur d'un corps : le `;`.

```prolog
majeur(X) :- age(X, A), (A >= 18 ; parent_consent(X)).
```

Il est correct, mais **je vous conseille de l'éviter en épreuve**. Deux clauses
séparées sont plus lisibles, plus faciles à corriger, et le correcteur les lit
plus vite. Les corrigés officiels de votre enseignant n'en utilisent
pratiquement jamais.

## Réécrivons votre copie

### Question 3

Ce que vous avez rendu :

```
chercherflux(DirIn, Vect, DirOut)
    V = flux(Vect, Dir)
    cpt = 0
    while cpt < 4 :
        if V exist
            return V
```

Le corrigé :

```prolog
chercherFlux(In,Vect,Out):-
  findall(Dir,flux(Vect,Dir),LDir),
  select(In,LDir,[Out]).
```

Traduisons vos six lignes une à une :

| Votre ligne | Ce qui la remplace |
|---|---|
| `V = flux(Vect, Dir)` | `flux/2`, défini par quatre faits |
| `cpt = 0` puis `while cpt < 4` | le retour arrière sur les quatre faits |
| `if V exist` | le motif `[1,_,_,_]` dans chaque fait |
| `return V` | le troisième argument, `Out` |

Et `findall(Dir, flux(Vect,Dir), LDir)` se lit : « rassemble dans `LDir` tous
les `Dir` tels que `flux(Vect,Dir)` réussit ». Pour `[1,0,0,1]`, ça donne
`LDir = [n,o]`.

Puis `select(In, LDir, [Out])` retire `In` de la liste et exige qu'il ne reste
**qu'un seul** élément, qui devient `Out`. C'est élégant : une pièce a deux
ouvertures ; le flux entre par l'une, il sort forcément par l'autre.

### Question 5

L'énoncé : *« Écrire `deplacementValide(X,Y)` qui vérifie que les coordonnées
sont dans la grille. »* Vous n'avez rien rendu. Le corrigé :

```prolog
deplacementValide(X, Y) :-
  tailleX(MaxX), tailleY(MaxY),
  X >= 1, X =< MaxX,
  Y >= 1, Y =< MaxY.
```

Une règle, quatre comparaisons séparées par des virgules. Rien de plus. Et
remarquez : **pas de `return true`**. Un prédicat qui « vérifie » réussit ou
échoue, c'est sa façon de répondre oui ou non.

Notez aussi `=<` et non `<=`. En Prolog, l'inférieur ou égal s'écrit **`=<`**.
Écrire `<=` est une erreur de syntaxe. C'est bête, ça coûte un point, et ça
s'apprend en une seconde.

### Question 4

L'énoncé donnait `adjacence(Xin, Yin, DirOut, Xout, Yout, DirIn)` — six
arguments. Le corrigé :

```prolog
pieceSuivante([X,Y,Dir,_],Xs,Ys,Out):-
  adjacente(X,Y,Dir,Xs,Ys,Out).

adjacente(X,Y,n,X,Ys,s):- Ys is Y+1.
adjacente(X,Y,e,Xs,Y,o):- Xs is X+1.
adjacente(X,Y,s,X,Ys,n):- Ys is Y-1.
adjacente(X,Y,o,Xs,Y,e):- Xs is X-1.
```

Quatre clauses, une par direction. Et regardez comme les têtes travaillent :

- la troisième position contient l'atome `n`, `e`, `s` ou `o` — **c'est le `if`** ;
- quand on sort par le nord, on entre par le sud dans la case du dessus : le
  `s` est écrit directement dans la tête ;
- quand on va vers le nord, `X` ne change pas : on écrit **le même `X`** aux
  positions 1 et 4. L'unification fait la copie.

Sur la première ligne, `[X,Y,Dir,_]` décompose la pièce dans la tête même du
prédicat. Pas de « getter », pas d'accès par indice. Le motif extrait les
champs.

## Comment écrire un prédicat, le jour de l'épreuve

Une méthode en cinq temps, à appliquer mécaniquement :

1. **Recopier la signature de l'énoncé.** `orienter(X, Y, DirIn, NVect, DirOut)`.
   Cinq arguments, ni plus ni moins.
2. **Décider qui entre et qui sort.** Ici : `X, Y, DirIn` entrent ; `NVect,
   DirOut` sortent.
3. **Combien de cas ?** Un cas = une clause. Ici deux : la case d'arrivée, et
   le cas général.
4. **Écrire le corps comme une phrase française**, en séparant par des
   virgules : « la pièce en X,Y est de type Type, **et** le vecteur par défaut
   de Type est Vect, **et** NVect est une rotation de Vect, **et** le flux
   entrant DirIn sort par DirOut. »
5. **Traduire chaque morceau en un appel de prédicat.**

```prolog
orienter(X,Y,Dir,Vect,Dir):-
  arrivee([X,Y,Dir,Vect]).

orienter(X,Y,Dir,NVect,Out):-
  piece(X,Y,Type),
  vecteur(Type,Vect),
  rotation(Type,Vect,NVect),
  chercherFlux(Dir,NVect,Out).
```

L'étape 4 est celle qui débloque. **Écrivez d'abord la phrase en français.** Une
règle Prolog est une phrase où « et » s'écrit `,` et « si » s'écrit `:-`.

## Même si vous ne savez pas finir, écrivez la signature

Un dernier conseil, celui qui aurait rapporté le plus de points en mai.

Sur treize questions à l'exercice 4, vous en avez traité six, dont plusieurs à
moitié. Les questions 5, 6, 7, 8, 11 sont restées vides.

La question 5 — `deplacementValide` — est celle que vous venez de lire : quatre
comparaisons. Vous savez la faire maintenant, mais vous auriez pu en écrire la
moitié en mai.

**Une clause à moitié juste rapporte des points. Une page blanche n'en rapporte
aucun.** Si le corps vous échappe, écrivez au moins la tête avec la bonne
signature et un commentaire disant ce qu'elle doit faire. C'est du barème
gagné, et ça se fait en trente secondes.
MD,
                'recap' => <<<'MD'
- Une règle : `tête :- corps.` — `:-` se lit **« si »**, la virgule se lit
  **« et »**.
- **Le `if/else` devient plusieurs clauses** de même nom, souvent distinguées par
  le seul motif de la tête.
- **La boucle devient le retour arrière** (cas fixes) ou la récursivité
  (structures).
- **Le `return` devient un argument de sortie**, en dernière position. L'énoncé
  vous le donne : dans `chercherflux/3`, le troisième argument *est* le résultat.
- `x = x + 1` s'écrit `X1 is X + 1` : une **nouvelle** variable.
- L'inférieur ou égal s'écrit **`=<`**, jamais `<=`.
- Pour écrire un prédicat : recopier la signature, repérer entrées et sorties,
  compter les cas, **écrire la phrase en français**, puis la traduire.
- Une clause à moitié juste rapporte. **Une page blanche, jamais.**
MD,
            ],

            /* ================= Séance 5 ================= */
            [
                'title' => 'Les listes et la récursivité',
                'chapitre' => 'Ch0',
                'duree_min' => 35,
                'prerequis' => "Les séances 3 et 4 : unification, règles, clauses multiples.",
                'intro' => <<<'MD'
Les listes sont partout en Prolog : un parcours est une liste de pièces, une
solution est une liste d'affectations, une base de données est une liste de
faits.

Et comme Prolog n'a pas de boucle, la seule façon de traverser une liste est la
**récursivité**. Ça fait peur au début, mais le schéma est toujours le même — un
cas de base, un cas récursif — et une fois qu'on l'a vu trois fois, on l'écrit
sans réfléchir.

On finira par les prédicats de bibliothèque que les corrigés utilisent
constamment. Les connaître fait gagner beaucoup de temps le jour J.
MD,
                'body' => <<<'MD'
## Écrire une liste

```prolog
[]              % la liste vide
[a]             % un élément
[a, b, c]       % trois éléments
[1, 0, 0, 1]    % un vecteur de flux
[a, [b, c], d]  % une liste dont le deuxième élément est une liste
```

## La barre verticale : tête et queue

C'est la notation la plus importante du chapitre :

```prolog
[T | Q]
```

`T` est le **premier élément** (la tête), `Q` est **tout le reste** (la queue),
qui est elle-même une liste.

Vérifions par unification :

```prolog
?- [T|Q] = [a, b, c].
T = a,
Q = [b, c].
```

```prolog
?- [T|Q] = [a].
T = a,
Q = [].
```

```prolog
?- [T|Q] = [].
false.
```

Cette dernière ligne compte : **la liste vide ne se décompose pas.** Elle n'a
pas de tête. C'est ce qui fait s'arrêter les récursions.

On peut détacher plusieurs éléments :

```prolog
?- [A, B | R] = [1, 2, 3, 4].
A = 1, B = 2, R = [3, 4].
```

## Le schéma récursif

Toute traversée de liste s'écrit avec **deux clauses** :

1. **le cas de base** — que vaut le résultat pour la liste vide ?
2. **le cas récursif** — comment passer de `[T|Q]` au résultat, sachant qu'on
   sait déjà traiter `Q` ?

Voici la longueur d'une liste, celle que vous aviez écrite en mai :

```prolog
longueur([], 0).
longueur([_|Q], N) :- longueur(Q, N1), N is N1 + 1.
```

Lisons la deuxième clause : « la longueur de `[_|Q]` vaut `N` **si** la longueur
de `Q` vaut `N1` **et** `N` vaut `N1 + 1` ».

Deux détails à noter :

- On écrit `_` pour la tête : sa **valeur** ne sert pas, seul son **existence**
  compte.
- On utilise `N1`, une nouvelle variable. On ne peut pas écrire `N is N + 1` :
  une variable liée ne change plus (séance 3).

Suivons `longueur([a,b], N)` :

```
longueur([a,b], N)
  → longueur([b], N1),  N is N1+1
      → longueur([], N2),  N1 is N2+1
          → N2 = 0                (cas de base)
      → N1 is 0+1  →  N1 = 1
  → N is 1+1  →  N = 2
```

On descend jusqu'à la liste vide, puis on **remonte** en calculant. Le calcul se
fait à la remontée.

## Deux ou trois autres, pour la main

L'appartenance :

```prolog
appartient(X, [X|_]).
appartient(X, [_|Q]) :- appartient(X, Q).
```

Première clause : X est dans la liste **si** c'est la tête. Remarquez qu'il n'y
a pas de corps — le test est fait par l'unification du **même `X`** aux deux
endroits. Deuxième clause : sinon, il est dans la queue.

La somme :

```prolog
somme([], 0).
somme([T|Q], S) :- somme(Q, S1), S is S1 + T.
```

Le dernier élément :

```prolog
dernier([X], X).
dernier([_|Q], X) :- dernier(Q, X).
```

Le cas de base est ici `[X]` — une liste à **un** élément — et non `[]`. Le cas
de base dépend du problème : on se demande toujours « quelle est la plus petite
liste que je sais traiter directement ? »

## Les prédicats de bibliothèque à connaître

Les corrigés de votre enseignant les utilisent sans arrêt. **Vous avez le droit
de vous en servir**, et c'est même attendu.

| Prédicat | Ce qu'il fait | Exemple |
|---|---|---|
| `length(L, N)` | longueur | `length([a,b], N)` → `N = 2` |
| `member(X, L)` | X est dans L | `member(b, [a,b])` → `true` |
| `append(L1, L2, L3)` | concaténation | `append([a],[b],L)` → `L = [a,b]` |
| `nth1(I, L, X)` | le I-ième, **à partir de 1** | `nth1(2,[a,b,c],X)` → `X = b` |
| `reverse(L, R)` | inversion | `reverse([a,b],R)` → `R = [b,a]` |
| `select(X, L, R)` | retire X de L, reste R | `select(b,[a,b,c],R)` → `R = [a,c]` |
| `findall(M, But, L)` | **collecte toutes** les solutions | voir plus bas |
| `sort(L, T)` | trie et dédoublonne | `sort([b,a,b],T)` → `T = [a,b]` |

Deux d'entre eux méritent un développement.

### `select/3`, le générateur de permutations

`select(X, L, R)` retire un élément de `L`. Mais **en retour arrière, il les
retire tous, un par un** :

```prolog
?- select(X, [a,b,c], R).
X = a, R = [b,c] ;
X = b, R = [a,c] ;
X = c, R = [a,b].
```

C'est ce qui en fait un générateur. Le corrigé de Back2Kitchen s'en sert pour
tirer une permutation sans répétition :

```prolog
appartient([], _).
appartient([X|L], Dom) :- select(X, Dom, Dom1), appartient(L, Dom1).
```

Lisons-le : « pour affecter la liste `[X|L]` à partir du domaine `Dom`, on prend
un `X` dans `Dom`, **et on continue avec le domaine privé de X** ». Le
domaine rétrécit à chaque appel, donc deux personnes ne peuvent pas recevoir le
même classement. Quatre lignes, et la contrainte « tous différents » est
gratuite.

Le nom est trompeur — il ressemble à `member` — mais ce prédicat **affecte une
permutation**. Il sera au cœur de la séance 7.

### `findall/3`, la collecte

```prolog
findall(Modele, But, Liste)
```

Se lit : « rassemble dans `Liste` toutes les valeurs de `Modele` pour lesquelles
`But` réussit ».

```prolog
?- findall(X, pere(jean, X), L).
L = [marie, paul].
```

`findall` est le pont entre le monde des solutions-une-par-une et le monde des
listes. Chaque fois qu'un énoncé dit « **tous** les… » ou « la **liste** des… »,
c'est `findall`.

Il a une propriété précieuse : **si aucune solution n'existe, il réussit avec la
liste vide.** Il n'échoue jamais.

## Le piège de mai, une dernière fois

Question 1 : `tailleX/1`. Vous avez écrit le prédicat de longueur de liste, à
deux arguments. La réponse était `tailleX(4).`

Vous savez maintenant écrire les deux. Ce qui doit changer, c'est le réflexe de
lecture :

> **Avant d'écrire, comptez les arguments demandés.**

`tailleX/1` → un argument → ce n'est pas une fonction de calcul, c'est une
constante. `chercherflux/3` → trois → deux entrées, une sortie.
`adjacence/6` → six → l'énoncé décrit lesquels.

Trente secondes de lecture d'arité valaient un point en mai.
MD,
                'recap' => <<<'MD'
- `[T|Q]` : `T` est la tête, `Q` est la queue. **`[]` ne se décompose pas.**
- Toute traversée = **deux clauses** : un cas de base, un cas récursif.
- On descend jusqu'au cas de base, **le calcul se fait à la remontée**.
- Jamais `N is N + 1` : une variable liée ne change plus, il faut `N1`.
- À connaître par cœur : `length/2`, `member/2`, `append/3`, `nth1/3`,
  `reverse/2`, `select/3`, `findall/3`.
- **`select/3` génère les permutations** en retour arrière — c'est le moteur de
  « Générer et Tester ».
- **`findall/3`** collecte toutes les solutions dans une liste, et réussit
  toujours, même vide. Dès qu'un énoncé dit « tous les », c'est lui.
- Comptez l'arité avant d'écrire.
MD,
            ],

            /* ================= Séance 6 ================= */
            [
                'title' => "Le cut, la négation et l'arithmétique",
                'chapitre' => 'Ch0',
                'duree_min' => 30,
                'prerequis' => "Les séances 3 à 5. Trois questions du QCM de mai portaient exactement sur cette séance.",
                'intro' => <<<'MD'
Trois outils aujourd'hui, et un motif pour s'y mettre : **trois des huit
questions du QCM de mai portaient sur le sujet de cette séance**. Trois points
et demi sur trente, pour une demi-heure de travail.

On verra `is` — parce que Prolog ne calcule pas tout seul —, puis la négation
`\+`, puis le fameux **cut** `!`, qui est l'outil le plus mal compris du langage
et celui sur lequel votre enseignant interroge presque chaque année.

À la fin, on refait les trois questions du QCM ensemble.
MD,
                'body' => <<<'MD'
## `is` : le seul endroit où Prolog calcule

Première surprise :

```prolog
?- X = 2 + 3.
X = 2+3.
```

Prolog **n'a pas calculé**. Il a unifié `X` avec le terme `2+3`, comme il aurait
unifié `X` avec `f(2,3)`. Pour Prolog, `+` est juste un nom de fonction à deux
arguments.

Pour déclencher le calcul, il faut `is` :

```prolog
?- X is 2 + 3.
X = 5.
```

`is` évalue **ce qui est à sa droite** et unifie le résultat avec ce qui est à
gauche.

La règle d'or : **tout ce qui est à droite de `is` doit être entièrement connu**.

```prolog
?- X is Y + 1.
ERROR: Arguments are not sufficiently instantiated
```

`Y` n'a pas de valeur, le calcul est impossible. C'est l'erreur d'exécution la
plus fréquente en Prolog, et elle explique pourquoi, dans `longueur/2`, l'appel
récursif vient **avant** le `is` : il faut d'abord que `N1` soit connu.

```prolog
longueur([_|Q], N) :- longueur(Q, N1), N is N1 + 1.
                      %  d'abord ceci      ensuite cela
```

## Les comparaisons

| Prolog | Sens | Piège |
|---|---|---|
| `X < Y` | strictement inférieur | |
| `X > Y` | strictement supérieur | |
| `X =< Y` | inférieur ou égal | **`=<`, pas `<=`** |
| `X >= Y` | supérieur ou égal | |
| `X =:= Y` | égalité **des valeurs** | `2+3 =:= 5` → vrai |
| `X =\= Y` | différence des valeurs | |

Ces opérateurs **évaluent** leurs deux côtés, comme `is`. Donc les deux côtés
doivent être connus.

Ne confondez pas les trois « égalités » :

```prolog
?- 2 + 3 = 5.      false.   % unification : les TERMES diffèrent
?- 2 + 3 =:= 5.    true.    % comparaison : les VALEURS sont égales
?- X is 2 + 3.     X = 5.   % calcul et affectation du résultat
```

## La négation par échec : `\+`

```prolog
\+ But
```

Se lit « **on ne peut pas démontrer** `But` ». Elle réussit si le but échoue,
et échoue si le but réussit.

```prolog
?- \+ pere(jean, luc).
true.
```

Le terme officiel est **négation par échec**, et l'hypothèse sous-jacente
s'appelle l'**hypothèse du monde clos** : tout ce qui n'est pas démontrable est
considéré comme faux. Ce n'est pas la négation de la logique classique.

Un piège à connaître : **`\+` ne lie jamais de variable.**

```prolog
?- \+ pere(X, marie).
false.
```

Prolog trouve `X = jean`, donc le but réussit, donc `\+` échoue — et `X` reste
sans valeur. On n'utilise `\+` que sur des buts **déjà instanciés**.

L'usage typique, qu'on retrouve dans la Tuyauterie :

```prolog
\+ member([X,Y,_,_], [Piece|Tuyau])
```

« cette case n'est pas déjà dans le tuyau ». C'est le **test anti-cycle** du
parcours en profondeur — sans lui, le programme tourne en rond indéfiniment. On
y reviendra séance 10.

## Le cut : `!`

Voilà l'outil réputé difficile. Il tient pourtant en une phrase :

> **`!` réussit toujours, et supprime les points de choix créés depuis l'entrée
> dans la clause.**

Traduisons. Quand Prolog franchit un `!`, il s'engage :

1. il ne réessaiera **pas** les autres clauses du même prédicat ;
2. il ne réessaiera **pas** les buts placés **avant** le `!` dans cette clause.

En revanche, les buts placés **après** le `!` gardent leurs points de choix.
Cette dernière phrase est le cœur du sujet, et c'est elle qu'on teste au QCM.

### Un exemple

```prolog
t(1).
t(2).
t(3).
```

Sans cut :

```prolog
?- t(X).
X = 1 ; X = 2 ; X = 3.
```

Avec cut :

```prolog
?- t(X), !.
X = 1.
```

Le `!` a supprimé les points de choix de `t(X)`. Une seule réponse.

### À quoi il sert

À dire « ce cas est réglé, ne cherche pas ailleurs ». Dans la Tuyauterie :

```prolog
profondeur([Piece|R],[Piece|R]) :- arrivee(Piece), !.
```

« Si on est arrivé, c'est fini — n'essaie pas la clause suivante. »

Il sert aussi à rendre un prédicat déterministe, et à écrire des `if/else`
exclusifs. Mais **utilisé à mauvais escient, il supprime des solutions
correctes**, et c'est là-dessus qu'on vous interroge.

## Les trois questions du QCM de mai

On les refait ensemble. Base : `t(1). t(2). t(3).`

### Question 1

```prolog
m1(X,Y) :- t(X), t(Y), S is X+Y, S < 5.
```

Pas de cut. Toutes les paires sont essayées, on garde celles dont la somme est
strictement inférieure à 5 :

| X\\Y | 1 | 2 | 3 |
|---|---|---|---|
| **1** | 2 ✓ | 3 ✓ | 4 ✓ |
| **2** | 3 ✓ | 4 ✓ | 5 ✗ |
| **3** | 4 ✓ | 5 ✗ | 6 ✗ |

Six solutions. Les propositions allaient de 0 à 3, puis « Plus ».

> **Réponse : (e) Plus.**

### Question 2

```prolog
m2(X,Y) :- t(X), !, t(Y), S is X+Y, S < 5.
```

Le `!` est **juste après `t(X)`**. Il fige donc `X = 1` — la première solution —
et interdit d'y revenir.

Mais `t(Y)` est **après** le cut : il garde ses points de choix. Donc `Y`
parcourt 1, 2, 3, et les sommes valent 2, 3, 4 — toutes inférieures à 5.

> **Réponse : (d) 3.**

### Question 3

```prolog
m3(X,Y) :- t(X), t(Y), !, S is X+Y, S < 5.
```

Le `!` est **après les deux**. Il fige `X = 1` et `Y = 1`. Somme 2, inférieure à
5 : la clause réussit. Et il n'y a plus rien à réessayer.

> **Réponse : (b) 1.**

### Ce que ces trois questions testent

Une seule chose : **la position du cut dans le corps**. Ce qui est avant est
figé, ce qui est après reste libre.

Une méthode sûre le jour de l'épreuve : écrivez le corps à l'horizontale et
tracez un trait vertical au niveau du `!`. À gauche, une seule solution. À
droite, toutes.

```
m2 :   t(X)  │  t(Y),  S is X+Y,  S < 5
            !
        figé  │  libre
```

Trois questions, trois points et demi. Elles se refont en deux minutes le jour J
si le trait vertical est un réflexe.
MD,
                'recap' => <<<'MD'
- Prolog ne calcule que via **`is`**, et tout ce qui est à droite doit être
  connu. Sinon : *Arguments are not sufficiently instantiated*.
- `=` unifie des **termes**, `=:=` compare des **valeurs**, `is` **calcule**.
  `2+3 = 5` est faux ; `2+3 =:= 5` est vrai.
- Inférieur ou égal : **`=<`**, jamais `<=`.
- `\+ But` = « non démontrable ». **Elle ne lie aucune variable** : ne l'employer
  que sur des buts instanciés. Usage clé : le test anti-cycle
  `\+ member(...)`.
- **`!` fige tout ce qui est avant lui dans la clause, et laisse libre tout ce
  qui est après.** C'est la seule chose à savoir pour le QCM.
- Méthode : trait vertical au niveau du `!` — à gauche une solution, à droite
  toutes.
MD,
            ],

            /* ================= Séance 7 ================= */
            [
                'title' => 'Générer et tester',
                'chapitre' => 'Ch0',
                'duree_min' => 35,
                'prerequis' => "Les séances 2 à 6, et surtout select/3 (séance 5). On refait l'exercice 1 de mai en entier.",
                'intro' => <<<'MD'
Vous avez maintenant tout Prolog en main. On va s'en servir pour résoudre un
vrai problème d'examen, du début à la fin.

La méthode s'appelle **Générer et Tester**. C'est la plus simple des techniques
de résolution, et c'est celle que l'exercice 1 de mai demandait explicitement.

L'idée tient en une phrase : **on construit une solution complète au hasard, puis
on vérifie qu'elle respecte les contraintes** — et si elle ne les respecte pas,
le retour arrière en essaie une autre, tout seul.

À la fin de la séance, vous saurez traiter une énigme logique complète. C'est un
type d'exercice qui revient régulièrement, et il rapporte gros parce qu'il est
mécanique.
MD,
                'body' => <<<'MD'
## Le principe

Deux phases, dans cet ordre :

1. **Générer** — affecter une valeur à chaque variable, sans se soucier de rien.
2. **Tester** — vérifier toutes les contraintes.

Si un test échoue, Prolog **revient en arrière** dans la phase de génération et
essaie l'affectation suivante. La boucle « essayer toutes les combinaisons » est
entièrement gratuite.

C'est la question 7 du QCM de mai :

> *Quelle est la principale différence entre « Générer et Tester » (GT) et le
> « Backtracking » (BT) en programmation par contraintes ?*
>
> **(c) GT teste les contraintes uniquement sur une solution complète, alors que
> BT les teste au fur et à mesure de l'assignation.**

Retenez cette phrase telle quelle. C'est la définition, et c'est aussi la raison
pour laquelle GT est lent : il ne s'aperçoit qu'une piste est mauvaise qu'une
fois arrivé au bout.

## Le générateur de permutations

Reprenons le prédicat de la séance 5 :

```prolog
:-use_module(library(lists)).

appartient([], _).
appartient([X|L], Dom) :- select(X, Dom, Dom1), appartient(L, Dom1).
```

Il affecte à chaque variable de la première liste une valeur du domaine, **sans
répétition** — parce que le domaine rétrécit à chaque étape.

```prolog
?- appartient([A,B], [1,2,3]).
A = 1, B = 2 ;
A = 1, B = 3 ;
A = 2, B = 1 ;
...
```

Six solutions pour deux variables sur trois valeurs. C'est le générateur.

La première ligne, `:-use_module(library(lists)).`, charge la bibliothèque.
**Écrivez-la sur votre copie** : les corrigés le font, et ça montre que vous
savez d'où viennent `select/3` et `nth1/3`.

## L'exercice 1 de mai, en entier

> Quatre enseignants — Antoine, Fabrice, Louis et Xavier — ont participé à un
> concours de cuisine sur 4 jours. Retrouver pour chacun son classement (1er à
> 4e), sa spécialité (Entrée, Plat, Dessert, Pain) et son jour de passage
> (Lundi à Jeudi).

### Étape 1 — choisir la structure

Une ligne par personne, avec ses trois inconnues :

```prolog
L = [ [antoine, C_Ant, S_Ant, J_Ant],
      [fabrice, C_Fab, S_Fab, J_Fab],
      [louis,   C_Lou, S_Lou, J_Lou],
      [xavier,  C_Xav, S_Xav, J_Xav] ],
```

Les noms sont des atomes — ils sont connus. Les douze autres sont des variables.

### Étape 2 — générer

Trois domaines, trois appels :

```prolog
Classements = [1, 2, 3, 4],
appartient([C_Ant, C_Fab, C_Lou, C_Xav], Classements),

Specialites = [entree, plat, dessert, pain],
appartient([S_Ant, S_Fab, S_Lou, S_Xav], Specialites),

Jours = [lundi, mardi, mercredi, jeudi],
appartient([J_Ant, J_Fab, J_Lou, J_Xav], Jours),
```

Trois permutations indépendantes : 4! × 4! × 4! = **13 824 combinaisons**. On
les essaiera toutes s'il le faut ; c'est le prix de la simplicité.

### Étape 3 — tester, indice par indice

C'est ici que se gagnent les points. **Chaque phrase de l'énoncé devient une ou
deux lignes.** Écrivez le numéro de l'indice en commentaire : le correcteur
suit, et vous aussi.

> **1. Fabrice est passé le Lundi. Il n'a pas cuisiné le dessert et n'a pas
> gagné le concours.**

```prolog
% Phrase 1
J_Fab = lundi,
S_Fab \= dessert,
C_Fab \= 1,
```

Trois affirmations, trois lignes. « N'a pas gagné » veut dire « n'est pas
premier », donc `C_Fab \= 1`.

> **2. Louis a obtenu un meilleur classement que Fabrice grâce à sa recette de
> pain.**

```prolog
% Phrase 2
C_Lou < C_Fab,
S_Lou = pain,
```

Attention au piège : **« meilleur classement » veut dire nombre plus petit.**
1er est meilleur que 3e. C'est le genre de détail qui fait perdre l'exercice
entier, parce qu'une inégalité inversée rend le système insoluble et le
programme répond `false` sans rien dire de plus.

> **3. Antoine a cuisiné plus tard dans la semaine que Xavier, mais avant Louis.
> Antoine n'a pas fini sur le podium.**

Ici les jours sont des atomes : on ne peut pas écrire `J_Xav < J_Ant`. Il faut
comparer leurs **positions** dans la liste des jours. D'où le prédicat auxiliaire
du corrigé :

```prolog
avant(JourX, JourY, Ordre) :-
  nth1(IdX, Ordre, JourX),
  nth1(IdY, Ordre, JourY),
  IdX < IdY.
```

`nth1/3` donne l'indice d'un élément. On compare les indices.

```prolog
% Phrase 3
avant(J_Xav, J_Ant, Jours),
avant(J_Ant, J_Lou, Jours),
C_Ant = 4,
```

« Pas sur le podium » sur quatre participants ne laisse qu'une place : la
quatrième. Écrire directement `C_Ant = 4` est plus fort que `C_Ant \= 1, C_Ant
\= 2, C_Ant \= 3` — mais les deux sont acceptés, et le corrigé le signale.

> **4. Le résultat de Xavier est moins bon que celui de Louis, et il concernait
> le plat.**

```prolog
% Phrase 4
C_Xav > C_Lou,
S_Xav = plat,
```

« Moins bon » = nombre plus grand. Symétrique du piège de la phrase 2.

> **5. La personne ayant cuisiné l'entrée a fini exactement une place devant
> celle qui a fait le Dessert.**

Celle-là est plus subtile : on ne sait pas **qui** a fait l'entrée. On utilise
`member/2` pour aller chercher la ligne, quelle qu'elle soit :

```prolog
% Phrase 5
member([_, C_Ent, entree, _], L),
member([_, C_Des, dessert, _], L),
C_Des is C_Ent + 1.
```

« Une place devant » veut dire un rang **de moins**, donc le dessert a un numéro
**plus grand**. `C_Des is C_Ent + 1`.

`member/2` sur une liste de listes avec un motif partiel : voilà la vraie
puissance de l'unification. On dit « une ligne dont la troisième colonne vaut
`entree` », et Prolog la trouve.

### La solution

```
[antoine, 4, dessert, mercredi]
[fabrice, 3, entree,  lundi]
[louis,   1, pain,    jeudi]
[xavier,  2, plat,    mardi]
```

Vérifiez à la main sur les cinq indices : c'est la bonne habitude à prendre en
épreuve. Cinq relectures valent mieux qu'une réponse fausse.

## La méthode, en cinq lignes

À appliquer telle quelle à toute énigme de ce type :

1. **Une liste de listes**, une ligne par entité, une colonne par attribut.
2. **Un `appartient/2` par attribut**, avec son domaine.
3. **Un commentaire `% Phrase n` par indice**, et sa traduction juste en dessous.
4. Pour les **ordres sur des atomes** : `nth1/3` puis comparaison d'indices.
5. Pour les contraintes portant sur un **individu inconnu** : `member/2` avec un
   motif partiel.

## Les pièges de vocabulaire

Ils reviennent à chaque énigme, et ils coûtent l'exercice entier :

| L'énoncé dit | Ça veut dire |
|---|---|
| « meilleur classement » | nombre **plus petit** |
| « moins bon résultat » | nombre **plus grand** |
| « n'a pas gagné » | `\= 1` |
| « pas sur le podium » (sur 4) | `= 4` |
| « une place devant » | rang **inférieur de 1** |
| « plus tard dans la semaine » | indice **plus grand** |

## Où GT montre ses limites

Treize mille combinaisons, ça passe. Mais pour six personnes et quatre
attributs, on serait à 6!⁴ ≈ 1,7 milliard. GT s'effondre.

La raison est celle du QCM : GT ne teste qu'à la fin. Il construit une
affectation complète où Fabrice est 1er — alors que l'indice 1 l'interdisait
dès le départ — et ne s'en aperçoit qu'après avoir tout affecté.

**La solution est de tester pendant qu'on affecte**, et c'est exactement ce que
fait la programmation par contraintes. C'est la séance suivante — et l'exercice
1 de mai en demandait justement les deux versions.
MD,
                'recap' => <<<'MD'
- **Générer et Tester** : on construit une solution **complète**, puis on la
  vérifie. Le retour arrière essaie les autres tout seul.
- La différence avec le Backtracking (QCM) : **GT teste sur une solution
  complète, BT teste au fur et à mesure de l'assignation.**
- Le générateur de permutations tient en deux lignes, grâce à `select/3` :
  `appartient([X|L],Dom) :- select(X,Dom,Dom1), appartient(L,Dom1).`
- Structure d'une énigme : **liste de listes**, un `appartient/2` par attribut,
  puis **un commentaire `% Phrase n` par indice**.
- Ordre sur des atomes → `nth1/3` puis comparaison d'indices.
  Individu inconnu → `member/2` avec motif partiel.
- **« Meilleur classement » = nombre plus petit.** Une inégalité inversée rend le
  système insoluble et fait rendre une copie vide.
- Écrivez `:-use_module(library(lists)).` en tête.
MD,
            ],

        ];
    }
}