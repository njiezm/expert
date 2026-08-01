<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\ExamPaper;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Gap;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Diagnostic approfondi — issu de la relecture page à page des copies scannées.
 *
 * Deux découvertes changent la préparation :
 *
 * 1. ALO. Le 0/20 n'est pas une lacune de connaissances. Le correcteur écrit en
 *    tête de copie : « Pour les 3 questions de modélisation il était demandé un
 *    schéma. Vous avez rendu du pseudo-code. Je n'ai donc rien à noter. »
 *    Les trois réponses contiennent pourtant les bons patrons — Stratégie, État,
 *    Observateur — sous forme de plan indenté, jamais dessinés ni nommés.
 *
 * 2. SPP. L'épreuve portait massivement sur Why3, les prédicats du premier ordre
 *    et les définitions inductives sur les listes. Les exercices 2, 3 et 4 sont
 *    intégralement à zéro, avec les annotations « erreur de type », « incomplet »,
 *    « cours pas connu » et « + forall ».
 */
class DiagnosticApprofondiSeeder extends Seeder
{
    public function run(): void
    {
        $this->gaps();
        $this->aloSchema();
        $this->sppWhy3();
        $this->marquerPagesAnalysees();
    }

    /* ==================================================================== */

    private function gaps(): void
    {
        $entries = [
            'ALO' => [
                [
                    'title' => 'Pseudo-code rendu à la place du schéma — les 15 points annulés',
                    'chapter' => 'DP-Method',
                    'kind' => 'methode',
                    'severity' => 5,
                    'evidence' => "Pour les 3 questions de modélisation il était demandé un schéma. Vous avez rendu du pseudo-code. Je n'ai donc rien à noter.",
                    'explanation' => "Annotation portée en rouge en tête de copie. Les trois questions de conception ont été traitées sous forme de plan indenté — noms de classes, attributs et méthodes alignés par tabulations — au lieu d'un diagramme de classes. Le correcteur n'a rien pu noter : 15 points sur 20 annulés d'un coup. C'est la cause directe du 0/20.",
                    'remedy' => "Dessiner des rectangles reliés par des traits, toujours. Un diagramme de classes se compose de boîtes à trois compartiments (nom, attributs, méthodes) et de flèches typées. Aucune indentation ne remplace un trait.",
                ],
                [
                    'title' => 'Les patrons étaient corrects mais jamais nommés',
                    'chapter' => 'DP-Method',
                    'kind' => 'methode',
                    'severity' => 5,
                    'evidence' => '[Abstract], [Communicate] notés en marge',
                    'explanation' => "La conception 2 propose une « class Stratégie » avec Conduite Urbaine et Conduite Éco : c'est le patron Stratégie, correctement identifié. La conception 3 pose une « class Status » avec des règles de transition centralisées et des alertes vers Facturation et Service Client : c'est État plus Observateur. La conception 1 marque une classe « [Abstract] ». Aucun de ces patrons n'est écrit par son nom.",
                    'remedy' => "L'énoncé est explicite : « il faut identifier chaque pattern sur le schéma, si vous ne le faites pas il n'y a pas de point attribué ». Écrire le nom du patron, en toutes lettres, à côté de la zone du diagramme concernée.",
                ],
                [
                    'title' => 'La compréhension des patrons est acquise',
                    'chapter' => 'DP-Method',
                    'kind' => 'methode',
                    'severity' => 1,
                    'explanation' => "À contre-courant de la note : les trois copies de conception montrent une modélisation objet correcte — interfaces, classes abstraites, héritage, agrégations, et les trois bons patrons pour les trois points d'attention. Le fond est là. Seule la forme a été refusée.",
                    'remedy' => "Ne pas repartir de zéro sur ALO. L'effort doit porter sur le tracé du diagramme et l'étiquetage, pas sur la théorie des patrons.",
                ],
            ],

            'SPP' => [
                [
                    'title' => 'Syntaxe Why3 : confusion entre int et bool',
                    'chapter' => 'Contrats',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'erreur de type',
                    'explanation' => "Exercice 2 : `let P1 (a:int, b:int) : int = if a then b`. La condition d'un `if` doit être un booléen, pas un entier, et le type de retour ne correspond pas. Le correcteur a entouré la déclaration et écrit « erreur de type ». Note : 0.",
                    'remedy' => "Réviser le typage WhyML : `bool` pour les conditions, `int` pour les valeurs, `Prop` pour les formules logiques. Une implication logique `a → b` ne se code pas par un `if` mais s'exprime dans une `predicate` ou un `lemma`.",
                ],
                [
                    'title' => 'Déclaration de prédicats du premier ordre incomplète',
                    'chapter' => 'Pred',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'voir énoncé · incomplet',
                    'explanation' => "Exercice 3 : `predicate transitif = exist x. p x z` et `predicate asym = not(exist x. p y x)`. Les variables ne sont pas toutes quantifiées, les paramètres du prédicat ne sont pas déclarés, et la définition de la transitivité ne comporte pas les trois variables ni les deux hypothèses. Les trois questions sont à zéro.",
                    'remedy' => "Mémoriser les trois définitions canoniques : transitivité `forall x y z. p x y -> p y z -> p x z`, asymétrie `forall x y. p x y -> not (p y x)`, irréflexivité `forall x. not (p x x)`. Chaque variable doit être liée par un quantificateur.",
                ],
                [
                    'title' => 'Définition inductive de length sur les listes',
                    'chapter' => 'Calculs',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'cours pas connu',
                    'explanation' => "Exercice 4 : `length(Nil) = Nil = 0` puis `length(Cons(a,l)) = Cons'a list'a -> Cons'b(list'b)`. La première ligne confond la valeur et le constructeur, la seconde écrit des types au lieu d'une équation. Le correcteur a marqué « cours pas connu ». Toutes les questions de l'exercice sont à zéro.",
                    'remedy' => "Apprendre par cœur les deux équations : `length Nil = 0` et `length (Cons x r) = 1 + length r`. Une définition inductive donne une **valeur** par constructeur, jamais un type.",
                ],
                [
                    'title' => 'Lemmes énoncés sans quantificateur',
                    'chapter' => 'Pred',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => '+ forall',
                    'explanation' => "Exercice 4 question 5 : `lemma l2 : 0 < len(l)`. Deux fautes. La variable `l` est libre, donc l'énoncé n'a pas de valeur de vérité — le correcteur a écrit « + forall ». Et l'inégalité est stricte alors que la longueur d'une liste vide vaut zéro : il a corrigé `<` en `≤`.",
                    'remedy' => "Tout lemme se rédige clos : `lemma positive : forall l: list 'a. 0 <= length l`. Et vérifier le cas limite avant de choisir entre `<` et `<=` : la liste vide est presque toujours le contre-exemple.",
                ],
                [
                    'title' => 'Deux formules superposées à la question 8',
                    'chapter' => 'Prop',
                    'kind' => 'rigueur',
                    'severity' => 4,
                    'evidence' => 'les deux formules barrées en rouge',
                    'explanation' => "« Un étudiant a de bonnes notes sauf s'il ne travaille pas » a reçu deux réponses empilées, `¬N ∧ ¬T` puis `¬T ⇒ ¬N`. Les deux sont barrées. La même faute qu'à la question 3 : ne pas trancher.",
                    'remedy' => "Une question, une formule. C'est la troisième occurrence de cette erreur sur la même copie.",
                ],
                [
                    'title' => "L'épreuve porte sur Why3, pas seulement sur la théorie",
                    'chapter' => 'Intro',
                    'kind' => 'methode',
                    'severity' => 5,
                    'explanation' => "Répartition réelle du sujet de mai : exercice 1 sur la formalisation propositionnelle, exercice 2 sur le typage WhyML, exercice 3 sur les prédicats du premier ordre en Why3, exercice 4 sur les définitions inductives et les lemmes. Trois exercices sur quatre exigent d'écrire du code Why3 correct, sur feuille et sans machine.",
                    'remedy' => "S'entraîner à écrire du WhyML à la main. La syntaxe se retient par la pratique, pas par la lecture — et l'épreuve se compose sans machine pour vérifier.",
                ],
            ],
        ];

        foreach ($entries as $code => $list) {
            $subject = Subject::where('code', $code)->first();

            if (! $subject) {
                continue;
            }

            $paper = ExamPaper::where('subject_id', $subject->id)->first();

            foreach ($list as $i => $data) {
                $chapter = Chapter::where('subject_id', $subject->id)
                    ->where('code', $data['chapter'])->first();

                Gap::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $data['title']],
                    [
                        'chapter_id' => $chapter?->id,
                        'exam_paper_id' => $paper?->id,
                        'kind' => $data['kind'],
                        'evidence' => $data['evidence'] ?? null,
                        'explanation' => $data['explanation'],
                        'remedy' => $data['remedy'],
                        'severity' => $data['severity'],
                        'position' => 20 + $i,
                    ]
                );
            }
        }
    }

    /* ==================================================================== */

    /** ALO : la fiche qui traite la cause réelle du 0/20. */
    private function aloSchema(): void
    {
        $chapter = Chapter::whereHas('subject', fn ($q) => $q->where('code', 'ALO'))
            ->where('code', 'DP-Method')->first();

        if (! $chapter) {
            return;
        }

        Lesson::updateOrCreate(
            ['chapter_id' => $chapter->id, 'slug' => 'dessiner-le-diagramme'],
            [
                'title' => 'Dessiner le diagramme — la cause du 0/20',
                'est_minutes' => 20,
                'position' => 0,
                'intuition' => <<<'MD'
En haut de votre copie de janvier, en rouge :

> ⚠ **« Pour les 3 questions de modélisation il était demandé un schéma.
> Vous avez rendu du pseudo-code. Je n'ai donc rien à noter. »**

Quinze points sur vingt annulés d'un trait. Pas pour une erreur de fond : pour un
format.

Et le fond était bon. Vous aviez écrit, en conception 2, une `class Stratégie` avec
`Conduite Urbaine` et `Conduite Éco` — le patron Stratégie, correctement identifié.
En conception 3, une `class Status` avec états `expédié, validé, annulé, préparation`,
des « règles de transition centralisées », et des alertes vers Facturation et Service
Client quand le statut change — État et Observateur, tous les deux justes. Vous aviez
même noté `[Abstract]` et `[Communicate]` en marge.

**Vous connaissez les patrons.** Vous ne les avez pas dessinés.
MD,
                'formalism' => <<<'MD'
**Ce que le correcteur appelle un schéma**

Un diagramme de classes UML. Trois éléments, aucun de plus :

**1. Des boîtes** à trois compartiments :

```
┌─────────────────────┐
│      Commande       │   ← nom de la classe
├─────────────────────┤
│ - id : String       │   ← attributs
│ - adresse : String  │
├─────────────────────┤
│ + creer()           │   ← méthodes
│ + modifier()        │
└─────────────────────┘
```

**2. Des traits typés** entre les boîtes :

| Trait | Sens |
|---|---|
| `──────▷` triangle creux | héritage (« est un ») |
| `- - - -▷` pointillés, triangle creux | implémentation d'interface |
| `──────◇` losange creux | agrégation (les parties survivent) |
| `──────◆` losange plein | composition (les parties meurent avec le tout) |
| `────────` trait simple | association |
| `1`, `*`, `0..1` aux extrémités | multiplicités |

**3. Le nom du patron**, écrit à côté de la zone concernée :

```
        ◄──── Stratégie
```

**Les stéréotypes** se notent entre chevrons : `«interface»`, `«abstract»`.
Vos `[Abstract]` en crochets ne sont pas la notation du cours.
MD,
                'worked_example' => <<<'MD'
**Votre conception 3, redessinée.**

Ce que vous avez rendu :

```
Interface Traitement de Commandes
  ↳ class Commandes
        ID_unique
        adresse
        liste articles
        creer()
        modifier() si statut différent de expédié
     ↳ class Status
        status : expédié, validé, annulé, préparation
        modifier()
        si modifier() → alerte Facturation si status = validé
                      → alerte Service Client si status = livraison annulée
```

Ce qu'il fallait rendre :

```
   ┌──────────────────────┐              ┌─────────────────────┐
   │     «interface»      │              │    «interface»      │
   │    EtatCommande      │              │    Observateur      │
   ├──────────────────────┤              ├─────────────────────┤
   │ + traiter(c: Cmd)    │              │ + actualiser(c: Cmd)│
   └──────────△───────────┘              └──────────△──────────┘
              │                                     │
      ┌───────┼────────┬─────────┐        ┌─────────┼──────────┐
   ┌──┴───┐┌──┴───┐┌───┴───┐┌────┴───┐ ┌──┴────────┐┌──────────┴──┐
   │Prépa ││Validé││Expédié││ Annulé │ │Facturation││ServiceClient│
   └──────┘└──────┘└───────┘└────────┘ └───────────┘└─────────────┘
              ▲                                     ▲
              │ état                    observateurs│ *
              │                                     │
        ┌─────┴─────────────────────────────────────┴─────┐
        │                   Commande                      │
        ├─────────────────────────────────────────────────┤
        │ - id : String                                   │
        │ - adresse : String                              │
        │ - etat : EtatCommande                           │
        ├─────────────────────────────────────────────────┤
        │ + modifier()                                    │
        │ + attacher(o) + detacher(o) + notifier()        │
        └───────────────────◇─────────────────────────────┘
                            │ 1..*
                     ┌──────┴──────┐
                     │   Article   │
                     └─────────────┘

        ◄── État (les statuts de la commande)
        ◄── Observateur (alertes Facturation et Service Client)
        ◄── Composite ou agrégation simple pour les articles
```

Même contenu. Mais celui-ci se note.

**À la main, en trois heures**, pas besoin de perfection typographique : des
rectangles au stylo, des traits, les triangles et losanges tracés proprement, et
les noms de patrons encadrés. Comptez **quinze minutes de tracé** par exercice
sur les quarante-cinq allouées.
MD,
                'pitfalls' => <<<'MD'
- **L'indentation n'est pas une relation.** Décaler `class Status` sous `class Commandes`
  ne dit ni héritage, ni agrégation, ni association. Il faut un trait, et le bon.
- **`[Abstract]` en crochets** n'est pas la notation UML. C'est `«abstract»`,
  ou le nom de la classe en italique.
- **Décrire le patron en prose sous le schéma** sans l'écrire sur le schéma.
  L'énoncé exige l'identification **sur** le diagramme.
- **Oublier les multiplicités.** `1`, `*`, `1..*` aux extrémités : c'est ce qui
  distingue une agrégation d'une association simple.
- **Rendre un diagramme sans interface ni classe abstraite.** C'est un point entier
  du barème, et il s'obtient presque gratuitement.
MD,
                'examiner_expects' => <<<'MD'
Pour chacun des trois exercices de conception :

- [ ] **Des rectangles**, pas des lignes indentées.
- [ ] **Trois compartiments** par boîte : nom, attributs, méthodes.
- [ ] **Des traits typés** : triangle pour l'héritage, losange pour l'agrégation
      ou la composition.
- [ ] Au moins une **`«interface»`** ou une **classe abstraite**.
- [ ] Les **multiplicités** aux extrémités des relations.
- [ ] **Trois noms de patrons écrits sur le schéma**, encadrés ou fléchés.

Cette liste vaut 5 points par exercice, soit 15 sur 20. En janvier elle en a rapporté
zéro, alors que le raisonnement sous-jacent était juste.
MD,
                'source_refs' => [
                    ['label' => 'COPIE_ALO_ZAMON.pdf — annotation p. 2'],
                    ['label' => 'ALO_Examen_2025_01.pdf — énoncé'],
                ],
            ]
        );

        $cards = [
            [
                'kind' => 'piege',
                'front' => "Pourquoi avez-vous eu 0/20 à ALO en janvier ?",
                'back' => "**Parce que les trois questions de conception ont été rendues en pseudo-code au lieu d'un schéma.**\n\n« Il était demandé un schéma. Vous avez rendu du pseudo-code. Je n'ai donc rien à noter. »\n\n15 points sur 20 annulés. Les patrons choisis étaient pourtant les bons.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'definition',
                'front' => 'Les trois compartiments d’une boîte de classe UML ?',
                'back' => "1. **Le nom** de la classe\n2. **Les attributs** (`- nom : Type`)\n3. **Les méthodes** (`+ methode()`)\n\n`-` privé, `+` public. Stéréotypes entre chevrons : `«interface»`, `«abstract»` — pas entre crochets.",
                'difficulty' => 4,
            ],
            [
                'kind' => 'formule',
                'front' => 'Les cinq types de traits UML et leur sens ?',
                'back' => "`──▷` triangle creux → **héritage**\n`- -▷` pointillés + triangle → **implémentation d'interface**\n`──◇` losange creux → **agrégation**\n`──◆` losange plein → **composition**\n`────` trait simple → **association**\n\n+ les multiplicités `1`, `*`, `1..*` aux extrémités.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'methode',
                'front' => "Combien de temps consacrer au tracé du diagramme par exercice de conception ?",
                'back' => "**Environ 15 minutes** sur les 45 allouées.\n\nDes rectangles au stylo, des traits typés, les noms de patrons encadrés. La perfection typographique n'est pas demandée — la présence des boîtes et des traits, oui.",
            ],
            [
                'kind' => 'piege',
                'front' => "Vous décrivez le patron Stratégie en prose sous votre schéma. Cela suffit-il ?",
                'back' => "**Non.** L'énoncé exige l'identification **sur le schéma** : « si vous ne le faites pas il n'y a pas de point attribué ».\n\nÉcrivez le nom à côté de la zone concernée, encadré ou fléché.",
                'difficulty' => 4,
            ],
        ];

        foreach ($cards as $i => $card) {
            Flashcard::updateOrCreate(
                ['chapter_id' => $chapter->id, 'front' => $card['front']],
                $card + ['position' => 50 + $i]
            );
        }

        Exercise::updateOrCreate(
            ['subject_id' => $chapter->subject_id, 'title' => 'Redessiner vos trois conceptions de janvier'],
            [
                'chapter_id' => $chapter->id,
                'origin' => 'annale',
                'est_minutes' => 60,
                'difficulty' => 3,
                'position' => 0,
                'statement' => <<<'MD'
Voici, résumé, ce que vous avez rendu en janvier aux trois questions de conception.
Chacune a été notée zéro pour cause de format.

**Conception 1 — gestion des dossiers patients d'un hôpital**
Vous aviez posé : `Interface Gestion_Patient_Hopital`, `class Dossiers_Patient`
(nom, prénom, date_naissance, num_secu, creer(), modifier(), consulter(),
rapport_generation()), `class Consultations` (date, médecin, symptômes, diagnostic),
`class Prescriptions` (nom_medicament, dosage, fréquence, durée),
`class Status_Etats` (actif, achevé, en cours de validation),
`class Analyses_Medicales [Abstract]` (Sang → allergènes, globules ; Radio → Image),
`class Rapport` (generer_pour_med, generer_pour_admin).

**Conception 2 — simulateur de véhicules autonomes**
`Interface Simulateur`, `Vehicule` (type, année, couleur, immatriculation),
`class Scénario` (type_scenario : ville, autoroute, campagne),
`class Comportement`, `class Evenements_Exterieur` (obstacles, dépassement),
`class Stratégie [Abstract]` (Conduite Urbaine : vitesse 60 km/h, attention élevée ;
Conduite Éco : attention modérée), `class Type_Terrain` (asphalte, béton, gravier).

**Conception 3 — traitement de commandes**
`Interface Traitement de Commandes`, `class Commandes` (ID_unique, adresse,
liste articles, creer(), modifier() si statut ≠ expédié),
`class Status` (expédié, validé, annulé, préparation) avec « règles de transition
centralisées », et : si modifier() → alerte Facturation si statut = validé,
→ alerte Service Client si statut = livraison annulée, `class Articles` (ID, nom).

---

**Votre travail : redessinez les trois, en diagrammes de classes.**

Pour chacun :
1. Des **boîtes à trois compartiments**.
2. Des **traits typés** avec leurs **multiplicités**.
3. Au moins une **`«interface»`** ou classe abstraite.
4. **Trois patrons nommés sur le schéma.**

Le contenu est déjà là — vous ne refaites pas la réflexion, vous la mettez au format
qui se note.
MD,
                'hint' => "Les patrons sont déjà présents dans vos réponses, il faut les reconnaître et les nommer. Conception 2 : cherchez le mot « Stratégie » que vous aviez écrit. Conception 3 : « Status » avec transitions, et les alertes.",
                'method' => <<<'MD'
Pour chaque conception, procédez dans cet ordre :

1. **Lister les classes** de votre réponse d'origine — elles sont bonnes, gardez-les.
2. **Tracer une boîte** par classe, trois compartiments.
3. **Relier** : quelle classe hérite de quelle autre ? Laquelle en contient d'autres ?
   Choisir le bon trait, poser les multiplicités.
4. **Identifier les trois patrons** parmi ce que vous aviez déjà écrit.
5. **Écrire leur nom** à côté de la zone concernée, encadré.
MD,
                'solution' => <<<'MD'
**Conception 1 — dossiers patients.** Patrons attendus :

- **Composite** — un `Dossier` regroupe des `Consultations`, chacune regroupant des
  `Prescriptions`. Structure emboîtée manipulable uniformément.
  *(Votre réponse : « admet la création de 1 à n Consultations qui chacune admet la
  création de 1 à n prescriptions ». C'était exactement ça.)*
- **État** — `Status_Etats` : actif, achevé, en cours de validation, « régissant les
  actions de modification et de génération de rapports ».
  *(Votre réponse le disait mot pour mot.)*
- **Décorateur** ou **Composite** pour les `Analyses_Medicales` abstraites
  (Sang, Radio), selon la façon dont vous les reliez.

Diagramme : `«interface» ElementDossier` implémentée par `Consultation` et
`Prescription` ; `Dossier ◇──1..* Consultation ◇──1..* Prescription` ;
`«interface» EtatDossier` avec `Actif`, `Acheve`, `EnValidation`, et
`Dossier ──▷ etat : EtatDossier` ; `«abstract» AnalyseMedicale` avec `Sang` et `Radio`.

**Conception 2 — simulateur.** Patrons attendus :

- **Stratégie** — vous l'aviez nommée : `class Stratégie` avec `Conduite Urbaine`
  et `Conduite Éco`. Il suffisait de l'écrire sur un schéma.
- **État** — `Comportement` qui « évolue au fur et à mesure des Évènements extérieurs ».
- **Observateur** — les `Evenements_Exterieur` qui notifient les véhicules
  (`alerter_autre_vehicule()` que vous aviez écrit dans `class Comportement`).

Diagramme : `«interface» StrategieConduite` avec `ConduiteUrbaine` et `ConduiteEco` ;
`Vehicule ──▷ strategie` ; `«interface» EtatComportement` ; `«interface» Observateur`
implémentée par `Vehicule`, et `EvenementExterieur ◇──* Observateur`.

**Conception 3 — commandes.** Patrons attendus :

- **État** — `class Status` : expédié, validé, annulé, préparation, avec « règles de
  transition centralisées ». C'est la définition même du patron.
- **Observateur** — les alertes vers Facturation et Service Client au changement
  de statut. Vous l'aviez décrit précisément.
- **Composite** ou agrégation — `Commande ◇──1..* Article`.

Le diagramme complet figure dans la fiche « Dessiner le diagramme ».

---

**Le constat.** Sur les trois conceptions, les neuf patrons attendus étaient
identifiables dans vos réponses d'origine. Aucun n'était dessiné, aucun n'était
nommé. La note aurait pu être proche de 15 sur 20 sur cette partie.
MD,
                'rubric' => [
                    ['label' => 'Conception 1 : diagramme en boîtes à trois compartiments', 'points' => 1],
                    ['label' => 'Conception 1 : trois patrons nommés sur le schéma', 'points' => 2],
                    ['label' => 'Conception 2 : diagramme en boîtes à trois compartiments', 'points' => 1],
                    ['label' => 'Conception 2 : Stratégie nommée sur le schéma', 'points' => 2],
                    ['label' => 'Conception 3 : diagramme en boîtes à trois compartiments', 'points' => 1],
                    ['label' => 'Conception 3 : État et Observateur nommés sur le schéma', 'points' => 2],
                    ['label' => 'Les traits sont typés (triangle, losange) et portent des multiplicités', 'points' => 2],
                    ['label' => 'Chaque diagramme comporte une «interface» ou une classe abstraite', 'points' => 1],
                ],
            ]
        );
    }

    /* ==================================================================== */

    /** SPP : la fiche Why3, qui portait trois exercices sur quatre. */
    private function sppWhy3(): void
    {
        $subject = Subject::where('code', 'SPP')->first();
        $chapter = Chapter::where('subject_id', $subject?->id)->where('code', 'Contrats')->first();

        if (! $chapter) {
            return;
        }

        Lesson::updateOrCreate(
            ['chapter_id' => $chapter->id, 'slug' => 'ecrire-du-why3-a-la-main'],
            [
                'title' => 'Écrire du Why3 à la main',
                'est_minutes' => 25,
                'position' => 0,
                'intuition' => <<<'MD'
Le sujet de mai comptait quatre exercices. Le premier portait sur la formalisation
propositionnelle. **Les trois autres demandaient d'écrire du Why3 correct, sur feuille.**

Résultat : exercice 2 à zéro (« erreur de type »), exercice 3 à zéro
(« voir énoncé », « incomplet »), exercice 4 à zéro (« cours pas connu »).

Ce n'est pas une question de théorie. C'est une syntaxe qu'on ne retient qu'en
l'écrivant, et l'épreuve se compose **sans machine** pour vérifier.
MD,
                'formalism' => <<<'MD'
**Les trois natures d'objet, et leurs types**

| Mot-clé | Rend | Sert à |
|---|---|---|
| `predicate` | une **formule logique** | définir une propriété : `predicate pair (n:int) = mod n 2 = 0` |
| `function` | une **valeur** en logique | définir un terme : `function double (n:int) : int = 2 * n` |
| `let` / `let rec` | une **valeur** en programme | écrire du code exécutable |
| `lemma` | une formule **à démontrer** | énoncer un résultat intermédiaire |
| `axiom` | une formule **admise** | poser une hypothèse |

**La faute de l'exercice 2.**

Vous aviez écrit :
```whyml
let P1 (a:int, b:int) : int = if a then b
```

Trois erreurs en une ligne :
1. La condition d'un `if` doit être de type **`bool`**, pas `int`.
2. Le `if` n'a pas de branche `else`, donc il ne peut pas rendre un `int`.
3. Une **implication logique** `a → b` ne se code pas par un `if`. C'est une formule,
   pas un calcul.

L'énoncé demandait de traduire « a implique b ». La réponse attendue est une formule :
```whyml
predicate p1 (a b: bool) = a -> b
```
ou, si l'on reste au niveau logique :
```whyml
lemma p1 : forall a b: bool. a -> b -> b
```

**La syntaxe des quantificateurs**

```whyml
forall x: int. P x                    (* le point est obligatoire *)
exists x: int. P x
forall x y: int. P x y                (* variables groupées *)
forall l: list 'a. length l >= 0      (* type paramétré *)
```

Les connecteurs : `/\` conjonction, `\/` disjonction, `->` implication,
`<->` équivalence, `not` négation.

**La règle absolue : un `lemma` est toujours clos.** Toute variable libre doit être
quantifiée. C'est ce que signifiait le « + forall » du correcteur.
MD,
                'worked_example' => <<<'MD'
**Exercice 3 refait — les relations binaires.**

L'énoncé demandait de définir la transitivité, l'asymétrie, puis un lemme les reliant.

Vous aviez écrit :
```whyml
predicate p x y
predicate p y z
predicate transitif = exist x. p x z          (* « voir énoncé », 0 *)
predicate asym = not(exist x. p y x)          (* « incomplet », 0 »*)
```

Les variables ne sont pas déclarées, ni toutes quantifiées, et `transitif` ne
mentionne que deux des trois variables nécessaires.

**Ce qu'il fallait écrire :**

```whyml
type t

predicate p t t                    (* relation binaire sur t, déclarée *)

predicate transitif =
  forall x y z: t. p x y -> p y z -> p x z

predicate asymetrique =
  forall x y: t. p x y -> not (p y x)

predicate irreflexif =
  forall x: t. not (p x x)

lemma asym_implique_irrefl :
  asymetrique -> irreflexif
```

Trois observations :

1. **`predicate p t t`** déclare la relation avec ses **types d'arguments**,
   sans la définir.
2. Chaque définition **quantifie toutes ses variables**. Aucune n'est libre.
3. La transitivité s'écrit avec **trois** variables et **deux** hypothèses,
   enchaînées par `->` — en Why3 on ne met pas de `/\` entre les prémisses,
   on les curryfie.

**Exercice 4 refait — les listes.**

Vous aviez écrit :
```whyml
length(Nil) = Nil = 0                            (* faux *)
length(Cons(a,l)) = Cons'a list'a -> Cons'b(list'b)   (* « cours pas connu » *)
```

La première ligne confond la **valeur** `0` et le **constructeur** `Nil`.
La seconde écrit des **types** au lieu d'une **équation**.

**Ce qu'il fallait écrire :**

```whyml
type list 'a = Nil | Cons 'a (list 'a)

function length (l: list 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + length r
  end

lemma length_positive :
  forall l: list 'a. 0 <= length l

lemma length_nil :
  forall l: list 'a. length l = 0 <-> l = Nil
```

Une définition inductive donne **une valeur par constructeur**. Ici : `0` pour `Nil`,
`1 + length r` pour `Cons`.

**Et le piège du `<` :** vous aviez écrit `0 < len(l)`. Le correcteur a corrigé en `≤`.
La liste vide a une longueur de zéro : l'inégalité stricte est fausse. **Toujours
tester le cas limite avant de choisir entre `<` et `<=`.**
MD,
                'pitfalls' => <<<'MD'
- **`if` sur un entier.** La condition d'un `if` est un `bool`. Et un `if` sans `else`
  ne rend pas de valeur.
- **Coder une implication logique par un `if`.** `a -> b` est une **formule** :
  elle vit dans un `predicate` ou un `lemma`, pas dans du code.
- **Variable libre dans un `lemma`.** C'est le « + forall » du correcteur : sans
  quantificateur, l'énoncé n'a pas de valeur de vérité.
- **Oublier le point après le quantificateur.** `forall x: int. P x` — le point est
  syntaxiquement obligatoire.
- **Écrire des types là où on attend des valeurs.** `length (Cons x r) = 1 + length r`,
  pas une expression de types.
- **`<` au lieu de `<=`.** Vérifiez systématiquement le cas de base : liste vide,
  tableau vide, n = 0. C'est presque toujours là que l'inégalité stricte casse.
- **Filtrage non exhaustif** dans un `match` : un cas par constructeur, et `end`
  pour fermer.
MD,
                'examiner_expects' => <<<'MD'
- [ ] Le bon mot-clé : `predicate` pour une formule, `function` pour une valeur
      logique, `let` pour du code.
- [ ] **Tous les paramètres typés** dans la déclaration.
- [ ] **Aucune variable libre** : tout `lemma` est clos par des `forall`.
- [ ] Le **point** après chaque quantificateur.
- [ ] Un **`match` exhaustif** terminé par `end`.
- [ ] L'inégalité vérifiée **sur le cas de base** avant de choisir `<` ou `<=`.
MD,
                'source_refs' => [
                    ['label' => 'COPIE_SPP_ZAMON_a.pdf — exercices 2, 3 et 4'],
                    ['label' => 'devoir1spp.mlw et devoir2spp.mlw'],
                ],
            ]
        );

        $cards = [
            [
                'kind' => 'piege',
                'front' => "`let P1 (a:int, b:int) : int = if a then b` — combien d'erreurs ?",
                'back' => "**Trois.**\n1. La condition d'un `if` doit être un **`bool`**, pas un `int`.\n2. Pas de branche `else` : le `if` ne rend rien.\n3. Une **implication logique** ne se code pas par un `if` — c'est une formule, pas un calcul.\n\n*Exercice 2 de mai : « erreur de type », 0 point.*",
                'difficulty' => 5,
            ],
            [
                'kind' => 'formule',
                'front' => 'Why3 : définir la transitivité d’une relation binaire `p` ?',
                'back' => "```whyml\npredicate transitif =\n  forall x y z: t. p x y -> p y z -> p x z\n```\n\n**Trois** variables, **deux** hypothèses curryfiées par `->`. Toutes les variables quantifiées.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'formule',
                'front' => 'Why3 : définir l’asymétrie et l’irréflexivité ?',
                'back' => "```whyml\npredicate asymetrique =\n  forall x y: t. p x y -> not (p y x)\n\npredicate irreflexif =\n  forall x: t. not (p x x)\n```\n\nL'asymétrie **implique** l'irréflexivité.",
                'difficulty' => 5,
            ],
            [
                'kind' => 'formule',
                'front' => 'Why3 : définir `length` sur les listes ?',
                'back' => "```whyml\nfunction length (l: list 'a) : int =\n  match l with\n  | Nil      -> 0\n  | Cons _ r -> 1 + length r\n  end\n```\n\nUne **valeur** par constructeur. Pas un type.\n\n*Exercice 4 de mai : « cours pas connu », 0 point.*",
                'difficulty' => 5,
            ],
            [
                'kind' => 'piege',
                'front' => "`lemma l2 : 0 < len(l)` — deux fautes. Lesquelles ?",
                'back' => "1. **`l` est libre** : le lemme n'est pas clos. Le correcteur a écrit « **+ forall** ».\n2. **`<` au lieu de `<=`** : la liste vide a une longueur de zéro.\n\nCorrect : `lemma : forall l: list 'a. 0 <= length l`",
                'difficulty' => 5,
            ],
            [
                'kind' => 'definition',
                'front' => 'Why3 : `predicate`, `function`, `let` — quelle différence ?',
                'back' => "**`predicate`** — rend une **formule logique**.\n**`function`** — rend une **valeur**, au niveau logique.\n**`let` / `let rec`** — rend une valeur, en **code exécutable**.\n\n+ `lemma` (à démontrer) et `axiom` (admis).",
                'difficulty' => 4,
            ],
            [
                'kind' => 'formule',
                'front' => 'Why3 : syntaxe des quantificateurs et des connecteurs ?',
                'back' => "```whyml\nforall x: int. P x        (* point obligatoire *)\nexists x: int. P x\nforall x y: int. P x y    (* groupées *)\n```\n\n`/\\` et · `\\/` ou · `->` implique · `<->` équivaut · `not` non",
                'difficulty' => 4,
            ],
            [
                'kind' => 'methode',
                'front' => "Vous hésitez entre `<` et `<=` dans un lemme. Comment trancher ?",
                'back' => "**Testez le cas de base** : liste vide, tableau vide, n = 0.\n\n`length Nil = 0`, donc `0 < length l` est **faux** et `0 <= length l` est vrai.\n\nC'est exactement la correction portée sur votre copie.",
                'difficulty' => 4,
            ],
        ];

        foreach ($cards as $i => $card) {
            Flashcard::updateOrCreate(
                ['chapter_id' => $chapter->id, 'front' => $card['front']],
                $card + ['position' => 50 + $i]
            );
        }

        Exercise::updateOrCreate(
            ['subject_id' => $subject->id, 'title' => 'Exercices 2, 3 et 4 de mai — à refaire en Why3'],
            [
                'chapter_id' => $chapter->id,
                'origin' => 'annale',
                'est_minutes' => 45,
                'difficulty' => 4,
                'position' => 0,
                'statement' => <<<'MD'
Les trois exercices Why3 de l'épreuve du 21 mai, à refaire. Ils avaient tous été
notés zéro.

**Exercice 2.** Soit P1 « a implique b » et P2 « soit non a, soit a et b ».
Écrivez ces deux propositions en Why3, puis énoncez un lemme affirmant qu'elles
sont équivalentes. *(4 pts)*

**Exercice 3.** Soit `t` un type et `p` une relation binaire sur `t`.
1. Définissez le prédicat `transitif`. *(2 pts)*
2. Définissez le prédicat `asymetrique`. *(2 pts)*
3. Énoncez un lemme affirmant que toute relation asymétrique est irréflexive. *(2 pts)*

**Exercice 4.** Sur le type `list 'a = Nil | Cons 'a (list 'a)`.
1. Donnez la définition inductive de `length`. *(2 pts)*
2. Énoncez un lemme affirmant que la longueur est toujours positive ou nulle. *(2 pts)*
3. Énoncez un lemme caractérisant les listes de longueur nulle. *(2 pts)*

**Consignes.** Tous les paramètres typés. Aucune variable libre dans un lemme.
Le point après chaque quantificateur. Vérifiez vos inégalités sur le cas de base.
MD,
                'hint' => "Pour l'exercice 2, souvenez-vous qu'une implication logique est une **formule**, pas un `if`. Pour l'exercice 4 question 2 : quelle est la longueur de `Nil` ? Cela décide entre `<` et `<=`.",
                'method' => <<<'MD'
1. Choisissez le bon mot-clé : `predicate` pour une formule, `function` pour une
   valeur, `lemma` pour un énoncé à démontrer.
2. Déclarez les types de tous les paramètres.
3. Quantifiez **toutes** les variables. Relisez : reste-t-il une variable libre ?
4. Pour les définitions inductives : un cas par constructeur, une **valeur** à droite.
5. Testez chaque inégalité sur le cas de base avant de rendre.
MD,
                'solution' => <<<'MD'
**Exercice 2.**

```whyml
predicate p1 (a b: bool) = a -> b

predicate p2 (a b: bool) = (not a) \/ (a /\ b)

lemma equivalence :
  forall a b: bool. p1 a b <-> p2 a b
```

*Pourquoi elles sont équivalentes :* `a -> b` équivaut à `not a \/ b`.
Et `not a \/ (a /\ b)` se distribue en `(not a \/ a) /\ (not a \/ b)`,
soit `vrai /\ (not a \/ b)`, donc `not a \/ b`. Les deux coïncident.

*Votre erreur de mai :* avoir écrit `let P1 (a:int, b:int) : int = if a then b`.
Une implication est une formule, pas un programme. Et la condition d'un `if` est
un booléen.

**Exercice 3.**

```whyml
type t

predicate p t t

predicate transitif =
  forall x y z: t. p x y -> p y z -> p x z

predicate asymetrique =
  forall x y: t. p x y -> not (p y x)

predicate irreflexif =
  forall x: t. not (p x x)

lemma asym_implique_irrefl :
  asymetrique -> irreflexif
```

*Démonstration du lemme :* supposons `asymetrique`. Soit `x` quelconque et
supposons `p x x`. En appliquant l'asymétrie avec `y = x`, on obtient `not (p x x)`,
d'où une contradiction. Donc `not (p x x)` pour tout `x`, c'est-à-dire `irreflexif`. ∎

*Vos erreurs de mai :* `predicate transitif = exist x. p x z` ne quantifiait qu'une
variable sur trois, utilisait `exists` au lieu de `forall`, et laissait `z` libre.

**Exercice 4.**

```whyml
type list 'a = Nil | Cons 'a (list 'a)

function length (l: list 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + length r
  end

lemma length_positive :
  forall l: list 'a. 0 <= length l

lemma length_nulle :
  forall l: list 'a. length l = 0 <-> l = Nil
```

*Sur le `<=` :* `length Nil = 0`, donc l'inégalité **stricte** serait fausse.
C'est la correction portée en rouge sur votre copie.

*Sur le `forall` :* sans lui, `l` est libre et le lemme n'a pas de valeur de vérité.
C'est le « + forall » du correcteur.

*Démonstration de `length_positive`, par induction structurelle sur `l` :*
- **Cas `Nil`** : `length Nil = 0`, et `0 <= 0`. ✓
- **Cas `Cons x r`** : par hypothèse d'induction `0 <= length r`,
  donc `0 <= 1 + length r = length (Cons x r)`. ✓ ∎
MD,
                'rubric' => [
                    ['label' => 'Ex 2 : `p1` écrit comme une formule `a -> b`, pas comme un `if`', 'points' => 2],
                    ['label' => 'Ex 2 : lemme d’équivalence clos par un `forall`', 'points' => 2],
                    ['label' => 'Ex 3 : `transitif` avec trois variables et deux hypothèses', 'points' => 2],
                    ['label' => 'Ex 3 : `asymetrique` correctement quantifié', 'points' => 2],
                    ['label' => 'Ex 3 : lemme asymétrie ⇒ irréflexivité', 'points' => 2],
                    ['label' => 'Ex 4 : `length` définie par un cas par constructeur, valeurs à droite', 'points' => 2],
                    ['label' => 'Ex 4 : lemme de positivité avec `<=` et non `<`', 'points' => 2],
                    ['label' => 'Ex 4 : lemme caractérisant les listes de longueur nulle', 'points' => 1],
                    ['label' => 'Aucune variable libre dans aucun lemme', 'points' => 2],
                    ['label' => 'Tous les paramètres typés dans les déclarations', 'points' => 1],
                ],
            ]
        );
    }

    /* ==================================================================== */

    private function marquerPagesAnalysees(): void
    {
        // ALO : 5 pages sur 8 relues (1 à 5). SPP : 3 sur 4.
        ExamPaper::whereHas('subject', fn ($q) => $q->where('code', 'ALO'))
            ->update(['analysed_pages' => 5]);

        ExamPaper::whereHas('subject', fn ($q) => $q->where('code', 'SPP'))
            ->update(['analysed_pages' => 3]);
    }
}