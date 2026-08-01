<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu d'ALO — 0/20 en janvier, et première épreuve du rattrapage (24 août).
 *
 * L'analyse des annales 2022 à 2025 montre un basculement du format : le QCM est
 * passé de 20 points à 5, remplacé par trois exercices de conception à 5 points.
 * Ces exercices suivent un moule identique d'une année sur l'autre, ce qui les
 * rend directement entraînables — d'où la priorité donnée au chapitre méthode.
 *
 * Les correspondances « point d'attention → patron » proviennent des corrigés
 * officiels, pas d'une interprétation.
 */
class AloContentSeeder extends Seeder
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
                    $lesson + ['position' => $i + 1, 'est_minutes' => $lesson['est_minutes'] ?? 15]
                );
            }

            foreach ($data['cards'] ?? [] as $i => $card) {
                Flashcard::updateOrCreate(
                    ['chapter_id' => $chapter->id, 'front' => $card['front']],
                    $card + ['position' => $i + 1]
                );
            }

            foreach ($data['exercises'] ?? [] as $i => $exo) {
                Exercise::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $exo['title']],
                    $exo + ['chapter_id' => $chapter->id, 'position' => $i + 1]
                );
            }
        }

        $this->mockExam($subject);
    }

    /* ==================================================================== */

    private function content(): array
    {
        return [

            'DP-Method' => [
                'lessons' => [
                    [
                        'title' => 'Le format réel de l\'épreuve, et ce qu\'il impose',
                        'est_minutes' => 12,
                        'intuition' => <<<'MD'
Avant de réviser quoi que ce soit, il faut savoir où sont les points. Ils ont déménagé.

| Année | QCM | Conception |
|---|---|---|
| 2022 | **20 pts** (40 questions) | 5 pts |
| 2023 | 15 pts (30 questions) | 5 pts |
| 2024 | 15 pts (30 questions) | 5 pts |
| **2025** | **5 pts** (10 questions) | **15 pts** (3 × 5) |

En trois ans, le QCM est passé de l'essentiel de la note au quart. Réviser d'abord
les définitions de cours, c'est travailler pour 5 points sur 20.

**Les trois exercices de conception valent 15 points, et ils sont formulaires.**
MD,
                        'formalism' => <<<'MD'
Chaque exercice de conception est rédigé exactement de la même façon, et le barème
est annoncé dans l'énoncé :

| Attendu | Points |
|---|---|
| Notions objet mobilisées (interface, classe abstraite, héritage) | 1 |
| Patron n° 1, identifié sur le schéma | 1 |
| Patron n° 2, identifié sur le schéma | 1 |
| Patron n° 3, identifié sur le schéma | 1 |
| Cohérence et logique globale de la solution | 1 |

Deux clauses décisives, écrites noir sur blanc dans l'énoncé :

> « Il faut identifier chaque pattern sur le schéma —
> **si vous ne le faites pas il n'y a pas de point attribué**. »

> « Les patterns Singleton et Builder sont hors scope. Vous pouvez les utiliser
> mais ils ne comptent pas comme un des 3 patterns. »

Un diagramme juste mais non annoté vaut **zéro** sur les trois points de patrons.
MD,
                        'worked_example' => <<<'MD'
**La structure de réponse, à appliquer telle quelle aux trois exercices :**

1. **Le diagramme de classes.** Les entités de l'énoncé, avec au moins une interface
   ou une classe abstraite et une relation d'héritage — c'est le point « notions objet ».

2. **Trois étiquettes bien visibles sur le schéma.** Encadrez la zone concernée et
   écrivez le nom du patron à côté :
   `« ◄── Composite »`, `« ◄── État »`, `« ◄── Stratégie »`.

3. **Trois lignes de justification sous le schéma**, une par patron. L'énoncé précise :
   *« vous pouvez ajouter des explications pour justifier votre réponse, elles seront
   lues et prises en compte »*. C'est le point de cohérence qui se joue là.

**Répartition du temps sur 3 heures :**

| | Temps | Cumul |
|---|---|---|
| Lecture du sujet complet | 5 min | 5 min |
| QCM (5 pts) | 20 min | 25 min |
| Conception 1 (5 pts) | 45 min | 1 h 10 |
| Conception 2 (5 pts) | 45 min | 1 h 55 |
| Conception 3 (5 pts) | 45 min | 2 h 40 |
| Relecture : les étiquettes sont-elles toutes là ? | 20 min | 3 h |
MD,
                        'pitfalls' => <<<'MD'
- **Dessiner sans étiqueter.** Le piège le plus coûteux du sujet, et il est annoncé.
  Trois points partent en fumée pour une annotation oubliée.
- **Choisir Singleton ou Builder** comme l'un des trois patrons : ils sont hors scope.
- **Passer trop de temps sur le QCM.** Il vaut 5 points et pénalise l'erreur.
  Vingt minutes maximum.
- **Ne traiter que deux exercices de conception sur trois.** Chacun vaut 5 points,
  soit un quart de la note. Un exercice non abordé coûte plus qu'un exercice bâclé.
- **Oublier interface et classe abstraite** dans le diagramme : c'est un point entier,
  et il s'obtient presque gratuitement.
MD,
                        'examiner_expects' => <<<'MD'
Sur la copie, pour chaque exercice de conception :

- [ ] Un **diagramme de classes** couvrant toutes les entités de l'énoncé.
- [ ] Au moins une **interface** ou une **classe abstraite**, et une **relation d'héritage**.
- [ ] **Trois patrons nommés explicitement sur le schéma**, hors Singleton et Builder.
- [ ] Une **ligne de justification** par patron, sous le schéma.

Le correcteur coche cinq cases. Rien d'autre.
MD,
                        'source_refs' => [
                            ['label' => 'ALO_Examen_2025_01.pdf'],
                            ['label' => 'ALO_Examen_2025_01_Corrige.pdf'],
                        ],
                    ],
                    [
                        'title' => 'Le décodeur : du point d\'attention au patron',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
Les « points d'attention » ne sont pas rédigés au hasard. L'enseignant décrit toujours
le **problème que le patron résout**, dans les mêmes termes d'une année sur l'autre.

Une fois qu'on a vu trois sujets, la correspondance devient mécanique. C'est ce que
fait ce décodeur, construit à partir des corrigés officiels 2024 et 2025.
MD,
                        'formalism' => <<<'MD'
| Ce que dit l'énoncé | Le patron |
|---|---|
| un tout composé de parties, elles-mêmes composables · « agencements composés de… » · « s'organisent en groupe » · un jeu de cartes | **Composite** |
| le comportement change selon une phase, une saison, un moment · « en hiver… en été… » · « le jour… la nuit… » | **État** |
| un choix de mode opératoire · « bio ou standard » · un traitement qui varie selon un type | **Stratégie** |
| un objet doit être **informé** de ce qui arrive ailleurs · « monitorer les entrées et sorties » · « chaque objet doit être informé lorsqu'un autre le touche » | **Observateur** |
| on **enrichit** un objet sans changer sa classe · « la fourmi sans rien devient une fourmi avec de la nourriture » · ajouter une option, un supplément | **Décorateur** |
| on **parcourt** une structure pour y appliquer un traitement, sans la modifier · calculer un total sur un ensemble hétérogène | **Visiteur** |
| séparer les données, l'affichage et la commande · interface graphique | **MVC** |
| une instance unique partagée | **Singleton** — *hors scope au barème* |
| construction complexe, arguments nombreux ou optionnels | **Builder** — *hors scope au barème* |
MD,
                        'worked_example' => <<<'MD'
**Session de janvier 2025 — les trois exercices, avec les réponses du corrigé.**

*Exercice 1 — le jardin* (plantes, herbes, fleurs, insectes) :

| Point d'attention | Réponse officielle |
|---|---|
| « des agencements composés de fleurs, d'herbes et de plantes » | **Composite** |
| « le fonctionnement change selon les saisons : en hiver… en été… » | **État** |
| « on adopte le mode opératoire *bio* ou *standard* » | **Stratégie** |

*Exercice 2 — la fourmilière* (fourmis, tunnels, provisions) :

| Point d'attention | Réponse officielle |
|---|---|
| « les fourmis s'organisent en groupe » | **Composite** |
| « maintenir un compte précis, monitorer les entrées et sorties » | **Observateur** |
| « la fourmi *sans rien* devient une fourmi *avec de la nourriture* » | **Décorateur** |

*Exercice 3 — les points de vente d'une grande surface* :

| Point d'attention | Réponse officielle |
|---|---|
| — | **État** |
| — | **Visiteur** *(le corrigé note : « observateur est également possible »)* |
| — | **Stratégie** |

**Session de mai 2024 — l'aquarium :**

| Point d'attention | Patron |
|---|---|
| « chaque objet doit être informé lorsqu'un autre objet le touche » | **Observateur** |
| « l'image s'adapte selon l'heure : éclairé le jour, sombre la nuit » | **État** |

Le motif saute aux yeux : **Composite, État, Stratégie, Observateur et Décorateur
couvrent la quasi-totalité des points d'attention posés en quatre ans.**
MD,
                        'pitfalls' => <<<'MD'
- **Confondre État et Stratégie.** Les deux encapsulent un comportement variable.
  La distinction : l'**État** change tout seul au fil de la vie de l'objet (les saisons
  passent) ; la **Stratégie** est choisie de l'extérieur et ne change pas seule
  (on décide *bio* ou *standard* à la création).
- **Confondre Décorateur et Composite.** Le **Décorateur** enveloppe **un** objet pour
  lui ajouter quelque chose ; le **Composite** regroupe **plusieurs** objets en un tout
  manipulable comme un seul.
- **Confondre Observateur et Visiteur.** L'**Observateur** notifie : quelque chose a
  changé, préviens les intéressés. Le **Visiteur** parcourt : applique ce traitement
  à chaque élément de la structure.
- **Répondre Singleton** parce que « il n'y a qu'un seul jardin ». C'est vrai, et
  ça ne rapporte rien : hors scope.
MD,
                        'examiner_expects' => <<<'MD'
Un nom de patron, écrit sur le schéma, en face de la zone concernée.

Le corrigé de l'exercice 3 accepte deux réponses (« Visiteur. Note : observateur est
également possible »), ce qui confirme que **la justification compte** : si votre
patron est défendable et que vous expliquez pourquoi en une ligne, il passe.

Mais un patron non nommé sur le schéma ne passe jamais, aussi juste soit-il.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => "Combien de points valent les exercices de conception à l'épreuve d'ALO ?",
                        'back' => "**15 sur 20** depuis 2025 — trois exercices à 5 points.\n\nLe QCM est tombé de 20 points (2022) à 15 (2023-2024) puis 5 (2025). Réviser d'abord les définitions, c'est travailler pour un quart de la note.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Vous dessinez un diagramme de classes parfait, sans écrire le nom des patrons dessus. Combien de points ?",
                        'back' => "**Zéro** sur les trois points de patrons.\n\nL'énoncé le dit : « Il faut identifier chaque pattern sur le schéma, si vous ne le faites pas il n'y a pas de point attribué ».",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Quels patrons sont hors scope au barème de l'exercice de conception ?",
                        'back' => "**Singleton et Builder.**\n\nVous pouvez les utiliser, mais ils ne comptent pas parmi les trois patrons demandés. En 2024, seul Singleton était exclu ; en 2025, les deux.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« Dans le jardin on trouve des agencements composés de fleurs, d'herbes et de plantes. » Quel patron ?",
                        'back' => "**Composite**\n\nUn tout composé de parties, manipulable comme un seul élément. Réponse du corrigé de janvier 2025.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« Le fonctionnement du jardin change selon les saisons : en hiver les insectes sont absents, en été tout est vivant. » Quel patron ?",
                        'back' => "**État**\n\nLe comportement de l'objet change au fil de sa vie, tout seul. Réponse du corrigé de janvier 2025.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« Quand on crée un jardin il faut choisir : mode opératoire *bio* ou *standard*. » Quel patron ?",
                        'back' => "**Stratégie**\n\nUn algorithme interchangeable, choisi de l'extérieur. Réponse du corrigé de janvier 2025.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« La fourmilière doit maintenir un compte précis du nombre d'individus, en monitorant les entrées et sorties. » Quel patron ?",
                        'back' => "**Observateur**\n\nUn objet doit être informé d'un changement survenu ailleurs. Réponse du corrigé de janvier 2025.",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« Une fourmi *sans rien* devient une fourmi *avec de la nourriture sur le dos*. » Quel patron ?",
                        'back' => "**Décorateur**\n\nOn enrichit un objet existant sans changer sa classe. Réponse du corrigé de janvier 2025.\n\n*(Piège : ce n'est pas État — la fourmi ne change pas de comportement, elle gagne une responsabilité.)*",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'État ou Stratégie : comment trancher ?',
                        'back' => "**État** — le comportement change **tout seul** au fil de la vie de l'objet (les saisons passent, l'heure tourne).\n\n**Stratégie** — le comportement est **choisi de l'extérieur** et ne change pas seul (on décide *bio* ou *standard*).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Décorateur ou Composite : comment trancher ?',
                        'back' => "**Décorateur** — enveloppe **un** objet pour lui ajouter une responsabilité.\n\n**Composite** — regroupe **plusieurs** objets en un tout qu'on manipule comme un seul, récursivement.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Observateur ou Visiteur : comment trancher ?',
                        'back' => "**Observateur** — *notifie* : quelque chose a changé, préviens les intéressés.\n\n**Visiteur** — *parcourt* : applique un traitement à chaque élément d'une structure, sans la modifier.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Répartition du temps sur les 3 heures d'ALO ?",
                        'back' => "| | Temps |\n|---|---|\n| Lecture du sujet | 5 min |\n| QCM (5 pts) | 20 min |\n| Conception 1 | 45 min |\n| Conception 2 | 45 min |\n| Conception 3 | 45 min |\n| Relecture des étiquettes | 20 min |\n\nLe QCM ne vaut que 5 points : ne pas s'y attarder.",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Décoder dix points d\'attention',
                        'origin' => 'genere',
                        'est_minutes' => 20,
                        'difficulty' => 3,
                        'statement' => <<<'MD'
Pour chacun des points d'attention suivants — tous tirés des sujets réels de 2024 et
2025, ou construits sur le même moule — donnez **le patron attendu**, en une ligne
de justification.

Rappel : Singleton et Builder sont hors scope.

1. « Dans le jardin nous trouvons des agencements composés de fleurs, d'herbes et de plantes. »
2. « Le fonctionnement de notre jardin change en fonction des saisons. »
3. « Lorsque l'on crée un jardin il faut faire un choix : mode opératoire bio ou standard. »
4. « Dans notre fourmilière, les fourmis s'organisent en groupe pour aller chercher de la nourriture. »
5. « La fourmilière doit maintenir un compte précis du nombre d'individus à l'intérieur. »
6. « Lorsqu'une fourmi trouve de la nourriture, elle la prend sur son dos : la fourmi *sans rien* devient une fourmi *avec nourriture*. »
7. « Chaque objet de l'aquarium doit être informé lorsqu'un autre objet le touche. »
8. « L'image devra s'adapter en fonction de l'heure de la journée : claire le jour, sombre la nuit. »
9. « Chaque demande client a un type, et selon ce type un traitement approprié doit être exécuté. »
10. « Quel pattern pour modéliser un jeu de cartes ? »
MD,
                        'hint' => "Cinq patrons suffisent à couvrir les dix : Composite, État, Stratégie, Observateur, Décorateur. Cherchez d'abord le mot déclencheur — « composé de », « change selon », « choix », « informé », « devient ».",
                        'method' => <<<'MD'
Pour chaque énoncé, posez-vous trois questions dans l'ordre :

1. **Est-ce une structure ?** Un tout fait de parties → Composite. Un objet enrichi → Décorateur.
2. **Est-ce un comportement variable ?** Il change seul → État. Il est choisi → Stratégie.
3. **Est-ce une communication ?** On prévient → Observateur. On parcourt → Visiteur.

Si aucune ne s'applique, relisez : l'énoncé décrit toujours le problème que le patron résout.
MD,
                        'solution' => <<<'MD'
1. **Composite** — un tout composé de parties, manipulable comme un seul élément. *(corrigé 2025, ex. 1)*
2. **État** — le comportement change au fil de la vie de l'objet. *(corrigé 2025, ex. 1)*
3. **Stratégie** — un mode opératoire choisi de l'extérieur, interchangeable. *(corrigé 2025, ex. 1)*
4. **Composite** — les fourmis se regroupent en unités manipulables comme une seule. *(corrigé 2025, ex. 2)*
5. **Observateur** — la fourmilière est notifiée de chaque entrée et sortie. *(corrigé 2025, ex. 2)*
6. **Décorateur** — on enveloppe la fourmi pour lui ajouter une responsabilité, sans changer sa classe. *(corrigé 2025, ex. 2)*
7. **Observateur** — chaque objet s'abonne aux collisions qui le concernent. *(sujet 2024)*
8. **État** — le rendu change selon la phase jour/nuit, qui évolue seule. *(sujet 2024)*
9. **Stratégie** — un traitement interchangeable selon le type de demande. *(QCM 2025, question 6, réponse G)*
10. **Composite** — un jeu de cartes est un ensemble de cartes et de paquets, récursivement. *(QCM 2025, question 3, réponse A)*

**Le constat :** cinq patrons couvrent les dix cas. Composite apparaît trois fois,
État deux fois, Stratégie deux fois, Observateur deux fois, Décorateur une fois.
MD,
                        'rubric' => [
                            ['label' => '1. Composite', 'points' => 1],
                            ['label' => '2. État', 'points' => 1],
                            ['label' => '3. Stratégie', 'points' => 1],
                            ['label' => '4. Composite', 'points' => 1],
                            ['label' => '5. Observateur', 'points' => 1],
                            ['label' => '6. Décorateur (et non État)', 'points' => 1],
                            ['label' => '7. Observateur', 'points' => 1],
                            ['label' => '8. État', 'points' => 1],
                            ['label' => '9. Stratégie', 'points' => 1],
                            ['label' => '10. Composite', 'points' => 1],
                            ['label' => 'Chaque réponse est accompagnée d’une ligne de justification', 'points' => 2],
                        ],
                    ],
                ],
            ],

            /* ==================== Patrons structurels ==================== */
            'DP-Struct' => [
                'lessons' => [
                    [
                        'title' => 'Composite et Décorateur',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Les deux enveloppent un objet dans un objet de même interface. C'est pour cela qu'on
les confond. La différence tient à un seul mot : **combien**.

Le **Composite** enveloppe **plusieurs** enfants pour qu'un groupe se manipule comme
un élément unique. Un bouquet est une fleur, un agencement est une plante,
un paquet de cartes est une carte.

Le **Décorateur** enveloppe **un seul** objet pour lui ajouter une responsabilité.
La fourmi chargée est une fourmi, plus la nourriture.
MD,
                        'formalism' => <<<'MD'
**Composite**

```
        ┌─────────────┐
        │ «interface» │
        │  Composant  │
        │ operation() │
        └──────△──────┘
          ┌────┴────┐
   ┌──────┴────┐  ┌─┴──────────────┐
   │  Feuille  │  │   Composite    │◇──┐
   │operation()│  │ operation()    │   │ enfants *
   └───────────┘  │ ajouter(c)     │───┘
                  │ retirer(c)     │
                  └────────────────┘
```

Le `Composite` **contient** des `Composant`, donc potentiellement d'autres composites :
c'est la récursion qui fait tout l'intérêt. `operation()` sur un composite délègue à
chacun de ses enfants.

**Décorateur**

```
        ┌─────────────┐
        │ «interface» │
        │  Composant  │
        │ operation() │
        └──────△──────┘
          ┌────┴─────────┐
   ┌──────┴────┐  ┌──────┴───────┐
   │  Concret  │  │  Décorateur  │◇──┐
   │operation()│  │ operation()  │   │ 1 seul composant
   └───────────┘  └──────△───────┘───┘
                         │
                  ┌──────┴────────┐
                  │ DécoConcretA  │
                  │ operation()   │
                  └───────────────┘
```

Le `Décorateur` **référence un seul** `Composant`, appelle son `operation()`,
et ajoute son propre traitement avant ou après.
MD,
                        'worked_example' => <<<'MD'
**Composite — le jardin (sujet de janvier 2025)**

```java
public interface ElementJardin {
    void afficher();
}

public class Fleur implements ElementJardin {           // feuille
    public void afficher() { System.out.println("fleur"); }
}

public class Agencement implements ElementJardin {      // composite
    private final List<ElementJardin> elements = new ArrayList<>();

    public void ajouter(ElementJardin e) { elements.add(e); }

    public void afficher() {
        for (ElementJardin e : elements) e.afficher();   // délégation récursive
    }
}
```

Un `Agencement` peut contenir des fleurs, des herbes… et d'autres agencements.
Le client ne fait pas la différence : il appelle `afficher()`.

**Décorateur — la fourmi chargée (sujet de janvier 2025)**

```java
public interface Fourmi {
    double poids();
}

public class FourmiSimple implements Fourmi {
    public double poids() { return 0.01; }
}

public abstract class DecorateurFourmi implements Fourmi {
    protected final Fourmi fourmi;                       // un seul objet enveloppé
    protected DecorateurFourmi(Fourmi f) { this.fourmi = f; }
}

public class AvecNourriture extends DecorateurFourmi {
    private final double poidsCharge;

    public AvecNourriture(Fourmi f, double p) { super(f); this.poidsCharge = p; }

    public double poids() { return fourmi.poids() + poidsCharge; }   // ajout
}
```

`new AvecNourriture(new FourmiSimple(), 0.005)` reste une `Fourmi`.
On peut empiler les décorateurs — c'est leur autre force.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier que le Composite est récursif.** Un composite qui ne contient que des
  feuilles n'est qu'une liste. L'intérêt naît quand il peut contenir des composites.
- **Faire hériter le Décorateur de la classe concrète** au lieu de l'interface :
  on perd la possibilité de décorer n'importe quelle implémentation.
- **Utiliser Décorateur pour un changement de comportement.** Si l'objet doit
  *se comporter autrement*, c'est État. Si on lui *ajoute* quelque chose, c'est Décorateur.
- **Ne pas montrer l'agrégation sur le schéma.** Le losange qui relie le composite à
  ses enfants est ce qui prouve au correcteur que vous avez compris.
MD,
                        'examiner_expects' => <<<'MD'
Sur le diagramme :

- [ ] Une **interface** ou classe abstraite commune (`Composant`).
- [ ] Pour le Composite : la **relation d'agrégation** du composite vers l'interface,
      avec la multiplicité `*`.
- [ ] Pour le Décorateur : la **référence unique** vers l'interface, et une classe
      décorateur **abstraite** dont héritent les décorateurs concrets.
- [ ] Le **nom du patron écrit à côté** de la zone concernée.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Composite : intention en une phrase ?',
                        'back' => "**Composer des objets en arborescence pour que le client traite de la même façon un objet isolé et un groupe d'objets.**\n\nLa clé : le composite implémente la même interface que ses enfants, donc il peut contenir d'autres composites.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Décorateur : intention en une phrase ?',
                        'back' => "**Ajouter dynamiquement une responsabilité à un objet, sans modifier sa classe ni recourir à l'héritage.**\n\nLe décorateur enveloppe **un seul** objet, implémente la même interface, et empile son traitement.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Combien d’objets un Composite enveloppe-t-il ? Et un Décorateur ?',
                        'back' => "**Composite : plusieurs** (agrégation `*`).\n**Décorateur : un seul** (référence simple).\n\nC'est la seule différence structurelle entre les deux, et elle suffit à trancher.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ================== Patrons comportementaux ================== */
            'DP-Comp' => [
                'lessons' => [
                    [
                        'title' => 'État, Stratégie, Observateur, Visiteur',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Quatre patrons, deux couples faciles à confondre.

**État contre Stratégie.** Structurellement, ils sont presque identiques : une interface,
des implémentations, un contexte qui délègue. La différence est dans **qui décide**.
La stratégie est choisie de l'extérieur, une fois, et ne change pas seule. L'état change
tout seul, au fil de la vie de l'objet, et sait souvent vers quel état passer ensuite.

**Observateur contre Visiteur.** L'observateur **prévient** : je change, que ceux que
ça intéresse s'adaptent. Le visiteur **parcourt** : je passe sur chaque élément et
j'applique un traitement qui n'appartient pas à la structure.
MD,
                        'formalism' => <<<'MD'
**État**

```
┌──────────┐        ┌─────────────┐
│ Contexte │◇──────▷│ «interface» │
│ requete()│  état  │    État     │
└──────────┘        │  gerer(ctx) │
                    └──────△──────┘
                     ┌─────┴─────┐
              ┌──────┴───┐  ┌────┴──────┐
              │  Hiver   │  │    Été    │
              │gerer(ctx)│  │ gerer(ctx)│
              └──────────┘  └───────────┘
```

`Contexte.requete()` délègue à `état.gerer(this)`. L'état concret peut appeler
`ctx.setEtat(new Ete())` : **c'est l'état qui pilote la transition.**

**Stratégie** — même dessin, mais le contexte reçoit sa stratégie au constructeur
ou par un `setStrategie()` appelé par le client. Aucune stratégie ne change elle-même.

**Observateur**

```
┌─────────────┐  observateurs *  ┌─────────────┐
│   Sujet     │◇────────────────▷│ «interface» │
│ attacher(o) │                  │ Observateur │
│ detacher(o) │                  │ actualiser()│
│ notifier()  │                  └──────△──────┘
└─────────────┘                         │
                                 ┌──────┴───────┐
                                 │ ObsConcret   │
                                 │ actualiser() │
                                 └──────────────┘
```

`notifier()` boucle sur les observateurs et appelle `actualiser()` sur chacun.

**Visiteur**

```
┌─────────────┐            ┌──────────────────┐
│ «interface» │            │   «interface»    │
│   Element   │            │     Visiteur     │
│accepter(v)  │            │ visiterA(A a)    │
└──────△──────┘            │ visiterB(B b)    │
   ┌───┴────┐              └────────△─────────┘
┌──┴──┐  ┌──┴──┐              ┌─────┴──────┐
│  A  │  │  B  │              │ VisConcret │
└─────┘  └─────┘              └────────────┘
```

`A.accepter(v)` appelle `v.visiterA(this)` : c'est la **double distribution**,
le mécanisme central du patron.
MD,
                        'worked_example' => <<<'MD'
**État — le jardin au fil des saisons (janvier 2025, point d'attention n° 2)**

```java
public interface SaisonEtat {
    void simuler(Jardin jardin);
}

public class Hiver implements SaisonEtat {
    public void simuler(Jardin jardin) {
        jardin.masquerInsectes();
        jardin.mettreVegetauxAuRepos();
        jardin.setSaison(new Printemps());     // l'état pilote la transition
    }
}

public class Ete implements SaisonEtat {
    public void simuler(Jardin jardin) {
        jardin.afficherInsectes();
        jardin.activerCouleursEtOdeurs();
        jardin.setSaison(new Automne());
    }
}

public class Jardin {
    private SaisonEtat saison = new Printemps();
    public void setSaison(SaisonEtat s) { this.saison = s; }
    public void simuler() { saison.simuler(this); }
}
```

**Stratégie — le mode opératoire (janvier 2025, point d'attention n° 3)**

```java
public interface ModeOperatoire {
    void traiter(Plante p);
}

public class ModeBio implements ModeOperatoire {
    public void traiter(Plante p) { p.appliquerPurin(); }
}

public class ModeStandard implements ModeOperatoire {
    public void traiter(Plante p) { p.appliquerPesticide(); }
}

public class Jardin {
    private final ModeOperatoire mode;
    public Jardin(ModeOperatoire mode) { this.mode = mode; }   // choisi de l'extérieur
}
```

Comparez les deux `Jardin` : l'état est un champ **mutable que l'état lui-même modifie** ;
la stratégie est un champ **`final` reçu au constructeur**. C'est la signature de la distinction.
MD,
                        'pitfalls' => <<<'MD'
- **Dessiner État et Stratégie de la même façon sans les nommer.** Le correcteur ne
  peut pas deviner : l'étiquette sur le schéma est ce qui départage.
- **Faire changer l'état depuis le contexte.** C'est l'état concret qui appelle
  `ctx.setEtat(...)` ; sinon le patron perd son intérêt.
- **Oublier `attacher` et `detacher`** sur le sujet de l'Observateur : sans elles,
  ce n'est qu'une liste de callbacks.
- **Oublier la double distribution** du Visiteur : `accepter(v)` doit appeler
  `v.visiterX(this)`. Sans ce renvoi, ce n'est pas un visiteur.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] **État** : le contexte a un champ **mutable** vers l'interface, et les états
      concrets peuvent le modifier.
- [ ] **Stratégie** : le contexte reçoit sa stratégie **de l'extérieur**, elle ne
      change pas seule.
- [ ] **Observateur** : `attacher()`, `detacher()`, `notifier()` sur le sujet,
      `actualiser()` sur l'observateur, agrégation `*`.
- [ ] **Visiteur** : `accepter(Visiteur)` sur les éléments, une méthode `visiterX()`
      par type concret.
- [ ] Chaque patron **nommé sur le schéma**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => "Dans le code, comment reconnaître un État d'une Stratégie ?",
                        'back' => "**État** — champ **mutable**, et l'état concret appelle `ctx.setEtat(...)` : il pilote sa propre transition.\n\n**Stratégie** — champ souvent **`final`**, reçu au constructeur, jamais modifié de l'intérieur.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Les trois méthodes du sujet dans le patron Observateur ?',
                        'back' => "`attacher(Observateur)`\n`detacher(Observateur)`\n`notifier()`\n\nEt côté observateur : `actualiser()`.\n\nSans `attacher`/`detacher`, ce n'est qu'une liste de callbacks.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Qu\'est-ce que la double distribution du Visiteur ?',
                        'back' => "`element.accepter(visiteur)` appelle en retour `visiteur.visiterElement(this)`.\n\nLe premier appel choisit le type d'élément, le second le type de visiteur. C'est le mécanisme central : sans ce renvoi, ce n'est pas un visiteur.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Visiteur : quel est son intérêt principal ?',
                        'back' => "**Ajouter un traitement à une structure d'objets sans modifier leurs classes.**\n\nOn externalise l'algorithme dans le visiteur. Contrepartie : ajouter un nouveau type d'élément oblige à modifier tous les visiteurs.",
                    ],
                ],
            ],

            /* ======================== Modèle objet ======================== */
            'C1-Objet' => [
                'lessons' => [
                    [
                        'title' => 'Les définitions que le QCM interroge',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
Le QCM ne vaut plus que 5 points, mais il **pénalise l'erreur** : −0,25 par mauvaise
réponse, 0 pour une abstention. Il ne se joue donc pas au feeling. Ces définitions
sont celles qui reviennent d'une année sur l'autre.
MD,
                        'formalism' => <<<'MD'
**Les quatre relations entre classes** — c'est la question qui revient le plus souvent :

| Relation | Sens | Exemple du cours |
|---|---|---|
| **Association** | deux objets se connaissent, vies indépendantes | une voiture et son propriétaire, un compte et son client |
| **Agrégation** | un tout regroupe des parties qui lui survivent | une équipe et ses joueurs |
| **Composition** | un tout possède des parties qui meurent avec lui | une maison et ses pièces |
| **Héritage** | « est un » | un chien est un animal |

*Attention :* à la question « la relation entre une voiture et son propriétaire »,
le corrigé 2025 répond **association** (réponse D), pas agrégation.

**Les autres définitions récurrentes**

- **`Object`** est la seule classe sans classe mère. Une classe abstraite en a une —
  au minimum `Object`.
- `MaClass obj;` sans `new` : `obj` contient **`null`**. Aucune mémoire n'est allouée.
- Un **attribut de classe** (`static`) est **commun à toutes les instances**,
  pas dupliqué dans chacune.
- Lancer une exception : `throw new IllegalArgumentException("message");`
  Un `catch` ne lance pas, il attrape ; `setMessage()` n'existe pas.
- Une **interface** ne peut pas être instanciée, et une variable de type interface ne
  peut référencer qu'une classe qui l'implémente — directement ou par héritage.
MD,
                        'worked_example' => <<<'MD'
**La question de typage, tombée en 2025 (question 8) :**

```java
interface Position {}
class Premier {}
class Second extends Premier {}
class Troisieme extends Premier {}
class Quatrieme extends Second implements Position {}
class Cinquieme extends Quatrieme {}
class Sixieme extends Troisieme {}
```

Quelles instructions compilent ?

| Proposition | Verdict |
|---|---|
| `Position pos = new Sixieme();` | ❌ `Sixieme` hérite de `Troisieme`, qui n'implémente pas `Position`. |
| `Premier premier = new Cinquieme();` | ✅ `Cinquieme` → `Quatrieme` → `Second` → `Premier`. |
| `Position pos = new Quatrieme();` | ✅ `Quatrieme implements Position`. |
| `Troisieme troisieme = new Quatrieme();` | ❌ `Quatrieme` descend de `Second`, pas de `Troisieme`. |

**Réponses : B et C.**

*Méthode :* remontez la chaîne d'héritage depuis la classe instanciée. Si le type
déclaré n'y figure pas — ni comme ancêtre, ni comme interface implémentée par un
ancêtre — ça ne compile pas.
MD,
                        'pitfalls' => <<<'MD'
- **Répondre au hasard.** Avec −0,25 par erreur, deviner sur quatre propositions a une
  espérance de **−0,0625 point**. Il faut dépasser **une chance sur trois** pour que
  répondre devienne rentable.
- **Confondre agrégation et association.** L'agrégation suppose un rapport
  tout/partie. Une voiture n'est pas une partie de son propriétaire : c'est une association.
- **Oublier qu'une question peut avoir plusieurs réponses.** L'énoncé indique le nombre
  attendu, et il faut **toutes** les donner pour avoir le point.
MD,
                        'examiner_expects' => <<<'MD'
Les réponses sur le **feuillet réponse**, pas sur le questionnaire — l'énoncé le
précise en gras.

Et le calcul avant de cocher : *puis-je éliminer assez de propositions pour être
au-dessus d'une chance sur trois ?* Si oui, je réponds. Sinon, je laisse vide et
je passe à la conception, où sont les 15 points.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => "QCM d'ALO : quel est le barème exact ?",
                        'back' => "**Bonne réponse : +0,5**\n**Pas de réponse : 0**\n**Mauvaise réponse : −0,25**\n\nNote minimale du QCM : 0. Répondre au hasard sur 4 propositions a une espérance de **−0,0625**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "QCM d'ALO : à partir de quand faut-il répondre plutôt que s'abstenir ?",
                        'back' => "**Dès que vous dépassez une chance sur trois** d'avoir juste.\n\nSeuil : 0,5p − 0,25(1−p) = 0 → **p = 1/3**.\n\nSur quatre propositions, en éliminer deux suffit (p = 1/2, espérance +0,125).",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quelle classe Java n’a pas de classe mère ?',
                        'back' => "**`Object`** — et elle seule.\n\nUne classe abstraite a une classe mère (au minimum `Object`). `String` hérite d'`Object`.\n\n*Question 1 du QCM 2025, réponse C.*",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Que contient `obj` après `MaClass obj;` ?',
                        'back' => "**`null`.**\n\nLa déclaration seule n'alloue rien. Il faut `new` pour créer l'objet et obtenir une référence.\n\n*Question 2 du QCM 2025, réponse A.*",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Un attribut de classe (`static`) : quelle propriété ?',
                        'back' => "**Il est commun à toutes les instances**, pas dupliqué dans chacune.\n\nIl n'est pas forcément abstrait, et il reste accessible depuis une instance (même si l'accès par le nom de la classe est préférable).\n\n*Question 5 du QCM 2025, réponse B.*",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Voiture et propriétaire, compte bancaire et client : quelle relation ?',
                        'back' => "**Association.**\n\nPas agrégation : il n'y a pas de rapport tout/partie. Les deux objets se connaissent et ont des vies indépendantes.\n\n*Question 7 du QCM 2025, réponse D.*",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Agrégation ou composition : quelle différence ?',
                        'back' => "**Agrégation** — le tout regroupe des parties qui lui **survivent** (une équipe et ses joueurs).\n\n**Composition** — le tout **possède** les parties, qui meurent avec lui (une maison et ses pièces).",
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Comment lancer une IllegalArgumentException avec un message ?',
                        'back' => "```java\nthrow new IllegalArgumentException(\"nombre negatif\");\n```\n\nUn `catch` **attrape**, il ne lance pas. Et `setMessage()` n'existe pas sur les exceptions Java.\n\n*Question 4 du QCM 2025, réponse A.*",
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "`Position` est une interface. `Quatrieme extends Second implements Position`, `Cinquieme extends Quatrieme`. `Position p = new Cinquieme();` compile-t-il ?",
                        'back' => "**Oui.** `Cinquieme` hérite de `Quatrieme`, qui implémente `Position` : l'interface est héritée.\n\n*Méthode : remontez la chaîne depuis la classe instanciée. Si le type déclaré n'y figure pas, ça ne compile pas.*",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quand le patron Builder est-il préconisé ? (3 situations)',
                        'back' => "1. **L'objet final est imposant et sa création complexe.**\n2. **Beaucoup d'arguments** doivent être passés à la construction.\n3. **Certains arguments sont optionnels.**\n\n*Question 10 du QCM 2025, réponses A, B, D. Le « les arguments doivent être correctement instanciés » (C) n'en fait pas partie.*",
                        'difficulty' => 4,
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */

    /**
     * Examen blanc au format réel de 2025 : QCM à pénalité puis trois exercices
     * de conception. Durée et créneau de l'épreuve du 24 août, 20 h – 23 h.
     */
    private function mockExam(Subject $subject): void
    {
        $source = Resource::where('subject_id', $subject->id)
            ->where('filename', 'ilike', 'ALO_Examen_2025_01.pdf')
            ->first();

        $examen = MockExam::updateOrCreate(
            ['slug' => 'alo-blanc-conception-et-patrons'],
            [
                'subject_id' => $subject->id,
                'source_resource_id' => $source?->id,
                'title' => 'ALO blanc n°1 — patrons de conception',
                'instructions' => <<<'MD'
Durée : **3 heures**, comme l'épreuve du 24 août de 20 h à 23 h.
Matériel autorisé : aucun.

**Barème du QCM** : bonne réponse +0,5 · pas de réponse 0 · **mauvaise réponse −0,25**.
Ne cochez que si vous pouvez éliminer assez de propositions.

**Exercices de conception** : décrivez votre diagramme de classes en texte
(classes, interfaces, relations), puis **nommez explicitement les trois patrons**.
Un patron non nommé ne rapporte aucun point — c'est la règle de l'énoncé réel.

Singleton et Builder sont hors scope : ils ne comptent pas parmi les trois patrons.

Gestion du temps conseillée : 20 min de QCM, 45 min par exercice, 20 min de relecture.
MD,
                'duration_min' => 180,
                'total_points' => 20,
                'origin' => 'genere',
                'year' => 2026,
            ]
        );

        $ch = fn (string $code) => Chapter::where('subject_id', $subject->id)
            ->where('code', $code)->value('id');

        $questions = [
            [
                'number' => 'QCM',
                'chapter_id' => $ch('C1-Objet'),
                'points' => 5,
                'statement' => <<<'MD'
Répondez par la lettre. **Une mauvaise réponse retire 0,25 point ; une abstention ne
retire rien.** Le nombre de réponses attendues est indiqué quand il est supérieur à un.

**1.** Quelle classe n'a pas de classe mère ?
a. Orpheline · b. String · c. Object · d. Une classe abstraite

**2.** Que contient `obj` après `MaClass obj;` ?
a. La valeur null · b. Un pointeur · c. L'adresse mémoire allouée · d. Un Object

**3.** Quel patron pour modéliser un jeu de cartes ?
a. Composite · b. Décorateur · c. État · d. Builder · e. Observateur · f. Singleton · g. Stratégie · h. Visiteur

**4.** Un attribut de classe :
a. Est forcément abstrait · b. Est commun à toutes les instances · c. Est dupliqué dans chaque instance · d. N'est pas accessible depuis une instance

**5.** Chaque demande client a un type ; selon ce type un traitement approprié est exécuté. Quel patron ?
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie · h. Visiteur

**6.** La relation entre une voiture et son propriétaire est :
a. Agrégation · b. Héritage · c. Composition · d. Association

**7.** Quelle est la bonne manière de lancer une `IllegalArgumentException` ?
a. `throw new IllegalArgumentException("msg");`
b. `catch (new IllegalArgumentException("msg"))`
c. `catch (IllegalArgumentException e) { e.setMessage("msg"); }`

**8.** Dans quelles situations le Builder est-il préconisé ? *(3 réponses)*
a. L'objet final est imposant et sa création complexe · b. Beaucoup d'arguments à la construction · c. Les arguments doivent être correctement instanciés · d. Certains arguments sont optionnels

**9.** Un objet doit être informé lorsqu'un autre change d'état. Quel patron ?
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie · h. Visiteur

**10.** Le comportement d'un objet change au fil des saisons, de lui-même. Quel patron ?
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie · h. Visiteur
MD,
                'solution' => <<<'MD'
**1 : c** — `Object` est la seule classe sans mère.
**2 : a** — la déclaration seule n'alloue rien, `obj` vaut `null`.
**3 : a** — Composite : un paquet de cartes se manipule comme une carte.
**4 : b** — un attribut `static` est commun à toutes les instances.
**5 : g** — Stratégie : un traitement interchangeable selon le type.
**6 : d** — Association : pas de rapport tout/partie.
**7 : a** — `throw new …`. Un `catch` attrape, `setMessage()` n'existe pas.
**8 : a, b, d** — la proposition c ne fait pas partie du corrigé officiel.
**9 : e** — Observateur.
**10 : c** — État : la transition se fait d'elle-même.

Les questions 1 à 4, 6, 7 et 8 sont reprises telles quelles du sujet de janvier 2025.
MD,
                'rubric' => [
                    ['label' => '1 : c (Object)', 'points' => 0.5],
                    ['label' => '2 : a (null)', 'points' => 0.5],
                    ['label' => '3 : a (Composite)', 'points' => 0.5],
                    ['label' => '4 : b (commun à toutes les instances)', 'points' => 0.5],
                    ['label' => '5 : g (Stratégie)', 'points' => 0.5],
                    ['label' => '6 : d (Association)', 'points' => 0.5],
                    ['label' => '7 : a (throw new)', 'points' => 0.5],
                    ['label' => '8 : a, b, d — les trois, sinon rien', 'points' => 0.5],
                    ['label' => '9 : e (Observateur)', 'points' => 0.5],
                    ['label' => '10 : c (État)', 'points' => 0.5],
                ],
            ],
            [
                'number' => 'Conception 1 — la bibliothèque municipale',
                'chapter_id' => $ch('DP-Method'),
                'points' => 5,
                'statement' => <<<'MD'
Vous devez réaliser un logiciel de gestion pour une bibliothèque municipale.
On y trouve des **livres**, des **revues**, des **DVD**, des **rayons** et des **lecteurs**.

Faites une modélisation objet complète, puis répondez aux trois points d'attention.

**Point d'attention #1** — Les rayons contiennent des ouvrages, mais aussi des
sous-rayons thématiques qui contiennent eux-mêmes des ouvrages. On doit pouvoir compter
les ouvrages d'un rayon comme d'un sous-rayon, de la même façon.

**Point d'attention #2** — Un ouvrage passe par plusieurs situations : *disponible*,
*emprunté*, *réservé*, *en réparation*. Ce qu'on peut en faire dépend de la situation
du moment, et le passage de l'une à l'autre se fait au fil de sa vie.

**Point d'attention #3** — Les lecteurs peuvent demander à être prévenus dès qu'un
ouvrage qu'ils attendent redevient disponible.

Rappels : minimum 3 patrons, **chacun nommé explicitement**. Singleton et Builder
hors scope. 1 point pour les notions objet, 1 point par patron, 1 point de cohérence.
MD,
                'solution' => <<<'MD'
**Notions objet (1 pt).** Une interface `ElementBibliotheque` (ou classe abstraite
`Ouvrage`) dont héritent `Livre`, `Revue` et `DVD`. `Lecteur` est une classe à part.
L'héritage et l'interface doivent apparaître sur le schéma.

**Patron 1 — Composite (1 pt).** « Des rayons contenant des sous-rayons, comptés de la
même façon » : structure récursive.
`ElementBibliotheque` est l'interface commune ; `Ouvrage` est la feuille ;
`Rayon` est le composite, agrégeant `*` `ElementBibliotheque`.

**Patron 2 — État (1 pt).** « Ce qu'on peut faire dépend de la situation, et la
transition se fait au fil de la vie » : interface `EtatOuvrage`, implémentée par
`Disponible`, `Emprunte`, `Reserve`, `EnReparation`. `Ouvrage` possède un champ
**mutable** `etat`, et chaque état déclenche la transition suivante.

*Piège :* ce n'est pas Stratégie — personne ne choisit l'état de l'extérieur.

**Patron 3 — Observateur (1 pt).** « Les lecteurs veulent être prévenus » :
`Ouvrage` est le sujet, avec `attacher(Lecteur)`, `detacher(Lecteur)` et `notifier()`.
`Lecteur` implémente `Observateur` avec `actualiser()`. Le passage à l'état
`Disponible` appelle `notifier()`.

**Cohérence (1 pt).** Les trois patrons se combinent sans se contredire : un `Rayon`
composite contient des `Ouvrage` qui portent chacun un état et une liste d'observateurs.
MD,
                'rubric' => [
                    ['label' => 'Une interface ou classe abstraite, et une relation d’héritage', 'points' => 1],
                    ['label' => 'Composite identifié et nommé sur le schéma (rayons récursifs)', 'points' => 1],
                    ['label' => 'État identifié et nommé sur le schéma (situations de l’ouvrage)', 'points' => 1],
                    ['label' => 'Observateur identifié et nommé sur le schéma (alerte des lecteurs)', 'points' => 1],
                    ['label' => 'Solution globalement cohérente, les trois patrons se combinent', 'points' => 1],
                ],
            ],
            [
                'number' => 'Conception 2 — la station de ski',
                'chapter_id' => $ch('DP-Method'),
                'points' => 5,
                'statement' => <<<'MD'
Vous devez réaliser un logiciel de gestion pour une station de ski. On y trouve des
**pistes**, des **remontées mécaniques**, des **secteurs** et des **skieurs**.

Faites une modélisation objet complète, puis répondez aux trois points d'attention.

**Point d'attention #1** — La station est découpée en secteurs, qui regroupent des
pistes et des remontées, et peuvent eux-mêmes contenir d'autres secteurs. On doit
pouvoir fermer un secteur entier comme on ferme une piste.

**Point d'attention #2** — À l'achat d'un forfait, le client choisit une formule de
tarification : *journée*, *semaine*, ou *saison*. La formule est fixée à l'achat et
détermine le calcul du prix.

**Point d'attention #3** — Un forfait de base peut recevoir des options : *assurance*,
*accès spa*, *cours collectif*. Chaque option s'ajoute au prix et au descriptif, et
plusieurs options peuvent être cumulées sur un même forfait.

Rappels : minimum 3 patrons, **chacun nommé explicitement**. Singleton et Builder
hors scope.
MD,
                'solution' => <<<'MD'
**Notions objet (1 pt).** Interface `ElementStation` implémentée par `Piste` et
`RemonteeMecanique` ; classe abstraite `Forfait` ; héritage visible sur le schéma.

**Patron 1 — Composite (1 pt).** « Des secteurs regroupant pistes et remontées, et
contenant d'autres secteurs, fermés de la même façon » : `Secteur` est le composite,
agrégeant `*` `ElementStation`. `fermer()` délègue récursivement.

**Patron 2 — Stratégie (1 pt).** « Le client choisit une formule à l'achat, elle
détermine le calcul du prix » : interface `FormuleTarifaire` avec `calculer(Forfait)`,
implémentée par `TarifJournee`, `TarifSemaine`, `TarifSaison`. `Forfait` reçoit sa
formule **au constructeur**.

*Piège :* ce n'est pas État — la formule est choisie de l'extérieur et ne change pas seule.

**Patron 3 — Décorateur (1 pt).** « Des options qui s'ajoutent au prix et au descriptif,
cumulables » : `DecorateurForfait` abstrait implémente `Forfait` et référence **un seul**
`Forfait`. `AvecAssurance`, `AvecSpa`, `AvecCours` en héritent et ajoutent leur montant.
L'empilement permet le cumul.

*Piège :* ce n'est pas Composite — on enveloppe **un** forfait, pas un groupe.

**Cohérence (1 pt).** La distinction Stratégie / Décorateur est le cœur de l'exercice :
la formule est le mode de calcul, les options sont des enrichissements empilés.
MD,
                'rubric' => [
                    ['label' => 'Une interface ou classe abstraite, et une relation d’héritage', 'points' => 1],
                    ['label' => 'Composite identifié et nommé (secteurs récursifs)', 'points' => 1],
                    ['label' => 'Stratégie identifiée et nommée (formule tarifaire, et non État)', 'points' => 1],
                    ['label' => 'Décorateur identifié et nommé (options cumulables, et non Composite)', 'points' => 1],
                    ['label' => 'Solution globalement cohérente', 'points' => 1],
                ],
            ],
            [
                'number' => 'Conception 3 — le théâtre',
                'chapter_id' => $ch('DP-Method'),
                'points' => 5,
                'statement' => <<<'MD'
Vous devez réaliser un logiciel de gestion pour un théâtre. On y trouve des
**salles**, des **sièges**, des **représentations**, des **spectateurs** et du
**personnel**.

Faites une modélisation objet complète, puis répondez aux trois points d'attention.

**Point d'attention #1** — Une représentation traverse plusieurs phases :
*en préparation*, *billetterie ouverte*, *complet*, *en cours*, *terminée*. Les
opérations autorisées dépendent de la phase, et le passage de l'une à l'autre se fait
au fil du temps.

**Point d'attention #2** — La direction veut produire plusieurs états sur l'ensemble
des salles et des sièges : un état comptable du chiffre d'affaires, un état technique
des sièges à réparer, un état d'accessibilité. Ces traitements ne doivent pas alourdir
les classes `Salle` et `Siege`, et d'autres états seront ajoutés plus tard.

**Point d'attention #3** — Les spectateurs abonnés souhaitent être avertis dès
l'ouverture de la billetterie d'une représentation.

Rappels : minimum 3 patrons, **chacun nommé explicitement**. Singleton et Builder
hors scope.
MD,
                'solution' => <<<'MD'
**Notions objet (1 pt).** Interface `ElementTheatre` implémentée par `Salle` et `Siege` ;
classe abstraite `Personne` dont héritent `Spectateur` et `Personnel`.

**Patron 1 — État (1 pt).** « Plusieurs phases, opérations autorisées selon la phase,
passage au fil du temps » : interface `EtatRepresentation`, implémentée par
`EnPreparation`, `BilletterieOuverte`, `Complet`, `EnCours`, `Terminee`.
`Representation` porte un champ **mutable** que chaque état fait évoluer.

**Patron 2 — Visiteur (1 pt).** « Des traitements qui ne doivent pas alourdir les
classes, et d'autres seront ajoutés plus tard » : c'est la signature exacte du Visiteur.
`ElementTheatre.accepter(VisiteurTheatre)` ; `VisiteurTheatre` déclare `visiterSalle()`
et `visiterSiege()` ; `VisiteurComptable`, `VisiteurTechnique` et `VisiteurAccessibilite`
l'implémentent. Ajouter un état revient à écrire un visiteur de plus, sans toucher
à `Salle` ni à `Siege`.

*Note :* le Composite est également défendable ici (une salle contient des sièges).
Le corrigé officiel de 2025 accepte ce type de double lecture **à condition de la
justifier en une ligne**.

**Patron 3 — Observateur (1 pt).** « Les abonnés veulent être avertis de l'ouverture
de la billetterie » : `Representation` est le sujet avec `attacher`, `detacher`,
`notifier` ; `Spectateur` implémente `actualiser()`. Le passage à l'état
`BilletterieOuverte` déclenche `notifier()`.

**Cohérence (1 pt).** État et Observateur se combinent naturellement : c'est la
transition d'état qui déclenche la notification.
MD,
                'rubric' => [
                    ['label' => 'Une interface ou classe abstraite, et une relation d’héritage', 'points' => 1],
                    ['label' => 'État identifié et nommé (phases de la représentation)', 'points' => 1],
                    ['label' => 'Visiteur identifié et nommé (états sur salles et sièges)', 'points' => 1],
                    ['label' => 'Observateur identifié et nommé (alerte des abonnés)', 'points' => 1],
                    ['label' => 'Solution cohérente : la transition d’état déclenche la notification', 'points' => 1],
                ],
            ],
        ];

        foreach ($questions as $i => $q) {
            MockExamQuestion::updateOrCreate(
                ['mock_exam_id' => $examen->id, 'number' => $q['number']],
                $q + ['position' => $i + 1]
            );
        }
    }
}