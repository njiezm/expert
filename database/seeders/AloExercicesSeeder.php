<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Exercices des chapitres ALO qui n'en avaient pas.
 *
 * Les exercices de patrons demandent un diagramme : c'est la compétence qui a
 * valu zéro en janvier, et elle ne s'acquiert qu'en dessinant.
 */
class AloExercicesSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'ALO')->first();

        if (! $subject) {
            return;
        }

        foreach ($this->exercices() as $code => $liste) {
            $chapter = Chapter::where('subject_id', $subject->id)->where('code', $code)->first();

            if (! $chapter) {
                continue;
            }

            foreach ($liste as $i => $exo) {
                Exercise::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $exo['title']],
                    $exo + ['chapter_id' => $chapter->id, 'position' => 200 + $i]
                );
            }
        }
    }

    /* ==================================================================== */

    private function exercices(): array
    {
        return [

            'C2-Java' => [[
                'title' => 'Lire du Java — typage, static, polymorphisme',
                'origin' => 'genere',
                'est_minutes' => 30,
                'difficulty' => 3,
                'statement' => <<<'MD'
**Partie A — typage.** Soit la hiérarchie :

```java
interface Pilotable {}
interface Reparable {}
class Vehicule {}
class Voiture extends Vehicule implements Pilotable {}
class Camion extends Vehicule implements Pilotable, Reparable {}
class Berline extends Voiture {}
class Moto extends Vehicule {}
```

Pour chacune, dites si elle compile et **pourquoi**. *(6 pts)*

1. `Pilotable p = new Berline();`
2. `Reparable r = new Voiture();`
3. `Vehicule v = new Moto();`
4. `Voiture v = new Vehicule();`
5. `Pilotable p = new Camion();`
6. `Reparable r = new Berline();`

**Partie B — static.** *(4 pts)*

```java
public class Article {
    private static int compteur = 0;
    private int numero;

    public Article() {
        compteur++;
        numero = compteur;
    }

    public static int getCompteur() { return compteur; }
    public int getNumero() { return numero; }
}
```

1. Qu'affiche ce code ? Justifiez.
```java
Article a = new Article();
Article b = new Article();
Article c = new Article();
System.out.println(Article.getCompteur());
System.out.println(a.getNumero() + " " + c.getNumero());
```
2. Peut-on écrire `public static int getNumero() { return numero; }` ? Pourquoi ?

**Partie C — polymorphisme.** Écrivez une classe abstraite `Forme` avec une méthode
abstraite `aire()`, puis `Cercle` et `Rectangle`. Écrivez enfin une méthode qui reçoit
un tableau de `Forme` et rend la somme des aires. *(5 pts)*
MD,
                'hint' => "Pour la partie A, dessinez l'arbre d'héritage avant de répondre, en notant les interfaces à côté de chaque classe. Pour B2, demandez-vous ce qu'une méthode statique connaît de l'objet.",
                'method' => <<<'MD'
**A** — pour `TypeDeclare x = new ClasseReelle();`, remontez depuis `ClasseReelle` :
si `TypeDeclare` figure sur le chemin comme ancêtre ou comme interface implémentée
par un ancêtre, ça compile.

**B** — un attribut `static` est unique pour toute l'application ; un attribut
d'instance existe en un exemplaire par objet.

**C** — la classe abstraite déclare `aire()` sans corps ; chaque fille l'implémente.
La méthode de somme manipule des `Forme` sans savoir lesquelles : c'est le polymorphisme.
MD,
                'solution' => <<<'MD'
**Partie A**

```
        Vehicule
     ╱      │      ╲
 Voiture  Camion   Moto
(Pilot.) (Pilot.,
    │     Repar.)
 Berline
```

1. **Compile.** `Berline → Voiture`, qui implémente `Pilotable`. L'interface est héritée.
2. **Ne compile pas.** `Voiture` implémente `Pilotable`, pas `Reparable`.
3. **Compile.** `Moto → Vehicule`.
4. **Ne compile pas.** On ne peut pas déclarer un `Vehicule` comme `Voiture` :
   la descente doit être explicite, et resterait fausse à l'exécution.
5. **Compile.** `Camion implements Pilotable`.
6. **Ne compile pas.** `Berline → Voiture → Vehicule` : aucun `Reparable` sur le chemin.

**Partie B**

**1.** Affiche :
```
3
1 3
```

`compteur` est **`static`** : il est unique pour toute l'application, incrémenté à
chaque construction, donc il vaut 3. `numero` est un attribut **d'instance** : chaque
objet garde le sien, figé à la valeur du compteur au moment de sa création. D'où
`a.numero = 1` et `c.numero = 3`.

**2. Non.** Une méthode `static` appartient à la classe, pas à un objet : elle n'a
pas de `this`, donc elle ne peut pas savoir de quel `numero` on parle.
Le compilateur refuse.

**Partie C**

```java
public abstract class Forme {
    public abstract double aire();
}

public class Cercle extends Forme {
    private final double rayon;

    public Cercle(double rayon) { this.rayon = rayon; }

    @Override
    public double aire() { return Math.PI * rayon * rayon; }
}

public class Rectangle extends Forme {
    private final double largeur;
    private final double hauteur;

    public Rectangle(double largeur, double hauteur) {
        this.largeur = largeur;
        this.hauteur = hauteur;
    }

    @Override
    public double aire() { return largeur * hauteur; }
}

public static double sommeDesAires(Forme[] formes) {
    double total = 0;
    for (Forme f : formes) {
        total += f.aire();      // Java choisit la bonne aire() selon l'objet réel
    }
    return total;
}
```

La ligne `total += f.aire()` est le polymorphisme en action : le code ne sait pas
s'il manipule un cercle ou un rectangle, et n'a pas besoin de le savoir. Ajouter un
`Triangle` demain ne changera pas une ligne de `sommeDesAires`.
MD,
                'rubric' => [
                    ['label' => 'A : les six verdicts corrects', 'points' => 3],
                    ['label' => 'A : chaque verdict justifié par la chaîne d’héritage', 'points' => 3],
                    ['label' => 'B1 : affiche 3 puis « 1 3 », avec la distinction static / instance', 'points' => 2],
                    ['label' => 'B2 : non, une méthode static n’a pas de this', 'points' => 2],
                    ['label' => 'C : classe abstraite avec méthode abstraite `aire()`', 'points' => 2],
                    ['label' => 'C : les deux filles redéfinissent `aire()`', 'points' => 2],
                    ['label' => 'C : la somme manipule des `Forme` sans transtypage', 'points' => 1],
                ],
            ]],

            'DP-Struct' => [[
                'title' => 'Composite et Décorateur — le système de fichiers',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'needs_diagram' => true,
                'statement' => <<<'MD'
Vous modélisez un **système de fichiers**. On y trouve des **fichiers** et des
**dossiers**. Un dossier contient des fichiers et d'autres dossiers. On veut pouvoir
calculer la **taille** d'un dossier comme celle d'un fichier.

Par ailleurs, un fichier peut être **compressé**, **chiffré**, ou les deux — et ces
traitements se cumulent, chacun modifiant la taille et le nom affiché.

**1.** Quel patron pour la structure dossiers / fichiers ? Justifiez. *(1 pt)*
**2.** Quel patron pour les traitements cumulables ? Justifiez, et dites pourquoi
ce n'est pas le même que le premier. *(2 pts)*
**3.** Construisez le diagramme de classes avec l'éditeur. **Nommez les deux
patrons sur le schéma.** *(4 pts)*
**4.** Écrivez en Java l'interface commune et la classe `Dossier`. *(3 pts)*
MD,
                'hint' => "La question qui tranche : combien d'objets chaque patron enveloppe-t-il ? Un dossier en contient plusieurs ; une compression s'applique à un seul fichier.",
                'method' => <<<'MD'
1. Cherchez la structure **récursive** : « un dossier contient des dossiers ».
2. Cherchez l'**empilement** : « compressé, chiffré, ou les deux ».
3. Dans le diagramme, l'interface commune est ce qui rend les deux patrons possibles :
   c'est elle qui permet de traiter un dossier comme un fichier.
MD,
                'solution' => <<<'MD'
**1. Composite.** Un dossier contient des éléments qui peuvent eux-mêmes être des
dossiers — structure récursive — et l'on veut appeler `taille()` indifféremment sur
un fichier ou un dossier.

**2. Décorateur.** La compression et le chiffrement **enveloppent un seul** élément
pour en modifier le comportement, et s'empilent : `new Chiffre(new Compresse(fichier))`.

**Ce n'est pas un Composite** parce qu'un décorateur référence **un** objet, là où le
composite en agrège **plusieurs**. C'est la seule différence structurelle entre les
deux, et elle suffit à trancher.

**3. Le diagramme**

```
              ┌──────────────────────────┐
              │       «interface»        │
              │     ElementFichier       │
              ├──────────────────────────┤
              │ + taille() : long        │
              │ + nom() : String         │
              └────────────△─────────────┘
             ┌─────────────┼──────────────────┐
    ┌────────┴──────┐  ┌───┴────────┐  ┌──────┴──────────────────┐
    │    Fichier    │  │  Dossier   │  │ «abstract» Decorateur   │
    ├───────────────┤  ├────────────┤  ├─────────────────────────┤
    │ - octets:long │  │            │◇ │ # element:ElementFichier│◇
    ├───────────────┤  ├────────────┤ ││├─────────────────────────┤│
    │ + taille()    │  │ + taille() │ ││└────────────△────────────┘│
    │ + nom()       │  │ + ajouter()│ ││       ┌─────┴─────┐       │ 1
    └───────────────┘  └────────────┘ ││  ┌────┴────┐ ┌────┴────┐  │
                                      │└──│Compresse│ │ Chiffre │──┘
                              1..* ───┘   └─────────┘ └─────────┘

     ◄── Composite (Dossier agrège 1..* ElementFichier)
     ◄── Décorateur (Decorateur référence 1 seul ElementFichier)
```

**4. Le code**

```java
public interface ElementFichier {
    long taille();
    String nom();
}

public class Dossier implements ElementFichier {

    private final String nom;
    private final List<ElementFichier> enfants = new ArrayList<>();

    public Dossier(String nom) { this.nom = nom; }

    public void ajouter(ElementFichier e) { enfants.add(e); }

    @Override
    public long taille() {
        long total = 0;
        for (ElementFichier e : enfants) {
            total += e.taille();       // délégation récursive : c'est tout le Composite
        }
        return total;
    }

    @Override
    public String nom() { return nom; }
}
```

Et le décorateur, pour comparaison :

```java
public abstract class DecorateurFichier implements ElementFichier {
    protected final ElementFichier element;      // UN seul, non une liste
    protected DecorateurFichier(ElementFichier e) { this.element = e; }
}

public class Compresse extends DecorateurFichier {
    public Compresse(ElementFichier e) { super(e); }

    @Override
    public long taille() { return element.taille() / 2; }

    @Override
    public String nom() { return element.nom() + ".zip"; }
}
```

`List<ElementFichier>` d'un côté, `ElementFichier` de l'autre. Toute la distinction
tient là.
MD,
                'rubric' => [
                    ['label' => 'Q1 : Composite, justifié par la récursivité', 'points' => 1],
                    ['label' => 'Q2 : Décorateur, justifié par l’empilement', 'points' => 1],
                    ['label' => 'Q2 : la distinction un/plusieurs est explicitée', 'points' => 1],
                    ['label' => 'Diagramme : interface commune aux trois branches', 'points' => 1],
                    ['label' => 'Diagramme : agrégation 1..* du Dossier', 'points' => 1],
                    ['label' => 'Diagramme : référence 1 du Décorateur', 'points' => 1],
                    ['label' => 'Diagramme : les deux patrons nommés sur le schéma', 'points' => 1],
                    ['label' => 'Code : `taille()` du Dossier délègue récursivement', 'points' => 3],
                ],
            ]],

            'DP-Comp' => [[
                'title' => 'État, Stratégie, Observateur — le distributeur',
                'origin' => 'genere',
                'est_minutes' => 35,
                'difficulty' => 4,
                'needs_diagram' => true,
                'statement' => <<<'MD'
Un **distributeur automatique** de boissons. Trois exigences :

**#1** — Le distributeur traverse des phases : *en attente*, *paiement en cours*,
*distribution*, *hors service*. Ce qui est autorisé dépend de la phase, et le passage
de l'une à l'autre survient au fil de l'utilisation.

**#2** — Le prix se calcule selon une politique choisie à l'installation :
*tarif normal*, *tarif étudiant*, *happy hour*.

**#3** — Le service de maintenance et le gestionnaire de stock doivent être prévenus
dès qu'un produit tombe en rupture.

**1.** Donnez le patron pour chaque exigence, avec une ligne de justification. *(3 pts)*
**2.** Pour les exigences 1 et 2, expliquez précisément **pourquoi ce ne sont pas le
même patron**, alors que leur diagramme se ressemble. *(2 pts)*
**3.** Construisez le diagramme. **Nommez les trois patrons.** *(4 pts)*
**4.** Écrivez en Java l'interface d'état et deux états concrets, en montrant la
transition. *(3 pts)*
MD,
                'hint' => "Pour la question 2, regardez qui décide du changement. Dans un cas c'est l'objet lui-même au fil de sa vie, dans l'autre c'est le client, une fois pour toutes.",
                'method' => <<<'MD'
1. « Phases », « au fil de l'utilisation » → un patron.
   « Choisi à l'installation » → un autre.
   « Doivent être prévenus » → un troisième.
2. Comparez les champs : mutable et modifié de l'intérieur, ou `final` reçu du dehors ?
3. Dans le code, la transition se voit à l'appel `contexte.setEtat(...)`.
MD,
                'solution' => <<<'MD'
**1.**

- **#1 → État.** Le comportement dépend d'une phase qui **évolue seule** au fil de
  l'utilisation.
- **#2 → Stratégie.** Un algorithme de calcul **interchangeable**, choisi de
  l'extérieur à l'installation.
- **#3 → Observateur.** Des abonnés doivent être **notifiés** d'un changement.

**2. Pourquoi État ≠ Stratégie**

Structurellement, les deux sont identiques : une interface, des implémentations, un
contexte qui délègue. La différence est dans **qui décide du changement**.

| | État | Stratégie |
|---|---|---|
| Qui change | l'objet, **tout seul** | le **client**, de l'extérieur |
| Quand | en continu, au fil de la vie | une fois, à la configuration |
| Le champ | **mutable**, modifié par les états eux-mêmes | souvent **`final`**, reçu au constructeur |
| L'état concret connaît | le contexte, pour déclencher la suite | rien du contexte |

Dans le distributeur : la phase passe de *paiement* à *distribution* **d'elle-même**
quand la somme est atteinte. Le tarif étudiant, lui, ne devient jamais happy hour
tout seul — quelqu'un l'a décidé à l'installation.

**3. Le diagramme**

```
   ┌────────────────────────┐        ┌───────────────────────────┐
   │      «interface»       │        │       «interface»         │
   │      EtatMachine       │        │    PolitiqueTarifaire     │
   ├────────────────────────┤        ├───────────────────────────┤
   │ + inserer(d:Distrib)   │        │ + prix(p:Produit):double  │
   │ + choisir(d:Distrib)   │        └─────────────△─────────────┘
   └───────────△────────────┘              ┌───────┼────────┐
      ┌────┬───┴───┬─────────┐        ┌────┴───┐┌──┴────┐┌──┴──────┐
 ┌────┴──┐┌┴─────┐┌┴────────┐┌┴──────┐│ Normal ││Etudiant││HappyHour│
 │Attente││Paieme││Distribut││HorsSer││└────────┘└───────┘└─────────┘
 └───────┘└──────┘└─────────┘└───────┘         ▲
             ▲                                 │ final
             │ mutable                         │
   ┌─────────┴─────────────────────────────────┴──────┐
   │                  Distributeur                    │
   ├──────────────────────────────────────────────────┤
   │ - etat : EtatMachine                             │
   │ - tarif : PolitiqueTarifaire                     │
   ├──────────────────────────────────────────────────┤
   │ + setEtat(e)  + inserer()  + choisir()           │
   │ + attacher(o) + detacher(o) + notifier()         │
   └────────────────────────◇─────────────────────────┘
                            │ * observateurs
                  ┌─────────┴──────────┐
                  │     «interface»    │
                  │    Observateur     │
                  │ + actualiser(p)    │
                  └─────────△──────────┘
                      ┌─────┴──────┐
              ┌───────┴───┐  ┌─────┴──────┐
              │Maintenance│  │GestionStock│
              └───────────┘  └────────────┘

   ◄── État (les phases du distributeur)
   ◄── Stratégie (la politique tarifaire)
   ◄── Observateur (alerte de rupture)
```

**4. Le code des états**

```java
public interface EtatMachine {
    void inserer(Distributeur d, double montant);
    void choisir(Distributeur d, Produit p);
}

public class EnAttente implements EtatMachine {

    @Override
    public void inserer(Distributeur d, double montant) {
        d.crediter(montant);
        d.setEtat(new PaiementEnCours());     // ← l'état pilote la transition
    }

    @Override
    public void choisir(Distributeur d, Produit p) {
        System.out.println("Insérez d'abord de la monnaie");
    }
}

public class PaiementEnCours implements EtatMachine {

    @Override
    public void inserer(Distributeur d, double montant) {
        d.crediter(montant);                   // on reste dans le même état
    }

    @Override
    public void choisir(Distributeur d, Produit p) {
        if (d.credit() >= d.tarif().prix(p)) {
            d.setEtat(new Distribution());     // ← transition déclenchée par l'état
        } else {
            System.out.println("Crédit insuffisant");
        }
    }
}
```

La ligne `d.setEtat(new Distribution())` est ce qui fait de ce code un **État** et
non une **Stratégie** : c'est l'état lui-même qui décide de la suite.
MD,
                'rubric' => [
                    ['label' => 'Les trois patrons correctement identifiés', 'points' => 3],
                    ['label' => 'La distinction État / Stratégie porte sur **qui décide** du changement', 'points' => 1],
                    ['label' => 'La distinction mentionne le champ mutable contre le champ final', 'points' => 1],
                    ['label' => 'Diagramme : deux interfaces distinctes pour État et Stratégie', 'points' => 1],
                    ['label' => 'Diagramme : agrégation * vers les observateurs', 'points' => 1],
                    ['label' => 'Diagramme : les trois patrons nommés sur le schéma', 'points' => 2],
                    ['label' => 'Code : la transition `d.setEtat(...)` est déclenchée par l’état concret', 'points' => 3],
                ],
            ]],

            'DP-Creat' => [[
                'title' => 'Builder et Singleton — reconnaître, pas dessiner',
                'origin' => 'genere',
                'est_minutes' => 20,
                'difficulty' => 2,
                'statement' => <<<'MD'
**1.** Dans quelles situations le patron **Builder** est-il préconisé ?
*(Trois réponses attendues parmi les quatre.)* *(2 pts)*

a. L'objet final est imposant, et sa création complexe
b. Beaucoup d'arguments doivent être passés à la construction
c. Les arguments doivent être correctement instanciés
d. Certains des arguments sont optionnels

**2.** Voici un constructeur difficile à lire :
```java
Rapport r = new Rapport("mensuel", true, false, true, "PDF", null, 12);
```
Réécrivez son appel avec un Builder fluent, puis écrivez la classe `Builder`
correspondante. *(4 pts)*

**3.** Écrivez un Singleton `Journal` avec sa méthode `ecrire(String)`.
Identifiez les **trois éléments** qui en font un Singleton. *(3 pts)*

**4.** Ces deux patrons comptent-ils dans les trois patrons demandés à l'exercice
de conception ? *(1 pt)*
MD,
                'hint' => "Pour la question 1, une des quatre propositions ne figure pas dans le corrigé officiel. Pour la 3, demandez-vous ce qui empêche quelqu'un d'écrire `new Journal()`.",
                'method' => <<<'MD'
2. Chaque méthode du Builder doit rendre `this` — sans cela, pas de chaînage.
   Les valeurs obligatoires passent par le constructeur du Builder, les optionnelles
   par des méthodes.
3. Trois éléments : un attribut statique, un constructeur privé, une méthode
   statique d'accès. Retirez-en un et ce n'est plus un Singleton.
MD,
                'solution' => <<<'MD'
**1. a, b, d.**

La proposition **c** — « les arguments doivent être correctement instanciés » —
ne figure pas dans le corrigé officiel de janvier 2025.

**2.**

```java
Rapport r = new Rapport.Builder("mensuel")
        .avecGraphiques()
        .avecAnnexes()
        .format("PDF")
        .periode(12)
        .build();
```

```java
public class Rapport {

    private final String type;
    private final boolean graphiques;
    private final boolean annexes;
    private final String format;
    private final int periode;

    private Rapport(Builder b) {
        this.type = b.type;
        this.graphiques = b.graphiques;
        this.annexes = b.annexes;
        this.format = b.format;
        this.periode = b.periode;
    }

    public static class Builder {

        private final String type;              // obligatoire
        private boolean graphiques = false;     // optionnels, avec défaut
        private boolean annexes = false;
        private String format = "PDF";
        private int periode = 1;

        public Builder(String type) { this.type = type; }

        public Builder avecGraphiques() { this.graphiques = true; return this; }
        public Builder avecAnnexes()    { this.annexes = true;    return this; }
        public Builder format(String f) { this.format = f;        return this; }
        public Builder periode(int p)   { this.periode = p;       return this; }

        public Rapport build() { return new Rapport(this); }
    }
}
```

**3.**

```java
public class Journal {

    private static Journal instance;                    // ① attribut statique

    private Journal() { }                               // ② constructeur privé

    public static Journal getInstance() {               // ③ accès statique
        if (instance == null) {
            instance = new Journal();
        }
        return instance;
    }

    public void ecrire(String message) {
        System.out.println("[" + java.time.LocalDateTime.now() + "] " + message);
    }
}
```

Les trois éléments :

1. **L'attribut `static`** garde l'unique exemplaire, partagé par toute l'application.
2. **Le constructeur privé** interdit `new Journal()` depuis l'extérieur.
   C'est lui qui fait tout le patron : sans lui, rien n'empêche de créer un second objet.
3. **La méthode `static getInstance()`** est la seule porte d'accès, et elle crée
   l'instance au premier appel.

**4. Non.** L'énoncé des exercices de conception les met **hors scope** depuis 2024 :
« vous pouvez les utiliser mais ils ne comptent pas comme un des 3 patterns ».

Ils restent en revanche au QCM, et le Builder y est tombé en 2025.
MD,
                'rubric' => [
                    ['label' => 'Q1 : a, b, d — les trois, et pas c', 'points' => 2],
                    ['label' => 'Q2 : appel chaîné lisible', 'points' => 1],
                    ['label' => 'Q2 : chaque méthode du Builder rend `this`', 'points' => 2],
                    ['label' => 'Q2 : constructeur de Rapport privé, prenant le Builder', 'points' => 1],
                    ['label' => 'Q3 : les trois éléments présents dans le code', 'points' => 2],
                    ['label' => 'Q3 : le rôle du constructeur privé est expliqué', 'points' => 1],
                    ['label' => 'Q4 : hors scope depuis 2024', 'points' => 1],
                ],
            ]],

            'DP-MVC' => [[
                'title' => 'MVC — répartir le code en trois couches',
                'origin' => 'genere',
                'est_minutes' => 25,
                'difficulty' => 3,
                'needs_diagram' => true,
                'statement' => <<<'MD'
Une application de **gestion de tâches** : on ajoute une tâche, on la coche, la liste
se met à jour à l'écran, et le nombre de tâches restantes s'affiche en bas.

**1.** Répartissez les éléments suivants entre **Modèle**, **Vue** et **Contrôleur**.
Justifiez les cas litigieux. *(4 pts)*

a. La liste des tâches en mémoire
b. Le calcul du nombre de tâches restantes
c. Le champ de saisie du titre
d. La réaction au clic sur « Ajouter »
e. La couleur d'une tâche terminée
f. La règle « une tâche sans titre est refusée »
g. L'affichage du compteur en bas
h. La méthode appelée quand on coche une case

**2.** Décrivez le **cycle complet** qui se déclenche quand l'utilisateur coche une
tâche, en cinq étapes numérotées. *(3 pts)*

**3.** Quel patron le modèle emploie-t-il pour prévenir la vue ? Pourquoi ne peut-il
pas simplement appeler une méthode de la vue ? *(2 pts)*

**4.** Construisez le diagramme des trois couches. *(1 pt)*
MD,
                'hint' => "Le test pour chaque élément : est-ce une donnée ou une règle métier ? de l'affichage ? une réaction à une action ? Le point b est le plus discutable — demandez-vous si ce calcul dépend de l'écran.",
                'method' => <<<'MD'
1. Posez-vous, pour chaque élément : *ce code changerait-il si l'on remplaçait
   l'interface graphique par une ligne de commande ?* Si non, c'est du **modèle**.
2. Le cycle part toujours de l'utilisateur et revient à l'écran.
3. Regardez le sens des dépendances : qui a le droit de connaître qui ?
MD,
                'solution' => <<<'MD'
**1.**

| | Couche | Pourquoi |
|---|---|---|
| a. liste des tâches | **Modèle** | ce sont les données |
| b. nombre de tâches restantes | **Modèle** | c'est un calcul métier ; il ne change pas si l'on passe en ligne de commande |
| c. champ de saisie | **Vue** | pur affichage |
| d. réaction au clic « Ajouter » | **Contrôleur** | réaction à une action utilisateur |
| e. couleur d'une tâche terminée | **Vue** | décision purement visuelle |
| f. « une tâche sans titre est refusée » | **Modèle** | règle métier, valable quelle que soit l'interface |
| g. affichage du compteur | **Vue** | l'affichage, pas le calcul |
| h. méthode appelée quand on coche | **Contrôleur** | réaction à une action |

Les deux cas litigieux sont **b** et **g** : le **calcul** appartient au modèle,
l'**affichage** du résultat à la vue. Et **f** : une validation métier reste dans le
modèle, même si la vue peut la doubler d'un contrôle de confort.

**2. Le cycle**

1. L'utilisateur **coche la case** dans la **vue**.
2. La vue transmet l'événement au **contrôleur** (`surCaseCochee(tache)`).
3. Le contrôleur appelle le **modèle** (`modele.basculer(tache)`).
4. Le modèle modifie ses données puis **notifie ses observateurs** (`notifier()`).
5. La **vue**, abonnée, se **redessine** : la tâche est barrée et le compteur diminue.

**3. L'Observateur.**

Le modèle ne peut pas appeler directement une méthode de la vue, car cela créerait
une **dépendance du modèle vers la vue**. Trois conséquences fâcheuses :

- le modèle deviendrait inutilisable sans interface graphique ;
- on ne pourrait pas avoir **deux vues** du même modèle — une liste et un graphique ;
- tester le modèle exigerait de fabriquer une vue.

Avec l'Observateur, le modèle ne connaît qu'une **interface** `Observateur`. Il
notifie sans savoir qui écoute. La dépendance est inversée.

**4. Le diagramme**

```
   ┌──────────────────┐   1        1  ┌──────────────────────┐
   │       Vue        │───────────────│     Controleur       │
   ├──────────────────┤   transmet    ├──────────────────────┤
   │ - liste : JList  │               │ + surAjout(titre)    │
   │ - compteur:JLabel│               │ + surCaseCochee(t)   │
   ├──────────────────┤               └──────────┬───────────┘
   │ + actualiser()   │                          │ 1
   └────────△─────────┘                          │ met à jour
            ┊ implements                         ▼
   ┌────────┴─────────┐   *          1  ┌────────────────────┐
   │   «interface»    │◄────────────────│   ModeleTaches     │
   │   Observateur    │   observateurs  ├────────────────────┤
   │ + actualiser()   │     notifie     │ - taches : List    │
   └──────────────────┘                 ├────────────────────┤
                                        │ + ajouter(titre)   │
                                        │ + basculer(t)      │
                                        │ + restantes():int  │
                                        │ + attacher(o)      │
                                        │ + notifier()       │
                                        └────────────────────┘

   ◄── MVC (les trois couches)
   ◄── Observateur (le modèle notifie la vue)
```

Notez le sens des flèches : le **contrôleur** et la **vue** connaissent le modèle,
le modèle ne connaît qu'une interface. C'est ce qui rend les trois couches
réellement séparables.
MD,
                'rubric' => [
                    ['label' => 'Les huit éléments correctement répartis', 'points' => 3],
                    ['label' => 'Les cas litigieux b, f et g sont justifiés', 'points' => 1],
                    ['label' => 'Le cycle en cinq étapes, dans le bon ordre', 'points' => 3],
                    ['label' => 'Observateur identifié comme mécanisme de notification', 'points' => 1],
                    ['label' => 'La raison donnée : éviter la dépendance modèle → vue', 'points' => 1],
                    ['label' => 'Diagramme des trois couches avec le sens des dépendances', 'points' => 1],
                ],
            ]],
        ];
    }
}