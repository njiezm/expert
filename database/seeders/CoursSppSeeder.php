<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours de SPP, de A à Z.
 *
 * Note obtenue en mai : 1,5/20, « quasiment aucun acquis ». L'épreuve comptait
 * quatre exercices : formalisation propositionnelle, typage WhyML, prédicats du
 * premier ordre, définitions inductives. Trois sur quatre à zéro.
 *
 * Ce cours part donc de rien : la première séance ne suppose aucune notion de
 * logique, et chaque symbole est lu à voix haute avant d'être employé.
 */
class CoursSppSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'SPP')->first();

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
                'title' => 'Pourquoi on prouve un programme',
                'chapitre' => 'Intro',
                'duree_min' => 20,
                'prerequis' => "Aucun. On part de zéro, et on ne suppose aucune notion de logique.",
                'intro' => <<<'MD'
Bonjour.

Ce module s'appelle « Spécification et preuves de programmes ». Trois mots, et
le plus important est le dernier : **preuve**.

Vous savez déjà tester un programme : on l'exécute sur quelques exemples et on
regarde si ça marche. Aujourd'hui, on va voir pourquoi ça ne suffit pas, et ce
qu'on met à la place.

Aucune logique encore. Juste l'idée.
MD,
                'body' => <<<'MD'
## Un programme qui a l'air correct

Voici une fonction qui calcule la moyenne de deux entiers :

```
moyenne(a, b) = (a + b) / 2
```

Testons-la.

| a | b | Résultat | Attendu | Verdict |
|---|---|---|---|---|
| 4 | 6 | 5 | 5 | ✅ |
| 0 | 10 | 5 | 5 | ✅ |
| −4 | 4 | 0 | 0 | ✅ |
| 100 | 200 | 150 | 150 | ✅ |

Quatre tests, quatre succès. On livre.

Et un jour, avec des entiers sur 32 bits, quelqu'un appelle
`moyenne(2000000000, 2000000000)`. La somme dépasse la capacité, elle **déborde**,
et le résultat est **négatif**.

Le bug était là depuis le début. Les tests ne l'ont pas vu, parce qu'aucun ne
tombait dessus.

## La phrase de Dijkstra

Edsger Dijkstra l'a formulé une fois pour toutes, et c'est une citation qu'on
attend à l'examen :

> **« Le test peut révéler la présence de bugs, jamais leur absence. »**

Pourquoi ? Parce que tester, c'est examiner un **nombre fini** de cas. Or une
fonction sur deux entiers 32 bits a environ 18 milliards de milliards d'entrées
possibles. Même en testant un milliard de cas par seconde, il faudrait des siècles.

Le test dit : *« sur ces cas-là, ça marche »*. Il ne dit rien des autres.

## Ce qu'on met à la place

**Prouver**, c'est établir une propriété **pour toutes les entrées possibles**,
y compris celles qu'on n'a pas imaginées.

Ce n'est pas de la magie. C'est du raisonnement mathématique, appliqué au
programme. Exactement comme on démontre qu'un triangle rectangle vérifie le
théorème de Pythagore : on ne teste pas mille triangles, on démontre une fois.

Et pour raisonner sur un programme, il faut d'abord **dire ce qu'il devrait
faire**. C'est le premier mot du titre : la **spécification**.

## Spécifier avant de prouver

Voici la spécification de notre moyenne :

> **Précondition** — ce que l'appelant doit garantir : rien de particulier.
> **Postcondition** — ce que la fonction garantit : `2 × résultat = a + b`.

Cette écriture évite le débordement, parce qu'elle ne parle pas de la somme mais
de la relation entre le résultat et les entrées.

Retenez ces deux mots, on les reverra beaucoup :

- La **précondition** est ce qu'on exige **en entrée**. C'est la responsabilité
  de celui qui appelle.
- La **postcondition** est ce qu'on promet **en sortie**. C'est la responsabilité
  de la fonction.

Un programme prouvé, c'est un programme dont on a démontré que **si la précondition
est vraie avant, la postcondition est vraie après**.

## Le piège qu'il faut voir tout de suite

Regardez cette spécification pour une fonction « maximum d'un tableau » :

> Le résultat est supérieur ou égal à tous les éléments du tableau.

Elle a l'air correcte. Or voici une fonction qui la respecte parfaitement :

```
maximum(tableau) = 1000000
```

Un million est bien supérieur à tous les éléments — pourvu qu'ils soient plus
petits. Cette fonction est **prouvée correcte**, et elle est **absurde**.

Il manquait une phrase :

> …**et le résultat est un élément du tableau**.

**Voici la leçon, et elle vaut pour tout le module :** prouver un programme, c'est
démontrer qu'il respecte **une spécification donnée**. Si la spécification est
incomplète, la preuve ne garantit rien d'intéressant.

**Écrire la spécification est la partie difficile.** Le code vient après.

## Les trois niveaux de garantie

Dernière chose pour aujourd'hui. Il y a trois niveaux, et il faut les distinguer.

| Niveau | Ce qui est garanti |
|---|---|
| **Correction partielle** | *si* le programme termine, le résultat est correct |
| **Terminaison** | le programme s'arrête sur toute entrée valide |
| **Correction totale** | les deux à la fois |

Le mot **partielle** est important. Un programme qui boucle indéfiniment ne rend
jamais de résultat faux — donc il satisfait trivialement n'importe quelle
correction partielle. C'est absurde, mais c'est exactement pourquoi il faut
prouver la terminaison **séparément**.

On y reviendra en séance 12, avec un outil qui s'appelle le **variant**.

## Comment on fait, en pratique

Écrire les preuves à la main serait interminable. On utilise donc un outil :
**Why3**.

Le principe : on écrit le programme **et** sa spécification dans un langage que
Why3 comprend. L'outil en déduit une liste de formules logiques à démontrer — on
les appelle les **obligations de preuve** — et les envoie à des démonstrateurs
automatiques.

```
programme + spécification
        ↓   Why3 engendre
   obligations de preuve
        ↓   les solveurs démontrent
   programme prouvé correct
```

Vous verrez du Why3 à partir de la séance 7, et **votre épreuve en contient
beaucoup** : en mai, trois exercices sur quatre demandaient d'en écrire, sur
feuille et sans machine.

Alors on va apprendre à en écrire. Mais d'abord, il faut la logique — parce que
c'est le langage dans lequel les spécifications s'écrivent.

C'est l'objet des cinq prochaines séances.
MD,
                'recap' => <<<'MD'
**Tester** examine un nombre fini de cas. **Prouver** établit une propriété pour
toutes les entrées.

> « Le test peut révéler la présence de bugs, jamais leur absence. » — **Dijkstra**

**Deux mots à retenir dès maintenant :**
- **Précondition** — ce qu'on exige en entrée. Responsabilité de l'appelant.
- **Postcondition** — ce qu'on promet en sortie. Responsabilité de la fonction.

**Les trois niveaux :**

| Niveau | Garantit |
|---|---|
| Correction **partielle** | *si* ça termine, le résultat est correct |
| **Terminaison** | ça s'arrête toujours |
| Correction **totale** | les deux |

**Le piège du module :** une spécification incomplète se prouve très bien et ne
garantit rien. Écrire la spécification est la partie difficile.
MD,
            ],

            /* ================= Séance 2 ================= */
            [
                'title' => 'La logique propositionnelle — les briques',
                'chapitre' => 'Prop',
                'duree_min' => 30,
                'prerequis' => "La séance 1 : vous savez ce qu'est une précondition et une postcondition.",
                'intro' => <<<'MD'
Pour écrire une spécification, il faut un langage précis. Le français ne suffit
pas : *« le résultat doit être grand »* ne veut rien dire pour une machine.

Ce langage, c'est la **logique**. On commence aujourd'hui par sa forme la plus
simple : la **logique propositionnelle**.

C'est vraiment simple. Cinq symboles, et on aura fait le tour.
MD,
                'body' => <<<'MD'
## Une proposition

Une **proposition**, c'est une affirmation qui est soit **vraie**, soit **fausse**.
Pas les deux, pas autre chose.

Exemples de propositions :

- « Il pleut. » → vraie ou fausse, selon le moment.
- « 3 est un nombre pair. » → fausse.
- « L'étudiant travaille. » → vraie ou fausse.

Ce qui n'est **pas** une proposition :

- « Quelle heure est-il ? » → une question n'est ni vraie ni fausse.
- « Ferme la porte. » → un ordre non plus.
- « x + 1 » → ce n'est pas une affirmation.

En logique, on remplace les propositions par des **lettres** : `P`, `Q`, `T`, `N`.
Cela permet de raisonner sur la **forme** sans se laisser distraire par le sens.

## Les cinq connecteurs

On combine les propositions avec cinq symboles. Les voici, et je les lis à voix
haute pour vous.

### La négation : ¬

`¬P` se lit **« non P »**.

Elle inverse : si P est vraie, `¬P` est fausse, et réciproquement.

*« Il ne pleut pas »* → `¬P`.

### La conjonction : ∧

`P ∧ Q` se lit **« P et Q »**.

Vraie **seulement si les deux** sont vraies.

*« Il pleut et il fait froid »* → `P ∧ Q`.

Retenez la forme du symbole : le `∧` ressemble à un **A**, comme *and*.

### La disjonction : ∨

`P ∨ Q` se lit **« P ou Q »**.

Vraie si **au moins une** des deux est vraie — y compris les deux. C'est le « ou »
inclusif, pas le « ou » du restaurant.

*« Il pleut ou il neige »* → `P ∨ Q`. Et s'il fait les deux, la proposition est
vraie.

Le `∨` est le `∧` retourné. C'est fait exprès.

### L'implication : ⇒

`P ⇒ Q` se lit **« P implique Q »**, ou **« si P alors Q »**.

C'est le connecteur le plus important du module, et le plus mal compris.
On lui consacre toute la séance 3.

### L'équivalence : ⟺

`P ⟺ Q` se lit **« P équivaut à Q »**, ou **« P si et seulement si Q »**.

Vraie quand les deux propositions ont **la même valeur** : soit toutes deux vraies,
soit toutes deux fausses.

## La table de vérité

Comment savoir si une formule est vraie ? On énumère **tous les cas possibles**.
C'est ce que fait une **table de vérité**.

Avec deux propositions P et Q, il y a quatre combinaisons. Voici les cinq
connecteurs, tous d'un coup :

| P | Q | ¬P | P ∧ Q | P ∨ Q | P ⇒ Q | P ⟺ Q |
|---|---|---|---|---|---|---|
| V | V | F | **V** | **V** | **V** | **V** |
| V | F | F | F | **V** | **F** | F |
| F | V | **V** | F | **V** | **V** | F |
| F | F | **V** | F | F | **V** | **V** |

Prenez trente secondes pour lire chaque colonne. Elles ne s'inventent pas : ce sont
des définitions.

## La ligne qui surprend tout le monde

Regardez les deux dernières lignes de la colonne `P ⇒ Q`.

**Quand P est fausse, l'implication est vraie.** Toujours. Quelle que soit Q.

Cela paraît absurde au premier abord. Prenons un exemple.

> « **S'il** pleut, **alors** je prends mon parapluie. »

Il ne pleut pas, et je ne prends pas mon parapluie. Ai-je menti ? Non — je n'avais
rien promis pour le cas où il ne pleut pas.

Il ne pleut pas, et je prends quand même mon parapluie. Ai-je menti ? Non plus.
J'ai le droit.

**Une promesse conditionnelle n'est violée que dans un seul cas : quand la
condition est remplie et que la conséquence ne suit pas.** C'est la deuxième ligne
de la table : P vraie, Q fausse → l'implication est fausse.

Retenez cette formulation : **`P ⇒ Q` est fausse dans un seul cas, quand P est
vraie et Q fausse.** Tout le reste est vrai.

## Deux équivalences à connaître par cœur

Il y en a beaucoup, mais deux reviennent tout le temps.

### La forme disjonctive de l'implication

```
P ⇒ Q   ≡   ¬P ∨ Q
```

Vérifions sur la table :

| P | Q | P ⇒ Q | ¬P | ¬P ∨ Q |
|---|---|---|---|---|
| V | V | V | F | **V** |
| V | F | F | F | **F** |
| F | V | V | V | **V** |
| F | F | V | V | **V** |

Les deux colonnes en gras sont identiques. Les formules sont **équivalentes**.

Cette réécriture est très utile : elle transforme une implication en quelque chose
qu'on manipule plus facilement.

### La contraposée

```
P ⇒ Q   ≡   ¬Q ⇒ ¬P
```

*« S'il pleut, je prends mon parapluie »* équivaut à
*« si je ne prends pas mon parapluie, c'est qu'il ne pleut pas »*.

**La contraposée est toujours équivalente à l'implication de départ.** C'est la
seule des trois formes dérivées à l'être — et c'est un point sur lequel votre copie
de mai a été sanctionnée. On y revient en séance 3.

## De Morgan

Deux lois pour distribuer une négation :

```
¬(P ∧ Q)   ≡   ¬P ∨ ¬Q
¬(P ∨ Q)   ≡   ¬P ∧ ¬Q
```

En français : *« il est faux que les deux soient vraies »* équivaut à *« au moins
une est fausse »*.

**Retenez la mécanique : la négation traverse, et le connecteur bascule.**
Le `∧` devient `∨`, le `∨` devient `∧`.

## La négation d'une implication

Celle-ci sert constamment, alors on la démontre.

```
¬(P ⇒ Q)
  ≡ ¬(¬P ∨ Q)          [forme disjonctive]
  ≡ ¬¬P ∧ ¬Q           [De Morgan]
  ≡ P ∧ ¬Q             [double négation]
```

Donc :

```
¬(P ⇒ Q)   ≡   P ∧ ¬Q
```

En français : *« il est faux que P implique Q »* signifie **« P est vraie et Q est
fausse »** — c'est-à-dire exactement le contre-exemple.

C'est cohérent avec la table de vérité : l'implication n'est fausse que dans ce cas.

Notez-la. On s'en servira à chaque fois qu'il faudra nier une propriété.
MD,
                'recap' => <<<'MD'
Une **proposition** est une affirmation vraie ou fausse.

**Les cinq connecteurs :**

| Symbole | Se lit | Vrai quand |
|---|---|---|
| `¬P` | non P | P est fausse |
| `P ∧ Q` | P **et** Q | les deux sont vraies |
| `P ∨ Q` | P **ou** Q | au moins une est vraie |
| `P ⇒ Q` | P implique Q | **sauf** si P vraie et Q fausse |
| `P ⟺ Q` | P équivaut à Q | les deux ont la même valeur |

**La ligne qui surprend :** quand P est **fausse**, `P ⇒ Q` est **vraie**.
Une promesse conditionnelle n'est violée que si la condition est remplie et la
conséquence absente.

**Les équivalences à connaître :**

```
P ⇒ Q     ≡  ¬P ∨ Q            (forme disjonctive)
P ⇒ Q     ≡  ¬Q ⇒ ¬P           (contraposée)
¬(P ∧ Q)  ≡  ¬P ∨ ¬Q           (De Morgan)
¬(P ∨ Q)  ≡  ¬P ∧ ¬Q           (De Morgan)
¬(P ⇒ Q)  ≡  P ∧ ¬Q            (la négation d'une implication)
```
MD,
            ],

            /* ================= Séance 3 ================= */
            [
                'title' => 'Formaliser une phrase française',
                'chapitre' => 'Prop',
                'duree_min' => 35,
                'prerequis' => "La séance 2 : vous connaissez les cinq connecteurs et la table de vérité de l'implication.",
                'intro' => <<<'MD'
Séance la plus importante du module.

À votre épreuve de mai, l'exercice 1 demandait de traduire cinq phrases françaises
en logique. **Trois réponses sur cinq ont été comptées fausses.** C'était le premier
exercice, le plus accessible du sujet, et il a été manqué aux trois quarts.

La difficulté n'est presque jamais la logique. C'est le **français**. Certaines
tournures signalent une implication dans un sens, d'autres dans l'autre — et sous
pression, on les confond.

Aujourd'hui on règle ça définitivement, avec une règle unique et un tableau.
MD,
                'body' => <<<'MD'
## Le problème, sur un exemple

Deux phrases :

> **A.** *Il faut travailler pour réussir.*
> **B.** *Il suffit de travailler pour réussir.*

Mêmes mots ou presque. Décrivent-elles la même chose ?

**Non.** Et elles se formalisent **dans des sens opposés**.

La phrase A dit : sans travail, pas de réussite. Le travail est **indispensable**,
mais peut-être pas suffisant — on peut travailler et échouer quand même.

La phrase B dit : le travail garantit la réussite. Travaillez, et c'est gagné.

Avec `T` = « l'étudiant travaille » et `N` = « l'étudiant a de bonnes notes » :

- **A** → `N ⇒ T` (avoir de bonnes notes implique avoir travaillé)
- **B** → `T ⇒ N` (travailler implique avoir de bonnes notes)

Les deux flèches vont dans des directions opposées. Se tromper de sens, c'est
répondre exactement le contraire de ce qui est demandé.

## La règle unique

Il n'y a qu'une chose à retenir, et tout en découle.

> ### Le fait qui est **garanti** se place à **droite** de la flèche.

Dans `A ⇒ B` : `A` est ce qu'on suppose, `B` est ce qu'on en déduit.
`B` est **garanti** par `A`.

Reprenons la phrase A : *« il faut travailler pour réussir »*.

Qu'est-ce qui est garanti par quoi ? Si je vois quelqu'un qui a réussi, je peux en
**déduire** qu'il a travaillé. Le travail est donc garanti par la réussite.

Le fait garanti — le travail — va à droite : **`N ⇒ T`**.

## Le tableau complet

Appliquez la règle une fois à chaque tournure, et vous obtenez ce tableau. Il
couvre tout ce qui peut tomber.

| Tournure française | Formalisation |
|---|---|
| **si** A alors B | `A ⇒ B` |
| A **si** B | `B ⇒ A` |
| A **seulement si** B | `A ⇒ B` |
| A **ne** … **que si** B | `A ⇒ B` |
| B est **nécessaire** à A | `A ⇒ B` |
| il **faut** B pour A | `A ⇒ B` |
| B est **suffisant** pour A | `B ⇒ A` |
| il **suffit** de B pour A | `B ⇒ A` |
| A **à moins que** B | `¬B ⇒ A` |
| **malgré** B, A | `B ∧ A` |

Regardez la colonne de droite. **Six lignes sur dix donnent `A ⇒ B`.**
Seules « suffisant » et « il suffit » inversent. Et « malgré » n'est pas une
implication du tout.

## Les trois pièges

### Piège 1 : « seulement si »

C'est celui qui vous a coûté la question 1 en mai.

> *« C'est **seulement si** un étudiant travaille qu'il a de bonnes notes. »*

Vous aviez répondu `T ⇒ N`. Compté faux.

**Le mot « seulement » inverse le sens.** Sans lui, « si un étudiant travaille, il
a de bonnes notes » donnerait bien `T ⇒ N`. Avec lui, la phrase dit que le travail
est **la seule voie** — donc qu'observer de bonnes notes garantit le travail.

**Réponse : `N ⇒ T`.**

Le test qui tranche : *cherchez le contre-exemple*. Qu'est-ce qui contredirait la
phrase ? Un étudiant qui a de bonnes notes **sans** avoir travaillé. Autrement dit
`N ∧ ¬T`. Or on a vu en séance 2 que `¬(P ⇒ Q) ≡ P ∧ ¬Q`. Donc la phrase est
`N ⇒ T`.

Ce test prend quinze secondes et il ne se trompe jamais.

### Piège 2 : deux réponses au lieu d'une

Question 3 de votre copie :

> *« Pour un étudiant, le travail est une condition nécessaire à l'obtention de
> bonnes notes. »*

Vous aviez écrit **deux** formules, l'une sous l'autre :

```
T → N
¬T → ¬N
```

Le correcteur a barré et annoté : **« faux, choisir, pas équivalent »**.

Trois choses à en tirer.

**Un.** La bonne réponse était `N ⇒ T`. Aucune des deux propositions n'était juste.

**Deux.** `T ⇒ N` et `¬T ⇒ ¬N` **ne sont pas équivalentes**. On l'a vu en séance 2 :
la seule forme équivalente à `P ⇒ Q` est la **contraposée** `¬Q ⇒ ¬P`. Écrire
`¬P ⇒ ¬Q`, c'est la **réciproque** — une affirmation différente.

**Trois, et c'est le plus important :** écrire deux réponses fait perdre les points
des deux. Le correcteur ne trie pas à votre place. Deux formules contradictoires
signalent que vous ne savez pas laquelle est juste, et c'est précisément ce qui est
évalué.

**La règle : une question, une réponse.** Si l'hésitation persiste, écrivez la
formule retenue seule, puis une ligne d'argument. Jamais deux formules côte à côte.

### Piège 3 : « malgré »

> *« **Malgré** son travail, un étudiant a de mauvaises notes. »*

Ce n'est **pas** une implication. « Malgré » marque une opposition rhétorique, pas
une dépendance logique. La phrase affirme deux choses simultanément : il a
travaillé, **et** il a de mauvaises notes.

**Réponse : `T ∧ ¬N`.**

Vous aviez juste sur celle-là. Notez pourquoi : elle ne contenait pas le mot
« seulement ».

## Refaisons l'exercice de mai, en entier

Avec `T` = travaille, `N` = bonnes notes.

**1.** *C'est seulement si un étudiant travaille qu'il a de bonnes notes.*

« Seulement si » → condition nécessaire → le travail est garanti par les notes.
**`N ⇒ T`**

**2.** *Un étudiant n'a de bonnes notes que s'il travaille.*

« ne … que si » — même tournure que la 1, formulée autrement.
**`N ⇒ T`**

**3.** *Le travail est une condition nécessaire à l'obtention de bonnes notes.*

« nécessaire » → le mot qui suit est la **conclusion**.
**`N ⇒ T`**

**4.** *Un étudiant a de mauvaises notes, à moins qu'il ne travaille.*

« à moins que » → `¬B ⇒ A` avec A = mauvaises notes, B = travaille.
**`¬T ⇒ ¬N`**

*C'est la contraposée de `N ⇒ T`, donc équivalent aux trois premières.*

**5.** *Malgré son travail, un étudiant a de mauvaises notes.*

Conjonction.
**`T ∧ ¬N`**

## Le constat

**Quatre énoncés sur cinq disent la même chose.** Les questions 1, 2, 3 et 4
expriment toutes que le travail est nécessaire aux bonnes notes. Seule la 5 est
d'une autre nature.

Une fois qu'on l'a vu, l'exercice devient facile. Le sujet ne teste pas cinq
notions différentes : il teste **une** notion, sous cinq formulations.

## La méthode, en trois temps

Pour chaque phrase, à l'examen :

**Un.** Repérez la **tournure** dans le tableau.

**Deux.** Demandez-vous : *quel fait est garanti par l'autre ?* Le fait garanti va
à droite.

**Trois.** Vérifiez par le **contre-exemple** : quelle situation contredirait la
phrase ? Elle doit correspondre à `gauche ∧ ¬droite`.

Trois étapes, quinze secondes chacune. Et **une seule formule** rendue.
MD,
                'recap' => <<<'MD'
> **La règle unique : le fait qui est garanti se place à droite de la flèche.**

**Le tableau des tournures :**

| Tournure | Formalisation |
|---|---|
| si A alors B | `A ⇒ B` |
| A **seulement si** B | `A ⇒ B` |
| A **ne … que si** B | `A ⇒ B` |
| B **nécessaire** à A | `A ⇒ B` |
| il **faut** B pour A | `A ⇒ B` |
| B **suffisant** pour A | `B ⇒ A` |
| il **suffit** de B pour A | `B ⇒ A` |
| A **à moins que** B | `¬B ⇒ A` |
| **malgré** B, A | `B ∧ A` |

**Les trois pièges :**

1. **« Seulement si » inverse le sens.** Sans « seulement », `T ⇒ N`. Avec,
   `N ⇒ T`.
2. **`P ⇒ Q` et `¬P ⇒ ¬Q` ne sont pas équivalentes.** La seule forme équivalente
   est la contraposée `¬Q ⇒ ¬P`.
3. **« Malgré » n'est pas une implication**, c'est une conjonction.

**Et la règle de rédaction : une question, une formule.** Deux réponses
superposées font perdre les points des deux.

**Le test infaillible :** cherchez le contre-exemple. Ce qui contredirait `A ⇒ B`,
c'est `A ∧ ¬B`.
MD,
            ],

            /* ================= Séance 4 ================= */
            [
                'title' => 'Prouver une équivalence',
                'chapitre' => 'Prop',
                'duree_min' => 25,
                'prerequis' => "Les séances 2 et 3 : connecteurs, tables de vérité, formalisation.",
                'intro' => <<<'MD'
On sait traduire une phrase en formule. Il faut maintenant savoir **manipuler**
ces formules : montrer que deux d'entre elles disent la même chose, ou qu'une
formule est toujours vraie.

Deux méthodes, et il faut savoir choisir.
MD,
                'body' => <<<'MD'
## Trois sortes de formules

Avant les méthodes, trois mots de vocabulaire.

Une formule est une **tautologie** si elle est vraie **dans tous les cas**.
Exemple : `P ∨ ¬P` — soit il pleut, soit il ne pleut pas.

Elle est **contradictoire** si elle est fausse dans tous les cas.
Exemple : `P ∧ ¬P`.

Elle est **satisfiable** s'il existe au moins un cas où elle est vraie.

Et deux formules sont **équivalentes** si elles ont la même valeur dans tous les
cas. On note `F ≡ G`.

*Lien utile :* `F ≡ G` si et seulement si `F ⟺ G` est une **tautologie**.

## Méthode 1 : la table de vérité

C'est la méthode systématique. On énumère tous les cas et on compare.

**Montrons que `P ⇒ Q ≡ ¬P ∨ Q`.**

| P | Q | P ⇒ Q | ¬P | ¬P ∨ Q |
|---|---|---|---|---|
| V | V | **V** | F | **V** |
| V | F | **F** | F | **F** |
| F | V | **V** | V | **V** |
| F | F | **V** | V | **V** |

Les deux colonnes en gras coïncident ligne à ligne. **Les formules sont
équivalentes.** ∎

**Avantage :** infaillible, mécanique, aucune astuce à trouver.

**Inconvénient :** avec `n` propositions, la table a **2ⁿ lignes**. Deux
propositions font 4 lignes, trois en font 8, quatre en font 16. Au-delà, c'est
ingérable à la main.

**La règle pratique :** table de vérité jusqu'à trois propositions. Au-delà, on
calcule.

## Méthode 2 : la réécriture

On transforme une formule en l'autre, étape par étape, en appliquant des
équivalences connues. Chaque étape est **justifiée par une règle nommée**.

**Montrons que `¬(P ⇒ Q) ≡ P ∧ ¬Q`.**

```
¬(P ⇒ Q)
  ≡ ¬(¬P ∨ Q)        [forme disjonctive de l'implication]
  ≡ ¬¬P ∧ ¬Q         [loi de De Morgan]
  ≡ P ∧ ¬Q           [double négation]
```
∎

**Ce qui rapporte les points, c'est la colonne de droite.** Une chaîne d'égalités
sans justification ne se vérifie pas. Le correcteur veut savoir **quelle règle**
vous appliquez à chaque ligne.

## Les règles utilisables

Voici celles que le cours autorise. Apprenez-les : ce sont vos outils.

| Nom | Règle |
|---|---|
| Double négation | `¬¬P ≡ P` |
| De Morgan | `¬(P ∧ Q) ≡ ¬P ∨ ¬Q` · `¬(P ∨ Q) ≡ ¬P ∧ ¬Q` |
| Forme disjonctive | `P ⇒ Q ≡ ¬P ∨ Q` |
| Contraposition | `P ⇒ Q ≡ ¬Q ⇒ ¬P` |
| Commutativité | `P ∧ Q ≡ Q ∧ P` · `P ∨ Q ≡ Q ∨ P` |
| Associativité | `(P ∧ Q) ∧ R ≡ P ∧ (Q ∧ R)` |
| Distributivité | `P ∧ (Q ∨ R) ≡ (P ∧ Q) ∨ (P ∧ R)` |
| Idempotence | `P ∧ P ≡ P` · `P ∨ P ≡ P` |
| Absorption | `P ∧ (P ∨ Q) ≡ P` |
| Tiers exclu | `P ∨ ¬P ≡ V` |
| Contradiction | `P ∧ ¬P ≡ F` |

## Un exercice complet

**Montrez que `(P ⇒ Q) ∧ (P ⇒ ¬Q)` équivaut à `¬P`.**

Prenons la réécriture.

```
(P ⇒ Q) ∧ (P ⇒ ¬Q)
  ≡ (¬P ∨ Q) ∧ (¬P ∨ ¬Q)      [forme disjonctive, deux fois]
  ≡ ¬P ∨ (Q ∧ ¬Q)             [distributivité, en factorisant ¬P]
  ≡ ¬P ∨ F                    [contradiction]
  ≡ ¬P                        [F est neutre pour ∨]
```
∎

**Lisons le résultat.** Si P impliquait à la fois Q et son contraire, alors P ne
peut pas être vraie — sinon on aurait Q et ¬Q en même temps. Donc P est fausse.

Le calcul dit exactement ce que le bon sens suggère. C'est bon signe : quand un
calcul logique donne un résultat qui vous surprend, relisez-le.

## Comment l'examen pose la question

Trois formes reviennent.

**« Ces deux formules sont-elles équivalentes ? »**
→ Table de vérité si peu de variables, sinon réécriture. **Concluez explicitement.**

**« Simplifiez cette formule. »**
→ Réécriture, chaque étape justifiée.

**« Cette formule est-elle une tautologie ? »**
→ Table de vérité : toutes les lignes à V. Ou réécriture jusqu'à `V`.

Et dans tous les cas : **une seule réponse finale**, encadrée ou soulignée.
MD,
                'recap' => <<<'MD'
**Le vocabulaire :** une **tautologie** est vraie dans tous les cas ; une formule
**contradictoire** est fausse dans tous les cas ; une formule **satisfiable** est
vraie dans au moins un cas.

**Deux méthodes :**

| Méthode | Quand | Attention |
|---|---|---|
| **Table de vérité** | jusqu'à 3 propositions | 2ⁿ lignes, ça explose |
| **Réécriture** | au-delà | **chaque étape doit être justifiée** |

**Les règles principales :**

```
¬¬P       ≡ P
¬(P ∧ Q)  ≡ ¬P ∨ ¬Q            De Morgan
¬(P ∨ Q)  ≡ ¬P ∧ ¬Q            De Morgan
P ⇒ Q     ≡ ¬P ∨ Q             forme disjonctive
P ⇒ Q     ≡ ¬Q ⇒ ¬P            contraposition
P ∨ ¬P    ≡ V                  tiers exclu
P ∧ ¬P    ≡ F                  contradiction
```

**Ce qui rapporte les points en réécriture : la colonne des justifications.**
Une chaîne d'égalités sans règle nommée ne se vérifie pas.
MD,
            ],

            /* ================= Séance 5 ================= */
            [
                'title' => 'La logique du premier ordre — les quantificateurs',
                'chapitre' => 'Pred',
                'duree_min' => 35,
                'prerequis' => "Les séances 2 à 4 : la logique propositionnelle vous est acquise.",
                'intro' => <<<'MD'
La logique propositionnelle a une limite. Elle sait dire *« il pleut »*, mais pas
*« tous les étudiants travaillent »*.

Pour parler d'**objets** et de ce qui vaut **pour tous** ou **pour au moins un**,
il faut monter d'un cran : la **logique du premier ordre**, aussi appelée logique
des prédicats.

Deux symboles nouveaux, et trois pièges. C'est le chapitre de poids 5 du module.
MD,
                'body' => <<<'MD'
## Le prédicat

Un **prédicat** est une propriété qui porte sur un ou plusieurs objets.

- `Travaille(x)` — « x travaille ». Un argument.
- `Aime(x, y)` — « x aime y ». Deux arguments.
- `Entre(x, y, z)` — « x est entre y et z ». Trois arguments.

Le nombre d'arguments s'appelle l'**arité**.

Un prédicat n'est ni vrai ni faux tant qu'on n'a pas dit **de qui** on parle.
`Travaille(x)` n'a pas de valeur de vérité. `Travaille(ana)` en a une.

C'est la différence avec une proposition : `P` était vraie ou fausse ;
`Travaille(x)` attend qu'on remplisse le trou.

## Les deux quantificateurs

Pour remplir le trou sans nommer personne, on **quantifie**.

### Le quantificateur universel : ∀

`∀x` se lit **« pour tout x »**.

`∀x Travaille(x)` — *« tout le monde travaille »*.

Le symbole est un **A** retourné, comme *all*.

### Le quantificateur existentiel : ∃

`∃x` se lit **« il existe x »**.

`∃x Travaille(x)` — *« quelqu'un travaille »*, c'est-à-dire au moins une personne.

Le symbole est un **E** retourné, comme *exists*.

## La règle du connecteur

Voici le premier piège, et il est très fréquent.

> ### `∀` va avec `⇒`. `∃` va avec `∧`.

Pourquoi ? Regardons.

**Avec ∀.** *« Tout étudiant travailleur réussit. »*

```
∀x ( Travaille(x) ⇒ Reussit(x) )
```

L'implication restreint : on ne parle **que** des travailleurs. Pour les autres,
l'implication est automatiquement vraie — souvenez-vous de la séance 2, quand la
gauche est fausse, l'implication est vraie.

Si l'on écrivait `∀x ( Travaille(x) ∧ Reussit(x) )`, on affirmerait que **tout le
monde travaille et réussit**. C'est infiniment plus fort, et ce n'est pas la phrase.

**Avec ∃.** *« Un étudiant travailleur a réussi. »*

```
∃x ( Travaille(x) ∧ Reussit(x) )
```

La conjonction exige les deux. Si l'on écrivait
`∃x ( Travaille(x) ⇒ Reussit(x) )`, il suffirait qu'**une seule personne ne
travaille pas** pour que la formule soit vraie — l'implication serait satisfaite
par la fausseté de sa gauche. La formule ne dirait plus rien.

**Retenez-le mécaniquement : `∀` avec `⇒`, `∃` avec `∧`.** Sans exception.

## L'ordre des quantificateurs

Deuxième piège, et le plus subtil.

```
∀x ∃y  Aime(x, y)
∃y ∀x  Aime(x, y)
```

Ces deux formules ne disent **pas** la même chose.

**La première** : *pour tout x, il existe un y que x aime.* Chacun aime quelqu'un —
mais **potentiellement une personne différente**. Ana aime Ben, Ben aime Chloé.

**La seconde** : *il existe un y tel que tout x l'aime.* Il y a **une même
personne** aimée de tous.

La seconde est bien plus forte. Elle **implique** la première : si tout le monde
aime Chloé, alors chacun aime bien quelqu'un.

L'inverse est faux : que chacun aime quelqu'un n'entraîne pas qu'il existe une
personne universellement aimée.

**La règle :** on ne peut **jamais** intervertir deux quantificateurs de nature
différente. Deux `∀` entre eux, oui. Deux `∃` entre eux, oui. Un `∀` et un `∃`,
jamais.

## Nier une formule quantifiée

Troisième piège, et c'est celui qui rapporte des points quand on le maîtrise.

Deux lois, symétriques :

```
¬ ∀x P(x)   ≡   ∃x ¬P(x)
¬ ∃x P(x)   ≡   ∀x ¬P(x)
```

En français : *« il est faux que tous soient P »* équivaut à *« il en existe un qui
n'est pas P »*. C'est le **contre-exemple**.

**La mécanique : on pousse la négation vers l'intérieur, et le quantificateur
bascule.** `∀` devient `∃`, `∃` devient `∀`.

### La formule à connaître par cœur

En combinant avec `¬(A ⇒ B) ≡ A ∧ ¬B` de la séance 2 :

```
¬ ∀x ( P(x) ⇒ Q(x) )   ≡   ∃x ( P(x) ∧ ¬Q(x) )
```

**La négation d'un « tous » est un contre-exemple.**

*« Il est faux que tout étudiant travailleur réussit »* signifie *« il existe un
étudiant qui travaille et qui ne réussit pas »*.

C'est exactement ce qu'on cherche quand on veut réfuter une affirmation générale :
un seul contre-exemple suffit.

## Variables libres et liées

Un dernier point de vocabulaire, mais il est **essentiel** — c'est lui qui vous a
coûté des points en mai.

Dans `∀x ( P(x) ⇒ Q(x, y) )` :

- `x` est **liée** : elle est sous la portée d'un quantificateur.
- `y` est **libre** : rien ne dit qui elle est.

Une formule sans variable libre s'appelle une **formule close**.

> ### Seule une formule close a une valeur de vérité.

`Q(x, y)` n'est ni vraie ni fausse — on ne sait pas de qui on parle.
`∀x ∀y Q(x, y)` est vraie ou fausse.

**Retenez cela.** En mai, vous avez écrit un lemme avec une variable libre, et le
correcteur a annoté « + forall ». On y reviendra en séance 8, quand on écrira du
Why3 — mais le principe est logique, pas syntaxique.

## Formaliser, sur un exemple complet

*« Tout étudiant qui rend tous ses devoirs obtient une mention. »*

Prédicats : `E(x)` — x est étudiant · `D(d)` — d est un devoir ·
`Rend(x, d)` — x rend d · `M(x)` — x obtient une mention.

**Procédons de l'extérieur vers l'intérieur.**

Le quantificateur principal porte sur les étudiants : `∀x`.
Suivi de `⇒`, puisque c'est un `∀`.

```
∀x ( E(x) ∧ [rend tous ses devoirs]  ⇒  M(x) )
```

Reste à écrire « rend tous ses devoirs ». C'est encore un `∀`, donc encore un `⇒` :

```
∀d ( D(d) ⇒ Rend(x, d) )
```

En assemblant :

```
∀x ( E(x) ∧ ∀d ( D(d) ⇒ Rend(x, d) )  ⇒  M(x) )
```

**Notez les deux `⇒`**, chacun attaché à son `∀`. Et les variables sont **typées**
par un prédicat de domaine — `E(x)`, `D(d)` — plutôt que laissées libres de
parcourir n'importe quoi.

### Et sa négation

*« Il existe un étudiant qui rend tous ses devoirs et n'obtient pas de mention. »*

```
∃x ( E(x) ∧ ∀d ( D(d) ⇒ Rend(x, d) )  ∧  ¬M(x) )
```

Le `∀x` est devenu `∃x`, et l'implication principale s'est transformée en
conjonction.

**Attention :** le `∀d` interne **n'a pas bougé**. La négation ne traverse que ce
qui est effectivement dans sa portée, et une fois poussée jusqu'à `¬M(x)`, elle
n'a plus rien à faire du `∀d`.

C'est le piège classique : nier tous les quantificateurs par réflexe. Il faut
pousser **pas à pas**, en notant la règle à chaque étape.
MD,
                'recap' => <<<'MD'
Un **prédicat** est une propriété portant sur des objets : `Travaille(x)`,
`Aime(x, y)`. Il n'a de valeur de vérité qu'une fois les trous remplis.

**Les deux quantificateurs :** `∀x` « pour tout x », `∃x` « il existe x ».

> **La règle du connecteur : `∀` va avec `⇒`, `∃` va avec `∧`.**

`∀x (P(x) ∧ Q(x))` dirait que **tout le monde** est P — bien trop fort.
`∃x (P(x) ⇒ Q(x))` serait satisfaite par n'importe quel non-P — bien trop faible.

**L'ordre compte.** `∀x ∃y` : chacun a le sien. `∃y ∀x` : un seul pour tous.
La seconde implique la première, jamais l'inverse. **On n'intervertit jamais deux
quantificateurs de nature différente.**

**La négation :**

```
¬ ∀x P(x)              ≡  ∃x ¬P(x)
¬ ∃x P(x)              ≡  ∀x ¬P(x)
¬ ∀x ( P(x) ⇒ Q(x) )   ≡  ∃x ( P(x) ∧ ¬Q(x) )     ← le contre-exemple
```

**Variable liée** = sous un quantificateur. **Libre** = sinon.
**Seule une formule close — sans variable libre — a une valeur de vérité.**
MD,
            ],

            /* ================= Séance 6 ================= */
            [
                'title' => 'Théories, modèles, et ce que les solveurs savent faire',
                'chapitre' => 'Theories',
                'duree_min' => 25,
                'prerequis' => "La séance 5 : quantificateurs et prédicats.",
                'intro' => <<<'MD'
Séance plus courte, un peu plus abstraite, mais nécessaire : elle explique
**pourquoi Why3 a besoin qu'on lui dise `use int.Int`** avant de pouvoir démontrer
que `x + 0 = x`.

Ce n'est pas une formalité administrative. C'est de la logique.
MD,
                'body' => <<<'MD'
## Un symbole ne veut rien dire

Écrivons une formule :

```
∀x ∀y  x + y = y + x
```

Elle affirme que `+` est commutatif. Est-elle vraie ?

**On ne peut pas répondre.** Parce que `+` n'est, pour l'instant, qu'un **symbole**.
Rien ne dit qu'il désigne l'addition. Il pourrait désigner la soustraction — auquel
cas la formule serait fausse.

Un symbole ne prend un sens que dans un **contexte** qui le lui donne. Ce contexte
s'appelle une **théorie**.

## Trois notions

### La signature

La **signature** est la liste des symboles disponibles, avec leur **arité** :

```
{ 0 (constante), s (unaire), + (binaire), < (prédicat binaire) }
```

Elle dit ce qu'on peut écrire, pas ce que ça veut dire.

### La théorie

Une **théorie** est un ensemble de **formules closes** sur cette signature, appelées
les **axiomes**. Ce sont les règles du jeu, posées d'avance.

Par exemple, pour l'addition sur les entiers :

```
A1 : ∀x.  x + 0 = x
A2 : ∀x ∀y.  x + s(y) = s(x + y)
```

Ici `s` est le successeur : `s(0)` est 1, `s(s(0))` est 2, et ainsi de suite.

### Le modèle

Un **modèle** est une **interprétation** qui rend **tous les axiomes vrais**.
Concrètement : un domaine d'objets, une valeur pour chaque constante, une fonction
pour chaque symbole de fonction.

Les entiers naturels, avec l'addition usuelle, forment un modèle de la théorie
ci-dessus. Mais ce n'est pas forcément le seul.

## Ce que « T ⊨ φ » veut dire

Le symbole `⊨` se lit **« satisfait »** ou **« a pour conséquence »**.

> `T ⊨ φ` signifie : **φ est vraie dans tous les modèles de T**.

Autrement dit, φ découle des axiomes, quelle que soit l'interprétation choisie.

**Démonstration sur un exemple.** Montrons que `T ⊨ s(0) + s(0) = s(s(0))`,
c'est-à-dire que 1 + 1 = 2.

```
s(0) + s(0)
  = s( s(0) + 0 )        [par A2, avec x := s(0) et y := 0]
  = s( s(0) )            [par A1, avec x := s(0)]
```

C'est démontré, et **sans jamais interpréter les symboles**. La dérivation vaut
donc dans tous les modèles. ∎

Notez la présentation : chaque étape indique **quel axiome** est appliqué et
**avec quelle substitution**. C'est ce que le correcteur cherche.

## Trois propriétés d'une théorie

Trois mots à ne pas confondre. Ils sont **indépendants**.

**Cohérente** — elle admet **au moins un modèle**. De façon équivalente, elle ne
démontre pas `faux`.

*Une théorie incohérente démontre tout*, y compris n'importe quelle absurdité.
C'est le pire défaut possible.

**Complète** — pour toute formule close φ, elle **tranche** : soit `T ⊨ φ`, soit
`T ⊨ ¬φ`. Aucune question ne reste sans réponse.

**Décidable** — il existe un **algorithme** qui décide, pour toute φ, si `T ⊨ φ`.

## Les deux exemples à connaître

**L'arithmétique de Presburger** — les entiers avec `+` et `<`, **sans
multiplication**. Elle est **cohérente, complète et décidable**.

**L'arithmétique de Peano** — on ajoute `×`. Elle est **cohérente**, mais **ni
complète ni décidable**.

C'est le premier **théorème d'incomplétude de Gödel**, et il tombe en question de
cours.

**Qu'est-ce qui change ?** La multiplication, et elle seule.

Avec l'addition seule, on ne peut pas exprimer la divisibilité de façon générale.
Avec la multiplication, on le peut — et l'on devient capable de **coder à
l'intérieur de la théorie des énoncés sur la théorie elle-même**.

Un symbole de plus, et la théorie peut parler d'elle-même. C'est ce qui permet à
Gödel de construire un énoncé qui affirme sa propre indémontrabilité.

## Le lien avec Why3

Nous y voilà.

Why3 s'appuie sur des **solveurs SMT** — Alt-Ergo, Z3, CVC4. « SMT » signifie
*Satisfiability Modulo Theories* : satisfiabilité **modulo des théories**.

Ces solveurs ne raisonnent pas dans le vide. Ils connaissent un certain nombre de
théories — l'arithmétique linéaire, les tableaux, les listes — et savent y décider
efficacement.

D'où la ligne que vous verrez en tête de chaque fichier :

```whyml
use int.Int
```

Elle **importe la théorie des entiers** : les axiomes qui rendent `+`, `*` et `<`
conformes à leur sens usuel.

**Sans elle, `x + 0 = x` n'est pas démontrable.** Rien n'a dit que `0` est neutre.
Le solveur voit un symbole binaire quelconque et une constante quelconque, et il
ne peut rien en faire.

Ce n'est pas un caprice de l'outil. C'est exactement ce qu'on vient de voir :
**un symbole ne veut rien dire sans théorie**.
MD,
                'recap' => <<<'MD'
**Signature** — les symboles disponibles, avec leur arité.
**Théorie** — un ensemble d'**axiomes** sur cette signature. Elle donne un sens aux
symboles.
**Modèle** — une interprétation qui rend tous les axiomes vrais.

**`T ⊨ φ`** se lit « φ est vraie dans **tous** les modèles de T ».

**Trois propriétés indépendantes :**

| | Définition | Peano |
|---|---|---|
| **Cohérente** | admet au moins un modèle | ✅ |
| **Complète** | tranche toute formule close | ❌ (Gödel) |
| **Décidable** | un algorithme décide `T ⊨ φ` | ❌ |

**Presburger** (entiers, `+`, `<`) est décidable. **Peano** (avec `×`) ne l'est pas.
La multiplication est la seule différence : elle permet à la théorie de parler
d'elle-même.

**Et c'est pourquoi Why3 exige `use int.Int`** : sans théorie importée, les symboles
n'ont pas de sens, et `x + 0 = x` n'est pas démontrable.
MD,
            ],

            /* ================= Séance 7 ================= */
            [
                'title' => 'Écrire du Why3 — la syntaxe minimale',
                'chapitre' => 'Contrats',
                'duree_min' => 35,
                'prerequis' => "Les séances 5 et 6 : quantificateurs, et ce qu'est une théorie.",
                'intro' => <<<'MD'
Séance décisive. À l'épreuve de mai, **trois exercices sur quatre** demandaient
d'écrire du Why3, sur feuille et sans machine. Les trois ont été notés zéro, avec
les annotations *« erreur de type »*, *« incomplet »* et *« cours pas connu »*.

Ce n'est pas une question de théorie — la logique, vous l'avez vue dans les séances
précédentes. C'est une **syntaxe**, et une syntaxe ne se retient qu'en l'écrivant.

Alors aujourd'hui, on écrit.
MD,
                'body' => <<<'MD'
## Le squelette d'un fichier

Un fichier Why3 porte l'extension `.mlw` et s'organise en **modules** :

```whyml
module MonModule

  use int.Int          (* on importe la théorie des entiers *)

  (* ici : les déclarations *)

end
```

Les commentaires s'écrivent entre `(*` et `*)`.

## Les cinq sortes de déclarations

C'est le point qu'il faut fixer avant tout le reste. Chaque mot-clé rend une chose
différente, et les confondre est l'erreur numéro un.

| Mot-clé | Rend | Sert à |
|---|---|---|
| `predicate` | une **formule logique** | définir une propriété |
| `function` | une **valeur**, en logique | définir un terme |
| `let` / `let rec` | une **valeur**, en programme | écrire du code exécutable |
| `lemma` | une formule **à démontrer** | énoncer un résultat |
| `axiom` | une formule **admise** | poser une hypothèse |

Exemples :

```whyml
predicate pair (n: int) = mod n 2 = 0          (* rend vrai ou faux *)

function double (n: int) : int = 2 * n         (* rend un entier *)

let calculer (n: int) : int = 2 * n            (* du code exécutable *)

lemma double_pair : forall n: int. pair (double n)    (* à démontrer *)
```

**Retenez la distinction `predicate` / `function`.** Un prédicat rend une
**proposition** ; une fonction rend une **valeur**. On ne peut pas les échanger.

## La syntaxe des formules

Les connecteurs de la séance 2, en notation Why3 :

| Logique | Why3 |
|---|---|
| `¬` | `not` |
| `∧` | `/\` |
| `∨` | `\/` |
| `⇒` | `->` |
| `⟺` | `<->` |
| `∀` | `forall` |
| `∃` | `exists` |

Et la syntaxe des quantificateurs :

```whyml
forall x: int. P x                    (* le POINT est obligatoire *)
exists x: int. P x
forall x y: int. P x y                (* variables groupées, même type *)
forall l: list 'a. length l >= 0      (* type paramétré *)
```

**Le point après le quantificateur est syntaxiquement obligatoire.** L'oublier est
une erreur de compilation.

Notez aussi que l'application se note **sans parenthèses** : on écrit `P x y`, pas
`P(x, y)`. C'est la convention des langages de la famille ML.

## L'erreur de l'exercice 2

Voici ce que vous aviez rendu :

```whyml
let P1 (a:int, b:int) : int = if a then b
```

L'énoncé demandait de traduire « a implique b ». Le correcteur a entouré et écrit
**« erreur de type »**.

**Trois erreurs en une ligne.** Prenons-les.

**Un.** La condition d'un `if` doit être de type **`bool`**, pas `int`. En Java,
`if (1)` ne compile pas non plus ; en Why3, c'est pareil.

**Deux.** Le `if` n'a **pas de branche `else`**. Une expression qui doit rendre un
`int` doit en rendre un dans tous les cas.

**Trois, et c'est le fond du problème.** Une **implication logique** ne se code pas
par un `if`. Un `if` est une instruction de **calcul** ; une implication est une
**formule**.

Ce que l'énoncé demandait :

```whyml
predicate p1 (a b: bool) = a -> b
```

Une **formule**, dans un `predicate`. Pas du code.

**La leçon générale :** avant d'écrire, demandez-vous *« est-ce que je décris un
calcul, ou une propriété ? »* Un calcul → `let` ou `function`. Une propriété →
`predicate` ou `lemma`.

## Refaisons l'exercice 2 en entier

L'énoncé posait deux propositions :

> P1 : « a implique b »
> P2 : « soit non a, soit a et b »

```whyml
predicate p1 (a b: bool) = a -> b

predicate p2 (a b: bool) = (not a) \/ (a /\ b)

lemma equivalence :
  forall a b: bool. p1 a b <-> p2 a b
```

*Et la démonstration, pour vous convaincre :*

```
(not a) \/ (a /\ b)
  ≡ ((not a) \/ a) /\ ((not a) \/ b)     [distributivité]
  ≡ vrai /\ ((not a) \/ b)               [tiers exclu]
  ≡ (not a) \/ b                         [vrai est neutre pour ∧]
  ≡ a -> b                               [forme disjonctive]
```

C'est exactement la séance 4, appliquée.

## Les prédicats du premier ordre

L'exercice 3 portait sur les relations binaires. Voici ce que vous aviez écrit :

```whyml
predicate p x y
predicate p y z
predicate transitif = exist x. p x z
predicate asym = not(exist x. p y x)
```

Annotations : **« voir énoncé »**, **« incomplet »**. Zéro.

**Ce qui n'allait pas.**

- Les prédicats sont **déclarés sans leurs types**. Il faut dire sur quoi ils portent.
- `transitif` ne quantifie **qu'une variable sur trois**, et `z` reste libre.
- `exist` s'écrit `exists`.
- La transitivité demande **trois** variables et **deux** hypothèses.

**Ce qu'il fallait écrire :**

```whyml
type t                              (* le type sur lequel porte la relation *)

predicate p t t                     (* déclaration : deux arguments de type t *)

predicate reflexif =
  forall x: t. p x x

predicate symetrique =
  forall x y: t. p x y -> p y x

predicate transitif =
  forall x y z: t. p x y -> p y z -> p x z

predicate asymetrique =
  forall x y: t. p x y -> not (p y x)

predicate irreflexif =
  forall x: t. not (p x x)
```

**Trois points à noter.**

**Un.** `predicate p t t` **déclare** la relation avec ses types d'arguments, sans
la définir. C'est comme déclarer une fonction sans l'implémenter.

**Deux.** `transitif` prend **trois variables** et **deux hypothèses**, enchaînées
par `->`. On écrit `p x y -> p y z -> p x z`, et non
`p x y /\ p y z -> p x z`. Les deux sont logiquement équivalents, mais la première
forme — dite **curryfiée** — est la convention de Why3.

**Trois.** Toutes les variables sont **quantifiées**. Aucune n'est libre.

## Le mot du correcteur : « + forall »

Exercice 4, question 5. Vous aviez écrit :

```whyml
lemma l2 : 0 < len(l)
```

Deux annotations : **« + forall »**, et le `<` corrigé en `≤`.

**Première faute.** La variable `l` est **libre**. On l'a vu en séance 5 : une
formule avec une variable libre **n'a pas de valeur de vérité**. On ne peut donc
pas la démontrer.

> ### Tout `lemma` est clos. Sans exception.

```whyml
lemma l2 : forall l: list 'a. 0 <= length l
```

**Seconde faute.** L'inégalité était **stricte**. Or la liste vide a une longueur
de **zéro**, et `0 < 0` est faux. Il fallait `<=`.

**La règle qui en découle, et elle vaut pour tout le module :**

> ### Avant de choisir entre `<` et `<=`, testez le cas de base.

Liste vide, tableau vide, `n = 0`. C'est presque toujours là que l'inégalité stricte
casse.

## Votre liste de vérification

Avant de rendre une ligne de Why3, passez-la à ce filtre :

- [ ] Le bon mot-clé — `predicate` pour une formule, `function` pour une valeur,
      `let` pour du code.
- [ ] **Tous les paramètres typés** dans la déclaration.
- [ ] **Aucune variable libre** — tout `lemma` est clos par des `forall`.
- [ ] Le **point** après chaque quantificateur.
- [ ] Les inégalités **vérifiées sur le cas de base**.

Cinq points. Ils couvrent la totalité des annotations de votre copie de mai.
MD,
                'recap' => <<<'MD'
**Les cinq déclarations :**

| Mot-clé | Rend |
|---|---|
| `predicate` | une **formule logique** |
| `function` | une **valeur**, en logique |
| `let` / `let rec` | une valeur, en **code exécutable** |
| `lemma` | une formule **à démontrer** |
| `axiom` | une formule **admise** |

**La syntaxe :** `not` · `/\` · `\/` · `->` · `<->` · `forall x: t. P x`
*(le point est obligatoire)*. Application sans parenthèses : `p x y`.

**Les définitions canoniques :**

```whyml
predicate transitif    = forall x y z: t. p x y -> p y z -> p x z
predicate symetrique   = forall x y: t. p x y -> p y x
predicate asymetrique  = forall x y: t. p x y -> not (p y x)
predicate reflexif     = forall x: t. p x x
```

**Les trois erreurs de mai :**

1. Une implication logique **n'est pas un `if`**. C'est une formule, dans un
   `predicate`.
2. **Tout `lemma` est clos.** Une variable libre n'a pas de valeur de vérité.
   C'était le « + forall ».
3. **Testez le cas de base** avant de choisir `<` ou `<=`. La liste vide a une
   longueur de zéro.
MD,
            ],

            /* ================= Séance 8 ================= */
            [
                'title' => 'Les types inductifs et les listes',
                'chapitre' => 'Types',
                'duree_min' => 30,
                'prerequis' => "La séance 7 : vous savez écrire une déclaration Why3.",
                'intro' => <<<'MD'
L'exercice 4 de mai portait sur les listes. Vous aviez écrit :

```
length(Nil) = Nil = 0
```

Le correcteur a annoté **« cours pas connu »**. Zéro sur tout l'exercice.

Ce n'est pas grave, mais il faut reprendre depuis le début. Une **liste**, en
programmation fonctionnelle, ce n'est pas un tableau. C'est une structure définie
d'une façon très particulière — et cette façon détermine ensuite comment on écrit
les fonctions et comment on fait les preuves.

C'est tout l'objet de la séance.
MD,
                'body' => <<<'MD'
## Définir un ensemble par ses constructeurs

Voici une idée nouvelle. Au lieu de décrire ce qu'est une liste, on décrit
**comment on en fabrique une**.

Il n'y a que deux façons :

1. Prendre la **liste vide**. On l'appelle `Nil`.
2. Prendre un élément `x` et une liste `r` déjà construite, et **coller** `x`
   devant. On l'appelle `Cons x r`.

C'est tout. Toute liste s'obtient par ces deux moyens, en un nombre fini d'étapes.

En Why3 :

```whyml
type list 'a = Nil | Cons 'a (list 'a)
```

Décryptons.

- `'a` est un **paramètre de type** : la liste peut contenir n'importe quoi.
  `list int` est une liste d'entiers, `list bool` une liste de booléens.
- `Nil` et `Cons` sont les deux **constructeurs**, séparés par une barre `|`.
- `Cons 'a (list 'a)` signifie que `Cons` prend **deux arguments** : un élément
  de type `'a`, et une liste.

Notez que `Cons` prend une liste — c'est-à-dire que la définition **se
réfère à elle-même**. On dit qu'elle est **inductive**, ou récursive.

## À quoi ressemble une liste, concrètement

La liste contenant 1, 2, 3 s'écrit :

```
Cons 1 (Cons 2 (Cons 3 Nil))
```

Lisez de l'intérieur : on part de `Nil`, on colle 3 devant, puis 2, puis 1.

C'est une structure en **oignon**. Chaque couche est un `Cons`, et au centre il y a
`Nil`.

**Votre erreur de mai devient claire.** Vous aviez écrit `length(Nil) = Nil = 0`.
Or `Nil` est un **constructeur de liste**, et `0` est un **entier**. Les deux
n'ont pas le même type, ils ne peuvent pas être égaux.

`length Nil` **vaut** `0`. Mais `Nil` ne vaut pas `0` — `Nil` est une liste.

## Les trois propriétés garanties

Une définition inductive garantit trois choses, et il faut savoir les énoncer.

**Exhaustivité** — tout élément du type provient d'un constructeur. Il n'y a pas de
liste qui ne soit ni `Nil` ni un `Cons`.

**Disjonction** — deux constructeurs distincts donnent des valeurs distinctes.
`Nil` n'est jamais égal à un `Cons`, quels que soient ses arguments.

**Injectivité** — si `Cons x r = Cons y s`, alors `x = y` **et** `r = s`.

Ces trois propriétés paraissent évidentes. Elles sont pourtant ce qui **fonde
l'induction structurelle** de la séance 9 : puisqu'il n'y a pas d'autre façon de
fabriquer une liste, traiter les deux constructeurs, c'est tout traiter.

## Le filtrage

Comment écrire une fonction sur une liste ? On **filtre** : on examine par quel
constructeur elle a été fabriquée, et on traite chaque cas.

```whyml
function length (l: list 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + length r
  end
```

Lisez-le.

- **Cas `Nil`** — la liste vide a une longueur de zéro.
- **Cas `Cons _ r`** — la liste est un élément suivi d'un reste `r`.
  Sa longueur est **1 plus** la longueur du reste.

Le `_` est un **motif joker** : « je ne me sers pas de cet argument ». Ici on
compte les éléments sans les regarder.

Le `end` ferme le `match`. Il est obligatoire.

**Voilà la définition correcte de `length`**, celle que l'exercice 4 demandait :

```
length Nil          = 0
length (Cons x r)   = 1 + length r
```

**Une valeur par constructeur.** Pas un type, pas une égalité entre choses de types
différents. Une valeur.

## Déroulons un calcul

Calculons `length (Cons 1 (Cons 2 (Cons 3 Nil)))`, pas à pas :

```
length (Cons 1 (Cons 2 (Cons 3 Nil)))
  = 1 + length (Cons 2 (Cons 3 Nil))       [cas Cons]
  = 1 + (1 + length (Cons 3 Nil))          [cas Cons]
  = 1 + (1 + (1 + length Nil))             [cas Cons]
  = 1 + (1 + (1 + 0))                      [cas Nil]
  = 3
```

Chaque étape pèle une couche de l'oignon, jusqu'à `Nil`. **La récursion s'arrête
toujours**, parce qu'une liste est finie par construction. On y reviendra en
séance 9 : c'est ce qui dispense de fournir un variant.

## Deux autres fonctions à connaître

Elles reviennent constamment, et elles étaient à votre épreuve.

### La concaténation

```whyml
function append (l1 l2: list 'a) : list 'a =
  match l1 with
  | Nil      -> l2
  | Cons x r -> Cons x (append r l2)
  end
```

On filtre sur **`l1`** — le premier argument — et l'on reconstruit en recollant
les éléments devant.

*Exemple :* `append (Cons 1 Nil) (Cons 2 Nil)` donne `Cons 1 (Cons 2 Nil)`.

### Le renversement

```whyml
function reverse (l: list 'a) : list 'a =
  match l with
  | Nil      -> Nil
  | Cons x r -> append (reverse r) (Cons x Nil)
  end
```

On renverse le reste, puis on colle le premier élément **à la fin**.

## D'autres types inductifs

Le principe ne se limite pas aux listes. Un **arbre binaire** :

```whyml
type arbre 'a = Feuille | Noeud (arbre 'a) 'a (arbre 'a)
```

Deux constructeurs. `Noeud` prend **deux sous-arbres** et une valeur.

```whyml
function taille (a: arbre 'a) : int =
  match a with
  | Feuille      -> 0
  | Noeud g _ d  -> 1 + taille g + taille d
  end
```

Ou une **expression arithmétique** :

```whyml
type expr = Const int | Plus expr expr | Mult expr expr

function eval (e: expr) : int =
  match e with
  | Const n  -> n
  | Plus a b -> eval a + eval b
  | Mult a b -> eval a * eval b
  end
```

**La structure du `match` reproduit toujours celle de la définition du type.**
Un cas par constructeur, un appel récursif par sous-structure.

C'est mécanique. Une fois le type écrit, la forme de la fonction est imposée.

## L'exhaustivité est obligatoire

Que se passe-t-il si l'on oublie un cas ?

```whyml
function taille (a: arbre 'a) : int =
  match a with
  | Noeud g _ d -> 1 + taille g + taille d
  end                                          (* et Feuille ? *)
```

À l'exécution, la fonction planterait sur une feuille. Mais en logique, c'est pire :
la fonction devient **partielle**, c'est-à-dire non définie sur tout son domaine.

Or **Why3 exige que les fonctions logiques soient totales**. Sinon, un énoncé comme
`forall a. taille a >= 0` n'aurait pas de sens : que vaudrait-il sur une feuille ?

Why3 **refuse donc un `match` non exhaustif**. Il engendre une obligation de preuve
d'exhaustivité qui échoue.

**Un cas par constructeur. Toujours.**
MD,
                'recap' => <<<'MD'
Un **type inductif** se définit par ses **constructeurs** :

```whyml
type list 'a  = Nil | Cons 'a (list 'a)
type arbre 'a = Feuille | Noeud (arbre 'a) 'a (arbre 'a)
```

**Les trois propriétés garanties :** **exhaustivité** (tout vient d'un
constructeur), **disjonction** (deux constructeurs distincts donnent des valeurs
distinctes), **injectivité** (`Cons x r = Cons y s` implique `x = y` et `r = s`).

**Le filtrage** : un cas par constructeur, terminé par `end`.

```whyml
function length (l: list 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + length r
  end
```

**Une valeur par constructeur.** `length Nil = 0` — et non `length Nil = Nil = 0`,
qui compare une liste à un entier.

**Le `match` doit être exhaustif** : Why3 exige des fonctions totales.
MD,
            ],

            /* ================= Séance 9 ================= */
            [
                'title' => 'Récurrence et induction structurelle',
                'chapitre' => 'Induction',
                'duree_min' => 35,
                'prerequis' => "La séance 8 : les types inductifs et le filtrage.",
                'intro' => <<<'MD'
On sait définir des fonctions sur les listes. Il faut maintenant **prouver** des
propriétés à leur sujet.

L'outil s'appelle l'**induction**. Vous en connaissez peut-être une forme sous le
nom de **récurrence** — celle qu'on fait sur les entiers. On va voir que c'est un
cas particulier d'une idée plus générale.

Et surtout, on va voir la technique qui débloque les preuves difficiles : le
**renforcement**. C'est contre-intuitif, alors on prendra le temps.
MD,
                'body' => <<<'MD'
## La récurrence sur les entiers

Commençons par ce que vous connaissez peut-être déjà.

Pour démontrer qu'une propriété `P(n)` est vraie pour tout entier `n`, il suffit de
démontrer deux choses :

```
P(0)                        le cas de base
∀n, P(n) ⇒ P(n+1)          le pas
———————————————————
∀n ∈ ℕ, P(n)
```

L'image classique est celle des dominos. Si le premier tombe, et si chaque domino
fait tomber le suivant, alors tous tombent.

### Un exemple, rédigé comme on l'attend

**Démontrons que `1 + 2 + … + n = n(n+1)/2`.**

**Soit P(n) la propriété** : « `1 + 2 + … + n = n(n+1)/2` ».

*Cas de base.* Pour `n = 1` : la somme vaut 1, et `1 × 2 / 2 = 1`. ✅

*Hypothèse de récurrence.* Supposons P(n) vraie pour un `n ≥ 1` fixé.

*Pas.*
```
1 + 2 + … + n + (n+1)
  = n(n+1)/2 + (n+1)            [par hypothèse de récurrence]
  = (n+1)(n/2 + 1)
  = (n+1)(n+2)/2
```
C'est bien P(n+1). ✅

*Conclusion.* Par récurrence, P(n) est vraie pour tout `n ≥ 1`. ∎

**Notez la structure en cinq temps.** Énoncer P(n) en toutes lettres, cas de base,
hypothèse, pas, conclusion. Et surtout : **la mention « par hypothèse de
récurrence » à l'endroit exact où elle sert**.

Une preuve où l'hypothèse n'apparaît jamais n'est pas une récurrence.

## La récurrence forte

Parfois, P(n−1) ne suffit pas : on a besoin de **tous** les rangs inférieurs.

```
∀n, ( ∀k < n, P(k) ) ⇒ P(n)
————————————————————————————
∀n ∈ ℕ, P(n)
```

**Le cas de base y est inclus** : pour `n = 0`, l'hypothèse `∀k < 0` porte sur un
ensemble vide, donc elle est vraie. On n'a rien à vérifier séparément.

### Quand en a-t-on besoin ?

**Démontrons que tout entier `n ≥ 2` s'écrit comme produit de nombres premiers.**

**Soit P(n)** : « n se décompose en produit de premiers ».

*Hypothèse forte.* Supposons P(k) vraie pour tout `2 ≤ k < n`.

*Deux cas.*
- Si **n est premier**, il est son propre produit. ✅
- Sinon, `n = a × b` avec `2 ≤ a < n` et `2 ≤ b < n`.
  **Par hypothèse forte appliquée à `a` et à `b`**, chacun se décompose.
  Leur concaténation décompose n. ✅

*Conclusion.* Par récurrence forte, tout `n ≥ 2` se décompose. ∎

**Pourquoi la simple ne suffit pas.** Pour `n = 100 = 4 × 25`, on a besoin de P(4)
et de P(25) — pas de P(99). La récurrence simple ne donne que le rang précédent.

**La règle :** dès que le pas invoque un rang autre que `n−1`, il faut la forte.

## L'induction structurelle

Voici la généralisation.

La récurrence marche sur ℕ parce que tout entier s'obtient à partir de `0` en
appliquant `+1` un nombre fini de fois. Autrement dit, ℕ est un **type inductif** à
deux constructeurs : zéro, et successeur.

Or on a vu en séance 8 que les listes et les arbres sont aussi des types inductifs.
**Le même raisonnement s'y applique.**

### Sur les listes

Deux constructeurs, donc **deux cas** :

```
P(Nil)
∀x ∀l,  P(l) ⇒ P(Cons x l)
——————————————————————————
∀l, P(l)
```

### Sur les arbres

```
P(Feuille)
∀g ∀x ∀d,  P(g) ∧ P(d) ⇒ P(Noeud g x d)
————————————————————————————————————————
∀a, P(a)
```

**Deux hypothèses** dans le cas inductif — une par sous-arbre. C'est l'erreur
classique de n'en utiliser qu'une.

### Le principe général

> **Un cas par constructeur. Dans le cas d'un constructeur `C`, on dispose de
> l'hypothèse P pour chacun de ses arguments de type inductif.**

Il n'y a rien à deviner sur la forme de la preuve : **elle est dictée par la
définition du type**.

## Une preuve complète

**Démontrons que `length (append l1 l2) = length l1 + length l2`.**

**Soit P(l1)** : « `∀l2, length (append l1 l2) = length l1 + length l2` ».

Remarquez la quantification sur `l2` **à l'intérieur** de P. L'induction porte sur
`l1` seul, parce que c'est sur lui que les deux fonctions filtrent.

*Cas `Nil`.*
```
length (append Nil l2)
  = length l2                        [déf. append, cas Nil]
  = 0 + length l2                    [arithmétique]
  = length Nil + length l2           [déf. length, cas Nil]
```
✅

*Cas `Cons x r`.* Hypothèse : `∀l2, length (append r l2) = length r + length l2`.
```
length (append (Cons x r) l2)
  = length (Cons x (append r l2))    [déf. append, cas Cons]
  = 1 + length (append r l2)         [déf. length, cas Cons]
  = 1 + (length r + length l2)       [par hypothèse d'induction]
  = (1 + length r) + length l2       [associativité]
  = length (Cons x r) + length l2    [déf. length, cas Cons]
```
✅

*Conclusion.* Par induction structurelle sur `l1`, la propriété vaut pour toutes
listes `l1` et `l2`. ∎

**Ce qui rapporte les points :** chaque égalité est **justifiée en marge**, et
l'endroit où sert l'hypothèse est **signalé explicitement**.

## Le renforcement

Voici la technique la plus utile de la séance, et la plus contre-intuitive.

### Le problème

Considérons la version à accumulateur de la somme :

```whyml
function sommeAcc (l: list int) (acc: int) : int =
  match l with
  | Nil      -> acc
  | Cons x r -> sommeAcc r (acc + x)
  end
```

On veut prouver que `sommeAcc l 0 = somme l`.

**Essayons.** Soit P(l) : « `sommeAcc l 0 = somme l` ».

*Cas `Nil`.* `sommeAcc Nil 0 = 0 = somme Nil`. ✅

*Cas `Cons x r`.* Hypothèse : `sommeAcc r 0 = somme r`.
```
sommeAcc (Cons x r) 0
  = sommeAcc r (0 + x)
  = sommeAcc r x
```
Et maintenant ? L'hypothèse parle de `sommeAcc r 0`, pas de `sommeAcc r x`.

**On est bloqué.**

### La solution : demander plus

C'est le moment contre-intuitif. Au lieu d'affaiblir la propriété, on la **renforce**.

Calculons à la main pour deviner la forme générale :
`sommeAcc [1;2] 5 = sommeAcc [2] 6 = sommeAcc [] 8 = 8`.
Et `somme [1;2] = 3`. On observe `8 = 3 + 5`.

**Soit P(l)** : « `∀acc, sommeAcc l acc = somme l + acc` ».

C'est une propriété **plus forte** : elle vaut pour tout accumulateur, pas
seulement pour zéro.

*Cas `Nil`.*
```
sommeAcc Nil acc = acc = 0 + acc = somme Nil + acc
```
✅

*Cas `Cons x r`.* Hypothèse : `∀acc, sommeAcc r acc = somme r + acc`.

Soit `acc` quelconque.
```
sommeAcc (Cons x r) acc
  = sommeAcc r (acc + x)             [déf. sommeAcc]
  = somme r + (acc + x)              [par hypothèse, appliquée à acc + x]
  = (x + somme r) + acc              [commutativité, associativité]
  = somme (Cons x r) + acc           [déf. somme]
```
✅

**Ça passe.** Et le résultat voulu s'obtient en prenant `acc = 0`.

### Pourquoi ça marche

Voici le point à comprendre.

Une propriété plus forte est **plus difficile à démontrer**… mais elle donne aussi
une **hypothèse plus riche** au moment du pas.

Dans la version faible, l'hypothèse ne parlait que de `acc = 0`. Dans la version
forte, elle vaut pour **tout** `acc` — et l'on a pu l'appliquer à `acc + x`.

**On a gagné plus qu'on n'a perdu.**

> ### Quand une récurrence bloque, ne vous acharnez pas sur le calcul.
> ### Renforcez la propriété.

C'est le réflexe à acquérir. Neuf fois sur dix, une preuve par induction qui ne
passe pas a besoin d'un renforcement — souvent une variable à quantifier
universellement à l'intérieur de P.
MD,
                'recap' => <<<'MD'
**Récurrence simple :** `P(0)` et `∀n, P(n) ⇒ P(n+1)`.

**Récurrence forte :** `∀n, (∀k < n, P(k)) ⇒ P(n)`. Le cas de base y est inclus.
**Nécessaire dès que le pas invoque un rang autre que n−1.**

**Induction structurelle** — la généralisation. **Un cas par constructeur**, avec
une hypothèse par argument de type inductif.

```
Listes :  P(Nil)  et  ∀x l, P(l) ⇒ P(Cons x l)
Arbres :  P(Feuille)  et  ∀g x d, P(g) ∧ P(d) ⇒ P(Noeud g x d)     ← DEUX hypothèses
```

**La rédaction en cinq temps :** énoncer P en toutes lettres · cas de base ·
hypothèse · pas, avec **« par hypothèse d'induction »** à l'endroit exact ·
conclusion.

**Le renforcement.** Quand la preuve bloque, **rendez la propriété plus forte** —
typiquement en quantifiant universellement une variable à l'intérieur de P.
Plus dur à démontrer, mais l'hypothèse devient plus riche, et l'on gagne au change.
MD,
            ],

            /* ================= Séance 10 ================= */
            [
                'title' => 'Les contrats : précondition et postcondition',
                'chapitre' => 'Contrats',
                'duree_min' => 30,
                'prerequis' => "Les séances 7 et 9 : la syntaxe Why3, et l'induction.",
                'intro' => <<<'MD'
On revient au programme. En séance 1, j'ai posé deux mots : précondition et
postcondition. Il est temps de les écrire vraiment.

Un **contrat**, c'est la répartition des responsabilités entre celui qui appelle
une fonction et la fonction elle-même. Et c'est ce qui rend une preuve possible :
sans contrat, il n'y a rien à démontrer.
MD,
                'body' => <<<'MD'
## Le contrat, comme un contrat

L'analogie est littérale.

Vous confiez un colis à un transporteur. Le contrat dit :

> **Vous vous engagez à** — que le colis fasse moins de 20 kg et soit correctement
> emballé.
>
> **Le transporteur s'engage à** — le livrer sous 48 heures, intact.

Si vous confiez 40 kg, le transporteur n'est tenu à rien. Vous n'avez pas respecté
votre part.

En programmation, c'est identique :

- La **précondition** est ce que **l'appelant** garantit. Sa responsabilité.
- La **postcondition** est ce que **la fonction** garantit en retour. Sa
  responsabilité.

Si la précondition n'est pas respectée, la fonction ne promet **rien**. Elle peut
planter, rendre n'importe quoi — ce n'est pas sa faute.

## En Why3

```whyml
let division (a b: int) : int
  requires { b <> 0 }
  ensures  { result * b <= a < (result + 1) * b }
= a / b
```

Trois mots-clés :

- **`requires`** — la précondition.
- **`ensures`** — la postcondition. `result` désigne la valeur rendue.
- **`<>`** — le « différent de » de Why3.

`b <> 0` est la responsabilité de l'appelant : diviser par zéro n'a pas de sens,
et la fonction n'a pas à le gérer.

## Une spécification complète

Revenons à l'exemple de la séance 1, parce qu'il est central.

**La spécification insuffisante :**

```whyml
let maximum (a: array int) : int
  requires { length a > 0 }
  ensures  { forall i. 0 <= i < length a -> result >= a[i] }
```

Elle est satisfaite par :

```whyml
= 1000000
```

Un million est bien supérieur à tous les éléments. **Prouvé correct, et absurde.**

**Ce qui manquait :**

```whyml
  ensures  { exists i. 0 <= i < length a /\ result = a[i] }
```

Le résultat doit être **un élément du tableau**.

### La méthode pour ne plus se faire avoir

Après avoir écrit une postcondition, posez-vous une seule question :

> **Puis-je écrire une fonction absurde qui la satisfait ?**

Si oui, la spécification est incomplète. Cherchez ce qui manque.

Deux réflexes qui couvrent presque tous les cas :

- Si le résultat doit **venir de l'entrée**, dites-le : `exists i. result = a[i]`.
- Si le résultat doit être **unique**, dites-le. C'est souvent une borne :
  pour un reste de division, `0 <= r < |b|`.

## Un exemple qui montre l'unicité

Spécifions la division euclidienne, qui rend un quotient **et** un reste.

```whyml
let divmod (a b: int) : (int, int)
  requires { b <> 0 }
  ensures  { let (q, r) = result in
               a = b * q + r
            /\ 0 <= r < abs b }
```

**Pourquoi les deux conjoints sont indispensables.**

Le premier, `a = b * q + r`, est l'identité de la division. Mais à lui seul, il est
satisfait par une infinité de couples : `q = 0, r = a` fonctionne pour tout `a`.

Le second, `0 <= r < abs b`, **force l'unicité**. Il n'existe qu'un seul couple
`(q, r)` vérifiant les deux.

C'est un cas d'école de spécification incomplète, et il tombe.

## Spécifier une fonction sur les listes

Reprenons `append` de la séance 8, et spécifions-la.

```whyml
let append (l1 l2: list 'a) : list 'a
  ensures { length result = length l1 + length l2 }
```

C'est un début, mais insuffisant : une fonction qui rendrait une liste de la bonne
longueur, remplie de n'importe quoi, satisferait cette postcondition.

Une spécification complète devrait dire que les éléments sont ceux de `l1` puis
ceux de `l2`, dans l'ordre. C'est plus lourd à écrire, et c'est pourquoi on
préfère souvent définir `append` comme une `function` logique — auquel cas la
définition **est** la spécification.

**Retenez cette alternative :**

- Une **`function`** logique se définit par équations. La définition tient lieu de
  spécification.
- Un **`let`** est du code. Il faut lui donner un contrat.

## Les obligations de preuve

Quand vous écrivez un `let` avec `requires` et `ensures`, Why3 en déduit
mécaniquement une liste de formules à démontrer : les **obligations de preuve**.

Le programme est prouvé correct quand **toutes** sont démontrées.

Qui les engendre ? **Why3**, en appliquant les règles de la logique de Hoare —
c'est la séance 11.

Qui les démontre ? Des **solveurs automatiques** : Alt-Ergo, Z3, CVC4. Ceux dont on
a parlé en séance 6. Ce qu'aucun ne parvient à décharger reste à l'utilisateur,
qui doit soit le démontrer à la main, soit aider l'outil en ajoutant des
**assertions** intermédiaires.

```whyml
assert { 0 <= i < length a };     (* une étape intermédiaire, à démontrer aussi *)
```

## Ce qu'on attend de vous à l'examen

Pour une question « spécifiez la fonction f » :

- [ ] Une **précondition** qui couvre tous les cas où le code planterait.
- [ ] Une **postcondition complète**, qui exclut les résultats absurdes.
- [ ] Des **types** sur tous les paramètres.
- [ ] Le mot-clé **`result`** pour désigner la valeur rendue.

Et surtout : **le test de la fonction absurde**. Avant de rendre, essayez de casser
votre propre spécification.
MD,
                'recap' => <<<'MD'
Un **contrat** répartit les responsabilités.

- **Précondition** (`requires`) — ce que **l'appelant** garantit.
- **Postcondition** (`ensures`) — ce que **la fonction** garantit. `result` désigne
  la valeur rendue.

Si la précondition n'est pas respectée, **la fonction ne promet rien**.

**Le test à appliquer systématiquement :**

> Puis-je écrire une fonction absurde qui satisfait ma postcondition ?

Si oui, elle est incomplète. Deux réflexes :
- le résultat vient de l'entrée → `exists i. result = a[i]` ;
- le résultat doit être unique → ajoutez une borne, comme `0 <= r < abs b`.

**Les obligations de preuve** sont engendrées par **Why3** et déchargées par les
**solveurs** (Alt-Ergo, Z3, CVC4). Ce qui résiste se traite à la main, ou en
ajoutant des `assert` intermédiaires.
MD,
            ],

            /* ================= Séance 11 ================= */
            [
                'title' => 'La logique de Hoare',
                'chapitre' => 'Hoare',
                'duree_min' => 35,
                'prerequis' => "La séance 10 : précondition et postcondition.",
                'intro' => <<<'MD'
Voici le cœur théorique du module.

La **logique de Hoare** est un système de règles qui permet de démontrer qu'un
programme respecte son contrat — de façon **mécanique**, en assemblant des briques
élémentaires.

Quatre règles. Elles suffisent à prouver n'importe quel programme impératif simple.
MD,
                'body' => <<<'MD'
## Le triplet

L'objet de base s'écrit :

```
{ P }  S  { Q }
```

et se lit :

> **Si** l'état vérifie `P` avant d'exécuter `S`, **et si** `S` termine,
> **alors** l'état vérifie `Q` après.

`P` est la **précondition**, `S` le **programme**, `Q` la **postcondition**.

**Deux « si ».** Le second est celui qu'on oublie : le triplet ne garantit **pas**
que `S` termine. C'est la **correction partielle** de la séance 1.

Conséquence amusante et instructive : un programme qui boucle indéfiniment satisfait
`{ P } S { Q }` pour **n'importe quels** P et Q. Il ne rend jamais de résultat
faux, puisqu'il n'en rend aucun.

D'où la nécessité de prouver la terminaison **séparément**. Ce sera la séance 12.

## Règle 1 — l'affectation

C'est la règle la plus surprenante, alors on prend le temps.

```
———————————————————————————
{ Q[x ← E] }   x := E   { Q }
```

`Q[x ← E]` se lit « Q dans laquelle on a remplacé x par E ».

**Un exemple pour comprendre.** Je veux qu'après `x := x + 1`, on ait `x = 5`.
Que fallait-il avant ?

Il fallait que `x + 1 = 5`, c'est-à-dire `x = 4`.

```
{ x + 1 = 5 }   x := x + 1   { x = 5 }
```

Et en effet, `Q` est `x = 5`, donc `Q[x ← x+1]` est `x + 1 = 5`.

**Pourquoi à rebours ?** Parce qu'on raisonne **de la postcondition vers la
précondition**. On sait où l'on veut arriver, la règle dit d'où il faut partir.

C'est déroutant au début. Retenez l'exemple : `{x+1 = 5} x := x+1 {x = 5}`.
Il suffit à reconstruire la règle.

**L'erreur classique** est de substituer dans la précondition. Ça donnerait
`{x = 5} x := x+1 {x+1 = 5}`, ce qui est faux : après l'affectation, `x` vaut 6,
donc `x + 1` vaut 7, pas 5.

## Règle 2 — la séquence

```
{P} S₁ {Q}      {Q} S₂ {R}
———————————————————————————
      {P} S₁ ; S₂ {R}
```

Deux triplets qui se **raccordent** sur `Q` se composent.

C'est la règle qui permet d'enchaîner les instructions. Le `Q` intermédiaire doit
être **exactement le même** des deux côtés.

## Règle 3 — la conditionnelle

```
{P ∧ b} S₁ {Q}      {P ∧ ¬b} S₂ {Q}
———————————————————————————————————
{P} if b then S₁ else S₂ fi {Q}
```

On prouve les deux branches séparément. Dans la branche « then », on **sait en
plus** que `b` est vraie ; dans la branche « else », que `b` est fausse.

Les deux doivent aboutir à la **même** postcondition `Q`.

## Règle 4 — la boucle

La règle centrale, et la plus difficile.

```
        {I ∧ b} S {I}
———————————————————————————
{I} while b do S od {I ∧ ¬b}
```

`I` s'appelle l'**invariant**. C'est une propriété qui reste vraie **avant et après
chaque tour** de boucle.

Lisons la règle. Si le corps `S` préserve `I` — c'est-à-dire que partant de `I ∧ b`
il aboutit à `I` — alors la boucle entière préserve `I`. Et en sortie, on dispose
de `I` **et** de la négation de la garde.

**Le `¬b` en sortie est essentiel.** C'est lui qui, combiné à l'invariant, permet
de conclure.

## Règle 5 — la conséquence

Une règle d'ajustement, mais indispensable.

```
P ⇒ P'      {P'} S {Q'}      Q' ⇒ Q
———————————————————————————————————
            {P} S {Q}
```

On peut toujours **renforcer la précondition** et **affaiblir la postcondition**.

En clair : si un programme marche en supposant peu, il marche aussi en supposant
plus. Et s'il garantit beaucoup, il garantit aussi moins.

C'est cette règle qui permet de recoller les morceaux quand les formules ne
s'emboîtent pas exactement.

## Une preuve complète

Prouvons le calcul de la factorielle.

```
{ n ≥ 0 }
i := 0;
f := 1;
while i < n do
    i := i + 1;
    f := f * i
od
{ f = n! }
```

### Étape 1 — trouver l'invariant

C'est **la** difficulté. Voici la recette générale :

> **L'invariant est la postcondition, restreinte à la portion déjà traitée.**

La postcondition dit `f = n!`. À mi-parcours, quand on en est à `i`, on a calculé
`f = i!`.

D'où :

```
I ≡ i ≤ n ∧ f = i!
```

**Pourquoi la borne `i ≤ n` ?** Sans elle, la sortie de boucle donnerait
`¬(i < n) ∧ f = i!`, soit `i ≥ n ∧ f = i!`. On ne pourrait pas conclure `i = n`,
donc pas `f = n!`.

**C'est l'erreur la plus fréquente : oublier la borne.** Un invariant sans borne
est préservé, mais ne permet pas de conclure.

### Étape 2 — l'établissement

Après `i := 0; f := 1` : on a `i = 0` et `f = 1`.

- `i ≤ n` : oui, car `0 ≤ n` d'après la précondition.
- `f = i!` : oui, car `1 = 0!`.

`I` est établi. ✅

### Étape 3 — la préservation

Supposons `I ∧ i < n`, et exécutons le corps.

Après `i := i + 1`, notons `i' = i + 1`. De `i < n` on tire `i' ≤ n`.
Et `f` vaut encore l'ancien `i!`, c'est-à-dire `(i' − 1)!`.

Après `f := f * i'` : `f' = (i'−1)! × i' = i'!`.

Donc `i' ≤ n ∧ f' = i'!`, c'est-à-dire `I`. ✅

### Étape 4 — la conclusion

En sortie, on dispose de `¬(i < n) ∧ I`, soit :

```
i ≥ n  ∧  i ≤ n  ∧  f = i!
```

De `i ≥ n` et `i ≤ n` on tire **`i = n`**, donc **`f = n!`**. ✅

C'est la postcondition. ∎

## Le tableau de preuve

À l'examen, on demande souvent de présenter la preuve sous forme de **tableau à
trois colonnes** : numéro, triplet, justification.

| N° | Triplet | Justification |
|---|---|---|
| 1 | {i < n ∧ f = i!} i := i+1 {i ≤ n ∧ f = (i−1)!} | (assignment) |
| 2 | {i ≤ n ∧ f = (i−1)!} f := f∗i {i ≤ n ∧ f = i!} | (assignment) |
| 3 | {i < n ∧ f = i!} i := i+1; f := f∗i {I} | **(sequence) 1 2** |
| 4 | n ≥ 0 ∧ i = 0 ∧ f = 1 ⇒ I | OK (arithmétique) |
| 5 | {I} while i < n do … od {¬(i<n) ∧ I} | **(while) 3** |
| 6 | ¬(i<n) ∧ I ⇒ f = n! | OK |
| 7 | {I} while … od {f = n!} | **(consequence) 5 6** |

**Ce qui rapporte les points, c'est la troisième colonne.**

Trois exigences :

- Le **nom exact** de la règle, entre parenthèses, dans la notation du cours :
  `(assignment)`, `(sequence)`, `(while)`, `(consequence)`.
- Les **numéros des lignes** auxquelles la règle s'applique.
- Rien de vague. « on applique la règle » ne vaut rien.

## Les quatre éléments d'une preuve de boucle

Pour toute question sur une boucle, votre copie doit contenir :

1. **L'invariant**, énoncé explicitement, **avec sa borne**.
2. Son **établissement** avant l'entrée.
3. Sa **préservation** par le corps.
4. La **conclusion** tirée de `¬garde ∧ I`.

Quatre éléments. S'il en manque un, la preuve est incomplète.
MD,
                'recap' => <<<'MD'
Un **triplet** `{P} S {Q}` : *si* P avant **et si** S termine, *alors* Q après.
C'est la **correction partielle** — il ne dit rien de la terminaison.

**Les règles :**

```
Affectation   { Q[x ← E] }  x := E  { Q }        ← substitution dans la POSTcondition

Séquence      {P} S₁ {Q}   {Q} S₂ {R}
              ————————————————————————
                  {P} S₁ ; S₂ {R}

Conditionnelle  {P ∧ b} S₁ {Q}   {P ∧ ¬b} S₂ {Q}
                ————————————————————————————————
                {P} if b then S₁ else S₂ fi {Q}

Boucle              {I ∧ b} S {I}
              ———————————————————————————
              {I} while b do S od {I ∧ ¬b}

Conséquence   P ⇒ P'   {P'} S {Q'}   Q' ⇒ Q
              ——————————————————————————————
                        {P} S {Q}
```

**Trouver l'invariant :** c'est **la postcondition restreinte à la portion déjà
traitée**, plus une **borne** sur le compteur. Oublier la borne est l'erreur la
plus fréquente.

**Les quatre éléments d'une preuve de boucle :** l'invariant, son établissement,
sa préservation, la conclusion tirée de `¬garde ∧ I`.

**Dans un tableau de preuve, la colonne des justifications rapporte les points :**
nom exact de la règle et numéros des lignes.
MD,
            ],

            /* ================= Séance 12 ================= */
            [
                'title' => 'Terminaison, variant, et composer une copie',
                'chapitre' => 'Hoare',
                'duree_min' => 35,
                'prerequis' => "Toutes les séances précédentes. C'est la dernière.",
                'intro' => <<<'MD'
Il reste une chose à démontrer : que le programme **s'arrête**.

Puis on assemblera tout, et je vous donnerai la méthode pour composer votre copie
le 26 août au soir — après trois heures d'AGC dans l'après-midi.
MD,
                'body' => <<<'MD'
## Le problème de la terminaison

Reprenons la factorielle. On a prouvé que **si** elle termine, `f = n!`.

Mais termine-t-elle ? Regardons la boucle :

```
while i < n do i := i + 1; f := f * i od
```

Intuitivement, oui : `i` augmente, donc finira par atteindre `n`. Mais
« intuitivement » ne suffit pas dans une preuve.

## Le variant

L'outil s'appelle le **variant**. C'est une **expression** qui doit vérifier trois
propriétés :

1. **À valeurs dans un ensemble bien fondé** — typiquement ℕ, les entiers naturels.
   « Bien fondé » signifie qu'on ne peut pas descendre indéfiniment.
2. **Minorée** tant que la garde est vraie.
3. **Strictement décroissante** à chaque tour.

Si les trois sont vérifiées, la boucle **termine nécessairement** : une suite
d'entiers positifs strictement décroissante ne peut pas être infinie.

### Pour la factorielle

```
V = n − i
```

Vérifions les trois :

1. **Entier** — `n` et `i` sont des entiers, donc `n − i` aussi. ✅
2. **Minoré** — tant que la garde `i < n` est vraie, on a `n − i > 0`. ✅
3. **Strictement décroissant** — à chaque tour, `i` augmente de 1, donc `n − i`
   diminue de 1. ✅

**La boucle termine.** Combinée à la correction partielle de la séance 11, on
obtient la **correction totale**. ∎

**Attention à la rédaction.** Donner l'expression ne suffit pas : il faut **énoncer
les trois propriétés** et les vérifier. C'est ce qui est demandé.

## En Why3

```whyml
let factorielle (n: int) : int
  requires { n >= 0 }
  ensures  { result = fact n }
= let ref i = 0 in
  let ref f = 1 in
  while i < n do
    invariant { 0 <= i <= n }
    invariant { f = fact i }
    variant   { n - i }
    i <- i + 1;
    f <- f * i
  done;
  f
```

Quatre annotations, et chacune correspond à ce qu'on vient de voir :

- `requires` — la précondition.
- `ensures` — la postcondition.
- `invariant` — l'invariant de boucle. On peut en écrire **plusieurs**, ils se
  conjuguent.
- `variant` — la terminaison.

**Sans `variant`, Why3 ne prouve que la correction partielle.**

## La récursion aussi a besoin d'un variant

Une fonction récursive peut boucler indéfiniment, exactement comme un `while`.

```whyml
let rec pgcd (a b: int) : int
  requires { a >= 0 /\ b >= 0 }
  variant  { b }
= if b = 0 then a
  else pgcd b (mod a b)
```

Le variant est `b`. Vérifions :

1. Entier, et `b ≥ 0` par la précondition. ✅
2. Dans la branche récursive, `b ≠ 0`, donc `b ≥ 1 > 0`. ✅
3. L'appel récursif passe `mod a b` en second argument. Or `0 ≤ mod a b < b`
   quand `b > 0`. Le nouveau variant est donc strictement plus petit. ✅

**Une exception importante.** La **récursion structurelle** n'a pas besoin de
variant :

```whyml
function length (l: list 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + length r      (* r est un sous-terme de l *)
  end
```

L'appel porte sur `r`, un **sous-terme syntaxique** de l'argument. Un type inductif
n'ayant aucun élément infini, la descente est nécessairement finie, et Why3 le
reconnaît tout seul.

> **La règle :** dès que l'argument de l'appel récursif n'est pas un **sous-terme
> syntaxique** de l'argument d'entrée, un variant est obligatoire.

## Récapitulatif du module

Vous avez suivi douze séances. Voici la carte.

| Séances | Ce qu'on a construit |
|---|---|
| 1 | Pourquoi prouver, et la spécification |
| 2 – 4 | La logique propositionnelle |
| 5 | La logique du premier ordre |
| 6 | Théories et solveurs |
| 7 – 8 | Écrire du Why3, les types inductifs |
| 9 | Récurrence et induction |
| 10 | Les contrats |
| 11 – 12 | Hoare, invariant, variant |

Tout se tient : la logique sert à écrire les spécifications, les spécifications
engendrent les obligations, et Hoare fournit les règles pour les démontrer.

## Composer votre copie le 26 août

L'épreuve dure **trois heures, de 20 h à 23 h**, après trois heures d'AGC
l'après-midi. Vous serez fatiguée. Voici comment structurer.

### La répartition du temps

D'après le sujet de mai, quatre exercices. En supposant un barème équilibré :

| | Durée | Cumul |
|---|---|---|
| Lecture du sujet en entier | 5 min | 5 min |
| Exercice 1 — formalisation | 30 min | 35 min |
| Exercice 2 — Why3, propositions | 35 min | 1 h 10 |
| Exercice 3 — prédicats | 40 min | 1 h 50 |
| Exercice 4 — listes et induction | 50 min | 2 h 40 |
| Relecture | 20 min | 3 h |

**Commencez par l'exercice 1.** C'est le plus court et le plus sûr — cinq
formalisations, dix minutes si le tableau de la séance 3 est en tête. Il met en
confiance.

### Les cinq vérifications de la relecture

Gardez vingt minutes, et passez la copie à ce filtre :

1. **Une seule formule par question de formalisation.** Aucune paire superposée.
2. **Tout `lemma` est clos** — un `forall` devant chaque variable.
3. **Les inégalités testées sur le cas de base** — liste vide, `n = 0`.
4. **Chaque étape de preuve porte sa justification** — nom de règle et numéros.
5. **Aucune question laissée vide.** Une définition juste vaut mieux que rien.

### Les trois choses à retenir de tout le module

**Un. Une question, une réponse.** C'est ce qui vous a coûté le plus de points en
mai, et c'est le plus facile à corriger. Le correcteur ne trie pas à votre place.

**Deux. Tout lemme est clos.** Une variable libre n'a pas de valeur de vérité.
Le « + forall » était la seule chose qui séparait votre réponse de la bonne.

**Trois. L'invariant, c'est la postcondition restreinte — plus la borne.**
Cette recette vous donne l'invariant de n'importe quelle boucle simple, et l'oubli
de la borne est l'erreur la plus fréquente.

Bon courage pour le 26 août au soir.
MD,
                'recap' => <<<'MD'
**Le variant** prouve la terminaison. Trois propriétés à **énoncer et vérifier** :

1. À valeurs dans un ensemble **bien fondé** (typiquement ℕ).
2. **Minoré** tant que la garde est vraie.
3. **Strictement décroissant** à chaque tour.

*Pour la factorielle :* `V = n − i`. *Pour le pgcd d'Euclide :* `V = b`.

**Correction partielle + terminaison = correction totale.**

**La récursion structurelle** — appel sur un **sous-terme syntaxique** — n'a pas
besoin de variant. Dès qu'on s'en écarte, il est obligatoire.

**Les cinq vérifications de la relecture :**

1. Une seule formule par question.
2. Tout `lemma` clos par un `forall`.
3. Inégalités testées sur le cas de base.
4. Chaque étape de preuve justifiée par une règle nommée.
5. Aucune question vide.
MD,
            ],
        ];
    }
}