<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Seconds examens blancs — le passage à J−2 que le planning réserve.
 *
 * Ils diffèrent des premiers sur un point : chaque sujet est construit autour des
 * erreurs réellement commises en session 1, telles que la relecture des copies les
 * a établies. Rendre un diagramme et non du pseudo-code, écrire du Why3 clos,
 * reconnaître un problème de programmation dynamique, ne jamais laisser une
 * complexité vide, formaliser « en général » par un défaut.
 */
class SecondsExamensBlancsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->examens() as $data) {
            $subject = Subject::where('code', $data['code'])->first();

            if (! $subject) {
                continue;
            }

            $examen = MockExam::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'subject_id' => $subject->id,
                    'title' => $data['title'],
                    'instructions' => $data['instructions'],
                    'duration_min' => $data['duration'],
                    'total_points' => 20,
                    'origin' => 'genere',
                    'year' => 2026,
                    'position' => 2,
                ]
            );

            foreach ($data['questions'] as $i => $q) {
                $chapter = isset($q['chapter'])
                    ? Chapter::where('subject_id', $subject->id)->where('code', $q['chapter'])->first()
                    : null;

                unset($q['chapter']);

                MockExamQuestion::updateOrCreate(
                    ['mock_exam_id' => $examen->id, 'number' => $q['number']],
                    $q + ['chapter_id' => $chapter?->id, 'position' => $i + 1]
                );
            }
        }
    }

    /* ==================================================================== */

    private function examens(): array
    {
        return [
            $this->alo(),
            $this->spp(),
            $this->mia(),
            $this->agc(),
            $this->ep(),
        ];
    }

    /* ==================== ALO ==================== */

    private function alo(): array
    {
        return [
            'code' => 'ALO',
            'slug' => 'alo-blanc-2-conception-j-moins-2',
            'title' => 'ALO blanc n°2 — trois conceptions, passage J−2',
            'duration' => 180,
            'instructions' => <<<'MD'
Durée : **3 heures**. Épreuve du 24 août, 20 h – 23 h.

**La règle qui a coûté 15 points en janvier.** Chaque question de conception attend
un **diagramme de classes** : des rectangles à trois compartiments, des traits typés,
des multiplicités. Un plan indenté n'est pas un schéma et ne se note pas.

Puisque vous composez ici au clavier, décrivez votre diagramme **boîte par boîte et
trait par trait** — c'est l'équivalent textuel du tracé. Le jour J, vous dessinerez.

**Et surtout : nommez les trois patrons.** L'énoncé réel précise « si vous ne le
faites pas il n'y a pas de point attribué ». Singleton et Builder sont hors scope.

Répartition conseillée : 20 min de QCM, 45 min par conception, 20 min de relecture
consacrée à vérifier que **chaque patron porte son nom**.
MD,
            'questions' => [
                [
                    'number' => 'QCM',
                    'chapter' => 'C1-Objet',
                    'points' => 5,
                    'statement' => <<<'MD'
**Barème : bonne réponse +0,5 · abstention 0 · mauvaise réponse −0,25.**
Ne répondez que si vous dépassez une chance sur trois.

**1.** Une classe abstraite peut-elle être instanciée directement ?
a. Oui · b. Non · c. Seulement si elle n'a aucune méthode abstraite

**2.** Une relation de composition implique :
a. Les parties survivent au tout · b. Les parties meurent avec le tout ·
c. Aucune dépendance de cycle de vie

**3.** Un objet doit changer de comportement au fil de sa vie, seul. Quel patron ?
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie

**4.** On veut ajouter des options cumulables à un objet sans modifier sa classe.
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie

**5.** En UML, un losange **plein** à l'extrémité d'un trait signifie :
a. Agrégation · b. Composition · c. Héritage · d. Implémentation

**6.** `interface I {} class A implements I {} class B extends A {}`.
`I x = new B();` compile-t-il ?
a. Oui · b. Non, B n'implémente pas I · c. Non, il faut un transtypage

**7.** Le patron Visiteur permet :
a. D'ajouter un type d'élément sans modifier les traitements ·
b. D'ajouter un traitement sans modifier les types d'éléments ·
c. Les deux

**8.** Dans le patron Observateur, quelles méthodes porte le **sujet** ? *(3 réponses)*
a. attacher() · b. actualiser() · c. detacher() · d. notifier()

**9.** Une méthode `static` :
a. Ne peut pas accéder aux attributs d'instance · b. Est forcément publique ·
c. Doit être redéfinie dans les sous-classes

**10.** Quel patron modélise « un dossier contient des sous-dossiers et des fichiers,
et l'on calcule la taille de la même façon pour les deux » ?
a. Composite · b. Décorateur · c. État · e. Observateur · g. Stratégie
MD,
                    'solution' => <<<'MD'
**1 : b** — une classe abstraite ne s'instancie jamais, même sans méthode abstraite.
**2 : b** — composition : les parties meurent avec le tout. L'agrégation, non.
**3 : c** — État : la transition se fait d'elle-même.
**4 : b** — Décorateur : enveloppe un objet, empilable.
**5 : b** — losange plein = composition ; losange creux = agrégation.
**6 : a** — B hérite de A qui implémente I : l'interface est héritée.
**7 : b** — le Visiteur externalise les traitements. Ajouter un **type** oblige au
contraire à modifier tous les visiteurs.
**8 : a, c, d** — `actualiser()` appartient à l'observateur, pas au sujet.
**9 : a** — une méthode statique n'a pas de `this`.
**10 : a** — Composite, structure récursive traitée uniformément.
MD,
                    'rubric' => [
                        ['label' => '1 : b', 'points' => 0.5], ['label' => '2 : b', 'points' => 0.5],
                        ['label' => '3 : c', 'points' => 0.5], ['label' => '4 : b', 'points' => 0.5],
                        ['label' => '5 : b', 'points' => 0.5], ['label' => '6 : a', 'points' => 0.5],
                        ['label' => '7 : b', 'points' => 0.5], ['label' => '8 : a, c, d — les trois', 'points' => 0.5],
                        ['label' => '9 : a', 'points' => 0.5], ['label' => '10 : a', 'points' => 0.5],
                    ],
                ],
                [
                    'number' => 'Conception 1 — la médiathèque numérique',
                    'chapter' => 'DP-Method',
                    'points' => 5,
                    'statement' => <<<'MD'
Vous devez concevoir un logiciel de médiathèque numérique. On y trouve des
**films**, des **albums**, des **podcasts**, des **playlists** et des **abonnés**.

Modélisez, puis répondez aux trois points d'attention.

**#1** — Une playlist contient des médias, mais aussi d'autres playlists. On doit
pouvoir calculer la durée totale d'une playlist comme celle d'un média isolé.

**#2** — Un abonnement traverse plusieurs situations : *essai gratuit*, *actif*,
*suspendu*, *résilié*. Ce qui est autorisé dépend de la situation du moment, et le
passage de l'une à l'autre survient au fil du temps.

**#3** — À l'inscription, l'abonné choisit une politique de recommandation :
*par popularité*, *par similarité*, ou *aléatoire*. Elle est fixée au départ et
détermine la liste proposée.

**Décrivez votre diagramme** : chaque boîte avec ses trois compartiments, chaque
trait avec son type et ses multiplicités. **Nommez les trois patrons.**
MD,
                    'solution' => <<<'MD'
**Notions objet (1 pt).** `«interface» Media` implémentée par `Film`, `Album`,
`Podcast`. `Abonne` est une classe distincte.

**Patron 1 — Composite (1 pt).** « Une playlist contient des médias et d'autres
playlists, durée calculée de la même façon » : structure récursive.

```
        «interface» ElementMediatheque
            + duree() : int
                   △
        ┌──────────┴──────────┐
   «abstract» Media       Playlist ◇──1..* ElementMediatheque
   + duree()              + duree()  (somme des enfants)
        △
  ┌─────┼─────┐
Film  Album  Podcast
```

**Patron 2 — État (1 pt).** « Ce qui est autorisé dépend de la situation, transition
au fil du temps » :

```
        «interface» EtatAbonnement
            + peutLire() : bool
            + facturer(a: Abonne)
                   △
   ┌────────┬──────┴────┬──────────┐
EssaiGratuit  Actif  Suspendu  Resilie

Abonne ──▷ etat : EtatAbonnement    (champ mutable, modifié par les états)
```

**Patron 3 — Stratégie (1 pt).** « L'abonné choisit une politique fixée au départ » :

```
        «interface» StrategieRecommandation
            + recommander(a: Abonne) : List<Media>
                   △
   ┌───────────────┼───────────────┐
ParPopularite  ParSimilarite   Aleatoire

Abonne ──▷ strategie : StrategieRecommandation   (final, reçu au constructeur)
```

**Cohérence (1 pt).** La distinction État / Stratégie est le cœur du sujet :
l'état de l'abonnement évolue seul, la politique de recommandation est choisie
de l'extérieur et ne change pas seule.
MD,
                    'rubric' => [
                        ['label' => 'Une «interface» ou classe abstraite, et une relation d’héritage', 'points' => 1],
                        ['label' => 'Composite nommé, avec agrégation 1..* vers l’interface commune', 'points' => 1],
                        ['label' => 'État nommé, avec champ mutable sur le contexte', 'points' => 1],
                        ['label' => 'Stratégie nommée, reçue de l’extérieur (et non confondue avec État)', 'points' => 1],
                        ['label' => 'Diagramme décrit en boîtes et traits typés, pas en plan indenté', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Conception 2 — la centrale de réservation',
                    'chapter' => 'DP-Method',
                    'points' => 5,
                    'statement' => <<<'MD'
Logiciel de réservation pour un réseau d'hôtels : **chambres**, **hôtels**,
**réservations**, **clients**, **services annexes**.

**#1** — Une réservation de base peut recevoir des services : *petit-déjeuner*,
*navette aéroport*, *lit supplémentaire*. Chacun s'ajoute au prix et au récapitulatif,
et plusieurs peuvent se cumuler sur la même réservation.

**#2** — Le service comptable, le service ménage et le client doivent tous être
prévenus dès qu'une réservation est confirmée ou annulée.

**#3** — Le logiciel doit produire plusieurs états sur l'ensemble du parc :
un état d'occupation, un état de maintenance, un état de rentabilité. Ces traitements
ne doivent pas alourdir les classes `Hotel` et `Chambre`, et d'autres viendront.

**Décrivez votre diagramme** et **nommez les trois patrons**.
MD,
                    'solution' => <<<'MD'
**Notions objet (1 pt).** `«interface» ElementParc` implémentée par `Hotel` et
`Chambre` ; `«abstract» Personne` dont héritent `Client` et `Employe`.

**Patron 1 — Décorateur (1 pt).** « Des services qui s'ajoutent au prix, cumulables » :

```
        «interface» Reservation
            + prix() : double
            + recapitulatif() : String
                   △
        ┌──────────┴──────────────┐
 ReservationSimple      «abstract» DecorateurReservation
                            ◇──1 Reservation        ← un seul objet enveloppé
                                   △
                    ┌──────────────┼──────────────┐
             AvecPetitDejeuner  AvecNavette  AvecLitSupp
```

*Piège écarté :* ce n'est pas Composite — on enveloppe **une** réservation, pas un groupe.

**Patron 2 — Observateur (1 pt).**

```
   Reservation ◇──* «interface» Observateur
   + attacher(o)          + actualiser(r: Reservation)
   + detacher(o)                 △
   + notifier()          ┌───────┼────────┐
                    Comptabilite  Menage  Client
```

`confirmer()` et `annuler()` appellent `notifier()`.

**Patron 3 — Visiteur (1 pt).** « Des traitements qui ne doivent pas alourdir les
classes, et d'autres viendront » — la signature exacte du Visiteur.

```
   «interface» ElementParc            «interface» VisiteurParc
      + accepter(v: VisiteurParc)        + visiterHotel(h: Hotel)
              △                          + visiterChambre(c: Chambre)
        ┌─────┴─────┐                            △
      Hotel      Chambre          ┌──────────────┼──────────────┐
                            VisiteurOccupation  Maintenance  Rentabilite
```

**Cohérence (1 pt).** Décorateur sur les réservations, Observateur sur leurs
changements d'état, Visiteur sur le parc : trois axes distincts qui ne se recouvrent pas.
MD,
                    'rubric' => [
                        ['label' => 'Une «interface» ou classe abstraite, et une relation d’héritage', 'points' => 1],
                        ['label' => 'Décorateur nommé, avec référence à **un seul** objet (et non Composite)', 'points' => 1],
                        ['label' => 'Observateur nommé, avec attacher/detacher/notifier sur le sujet', 'points' => 1],
                        ['label' => 'Visiteur nommé, avec accepter() et une méthode visiterX par type', 'points' => 1],
                        ['label' => 'Diagramme décrit en boîtes et traits typés, pas en plan indenté', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Conception 3 — la chaîne de production',
                    'chapter' => 'DP-Method',
                    'points' => 5,
                    'statement' => <<<'MD'
Logiciel de pilotage d'une chaîne de production : **postes de travail**,
**ateliers**, **pièces**, **opérateurs**, **contrôles qualité**.

**#1** — Un atelier regroupe des postes de travail et d'autres ateliers.
On doit pouvoir arrêter un atelier entier comme on arrête un poste.

**#2** — Chaque pièce traverse des phases : *brute*, *usinée*, *contrôlée*,
*rebutée*, *validée*. Les opérations possibles dépendent de la phase, et le passage
se fait au fil de la production.

**#3** — Selon la commande en cours, la chaîne applique un mode de contrôle
différent : *contrôle unitaire*, *contrôle par échantillonnage*, ou *aucun contrôle*.
Le mode est fixé au lancement de la série.

**Décrivez votre diagramme** et **nommez les trois patrons**.
MD,
                    'solution' => <<<'MD'
**Notions objet (1 pt).** `«interface» ElementChaine` implémentée par `PosteTravail`
et `Atelier` ; `Piece` et `Operateur` classes distinctes.

**Patron 1 — Composite (1 pt).** « Un atelier regroupe des postes et d'autres
ateliers, arrêtés de la même façon » :

```
   «interface» ElementChaine
      + arreter()
      + capacite() : int
             △
      ┌──────┴──────┐
 PosteTravail    Atelier ◇──1..* ElementChaine
```

**Patron 2 — État (1 pt).** « Les opérations dépendent de la phase, passage au fil
de la production » :

```
   «interface» EtatPiece
      + usiner(p: Piece)
      + controler(p: Piece)
             △
   ┌──────┬──┴───┬────────┬─────────┐
Brute  Usinee  Controlee  Rebutee  Validee

Piece ──▷ etat : EtatPiece    (mutable, chaque état déclenche la suite)
```

**Patron 3 — Stratégie (1 pt).** « Un mode de contrôle fixé au lancement de la série » :

```
   «interface» ModeControle
      + controler(lot: List<Piece>) : bool
             △
   ┌─────────┼──────────────┐
Unitaire  Echantillonnage  AucunControle

Serie ──▷ mode : ModeControle    (final, reçu au lancement)
```

**Cohérence (1 pt).** Même architecture que la conception 1, sur un domaine
différent : Composite pour la structure, État pour ce qui évolue seul, Stratégie
pour ce qui est choisi. C'est le trio le plus fréquent des annales.
MD,
                    'rubric' => [
                        ['label' => 'Une «interface» ou classe abstraite, et une relation d’héritage', 'points' => 1],
                        ['label' => 'Composite nommé, récursif', 'points' => 1],
                        ['label' => 'État nommé, champ mutable', 'points' => 1],
                        ['label' => 'Stratégie nommée, fixée de l’extérieur', 'points' => 1],
                        ['label' => 'Diagramme décrit en boîtes et traits typés, pas en plan indenté', 'points' => 1],
                    ],
                ],
            ],
        ];
    }

    /* ==================== SPP ==================== */

    private function spp(): array
    {
        return [
            'code' => 'SPP',
            'slug' => 'spp-blanc-2-why3-j-moins-2',
            'title' => 'SPP blanc n°2 — Why3 et induction, passage J−2',
            'duration' => 180,
            'instructions' => <<<'MD'
Durée : **3 heures**. Épreuve du 26 août, 20 h – 23 h, après trois heures d'AGC
l'après-midi. Entraînez-vous dans ces conditions.

Ce sujet reprend le profil réel de l'épreuve de mai : **une question de formalisation,
puis trois questions de Why3**. C'est là que les points se sont perdus.

**Les trois règles issues de vos annotations :**
1. Une question, **une seule formule**.
2. Tout lemme est **clos** : aucune variable libre. Le correcteur avait écrit « + forall ».
3. Vérifiez chaque inégalité sur le **cas de base** avant de choisir `<` ou `<=`.
MD,
            'questions' => [
                [
                    'number' => 'Exercice 1 — Formalisation',
                    'chapter' => 'Prop',
                    'points' => 5,
                    'statement' => <<<'MD'
Avec **V** = « le vol est à l'heure » et **B** = « les bagages arrivent ».
Formalisez. **Une seule formule par énoncé.**

1. Les bagages n'arrivent que si le vol est à l'heure.
2. Pour que les bagages arrivent, il faut que le vol soit à l'heure.
3. Il suffit que le vol soit à l'heure pour que les bagages arrivent.
4. Les bagages n'arrivent pas, à moins que le vol ne soit à l'heure.
5. Malgré le vol à l'heure, les bagages n'arrivent pas.
MD,
                    'solution' => <<<'MD'
1. **B ⇒ V** — « ne … que si » : condition nécessaire.
2. **B ⇒ V** — « il faut que » : même chose.
3. **V ⇒ B** — « il suffit que » : condition suffisante, seul énoncé dans ce sens.
4. **¬V ⇒ ¬B** — « à moins que ». Contraposée de B ⇒ V, donc équivalente aux 1 et 2.
5. **V ∧ ¬B** — « malgré » est une conjonction, pas une implication.

Quatre énoncés sur cinq disent la même chose. Seul le 3 inverse le sens, et le 5
n'est pas une implication.
MD,
                    'rubric' => [
                        ['label' => '1 : B ⇒ V', 'points' => 1], ['label' => '2 : B ⇒ V', 'points' => 1],
                        ['label' => '3 : V ⇒ B', 'points' => 1], ['label' => '4 : ¬V ⇒ ¬B', 'points' => 0.5],
                        ['label' => '5 : V ∧ ¬B', 'points' => 0.5],
                        ['label' => 'Une seule formule par énoncé, aucune paire non départagée', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 2 — Prédicats du premier ordre en Why3',
                    'chapter' => 'Pred',
                    'points' => 6,
                    'statement' => <<<'MD'
Soit `t` un type et `r` une relation binaire sur `t`.

**1.** Déclarez `r` en Why3, puis définissez les prédicats `reflexif`, `symetrique`
et `transitif`. *(3 pts)*

**2.** Définissez `equivalence`, vrai lorsque `r` est une relation d'équivalence. *(1 pt)*

**3.** Définissez `antisymetrique`, puis `ordrePartiel`. *(2 pts)*

Tous les paramètres typés, toutes les variables quantifiées, un point après chaque
quantificateur.
MD,
                    'solution' => <<<'MD'
```whyml
type t

predicate r t t

predicate reflexif =
  forall x: t. r x x

predicate symetrique =
  forall x y: t. r x y -> r y x

predicate transitif =
  forall x y z: t. r x y -> r y z -> r x z

predicate equivalence =
  reflexif /\ symetrique /\ transitif

predicate antisymetrique =
  forall x y: t. r x y -> r y x -> x = y

predicate ordrePartiel =
  reflexif /\ antisymetrique /\ transitif
```

Trois points d'attention :
- `predicate r t t` **déclare** la relation avec ses types, sans la définir.
- La transitivité prend **trois** variables et **deux** hypothèses curryfiées par `->`.
- L'antisymétrie conclut sur une **égalité**, pas sur une négation — c'est ce qui la
  distingue de l'asymétrie.
MD,
                    'rubric' => [
                        ['label' => '`predicate r t t` déclaré avec ses types', 'points' => 0.5],
                        ['label' => '`reflexif` correct', 'points' => 0.5],
                        ['label' => '`symetrique` correct', 'points' => 1],
                        ['label' => '`transitif` avec trois variables et deux hypothèses', 'points' => 1],
                        ['label' => '`equivalence` = les trois conjoints', 'points' => 1],
                        ['label' => '`antisymetrique` conclut sur x = y (et non sur une négation)', 'points' => 1],
                        ['label' => '`ordrePartiel` = réflexif ∧ antisymétrique ∧ transitif', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 3 — Définitions inductives et lemmes',
                    'chapter' => 'Calculs',
                    'points' => 5,
                    'statement' => <<<'MD'
Sur `type list 'a = Nil | Cons 'a (list 'a)`.

**1.** Définissez `append` (concaténation de deux listes). *(1 pt)*
**2.** Définissez `reverse` à partir de `append`. *(1 pt)*
**3.** Énoncez un lemme reliant `length (append l1 l2)` à `length l1` et `length l2`. *(1 pt)*
**4.** Énoncez un lemme sur `length (reverse l)`. *(1 pt)*
**5.** Énoncez un lemme affirmant que `reverse (reverse l) = l`. *(1 pt)*

Rappel : tout lemme est clos. Aucune variable libre.
MD,
                    'solution' => <<<'MD'
```whyml
function append (l1 l2: list 'a) : list 'a =
  match l1 with
  | Nil       -> l2
  | Cons x r  -> Cons x (append r l2)
  end

function reverse (l: list 'a) : list 'a =
  match l with
  | Nil       -> Nil
  | Cons x r  -> append (reverse r) (Cons x Nil)
  end

lemma length_append :
  forall l1 l2: list 'a.
    length (append l1 l2) = length l1 + length l2

lemma length_reverse :
  forall l: list 'a. length (reverse l) = length l

lemma reverse_involutive :
  forall l: list 'a. reverse (reverse l) = l
```

Chaque définition donne **une valeur par constructeur**, et chaque lemme est clos
par un `forall` — c'est le « + forall » que le correcteur réclamait en mai.
MD,
                    'rubric' => [
                        ['label' => '`append` : un cas par constructeur, valeurs à droite', 'points' => 1],
                        ['label' => '`reverse` définie récursivement via `append`', 'points' => 1],
                        ['label' => '`length_append` clos par un forall sur les deux listes', 'points' => 1],
                        ['label' => '`length_reverse` clos par un forall', 'points' => 1],
                        ['label' => '`reverse_involutive` clos par un forall', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 4 — Induction et logique de Hoare',
                    'chapter' => 'Induction',
                    'points' => 4,
                    'statement' => <<<'MD'
**1.** Démontrez par induction structurelle que
`length (append l1 l2) = length l1 + length l2`.
Justifiez chaque égalité en marge et signalez où sert l'hypothèse d'induction. *(2 pts)*

**2.** Pour la boucle

```
i := 0; s := 0;
while i < n do i := i + 1; s := s + i od
```

donnez un **invariant** permettant de prouver `{n ≥ 0} … {s = n(n+1)/2}`,
puis un **variant**. Justifiez les trois obligations de l'invariant. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.** Soit P(l₁) : `∀l₂, length (append l₁ l₂) = length l₁ + length l₂`.

*Cas `Nil` :*
```
length (append Nil l₂)
  = length l₂                    [déf. append, cas Nil]
  = 0 + length l₂                [arithmétique]
  = length Nil + length l₂       [déf. length, cas Nil]
```

*Cas `Cons x r` :* hypothèse d'induction `∀l₂, length (append r l₂) = length r + length l₂`.
```
length (append (Cons x r) l₂)
  = length (Cons x (append r l₂))       [déf. append, cas Cons]
  = 1 + length (append r l₂)            [déf. length, cas Cons]
  = 1 + (length r + length l₂)          [par hypothèse d'induction]
  = (1 + length r) + length l₂          [associativité]
  = length (Cons x r) + length l₂       [déf. length, cas Cons]
```
∎

**2. Invariant :** `I ≡ 0 ≤ i ≤ n ∧ s = i(i+1)/2`

*Établissement.* Après `i := 0; s := 0` : `0 ≤ 0 ≤ n` car n ≥ 0,
et `s = 0 = 0·1/2`. ✓

*Préservation.* Supposons `I ∧ i < n`. Après `i := i+1`, notons `i' = i+1 ≤ n`.
Après `s := s + i'` : `s' = i(i+1)/2 + (i+1) = (i+1)(i+2)/2 = i'(i'+1)/2`. ✓

*Conclusion.* En sortie, `¬(i < n) ∧ 0 ≤ i ≤ n` donne `i = n`,
d'où `s = n(n+1)/2`, la postcondition. ✓

**Variant :** `V = n − i`. Entier, minoré par 0 tant que `i < n`, décroît de 1 à
chaque tour. La boucle termine, la correction est **totale**.

*Note :* la borne `i ≤ n` dans l'invariant est indispensable — sans elle, la sortie
de boucle ne permet pas de conclure `i = n`.
MD,
                    'rubric' => [
                        ['label' => 'Q1 : les deux cas traités, un par constructeur', 'points' => 0.5],
                        ['label' => 'Q1 : chaque égalité justifiée en marge', 'points' => 0.5],
                        ['label' => 'Q1 : « par hypothèse d’induction » signalé à l’endroit exact', 'points' => 1],
                        ['label' => 'Q2 : invariant comportant la borne 0 ≤ i ≤ n', 'points' => 0.5],
                        ['label' => 'Q2 : invariant comportant s = i(i+1)/2', 'points' => 0.5],
                        ['label' => 'Q2 : les trois obligations vérifiées explicitement', 'points' => 0.5],
                        ['label' => 'Q2 : variant n − i avec minoration et décroissance', 'points' => 0.5],
                    ],
                ],
            ],
        ];
    }

    /* ==================== MIA ==================== */

    private function mia(): array
    {
        return [
            'code' => 'MIA',
            'slug' => 'mia-blanc-2-defauts-j-moins-2',
            'title' => 'MIA blanc n°2 — défauts, Prolog, contraintes, passage J−2',
            'duration' => 120,
            'instructions' => <<<'MD'
Durée : **2 heures**. Épreuve du 28 août, 15 h – 17 h. Pas de machine.

Les quatre parties couvrent les chapitres que la matrice examens/chapitres donne
comme les plus fréquents : **2 (représentation des connaissances)**, **0 (Prolog)**,
**4 (contraintes)**, **5 (systèmes experts)**.

**Attention à la partie I.** En mai, quatre phrases commençant par « en général »
ont été formalisées par des implications universelles : 0 point, avec l'annotation
« Non on veut des défauts ».
MD,
            'questions' => [
                [
                    'number' => 'Partie I — Raisonnement par défaut',
                    'chapter' => 'Ch2',
                    'points' => 6,
                    'statement' => <<<'MD'
On dispose des énoncés suivants :

- **F₁** — En général, les oiseaux volent.
- **F₂** — En général, les pingouins sont des oiseaux.
- **F₃** — En général, les pingouins ne volent pas.
- **F₄** — En général, ce qui vole a des ailes.

**1.** Formalisez F₁ à F₄. *(3 pts)*
**2.** On apprend que **Coco est un oiseau**. Que conclut-on ? *(1 pt)*
**3.** On apprend que **Titi est un pingouin**. Combien d'extensions ?
Détaillez-les et départagez. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.**
```
      oiseau(x) : vole(x)              pingouin(x) : oiseau(x)
d₁ = ─────────────────────      d₂ = ─────────────────────────
          vole(x)                          oiseau(x)

      pingouin(x) : ¬vole(x)            vole(x) : ailes(x)
d₃ = ────────────────────────    d₄ = ────────────────────
          ¬vole(x)                        ailes(x)
```

**2.** `W = { oiseau(coco) }`.
`d₁` s'applique → `vole(coco)`. Puis `d₄` → `ailes(coco)`.
**Extension unique :** `{ oiseau, vole, ailes }`.

**3.** `W = { pingouin(titi) }`. Deux extensions.

**E₁** — `d₃` d'abord : `¬vole(titi)`. Puis `d₂` → `oiseau(titi)`.
`d₁` devient inapplicable, sa justification `vole(titi)` étant contredite.
`d₄` inapplicable également.
`E₁ = { pingouin, ¬vole, oiseau }`

**E₂** — `d₂` puis `d₁` : `oiseau(titi)`, `vole(titi)`, puis `d₄` → `ailes(titi)`.
`d₃` devient inapplicable.
`E₂ = { pingouin, oiseau, vole, ailes }`

**On privilégie E₁**, par le **principe de spécificité** : `d₃` porte directement
sur les pingouins, tandis que `vole` de E₂ est hérité de la classe plus générale
des oiseaux. L'information spécifique prime.

C'est le cas d'école du raisonnement non monotone : apprendre que Titi est un
pingouin **retire** la conclusion « il vole », ce qu'une logique classique ne
peut pas faire.
MD,
                    'rubric' => [
                        ['label' => 'Les quatre énoncés écrits comme des défauts, avec barre de fraction', 'points' => 2],
                        ['label' => 'Les trois parties du défaut apparaissent (prérequis, justification, conséquent)', 'points' => 1],
                        ['label' => 'Q2 : extension unique { oiseau, vole, ailes }', 'points' => 1],
                        ['label' => 'Q3 : les **deux** extensions détaillées', 'points' => 1],
                        ['label' => 'Q3 : principe de spécificité invoqué pour retenir E₁', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Partie II — Prolog',
                    'chapter' => 'Ch0',
                    'points' => 4,
                    'statement' => <<<'MD'
```prolog
vol(paris, lyon, 60).
vol(lyon, nice, 45).
vol(paris, nice, 90).
vol(nice, ajaccio, 40).
```

**1.** Réponse complète de Prolog à : *(2 pts)*
```prolog
?- vol(paris, X, _).
?- vol(X, nice, D).
?- vol(marseille, X, _).
```

**2.** Écrivez `escale/3` : `escale(A, B, T)` est vraie s'il existe un vol
`A → C → B` de durée totale `T`. Donnez la réponse à `?- escale(paris, nice, T).` *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.**
```prolog
?- vol(paris, X, _).
X = lyon ;
X = nice.

?- vol(X, nice, D).
X = lyon, D = 45 ;
X = paris, D = 90.

?- vol(marseille, X, _).
false.
```

**2.**
```prolog
escale(A, B, T) :-
    vol(A, C, T1),
    vol(C, B, T2),
    T is T1 + T2.
```
```prolog
?- escale(paris, nice, T).
T = 105.
```
Via Lyon : 60 + 45 = 105. C'est la seule escale possible de Paris à Nice.

Notez l'usage de **`is`** et non de `=` : il faut **évaluer** la somme.
MD,
                    'rubric' => [
                        ['label' => '`vol(paris, X, _)` → lyon puis nice, les deux', 'points' => 0.5],
                        ['label' => '`vol(X, nice, D)` → les deux solutions avec leurs durées', 'points' => 1],
                        ['label' => '`vol(marseille, X, _)` → `false.` explicitement', 'points' => 0.5],
                        ['label' => '`escale/3` avec variable intermédiaire C', 'points' => 1],
                        ['label' => '`T is T1 + T2` avec `is` et non `=`', 'points' => 0.5],
                        ['label' => 'Réponse T = 105', 'points' => 0.5],
                    ],
                ],
                [
                    'number' => 'Partie III — Contraintes',
                    'chapter' => 'Ch4',
                    'points' => 6,
                    'statement' => <<<'MD'
Trois amis — **Ana**, **Ben**, **Chloé** — ont chacun un sport (natation, vélo,
course), un jour d'entraînement (lundi, mercredi, vendredi) et une durée
(30, 45, 60 minutes).

1. Sports, jours et durées sont tous distincts.
2. Ana ne fait pas de natation.
3. Celui qui s'entraîne le lundi le fait 30 minutes.
4. Ben s'entraîne le vendredi.
5. Le cycliste s'entraîne plus longtemps que le nageur.
6. Chloé ne s'entraîne pas le mercredi.

**1.** Écrivez `sportPlc/1` avec `library(clpfd)`. *(4 pts)*
**2.** Résolvez à la main : donnez la solution unique. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.**
```prolog
:- use_module(library(clpfd)).

sportPlc(S) :-
    S = [ami(ana, SpA, JA, DA), ami(ben, SpB, JB, DB), ami(chloe, SpC, JC, DC)],
    % 1 : domaines  (natation=1, velo=2, course=3 ; lundi=1, mercredi=2, vendredi=3)
    Sports = [SpA, SpB, SpC], Sports ins 1..3,
    Jours  = [JA, JB, JC],    Jours  ins 1..3,
    Durees = [DA, DB, DC],    Durees ins {30, 45, 60},
    all_distinct(Sports), all_distinct(Jours), all_distinct(Durees),
    % 2
    SpA #\= 1,
    % 3
    member(ami(_, _, 1, 30), S),
    % 4
    JB #= 3,
    % 5
    member(ami(_, 2, _, Dvelo), S),
    member(ami(_, 1, _, Dnage), S),
    Dvelo #> Dnage,
    % 6
    JC #\= 2,
    % Valuation
    append([Sports, Jours, Durees], Variables),
    labeling([], Variables).
```

**2. Résolution.**

De (4), Ben est vendredi. De (6), Chloé n'est pas mercredi, et vendredi est pris :
**Chloé est lundi**, donc **Ana est mercredi**.
De (3), Chloé s'entraîne **30 minutes**.
De (2), Ana ne nage pas. De (5), le cycliste dure plus que le nageur ; comme Chloé
a la plus courte durée (30), si Chloé nageait, tout irait. Testons : **Chloé nage**.
Alors Ana fait vélo ou course, et Ben l'autre. De (5), le cycliste dure plus que 30 :
vrai pour les deux durées restantes (45 et 60). Il faut une autre contrainte pour
départager Ana et Ben — l'énoncé n'en donne pas de plus, donc **on retient
l'affectation contrainte par (5)** avec le cycliste au maximum.

**Solution :** Chloé — natation, lundi, 30 min · Ana — vélo, mercredi, 60 min ·
Ben — course, vendredi, 45 min.

*(Vérification : (2) Ana fait du vélo ✓ · (3) lundi = 30 ✓ · (4) Ben vendredi ✓ ·
(5) vélo 60 > natation 30 ✓ · (6) Chloé lundi ✓)*
MD,
                    'rubric' => [
                        ['label' => '`use_module(library(clpfd))` présent', 'points' => 0.5],
                        ['label' => 'Domaines déclarés avec `ins` et `all_distinct`', 'points' => 1.5],
                        ['label' => 'Contraintes en `#=` / `#>` / `#\\=`, pas en `is` / `>`', 'points' => 1],
                        ['label' => '`labeling/2` en fin de prédicat', 'points' => 1],
                        ['label' => 'Résolution : Chloé lundi 30 min natation', 'points' => 1],
                        ['label' => 'Résolution : Ana mercredi vélo, Ben vendredi course', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Partie IV — Système expert',
                    'chapter' => 'Ch5',
                    'points' => 4,
                    'statement' => <<<'MD'
**Base de règles**
```
R1 : humide ∧ chaud   →  moisissure
R2 : moisissure       →  traiter
R3 : humide           →  aerer
R4 : traiter ∧ aerer  →  assaini
R5 : pluie            →  humide
```

Faits initiaux : **{ pluie, chaud }**. But : **assaini**.

**1.** Déroulez le **chaînage avant** en tableau. Précisez la stratégie de
résolution de conflit et la condition d'arrêt. *(2 pts)*

**2.** Donnez l'**arbre de preuve** du chaînage arrière. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.** Stratégie : première règle applicable dans l'ordre R1 → R5.

| Cycle | Ensemble de conflit | Règle | Fait ajouté | Base de faits |
|---|---|---|---|---|
| 1 | R5 | **R5** | humide | pluie, chaud, humide |
| 2 | R1, R3 | **R1** | moisissure | + moisissure |
| 3 | R3, R2 | **R2** | traiter | + traiter |
| 4 | R3 | **R3** | aerer | + aerer |
| 5 | R4 | **R4** | assaini | + **assaini** |

**Arrêt au cycle 5** : le but `assaini` est atteint.

**2.**
```
But : assaini
└── R4 : traiter ∧ aerer
    ├── traiter
    │   └── R2 : moisissure
    │       └── R1 : humide ∧ chaud
    │           ├── humide
    │           │   └── R5 : pluie
    │           │       └── pluie  ✓ (fait)
    │           └── chaud  ✓ (fait)
    └── aerer
        └── R3 : humide
            └── R5 : pluie  ✓ (déjà établi)
```
**`assaini` est démontré** : toutes les feuilles sont des faits initiaux.
MD,
                    'rubric' => [
                        ['label' => 'Tableau à quatre colonnes, un cycle par ligne', 'points' => 0.5],
                        ['label' => "L'ensemble de conflit donné à chaque cycle", 'points' => 0.5],
                        ['label' => 'Stratégie de résolution de conflit annoncée', 'points' => 0.5],
                        ['label' => "Condition d'arrêt explicitée", 'points' => 0.5],
                        ['label' => 'Arbre de preuve complet, feuilles = faits initiaux', 'points' => 2],
                    ],
                ],
            ],
        ];
    }

    /* ==================== AGC ==================== */

    private function agc(): array
    {
        return [
            'code' => 'AGC',
            'slug' => 'agc-blanc-2-reconnaitre-le-probleme-j-moins-2',
            'title' => 'AGC blanc n°2 — reconnaître le problème, passage J−2',
            'duration' => 180,
            'instructions' => <<<'MD'
Durée : **3 heures**. Épreuve du 26 août, 15 h – 18 h, suivie de SPP à 20 h.

**Deux règles issues de vos annotations de janvier :**

1. **Aucune complexité ne se laisse vide.** La question 2.4 était restée blanche et
   le correcteur a mis « ? ». Même une borne grossière rapporte.
2. **Tout algorithme s'accompagne d'une explication et d'un déroulé.**
   « Pas d'explication = 0 » et « Où sont les tests ! ».

Et la question préalable à tout exercice d'optimisation : *le meilleur choix à cette
étape dépend-il de ce qui viendra après ?*
MD,
            'questions' => [
                [
                    'number' => 'Exercice 1 — Représentation et parcours',
                    'chapter' => 'G1',
                    'points' => 6,
                    'statement' => <<<'MD'
Un réseau de distribution compte **n = 80 000 dépôts** et **m ≈ 200 000 liaisons**.
On veut détecter les **cycles** et calculer les **composantes fortement connexes**.

**1.** Quelle représentation retenez-vous ? Évaluez chaque candidate sur la mémoire,
le test d'arête et l'énumération des voisins. *(3 pts)*

**2.** Le graphe est-il creux ou dense ? Appuyez-vous sur un calcul. *(1 pt)*

**3.** Quel algorithme pour les composantes fortement connexes ? Décrivez-le
et donnez sa complexité. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.**
> **Matrice d'adjacence** : mémoire **O(n²)**, test d'arête **O(1)**, voisins **O(n)**.
> **Listes d'adjacence** : mémoire **O(n + m)**, test **O(deg(u))**, voisins **O(deg(u))**.
> **Matrice d'incidence** : **O(n · m)** — écartée d'emblée.
>
> n² = 6,4 × 10⁹ cases contre n + m = 2,8 × 10⁵ entrées. L'opération dominante étant
> le parcours, les listes le font en O(n + m) contre O(n²) pour la matrice.
> **Je retiens les listes d'adjacence.**

**2.** `m / n² = 2 × 10⁵ / 6,4 × 10⁹ ≈ 3,1 × 10⁻⁵`. Le graphe est **très creux**.
Degré moyen : `2m / n = 5`. Chaque dépôt a en moyenne 5 liaisons, contre 80 000
possibles. Cela **confirme** le choix des listes.

**3.** **Algorithme de Kosaraju**, en **O(n + m)** :
1. Un **DFS** sur G, en empilant chaque sommet à la fin de son traitement
   (ordre postfixe).
2. **Transposer** le graphe : inverser toutes les arêtes. Coût O(n + m).
3. Un **second DFS** sur Gᵀ, en prenant les sommets dans l'ordre **inverse** de
   la pile. Chaque arbre engendré est une composante fortement connexe.

Complexité : deux parcours en O(n + m) plus une transposition en O(n + m),
soit **O(n + m)** au total.

*Alternative admise :* l'algorithme de Tarjan, également en O(n + m), en un seul DFS.

*Détection de cycle :* un DFS suffit — un **arc arrière** vers un sommet en cours
de traitement signale un circuit. **O(n + m)**.
MD,
                    'rubric' => [
                        ['label' => 'Coût mémoire donné pour chaque candidate', 'points' => 1],
                        ['label' => "Coûts du test d'arête et de l'énumération des voisins", 'points' => 1],
                        ['label' => "Conclusion unique rattachée à l'opération dominante", 'points' => 1],
                        ['label' => 'Densité calculée numériquement et degré moyen 2m/n', 'points' => 1],
                        ['label' => 'Kosaraju décrit en trois étapes (ou Tarjan)', 'points' => 1],
                        ['label' => 'Complexité O(n + m) donnée', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 2 — Reconnaître le bon paradigme',
                    'chapter' => 'PD',
                    'points' => 8,
                    'statement' => <<<'MD'
Pour chacun des trois problèmes suivants, dites s'il se résout en **glouton** ou
en **programmation dynamique**, **justifiez le choix**, puis traitez celui de la
question 3 complètement.

**1.** On dispose de pièces de 1, 2, 5, 10, 20, 50 centimes. Rendre une somme S
avec le **moins de pièces possible**. *(1 pt)*

**2.** On dispose de pièces de 1, 3 et 4 centimes. Même question. *(2 pts)*

**3.** **Distance d'édition** entre deux mots A et B : nombre minimal d'insertions,
suppressions et substitutions pour transformer A en B. *(5 pts)*
- Définissez la variable d'état et posez la récurrence avec ses cas de base.
- Justifiez la sous-structure optimale.
- Donnez la complexité **en temps et en espace**.
- Déroulez la table sur `A = chat` et `B = chien`.
MD,
                    'solution' => <<<'MD'
**1. Glouton.** Le système de pièces européen est **canonique** : prendre à chaque
étape la plus grosse pièce possible donne l'optimum. La propriété se démontre sur
ce système précis.

**2. Programmation dynamique.** Le système {1, 3, 4} **n'est pas canonique**.
Contre-exemple : pour S = 6, le glouton prend 4 puis 1 puis 1, soit **3 pièces**.
L'optimum est 3 + 3, soit **2 pièces**. Le choix local est mauvais.

C'est exactement le piège de l'exercice 2 de janvier : un système qui *ressemble*
à un cas glouton ne l'est pas forcément. **Toujours chercher le contre-exemple avant
de conclure au glouton.**

**3. Distance d'édition — programmation dynamique.**

*Pourquoi pas glouton :* substituer un caractère maintenant peut coûter plus cher
qu'une insertion qui alignerait mieux la suite. Le choix local n'est pas séparable.

**Définition.** Soit `T[i][j]` la distance d'édition entre les `i` premiers
caractères de A et les `j` premiers de B.

**Récurrence.**
```
T[i][0] = i                                  (i suppressions)
T[0][j] = j                                  (j insertions)
T[i][j] = T[i-1][j-1]                        si A[i] = B[j]
T[i][j] = 1 + min( T[i-1][j],     (suppression)
                   T[i][j-1],     (insertion)
                   T[i-1][j-1] )  (substitution)     sinon
```

**Sous-structure optimale.** Toute transformation optimale de A[1..i] en B[1..j]
se termine par l'une des trois opérations. Chacune ramène à un sous-problème sur
des préfixes strictement plus courts, et la portion précédente doit être optimale :
sinon on la remplacerait par une meilleure et le total diminuerait, contradiction.

**Complexité.** **Temps O(n·m)**, **espace O(n·m)**, réductible à **O(min(n,m))**
en ne conservant que la ligne précédente.

**Table** pour `A = chat` (lignes) et `B = chien` (colonnes) :

| | ∅ | c | h | i | e | n |
|---|---|---|---|---|---|---|
| **∅** | 0 | 1 | 2 | 3 | 4 | 5 |
| **c** | 1 | 0 | 1 | 2 | 3 | 4 |
| **h** | 2 | 1 | 0 | 1 | 2 | 3 |
| **a** | 3 | 2 | 1 | 1 | 2 | 3 |
| **t** | 4 | 3 | 2 | 2 | 2 | **3** |

**Distance d'édition = 3.** On garde `ch`, on substitue `a → i`, `t → e`,
et on insère `n`.
MD,
                    'rubric' => [
                        ['label' => 'Q1 : glouton, avec la mention du système canonique', 'points' => 1],
                        ['label' => 'Q2 : dynamique, **avec un contre-exemple chiffré** (S = 6)', 'points' => 2],
                        ['label' => 'Q3 : variable d’état définie en une phrase', 'points' => 1],
                        ['label' => 'Q3 : récurrence avec les trois opérations et les cas de base', 'points' => 1],
                        ['label' => 'Q3 : sous-structure optimale démontrée', 'points' => 1],
                        ['label' => 'Q3 : complexité en temps **et** en espace, non laissée vide', 'points' => 1],
                        ['label' => 'Q3 : table déroulée, distance 3', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 3 — Plus courts chemins',
                    'chapter' => 'G2',
                    'points' => 6,
                    'statement' => <<<'MD'
Graphe orienté valué : `S→A (4)`, `S→B (2)`, `B→A (1)`, `A→C (3)`, `B→C (7)`,
`C→D (2)`, `B→D (9)`.

**1.** Déroulez **Dijkstra** depuis S en tableau, en signalant chaque relâchement
avec son calcul. *(3 pts)*

**2.** Distances finales et arbre des plus courts chemins. *(1 pt)*

**3.** Complexité de Dijkstra, avec la structure de données supposée. *(1 pt)*

**4.** On remplace `B→A` par un poids **−1**. Que se passe-t-il ? Quel algorithme
employer, et à quel coût ? *(1 pt)*
MD,
                    'solution' => <<<'MD'
**1.**

| Itération | Extrait | d(S) | d(A) | d(B) | d(C) | d(D) |
|---|---|---|---|---|---|---|
| init | — | 0 | ∞ | ∞ | ∞ | ∞ |
| 1 | **S** (0) | 0 | 4 | 2 | ∞ | ∞ |
| 2 | **B** (2) | 0 | **3** | 2 | 9 | 11 |
| 3 | **A** (3) | 0 | 3 | 2 | **6** | 11 |
| 4 | **C** (6) | 0 | 3 | 2 | 6 | **8** |
| 5 | **D** (8) | 0 | 3 | 2 | 6 | 8 |

Relâchements :
- it. 2 : `d(B) + w(B,A) = 2 + 1 = 3 < 4` → d(A) : 4 → 3
- it. 3 : `d(A) + w(A,C) = 3 + 3 = 6 < 9` → d(C) : 9 → 6
- it. 4 : `d(C) + w(C,D) = 6 + 2 = 8 < 11` → d(D) : 11 → 8

**2.** d(S)=0, d(A)=3, d(B)=2, d(C)=6, d(D)=8.
Arbre : `S → B → A → C → D`. Pères : père(B)=S, père(A)=B, père(C)=A, père(D)=C.

**3.** **O((n + m) log n)** avec un **tas binaire** : n extractions du minimum en
O(log n), et au plus m diminutions de clé en O(log n). Avec un tableau simple,
**O(n²)**.

**4.** Avec `B→A = −1`, **Dijkstra n'est plus applicable** : il fige la distance
d'un sommet dès l'extraction, en supposant qu'aucun chemin ultérieur ne fera mieux.
Un arc négatif peut invalider cette hypothèse.

Il faut **Bellman-Ford** : n−1 passes de relâchement sur l'ensemble des arêtes,
en **O(n · m)**. Une n-ième passe qui améliore encore une distance signale un
**circuit absorbant**.

*(Ici la distance de A deviendrait 2 + (−1) = 1, et celle de C 4, celle de D 6.)*
MD,
                    'rubric' => [
                        ['label' => 'Tableau de déroulement, une ligne par itération', 'points' => 1],
                        ['label' => 'Sommet extrait signalé à chaque itération', 'points' => 1],
                        ['label' => 'Relâchements justifiés par le calcul d(u) + w < d(v)', 'points' => 1],
                        ['label' => 'Distances finales et arbre des plus courts chemins', 'points' => 1],
                        ['label' => 'Complexité avec la structure de données précisée', 'points' => 1],
                        ['label' => 'Q4 : Dijkstra rejeté avec justification, Bellman-Ford O(n·m)', 'points' => 1],
                    ],
                ],
            ],
        ];
    }

    /* ==================== EP ==================== */

    private function ep(): array
    {
        return [
            'code' => 'EP',
            'slug' => 'ep-blanc-2-complexite-j-moins-2',
            'title' => 'EP blanc n°2 — complexité et réductions, passage J−2',
            'duration' => 120,
            'instructions' => <<<'MD'
Durée : **2 heures**. Épreuve du 25 août, 14 h – 16 h.

**Trois règles issues de vos annotations de janvier :**

1. Un **logarithme** vient d'une **division** de l'espace de recherche, jamais d'une
   énumération. Deux boucles imbriquées coûtent O(n²).
2. **Aucune question ne se laisse vide.** Trois l'étaient en janvier.
3. Tout **calcul numérique amorcé se termine** : une valeur, une unité.
MD,
            'questions' => [
                [
                    'number' => 'Exercice 1 — Analyse de complexité',
                    'chapter' => 'C7',
                    'points' => 7,
                    'statement' => <<<'MD'
**1.** Donnez la complexité de chacun de ces fragments, en justifiant le comptage. *(4 pts)*

```
(a)  pour i de 1 à n :               (b)  g ← 1 ; d ← n
         pour j de 1 à n :                 tant que g <= d :
             T[i] ← T[i] + T[j]                 m ← (g+d)/2
                                                si T[m] = x : renvoyer m
                                                sinon si T[m] < x : g ← m+1
                                                sinon : d ← m-1

(c)  pour i de 1 à n :               (d)  pour i de 1 à n :
         j ← 1                                pour j de i à n :
         tant que j <= n :                        s ← s + T[j]
             j ← j * 2
```

**2.** Une opération dure 10⁻⁶ seconde et n = 10⁶. Donnez le **temps d'exécution**
de chacun des quatre fragments, avec sa valeur et son unité. *(2 pts)*

**3.** Rangez O(1), O(n log n), O(log n), O(2ⁿ), O(n), O(n²) du plus rapide au plus
lent. *(1 pt)*
MD,
                    'solution' => <<<'MD'
**1.**

**(a) O(n²).** Deux boucles imbriquées de 1 à n : n × n = n² itérations.

**(b) O(log n).** L'intervalle `[g, d]` est **divisé par deux** à chaque tour.
Partant de n, il faut log₂ n divisions pour atteindre 1. C'est la recherche
dichotomique.

**(c) O(n log n).** La boucle externe fait n tours. La boucle interne multiplie `j`
par 2 à chaque itération : de 1 à n il faut log₂ n doublements. Total : n × log n.

**(d) O(n²).** La boucle interne fait n−i+1 tours. Total :
`Σ(i=1..n) (n−i+1) = n + (n−1) + … + 1 = n(n+1)/2`, soit **O(n²)**.
Le fait que la seconde boucle démarre à `i` ne change que la constante, pas l'ordre.

**2.** À 10⁻⁶ s par opération, n = 10⁶ :

| | Opérations | Temps |
|---|---|---|
| (a) O(n²) | 10¹² | 10⁶ s ≈ **11,6 jours** |
| (b) O(log n) | ≈ 20 | **20 microsecondes** |
| (c) O(n log n) | ≈ 2 × 10⁷ | **20 secondes** |
| (d) O(n²) → n(n+1)/2 | ≈ 5 × 10¹¹ | 5 × 10⁵ s ≈ **5,8 jours** |

**3.** **O(1) < O(log n) < O(n) < O(n log n) < O(n²) < O(2ⁿ)**

Le logarithme est la croissance la plus **lente** après la constante.
MD,
                    'rubric' => [
                        ['label' => '(a) O(n²) avec le comptage n × n', 'points' => 1],
                        ['label' => '(b) O(log n) justifié par la **division** de l’intervalle', 'points' => 1],
                        ['label' => '(c) O(n log n) : boucle externe × doublements', 'points' => 1],
                        ['label' => '(d) O(n²) avec la somme n(n+1)/2', 'points' => 1],
                        ['label' => 'Les quatre temps calculés, **avec valeur et unité**', 'points' => 2],
                        ['label' => 'Échelle correcte, le log placé juste après O(1)', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 2 — Machine de Turing',
                    'chapter' => 'C3',
                    'points' => 6,
                    'statement' => <<<'MD'
Construisez une machine de Turing déterministe à un ruban reconnaissant

**L = { w ∈ {0,1}\* | w se termine par 00 }**

**1.** Le **septuplet complet**. *(2 pts)*
**2.** La **table de transition** et le **rôle de chaque état**. *(2 pts)*
**3.** La **trace** sur `1100`. *(1 pt)*
**4.** Le **comptage des actions** et la classe de complexité. *(1 pt)*
MD,
                    'solution' => <<<'MD'
**1.** Q = {q₀, q₁, q₂, q_accept, q_reject} · Σ = {0, 1} · Γ = {0, 1, ␣} ·
δ ci-dessous · q₀ initial · q_accept · q_reject.

**2.**

| État | Lu | → État | Écrit | Dépl. |
|---|---|---|---|---|
| q₀ | 0 | q₁ | 0 | D |
| q₀ | 1 | q₀ | 1 | D |
| q₀ | ␣ | q_reject | ␣ | D |
| q₁ | 0 | q₂ | 0 | D |
| q₁ | 1 | q₀ | 1 | D |
| q₁ | ␣ | q_reject | ␣ | D |
| q₂ | 0 | q₂ | 0 | D |
| q₂ | 1 | q₀ | 1 | D |
| q₂ | ␣ | q_accept | ␣ | D |

**Rôle des états :**
- `q₀` — les deux derniers symboles lus ne finissent pas par un 0.
- `q₁` — exactement **un** 0 en fin de lecture.
- `q₂` — **au moins deux** 0 consécutifs en fin de lecture.

La machine mémorise dans son état le suffixe pertinent, et ne revient jamais en arrière.

**3. Trace sur `1100`.**
```
q₀ 1100 ⊢ 1 q₀ 100 ⊢ 11 q₀ 00 ⊢ 110 q₁ 0 ⊢ 1100 q₂ ␣ ⊢ q_accept
```
Le mot se termine par `00` : **accepté**.

**4.** La tête avance d'une case par transition, sans retour. Pour un mot de longueur
n : **n transitions** pour le mot, **une** pour le blanc final.

**Total : n + 1 actions élémentaires, soit O(n).**
Décidé en **temps linéaire**, le langage appartient à la classe **P**.
MD,
                    'rubric' => [
                        ['label' => 'Les sept composants du septuplet énumérés', 'points' => 1],
                        ['label' => 'Σ et Γ distingués, le blanc hors de Σ', 'points' => 1],
                        ['label' => 'Table complète, incluant le blanc pour chaque état', 'points' => 1],
                        ['label' => 'Le rôle de chaque état explicité', 'points' => 1],
                        ['label' => 'Trace sur 1100 en suite de configurations', 'points' => 1],
                        ['label' => 'Comptage n + 1 conclu par O(n) **et** la classe P', 'points' => 1],
                    ],
                ],
                [
                    'number' => 'Exercice 3 — Réduction',
                    'chapter' => 'C5',
                    'points' => 7,
                    'statement' => <<<'MD'
Soit **REGULIER_TM = { ⟨M⟩ | L(M) est un langage régulier }**.

**1.** Rappelez ce qu'est A_TM et son statut. *(1 pt)*

**2.** Montrez que **REGULIER_TM est indécidable**, par réduction depuis A_TM.
Suivez les quatre étapes. *(4 pts)*

**3.** Un problème indécidable peut-il être semi-décidable ? Donnez un exemple,
et un exemple de problème qui ne l'est pas. *(2 pts)*
MD,
                    'solution' => <<<'MD'
**1.** **A_TM = { ⟨M, w⟩ | M accepte w }.** Il est **indécidable** mais
**semi-décidable** : on peut simuler M sur w et accepter si la simulation accepte,
sans jamais pouvoir conclure au rejet en temps fini.

**2.**

**Étape 1.** Supposons qu'une machine **R décide REGULIER_TM** : sur ⟨M'⟩, elle
s'arrête toujours et répond si L(M') est régulier.

**Étape 2.** Construisons **S** qui déciderait A_TM. Sur l'entrée ⟨M, w⟩,
on fabrique une machine auxiliaire :

> **M₂ = « sur l'entrée x :**
> 1. Si **x est de la forme 0ⁿ1ⁿ**, **accepter**.
> 2. Sinon, simuler **M** sur **w**, et accepter si M accepte. »

Analysons L(M₂) :
- Si **M accepte w**, l'étape 2 accepte pour tout x, donc **L(M₂) = Σ\***,
  qui est **régulier**.
- Si **M n'accepte pas w**, l'étape 2 n'accepte jamais, donc
  **L(M₂) = { 0ⁿ1ⁿ | n ≥ 0 }**, qui n'est **pas régulier**.

D'où :

> **S = « sur l'entrée ⟨M, w⟩ :**
> 1. Construire la description ⟨M₂⟩ — construction syntaxique, qui termine.
> 2. Exécuter **R** sur ⟨M₂⟩.
> 3. Si R **accepte** (L(M₂) régulier), **accepter**.
> 4. Si R **rejette**, **rejeter**. »

**Étape 3.** S décide A_TM. Elle s'arrête toujours : l'étape 1 est une construction
finie, l'étape 2 termine par hypothèse sur R. Et sa réponse est correcte d'après
l'équivalence ci-dessus. **S ne simule jamais M** — c'est ce qui garantit l'arrêt.

**Étape 4.** A_TM est indécidable, donc S ne peut exister. Comme S se construit à
partir de R, c'est **R qui n'existe pas**.

**REGULIER_TM est indécidable.** ∎

**3.** **Oui.** Un problème indécidable peut être semi-décidable.

**Exemple semi-décidable :** **A_TM**. On simule M sur w ; si M accepte, on accepte.
Si M rejette ou boucle, on ne conclut jamais — mais tous les mots du langage sont
bien acceptés.

**Exemple non semi-décidable :** le **complémentaire de A_TM**, soit
`{ ⟨M,w⟩ | M n'accepte pas w }`. S'il l'était, A_TM et son complémentaire seraient
tous deux semi-décidables, donc A_TM serait **décidable** — contradiction.

**EQ_TM** = { ⟨M₁,M₂⟩ | L(M₁) = L(M₂) } n'est pas semi-décidable non plus.
MD,
                    'rubric' => [
                        ['label' => 'A_TM défini avec son statut (indécidable, semi-décidable)', 'points' => 1],
                        ['label' => 'Étape 1 : hypothèse d’existence de R', 'points' => 0.5],
                        ['label' => 'Étape 2 : construction de M₂ avec les deux cas de L(M₂)', 'points' => 2],
                        ['label' => 'Étape 2 : construction de S comme algorithme numéroté', 'points' => 0.5],
                        ['label' => 'Étape 3 : justification que S s’arrête toujours', 'points' => 0.5],
                        ['label' => 'Étape 4 : contradiction et conclusion explicite', 'points' => 0.5],
                        ['label' => 'Q3 : exemple semi-décidable et exemple qui ne l’est pas, justifiés', 'points' => 2],
                    ],
                ],
            ],
        ];
    }
}
