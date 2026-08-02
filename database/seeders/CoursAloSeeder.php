<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours d'ALO, de A à Z.
 *
 * Écrit comme un enseignant parle : on part de ce que l'élève connaît, on
 * introduit un mot à la fois, on revient sur ce qui a été vu. Les séances se
 * suivent dans l'ordre et chacune ne suppose que la précédente.
 *
 * Ce n'est pas une fiche de révision : c'est le cours qu'on n'a pas suivi.
 */
class CoursAloSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'ALO')->first();

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
                $seance + [
                    'chapter_id' => $chapter?->id,
                    'position' => $i + 1,
                ]
            );
        }
    }

    /* ==================================================================== */

    private function seances(): array
    {
        return [

            /* ================= Séance 1 ================= */
            [
                'title' => 'Pourquoi l\'objet existe',
                'chapitre' => 'C1-Objet',
                'duree_min' => 25,
                'prerequis' => "Aucun. C'est la première séance : on part de rien.",
                'intro' => <<<'MD'
Bonjour.

Avant de parler d'objets, de classes ou de patrons, je voudrais qu'on comprenne
**quel problème tout cela résout**. Parce que si on ne voit pas le problème,
la solution paraît arbitraire — et c'est comme ça qu'on apprend par cœur sans
comprendre.

Aujourd'hui, on ne va écrire presque aucun code. On va regarder un programme qui
grossit, voir où il casse, et découvrir l'objet comme une réponse à ça.
MD,
                'body' => <<<'MD'
## Un programme qui commence bien

Imaginons qu'on écrive un logiciel pour une petite banque. Au début, c'est simple.
On veut stocker le solde d'un client. Alors on écrit :

```
solde = 1240
```

Une variable. Parfait. Maintenant on veut pouvoir déposer de l'argent :

```
solde = solde + 500
```

Toujours parfait. Le programme fait dix lignes, tout va bien.

## Le premier problème : il y a plusieurs clients

Un deuxième client arrive. On écrit :

```
solde1 = 1240
solde2 = 830
```

Puis un troisième, un dixième, un millième. Vous voyez déjà que ça ne tient pas.
On passe donc à une liste :

```
soldes = [1240, 830, 2100, ...]
```

Mieux. Mais un compte, ce n'est pas qu'un solde. Il y a aussi un numéro, un
titulaire, une date d'ouverture. Alors on fait :

```
soldes     = [1240, 830, 2100]
numeros    = ["FR76...", "FR14...", "FR33..."]
titulaires = ["Ana", "Ben", "Chloé"]
```

**Et là, quelque chose vient de se casser.**

Regardez bien. L'information « le compte d'Ana a 1240 euros » n'est écrite nulle
part. Elle est **implicite** : elle tient au fait que `soldes[0]`, `numeros[0]` et
`titulaires[0]` parlent du même compte. Cette cohérence n'est garantie par rien.

Si quelqu'un trie `soldes` sans trier les deux autres listes, tous les comptes
sont mélangés. Le programme ne plantera pas. Il donnera simplement des résultats
faux, silencieusement.

## L'idée de l'objet, en une phrase

**Rassembler dans un seul paquet ce qui va ensemble.**

Au lieu de trois listes parallèles, on écrit :

```
compte1 = { numero: "FR76...", titulaire: "Ana", solde: 1240 }
compte2 = { numero: "FR14...", titulaire: "Ben", solde:  830 }
```

Un compte est maintenant **une seule chose**. On ne peut plus désynchroniser son
solde de son titulaire, parce qu'ils vivent au même endroit.

C'est déjà 80 % de l'idée. Retenez-la : **un objet, c'est un paquet cohérent**.

## Le deuxième problème : n'importe qui peut tout modifier

On continue. Le logiciel grossit, plusieurs personnes travaillent dessus.
Un jour, quelqu'un écrit quelque part :

```
compte1.solde = 1000000
```

Pas par malveillance — par erreur, ou par facilité. Et le compte d'Ana passe à un
million d'euros sans qu'aucun dépôt n'ait eu lieu.

Le problème n'est pas la ligne elle-même. Le problème, c'est qu'**elle était
possible**.

Dans la vraie vie, on ne modifie pas un solde. On fait un **dépôt**, et le dépôt
vérifie des choses : que le montant est positif, que le compte existe, qu'on trace
l'opération. Le solde n'est que la **conséquence** de l'opération.

## L'encapsulation

D'où la deuxième idée : **cacher les données, et n'y donner accès que par des
opérations qui contrôlent**.

```
compte1.solde              ← interdit
compte1.deposer(500)       ← la seule porte d'entrée
```

`deposer` peut alors refuser un montant négatif, journaliser l'opération, prévenir
la comptabilité. Et surtout : **on ne peut pas contourner ces règles**, parce
qu'il n'y a pas d'autre chemin.

Ce mot — **encapsulation** — désigne exactement ça. Ni plus, ni moins.

Vous entendrez parfois « masquer l'état interne », « protéger les invariants ».
C'est la même chose dite plus savamment. L'idée reste : *les données sont derrière
une porte, et la porte a un gardien*.

## Le troisième problème : on se répète

Le logiciel de la banque grandit encore. On a maintenant des comptes courants,
des comptes épargne, des comptes joints.

Un compte épargne, c'est un compte, plus un taux d'intérêt. Un compte joint, c'est
un compte, plus un second titulaire.

Si on écrit trois paquets complets, on recopie trois fois le numéro, le solde,
le dépôt, le retrait. Et le jour où l'on corrige un bug dans `deposer`, il faut
penser à le corriger trois fois. On oubliera.

D'où la troisième idée : **dire qu'un compte épargne *est un* compte**, et ne
décrire que ce qu'il ajoute.

C'est l'**héritage**. On y reviendra longuement, mais retenez déjà la formule qui
le déclenche : **« est un »**.

## Ce que ça donne

Ces trois idées — regrouper, protéger, factoriser — forment le **modèle objet**.
Tout le reste du module en découle :

- Les **classes** et les **objets** : la séance 2.
- Les **relations** entre eux : la séance 4.
- Les **patrons de conception** : à partir de la séance 7. Ce sont des solutions
  toutes faites à des problèmes qui reviennent tout le temps, et elles pèsent
  **15 points sur 20** à votre épreuve.

## Un mot sur la méthode

Vous allez lire des choses qui paraîtront évidentes. Tant mieux : c'est le signe
que ça rentre. Le piège de ce module n'est pas la difficulté conceptuelle — c'est
le vocabulaire. Une vingtaine de mots précis, qu'il faut employer **exactement**
comme le cours les emploie, parce que le barème les cherche.

Alors à chaque fois que j'introduis un mot en **gras**, arrêtez-vous une seconde
et redites-le dans votre tête avec sa définition. C'est tout ce que je vous demande.
MD,
                'recap' => <<<'MD'
Trois problèmes, trois réponses. C'est tout le modèle objet.

| Problème | Réponse |
|---|---|
| Les données qui vont ensemble se désynchronisent | **L'objet** — un paquet cohérent |
| N'importe qui peut modifier n'importe quoi | **L'encapsulation** — données cachées, accès contrôlé |
| On recopie le même code plusieurs fois | **L'héritage** — « est un » |

Le mot à retenir aujourd'hui : **encapsulation**. Les données sont derrière une
porte, et la porte a un gardien.
MD,
            ],

            /* ================= Séance 2 ================= */
            [
                'title' => 'La classe et l\'objet',
                'chapitre' => 'C1-Objet',
                'duree_min' => 30,
                'prerequis' => "La séance 1 : vous savez pourquoi on regroupe les données et pourquoi on les protège.",
                'intro' => <<<'MD'
La dernière fois, on a vu qu'un objet est un paquet cohérent de données et
d'opérations. Aujourd'hui on va mettre les mots exacts dessus, et écrire notre
premier vrai code Java.

Deux mots à distinguer absolument : **classe** et **objet**. Ils sont confondus
tout le temps, y compris dans des copies d'examen. À la fin de cette séance,
vous ne les confondrez plus.
MD,
                'body' => <<<'MD'
## Le moule et le gâteau

Voici l'image que je veux que vous gardiez.

Une **classe**, c'est un **moule**. Elle décrit ce que tout compte bancaire
possède : un numéro, un solde, un titulaire, et les opérations qu'on peut faire.
Mais elle n'est le compte de personne. On ne peut pas retirer d'argent d'un moule.

Un **objet**, c'est ce qui **sort du moule**. Votre compte à vous, avec son numéro
et ses 1240 euros. Celui de votre voisin, avec les siens. Un moule, des milliers
de gâteaux.

On dit aussi qu'un objet est une **instance** de sa classe. « Instancier une
classe » signifie « fabriquer un objet à partir d'elle ». C'est le vocabulaire
qu'emploie le polycopié, donc c'est celui qu'il faut employer.

Notez la convention d'écriture : une classe porte un nom au **singulier**, avec une
**majuscule** : `CompteBancaire`, `Livre`, `Etudiant`. Jamais `Comptes`.

## Ce qu'un objet contient

Deux sortes de choses, et il faut les nommer correctement.

Ce que l'objet **sait** — son numéro, son solde — s'appelle ses **attributs**.
Vous verrez aussi « champs » ou « propriétés » ; le cours dit **attributs**.

Ce que l'objet **sait faire** — déposer, retirer — s'appelle ses **méthodes**.
Une méthode, c'est une fonction qui appartient à un objet.

C'est tout. Attributs et méthodes. Il n'y a rien d'autre dans une classe.

## Les trois caractéristiques d'un objet

Le polycopié en donne trois, et elles tombent en question de cours.

**L'état.** C'est la valeur de ses attributs **à cet instant**. Le solde du compte
d'Ana est de 1240 euros maintenant ; il sera différent demain. L'état change au
cours de la vie de l'objet.

**Le comportement.** C'est l'ensemble de ses méthodes. Ce qu'il sait faire.
Contrairement à l'état, le comportement ne change pas : un compte sait toujours
déposer et retirer.

**L'identité.** C'est ce qui distingue un objet de tous les autres, **même de son
sosie**.

Cette troisième mérite qu'on s'arrête. Imaginez deux comptes ouverts le même jour,
avec le même solde de zéro euro, au même nom. Sont-ils le même compte ?

**Non.** Ce sont deux comptes, avec deux numéros. Si l'un est fermé, l'autre reste
ouvert.

C'est ce qui distingue un objet d'une simple valeur. Deux fois le nombre 5, c'est
le même 5 — il n'y a qu'un seul 5 au monde. Deux comptes à 5 euros, ce sont deux
comptes différents.

Retenez la formule : **l'état peut être identique, l'identité jamais**.

## Écrivons-la, cette classe

On y va. Je vais poser le code d'un bloc, puis on le relit ligne par ligne.

```java
public class CompteBancaire {

    private String numero;
    private double solde;

    public CompteBancaire(String numero) {
        this.numero = numero;
        this.solde = 0;
    }

    public void deposer(double montant) {
        if (montant <= 0) {
            throw new IllegalArgumentException("Le montant doit être positif");
        }
        this.solde = this.solde + montant;
    }

    public double getSolde() {
        return this.solde;
    }
}
```

Reprenons.

**`public class CompteBancaire {`** — on déclare une classe. `public` veut dire
qu'elle est visible depuis tout le programme.

**`private String numero;`** — un attribut. `String` est son **type** : du texte.
`private` signifie que **personne à l'extérieur ne peut y toucher**. C'est
l'encapsulation de la séance 1, concrétisée en un mot-clé.

**`private double solde;`** — même chose. `double` est le type des nombres à
virgule. Pour de l'argent, on utiliserait plutôt `BigDecimal` en vrai, mais ne
compliquons pas.

**`public CompteBancaire(String numero) {`** — voici quelque chose de nouveau.
Cette méthode porte **exactement le nom de la classe** et n'a **aucun type de
retour**. Ces deux signes en font un **constructeur** : la méthode qui fabrique
l'objet.

**`this.numero = numero;`** — attention, c'est ici que ça se joue.

Il y a deux `numero` dans cette ligne. À gauche, `this.numero` : l'**attribut** de
l'objet. À droite, `numero` tout seul : le **paramètre** reçu par le constructeur.

`this` veut dire « l'objet courant », celui qu'on est en train de fabriquer.

Si vous écriviez `numero = numero;` sans le `this`, vous affecteriez le paramètre
à lui-même. L'attribut resterait vide, et le programme ne planterait pas — il
donnerait juste des comptes sans numéro. C'est un bug classique, et silencieux.

**`public void deposer(double montant) {`** — une méthode. `void` signifie qu'elle
ne rend rien : elle agit, elle ne calcule pas.

**`throw new IllegalArgumentException(...)`** — si le montant est négatif, on
**lance une exception** : on signale une erreur et on interrompt. C'est le gardien
de la porte dont je parlais en séance 1.

**`public double getSolde() { return this.solde; }`** — une méthode qui **rend**
une valeur. Son type de retour est `double`, et `return` fournit la valeur.

Une méthode qui se contente de rendre un attribut s'appelle un **accesseur**, ou
*getter* en anglais. Par convention, elle s'appelle `getQuelqueChose`.

## Fabriquer et utiliser un objet

```java
CompteBancaire c = new CompteBancaire("FR76 1234");
c.deposer(1240);
System.out.println(c.getSolde());     // affiche 1240.0
```

**`new`** est le mot-clé qui fabrique l'objet. Il appelle le constructeur.

**`c.deposer(1240)`** — le point signifie « appelle la méthode de cet objet ».
On dit qu'on « envoie un message » à l'objet ; c'est le vocabulaire du polycopié.

Et maintenant, la ligne qui montre que tout ça sert à quelque chose :

```java
c.solde = 1000000;     // ERREUR de compilation
```

Le compilateur **refuse**. `solde` est `private`. Le million d'euros de la séance 1
est devenu impossible — pas déconseillé, **impossible**.

## Un piège qui tombe au QCM

Que contient `obj` après cette ligne ?

```java
CompteBancaire obj;
```

Beaucoup répondent « un compte vide ». **Non.** Cette ligne ne fabrique aucun objet.
Elle déclare seulement une variable, qui vaut **`null`** — c'est-à-dire *rien*,
*aucun objet*.

Il faut `new` pour fabriquer. Tant qu'on n'a pas écrit `new`, il n'y a pas d'objet.

Cette question est tombée telle quelle en janvier 2025, question 2 du QCM.
La réponse était **a : la valeur null**.
MD,
                'recap' => <<<'MD'
**La classe est le moule, l'objet est ce qui en sort.** Un objet est une *instance*
de sa classe.

Une classe contient deux choses, et rien d'autre :
- des **attributs** — ce que l'objet sait, en `private` ;
- des **méthodes** — ce qu'il sait faire, en `public`.

**Les trois caractéristiques d'un objet** : l'**état** (valeur des attributs
maintenant), le **comportement** (ses méthodes), l'**identité** (ce qui le distingue
de son sosie).

**Le vocabulaire Java de la séance :**

| Mot | Sens |
|---|---|
| `private` / `public` | caché / visible de l'extérieur |
| **constructeur** | méthode au nom de la classe, sans type de retour, qui fabrique l'objet |
| `this` | l'objet courant |
| `new` | fabrique un objet en appelant le constructeur |
| `void` | la méthode ne rend rien |
| `null` | aucun objet |

**Le piège de la séance :** `MaClasse obj;` ne crée **rien**. `obj` vaut `null`.
MD,
            ],

            /* ================= Séance 3 ================= */
            [
                'title' => 'Lire un diagramme de classes',
                'chapitre' => 'C1-Objet',
                'duree_min' => 25,
                'prerequis' => "Les séances 1 et 2 : vous savez ce qu'est une classe, un attribut, une méthode.",
                'intro' => <<<'MD'
On sait écrire une classe en Java. Il faut maintenant savoir la **dessiner**.

Je vous préviens tout de suite : cette séance est la plus rentable du cours.
À votre épreuve, **trois exercices sur quatre demandent un dessin**, pour un total
de 15 points sur 20. Et en janvier, ces 15 points ont été annulés d'un trait parce
que la copie contenait du texte indenté au lieu d'un schéma.

Le correcteur avait écrit : *« il était demandé un schéma, vous avez rendu du
pseudo-code, je n'ai donc rien à noter »*.

Alors aujourd'hui, on apprend à dessiner. Ce n'est ni long ni difficile.
MD,
                'body' => <<<'MD'
## Le langage des schémas s'appelle UML

UML, c'est une notation normalisée pour dessiner des logiciels. Le cours n'en
utilise qu'une petite partie : le **diagramme de classes**.

Un diagramme de classes contient trois sortes de choses :
des **boîtes**, des **traits**, et des **étiquettes**. C'est tout.

Aujourd'hui on voit les boîtes. Les traits, ce sera la séance 4.

## La boîte

Une classe se dessine comme un rectangle à **trois compartiments**, séparés par
des traits horizontaux :

```
┌─────────────────────────┐
│     CompteBancaire      │   ← 1. le nom
├─────────────────────────┤
│ - numero : String       │   ← 2. les attributs
│ - solde : double        │
├─────────────────────────┤
│ + deposer(m : double)   │   ← 3. les méthodes
│ + getSolde() : double   │
└─────────────────────────┘
```

Comparez avec le code de la séance 2 : c'est exactement la même information,
dessinée.

**Trois compartiments. Toujours trois.** Même si un compartiment est vide, on le
laisse — un rectangle sans séparation n'est pas une classe UML.

## Les symboles de visibilité

Vous avez vu les `-` et les `+`. Ce sont les **visibilités** :

| Symbole | Signifie | En Java |
|---|---|---|
| **`-`** | privé | `private` |
| **`+`** | public | `public` |
| **`#`** | protégé | `protected` — visible des classes filles |
| *(rien)* | paquet | visible dans le même paquet |

La règle que vous appliquerez neuf fois sur dix : **attributs en `-`, méthodes
en `+`**. C'est l'encapsulation, dessinée.

## Le typage

Après le nom, deux-points, puis le type :

```
- solde : double
+ getSolde() : double
+ deposer(m : double)
```

Pour un attribut : `- nom : Type`.
Pour une méthode qui rend quelque chose : `+ nom(params) : TypeDeRetour`.
Pour une méthode qui ne rend rien, on omet simplement le type de retour.

**Ne sautez pas les types.** C'est un des éléments que le barème cherche, et
l'ajouter coûte trois caractères.

## Les stéréotypes

Certaines classes sont particulières. On l'indique par un **stéréotype**, écrit
entre **chevrons doubles** au-dessus du nom :

```
┌─────────────────────┐        ┌─────────────────────┐
│    «interface»      │        │     «abstract»      │
│      Volant         │        │      Animal         │
├─────────────────────┤        ├─────────────────────┤
│                     │        │ # nom : String      │
├─────────────────────┤        ├─────────────────────┤
│ + voler()           │        │ + dormir()          │
└─────────────────────┘        │ + crier()           │
                               └─────────────────────┘
```

Les chevrons `« »`, pas des crochets `[ ]`. C'est la notation du cours, et c'est
celle que le correcteur reconnaît.

Une classe abstraite peut aussi se noter en écrivant son **nom en italique**.
Les deux sont acceptés ; le stéréotype est plus lisible à la main.

*Ce que sont exactement une interface et une classe abstraite, on le verra en
séance 5. Pour l'instant, retenez juste comment on les dessine.*

## L'erreur à ne plus jamais faire

Voici ce qui a été rendu en janvier, à la question de conception :

```
Interface Traitement de Commandes
  ↳ class Commandes
        ID_unique
        adresse
        creer()
        modifier()
     ↳ class Status
        status : expédié, validé, annulé
```

C'est lisible. C'est même **juste** — les classes sont bien choisies.
Et ça vaut **zéro**.

Pourquoi ? Parce que l'indentation n'est pas une relation. Décaler `class Status`
sous `class Commandes` ne dit **rien** : est-ce un héritage ? une composition ?
une association ? Le lecteur ne peut pas savoir, donc le correcteur ne peut rien
noter.

Voici la même chose, dessinée :

```
   ┌──────────────────────┐
   │      Commande        │
   ├──────────────────────┤
   │ - id : String        │
   │ - adresse : String   │
   ├──────────────────────┤
   │ + creer()            │
   │ + modifier()         │
   └──────────┬───────────┘
              │ 1
              │ etat
              ▽
   ┌──────────────────────┐
   │     «interface»      │
   │    EtatCommande      │
   ├──────────────────────┤
   │ + traiter(c: Cmd)    │
   └──────────────────────┘
```

Même contenu. Mais maintenant il y a un **trait**, avec une **multiplicité** (`1`)
et un **rôle** (`etat`). Le lecteur sait qu'une commande possède exactement un
état. Ça se note.

## Comment faire, à la main, en trois heures

Vous n'aurez ni ordinateur ni règle. Voici la méthode pratique.

**Un.** Listez d'abord les classes au brouillon, sans rien dessiner. Relisez
l'énoncé et soulignez les **noms** : ce sont vos classes candidates.

**Deux.** Dessinez les rectangles, bien espacés, sur toute la largeur de la page.
Ne serrez pas : vous aurez besoin de place pour les traits.

**Trois.** Tracez les deux séparations horizontales dans chaque rectangle.
Remplissez : nom, attributs avec leurs types, méthodes.

**Quatre.** Reliez — c'est la séance 4.

**Cinq.** Écrivez le nom des patrons à côté des zones concernées — c'est la
séance 7.

Comptez **quinze minutes par diagramme**. Vous en avez trois à faire, et
quarante-cinq minutes chacun. Le tracé n'est pas ce qui prend le temps ; c'est
la réflexion.

Et n'ayez aucune inquiétude sur l'esthétique. Des rectangles au stylo, des traits
droits, une écriture lisible : c'est tout ce qu'on demande. Personne n'attend un
schéma d'imprimerie.
MD,
                'recap' => <<<'MD'
Une classe se dessine en **rectangle à trois compartiments** : nom, attributs,
méthodes.

**Les visibilités :** `-` privé, `+` public, `#` protégé.
Neuf fois sur dix : **attributs en `-`, méthodes en `+`**.

**Le typage** : `- nom : Type` pour un attribut,
`+ methode() : TypeRetour` pour une méthode.

**Les stéréotypes** s'écrivent entre chevrons doubles : `«interface»`, `«abstract»`.
Pas entre crochets.

**Et la règle qui vaut 15 points à votre épreuve :** un plan indenté n'est pas un
schéma. L'indentation ne dit pas quelle relation lie deux classes. Il faut des
boîtes et des traits.
MD,
            ],

            /* ================= Séance 4 ================= */
            [
                'title' => 'Relier les classes : les cinq traits',
                'chapitre' => 'C1-Concept',
                'duree_min' => 35,
                'prerequis' => "La séance 3 : vous savez dessiner une boîte de classe.",
                'intro' => <<<'MD'
On sait dessiner des boîtes. Un logiciel, c'est des boîtes **reliées**.

Bonne nouvelle : il n'existe que **cinq façons** de relier deux classes. Cinq,
pas davantage. Les connaître, c'est savoir lire et dessiner n'importe quel
diagramme.

Trois d'entre elles se ressemblent beaucoup — association, agrégation, composition
— et c'est précisément là-dessus que le QCM interroge chaque année. Je vais vous
donner un test qui tranche à coup sûr.
MD,
                'body' => <<<'MD'
## Le tour d'horizon, en une image

| Ce qu'on veut dire | Exemple | Relation |
|---|---|---|
| « **est un** » | un chien est un animal | **héritage** |
| « **sait faire** » | un canard sait voler | **implémentation** |
| « se connaissent » | une voiture et son propriétaire | **association** |
| « regroupe », les parties survivent | une équipe et ses joueurs | **agrégation** |
| « possède », les parties meurent avec | une maison et ses pièces | **composition** |

Prenons-les une par une.

## 1. L'héritage — « est un »

Un chien **est un** animal. Il a tout ce qu'a un animal — un nom, il dort, il
mange — plus ce qui lui est propre.

```
   ┌──────────┐
   │  Animal  │
   └────△─────┘
        │          ← triangle CREUX, trait PLEIN
   ┌────┴─────┐
   │  Chien   │
   └──────────┘
```

Le triangle pointe vers le **parent**. Toujours. C'est la classe générale qui est
en haut.

En Java : `class Chien extends Animal`.

**Le test :** posez la question « un X est-il un Y ? ». Si la phrase sonne juste,
c'est un héritage.

*« Un chien est-il un animal ? »* Oui → héritage.
*« Une voiture est-elle un moteur ? »* Non → ce n'est pas un héritage.

Cette dernière est l'erreur classique. Une voiture **a un** moteur ; elle n'en est
pas un. On y revient dans deux minutes.

## 2. L'implémentation — « sait faire »

Une **interface** est un contrat. Elle liste des méthodes, sans dire comment on les
réalise. « Volant » dit qu'il faut savoir voler, sans dire comment.

```
   ┌─────────────┐
   │ «interface» │
   │   Volant    │
   └──────△──────┘
          ┊          ← triangle CREUX, trait POINTILLÉ
   ┌──────┴──────┐
   │   Canard    │
   └─────────────┘
```

Même triangle que l'héritage, mais **le trait est en pointillés**. C'est la seule
différence visuelle, et elle compte.

En Java : `class Canard extends Animal implements Volant`.

Remarquez : le canard fait les deux. Il **est un** animal, et il **sait** voler.
C'est le cas le plus fréquent dans les sujets d'examen.

## 3, 4, 5 — les trois qui se ressemblent

Voici le cœur de la séance. Ces trois relations se dessinent presque pareil,
et on les confond tout le temps.

### L'association — ils se connaissent, c'est tout

Deux objets se connaissent. Ni l'un ni l'autre n'est un morceau de l'autre.
Leurs vies sont **indépendantes**.

```
   ┌───────────┐  1        1  ┌──────────────┐
   │  Voiture  │──────────────│ Proprietaire │
   └───────────┘              └──────────────┘
```

Un **trait simple**, sans aucune décoration. Les nombres aux extrémités sont les
**multiplicités** — on y vient.

### L'agrégation — un tout, des parties qui survivent

Une équipe regroupe des joueurs. Si l'équipe est dissoute, **les joueurs existent
toujours** : ils iront dans d'autres équipes.

```
   ┌──────────┐ 1        * ┌─────────┐
   │  Equipe  │◇───────────│  Joueur │
   └──────────┘            └─────────┘
```

Un **losange creux**, du côté du **tout**.

### La composition — un tout, des parties qui meurent avec lui

Une maison contient des pièces. Si la maison est démolie, **les pièces
disparaissent**. Une pièce n'existe pas toute seule.

```
   ┌──────────┐ 1        * ┌─────────┐
   │  Maison  │◆───────────│  Piece  │
   └──────────┘            └─────────┘
```

Un **losange plein**, du côté du tout.

## Le test qui tranche

Vous hésitez entre les trois ? Deux questions, dans cet ordre.

**Question 1 : y a-t-il un rapport tout / partie ?**

Est-ce que l'un est un **morceau** de l'autre ?

- Une pièce est un morceau d'une maison. Oui.
- Un joueur est un morceau d'une équipe. Oui.
- Une voiture est-elle un morceau de son propriétaire ? **Non.**

Si la réponse est non → **association**. On s'arrête là.

**Question 2 : si je détruis le tout, la partie survit-elle ?**

- La maison est démolie : la pièce disparaît. **Non** → **composition**, losange plein.
- L'équipe est dissoute : le joueur existe encore. **Oui** → **agrégation**, losange creux.

Deux questions. Elles suffisent toujours.

### Le cas qui est tombé en 2025

*« La relation entre une voiture et son propriétaire, ou un compte bancaire et le
client, est : a. agrégation, b. héritage, c. composition, d. association. »*

Appliquons. Question 1 : une voiture est-elle un morceau de son propriétaire ?
Non, évidemment. On s'arrête : **association**.

La réponse du corrigé était bien **d**.

Beaucoup répondent « agrégation » parce que « le propriétaire a une voiture ».
Mais « avoir » ne suffit pas — il faut un rapport **tout / partie**. Un propriétaire
n'est pas fait de voitures.

## Les multiplicités

Aux extrémités de chaque trait, on écrit combien d'objets sont concernés.

| Écriture | Sens |
|---|---|
| `1` | exactement un |
| `*` | zéro ou plusieurs |
| `1..*` | au moins un |
| `0..1` | zéro ou un |
| `2..5` | entre deux et cinq |

```
   ┌──────────┐ 1      1..* ┌─────────┐
   │  Equipe  │◇────────────│  Joueur │
   └──────────┘             └─────────┘
```

Se lit : *une équipe regroupe au moins un joueur ; un joueur appartient à
exactement une équipe*.

**N'oubliez pas les multiplicités.** Un trait sans multiplicité est incomplet,
et c'est un des éléments que la grille cherche.

## Un diagramme complet

Rassemblons tout sur un exemple. Une école.

```
        ┌────────────────────────┐
        │   «abstract» Personne  │
        ├────────────────────────┤
        │ # nom : String         │
        ├────────────────────────┤
        │ + getNom() : String    │
        └───────────△────────────┘
              ┌─────┴─────┐
    ┌─────────┴──┐    ┌───┴────────┐
    │ Enseignant │    │   Eleve    │
    └──────┬─────┘    └─────┬──────┘
           │ *              │ *
           │                │
      ┌────┴────────────────┴───┐
      │         Classe          │
      └────────────△────────────┘
                   │ 1..*
            ┌──────┴──────┐
            │    Ecole    │
            └─────────────┘
```

Lisez-le : `Personne` est abstraite, `Enseignant` et `Eleve` en héritent
(triangles creux). Une `Classe` est liée à plusieurs enseignants et plusieurs
élèves. Et `Ecole` contient au moins une classe.

Cette dernière relation : agrégation ou composition ? Si l'école ferme, les classes
disparaissent-elles ? Oui — une classe n'existe pas sans son école.
**Composition, losange plein.**
MD,
                'recap' => <<<'MD'
**Les cinq traits :**

| Trait | Relation | Se lit |
|---|---|---|
| `──▷` triangle creux, plein | **héritage** | « est un » |
| `┈┈▷` triangle creux, pointillé | **implémentation** | « sait faire » |
| `──◇` losange creux | **agrégation** | les parties survivent |
| `──◆` losange plein | **composition** | les parties meurent avec |
| `────` trait simple | **association** | se connaissent |

**Le test en deux questions :**

1. Y a-t-il un rapport **tout / partie** ? Non → **association**.
2. Si je détruis le tout, la partie survit-elle ?
   Oui → **agrégation**. Non → **composition**.

**Les multiplicités** aux deux extrémités : `1`, `*`, `1..*`, `0..1`.
Un trait sans multiplicité est incomplet.

**Le piège de 2025 :** une voiture et son propriétaire, c'est une **association** —
pas une agrégation. Il n'y a pas de rapport tout/partie.
MD,
            ],

            /* ================= Séance 5 ================= */
            [
                'title' => 'Interface, classe abstraite, polymorphisme',
                'chapitre' => 'C1-Concept',
                'duree_min' => 35,
                'prerequis' => "La séance 4 : vous connaissez les cinq relations, notamment l'héritage et l'implémentation.",
                'intro' => <<<'MD'
On a vu que l'héritage se dessine avec un trait plein et l'implémentation avec un
trait pointillé. Aujourd'hui, on regarde **ce qui se cache derrière**, et surtout
**comment choisir** entre les deux.

Et on va rencontrer le mot le plus intimidant du module — **polymorphisme** — pour
découvrir qu'il désigne une idée très simple.
MD,
                'body' => <<<'MD'
## L'héritage, concrètement

Reprenons nos animaux.

```java
public class Animal {

    protected String nom;                    // « protected » : visible des filles

    public Animal(String nom) {
        this.nom = nom;
    }

    public void dormir() {
        System.out.println(nom + " dort");
    }
}
```

```java
public class Chien extends Animal {

    public Chien(String nom) {
        super(nom);                          // appelle le constructeur du parent
    }

    public void aboyer() {
        System.out.println("Ouaf");
    }
}
```

Deux mots nouveaux.

**`protected`** — troisième visibilité. L'attribut est caché de l'extérieur, mais
**visible des classes filles**. Entre `private` et `public`.

**`super(nom)`** — appelle le constructeur du parent. Une classe fille doit
toujours commencer par construire la partie qu'elle hérite. C'est logique : avant
d'être un chien, il faut être un animal.

Et maintenant :

```java
Chien rex = new Chien("Rex");
rex.dormir();      // hérité d'Animal → affiche « Rex dort »
rex.aboyer();      // propre à Chien  → affiche « Ouaf »
```

`Chien` n'a jamais défini `dormir`. Il l'a **héritée**. C'est tout l'intérêt :
le code écrit une fois sert à toutes les filles.

## La classe abstraite

Parfois, la classe parente est **incomplète** par nature.

Tous les animaux crient — mais chacun à sa façon. On ne peut pas écrire un `crier()`
générique. Pourtant on veut garantir que **toute** classe fille en fournira un.

```java
public abstract class Animal {

    protected String nom;

    public Animal(String nom) { this.nom = nom; }

    public void dormir() {                   // du VRAI code, hérité tel quel
        System.out.println(nom + " dort");
    }

    public abstract void crier();            // pas de corps : à chaque fille de dire
}
```

Deux nouveautés.

**`abstract class`** — la classe est incomplète. Conséquence directe :

```java
Animal a = new Animal("x");     // ERREUR : on ne peut pas instancier une classe abstraite
```

C'est cohérent. « Un animal » dans l'absolu, ça n'existe pas — il y a des chiens,
des chats, des canards.

**`public abstract void crier();`** — une méthode déclarée sans corps.
Le point-virgule remplace les accolades. Toute classe fille **doit** la fournir,
sinon elle ne compile pas.

```java
public class Chien extends Animal {

    public Chien(String nom) { super(nom); }

    @Override
    public void crier() {
        System.out.println("Ouaf");
    }
}
```

**`@Override`** est une annotation. Elle dit au compilateur « cette méthode
redéfinit celle du parent ». Ce n'est pas obligatoire, mais c'est une bonne
habitude : si vous vous trompez de nom, le compilateur vous le dit.

## L'interface

Une **interface** va plus loin dans la même direction : elle ne contient **que**
des déclarations, aucun code, aucun attribut.

```java
public interface Volant {
    void voler();        // implicitement public et abstract
}
```

C'est un **pur contrat**. Elle dit ce qu'il faut savoir faire, jamais comment.

```java
public class Canard extends Animal implements Volant {

    public Canard(String nom) { super(nom); }

    @Override
    public void crier() { System.out.println("Coin"); }

    @Override
    public void voler() { System.out.println("Le canard s'envole"); }
}
```

Le canard **est un** animal — `extends` — et **sait** voler — `implements`.

## Interface ou classe abstraite : le tableau qui tranche

C'est une question de cours qui tombe. Voici la comparaison, et surtout la règle
de choix.

| | Interface | Classe abstraite |
|---|---|---|
| Contient du code | non | **oui** |
| Contient des attributs | non | **oui** |
| On peut en avoir plusieurs | **oui, autant qu'on veut** | **non, une seule** |
| Mot-clé | `implements` | `extends` |
| Relation | « sait faire » | « est un » |
| Trait UML | pointillé | plein |

**La règle de choix, en une phrase :**

> Si les classes partagent du **code**, classe abstraite.
> Si elles partagent seulement une **capacité**, interface.

Un canard et un chien partagent du code : ils ont tous deux un nom, ils dorment
pareil. → classe abstraite `Animal`.

Un canard et un avion partagent une capacité, mais aucun code : rien de commun
entre eux sinon qu'ils volent. → interface `Volant`.

**Et retenez la limite technique :** en Java, on ne peut hériter que d'**une seule**
classe. En revanche, on peut implémenter **autant d'interfaces qu'on veut**.

```java
class Canard extends Animal implements Volant, Nageur, Comestible
```

Un `extends`, trois `implements`. C'est légal.

## Le polymorphisme

Voilà le mot. Il vient du grec : *plusieurs formes*. Voici ce qu'il désigne.

```java
Animal a = new Chien("Rex");
a.crier();                        // affiche « Ouaf »

Animal b = new Canard("Donald");
b.crier();                        // affiche « Coin »
```

Regardez bien. Les deux variables sont **déclarées `Animal`**. Le même appel,
`crier()`, produit deux résultats différents.

Pourquoi ? Parce que Java ne regarde pas le **type déclaré** de la variable pour
choisir la méthode. Il regarde l'**objet réellement créé**.

C'est ça, le polymorphisme : **le même appel donne un comportement différent selon
l'objet réel**.

### Pourquoi c'est utile

Sans polymorphisme, il faudrait écrire :

```java
if (animal est un Chien)  { ((Chien) animal).aboyer(); }
else if (animal est un Chat) { ((Chat) animal).miauler(); }
else if (animal est un Canard) { ((Canard) animal).cancaner(); }
```

Et à chaque nouvel animal, revenir modifier ce bloc. Avec le polymorphisme :

```java
public void faireCrier(List<Animal> animaux) {
    for (Animal a : animaux) {
        a.crier();          // Java choisit tout seul
    }
}
```

Ajoutez une classe `Vache` demain : **cette méthode ne change pas d'une ligne**.

C'est le mécanisme qui rend les patrons de conception possibles. Retenez-le bien —
tous les patrons qu'on verra à partir de la séance 7 reposent dessus.

## Le transtypage

Dernier point de la séance. Que se passe-t-il quand on change le type déclaré ?

```java
Animal a = new Chien("Rex");     // montée : toujours autorisée
```

Un chien **est** un animal, donc on peut le ranger dans une variable `Animal`.
C'est toujours sûr, Java ne dit rien.

```java
Chien c = (Chien) a;             // descente : il faut le dire explicitement
```

Là, on affirme « cet animal est en fait un chien ». Java vous croit sur parole,
mais si vous mentez, le programme plante **à l'exécution**.

D'où la précaution :

```java
if (a instanceof Chien) {
    Chien c = (Chien) a;
    c.aboyer();
}
```

**`instanceof`** teste le type réel. Avec lui, la descente est sûre.

### La méthode pour les questions de typage

Le QCM pose ce genre de question :

```java
interface Position {}
class Premier {}
class Second extends Premier {}
class Quatrieme extends Second implements Position {}
class Cinquieme extends Quatrieme {}
```

*« `Position p = new Cinquieme();` compile-t-il ? »*

**La méthode, en deux temps.**

**Un.** Dessinez la chaîne, en partant de la classe instanciée et en remontant :

```
Cinquieme → Quatrieme → Second → Premier
                ↑
           (Position)
```

**Deux.** Cherchez le type déclaré sur ce chemin — comme ancêtre, ou comme
interface implémentée par un ancêtre.

`Position` est implémentée par `Quatrieme`, qui est sur le chemin. **Ça compile.**

Appliquez cette méthode mécaniquement et vous ne vous tromperez jamais sur ce
type de question.
MD,
                'recap' => <<<'MD'
**Classe abstraite** — incomplète, ne s'instancie pas, contient du **code** et des
attributs. Une méthode `abstract` n'a pas de corps et **doit** être fournie par les
filles.

**Interface** — pur contrat, aucun code, aucun attribut.

**La règle de choix :** du **code** partagé → classe abstraite. Une **capacité**
partagée → interface.

**La limite Java :** une seule classe mère (`extends`), autant d'interfaces qu'on
veut (`implements`).

**Le polymorphisme** — le même appel donne un comportement différent selon l'objet
**réellement créé**, pas selon le type déclaré. C'est ce qui rend les patrons de
conception possibles.

**Le transtypage** — la montée est toujours sûre, la descente exige `(Type)` et
devrait être précédée d'un `instanceof`.

**La méthode pour les questions de typage :** remontez la chaîne depuis la classe
instanciée. Si le type déclaré y figure, ça compile.

**Les mots-clés de la séance :** `extends`, `implements`, `abstract`, `protected`,
`super`, `@Override`, `instanceof`.
MD,
            ],

            /* ================= Séance 6 ================= */
            [
                'title' => 'Java pratique : collections, exceptions, JDBC',
                'chapitre' => 'C2-Coll',
                'duree_min' => 30,
                'prerequis' => "Les séances 2 et 5 : vous lisez du Java, vous connaissez interfaces et polymorphisme.",
                'intro' => <<<'MD'
Séance plus courte et plus pratique. On voit trois outils du quotidien Java, qui
apparaissent régulièrement au QCM.

Ce n'est pas la partie la plus rentable du module — le QCM ne vaut que 5 points
sur 20. Mais ces questions sont **faciles** quand on connaît la règle, et il serait
dommage de les laisser.
MD,
                'body' => <<<'MD'
## Les collections

Un tableau Java a une taille fixée à la création. Les **collections** grandissent
toutes seules. Il y en a trois familles, et on choisit avec deux questions :
*faut-il un ordre ?* et *les doublons sont-ils permis ?*

| Interface | Ordonné ? | Doublons ? | Implémentations |
|---|---|---|---|
| **`List`** | oui, par indice | **oui** | `ArrayList`, `LinkedList` |
| **`Set`** | non | **non** | `HashSet`, `TreeSet` |
| **`Map`** | par **clé** | clés uniques | `HashMap`, `TreeMap` |

Une `Map` est à part : elle ne contient pas des éléments mais des **couples
clé → valeur**. Un annuaire : le nom est la clé, le numéro la valeur.

```java
List<String> noms = new ArrayList<>();
noms.add("Ana");
noms.add("Ana");
System.out.println(noms.size());       // 2 — une liste accepte les doublons

Set<String> uniques = new HashSet<>();
uniques.add("Ana");
uniques.add("Ana");
System.out.println(uniques.size());    // 1 — un ensemble les refuse

Map<String, String> annuaire = new HashMap<>();
annuaire.put("Ana", "0696...");
System.out.println(annuaire.get("Ana"));
```

Les chevrons `<String>` sont la **généricité** : ils disent au compilateur ce que
la collection contient. Sans eux, il faudrait transtyper à chaque lecture.

Notez que `List`, `Set` et `Map` sont des **interfaces**, et `ArrayList`, `HashSet`,
`HashMap` des **classes** qui les implémentent. C'est exactement la séance 5 en
application : on déclare par l'interface, on instancie par la classe.

```java
List<String> noms = new ArrayList<>();
//  ↑ interface        ↑ implémentation
```

Cette habitude permet de changer d'implémentation en modifiant **une seule ligne**.

**Deux pièges du QCM.**

*Un `HashSet` conserve-t-il l'ordre d'insertion ?* **Non.** Aucun ordre garanti.
Pour un ordre trié : `TreeSet`. Pour l'ordre d'insertion : `LinkedHashSet`.

*Comment comparer deux `String` ?* Avec **`.equals()`**, jamais avec `==`.
`==` compare les **références** — « est-ce le même objet en mémoire ? » —
et `.equals()` compare le **contenu**.

```java
String x = "bonjour";
String y = new String("bonjour");
x == y            // false : deux objets distincts
x.equals(y)       // true  : même contenu
```

Le piège est que `==` marche **parfois** par accident, parce que Java met en cache
les littéraux identiques. Un bug qui n'apparaît qu'une fois sur deux est pire qu'un
bug franc.

## Les exceptions

Une **exception** signale une erreur et interrompt le cours normal du programme.

Deux verbes à ne pas confondre.

**`throw`** — je **lance** une exception :
```java
throw new IllegalArgumentException("nombre négatif");
```

**`catch`** — je l'**attrape** :
```java
try {
    faireQuelqueChose();
} catch (IllegalArgumentException e) {
    System.out.println(e.getMessage());
}
```

Le message se donne **au constructeur**, à la création. Il n'y a pas de
`setMessage()` — c'est la fausse réponse classique du QCM.

## Le try-with-resources

Quand on ouvre un fichier, il faut le fermer. Même si une erreur survient au milieu.
Java propose une forme qui s'en charge :

```java
try (BufferedReader r = new BufferedReader(new FileReader("fichier.txt"))) {
    String ligne;
    while ((ligne = r.readLine()) != null) {
        System.out.println(ligne);
    }
} catch (IOException e) {
    System.err.println("Lecture impossible : " + e.getMessage());
}
```

Les **parenthèses après `try`** font toute la différence : la ressource déclarée
là est fermée automatiquement à la sortie du bloc, quoi qu'il arrive.

Utilisez-la systématiquement. Il n'y a aucune raison de faire autrement.

## JDBC

Pour parler à une base de données, quatre temps, toujours les mêmes.

```java
// 1. Se connecter
Connection cn = DriverManager.getConnection(url, utilisateur, motDePasse);

// 2. Préparer la requête, avec des « ? » à la place des valeurs
PreparedStatement st = cn.prepareStatement("SELECT * FROM livre WHERE auteur = ?");
st.setString(1, "Saint-Exupéry");

// 3. Exécuter et parcourir
ResultSet rs = st.executeQuery();
while (rs.next()) {
    System.out.println(rs.getString("titre"));
}

// 4. Fermer — ou mieux, utiliser un try-with-resources
```

### Pourquoi `PreparedStatement` et pas `Statement`

C'est **la** question qui tombe, et la réponse mérite d'être comprise, pas récitée.

Avec un `Statement`, on concatène :

```java
String sql = "SELECT * FROM livre WHERE auteur = '" + saisie + "'";
```

Si l'utilisateur saisit dans le champ auteur :

```
'; DROP TABLE livre; --
```

la requête envoyée devient :

```sql
SELECT * FROM livre WHERE auteur = ''; DROP TABLE livre; --'
```

**La table est détruite.** Le `--` met en commentaire ce qui traîne après, pour que
la syntaxe reste valide.

Avec un `PreparedStatement`, la requête est **compilée d'abord**, avec ses `?`
comme emplacements vides. Les valeurs sont transmises **séparément** et ne sont
jamais interprétées comme du SQL. Le texte malveillant serait simplement cherché
tel quel comme nom d'auteur — et ne trouverait rien.

C'est ce qu'on appelle une **injection SQL**, et c'est encore aujourd'hui l'une
des failles les plus répandues.
MD,
                'recap' => <<<'MD'
**Les trois collections :**

| | Ordre | Doublons |
|---|---|---|
| `List` | oui, par indice | **oui** |
| `Set` | non | **non** |
| `Map` | par clé | clés uniques |

Déclarez par l'**interface**, instanciez par la **classe** :
`List<String> l = new ArrayList<>();`

**Comparer deux `String` :** toujours `.equals()`. `==` compare les références.

**Exceptions :** `throw` lance, `catch` attrape. Le message se donne au
constructeur — `setMessage()` n'existe pas.

**`try (...)` avec parenthèses** = try-with-resources : ferme automatiquement.

**JDBC :** toujours `PreparedStatement`, jamais `Statement`. Les `?` séparent la
requête des valeurs et bloquent l'**injection SQL**.
MD,
            ],

            /* ================= Séance 7 ================= */
            [
                'title' => 'Les patrons de conception — ce que c\'est, et pourquoi ils valent 15 points',
                'chapitre' => 'DP-Method',
                'duree_min' => 25,
                'prerequis' => "Les séances 4 et 5 : les relations UML, les interfaces, le polymorphisme.",
                'intro' => <<<'MD'
Nous y voilà. Les **patrons de conception** — *design patterns* en anglais —
sont le cœur de votre épreuve : **15 points sur 20**.

Avant d'en voir un seul, je veux qu'on comprenne ce que c'est. Parce que la plupart
des étudiants les apprennent comme une liste de recettes à réciter, et c'est
exactement ce que l'examen ne demande pas.
MD,
                'body' => <<<'MD'
## L'idée

Un patron de conception, c'est une **solution éprouvée à un problème de conception
qui revient souvent**.

Prenez une analogie. En architecture, « une véranda orientée au sud » est un patron :
un problème récurrent — comment avoir de la lumière sans surchauffer l'été — et une
solution connue. On ne réinvente pas la véranda à chaque maison ; on l'adapte.

En programmation, c'est pareil. Le problème « je veux qu'un objet change de
comportement selon son état » revient dans tous les logiciels : un distributeur,
une commande, un abonnement. La solution s'appelle le patron **État**, et elle est
toujours la même.

Un patron n'est **pas** du code à recopier. C'est une **structure de classes** :
qui hérite de qui, qui référence qui.

## Comment on les reconnaît

Voici le point crucial, et c'est celui que l'examen teste.

L'énoncé ne dit jamais « appliquez le patron Composite ». Il décrit un **problème**,
dans la langue courante. À vous de reconnaître lequel.

Et l'enseignant décrit toujours le problème dans les mêmes termes. Regardez :

| Ce que dit l'énoncé | Le patron |
|---|---|
| « des agencements composés de fleurs, d'herbes et de plantes » | **Composite** |
| « les fourmis s'organisent en groupe » | **Composite** |
| « le fonctionnement change selon les saisons » | **État** |
| « l'image s'adapte selon l'heure : claire le jour, sombre la nuit » | **État** |
| « on adopte le mode opératoire bio ou standard » | **Stratégie** |
| « selon le type de demande, un traitement approprié » | **Stratégie** |
| « maintenir un compte précis, monitorer les entrées et sorties » | **Observateur** |
| « chaque objet doit être informé lorsqu'un autre le touche » | **Observateur** |
| « la fourmi sans rien devient une fourmi avec de la nourriture » | **Décorateur** |
| « des traitements qui ne doivent pas alourdir les classes » | **Visiteur** |

Ces exemples viennent **des sujets réels** de 2024 et 2025, avec les réponses
des corrigés officiels.

Regardez la colonne de droite. **Cinq patrons** couvrent la quasi-totalité :
Composite, État, Stratégie, Observateur, Décorateur.

C'est là-dessus qu'il faut concentrer l'effort.

## Le format exact de votre épreuve

Vous aurez **trois exercices de conception**, à 5 points chacun. Ils sont bâtis
sur le même moule, et le barème est **annoncé dans l'énoncé** :

| Attendu | Points |
|---|---|
| Notions objet mobilisées : interface, classe abstraite, héritage | 1 |
| Patron n° 1, identifié sur le schéma | 1 |
| Patron n° 2, identifié sur le schéma | 1 |
| Patron n° 3, identifié sur le schéma | 1 |
| Cohérence et logique globale | 1 |

Deux clauses de l'énoncé qu'il faut connaître par cœur.

> « Il faut identifier chaque pattern sur le schéma — **si vous ne le faites pas
> il n'y a pas de point attribué**. »

Un diagramme parfait sans le nom du patron écrit dessus vaut **zéro** sur les trois
points de patrons. Ce n'est pas une menace en l'air : c'est écrit dans le sujet.

> « Les patterns Singleton et Builder sont hors scope. Vous pouvez les utiliser
> mais ils ne comptent pas comme un des 3 patterns. »

Ne les proposez pas comme réponse. Ils restent au QCM, mais pas ici.

## La structure de la réponse

Pour chacun des trois exercices, voici exactement quoi rendre.

**Un. Le diagramme de classes.** Les entités de l'énoncé, en boîtes à trois
compartiments. Avec au moins une `«interface»` ou une classe abstraite, et un
héritage — c'est le point « notions objet », et il s'obtient presque gratuitement.

**Deux. Trois étiquettes bien visibles sur le schéma.** Encadrez la zone concernée
et écrivez le nom :

```
        ◄──── Composite
```

**Trois. Trois lignes de justification sous le schéma.** L'énoncé précise que
*« vous pouvez ajouter des explications, elles seront lues et prises en compte »*.
C'est le point de cohérence qui se joue là, et une ligne par patron suffit :

> *Composite : un rayon contient des ouvrages et d'autres rayons, comptés de la
> même façon.*

## La gestion du temps

Trois heures, et voici comment les répartir :

| | Durée | Cumul |
|---|---|---|
| Lecture du sujet en entier | 5 min | 5 min |
| QCM — il ne vaut que 5 points | 20 min | 25 min |
| Conception 1 | 45 min | 1 h 10 |
| Conception 2 | 45 min | 1 h 55 |
| Conception 3 | 45 min | 2 h 40 |
| Relecture : les étiquettes sont-elles toutes là ? | 20 min | 3 h |

Ne passez pas une heure sur le QCM. Il vaut un quart de ce que vaut une seule
question de conception.

Et gardez vraiment les vingt dernières minutes pour vérifier une chose : **chaque
diagramme porte-t-il trois noms de patrons ?** C'est trois points par exercice,
soit neuf au total, et ils se perdent par simple oubli.

## Un mot sur le QCM

Détail qui compte : le QCM d'ALO **pénalise l'erreur**.

> Bonne réponse **+0,5** · Pas de réponse **0** · Mauvaise réponse **−0,25**

Le seuil de rentabilité se calcule. Répondre rapporte en moyenne
`p × 0,5 − (1−p) × 0,25`. Cette quantité devient positive quand **p > 1/3**.

Autrement dit : **répondez dès que vous pouvez éliminer assez de propositions pour
avoir plus d'une chance sur trois**. Sur une question à quatre propositions, en
écarter deux suffit largement.

En dessous, abstenez-vous. Une case vide vaut zéro ; une mauvaise réponse coûte.
MD,
                'recap' => <<<'MD'
Un **patron de conception** est une solution éprouvée à un problème récurrent.
Ce n'est pas du code, c'est une **structure de classes**.

**Cinq patrons couvrent presque tous les sujets :**
Composite, État, Stratégie, Observateur, Décorateur.

**Le barème d'un exercice de conception :** 1 point pour les notions objet,
1 point par patron **nommé sur le schéma**, 1 point de cohérence.

**La clause qui coûte cher :** un patron non nommé sur le diagramme ne rapporte
rien, même s'il est juste.

**Hors scope :** Singleton et Builder ne comptent pas parmi les trois patrons.

**Gestion du temps :** 20 min de QCM, 45 min par conception, 20 min de relecture
consacrée à vérifier les étiquettes.

**Le QCM pénalise l'erreur** : −0,25. Répondez au-dessus d'une chance sur trois,
abstenez-vous en dessous.
MD,
            ],

            /* ================= Séance 8 ================= */
            [
                'title' => 'Composite et Décorateur',
                'chapitre' => 'DP-Struct',
                'duree_min' => 30,
                'prerequis' => "La séance 7 : vous savez ce qu'est un patron et comment l'épreuve les évalue.",
                'intro' => <<<'MD'
Premiers patrons. Ces deux-là sont dits **structurels** : ils organisent la façon
dont les objets s'assemblent.

Ils se ressemblent beaucoup — au point qu'on les confond régulièrement — et la
différence tient à **un seul mot**. Je vous le donnerai à la fin, mais essayez de
le trouver en chemin.
MD,
                'body' => <<<'MD'
## Composite — traiter un groupe comme un élément

### Le problème

Vous modélisez un système de fichiers. Il y a des **fichiers** et des **dossiers**.
Un dossier contient des fichiers… et d'autres dossiers.

Vous voulez calculer la taille d'un dossier. Naïvement :

```java
long taille(Object element) {
    if (element instanceof Fichier) {
        return ((Fichier) element).octets();
    } else if (element instanceof Dossier) {
        long total = 0;
        for (Object enfant : ((Dossier) element).enfants()) {
            total += taille(enfant);        // récursion, avec le même test à chaque niveau
        }
        return total;
    }
    return 0;
}
```

Ça marche, mais c'est laid. Des `instanceof` partout, des transtypages, et le jour
où l'on ajoute les raccourcis, il faut revenir modifier ce code.

### La solution

**Donner la même interface au fichier et au dossier.** Le client ne fait plus la
différence.

```java
public interface ElementFichier {
    long taille();
    String nom();
}
```

La **feuille** — un fichier :

```java
public class Fichier implements ElementFichier {

    private final String nom;
    private final long octets;

    public Fichier(String nom, long octets) {
        this.nom = nom;
        this.octets = octets;
    }

    @Override
    public long taille() { return octets; }

    @Override
    public String nom() { return nom; }
}
```

Le **composite** — un dossier :

```java
public class Dossier implements ElementFichier {

    private final String nom;
    private final List<ElementFichier> enfants = new ArrayList<>();

    public Dossier(String nom) { this.nom = nom; }

    public void ajouter(ElementFichier e) { enfants.add(e); }

    @Override
    public long taille() {
        long total = 0;
        for (ElementFichier e : enfants) {
            total += e.taille();       // ← délégation. C'est tout le patron.
        }
        return total;
    }

    @Override
    public String nom() { return nom; }
}
```

Regardez la ligne `total += e.taille()`. Elle ne teste rien. `e` peut être un
fichier ou un dossier — grâce au **polymorphisme** de la séance 5, Java choisit la
bonne méthode tout seul.

Et comme `Dossier` implémente `ElementFichier`, **un dossier peut contenir un
dossier**. La récursion est gratuite.

```java
Dossier racine = new Dossier("racine");
racine.ajouter(new Fichier("notes.txt", 1200));

Dossier images = new Dossier("images");
images.ajouter(new Fichier("photo.jpg", 450000));
racine.ajouter(images);              // un dossier dans un dossier

System.out.println(racine.taille()); // 451200
```

### Le diagramme

```
        ┌──────────────────────────┐
        │       «interface»        │
        │     ElementFichier       │
        ├──────────────────────────┤
        │ + taille() : long        │
        └────────────△─────────────┘
              ┌──────┴───────┐
      ┌───────┴────┐   ┌─────┴───────┐
      │  Fichier   │   │   Dossier   │◇──┐
      ├────────────┤   ├─────────────┤   │ 1..*
      │ - octets   │   │ + ajouter() │───┘
      │ + taille() │   │ + taille()  │
      └────────────┘   └─────────────┘

              ◄── Composite
```

**Notez bien l'agrégation** : le losange part de `Dossier` et pointe vers
`ElementFichier`, avec la multiplicité `1..*`. C'est **elle** qui rend la structure
récursive, et c'est elle que le correcteur cherche.

### Comment on le reconnaît

Les formules qui déclenchent Composite :

- « des agencements **composés de** fleurs, d'herbes et de plantes »
- « les fourmis **s'organisent en groupe** »
- « un dossier contient des **sous-dossiers** »
- « on doit pouvoir calculer la taille d'un rayon **comme celle d'un** ouvrage »

Le signal : **une structure qui se contient elle-même**, et qu'on veut traiter
uniformément.

## Décorateur — enrichir sans hériter

### Le problème

Un fichier peut être **compressé**. Ou **chiffré**. Ou les deux. Ou chiffré puis
compressé.

Par héritage, il faudrait : `FichierCompresse`, `FichierChiffre`,
`FichierCompresseEtChiffre`, `FichierChiffreEtCompresse`… Avec quatre traitements,
on arrive à seize classes. **Ça explose.**

### La solution

**Envelopper l'objet dans un autre objet de même interface**, qui ajoute son
traitement.

```java
public abstract class DecorateurFichier implements ElementFichier {

    protected final ElementFichier element;      // UN SEUL, pas une liste

    protected DecorateurFichier(ElementFichier element) {
        this.element = element;
    }
}
```

```java
public class Compresse extends DecorateurFichier {

    public Compresse(ElementFichier e) { super(e); }

    @Override
    public long taille() {
        return element.taille() / 2;             // on modifie le résultat
    }

    @Override
    public String nom() {
        return element.nom() + ".zip";
    }
}
```

```java
public class Chiffre extends DecorateurFichier {

    public Chiffre(ElementFichier e) { super(e); }

    @Override
    public long taille() { return element.taille() + 128; }   // en-tête de chiffrement

    @Override
    public String nom() { return element.nom() + ".enc"; }
}
```

Et voici la magie :

```java
ElementFichier f = new Fichier("rapport.pdf", 1000);
System.out.println(f.taille());                         // 1000

ElementFichier c = new Compresse(f);
System.out.println(c.taille());                         // 500

ElementFichier cc = new Chiffre(new Compresse(f));
System.out.println(cc.taille());                        // 628
System.out.println(cc.nom());                           // rapport.pdf.zip.enc
```

**Les décorateurs s'empilent.** Chacun appelle celui qu'il enveloppe, puis ajoute
sa contribution. Aucune explosion combinatoire : deux classes suffisent pour
quatre combinaisons.

### Le diagramme

```
        ┌──────────────────────────┐
        │       «interface»        │
        │     ElementFichier       │
        └────────────△─────────────┘
              ┌──────┴────────────────────┐
      ┌───────┴────┐   ┌──────────────────┴──────┐
      │  Fichier   │   │ «abstract» Decorateur   │◇──┐
      └────────────┘   ├─────────────────────────┤   │ 1  ← UN SEUL
                       │ # element               │───┘
                       └────────────△────────────┘
                            ┌───────┴────────┐
                     ┌──────┴────┐    ┌──────┴────┐
                     │ Compresse │    │  Chiffre  │
                     └───────────┘    └───────────┘

              ◄── Décorateur
```

### Comment on le reconnaît

- « la fourmi **sans rien** devient une fourmi **avec** de la nourriture »
- « des options qui **s'ajoutent** au prix, **cumulables** »
- « un forfait de base peut recevoir des suppléments »

Le signal : **on enrichit un objet existant**, et les enrichissements **se cumulent**.

## Le mot qui les distingue

Vous l'avez trouvé ?

**Combien.**

| | Composite | Décorateur |
|---|---|---|
| Enveloppe | **plusieurs** objets | **un seul** objet |
| Dans le code | `List<Element> enfants` | `Element element` |
| Sur le diagramme | agrégation `1..*` | référence `1` |
| Sert à | traiter un groupe comme un élément | ajouter une responsabilité |

Un dossier contient **plusieurs** éléments. Une compression enveloppe **un seul**
fichier.

C'est la seule différence structurelle, et elle suffit toujours à trancher.
Quand vous hésitez, posez-vous la question : *combien d'objets là-dedans ?*
MD,
                'recap' => <<<'MD'
**Composite** — composer des objets en arborescence pour que le client traite de la
même façon un objet isolé et un groupe. La clé : le composite implémente **la même
interface** que ses enfants, donc il peut en contenir d'autres.

*Se reconnaît à :* « composé de », « s'organisent en groupe », « sous-dossiers »,
« traité de la même façon ».

**Décorateur** — ajouter dynamiquement une responsabilité à un objet, sans modifier
sa classe. Les décorateurs **s'empilent**.

*Se reconnaît à :* « devient un X avec Y », « options cumulables », « suppléments ».

**La différence, en un mot : combien.**
Composite enveloppe **plusieurs** objets (agrégation `1..*`).
Décorateur en enveloppe **un seul** (référence `1`).

Sur votre diagramme, c'est la multiplicité qui prouve au correcteur que vous avez
compris.
MD,
            ],

            /* ================= Séance 9 ================= */
            [
                'title' => 'État et Stratégie — les jumeaux qu\'il faut distinguer',
                'chapitre' => 'DP-Comp',
                'duree_min' => 30,
                'prerequis' => "La séance 8 : vous avez vu vos deux premiers patrons.",
                'intro' => <<<'MD'
Ces deux patrons ont **le même diagramme**. Exactement le même : une interface, des
implémentations, un contexte qui délègue.

Et pourtant ce sont deux patrons différents, qui répondent à deux problèmes
différents. Les distinguer est probablement la question la plus fréquente de
l'épreuve — les deux sont tombés en 2025, dans le même sujet.

La différence ne se voit pas dans la structure. Elle se voit dans **qui décide**.
MD,
                'body' => <<<'MD'
## État — le comportement change au fil de la vie

### Le problème

Un distributeur automatique. Il passe par des phases : *en attente*, *paiement en
cours*, *distribution*, *hors service*.

Ce qu'on peut faire dépend de la phase. Insérer une pièce quand il est hors service
n'a pas de sens. Choisir un produit avant d'avoir payé non plus.

La version naïve :

```java
public void choisir(Produit p) {
    if (phase.equals("attente")) {
        System.out.println("Insérez d'abord de la monnaie");
    } else if (phase.equals("paiement")) {
        if (credit >= p.prix()) { phase = "distribution"; }
        else { System.out.println("Crédit insuffisant"); }
    } else if (phase.equals("distribution")) {
        System.out.println("Patientez");
    } else if (phase.equals("horsService")) {
        System.out.println("Hors service");
    }
}
```

Et le même bloc de `if` dans `inserer()`, dans `rendreMonnaie()`, dans
`annuler()`. Ajouter une phase demande de retrouver et modifier tous ces blocs.

### La solution

**Faire de chaque phase une classe.**

```java
public interface EtatMachine {
    void inserer(Distributeur d, double montant);
    void choisir(Distributeur d, Produit p);
}
```

```java
public class EnAttente implements EtatMachine {

    @Override
    public void inserer(Distributeur d, double montant) {
        d.crediter(montant);
        d.setEtat(new PaiementEnCours());        // ← l'état déclenche la transition
    }

    @Override
    public void choisir(Distributeur d, Produit p) {
        System.out.println("Insérez d'abord de la monnaie");
    }
}
```

```java
public class PaiementEnCours implements EtatMachine {

    @Override
    public void inserer(Distributeur d, double montant) {
        d.crediter(montant);                      // on reste dans le même état
    }

    @Override
    public void choisir(Distributeur d, Produit p) {
        if (d.credit() >= p.prix()) {
            d.setEtat(new Distribution());        // ← transition
        } else {
            System.out.println("Crédit insuffisant");
        }
    }
}
```

Et le contexte devient minuscule :

```java
public class Distributeur {

    private EtatMachine etat = new EnAttente();   // ← MUTABLE

    public void setEtat(EtatMachine e) { this.etat = e; }

    public void inserer(double m) { etat.inserer(this, m); }
    public void choisir(Produit p) { etat.choisir(this, p); }
}
```

Plus un seul `if`. Ajouter une phase, c'est ajouter une classe — sans toucher aux
autres.

**Retenez la ligne `d.setEtat(new Distribution())`.** C'est l'état lui-même qui
décide de la suite. C'est la signature du patron.

## Stratégie — l'algorithme est choisi de l'extérieur

### Le problème

Le même distributeur calcule ses prix selon une politique : *tarif normal*,
*tarif étudiant*, *happy hour*. La politique est fixée **à l'installation** de la
machine.

### La solution

```java
public interface PolitiqueTarifaire {
    double prix(Produit p);
}

public class TarifNormal implements PolitiqueTarifaire {
    public double prix(Produit p) { return p.prixBase(); }
}

public class TarifEtudiant implements PolitiqueTarifaire {
    public double prix(Produit p) { return p.prixBase() * 0.8; }
}

public class HappyHour implements PolitiqueTarifaire {
    public double prix(Produit p) { return p.prixBase() * 0.5; }
}
```

Et dans le contexte :

```java
public class Distributeur {

    private final PolitiqueTarifaire tarif;       // ← FINAL

    public Distributeur(PolitiqueTarifaire tarif) {
        this.tarif = tarif;                       // reçu de l'extérieur
    }
}
```

```java
Distributeur d = new Distributeur(new TarifEtudiant());
```

## La comparaison — et c'est là que tout se joue

Mettez les deux contextes côte à côte :

```java
// ÉTAT
private EtatMachine etat = new EnAttente();       // mutable
public void setEtat(EtatMachine e) { this.etat = e; }

// STRATÉGIE
private final PolitiqueTarifaire tarif;           // final
public Distributeur(PolitiqueTarifaire t) { this.tarif = t; }
```

| | **État** | **Stratégie** |
|---|---|---|
| **Qui change** | l'objet, **tout seul** | le **client**, de l'extérieur |
| **Quand** | en continu, au fil de la vie | une fois, à la configuration |
| **Le champ** | **mutable** | souvent **`final`** |
| **Qui modifie le champ** | les états concrets eux-mêmes | le constructeur, une fois |
| **L'implémentation connaît le contexte** | **oui**, pour déclencher la suite | non |

**La question qui tranche : qui décide du changement ?**

Les saisons passent toutes seules → **État**.
On choisit *bio* ou *standard* à la création → **Stratégie**.

### Le sujet de janvier 2025

Le jardin. Deux points d'attention consécutifs :

> **#2** — « Le fonctionnement de notre jardin change **en fonction des saisons**.
> En hiver les insectes sont absents… En été tout est vivant… »

Les saisons passent d'elles-mêmes. Personne ne décide qu'on est en été.
→ **État**. C'est bien la réponse du corrigé.

> **#3** — « Lorsque l'on crée un jardin il **faut faire un choix** : on peut
> adopter le mode opératoire *bio* ou *standard*. »

« Il faut faire un choix », « lorsque l'on crée ». C'est décidé de l'extérieur,
une fois pour toutes.
→ **Stratégie**. Réponse du corrigé également.

Les mots de l'énoncé suffisent à trancher, si l'on sait quoi chercher.

## Le diagramme

Les deux se dessinent de la même façon. **C'est pour cela que l'étiquette est
indispensable** — sans elle, le correcteur ne peut pas savoir lequel vous avez voulu.

```
   ┌────────────────────────┐        ┌───────────────────────────┐
   │      «interface»       │        │       «interface»         │
   │      EtatMachine       │        │    PolitiqueTarifaire     │
   └───────────△────────────┘        └─────────────△─────────────┘
      ┌────┬───┴───┬────────┐              ┌───────┼────────┐
 ┌────┴──┐┌┴─────┐┌┴───────┐┌┴─────┐  ┌────┴───┐┌──┴────┐┌──┴──────┐
 │Attente││Paieme││Distrib ││HorsS │  │ Normal ││Etudiant││HappyHour│
 └───────┘└──────┘└────────┘└──────┘  └────────┘└───────┘└─────────┘
             ▲                                  ▲
             │ etat (mutable)                   │ tarif (final)
   ┌─────────┴──────────────────────────────────┴─────┐
   │                  Distributeur                    │
   └──────────────────────────────────────────────────┘

   ◄── État (les phases, qui se succèdent seules)
   ◄── Stratégie (la politique, choisie à l'installation)
```

Remarquez que j'ai précisé `mutable` et `final` sur les rôles. Ce n'est pas exigé,
mais c'est le genre de détail qui montre au correcteur que la distinction est
comprise — et le point de cohérence se joue là.
MD,
                'recap' => <<<'MD'
**État** — le comportement dépend d'une phase qui **évolue seule** au fil de la vie
de l'objet. Le champ est **mutable**, et **les états concrets déclenchent eux-mêmes
la transition** par `contexte.setEtat(...)`.

*Se reconnaît à :* « change selon les saisons », « le jour… la nuit… »,
« traverse plusieurs phases ».

**Stratégie** — un algorithme **interchangeable, choisi de l'extérieur** et qui ne
change pas seul. Le champ est souvent **`final`**, reçu au constructeur.

*Se reconnaît à :* « il faut faire un choix », « selon le mode opératoire »,
« fixé à l'installation ».

**La question qui tranche : qui décide du changement ?**
L'objet lui-même → État. Le client → Stratégie.

**Et puisque les deux diagrammes sont identiques, l'étiquette sur le schéma n'est
pas une formalité : c'est la seule chose qui les distingue aux yeux du correcteur.**
MD,
            ],

            /* ================= Séance 10 ================= */
            [
                'title' => 'Observateur et Visiteur',
                'chapitre' => 'DP-Comp',
                'duree_min' => 30,
                'prerequis' => "La séance 9 : État et Stratégie.",
                'intro' => <<<'MD'
Deux derniers patrons comportementaux, et vous aurez les cinq qui couvrent la
quasi-totalité des sujets.

Ceux-là se confondent moins que les précédents, mais il faut savoir les distinguer
en une phrase : l'un **prévient**, l'autre **parcourt**.
MD,
                'body' => <<<'MD'
## Observateur — prévenir ceux que ça intéresse

### Le problème

Une commande change de statut. Il faut prévenir la comptabilité, le service
d'expédition et le client.

Naïvement :

```java
public void valider() {
    this.statut = "validé";
    comptabilite.enregistrer(this);
    expedition.preparer(this);
    client.envoyerMail(this);
}
```

Trois problèmes.

**Un.** La classe `Commande` connaît maintenant la comptabilité, l'expédition et
la messagerie. Elle dépend de tout le logiciel.

**Deux.** Ajouter un destinataire — le service qualité, par exemple — oblige à
rouvrir `Commande`.

**Trois.** On ne peut plus tester `Commande` sans fabriquer les trois autres.

### La solution

**Inverser la dépendance.** La commande ne connaît qu'une interface, et notifie
sans savoir qui écoute.

```java
public interface Observateur {
    void actualiser(Commande c);
}
```

```java
public class Commande {

    private final List<Observateur> observateurs = new ArrayList<>();
    private String statut = "en préparation";

    public void attacher(Observateur o) { observateurs.add(o); }
    public void detacher(Observateur o) { observateurs.remove(o); }

    private void notifier() {
        for (Observateur o : observateurs) {
            o.actualiser(this);
        }
    }

    public void valider() {
        this.statut = "validé";
        notifier();                     // ← elle prévient, sans savoir qui
    }
}
```

```java
public class Comptabilite implements Observateur {
    @Override
    public void actualiser(Commande c) {
        System.out.println("Facture émise pour " + c.id());
    }
}
```

Et le montage :

```java
Commande c = new Commande();
c.attacher(new Comptabilite());
c.attacher(new Expedition());
c.attacher(new NotificationClient());

c.valider();      // les trois sont prévenus
```

**Les trois méthodes du sujet** — `attacher`, `detacher`, `notifier` — sont ce que
le correcteur cherche. Sans `attacher` et `detacher`, ce n'est qu'une liste de
callbacks figée, pas un Observateur.

### Le diagramme

```
   ┌────────────────────┐  observateurs *  ┌─────────────────────┐
   │     Commande       │◇─────────────────│    «interface»      │
   ├────────────────────┤                  │    Observateur      │
   │ - statut : String  │                  ├─────────────────────┤
   ├────────────────────┤                  │ + actualiser(c)     │
   │ + attacher(o)      │                  └──────────△──────────┘
   │ + detacher(o)      │                       ┌─────┼──────┐
   │ + notifier()       │              ┌────────┴─┐┌──┴────┐┌┴─────────┐
   │ + valider()        │              │Comptabil.││Expedit││NotifClien│
   └────────────────────┘              └──────────┘└───────┘└──────────┘

              ◄── Observateur
```

Remarquez le sens : `Commande` agrège des `Observateur` **par l'interface**.
Elle ne connaît aucune classe concrète. C'est ce qui rend le patron utile.

### Comment on le reconnaît

- « la fourmilière doit **maintenir un compte précis**, en **monitorant** les entrées
  et sorties »
- « chaque objet doit être **informé lorsqu'un autre le touche** »
- « les abonnés souhaitent être **avertis** dès l'ouverture de la billetterie »
- « prévenir le service X dès qu'un produit tombe en rupture »

Le signal : **quelqu'un doit être mis au courant** d'un changement qui a lieu
ailleurs.

## Visiteur — parcourir sans modifier

### Le problème

Vous avez une structure d'objets — disons un parc immobilier avec des `Hotel` et
des `Chambre`. On vous demande un état d'occupation. Vous ajoutez une méthode
`occupation()` dans les deux classes.

Puis on demande un état de maintenance. Puis un état de rentabilité. Puis un état
d'accessibilité.

À chaque fois, il faut **rouvrir `Hotel` et `Chambre`** pour y ajouter une méthode.
Ces classes finissent par contenir vingt traitements qui n'ont rien à voir avec
leur responsabilité.

### La solution

**Sortir les traitements des classes**, et les mettre dans des objets à part
qu'on fait circuler.

```java
public interface VisiteurParc {
    void visiterHotel(Hotel h);
    void visiterChambre(Chambre c);
}
```

```java
public interface ElementParc {
    void accepter(VisiteurParc v);
}

public class Hotel implements ElementParc {
    @Override
    public void accepter(VisiteurParc v) {
        v.visiterHotel(this);              // ← le renvoi. Voir ci-dessous.
    }
}

public class Chambre implements ElementParc {
    @Override
    public void accepter(VisiteurParc v) {
        v.visiterChambre(this);
    }
}
```

```java
public class VisiteurOccupation implements VisiteurParc {

    private int occupees = 0;

    @Override
    public void visiterHotel(Hotel h) { /* rien à compter au niveau hôtel */ }

    @Override
    public void visiterChambre(Chambre c) {
        if (c.estOccupee()) occupees++;
    }

    public int resultat() { return occupees; }
}
```

Usage :

```java
VisiteurOccupation v = new VisiteurOccupation();
for (ElementParc e : parc) {
    e.accepter(v);
}
System.out.println(v.resultat());
```

**Ajouter un traitement = écrire une classe.** `Hotel` et `Chambre` ne bougent pas.

### La double distribution

Il faut comprendre pourquoi `accepter` rappelle `visiterX(this)`. C'est le
mécanisme central du patron, et il porte un nom : la **double distribution**.

```java
element.accepter(visiteur);          // 1er appel : choisit le type d'ÉLÉMENT
    → visiteur.visiterHotel(this);   // 2e appel : choisit le type de VISITEUR
```

Le premier appel est résolu par polymorphisme sur l'élément : Java sait si c'est
un `Hotel` ou une `Chambre`. Le second est résolu sur le visiteur. On combine ainsi
deux dimensions de choix.

**Sans ce renvoi, ce n'est pas un Visiteur.** C'est le détail que le correcteur
vérifie.

### La contrepartie

Le Visiteur a un défaut symétrique de sa qualité :

- Ajouter un **traitement** : facile, une classe de plus. ✅
- Ajouter un **type d'élément** : il faut modifier **tous** les visiteurs, pour
  y ajouter `visiterNouveauType`. ❌

Il convient donc quand la structure est **stable** et les traitements **nombreux
et évolutifs**. C'est exactement le cas décrit par les énoncés d'examen.

### Comment on le reconnaît

- « produire plusieurs états sur l'ensemble du parc… ces traitements **ne doivent
  pas alourdir** les classes… et **d'autres viendront** »
- « calculer un total sur un ensemble hétérogène »

Le signal : **des traitements variés sur une structure fixe**, et la mention
explicite qu'il ne faut pas alourdir les classes.

## Observateur ou Visiteur ?

En une phrase :

> L'**Observateur** *prévient* : quelque chose a changé, que ceux que ça intéresse
> s'adaptent.
>
> Le **Visiteur** *parcourt* : j'applique ce traitement à chaque élément de la
> structure.

L'un est déclenché par un **changement**. L'autre par une **demande de calcul**.

Le corrigé de janvier 2025 accepte d'ailleurs les deux sur un même point
d'attention — *« Visiteur. Note : observateur est également possible »* — ce qui
confirme que **la justification compte**. Si votre choix est défendable et que
vous expliquez pourquoi en une ligne, il passe.
MD,
                'recap' => <<<'MD'
**Observateur** — un objet notifie ses abonnés d'un changement, sans les connaître.

*Les trois méthodes du sujet :* `attacher(o)`, `detacher(o)`, `notifier()`.
*Côté observateur :* `actualiser()`.
*L'agrégation `*` se fait vers l'interface*, jamais vers les classes concrètes.

*Se reconnaît à :* « doit être informé », « monitorer », « avertir », « prévenir ».

**Visiteur** — ajouter un traitement à une structure d'objets sans modifier leurs
classes.

*Le mécanisme central :* la **double distribution**.
`element.accepter(v)` rappelle `v.visiterElement(this)`.
Sans ce renvoi, ce n'est pas un Visiteur.

*La contrepartie :* ajouter un **type** oblige à modifier tous les visiteurs.

*Se reconnaît à :* « ne doivent pas alourdir les classes », « d'autres viendront ».

**La distinction en une phrase :** l'Observateur **prévient**, le Visiteur
**parcourt**.
MD,
            ],

            /* ================= Séance 11 ================= */
            [
                'title' => 'Builder, Singleton et MVC',
                'chapitre' => 'DP-Creat',
                'duree_min' => 25,
                'prerequis' => "Les séances 8 à 10 : les cinq patrons principaux.",
                'intro' => <<<'MD'
Trois patrons pour finir le tour, avec un statut particulier.

**Builder et Singleton** sont **hors scope** aux exercices de conception depuis
2024 — l'énoncé le dit. Mais ils tombent au QCM, donc il faut savoir les
reconnaître.

**MVC** n'est pas un patron au sens des précédents : c'est une **architecture**,
une façon d'organiser tout un logiciel. Il peut servir dans une conception.
MD,
                'body' => <<<'MD'
## Builder — construire lisiblement

### Le problème

Voici un constructeur qui a mal tourné :

```java
Pizza p = new Pizza("grande", true, false, true, true, false, "fine");
```

Que signifie le troisième `false` ? Impossible à dire sans aller lire la
signature. Et si la moitié des ingrédients sont optionnels, il faudrait un
constructeur par combinaison.

### La solution

```java
Pizza p = new Pizza.Builder("grande")
        .avecFromage()
        .avecChampignons()
        .patePaisse()
        .build();
```

Chaque option se nomme. On ne met que ce qu'on veut.

Le code :

```java
public class Pizza {

    private final String taille;
    private final boolean fromage;
    private final boolean champignons;

    private Pizza(Builder b) {                    // constructeur PRIVÉ
        this.taille = b.taille;
        this.fromage = b.fromage;
        this.champignons = b.champignons;
    }

    public static class Builder {                 // classe imbriquée

        private final String taille;              // obligatoire
        private boolean fromage = false;          // optionnels, avec leur défaut
        private boolean champignons = false;

        public Builder(String taille) { this.taille = taille; }

        public Builder avecFromage() {
            this.fromage = true;
            return this;                          // ← rendre « this » permet de chaîner
        }

        public Builder avecChampignons() {
            this.champignons = true;
            return this;
        }

        public Pizza build() { return new Pizza(this); }
    }
}
```

**La clé du chaînage tient en une ligne : `return this`.** Chaque méthode rend le
builder, donc on peut enchaîner l'appel suivant dessus.

### Ce que le QCM demande

*« Dans quelles situations le Builder est-il préconisé ? »* — trois réponses :

1. L'objet final est **imposant** et sa création complexe.
2. **Beaucoup d'arguments** doivent être passés à la construction.
3. Certains arguments sont **optionnels**.

Attention au piège : *« les arguments doivent être correctement instanciés »*
**n'en fait pas partie**. C'était la proposition c de la question 10 en 2025.

## Singleton — une seule instance

### Le problème

Certains objets ne doivent exister qu'en un exemplaire : la configuration de
l'application, le journal d'événements, le pool de connexions. Deux exemplaires
n'auraient pas de sens, et créeraient des incohérences.

### La solution

```java
public class Journal {

    private static Journal instance;              // ① l'unique exemplaire

    private Journal() { }                         // ② constructeur PRIVÉ

    public static Journal getInstance() {         // ③ seule porte d'accès
        if (instance == null) {
            instance = new Journal();
        }
        return instance;
    }

    public void ecrire(String message) {
        System.out.println("[" + LocalDateTime.now() + "] " + message);
    }
}
```

Usage :

```java
Journal.getInstance().ecrire("Démarrage");
```

**Les trois éléments sont indispensables**, et c'est ce qu'on demande :

1. Un attribut **`static`** qui garde l'instance.
2. Un **constructeur privé** — c'est lui qui interdit `new Journal()`.
   Retirez-le et le patron n'existe plus.
3. Une méthode **`static getInstance()`**.

## MVC — répartir le code en trois couches

### Le problème

Un programme graphique mélange trois choses très différentes : les **données**,
leur **affichage**, et la **réaction aux clics**. Sans discipline, tout se retrouve
dans les mêmes classes. Changer la couleur d'un bouton oblige à toucher au calcul
du solde bancaire.

### La solution

Trois couches, avec une règle stricte sur ce que chacune contient — et surtout sur
ce qu'elle **ne contient pas**.

| Couche | Contient | Ne contient **jamais** |
|---|---|---|
| **Modèle** | les données et les règles métier | aucun code d'affichage |
| **Vue** | l'affichage | aucune règle métier |
| **Contrôleur** | la réaction aux actions | ni données ni affichage |

### Le cycle, en cinq temps

```
   utilisateur
       │ ① agit sur
       ▼
  ┌─────────┐  ② transmet  ┌────────────┐  ③ met à jour  ┌─────────┐
  │   Vue   │─────────────▶│ Controleur │───────────────▶│ Modele  │
  └────△────┘              └────────────┘                └────┬────┘
       │                                                      │
       └──────────────── ④ notifie ───────────────────────────┘
       ⑤ se redessine
```

1. L'utilisateur agit sur la **vue**.
2. La vue transmet au **contrôleur**.
3. Le contrôleur met à jour le **modèle**.
4. Le modèle **notifie** ses observateurs.
5. La vue se redessine.

**L'étape 4 est celle qui tombe.** Le modèle ne connaît pas la vue — sinon il
serait inutilisable sans interface graphique, on ne pourrait pas avoir deux vues
du même modèle, et le tester exigerait de fabriquer une vue.

Il notifie donc par une **interface**. Autrement dit : **MVC repose sur le patron
Observateur.**

C'est le lien entre les deux, et c'est une question de cours classique.

Le polycopié illustre MVC par **Swing**, la bibliothèque graphique de Java.
MD,
                'recap' => <<<'MD'
**Builder** — construction lisible d'un objet complexe. Chaque méthode rend `this`,
ce qui permet le chaînage. Un `build()` final.

*Les trois situations préconisées :* objet **imposant**, **beaucoup d'arguments**,
arguments **optionnels**. *(« correctement instanciés » n'en fait pas partie.)*

**Singleton** — une seule instance. **Trois éléments** : attribut `static`,
**constructeur privé**, méthode `static getInstance()`.

**Les deux sont hors scope** aux exercices de conception depuis 2024, mais tombent
au QCM.

**MVC** — trois couches. Modèle = données et règles. Vue = affichage. Contrôleur =
réaction aux actions.

*Le cycle en cinq temps*, et l'étape 4 est la clé : **le modèle notifie la vue par
le patron Observateur**, parce qu'il ne doit pas la connaître.
MD,
            ],

            /* ================= Séance 12 ================= */
            [
                'title' => 'Composer un exercice de conception, du début à la fin',
                'chapitre' => 'DP-Method',
                'duree_min' => 35,
                'prerequis' => "Toutes les séances précédentes. C'est la dernière : on met tout ensemble.",
                'intro' => <<<'MD'
Dernière séance. Vous connaissez les patrons, vous savez dessiner. Il reste à
enchaîner les deux **dans les conditions de l'épreuve**.

On va prendre un sujet complet et le traiter ensemble, du moment où vous lisez
l'énoncé jusqu'à la copie rendue. Pas de nouvelle notion : de la méthode.
MD,
                'body' => <<<'MD'
## Le sujet

> Vous devez réaliser un logiciel pour gérer une **bibliothèque municipale**.
> On y trouve des **livres**, des **revues**, des **DVD**, des **rayons** et des
> **lecteurs**.
>
> Faites une modélisation objet complète, puis répondez aux trois points
> d'attention.
>
> **#1** — Les rayons contiennent des ouvrages, mais aussi des sous-rayons
> thématiques qui contiennent eux-mêmes des ouvrages. On doit pouvoir compter les
> ouvrages d'un rayon comme d'un sous-rayon, de la même façon.
>
> **#2** — Un ouvrage passe par plusieurs situations : *disponible*, *emprunté*,
> *réservé*, *en réparation*. Ce qu'on peut en faire dépend de la situation du
> moment, et le passage de l'une à l'autre se fait au fil de sa vie.
>
> **#3** — Les lecteurs peuvent demander à être prévenus dès qu'un ouvrage qu'ils
> attendent redevient disponible.

## Étape 1 — lire et souligner (3 minutes)

Ne dessinez rien encore. Lisez et soulignez.

**Les noms** — ce sont vos classes candidates :
livre, revue, DVD, rayon, lecteur, ouvrage.

**Les points d'attention** — un patron chacun. Repérez le mot déclencheur :

| Point | Mot déclencheur | Patron |
|---|---|---|
| #1 | « contiennent… **sous-rayons**… **de la même façon** » | **Composite** |
| #2 | « plusieurs situations… **au fil de sa vie** » | **État** |
| #3 | « demander à être **prévenus** » | **Observateur** |

Trois patrons trouvés en trois minutes. Écrivez-les au brouillon tout de suite,
avant même de dessiner : c'est trois points sécurisés.

## Étape 2 — la hiérarchie (5 minutes)

`Livre`, `Revue` et `DVD` ont des choses en commun : un titre, une cote, un état.
Ils partagent donc du **code** → **classe abstraite** (règle de la séance 5).

```
   ┌──────────────────────────────┐
   │     «abstract» Ouvrage       │
   ├──────────────────────────────┤
   │ # titre : String             │
   │ # cote : String              │
   ├──────────────────────────────┤
   │ + emprunter()                │
   │ + dureeEmprunt() : int       │
   └──────────────△───────────────┘
        ┌─────────┼─────────┐
   ┌────┴───┐ ┌───┴───┐ ┌───┴───┐
   │ Livre  │ │ Revue │ │  DVD  │
   └────────┘ └───────┘ └───────┘
```

Voilà **le point « notions objet » acquis** : une classe abstraite et un héritage.
Cinq minutes, un point.

## Étape 3 — le Composite (8 minutes)

Point #1 : un rayon contient des ouvrages **et** des sous-rayons, comptés de la
même façon.

Réflexe Composite : il faut une **interface commune** au-dessus de l'ouvrage et du
rayon.

```
        ┌──────────────────────────────┐
        │        «interface»           │
        │     ElementBibliotheque      │
        ├──────────────────────────────┤
        │ + nombreOuvrages() : int     │
        └──────────────△───────────────┘
             ┌─────────┴──────────┐
   ┌─────────┴────────┐   ┌───────┴────────┐
   │ «abstract»       │   │     Rayon      │◇──┐
   │    Ouvrage       │   ├────────────────┤   │ 1..*
   └──────────────────┘   │ + ajouter(e)   │───┘
                          │ + nombreOuvr() │
                          └────────────────┘

        ◄── Composite
```

`Rayon` agrège `1..*` `ElementBibliotheque` — donc potentiellement d'autres rayons.
C'est la récursion, et c'est ce que le correcteur cherche.

**N'oubliez pas l'étiquette.** Écrivez-la maintenant, pas à la fin.

## Étape 4 — l'État (8 minutes)

Point #2 : les situations d'un ouvrage, qui se succèdent au fil de sa vie.

C'est bien État et non Stratégie : personne ne « choisit » qu'un livre est emprunté,
cela résulte de son usage.

```
        ┌──────────────────────────┐
        │       «interface»        │
        │       EtatOuvrage        │
        ├──────────────────────────┤
        │ + emprunter(o: Ouvrage)  │
        │ + rendre(o: Ouvrage)     │
        └────────────△─────────────┘
        ┌──────┬─────┴─────┬─────────┐
   ┌────┴────┐┌┴────────┐┌─┴──────┐┌─┴──────────┐
   │Disponibl││Emprunte ││Reserve ││EnReparation│
   └─────────┘└─────────┘└────────┘└────────────┘
                    ▲
                    │ etat  (mutable)
        ┌───────────┴──────────────┐
        │   «abstract» Ouvrage     │
        │ + setEtat(e)             │
        └──────────────────────────┘

        ◄── État
```

Le rôle `etat` est **mutable**, et les états concrets appellent
`ouvrage.setEtat(...)`. Précisez-le : c'est ce qui prouve que vous ne confondez pas
avec Stratégie.

## Étape 5 — l'Observateur (8 minutes)

Point #3 : les lecteurs veulent être prévenus.

```
   ┌────────────────────────┐  observateurs *  ┌──────────────────┐
   │  «abstract» Ouvrage    │◇─────────────────│   «interface»    │
   ├────────────────────────┤                  │   Observateur    │
   │ + attacher(o)          │                  ├──────────────────┤
   │ + detacher(o)          │                  │ + actualiser(ouv)│
   │ + notifier()           │                  └────────△─────────┘
   └────────────────────────┘                           │
                                                 ┌──────┴──────┐
                                                 │   Lecteur   │
                                                 └─────────────┘

        ◄── Observateur
```

Les trois méthodes sur le sujet, `actualiser` sur l'observateur, agrégation `*`
vers l'**interface**.

Et le lien avec l'étape précédente : c'est le passage à l'état `Disponible` qui
appelle `notifier()`. Mentionnez-le — c'est le point de cohérence.

## Étape 6 — les justifications (5 minutes)

Sous le schéma, trois lignes :

> **Composite** — un rayon contient des ouvrages et des sous-rayons ; l'interface
> `ElementBibliotheque` permet de compter les uns comme les autres.
>
> **État** — les situations de l'ouvrage se succèdent au fil de sa vie, et chaque
> état déclenche la transition suivante. Le champ `etat` est mutable.
>
> **Observateur** — les lecteurs s'abonnent à un ouvrage ; le passage à l'état
> `Disponible` déclenche `notifier()`.

Cinq minutes, et c'est le point de cohérence.

## Étape 7 — la relecture (2 minutes par exercice)

Une seule question, posée trois fois :

> **Est-ce que chaque diagramme porte trois noms de patrons ?**

C'est neuf points sur vingt qui dépendent de cette vérification. Faites-la.

## Le bilan du temps

| Étape | Durée |
|---|---|
| Lire et souligner | 3 min |
| Hiérarchie | 5 min |
| Patron 1 | 8 min |
| Patron 2 | 8 min |
| Patron 3 | 8 min |
| Justifications | 5 min |
| Relecture | 2 min |
| **Total** | **39 min** |

Vous en avez 45 par exercice. La marge est confortable.

## Ce qu'il faut retenir de tout le cours

Vous avez suivi douze séances. Si vous ne deviez garder que trois choses :

**Un. Dessinez.** Le 0/20 de janvier ne venait pas d'une lacune de connaissances —
les patrons choisis étaient les bons. Il venait du format. Des rectangles, des
traits typés, des multiplicités.

**Deux. Nommez les patrons sur le schéma.** L'énoncé le dit noir sur blanc :
sans le nom, pas de point. C'est trois points par exercice, neuf au total, et ils
se perdent par simple oubli.

**Trois. Cinq patrons suffisent.** Composite, État, Stratégie, Observateur,
Décorateur. Sachez les reconnaître aux mots de l'énoncé, et vous aurez traité la
quasi-totalité des points d'attention posés depuis quatre ans.

Bon courage pour le 24 août.
MD,
                'recap' => <<<'MD'
**La méthode en sept étapes, pour 45 minutes :**

| Étape | Durée |
|---|---|
| 1. Lire et souligner les noms et les mots déclencheurs | 3 min |
| 2. Poser la hiérarchie — classe abstraite + héritage | 5 min |
| 3-5. Un patron par point d'attention, **étiqueté au fur et à mesure** | 8 min chacun |
| 6. Trois lignes de justification sous le schéma | 5 min |
| 7. Relecture : chaque schéma porte-t-il trois noms ? | 2 min |

**Les trois choses à retenir de tout le cours :**

1. **Dessinez** — boîtes, traits typés, multiplicités. Pas de plan indenté.
2. **Nommez les patrons sur le schéma** — sans le nom, pas de point.
3. **Cinq patrons suffisent** — Composite, État, Stratégie, Observateur, Décorateur.
MD,
            ],
        ];
    }
}