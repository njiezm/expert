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
 * Contenu SPP — les huit chapitres non couverts par SppContentSeeder.
 *
 * L'effort suit les poids d'examen : Pred (5), Contrats (4), Recur (4) et
 * Induction (4) reçoivent une fiche complète ; Intro, Theories, Types et
 * Calculs sont traités en fiche courte, suffisante pour les questions de cours.
 */
class SppContentSeeder2 extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'SPP')->first();

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
    }

    /* ==================================================================== */

    private function content(): array
    {
        return [

            /* ==================== Intro ==================== */
            'Intro' => [
                'lessons' => [
                    [
                        'title' => 'Prouver n\'est pas tester',
                        'est_minutes' => 10,
                        'intuition' => <<<'MD'
Tester, c'est examiner un nombre fini de cas et espérer que les autres se comportent
pareil. Prouver, c'est établir une propriété **pour toutes les entrées possibles**,
y compris celles qu'on n'a pas imaginées.

Dijkstra l'a formulé une fois pour toutes : *« le test peut révéler la présence de
bugs, jamais leur absence »*.

La **vérification déductive** consiste à traduire un programme et sa spécification
en formules logiques, puis à démontrer ces formules — à la main, ou en déléguant
à un solveur via un outil comme Why3.
MD,
                        'formalism' => <<<'MD'
**La chaîne de vérification déductive**

```
programme + spécification
        ↓  génération d'obligations de preuve
   formules logiques
        ↓  démonstration (manuelle ou automatique)
   programme prouvé correct
```

Une **obligation de preuve** est une formule dont la validité garantit un morceau
de la correction. Le programme est correct quand **toutes** les obligations sont
démontrées.

**Why3** est la plate-forme du cours : on y écrit le programme et sa spécification
en WhyML (fichiers `.mlw`), l'outil génère les obligations et les envoie à des
solveurs SMT (Alt-Ergo, Z3, CVC4).

**Trois niveaux de garantie** — la hiérarchie à connaître :

| Niveau | Ce qui est garanti |
|---|---|
| **Correction partielle** | *si* le programme termine, le résultat est correct |
| **Terminaison** | le programme s'arrête sur toute entrée valide |
| **Correction totale** | correction partielle **et** terminaison |
MD,
                        'worked_example' => <<<'MD'
**Un fichier WhyML minimal**

```whyml
module Fact
  use int.Int
  use int.Fact

  let rec factorielle (n: int) : int
    requires { n >= 0 }              (* précondition *)
    ensures  { result = fact n }     (* postcondition *)
    variant  { n }                   (* terminaison *)
  = if n = 0 then 1
    else n * factorielle (n - 1)
end
```

- `requires` — ce que l'appelant doit garantir.
- `ensures` — ce que la fonction garantit en retour, `result` désignant la valeur rendue.
- `variant` — l'expression qui décroît, prouvant la terminaison.

Sans `variant`, Why3 ne prouve que la **correction partielle**.
MD,
                        'pitfalls' => <<<'MD'
- **Croire qu'une preuve dispense de spécification.** Prouver, c'est démontrer qu'un
  programme respecte **une spécification donnée**. Une spécification fausse se prouve
  très bien.
- **Confondre `ensures` et une assertion.** `ensures` est un contrat sur la sortie,
  pas une vérification à l'exécution.
- **Omettre le `variant`** puis affirmer la correction totale.
MD,
                        'examiner_expects' => <<<'MD'
Sur une question de cours : la distinction nette entre **test** et **preuve**, et la
hiérarchie **correction partielle → terminaison → correction totale**, avec ce que
chaque niveau garantit exactement.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Correction partielle, terminaison, correction totale ?',
                        'back' => "**Partielle** — *si* le programme termine, le résultat est correct.\n**Terminaison** — le programme s'arrête sur toute entrée valide.\n**Totale** — les deux.\n\nUn triplet de Hoare seul ne donne que la correction partielle.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'À quoi servent `requires`, `ensures` et `variant` en WhyML ?',
                        'back' => "**`requires`** — précondition, ce que l'appelant garantit.\n**`ensures`** — postcondition, ce que la fonction garantit (`result` = valeur rendue).\n**`variant`** — expression décroissante, prouve la **terminaison**.\n\nSans `variant`, seule la correction partielle est établie.",
                    ],
                ],
            ],

            /* ==================== Pred — poids 5 ==================== */
            'Pred' => [
                'lessons' => [
                    [
                        'title' => 'Quantificateurs : ordre, portée, négation',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
La logique propositionnelle permet de dire « il pleut ». La logique du premier ordre
permet de dire « **tout** étudiant qui travaille réussit » — c'est-à-dire de parler
d'objets, de leurs propriétés, et de quantifier dessus.

Trois choses s'y jouent, et ce sont exactement les trois sources d'erreur :
**quel connecteur** accompagne quel quantificateur, **dans quel ordre** on les écrit,
et **comment on les nie**.
MD,
                        'formalism' => <<<'MD'
**La règle du connecteur — celle qu'on oublie**

| Quantificateur | Connecteur | Forme |
|---|---|---|
| ∀ | **⇒** | `∀x (P(x) ⇒ Q(x))` |
| ∃ | **∧** | `∃x (P(x) ∧ Q(x))` |

« Tout étudiant travailleur réussit » → `∀x (Travaille(x) ⇒ Reussit(x))`.
Avec un `∧`, on dirait « tout le monde travaille et réussit » — bien plus fort.

« Un étudiant travailleur a réussi » → `∃x (Travaille(x) ∧ Reussit(x))`.
Avec un `⇒`, la formule serait satisfaite par n'importe quel non-travailleur,
puisque `faux ⇒ quoi que ce soit` est vrai.

**L'ordre des quantificateurs change le sens**

- `∀x ∃y  Aime(x, y)` — chacun aime quelqu'un, **potentiellement différent**.
- `∃y ∀x  Aime(x, y)` — il existe **une même personne** que tout le monde aime.

La seconde implique la première ; l'inverse est faux. Inverser deux quantificateurs
de nature différente est une faute de fond.

**Les lois de De Morgan quantifiées**

```
¬ ∀x P(x)  ≡  ∃x ¬P(x)
¬ ∃x P(x)  ≡  ∀x ¬P(x)
```

En pratique : **on pousse la négation vers l'intérieur en échangeant les
quantificateurs**. Et puisque `¬(A ⇒ B) ≡ A ∧ ¬B` :

```
¬ ∀x (P(x) ⇒ Q(x))  ≡  ∃x (P(x) ∧ ¬Q(x))
```

C'est la formule à connaître : la négation d'un « tous » est un **contre-exemple**.

**Variables libres et liées.** Dans `∀x (P(x) ⇒ Q(x, y))`, `x` est **liée** par le
quantificateur, `y` est **libre**. Une formule sans variable libre est une **formule
close**, la seule à laquelle on puisse attribuer une valeur de vérité.
MD,
                        'worked_example' => <<<'MD'
**Formaliser, puis nier.**

*Énoncé :* « Tout étudiant qui rend tous ses devoirs obtient une mention. »

Prédicats : `E(x)` — x est étudiant ; `D(x, d)` — x rend le devoir d ;
`Dev(d)` — d est un devoir ; `M(x)` — x obtient une mention.

**Formalisation :**
```
∀x ( E(x) ∧ ∀d (Dev(d) ⇒ D(x, d))  ⇒  M(x) )
```

Notez les deux `⇒`, chacun attaché à son `∀`.

**Négation.** « Il existe un étudiant qui rend tous ses devoirs et n'obtient pas
de mention. »
```
¬ ∀x ( … ⇒ M(x) )
≡ ∃x ( E(x) ∧ ∀d (Dev(d) ⇒ D(x, d))  ∧  ¬M(x) )
```

On a appliqué `¬(A ⇒ B) ≡ A ∧ ¬B`, et le `∀d` interne **n'a pas bougé** : il n'était
pas sous la portée de la négation une fois celle-ci poussée.

**Le piège classique :** nier en écrivant `∃x ∃d (…)`, c'est-à-dire nier aussi le
quantificateur interne. Faux : la négation ne traverse que ce qui est effectivement
dans sa portée.
MD,
                        'pitfalls' => <<<'MD'
- **`∀` avec `∧`** — dit bien plus que l'énoncé. `∀x (P(x) ∧ Q(x))` affirme que
  *tout le monde* est P.
- **`∃` avec `⇒`** — dit bien moins. La formule est satisfaite par n'importe quel
  objet non-P.
- **Inverser `∀∃` et `∃∀`.** Sens différents, et l'implication ne va que dans un sens.
- **Nier trop de quantificateurs.** La négation ne traverse que sa portée.
- **Oublier de typer les variables.** `∀x (Etudiant(x) ⇒ …)` et non `∀x (…)` seul,
  sauf si le domaine est explicitement restreint aux étudiants.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] **Une seule formule** par énoncé — la règle du chapitre précédent vaut ici aussi.
- [ ] Le **bon connecteur** : `⇒` après `∀`, `∧` après `∃`.
- [ ] Les **variables typées** par un prédicat de domaine.
- [ ] Pour une négation : la formule **poussée jusqu'aux atomes**, sans `¬` devant
      un quantificateur.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Quel connecteur va avec ∀ ? Avec ∃ ?',
                        'back' => "**∀ va avec ⇒** : `∀x (P(x) ⇒ Q(x))`\n**∃ va avec ∧** : `∃x (P(x) ∧ Q(x))`\n\n`∀x (P ∧ Q)` dit que **tout le monde** est P — trop fort.\n`∃x (P ⇒ Q)` est satisfaite par n'importe quel non-P — trop faible.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Négation de `∀x (P(x) ⇒ Q(x))` ?',
                        'back' => "**∃x (P(x) ∧ ¬Q(x))**\n\nDeux règles combinées : `¬∀ ≡ ∃¬` et `¬(A ⇒ B) ≡ A ∧ ¬B`.\n\nLa négation d'un « tous » est un **contre-exemple**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => '`∀x ∃y Aime(x,y)` et `∃y ∀x Aime(x,y)` : même sens ?',
                        'back' => "**Non.**\n\n`∀x ∃y` — chacun aime quelqu'un, **potentiellement différent**.\n`∃y ∀x` — il existe **une même personne** aimée de tous.\n\nLa seconde implique la première ; l'inverse est faux.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Les deux lois de De Morgan quantifiées ?',
                        'back' => "```\n¬ ∀x P(x)  ≡  ∃x ¬P(x)\n¬ ∃x P(x)  ≡  ∀x ¬P(x)\n```\n\nOn pousse la négation vers l'intérieur en **échangeant** le quantificateur.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Variable libre, variable liée, formule close ?',
                        'back' => "**Liée** — sous la portée d'un quantificateur.\n**Libre** — sinon.\n**Close** — aucune variable libre.\n\nSeule une formule close a une valeur de vérité.",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Formaliser et nier au premier ordre',
                        'origin' => 'td',
                        'est_minutes' => 25,
                        'difficulty' => 4,
                        'statement' => <<<'MD'
Prédicats : `E(x)` — x est étudiant · `C(x, m)` — x est inscrit au module m ·
`Mod(m)` — m est un module · `R(x, m)` — x a réussi le module m.

**1.** Formalisez chacun des énoncés. **Une seule formule par énoncé.** *(5 pts)*

a. Tout étudiant est inscrit à au moins un module.
b. Un étudiant a réussi tous les modules auxquels il est inscrit.
c. Aucun étudiant n'a réussi tous les modules.
d. Il existe un module que tous les étudiants ont réussi.
e. Tout module a au moins un étudiant qui l'a réussi.

**2.** Donnez la négation de (b), poussée jusqu'aux atomes — aucun `¬` ne doit
rester devant un quantificateur. *(2 pts)*

**3.** Les énoncés (d) et (e) sont-ils équivalents ? Justifiez. *(1 pt)*
MD,
                        'hint' => "Rappelez-vous la règle du connecteur : ⇒ après ∀, ∧ après ∃. Et pour la question 3, comparez l'ordre des quantificateurs.",
                        'method' => <<<'MD'
1. Identifiez le quantificateur principal — celui qui porte sur toute la phrase.
2. Typez la variable avec son prédicat de domaine (`E(x)`, `Mod(m)`).
3. Appliquez la règle du connecteur.
4. Traitez les quantificateurs internes de la même façon.
5. Pour la négation : poussez pas à pas, en notant à chaque étape la règle appliquée.
MD,
                        'solution' => <<<'MD'
**1.**

a. `∀x ( E(x) ⇒ ∃m (Mod(m) ∧ C(x, m)) )`
b. `∃x ( E(x) ∧ ∀m (Mod(m) ∧ C(x, m) ⇒ R(x, m)) )`
c. `¬ ∃x ( E(x) ∧ ∀m (Mod(m) ⇒ R(x, m)) )`
   — ou, forme équivalente poussée : `∀x ( E(x) ⇒ ∃m (Mod(m) ∧ ¬R(x, m)) )`
d. `∃m ( Mod(m) ∧ ∀x (E(x) ⇒ R(x, m)) )`
e. `∀m ( Mod(m) ⇒ ∃x (E(x) ∧ R(x, m)) )`

Observez la mécanique : chaque `∀` est suivi d'un `⇒`, chaque `∃` d'un `∧`,
sans exception.

**2.** Négation de (b) :

```
¬ ∃x ( E(x) ∧ ∀m (Mod(m) ∧ C(x,m) ⇒ R(x,m)) )
≡ ∀x ¬( E(x) ∧ ∀m (…) )                            [¬∃ ≡ ∀¬]
≡ ∀x ( E(x) ⇒ ¬∀m (Mod(m) ∧ C(x,m) ⇒ R(x,m)) )     [¬(A ∧ B) ≡ A ⇒ ¬B]
≡ ∀x ( E(x) ⇒ ∃m ¬(Mod(m) ∧ C(x,m) ⇒ R(x,m)) )     [¬∀ ≡ ∃¬]
≡ ∀x ( E(x) ⇒ ∃m (Mod(m) ∧ C(x,m) ∧ ¬R(x,m)) )     [¬(A ⇒ B) ≡ A ∧ ¬B]
```

**Lecture :** « tout étudiant a raté au moins un module auquel il est inscrit ».

**3.** **Non, (d) et (e) ne sont pas équivalents.**

(d) s'écrit `∃m ∀x` : il existe **un même module**, réussi par tous les étudiants.
(e) s'écrit `∀m ∃x` : chaque module a **un étudiant, potentiellement différent**,
qui l'a réussi.

**(d) n'implique pas (e)** : un module réussi de tous ne dit rien des autres modules.
**(e) n'implique pas (d)** non plus.

*Contre-exemple pour (e) ⇏ (d) :* deux modules M₁, M₂ et deux étudiants A, B ;
A réussit seulement M₁, B seulement M₂. (e) est vraie — chaque module a son
vainqueur — mais (d) est fausse, aucun module n'est réussi par les deux.
MD,
                        'rubric' => [
                            ['label' => 'a. ∀ avec ⇒, ∃ interne avec ∧', 'points' => 1],
                            ['label' => 'b. ∃ avec ∧, ∀ interne avec ⇒', 'points' => 1],
                            ['label' => 'c. formalisation correcte de « aucun »', 'points' => 1],
                            ['label' => 'd. ordre ∃m ∀x respecté', 'points' => 1],
                            ['label' => 'e. ordre ∀m ∃x respecté', 'points' => 1],
                            ['label' => 'Négation poussée jusqu’aux atomes, aucun ¬ devant un quantificateur', 'points' => 1],
                            ['label' => 'Chaque étape de la négation est justifiée par une règle nommée', 'points' => 1],
                            ['label' => 'Q3 : non-équivalence établie par un contre-exemple explicite', 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ==================== Contrats — poids 4 ==================== */
            'Contrats' => [
                'lessons' => [
                    [
                        'title' => 'Précondition, postcondition, invariant, variant',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Un contrat répartit les responsabilités. La **précondition** est ce que l'appelant
doit garantir ; la **postcondition** est ce que la fonction rend en échange.
Si l'appelant ne tient pas sa part, la fonction n'est tenue à rien.

Écrire la spécification **avant** le code n'est pas une formalité : c'est ce qui
oblige à décider ce que la fonction fait exactement, avant de décider comment.
MD,
                        'formalism' => <<<'MD'
**Les quatre clauses**

| Clause | Portée | Rôle |
|---|---|---|
| **Précondition** `requires` | à l'entrée | ce que l'appelant garantit |
| **Postcondition** `ensures` | à la sortie | ce que la fonction garantit |
| **Invariant** `invariant` | boucle | vrai avant, préservé à chaque tour |
| **Variant** `variant` | boucle ou récursion | décroît strictement, prouve la terminaison |

**L'invariant de boucle — les trois obligations**

Un invariant I n'en est un que si les trois conditions sont vérifiées :

1. **Établissement** — I est vrai avant le premier tour.
2. **Préservation** — si I ∧ garde est vrai avant un tour, I est vrai après.
3. **Conclusion** — I ∧ ¬garde entraîne la postcondition.

La troisième est celle qu'on oublie. Un invariant préservé mais qui ne permet pas
de conclure est inutile : il faut souvent y ajouter une **borne** sur le compteur.

**Le variant** doit être une expression :
- à valeurs dans un ensemble **bien fondé** (typiquement ℕ) ;
- **minorée** tant que la garde est vraie ;
- **strictement décroissante** à chaque tour.

Les trois propriétés doivent être énoncées, pas seulement l'expression.
MD,
                        'worked_example' => <<<'MD'
**Recherche du maximum d'un tableau.**

```whyml
let maximum (a: array int) : int
  requires { length a > 0 }
  ensures  { forall i. 0 <= i < length a -> result >= a[i] }
  ensures  { exists i. 0 <= i < length a /\ result = a[i] }
= let ref m = a[0] in
  let ref k = 1 in
  while k < length a do
    invariant { 1 <= k <= length a }
    invariant { forall i. 0 <= i < k -> m >= a[i] }
    invariant { exists i. 0 <= i < k /\ m = a[i] }
    variant   { length a - k }
    if a[k] > m then m <- a[k];
    k <- k + 1
  done;
  m
```

**Pourquoi deux postconditions ?** La première seule serait satisfaite par
`result = +∞`. La seconde impose que le résultat soit **un élément du tableau**.
Une spécification incomplète se prouve très bien — et ne garantit rien.

**Pourquoi trois invariants ?**
- `1 <= k <= length a` : la **borne**, indispensable pour conclure `k = length a`
  à la sortie.
- Les deux autres sont les postconditions **restreintes au préfixe déjà parcouru**.
  C'est la recette générale : *un invariant de boucle est la postcondition, limitée
  à la portion traitée.*

**Le variant.** `length a - k` est entier, minoré par 0 tant que `k < length a`,
et décroît de 1 à chaque tour puisque `k` augmente. La boucle termine.
MD,
                        'pitfalls' => <<<'MD'
- **Spécification incomplète.** « Le maximum est ≥ à tous les éléments » sans
  « et c'est un élément du tableau ». Le programme prouvé peut alors être faux.
- **Oublier la borne dans l'invariant.** Sans `k <= length a`, la sortie de boucle
  ne donne pas `k = length a`, et la conclusion échoue.
- **Confondre invariant et variant.** L'invariant est **préservé**, le variant
  **décroît**. L'un prouve la correction, l'autre la terminaison.
- **Donner le variant sans ses trois propriétés.** Il faut dire qu'il est entier,
  minoré, et strictement décroissant.
- **Précondition trop faible.** `length a > 0` est indispensable : sur un tableau
  vide, `a[0]` n'existe pas.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] La **précondition** couvre tous les cas où le code planterait.
- [ ] La **postcondition** est **complète** : elle exclut les résultats absurdes.
- [ ] L'invariant comporte la **borne** sur le compteur.
- [ ] Les **trois obligations** de l'invariant sont vérifiées explicitement.
- [ ] Le variant est donné **avec** minoration et décroissance stricte.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => 'Les trois obligations d’un invariant de boucle ?',
                        'back' => "1. **Établissement** — vrai avant le premier tour.\n2. **Préservation** — I ∧ garde avant ⟹ I après.\n3. **Conclusion** — I ∧ ¬garde ⟹ postcondition.\n\nLa troisième est celle qu'on oublie : c'est elle qui exige souvent une **borne** sur le compteur.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Comment trouver un invariant de boucle ? La recette générale.',
                        'back' => "**C'est la postcondition, restreinte à la portion déjà traitée.**\n\nPostcondition : « m ≥ a[i] pour tout i < length a ».\nInvariant : « m ≥ a[i] pour tout i < **k** ».\n\nPlus la **borne** sur le compteur : `0 ≤ k ≤ length a`.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Les trois propriétés que doit vérifier un variant ?',
                        'back' => "1. À valeurs dans un ensemble **bien fondé** (typiquement ℕ).\n2. **Minoré** tant que la garde est vraie.\n3. **Strictement décroissant** à chaque tour.\n\nLes énoncer fait partie de la réponse : donner l'expression seule ne suffit pas.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Spécifier `maximum` par « result ≥ a[i] pour tout i » : que manque-t-il ?",
                        'back' => "**Que le résultat soit un élément du tableau.**\n\nSans `exists i. result = a[i]`, la valeur `+∞` satisfait la spécification. Une spécification incomplète se prouve très bien et ne garantit rien.",
                        'difficulty' => 5,
                    ],
                ],
            ],

            /* ==================== Recur — poids 4 ==================== */
            'Recur' => [
                'lessons' => [
                    [
                        'title' => 'Récurrence simple, forte, et choix de la propriété',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Une preuve par récurrence a toujours la même charpente : un **cas de base**, une
**hypothèse**, un **pas**. La difficulté n'est jamais la charpente. C'est de choisir
**la bonne propriété P(n)**.

Une propriété trop faible ne se propage pas ; il faut alors la **renforcer** — la
rendre plus forte, ce qui donne une hypothèse plus riche au moment du pas. C'est
contre-intuitif et c'est le cœur du chapitre.
MD,
                        'formalism' => <<<'MD'
**Récurrence simple**

```
P(0)                        cas de base
∀n, P(n) ⇒ P(n+1)          pas
———————————————————
∀n ∈ ℕ, P(n)
```

**Récurrence forte** — l'hypothèse porte sur **tous** les rangs inférieurs :

```
∀n, ( ∀k < n, P(k) ) ⇒ P(n)
————————————————————————————
∀n ∈ ℕ, P(n)
```

Le cas de base y est inclus : pour n = 0, l'hypothèse `∀k < 0` est vide, donc
vraie. La récurrence forte est indispensable dès que P(n) dépend de rangs autres
que n−1 — suites définies par `u(n) = u(n/2) + …`, décompositions en facteurs
premiers, algorithmes « diviser pour régner ».

**La rédaction attendue, en quatre temps :**

1. **Énoncer P(n)** — « Soit P(n) la propriété : … ». Explicitement, en toutes lettres.
2. **Cas de base** — vérifier P(0), ou P(n₀).
3. **Hypothèse de récurrence** — « Supposons P(n) vraie pour un n fixé. »
4. **Pas** — démontrer P(n+1), **en signalant où l'hypothèse est utilisée**.
5. **Conclusion** — « Par récurrence, P(n) est vraie pour tout n ∈ ℕ. »
MD,
                        'worked_example' => <<<'MD'
**Le renforcement, sur un exemple.**

*À démontrer :* la suite `u(0) = 1`, `u(n+1) = u(n)/2 + 1` vérifie `u(n) < 3`.

**Tentative naïve.** P(n) : `u(n) < 3`.
Hypothèse : `u(n) < 3`. Alors `u(n+1) = u(n)/2 + 1 < 3/2 + 1 = 2,5 < 3`. ✓

Ici cela passe. Prenons un cas où cela échoue.

*À démontrer :* la suite `v(0) = 0`, `v(n+1) = (v(n) + 4)/2` converge et reste
majorée par 4.

**Tentative naïve.** P(n) : `v(n) ≤ 4`.
Hypothèse : `v(n) ≤ 4`. Alors `v(n+1) = (v(n)+4)/2 ≤ 8/2 = 4`. ✓ — cela passe aussi.

**Le vrai cas de renforcement : la croissance.**

*À démontrer :* `v` est croissante.
P(n) : `v(n) ≤ v(n+1)`. Hypothèse : `v(n) ≤ v(n+1)`.
Alors `v(n+1) = (v(n)+4)/2 ≤ (v(n+1)+4)/2 = v(n+2)`. ✓

Mais si l'on avait voulu prouver directement `v(n) ≤ 4` **et** la croissance
séparément, chaque preuve aurait été plus fragile. **Renforcer P(n) en
« v(n) ≤ v(n+1) ≤ 4 »** donne les deux d'un coup, et chaque moitié sert à l'autre
dans le pas.

**Récurrence forte — exemple canonique.**

*Tout entier n ≥ 2 admet un diviseur premier.*

P(n) : « n admet un diviseur premier ».

Hypothèse forte : supposons P(k) vraie pour tout `2 ≤ k < n`.

- Si n est premier, il est son propre diviseur premier. ✓
- Sinon, `n = a · b` avec `2 ≤ a < n`. **Par hypothèse forte appliquée à a**,
  a admet un diviseur premier p. Alors `p | a` et `a | n`, donc `p | n`. ✓

Une récurrence **simple** ne suffirait pas : on a besoin de P(a) pour un `a`
quelconque inférieur à n, pas seulement de P(n−1).
MD,
                        'pitfalls' => <<<'MD'
- **Ne pas énoncer P(n).** « Montrons par récurrence que… » sans définir la propriété
  rend la suite illisible. Le correcteur ne peut pas suivre.
- **Ne pas signaler où l'hypothèse sert.** Écrivez « par hypothèse de récurrence ».
  Une preuve où l'hypothèse n'apparaît jamais n'est pas une récurrence.
- **Utiliser une récurrence simple là où il faut une forte.** Dès que le pas invoque
  un rang autre que n−1, la simple ne suffit pas.
- **Oublier le cas de base**, ou le vérifier au mauvais rang.
- **Ne pas renforcer** quand l'hypothèse est trop faible pour conclure — et s'acharner
  sur le calcul au lieu de changer P(n).
MD,
                        'examiner_expects' => <<<'MD'
- [ ] **« Soit P(n) la propriété : … »** en toutes lettres.
- [ ] Le **cas de base** vérifié explicitement.
- [ ] L'**hypothèse** énoncée, et **la mention « par hypothèse de récurrence »** à
      l'endroit exact où elle sert.
- [ ] La **conclusion** rédigée.
- [ ] Si récurrence forte : le dire, et justifier pourquoi la simple ne suffit pas.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Récurrence simple ou forte : quand faut-il la forte ?',
                        'back' => "**Dès que le pas invoque un rang autre que n−1.**\n\nSimple : `P(n) ⇒ P(n+1)`.\nForte : `(∀k < n, P(k)) ⇒ P(n)`.\n\nCas typiques : décomposition en facteurs premiers, suites `u(n/2)`, diviser-pour-régner.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Les cinq temps d’une preuve par récurrence ?',
                        'back' => "1. **Énoncer P(n)** en toutes lettres.\n2. **Cas de base**.\n3. **Hypothèse** de récurrence.\n4. **Pas**, en signalant « par hypothèse de récurrence ».\n5. **Conclusion**.\n\nUne preuve où l'hypothèse n'apparaît jamais n'est pas une récurrence.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Votre hypothèse de récurrence est trop faible pour conclure. Que faire ?",
                        'back' => "**Renforcer P(n)** — la rendre **plus forte**.\n\nC'est contre-intuitif : une propriété plus forte à démontrer donne aussi une **hypothèse plus riche** au moment du pas. S'acharner sur le calcul ne sert à rien.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Pourquoi la récurrence forte n’a-t-elle pas besoin de cas de base séparé ?',
                        'back' => "Parce que pour **n = 0**, l'hypothèse `∀k < 0, P(k)` porte sur un ensemble **vide** : elle est donc vraie.\n\nLe cas de base est absorbé par le pas.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ==================== Induction — poids 4 ==================== */
            'Induction' => [
                'lessons' => [
                    [
                        'title' => 'Induction structurelle sur listes et arbres',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
La récurrence sur ℕ est un cas particulier. Le principe général est l'**induction
structurelle** : dès qu'un ensemble est défini par des **constructeurs**, on peut
raisonner dessus.

La règle est mécanique : **un cas de preuve par constructeur**. Les constructeurs
sans argument récursif donnent les cas de base ; ceux qui prennent des sous-structures
donnent les cas inductifs, avec une hypothèse par sous-structure.

Il n'y a donc rien à deviner sur la forme de la preuve : elle est dictée par la
définition du type.
MD,
                        'formalism' => <<<'MD'
**Listes**

```
type liste = Nil | Cons of int * liste
```

Deux constructeurs, donc **deux cas** :

```
P(Nil)
∀x, ∀l,  P(l) ⇒ P(Cons(x, l))
——————————————————————————————
∀l, P(l)
```

**Arbres binaires**

```
type arbre = Feuille | Noeud of arbre * int * arbre
```

Deux constructeurs, mais le second a **deux** sous-arbres, donc **deux hypothèses** :

```
P(Feuille)
∀g, ∀x, ∀d,  P(g) ∧ P(d) ⇒ P(Noeud(g, x, d))
—————————————————————————————————————————————
∀a, P(a)
```

**Le principe général.** Pour un type inductif à constructeurs `C₁, …, Cₖ` :
un cas par constructeur ; dans le cas de `Cᵢ`, on dispose de l'hypothèse P pour
**chaque argument de type inductif** de `Cᵢ`.
MD,
                        'worked_example' => <<<'MD'
**Démontrer que `longueur (concat l1 l2) = longueur l1 + longueur l2`.**

Définitions :

```
longueur Nil          = 0
longueur (Cons(x, l)) = 1 + longueur l

concat Nil          l2 = l2
concat (Cons(x, l)) l2 = Cons(x, concat l l2)
```

**Propriété.** Soit P(l₁) : `∀l₂, longueur (concat l₁ l₂) = longueur l₁ + longueur l₂`.

*Remarquez la quantification sur l₂ à l'intérieur de P.* L'induction porte sur `l₁`
seul — c'est sur lui que les deux fonctions filtrent.

**Cas de base : l₁ = Nil.**

```
longueur (concat Nil l₂)
  = longueur l₂                      [définition de concat, cas Nil]
  = 0 + longueur l₂                  [arithmétique]
  = longueur Nil + longueur l₂       [définition de longueur, cas Nil]
```
✓

**Cas inductif : l₁ = Cons(x, l).**

*Hypothèse d'induction :* `∀l₂, longueur (concat l l₂) = longueur l + longueur l₂`.

```
longueur (concat (Cons(x,l)) l₂)
  = longueur (Cons(x, concat l l₂))          [déf. concat, cas Cons]
  = 1 + longueur (concat l l₂)               [déf. longueur, cas Cons]
  = 1 + (longueur l + longueur l₂)           [par hypothèse d'induction]
  = (1 + longueur l) + longueur l₂           [associativité]
  = longueur (Cons(x,l)) + longueur l₂       [déf. longueur, cas Cons]
```
✓

**Conclusion.** Par induction structurelle sur l₁, la propriété est vraie pour
toute liste l₁ et toute liste l₂. ∎

**Ce qui rapporte les points :** chaque égalité est **justifiée en marge** par la
définition ou la règle employée, et l'endroit où sert l'hypothèse d'induction est
signalé explicitement.
MD,
                        'pitfalls' => <<<'MD'
- **Oublier un constructeur.** Un cas par constructeur, sans exception.
- **Oublier une hypothèse.** Sur `Noeud(g, x, d)`, on dispose de **deux** hypothèses,
  P(g) **et** P(d).
- **Choisir la mauvaise variable d'induction.** On raisonne sur celle sur laquelle
  la fonction **filtre**. Pour `concat`, c'est le premier argument.
- **Ne pas quantifier l'autre variable dans P.** Écrire P(l₁) avec `∀l₂` à l'intérieur
  donne une hypothèse bien plus forte, souvent nécessaire.
- **Ne pas justifier les égalités.** Chaque ligne doit porter, en marge, la définition
  ou la règle appliquée.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] **Un cas par constructeur**, tous traités.
- [ ] Une **hypothèse par sous-structure** dans les cas inductifs.
- [ ] Chaque égalité **justifiée en marge** par la définition employée.
- [ ] La mention explicite de **« par hypothèse d'induction »** à l'endroit où elle sert.
- [ ] Une **conclusion** rédigée.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => 'Combien de cas dans une preuve par induction structurelle ?',
                        'back' => "**Un par constructeur du type.**\n\n`liste = Nil | Cons` → 2 cas.\n`arbre = Feuille | Noeud(g, x, d)` → 2 cas, mais le second avec **deux** hypothèses, P(g) et P(d).",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Sur quelle variable faire l'induction quand la propriété en comporte deux ?",
                        'back' => "**Sur celle sur laquelle la fonction filtre.**\n\nPour `concat l1 l2`, les deux définitions filtrent sur `l1` : c'est donc sur `l1`. L'autre variable est **quantifiée universellement à l'intérieur de P**, ce qui donne une hypothèse plus forte.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Principe d’induction sur les arbres binaires ?',
                        'back' => "```\nP(Feuille)\n∀g,x,d :  P(g) ∧ P(d) ⇒ P(Noeud(g,x,d))\n———————————————————————————————————\n∀a, P(a)\n```\n\n**Deux** hypothèses dans le cas inductif — une par sous-arbre.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ============ Fiches courtes : Theories, Types, Calculs ============ */
            'Theories' => [
                'lessons' => [
                    [
                        'title' => 'Théories, modèles, décidabilité',
                        'est_minutes' => 12,
                        'intuition' => <<<'MD'
Une **théorie** fixe le sens des symboles. Sans elle, `+` n'est qu'un symbole binaire :
rien ne dit qu'il est commutatif. C'est l'axiomatisation qui le décide.

Les solveurs SMT qu'utilise Why3 sont des démonstrateurs **modulo théories** :
ils raisonnent dans l'arithmétique linéaire, la théorie des tableaux, celle des
listes… et c'est ce qui leur permet de décharger les obligations de preuve
automatiquement.
MD,
                        'formalism' => <<<'MD'
- **Signature** — les symboles disponibles : constantes, fonctions, prédicats,
  avec leur arité.
- **Théorie** — un ensemble de formules closes (les **axiomes**) sur cette signature.
- **Modèle** — une interprétation qui rend tous les axiomes vrais.
- **Conséquence sémantique** `T ⊨ φ` — φ est vraie dans **tous** les modèles de T.
- **Théorie cohérente** — elle admet au moins un modèle. Une théorie incohérente
  démontre tout.
- **Théorie complète** — pour toute formule close φ, `T ⊨ φ` ou `T ⊨ ¬φ`.
- **Théorie décidable** — il existe un algorithme qui décide `T ⊨ φ`.

**Exemples du cours :** l'arithmétique de Presburger (entiers, `+`, `<`, sans
multiplication) est **décidable** ; l'arithmétique de Peano complète, avec la
multiplication, ne l'est pas.
MD,
                        'worked_example' => <<<'MD'
Dans Why3, `use int.Int` importe la théorie des entiers : elle apporte les axiomes
qui rendent `+`, `*` et `<` conformes à leur sens usuel.

Sans cet import, une obligation aussi simple que `x + 0 = x` ne se démontre pas :
rien n'a dit que `0` est neutre pour `+`.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre cohérence et complétude.** Cohérente = admet un modèle.
  Complète = tranche toute formule close.
- **Croire qu'indécidable signifie incohérente.** Peano est cohérente et indécidable.
MD,
                        'examiner_expects' => <<<'MD'
Les définitions exactes de signature, théorie, modèle, et les trois propriétés —
cohérence, complétude, décidabilité — soigneusement distinguées.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Théorie cohérente, complète, décidable : trois notions distinctes ?',
                        'back' => "**Cohérente** — admet au moins un modèle.\n**Complète** — pour toute formule close φ, tranche entre φ et ¬φ.\n**Décidable** — un algorithme décide `T ⊨ φ`.\n\nPeano est **cohérente** mais ni complète ni décidable.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que signifie `T ⊨ φ` ?',
                        'back' => "**φ est vraie dans tous les modèles de T** — conséquence sémantique.\n\nÀ distinguer de `T ⊢ φ` : φ est **dérivable** dans un système déductif.",
                    ],
                ],
            ],

            'Types' => [
                'lessons' => [
                    [
                        'title' => 'Types inductifs et filtrage',
                        'est_minutes' => 12,
                        'intuition' => <<<'MD'
Un type inductif se définit par la liste **exhaustive** de ses constructeurs.
Tout élément du type s'obtient en appliquant un nombre fini de constructeurs —
ni plus, ni moins.

Cette clôture est ce qui autorise le raisonnement par induction structurelle :
puisqu'il n'y a pas d'autre façon de fabriquer un élément, traiter tous les
constructeurs, c'est tout traiter.
MD,
                        'formalism' => <<<'MD'
```whyml
type liste 'a = Nil | Cons 'a (liste 'a)

type arbre 'a = Feuille | Noeud (arbre 'a) 'a (arbre 'a)

type expr =
  | Const int
  | Plus expr expr
  | Mult expr expr
```

**Trois propriétés garanties :**

1. **Exhaustivité** — tout élément vient d'un constructeur.
2. **Disjonction** — deux constructeurs distincts produisent des valeurs distinctes.
3. **Injectivité** — `Cons(x, l) = Cons(y, m)` implique `x = y` et `l = m`.

Ces trois propriétés fondent l'induction et le filtrage.

**Le filtrage** doit couvrir **tous** les constructeurs, sinon la fonction est
partielle et l'obligation de preuve échoue :

```whyml
let rec taille (a: arbre 'a) : int =
  match a with
  | Feuille        -> 0
  | Noeud g _ d    -> 1 + taille g + taille d
  end
```
MD,
                        'worked_example' => <<<'MD'
Un évaluateur d'expressions, avec un cas par constructeur :

```whyml
let rec eval (e: expr) : int =
  match e with
  | Const n     -> n
  | Plus a b    -> eval a + eval b
  | Mult a b    -> eval a * eval b
  end
```

La structure du `match` est **exactement** celle de la définition du type — et donc
exactement celle de la preuve par induction qui suivra.
MD,
                        'pitfalls' => <<<'MD'
- **Filtrage non exhaustif.** Un constructeur oublié rend la fonction partielle.
- **Oublier la variance des paramètres de type.** `liste 'a` est paramétré :
  la définition vaut pour tout `'a`.
MD,
                        'examiner_expects' => <<<'MD'
Une définition de type avec **tous** ses constructeurs et leurs arités, et un
filtrage **exhaustif** dans chaque fonction qui l'exploite.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Les trois propriétés garanties par un type inductif ?',
                        'back' => "1. **Exhaustivité** — tout élément vient d'un constructeur.\n2. **Disjonction** — deux constructeurs distincts donnent des valeurs distinctes.\n3. **Injectivité** — `Cons(x,l) = Cons(y,m)` ⟹ `x=y` et `l=m`.\n\nElles fondent l'induction structurelle et le filtrage.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            'Calculs' => [
                'lessons' => [
                    [
                        'title' => 'Fonctions récursives et terminaison',
                        'est_minutes' => 12,
                        'intuition' => <<<'MD'
Une fonction récursive sur un type inductif suit la structure de ce type : un cas
par constructeur, et un appel récursif sur chaque sous-structure.

Cette discipline garantit la terminaison **gratuitement** : les sous-structures sont
strictement plus petites, et un type inductif n'a pas d'élément infini. Dès qu'on
s'en écarte, il faut fournir un **variant**.
MD,
                        'formalism' => <<<'MD'
**Récursion structurelle** — l'appel récursif porte sur un argument
**syntaxiquement plus petit**. La terminaison est automatique.

```whyml
let rec somme (l: liste int) : int =
  match l with
  | Nil       -> 0
  | Cons x r  -> x + somme r     (* r est un sous-terme de l *)
  end
```

**Récursion générale** — l'argument ne décroît pas structurellement. Il faut un
variant explicite :

```whyml
let rec pgcd (a b: int) : int
  requires { a >= 0 /\ b >= 0 }
  variant  { b }                 (* b décroît strictement *)
= if b = 0 then a else pgcd b (mod a b)
```

`mod a b < b` quand `b > 0` : le variant décroît, minoré par 0. La fonction termine.
MD,
                        'worked_example' => <<<'MD'
**Deux définitions de la même fonction, deux coûts.**

```whyml
(* naïve : O(n) en pile *)
let rec longueur (l: liste 'a) : int =
  match l with
  | Nil      -> 0
  | Cons _ r -> 1 + longueur r
  end

(* récursive terminale : O(1) en pile *)
let rec longueurAcc (l: liste 'a) (acc: int) : int
  variant { l }
= match l with
  | Nil      -> acc
  | Cons _ r -> longueurAcc r (acc + 1)
  end
```

La seconde est **récursive terminale** : l'appel récursif est la dernière opération,
et le compilateur peut le transformer en boucle. Prouver leur équivalence demande
une induction avec un **invariant sur l'accumulateur** :

`∀l, ∀acc, longueurAcc l acc = longueur l + acc`

C'est encore le renforcement de propriété : quantifier `acc` à l'intérieur de P
donne l'hypothèse dont on a besoin.
MD,
                        'pitfalls' => <<<'MD'
- **Omettre le variant** sur une récursion non structurelle : la terminaison n'est
  plus prouvée.
- **Oublier de quantifier l'accumulateur** dans la propriété d'induction. Sans
  `∀acc`, l'hypothèse est trop faible.
MD,
                        'examiner_expects' => <<<'MD'
Un cas par constructeur, un **variant** dès que la récursion n'est pas structurelle,
et pour une fonction à accumulateur, une propriété **quantifiée sur l'accumulateur**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Pourquoi la récursion structurelle termine-t-elle sans variant ?',
                        'back' => "Parce que l'appel récursif porte sur un **sous-terme syntaxique** strictement plus petit, et qu'un type inductif n'admet **aucun élément infini**.\n\nDès qu'on s'écarte de ce schéma, il faut un **variant** explicite.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Prouver `longueurAcc l acc = longueur l`. Pourquoi cet énoncé échoue-t-il ?",
                        'back' => "Parce qu'il est **faux** : `longueurAcc l acc = longueur l + acc`.\n\nEt il faut **quantifier acc à l'intérieur de P** : `∀l, ∀acc, …`. Sans cela, l'hypothèse d'induction est trop faible pour le pas.",
                        'difficulty' => 5,
                    ],
                ],
            ],
        ];
    }
}