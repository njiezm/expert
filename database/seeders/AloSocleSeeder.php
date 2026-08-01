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
 * ALO — le socle, écrit pour quelqu'un qui part de zéro.
 *
 * Les fiches existantes visaient les erreurs de la copie de janvier ; elles
 * supposaient acquis le vocabulaire objet. Celles-ci le construisent : aucun
 * terme n'est employé avant d'avoir été défini, aucun symbole avant d'avoir été
 * lu à voix haute une première fois.
 *
 * Elles sont placées en position 0 pour ouvrir chaque chapitre.
 */
class AloSocleSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'ALO')->first();

        if (! $subject) {
            return;
        }

        foreach ($this->content() as $code => $data) {
            $chapter = Chapter::where('subject_id', $subject->id)->where('code', $code)->first();

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

    /* ==================================================================== */

    private function content(): array
    {
        return [

            /* ============================================================
             | C1-Objet — la toute première fiche du module
             ============================================================ */
            'C1-Objet' => [
                'lessons' => [
                    [
                        'title' => 'Qu\'est-ce qu\'un objet ? — depuis zéro',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
Oubliez l'informatique une minute.

Vous avez un **compte bancaire**. Il a un numéro, un solde, un titulaire. Et on peut
faire trois choses avec : déposer de l'argent, en retirer, consulter le solde.

Voilà un objet. Rien de plus.

Un **objet**, c'est un paquet qui rassemble deux choses :
- **ce qu'il sait** — son numéro, son solde, son titulaire ;
- **ce qu'il sait faire** — déposer, retirer, consulter.

Toute la programmation objet part de là. Le reste du module consiste à donner des
noms précis à ce paquet et à ses variantes.

**Le vocabulaire, tout de suite.** Ce qu'un objet sait s'appelle ses **attributs**.
Ce qu'il sait faire s'appelle ses **méthodes**. Un attribut, c'est une donnée ;
une méthode, c'est une action.
MD,
                        'formalism' => <<<'MD'
**Classe et objet — la distinction fondatrice**

Une **classe** est un moule. Un **objet** est ce qui sort du moule.

La classe `CompteBancaire` décrit ce que tout compte possède : un numéro, un solde,
des opérations. Mais elle n'est le compte de personne. Votre compte à vous, avec son
numéro et ses 1 240 euros, est un **objet** — on dit aussi une **instance** de la
classe.

Une classe, des milliers d'objets. Le moule est unique, les gâteaux sont nombreux.

**Trois mots pour dire ce qu'un objet est**

Tout objet a :

| Notion | Ce que c'est | Sur votre compte |
|---|---|---|
| **L'état** | la valeur de ses attributs *maintenant* | solde = 1 240 € |
| **Le comportement** | ce qu'il sait faire | déposer, retirer |
| **L'identité** | ce qui le distingue des autres, même identiques | numéro FR76… |

L'identité mérite un mot. Deux comptes peuvent avoir exactement le même solde et le
même titulaire, ils restent **deux comptes différents**. C'est ce qui distingue un
objet d'une simple valeur : deux fois le nombre 5, c'est le même 5 ; deux comptes
à 5 euros, ce sont deux comptes.

**L'encapsulation — le principe le plus important du module**

Reprenez votre compte. Pouvez-vous écrire directement « solde = 1 000 000 » ?
Non. Vous devez passer par un **dépôt**, qui vérifie que l'argent existe.

C'est l'**encapsulation** : les attributs sont **cachés**, et l'on n'y touche qu'à
travers des méthodes qui contrôlent ce qui se passe.

En notation UML, cela s'écrit avec deux symboles :

- **`-`** signifie **privé** — inaccessible depuis l'extérieur.
- **`+`** signifie **public** — utilisable par tout le monde.

```
┌─────────────────────────┐
│     CompteBancaire      │
├─────────────────────────┤
│ - numero : String       │   ← privé : personne n'y touche directement
│ - solde : double        │
├─────────────────────────┤
│ + deposer(m : double)   │   ← public : la seule porte d'entrée
│ + retirer(m : double)   │
│ + getSolde() : double   │
└─────────────────────────┘
```

Retenez le réflexe : **attributs privés, méthodes publiques**. C'est vrai dans
quasiment tous les diagrammes que vous rendrez.
MD,
                        'worked_example' => <<<'MD'
**Le même compte, écrit en Java, ligne par ligne.**

```java
public class CompteBancaire {

    // ---- Les attributs : ce que l'objet sait ----
    private String numero;       // « private » = personne d'autre ne peut y toucher
    private double solde;        // « double » = un nombre à virgule

    // ---- Le constructeur : comment on fabrique un objet ----
    public CompteBancaire(String numero) {
        this.numero = numero;    // « this » désigne l'objet en train d'être créé
        this.solde = 0;
    }

    // ---- Les méthodes : ce que l'objet sait faire ----
    public void deposer(double montant) {
        if (montant <= 0) {
            throw new IllegalArgumentException("Le montant doit être positif");
        }
        this.solde = this.solde + montant;
    }

    public boolean retirer(double montant) {
        if (montant > this.solde) {
            return false;        // refus : pas assez d'argent
        }
        this.solde = this.solde - montant;
        return true;
    }

    public double getSolde() {
        return this.solde;
    }
}
```

**Décryptage des mots que vous venez de croiser :**

| Mot | Ce qu'il fait |
|---|---|
| `public class` | déclare une classe, visible partout |
| `private` | l'attribut est caché du monde extérieur |
| **constructeur** | méthode spéciale qui porte le **nom de la classe** et fabrique l'objet |
| `this` | « l'objet courant », celui sur lequel la méthode s'exécute |
| `void` | la méthode ne rend **rien** |
| `return` | la méthode rend une valeur et s'arrête |
| `throw` | signale une erreur et interrompt |

**Et on s'en sert comme ça :**

```java
CompteBancaire c = new CompteBancaire("FR76 1234");   // « new » fabrique l'objet
c.deposer(1240);                                       // le point appelle une méthode
System.out.println(c.getSolde());                      // affiche 1240.0

c.solde = 1000000;   // ERREUR de compilation : solde est private
```

La dernière ligne est le cœur de l'encapsulation. Le compilateur **refuse** de vous
laisser tricher.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre classe et objet.** La classe est le moule, l'objet est ce qui en sort.
  « Une classe est instanciée » signifie qu'on fabrique un objet à partir d'elle.
- **`MaClasse obj;` ne crée aucun objet.** Cette ligne déclare seulement une variable,
  qui vaut **`null`** — c'est-à-dire « rien ». Il faut `new MaClasse()` pour fabriquer.
  *(Question 2 du QCM de janvier 2025.)*
- **Attributs publics.** Écrire `public double solde;` détruit l'encapsulation :
  n'importe qui peut mettre n'importe quoi. Le réflexe est `private`.
- **Oublier `this`.** Dans `this.numero = numero`, `this.numero` est l'attribut de
  l'objet et `numero` le paramètre reçu. Sans `this`, on affecte le paramètre à
  lui-même et l'attribut reste vide.
- **Croire que deux objets identiques sont le même.** Ils ont la même **valeur**
  d'attributs mais des **identités** différentes.
MD,
                        'examiner_expects' => <<<'MD'
Sur un diagramme, pour toute classe :

- [ ] Les attributs en **`-`** (privé).
- [ ] Les méthodes en **`+`** (public).
- [ ] Le **type** après le deux-points : `- solde : double`, `+ getSolde() : double`.

Sur une question de cours :

- [ ] La distinction **classe / objet** énoncée sans hésitation.
- [ ] Les trois caractéristiques d'un objet : **état, comportement, identité**.
- [ ] L'**encapsulation** définie comme « attributs cachés, accès par méthodes ».
MD,
                        'source_refs' => [['label' => 'alo_V9.pdf § 1.1 — Le modèle objet']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Classe et objet : quelle différence, en une phrase ?',
                        'back' => "**La classe est le moule, l'objet est ce qui en sort.**\n\n`CompteBancaire` décrit ce que tout compte possède. Votre compte, avec ses 1 240 €, est un **objet** — on dit aussi une **instance**.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Les trois caractéristiques d’un objet ?',
                        'back' => "**L'état** — la valeur de ses attributs maintenant.\n**Le comportement** — ce qu'il sait faire.\n**L'identité** — ce qui le distingue, même de son sosie.\n\nDeux comptes au même solde restent deux comptes.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce que l'encapsulation ?",
                        'back' => "**Les attributs sont cachés (`private`), et l'on n'y accède qu'à travers des méthodes (`public`) qui contrôlent ce qui se passe.**\n\nOn ne fixe pas un solde à la main : on passe par `deposer()`, qui vérifie.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'En UML, que signifient `-` et `+` devant un membre ?',
                        'back' => "**`-`** → **privé**, inaccessible de l'extérieur. C'est le cas des attributs.\n**`+`** → **public**, utilisable par tous. C'est le cas des méthodes.\n\nÉcrit ainsi : `- solde : double`, `+ getSolde() : double`.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce qu'un constructeur ?",
                        'back' => "**Une méthode spéciale qui porte le nom de la classe et fabrique l'objet.**\n\n```java\npublic CompteBancaire(String numero) {\n    this.numero = numero;\n}\n```\n\nAppelée par `new CompteBancaire(\"FR76\")`.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'À quoi sert `this` ?',
                        'back' => "**`this` désigne l'objet courant**, celui sur lequel la méthode s'exécute.\n\nDans `this.numero = numero`, `this.numero` est l'**attribut** et `numero` le **paramètre**. Sans `this`, on affecte le paramètre à lui-même et l'attribut reste vide.",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Votre première classe — la bibliothèque',
                        'origin' => 'genere',
                        'est_minutes' => 25,
                        'difficulty' => 2,
                        'statement' => <<<'MD'
On veut modéliser un **livre** dans une bibliothèque. Un livre a un titre, un auteur,
un numéro ISBN, et il est soit disponible, soit emprunté.

**1.** Listez les **attributs** et les **méthodes** du livre. Pour chaque attribut,
donnez son type. *(2 pts)*

**2.** Écrivez la classe `Livre` en Java : attributs privés, constructeur, méthodes
publiques. La méthode `emprunter()` doit refuser si le livre est déjà emprunté. *(4 pts)*

**3.** Dessinez la boîte UML correspondante, avec les `-` et les `+`. *(2 pts)*

**4.** Écrivez trois lignes de code qui créent un livre, l'empruntent, et affichent
sa disponibilité. *(1 pt)*

**5.** Pourquoi l'attribut `disponible` doit-il être privé ? Que se passerait-il
s'il était public ? *(1 pt)*
MD,
                        'hint' => "Pour la question 2, souvenez-vous que le constructeur porte le nom de la classe et n'a pas de type de retour. Et qu'un livre neuf est disponible.",
                        'method' => <<<'MD'
1. Relisez l'énoncé et soulignez les **noms** : ce sont vos attributs.
   Soulignez les **verbes** : ce sont vos méthodes.
2. Chaque attribut reçoit un type : `String` pour du texte, `boolean` pour vrai/faux,
   `int` pour un entier, `double` pour un nombre à virgule.
3. Le constructeur initialise tout, y compris l'état de départ.
4. Une méthode qui peut échouer rend un `boolean`.
MD,
                        'solution' => <<<'MD'
**1. Attributs et méthodes**

| Attribut | Type |
|---|---|
| titre | `String` |
| auteur | `String` |
| isbn | `String` |
| disponible | `boolean` |

Méthodes : `emprunter()`, `rendre()`, `estDisponible()`, et les accesseurs
`getTitre()`, `getAuteur()`.

**2. La classe Java**

```java
public class Livre {

    private String titre;
    private String auteur;
    private String isbn;
    private boolean disponible;

    public Livre(String titre, String auteur, String isbn) {
        this.titre = titre;
        this.auteur = auteur;
        this.isbn = isbn;
        this.disponible = true;      // un livre neuf est disponible
    }

    public boolean emprunter() {
        if (!this.disponible) {
            return false;            // déjà emprunté : on refuse
        }
        this.disponible = false;
        return true;
    }

    public void rendre() {
        this.disponible = true;
    }

    public boolean estDisponible() {
        return this.disponible;
    }

    public String getTitre() { return this.titre; }
    public String getAuteur() { return this.auteur; }
}
```

**3. La boîte UML**

```
┌──────────────────────────────┐
│            Livre             │
├──────────────────────────────┤
│ - titre : String             │
│ - auteur : String            │
│ - isbn : String              │
│ - disponible : boolean       │
├──────────────────────────────┤
│ + emprunter() : boolean      │
│ + rendre()                   │
│ + estDisponible() : boolean  │
│ + getTitre() : String        │
└──────────────────────────────┘
```

**4. Utilisation**

```java
Livre l = new Livre("Le Petit Prince", "Saint-Exupéry", "978-2070612758");
l.emprunter();
System.out.println(l.estDisponible());   // affiche false
```

**5. Pourquoi privé ?**

S'il était public, n'importe quel code pourrait écrire `l.disponible = true;`
alors que le livre est physiquement chez un lecteur. La règle métier — « on ne
rend disponible que par la méthode `rendre()` » — serait contournable.

C'est exactement l'encapsulation : **l'attribut est caché pour que la règle qui le
protège soit obligatoire**.
MD,
                        'rubric' => [
                            ['label' => 'Les quatre attributs identifiés avec leur type', 'points' => 2],
                            ['label' => 'Attributs déclarés `private`', 'points' => 1],
                            ['label' => 'Constructeur portant le nom de la classe, initialisant disponible à true', 'points' => 1],
                            ['label' => '`emprunter()` refuse si le livre est déjà emprunté', 'points' => 2],
                            ['label' => 'Boîte UML à trois compartiments avec les `-` et `+`', 'points' => 2],
                            ['label' => 'Les types apparaissent après les deux-points', 'points' => 1],
                            ['label' => 'Trois lignes utilisant `new` puis l’appel par point', 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ============================================================
             | C1-Concept — relations, héritage, interfaces
             ============================================================ */
            'C1-Concept' => [
                'lessons' => [
                    [
                        'title' => 'Relier les classes entre elles',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
Une classe seule ne sert à rien. Un logiciel, c'est des classes **reliées**.

Et il n'existe que **cinq façons** de relier deux classes. Les connaître, c'est
savoir dessiner n'importe quel diagramme — et c'est 15 points sur 20 à l'épreuve
d'ALO.

Prenons des exemples du quotidien :

| Situation | Relation |
|---|---|
| Un **chien** est un **animal** | héritage |
| Une **voiture** a un **propriétaire** | association |
| Une **équipe** regroupe des **joueurs** | agrégation |
| Une **maison** contient des **pièces** | composition |
| Un **canard** sait **voler** | implémentation d'interface |

La différence entre les trois du milieu est subtile mais elle tombe à chaque examen.
Voici le critère qui tranche.
MD,
                        'formalism' => <<<'MD'
**1. L'héritage — « est un »**

Un chien **est un** animal. Il possède tout ce qu'a un animal, plus ses spécificités.

```
┌──────────┐
│  Animal  │
└─────△────┘
      │            ← triangle creux, trait plein
┌─────┴────┐
│  Chien   │
└──────────┘
```

En Java : `class Chien extends Animal`.

Le test : *« un chien est-il un animal ? »* Oui → héritage.
*« une voiture est-elle un moteur ? »* Non → ce n'est pas de l'héritage.

**2. L'association — « connaît »**

Deux objets se connaissent, mais ni l'un ni l'autre ne fait partie de l'autre.
Leurs vies sont **indépendantes**.

```
┌───────────┐  1      1  ┌──────────────┐
│  Voiture  │────────────│ Proprietaire │
└───────────┘            └──────────────┘
```

Trait simple, sans décoration. Les nombres aux extrémités sont les **multiplicités** :
combien d'objets de chaque côté.

*Attention :* à la question « la relation entre une voiture et son propriétaire »,
le corrigé de janvier 2025 répond **association**. Pas agrégation : une voiture
n'est pas un morceau de son propriétaire.

**3. L'agrégation — « regroupe », les parties survivent**

Une équipe regroupe des joueurs. Si l'équipe est dissoute, **les joueurs existent
toujours**.

```
┌──────────┐ 1      * ┌─────────┐
│  Equipe  │◇─────────│  Joueur │
└──────────┘          └─────────┘
```

Le **losange creux** est du côté du tout.

**4. La composition — « possède », les parties meurent avec le tout**

Une maison contient des pièces. Si la maison est démolie, **les pièces disparaissent** :
une pièce n'existe pas sans sa maison.

```
┌──────────┐ 1      * ┌─────────┐
│  Maison  │◆─────────│  Piece  │
└──────────┘          └─────────┘
```

Le **losange plein**, toujours du côté du tout.

**Le test qui tranche agrégation / composition :** *si je détruis le tout, la partie
survit-elle ?* Oui → agrégation. Non → composition.

**5. L'implémentation — « sait faire »**

Une **interface** est un contrat : une liste de méthodes, sans aucun code.
Elle dit *ce qu'il faut savoir faire*, pas *comment*.

```java
public interface Volant {
    void voler();        // pas de corps : juste la signature
}

public class Canard extends Animal implements Volant {
    public void voler() {
        System.out.println("Le canard s'envole");
    }
}
```

```
┌─────────────┐
│ «interface» │
│   Volant    │
└──────△──────┘
       ┊             ← triangle creux, trait POINTILLÉ
┌──────┴──────┐
│   Canard    │
└─────────────┘
```

**Les multiplicités**, à poser aux extrémités :

| Écriture | Sens |
|---|---|
| `1` | exactement un |
| `*` | zéro ou plusieurs |
| `1..*` | au moins un |
| `0..1` | zéro ou un |
MD,
                        'worked_example' => <<<'MD'
**Interface ou classe abstraite ?** La question tombe tout le temps.

Une **classe abstraite** est une classe **incomplète** : elle a du code, mais il lui
manque des morceaux que les filles devront fournir. On ne peut pas l'instancier.

```java
public abstract class Animal {

    protected String nom;                    // « protected » = visible des filles

    public Animal(String nom) {
        this.nom = nom;
    }

    public void dormir() {                   // du VRAI code, hérité tel quel
        System.out.println(nom + " dort");
    }

    public abstract void crier();            // pas de corps : à chaque fille de dire
}

public class Chien extends Animal {

    public Chien(String nom) { super(nom); } // « super » appelle le constructeur du père

    @Override
    public void crier() {                    // obligatoire : la méthode était abstraite
        System.out.println("Ouaf");
    }
}
```

```java
Animal a = new Animal("x");    // ERREUR : une classe abstraite ne s'instancie pas
Animal a = new Chien("Rex");   // correct : Chien est complet
```

**La comparaison, en tableau :**

| | Interface | Classe abstraite |
|---|---|---|
| Contient du code | non (ou très peu) | oui |
| Contient des attributs | non | oui |
| On peut en hériter de plusieurs | **oui** | **non**, une seule |
| Relation exprimée | « sait faire » | « est un » |
| Mot-clé | `implements` | `extends` |
| Trait UML | pointillé | plein |

**La règle de choix :** si les classes partagent du **code**, classe abstraite.
Si elles partagent seulement une **capacité**, interface.

Un canard *est un* animal (abstraite) et *sait* voler (interface). Il peut être les
deux à la fois — c'est même le cas le plus fréquent dans les sujets d'examen.

**Le principe de Liskov**, cité dans le polycopié : *partout où l'on attend un
`Animal`, on doit pouvoir mettre un `Chien` sans que rien ne casse.* Si votre
héritage viole cette règle, ce n'est pas un héritage — c'est probablement une
association.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre agrégation et association.** L'agrégation suppose un rapport
  **tout / partie**. Une voiture et son propriétaire n'en ont pas : c'est une association.
- **Confondre agrégation et composition.** Le seul critère : *la partie survit-elle
  à la destruction du tout ?* Oui → agrégation (losange creux). Non → composition
  (losange plein).
- **Utiliser l'héritage pour « a un ».** Une voiture **a un** moteur, elle n'**est
  pas** un moteur. C'est une composition.
- **Oublier les multiplicités.** Un trait sans `1`, `*` ou `1..*` est incomplet.
- **Croire qu'on peut hériter de plusieurs classes en Java.** Impossible : une seule
  classe mère. En revanche, **autant d'interfaces qu'on veut**.
- **Instancier une classe abstraite.** `new Animal()` ne compile pas.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Le **bon trait** : triangle creux pour l'héritage, pointillé pour
      l'implémentation, losange creux pour l'agrégation, plein pour la composition.
- [ ] Les **multiplicités** aux extrémités.
- [ ] Le stéréotype **`«interface»`** au-dessus du nom, ou le nom en italique pour
      une classe abstraite.
- [ ] Au moins **une interface ou une classe abstraite** dans le diagramme :
      c'est un point entier du barème, et il s'obtient presque gratuitement.
MD,
                        'source_refs' => [['label' => 'alo_V9.pdf § 1.1.8 à 1.4 — Relations et principes']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Agrégation ou composition : le test qui tranche ?',
                        'back' => "**Si je détruis le tout, la partie survit-elle ?**\n\n**Oui** → agrégation, losange **creux** ◇ (équipe / joueurs).\n**Non** → composition, losange **plein** ◆ (maison / pièces).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Les cinq relations UML et leur trait ?',
                        'back' => "**Héritage** `──▷` triangle creux, trait plein — « est un »\n**Implémentation** `┈┈▷` triangle creux, pointillé — « sait faire »\n**Agrégation** `──◇` losange creux — les parties survivent\n**Composition** `──◆` losange plein — les parties meurent avec\n**Association** `────` trait simple — se connaissent",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Interface ou classe abstraite : comment choisir ?',
                        'back' => "**Du code partagé** → classe abstraite (`extends`, une seule).\n**Une capacité partagée** → interface (`implements`, autant qu'on veut).\n\nUn canard **est un** animal (abstraite) et **sait** voler (interface).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que signifient les multiplicités 1, *, 1..*, 0..1 ?',
                        'back' => "`1` — exactement un\n`*` — zéro ou plusieurs\n`1..*` — au moins un\n`0..1` — zéro ou un\n\nÀ poser **aux deux extrémités** du trait. Un trait sans multiplicité est incomplet.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Énoncez le principe de substitution de Liskov.',
                        'back' => "**Partout où l'on attend un objet de la classe mère, on doit pouvoir mettre un objet d'une classe fille sans que rien ne casse.**\n\nSi votre héritage viole cette règle, ce n'en est pas un — c'est probablement une association.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'En Java, de combien de classes peut-on hériter ? De combien d’interfaces ?',
                        'back' => "**Une seule classe** (`extends`), **autant d'interfaces qu'on veut** (`implements`).\n\n```java\nclass Canard extends Animal implements Volant, Nageur\n```",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Une voiture et son propriétaire : quelle relation exactement ?',
                        'back' => "**Association** — et non agrégation.\n\nIl n'y a pas de rapport tout/partie : une voiture n'est pas un morceau de son propriétaire, et les deux ont des vies indépendantes.\n\n*Question 7 du QCM de janvier 2025, réponse D.*",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Choisir la bonne relation — dix cas',
                        'origin' => 'genere',
                        'est_minutes' => 25,
                        'difficulty' => 3,
                        'needs_diagram' => true,
                        'statement' => <<<'MD'
**Partie A — identifier.** Pour chacun des dix couples, donnez la relation
(héritage, implémentation, association, agrégation, composition) et **justifiez en
une ligne**. *(10 pts)*

1. Un `Chat` et un `Animal`
2. Une `Commande` et ses `LignesDeCommande` (supprimer la commande supprime les lignes)
3. Un `Etudiant` et les `Cours` auxquels il est inscrit
4. Une `Playlist` et ses `Morceaux` (les morceaux existent hors de la playlist)
5. Un `Carre` et un `Rectangle`
6. Une `Voiture` et son `Moteur` (le moteur est monté à l'usine, ferraillé avec elle)
7. Un `Robot` et l'interface `Deplacable`
8. Un `Medecin` et ses `Patients`
9. Un `Livre` et ses `Chapitres`
10. Un `CompteEpargne` et un `CompteBancaire`

**Partie B — dessiner.** Construisez le diagramme de classes d'une **école** :
`Personne`, `Enseignant`, `Eleve`, `Classe`, `Ecole`, `Matiere`.
Utilisez au moins **une classe abstraite**, **un héritage**, **une agrégation** et
**une composition**, avec les multiplicités. *(10 pts)*
MD,
                        'hint' => "Pour la partie A, posez-vous les questions dans cet ordre : « est un ? » → héritage. « sait faire ? » → interface. Sinon, y a-t-il un rapport tout/partie ? Si non → association. Si oui, la partie survit-elle ? → agrégation ou composition.",
                        'method' => <<<'MD'
**Partie A**, arbre de décision :

1. « X **est un** Y ? » → héritage.
2. « X **sait faire** Y ? » (Y est un contrat) → implémentation.
3. Y a-t-il un rapport **tout / partie** ?
   - Non → **association**.
   - Oui, et la partie survit au tout → **agrégation**.
   - Oui, et la partie meurt avec le tout → **composition**.

**Partie B** : commencez par la classe abstraite `Personne`, dont héritent
`Enseignant` et `Eleve`. Puis demandez-vous ce qui contient quoi, et ce qui survit
à quoi.
MD,
                        'solution' => <<<'MD'
**Partie A**

1. **Héritage** — un chat *est un* animal.
2. **Composition** — supprimer la commande supprime les lignes : elles n'existent pas seules.
3. **Association** — l'étudiant et le cours se connaissent, aucun n'est une partie de l'autre. Multiplicité `*` des deux côtés.
4. **Agrégation** — les morceaux existent indépendamment de la playlist.
5. **Héritage** — un carré *est un* rectangle.
   *(Nuance à mentionner : si `Rectangle` a un `setLargeur()` indépendant du `setHauteur()`, l'héritage viole Liskov — un carré ne peut pas changer de largeur seule. C'est le contre-exemple classique du principe.)*
6. **Composition** — le moteur est monté à l'usine et ferraillé avec la voiture.
7. **Implémentation** — `Deplacable` est un contrat, le robot sait s'y conformer.
8. **Association** — un médecin n'est pas fait de patients. `1..*` des deux côtés.
9. **Composition** — un chapitre n'existe pas hors de son livre.
10. **Héritage** — un compte épargne *est un* compte bancaire.

**Partie B — le diagramme**

```
        ┌────────────────────────┐
        │   «abstract» Personne  │
        ├────────────────────────┤
        │ - nom : String         │
        │ - id : String          │
        ├────────────────────────┤
        │ + getNom() : String    │
        └───────────△────────────┘
              ┌─────┴─────┐
    ┌─────────┴──┐    ┌───┴────────┐
    │ Enseignant │    │   Eleve    │
    ├────────────┤    ├────────────┤
    │ - grade    │    │ - niveau   │
    └──────┬─────┘    └─────┬──────┘
           │ *              │ *
           │ enseigne       │ inscrit
      ┌────┴────────────────┴───┐
      │         Classe          │
      ├─────────────────────────┤
      │ - libelle : String      │
      └────────┬─────────△──────┘
               │ *       │ 1..*
               │         │
       ┌───────┴──┐   ┌──┴──────────┐
       │  Ecole   │◆──│   Matiere   │
       └──────────┘   └─────────────┘
```

Relations attendues :

- `Personne` **abstraite**, `Enseignant` et `Eleve` en **héritage** (triangle creux).
- `Ecole ◆──1..* Classe` — **composition** : fermer l'école supprime ses classes.
- `Classe ◇──1..* Eleve` — **agrégation** : un élève existe même si la classe est dissoute.
- `Enseignant ──* * Classe` — **association** : un enseignant intervient dans
  plusieurs classes, une classe a plusieurs enseignants.
- `Classe ──* * Matiere` — **association**.
MD,
                        'rubric' => [
                            ['label' => 'A1 à A5 : cinq relations correctes avec justification', 'points' => 5],
                            ['label' => 'A6 à A10 : cinq relations correctes avec justification', 'points' => 5],
                            ['label' => 'B : une classe abstraite avec le stéréotype ou l’italique', 'points' => 2],
                            ['label' => 'B : au moins un héritage en triangle creux', 'points' => 2],
                            ['label' => 'B : une agrégation (losange creux) correctement placée', 'points' => 2],
                            ['label' => 'B : une composition (losange plein) correctement placée', 'points' => 2],
                            ['label' => 'B : multiplicités présentes sur toutes les relations', 'points' => 2],
                        ],
                    ],
                ],
            ],

            /* ============================================================
             | C2-Java — la syntaxe, pour quelqu'un qui n'en a jamais écrit
             ============================================================ */
            'C2-Java' => [
                'lessons' => [
                    [
                        'title' => 'Java, le minimum pour lire et écrire',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
Le QCM d'ALO pose des questions de code Java. Il ne s'agit pas de savoir programmer,
mais de **lire** un fragment et de dire s'il compile.

Cette fiche donne le strict nécessaire, dans l'ordre où il sert.
MD,
                        'formalism' => <<<'MD'
**Les types de base**

| Type | Contient | Exemple |
|---|---|---|
| `int` | un entier | `42` |
| `double` | un nombre à virgule | `3.5` |
| `boolean` | vrai ou faux | `true`, `false` |
| `char` | un caractère | `'a'` |
| `String` | du texte | `"bonjour"` |

Notez la majuscule de `String` : ce n'est pas un type de base mais une **classe**.

**Les modificateurs de visibilité**

| Mot | Qui peut y accéder |
|---|---|
| `private` | la classe elle-même, uniquement |
| `protected` | la classe et ses **filles** |
| `public` | tout le monde |
| *(rien)* | les classes du même paquet |

**`static` — le mot qui revient au QCM**

Un attribut ou une méthode **`static`** appartient à la **classe**, pas aux objets.
Il est donc **commun à toutes les instances** et existe même sans objet.

```java
public class Compteur {
    private static int total = 0;     // UN seul « total » pour toute l'application
    private int valeur = 0;           // UN « valeur » par objet créé

    public Compteur() {
        total++;                      // chaque création incrémente le compteur commun
    }

    public static int getTotal() {    // appelable sans objet : Compteur.getTotal()
        return total;
    }
}
```

Conséquence importante : **une méthode `static` n'a pas de `this`**, donc elle ne
peut pas lire un attribut d'instance.

**Le polymorphisme — le mot compliqué pour une idée simple**

*Le même appel donne un comportement différent selon l'objet réel.*

```java
Animal a = new Chien("Rex");
a.crier();                 // affiche « Ouaf »

Animal b = new Chat("Félix");
b.crier();                 // affiche « Miaou »
```

Les deux variables sont déclarées `Animal`. Mais Java regarde l'objet **réellement**
créé pour choisir la méthode. C'est ce qui permet d'écrire un code qui manipule des
`Animal` sans savoir lesquels.

**Le transtypage — changer le type déclaré**

```java
Animal a = new Chien("Rex");     // montée : toujours autorisée
Chien c = (Chien) a;             // descente : il faut le dire explicitement

if (a instanceof Chien) {        // à vérifier avant, sinon erreur à l'exécution
    Chien c2 = (Chien) a;
}
```

**Les exceptions — signaler une erreur**

```java
throw new IllegalArgumentException("nombre négatif");   // lancer

try {
    faireQuelqueChose();
} catch (IllegalArgumentException e) {                  // attraper
    System.out.println(e.getMessage());
}
```

Retenez : `throw` **lance**, `catch` **attrape**. Et `setMessage()` **n'existe pas** —
le message se donne au constructeur.
MD,
                        'worked_example' => <<<'MD'
**La question de typage tombée en 2025.** C'est le type d'exercice le plus rentable :
mécanique, sans piège de logique.

```java
interface Position {}
class Premier {}
class Second extends Premier {}
class Troisieme extends Premier {}
class Quatrieme extends Second implements Position {}
class Cinquieme extends Quatrieme {}
class Sixieme extends Troisieme {}
```

**La méthode, en trois temps.**

*Temps 1 — dessinez l'arbre.*

```
        Premier
       ╱       ╲
   Second     Troisieme
     │            │
 Quatrieme     Sixieme
 (Position)
     │
 Cinquieme
```

*Temps 2 — pour `TypeDeclare x = new ClasseReelle();`, remontez depuis la classe
réelle.* Si `TypeDeclare` figure sur le chemin — comme ancêtre, ou comme interface
implémentée par un ancêtre — ça compile.

*Temps 3 — appliquez.*

| Instruction | Chemin depuis la classe réelle | Verdict |
|---|---|---|
| `Position p = new Sixieme();` | Sixieme → Troisieme → Premier | ❌ pas de `Position` |
| `Premier p = new Cinquieme();` | Cinquieme → Quatrieme → Second → **Premier** | ✅ |
| `Position p = new Quatrieme();` | Quatrieme **implements Position** | ✅ |
| `Troisieme t = new Quatrieme();` | Quatrieme → Second → Premier | ❌ pas de `Troisieme` |

**Réponses : B et C.**

L'interface se transmet par héritage : `Cinquieme` hérite de `Quatrieme`,
donc `Position p = new Cinquieme();` compilerait aussi.
MD,
                        'pitfalls' => <<<'MD'
- **Croire qu'une méthode `static` peut lire un attribut d'instance.** Elle n'a pas
  de `this`, donc non.
- **Oublier que `String` prend une majuscule.** C'est une classe, pas un type de base.
- **Confondre `==` et `.equals()`** pour comparer deux `String`. `==` compare les
  **références** (est-ce le même objet ?), `.equals()` compare le **contenu**.
- **Descendre en type sans vérifier.** `(Chien) a` plante à l'exécution si `a`
  n'est pas un chien. Il faut `instanceof` avant.
- **Croire qu'un `catch` lance une exception.** Il l'attrape. C'est `throw` qui lance.
MD,
                        'examiner_expects' => <<<'MD'
Sur une question de typage : **remonter la chaîne d'héritage** depuis la classe
instanciée et vérifier que le type déclaré s'y trouve — comme ancêtre ou comme
interface d'un ancêtre.

Sur une question de définition : la formulation exacte du polycopié.
« Un attribut de classe est **commun à toutes les instances** », pas
« il est partagé ».
MD,
                        'source_refs' => [['label' => 'alo_V9.pdf § 2.1 — Concepts objet en Java'], ['label' => 'IntroductionJavaV1.pdf']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Que signifie `static` sur un attribut ?',
                        'back' => "**Il appartient à la classe, pas aux objets** — donc **commun à toutes les instances**, et non dupliqué dans chacune.\n\nConséquence : une méthode `static` n'a pas de `this` et ne peut pas lire un attribut d'instance.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Le polymorphisme, en une phrase et un exemple ?',
                        'back' => "**Le même appel donne un comportement différent selon l'objet réellement créé.**\n\n```java\nAnimal a = new Chien(); a.crier();  // Ouaf\nAnimal b = new Chat();  b.crier();  // Miaou\n```\n\nLes deux sont déclarés `Animal`, Java choisit selon l'objet réel.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "`TypeDeclare x = new ClasseReelle();` — comment savoir si ça compile ?",
                        'back' => "**Remontez la chaîne d'héritage depuis `ClasseReelle`.**\n\nSi `TypeDeclare` s'y trouve — comme ancêtre, ou comme interface implémentée par un ancêtre — ça compile. Sinon, non.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Comparer deux `String` : `==` ou `.equals()` ?',
                        'back' => "**`.equals()`** — compare le **contenu**.\n\n`==` compare les **références** : « est-ce le même objet en mémoire ? ». Deux chaînes identiques peuvent être deux objets distincts.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Les quatre niveaux de visibilité en Java ?',
                        'back' => "`private` — la classe seule\n`protected` — la classe et ses **filles**\n`public` — tout le monde\n*(rien)* — les classes du même paquet",
                    ],
                ],
            ],

            /* ============================================================
             | DP-Creat — Builder et Singleton
             ============================================================ */
            'DP-Creat' => [
                'lessons' => [
                    [
                        'title' => 'Builder et Singleton',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Ces deux patrons répondent à la même question : **comment fabriquer un objet ?**

Le **Builder** s'attaque aux constructeurs illisibles. Le **Singleton** garantit
qu'un objet n'existe qu'en un seul exemplaire.

Une remarque avant de commencer, qui change la façon de les réviser :
**les deux sont hors scope** dans les exercices de conception depuis 2024.
L'énoncé le précise — ils ne comptent pas parmi les trois patrons demandés.
Ils restent en revanche au **QCM**, où ils tombent régulièrement.

Révisez-les pour reconnaître leur définition, pas pour les dessiner.
MD,
                        'formalism' => <<<'MD'
**Le Builder — le problème qu'il résout**

Voici un constructeur qui a mal tourné :

```java
Pizza p = new Pizza("grande", true, false, true, true, false, "fine");
```

Impossible de savoir ce que signifie le troisième `false`. Et si la moitié des
ingrédients sont optionnels, il faudrait un constructeur par combinaison.

Le Builder remplace cela par une construction lisible, étape par étape :

```java
Pizza p = new Pizza.Builder("grande")
        .avecFromage()
        .avecChampignons()
        .patePaisse()
        .build();
```

**Quand l'employer** — les trois situations données par le corrigé de 2025 :

1. L'objet final est **imposant** et sa création complexe.
2. **Beaucoup d'arguments** doivent être passés à la construction.
3. Certains arguments sont **optionnels**.

*(La proposition « les arguments doivent être correctement instanciés » ne fait pas
partie de la réponse — c'est le piège de la question 10.)*

Le polycopié distingue quatre variantes : le **builder fluent** (beaucoup de méthodes
chaînées, un seul `build()`), le **builder Command** (beaucoup de setters, un seul
`build()` qui fait tout), le **builder officiel** (plusieurs `build()`, une classe
`Director`), et le choix entre les trois.

**Le Singleton — une seule instance**

```java
public class Configuration {

    private static Configuration instance;      // l'unique exemplaire

    private Configuration() { }                 // constructeur PRIVÉ : personne ne peut faire « new »

    public static Configuration getInstance() {
        if (instance == null) {
            instance = new Configuration();     // créé au premier appel seulement
        }
        return instance;
    }
}
```

Trois éléments, tous indispensables :

1. Un attribut **`static`** qui garde l'unique instance.
2. Un **constructeur privé** — c'est lui qui interdit le `new` depuis l'extérieur.
3. Une méthode **`static getInstance()`**, seule porte d'accès.

**Cas d'usage :** une configuration, un journal d'événements, un pool de connexions —
tout ce dont un second exemplaire n'aurait pas de sens.
MD,
                        'worked_example' => <<<'MD'
**Un Builder fluent complet.**

```java
public class Pizza {

    private final String taille;          // « final » : fixé une fois, jamais modifié
    private final boolean fromage;
    private final boolean champignons;

    // Constructeur PRIVÉ : on ne fabrique une Pizza que par le Builder
    private Pizza(Builder b) {
        this.taille = b.taille;
        this.fromage = b.fromage;
        this.champignons = b.champignons;
    }

    // Classe imbriquée : le Builder vit à l'intérieur de Pizza
    public static class Builder {

        private final String taille;       // obligatoire
        private boolean fromage = false;   // optionnels, avec leur défaut
        private boolean champignons = false;

        public Builder(String taille) {
            this.taille = taille;
        }

        public Builder avecFromage() {
            this.fromage = true;
            return this;                   // ← rendre « this » permet de chaîner
        }

        public Builder avecChampignons() {
            this.champignons = true;
            return this;
        }

        public Pizza build() {
            return new Pizza(this);
        }
    }
}
```

La clé du chaînage tient en une ligne : **chaque méthode rend `this`**, donc on peut
enchaîner l'appel suivant sur le résultat.

**Le diagramme :**

```
┌─────────────────────┐          ┌──────────────────────┐
│       Pizza         │◄─────────│   Pizza.Builder      │
├─────────────────────┤  build() ├──────────────────────┤
│ - taille : String   │          │ - taille : String    │
│ - fromage : boolean │          │ - fromage : boolean  │
├─────────────────────┤          ├──────────────────────┤
│ - Pizza(b: Builder) │          │ + avecFromage()      │
└─────────────────────┘          │ + build() : Pizza    │
                                 └──────────────────────┘
```
MD,
                        'pitfalls' => <<<'MD'
- **Compter Builder ou Singleton parmi les trois patrons** d'un exercice de conception.
  L'énoncé les exclut explicitement depuis 2024.
- **Oublier de rendre `this`** dans les méthodes du Builder : sans cela, pas de chaînage.
- **Oublier de rendre le constructeur privé** dans le Singleton. C'est ce qui interdit
  le `new`, donc c'est le patron tout entier.
- **Répondre « les arguments doivent être correctement instanciés »** à la question
  sur le Builder : ce n'est pas dans le corrigé.
MD,
                        'examiner_expects' => <<<'MD'
Au QCM, les **trois situations** du Builder — objet imposant, beaucoup d'arguments,
arguments optionnels — et pas une quatrième.

Sur le Singleton, les **trois éléments** : attribut statique, constructeur privé,
méthode `getInstance()` statique.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Builder et Singleton dans un exercice de conception ALO : combien de points ?',
                        'back' => "**Zéro.** L'énoncé les met **hors scope** depuis 2024 : « vous pouvez les utiliser mais ils ne comptent pas comme un des 3 patterns ».\n\nIls restent au **QCM**, où ils tombent souvent.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Les trois situations où le Builder est préconisé ?',
                        'back' => "1. L'objet final est **imposant** et sa création complexe.\n2. **Beaucoup d'arguments** à la construction.\n3. Certains arguments sont **optionnels**.\n\n*« Les arguments doivent être correctement instanciés » n'en fait pas partie — c'est le piège de la question 10 de 2025.*",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Les trois éléments obligatoires d’un Singleton ?',
                        'back' => "1. Un attribut **`static`** gardant l'unique instance.\n2. Un **constructeur privé** — c'est lui qui interdit le `new`.\n3. Une méthode **`static getInstance()`**, seule porte d'accès.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Qu’est-ce qui permet le chaînage `.avecFromage().avecChampignons()` ?',
                        'back' => "**Chaque méthode rend `this`.**\n\n```java\npublic Builder avecFromage() {\n    this.fromage = true;\n    return this;      // ← ici\n}\n```\n\nSans ce `return this`, aucun chaînage possible.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ============================================================
             | DP-MVC
             ============================================================ */
            'DP-MVC' => [
                'lessons' => [
                    [
                        'title' => 'Modèle, Vue, Contrôleur',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
MVC répond à une question d'organisation : **où mettre quel code ?**

Sans lui, un programme graphique mélange les données, leur affichage et la réaction
aux clics dans les mêmes classes. Changer la couleur d'un bouton oblige à toucher au
calcul du solde bancaire.

MVC sépare en trois couches, avec une règle de circulation stricte.
MD,
                        'formalism' => <<<'MD'
| Couche | Contient | Ne contient jamais |
|---|---|---|
| **Modèle** | les données et les règles métier | aucun code d'affichage |
| **Vue** | l'affichage | aucune règle métier |
| **Contrôleur** | la réaction aux actions de l'utilisateur | ni données ni affichage |

**Le sens de circulation**

```
   utilisateur
       │ agit
       ▼
  ┌──────────┐   met à jour   ┌─────────┐
  │Controleur│───────────────▶│ Modele  │
  └──────────┘                └────┬────┘
       │                           │ notifie
       │ choisit                   ▼
       │                      ┌─────────┐
       └─────────────────────▶│   Vue   │
                              └─────────┘
```

1. L'utilisateur agit sur la **vue**.
2. La vue transmet au **contrôleur**.
3. Le contrôleur met à jour le **modèle**.
4. Le modèle **notifie** la vue qu'il a changé.
5. La vue se redessine.

**L'étape 4 est le point clé** : le modèle ne connaît pas la vue, il se contente de
prévenir ses observateurs. **MVC repose donc sur le patron Observateur** — c'est le
lien entre les deux, et il tombe régulièrement.

Le polycopié illustre MVC par **Swing**, la bibliothèque graphique de Java.
MD,
                        'worked_example' => <<<'MD'
**Un compteur, en trois couches.**

```java
// ---- MODÈLE : les données, rien d'autre ----
public class ModeleCompteur {
    private int valeur = 0;
    private final List<Observateur> observateurs = new ArrayList<>();

    public void incrementer() {
        valeur++;
        notifier();                       // le modèle prévient, sans savoir qui
    }

    public int getValeur() { return valeur; }

    public void attacher(Observateur o) { observateurs.add(o); }

    private void notifier() {
        for (Observateur o : observateurs) o.actualiser();
    }
}

// ---- VUE : l'affichage, rien d'autre ----
public class VueCompteur implements Observateur {
    private final ModeleCompteur modele;
    private final JLabel etiquette = new JLabel("0");

    public VueCompteur(ModeleCompteur m) {
        this.modele = m;
        m.attacher(this);                 // la vue s'abonne au modèle
    }

    @Override
    public void actualiser() {
        etiquette.setText(String.valueOf(modele.getValeur()));
    }
}

// ---- CONTRÔLEUR : la réaction, rien d'autre ----
public class ControleurCompteur {
    private final ModeleCompteur modele;

    public ControleurCompteur(ModeleCompteur m) { this.modele = m; }

    public void surClicBouton() {
        modele.incrementer();             // le contrôleur ne dessine rien
    }
}
```

Observez ce que chaque classe **ne fait pas**. Le modèle n'affiche rien. La vue ne
calcule rien. Le contrôleur ne fait ni l'un ni l'autre. C'est cette discipline qui
constitue le patron.
MD,
                        'pitfalls' => <<<'MD'
- **Mettre une règle métier dans la vue.** Si la vue calcule un total, MVC est rompu.
- **Faire connaître la vue au modèle.** Le modèle **notifie** ses observateurs ;
  il ne les connaît pas nommément.
- **Oublier le lien avec Observateur.** C'est le mécanisme de l'étape 4, et la question
  tombe.
- **Confondre contrôleur et modèle.** Le contrôleur **orchestre**, il ne stocke rien.
MD,
                        'examiner_expects' => <<<'MD'
Les **trois couches** avec ce que chacune contient et **ce qu'elle ne contient pas**.
Le **sens de circulation** en cinq étapes. Et la mention que **le modèle notifie la
vue par le patron Observateur**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'MVC : que contient chaque couche ?',
                        'back' => "**Modèle** — les données et les règles métier. Aucun affichage.\n**Vue** — l'affichage. Aucune règle métier.\n**Contrôleur** — la réaction aux actions. Ni données ni affichage.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Quel patron MVC utilise-t-il pour que la vue se mette à jour ?',
                        'back' => "**L'Observateur.**\n\nLe modèle ne connaît pas la vue : il **notifie** ses observateurs, et la vue, abonnée, se redessine. C'est l'étape 4 du cycle et elle tombe régulièrement.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Le cycle MVC, en cinq étapes ?',
                        'back' => "1. L'utilisateur agit sur la **vue**.\n2. La vue transmet au **contrôleur**.\n3. Le contrôleur met à jour le **modèle**.\n4. Le modèle **notifie** ses observateurs.\n5. La vue se redessine.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ============================================================
             | C2-Coll — collections, entrées-sorties, JDBC
             ============================================================ */
            'C2-Coll' => [
                'lessons' => [
                    [
                        'title' => 'Collections, flux et JDBC',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Une **collection** est un objet qui en contient d'autres. Un tableau Java a une taille
figée à la création ; les collections, non — elles grandissent toutes seules.

Trois familles suffisent à couvrir le cours, et elles se distinguent par une seule
question : *comment les éléments sont-ils rangés, et peut-on avoir des doublons ?*
MD,
                        'formalism' => <<<'MD'
**Les trois familles**

| Interface | Rangement | Doublons | Implémentation courante |
|---|---|---|---|
| **`List`** | ordonné, par indice | **oui** | `ArrayList`, `LinkedList` |
| **`Set`** | non ordonné | **non** | `HashSet`, `TreeSet` |
| **`Map`** | par **clé** | clés uniques | `HashMap`, `TreeMap` |

Une `Map` n'est pas une collection d'éléments mais de **couples clé → valeur**.
Un annuaire : le nom est la clé, le numéro la valeur.

```java
List<String> noms = new ArrayList<>();
noms.add("Ana");
noms.add("Ana");                    // accepté : une liste tolère les doublons
System.out.println(noms.size());    // 2

Set<String> uniques = new HashSet<>();
uniques.add("Ana");
uniques.add("Ana");                 // ignoré : un ensemble refuse les doublons
System.out.println(uniques.size()); // 1

Map<String, String> annuaire = new HashMap<>();
annuaire.put("Ana", "0696...");
System.out.println(annuaire.get("Ana"));
```

Les chevrons `<String>` sont la **généricité** : ils annoncent au compilateur ce que
la collection contient, ce qui évite les transtypages.

**Parcourir**

```java
for (String n : noms) {             // « pour chaque n dans noms »
    System.out.println(n);
}

for (Map.Entry<String, String> e : annuaire.entrySet()) {
    System.out.println(e.getKey() + " → " + e.getValue());
}
```

**Les entrées-sorties**

Java lit et écrit par **flux**. Un flux est un canal d'où sortent des octets ou des
caractères, un à un.

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

Le `try (...)` avec des parenthèses est le **try-with-resources** : il ferme le
fichier automatiquement, même en cas d'erreur.

**JDBC — parler à une base de données**

Quatre temps, toujours les mêmes :

```java
// 1. Se connecter
Connection cn = DriverManager.getConnection(url, utilisateur, motDePasse);

// 2. Préparer la requête — avec des « ? » pour les valeurs
PreparedStatement st = cn.prepareStatement("SELECT * FROM livre WHERE auteur = ?");
st.setString(1, "Saint-Exupéry");

// 3. Exécuter et parcourir
ResultSet rs = st.executeQuery();
while (rs.next()) {
    System.out.println(rs.getString("titre"));
}

// 4. Fermer
rs.close(); st.close(); cn.close();
```

**Pourquoi `PreparedStatement` plutôt que `Statement` ?** Parce que les `?` empêchent
l'**injection SQL** : une valeur saisie par l'utilisateur ne peut pas être interprétée
comme du code SQL. C'est la question classique.
MD,
                        'worked_example' => <<<'MD'
**Compter les occurrences de chaque mot d'un texte** — l'exercice qui combine les trois.

```java
public Map<String, Integer> compter(String chemin) throws IOException {

    Map<String, Integer> comptes = new HashMap<>();

    try (BufferedReader r = new BufferedReader(new FileReader(chemin))) {
        String ligne;
        while ((ligne = r.readLine()) != null) {
            for (String mot : ligne.toLowerCase().split("\\s+")) {
                if (mot.isEmpty()) continue;
                // getOrDefault évite de tester la présence de la clé
                comptes.put(mot, comptes.getOrDefault(mot, 0) + 1);
            }
        }
    }

    return comptes;
}
```

Trois éléments à retenir de cet exemple : la `Map` pour associer mot → nombre,
le `try-with-resources` pour fermer le fichier, et `getOrDefault` pour ne pas
avoir à écrire un `if` de présence.
MD,
                        'pitfalls' => <<<'MD'
- **Attendre un ordre d'un `HashSet` ou d'une `HashMap`.** Il n'y en a aucun.
  Pour un ordre trié, il faut `TreeSet` ou `TreeMap`.
- **Oublier la généricité.** `List noms = new ArrayList();` compile mais oblige à
  transtyper à chaque lecture.
- **Confondre `size()` et `length`.** Les collections ont `size()`, les tableaux
  `length`, les `String` `length()`.
- **Employer `Statement` au lieu de `PreparedStatement`.** Le premier ouvre la porte
  à l'injection SQL.
- **Ne pas fermer les ressources.** Le `try-with-resources` s'en charge — l'utiliser
  systématiquement.
MD,
                        'examiner_expects' => <<<'MD'
La distinction **`List` / `Set` / `Map`** en une phrase chacune, avec le critère
des doublons et de l'ordre. Et pour JDBC, **`PreparedStatement` justifié par
l'injection SQL**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => '`List`, `Set`, `Map` : quelle différence ?',
                        'back' => "**`List`** — ordonné par indice, **doublons acceptés**.\n**`Set`** — non ordonné, **doublons refusés**.\n**`Map`** — couples **clé → valeur**, clés uniques.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Pourquoi employer `PreparedStatement` plutôt que `Statement` ?',
                        'back' => "**Pour empêcher l'injection SQL.**\n\nLes `?` séparent la requête des valeurs : une saisie utilisateur ne peut pas être interprétée comme du code SQL.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Que fait `try (BufferedReader r = ...) { }` ?',
                        'back' => "C'est le **try-with-resources** : il **ferme automatiquement** la ressource à la sortie du bloc, même en cas d'exception.\n\nPlus besoin d'un `finally` avec `close()`.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Un `HashSet` conserve-t-il l’ordre d’insertion ?',
                        'back' => "**Non.** `HashSet` et `HashMap` n'ont **aucun ordre garanti**.\n\nPour un ordre trié : `TreeSet` et `TreeMap`. Pour l'ordre d'insertion : `LinkedHashSet`, `LinkedHashMap`.",
                        'difficulty' => 4,
                    ],
                ],
            ],
        ];
    }
}