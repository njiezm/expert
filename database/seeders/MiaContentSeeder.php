<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\MockExamQuestion;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu de MIA — 3,34/20 (5 sur 30), épreuve du 28 août.
 *
 * Les priorités sortent de la matrice examens/chapitres fournie par les
 * enseignants, qui recense les épreuves de 2010-2011 à 2025-2026 : les
 * chapitres 0 (Prolog), 2, 4 (contraintes) et 8 (apprentissage) reviennent
 * dans la quasi-totalité des sujets ; le chapitre 6 (jeux) n'y figure jamais.
 *
 * Le devoir corrigé montre le moule des exercices : un même problème résolu
 * successivement en Générer & Tester, en backtracking, puis en PLC.
 */
class MiaContentSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'MIA')->first();

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

            /* ============================ Ch0 — Prolog ============================ */
            'Ch0' => [
                'lessons' => [
                    [
                        'title' => 'Faits, règles, requêtes, unification',
                        'est_minutes' => 20,
                        'intuition' => <<<'MD'
En mai, l'exercice 1 portait sur des faits Prolog très simples — `lundi(fabrice).`,
`cuisine(xavier, plat).` — et la partie a été notée **1,25 sur 6**. Le correcteur a
mis un « ? » en marge d'une réponse d'unification.

Vos faits étaient bien posés. Ce sont les **résultats de requête** qui étaient
incomplets. C'est un problème de méthode, pas de connaissance : on ne devine pas
la réponse d'une requête, on déroule la résolution.
MD,
                        'formalism' => <<<'MD'
**Les trois briques.**

Un **fait** est une clause sans corps, terminée par un point :
```prolog
lundi(fabrice).
cuisine(xavier, plat).
```

Une **règle** est une clause à corps. `:-` se lit « si », la virgule « et » :
```prolog
occupe(X) :- lundi(X), cuisine(X, _).
```

Une **requête** interroge la base. Prolog cherche **toutes** les solutions,
dans l'ordre des clauses :
```prolog
?- mardi(X).
X = antoine ;
X = louis ;
X = xavier.
```

**Les conventions de nommage — la source d'erreur numéro un.**

| Écriture | Nature |
|---|---|
| `fabrice`, `plat` | atome — commence par une **minuscule** |
| `X`, `Personne`, `_L` | **variable** — commence par une majuscule ou `_` |
| `_` | variable anonyme : « peu importe » |
| `12`, `9.5` | nombre |
| `bung(alsace, 95, 9, 4)` | terme composé |

**L'unification** — le mécanisme central. Deux termes s'unifient si :
1. ce sont deux atomes identiques ;
2. l'un est une variable libre : elle prend la valeur de l'autre ;
3. ce sont deux termes composés de **même foncteur** et **même arité**,
   et leurs arguments s'unifient deux à deux.

`cuisine(X, plat)` s'unifie avec `cuisine(xavier, plat)` en liant `X = xavier`.
Il **ne s'unifie pas** avec `cuisine(louis, pain)` : `plat ≠ pain`.
MD,
                        'worked_example' => <<<'MD'
**La base de l'exercice de mai :**

```prolog
% les jours
lundi(fabrice).
mardi(antoine).
mardi(louis).
mardi(xavier).

% la cuisine
cuisine(xavier, plat).
cuisine(louis, pain).
```

**Requête 1 :** `?- lundi(X).`

Prolog parcourt les clauses `lundi/1`. Une seule correspond.
```
X = fabrice.
```

**Requête 2 :** `?- mardi(X).`

Trois clauses `mardi/1` correspondent, dans l'ordre du fichier :
```
X = antoine ;
X = louis ;
X = xavier.
```

**Requête 3 :** `?- cuisine(X, entree).`

Aucun fait `cuisine/2` n'a `entree` en second argument.
```
false.
```
C'est ce type de réponse qu'on oublie d'écrire — et elle vaut un point comme une autre.

**Requête 4 :** `?- cuisine(X, _).`

La variable anonyme accepte n'importe quel plat :
```
X = xavier ;
X = louis.
```

**Méthode à appliquer systématiquement.** Pour chaque requête :
1. repérer le **foncteur et l'arité** cherchés (`cuisine/2`) ;
2. balayer les clauses **dans l'ordre du fichier** ;
3. tenter l'unification argument par argument ;
4. écrire **toutes** les solutions, séparées par `;`, ou `false.` s'il n'y en a aucune.
MD,
                        'pitfalls' => <<<'MD'
- **Ne donner qu'une solution quand il y en a plusieurs.** Prolog les énumère toutes ;
  votre réponse doit toutes les lister, dans l'ordre des clauses.
- **Oublier `false.`** quand la requête échoue. C'est une réponse, pas une absence de réponse.
- **Écrire une variable en minuscule.** `?- cuisine(x, plat).` demande si l'atome `x`
  cuisine un plat — la réponse est `false`, ce qui n'est pas la question posée.
- **Confondre `=` et `is`.** `X = 3+4` unifie `X` avec le **terme** `3+4` ;
  `X is 3+4` **évalue** et donne `X = 7`.
- **Confondre `\=` et `#\=`.** Le premier est la non-unifiabilité Prolog,
  le second la contrainte d'inégalité de CLP(FD).
MD,
                        'examiner_expects' => <<<'MD'
Pour chaque requête, la **liste complète** des réponses, dans l'ordre de parcours
des clauses, terminée par un point. Avec `;` entre les solutions multiples.

Et `false.` quand il n'y en a aucune — l'omettre, c'est laisser la question sans réponse.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'En Prolog, `fabrice` et `Fabrice` : quelle différence ?',
                        'back' => "**`fabrice`** (minuscule) est un **atome** — une valeur constante.\n\n**`Fabrice`** (majuscule) est une **variable** — un trou à remplir.\n\nÉcrire une variable en minuscule change complètement le sens de la requête.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => '`X = 3+4` contre `X is 3+4` ?',
                        'back' => "**`X = 3+4`** → `X = 3+4` : unification avec le **terme** non évalué.\n\n**`X is 3+4`** → `X = 7` : `is` **évalue** l'expression arithmétique.\n\nUne erreur classique : `X = X+1` échoue toujours (occurs check).",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Base : `mardi(antoine). mardi(louis). mardi(xavier).` Que répond `?- mardi(X).` ?",
                        'back' => "```\nX = antoine ;\nX = louis ;\nX = xavier.\n```\n\n**Toutes** les solutions, dans l'ordre des clauses. N'en donner qu'une est la faute qui a coûté des points en mai.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Base : `cuisine(xavier, plat). cuisine(louis, pain).` Que répond `?- cuisine(X, entree).` ?",
                        'back' => "```\nfalse.\n```\n\nAucun fait n'a `entree` en second argument. **`false.` est une réponse** : ne pas l'écrire revient à laisser la question vide.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'À quelles conditions deux termes composés s’unifient-ils ?',
                        'back' => "**Même foncteur, même arité**, et les arguments s'unifient **deux à deux**.\n\n`cuisine(X, plat)` ∪ `cuisine(xavier, plat)` → `X = xavier`.\n`cuisine(X, plat)` ∪ `cuisine(louis, pain)` → échec, `plat ≠ pain`.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'À quoi sert la variable anonyme `_` ?',
                        'back' => "Elle unifie avec **n'importe quoi** et sa valeur n'est pas retournée.\n\n`?- cuisine(X, _).` demande qui cuisine, sans se soucier du plat.\n\nDeux `_` dans la même clause sont **deux variables distinctes**.",
                    ],
                ],
                'exercises' => [
                    [
                        'title' => "Exercice 1 de l'épreuve de mai — à refaire",
                        'origin' => 'annale',
                        'est_minutes' => 25,
                        'difficulty' => 3,
                        'statement' => <<<'MD'
Soit la base de connaissances suivante :

```prolog
% les jours
lundi(fabrice).
mardi(antoine).
mardi(louis).
mardi(xavier).

% la cuisine
cuisine(xavier, plat).
cuisine(louis, pain).
```

**1.** Donnez la réponse complète de Prolog à chacune de ces requêtes :

```prolog
?- lundi(X).
?- mardi(X).
?- cuisine(X, entree).
?- cuisine(X, _).
?- lundi(antoine).
```

**2.** Écrivez une règle `cuisinierDeMardi/1` vraie pour toute personne qui vient
le mardi **et** qui cuisine quelque chose. Donnez ensuite la réponse à
`?- cuisinierDeMardi(X).`

**3.** Écrivez une règle `libre/1` vraie pour toute personne présente un jour de la
semaine **et** qui ne cuisine rien.
MD,
                        'hint' => "Pour chaque requête, balayez les clauses dans l'ordre du fichier et écrivez **toutes** les solutions, séparées par `;`. N'oubliez pas `false.` quand il n'y en a aucune.",
                        'method' => <<<'MD'
1. Repérez le foncteur et l'arité demandés (`cuisine/2`, pas `cuisine/1`).
2. Balayez les clauses de haut en bas.
3. Tentez l'unification argument par argument.
4. Notez chaque solution, puis passez à la clause suivante — Prolog ne s'arrête pas
   à la première.
5. Pour la question 3, souvenez-vous que la négation s'écrit `\+` en Prolog.
MD,
                        'solution' => <<<'MD'
**1.**

```prolog
?- lundi(X).
X = fabrice.

?- mardi(X).
X = antoine ;
X = louis ;
X = xavier.

?- cuisine(X, entree).
false.

?- cuisine(X, _).
X = xavier ;
X = louis.

?- lundi(antoine).
false.
```

**2.**

```prolog
cuisinierDeMardi(X) :- mardi(X), cuisine(X, _).
```

```prolog
?- cuisinierDeMardi(X).
X = louis ;
X = xavier.
```

*Attention à l'ordre :* Prolog essaie `antoine` d'abord — `mardi(antoine)` réussit,
puis `cuisine(antoine, _)` échoue, donc backtracking. Puis `louis` : les deux
réussissent. Puis `xavier` : idem. L'ordre de sortie suit celui des clauses `mardi/1`,
donc `louis` avant `xavier`.

**3.**

```prolog
libre(X) :- lundi(X), \+ cuisine(X, _).
libre(X) :- mardi(X), \+ cuisine(X, _).
```

```prolog
?- libre(X).
X = fabrice ;
X = antoine.
```

`\+` est la négation par échec : `\+ But` réussit si `But` échoue.
MD,
                        'rubric' => [
                            ['label' => '`?- lundi(X).` → X = fabrice.', 'points' => 1],
                            ['label' => '`?- mardi(X).` → les **trois** solutions, dans l’ordre des clauses', 'points' => 2],
                            ['label' => '`?- cuisine(X, entree).` → `false.` explicitement écrit', 'points' => 1],
                            ['label' => '`?- cuisine(X, _).` → xavier puis louis', 'points' => 1],
                            ['label' => '`?- lundi(antoine).` → `false.`', 'points' => 1],
                            ['label' => 'Règle `cuisinierDeMardi/1` correcte, avec la conjonction', 'points' => 2],
                            ['label' => 'Réponse à la requête : louis puis xavier', 'points' => 1],
                            ['label' => 'Règle `libre/1` utilisant la négation `\\+`', 'points' => 2],
                        ],
                    ],
                ],
            ],

            /* ================== Ch4 — Programmation par contraintes ================== */
            'Ch4' => [
                'lessons' => [
                    [
                        'title' => 'Générer & Tester, backtracking, PLC : les trois marches',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
C'est le chapitre le plus régulièrement évalué de tout le module — présent dans la
quasi-totalité des annales depuis 2010. Et l'exercice est toujours le même problème
posé trois fois, de trois façons de plus en plus fines.

L'énoncé est une énigme logique : cinq bungalows, cinq régions, cinq horaires,
cinq familles, une douzaine de phrases de contraintes. On demande de la résoudre :

1. en **Générer & Tester** — on énumère tout, puis on filtre ;
2. en **backtracking** — on réordonne pour échouer au plus tôt ;
3. en **PLC** — on pose les contraintes, le solveur propage.

Comprendre pourquoi la troisième est meilleure que la première, c'est comprendre
tout le chapitre.
MD,
                        'formalism' => <<<'MD'
**Marche 1 — Générer & Tester.** On génère une affectation complète, on la teste,
on recommence si elle échoue.

```prolog
elementMembre([], _).
elementMembre([X|L1], L2) :-
    select(X, L2, L3),
    elementMembre(L1, L3).
```

`elementMembre/2` affecte à chaque variable une valeur distincte du domaine.
Puis viennent les tests. Coût : le produit des tailles de domaines.

**Marche 2 — Backtracking, « échouer au plus tôt ».** Même code, mais on **remonte
chaque test juste après la génération des variables qu'il concerne**. Une branche
condamnée est coupée dès que possible, au lieu d'être menée jusqu'au bout.

C'est un réordonnancement, pas un changement d'algorithme. C'est exactement ce que
demande la question 2 des devoirs.

**Marche 3 — PLC, programmation logique avec contraintes.** On déclare les domaines
et les contraintes, le solveur propage avant d'énumérer.

```prolog
:- use_module(library(clpfd)).

Heures = [HA, HB, HC, HL, HP],
Heures ins {95, 110, 130, 150, 165},
all_distinct(Heures),
HB #= H_F4 + 20,
HB #< HP,
FP #\= 3,
...
labeling([], ToutesVariables).
```

**Le vocabulaire CLP(FD) à connaître par cœur :**

| Écriture | Sens |
|---|---|
| `X ins 1..9` / `L ins {2,4,6}` | déclaration de **domaine** |
| `#=` `#\=` `#<` `#>` `#=<` `#>=` | contraintes arithmétiques |
| `all_distinct(L)` | toutes les variables de `L` sont deux à deux différentes |
| `all_different(L)` | idem, propagation plus faible |
| `element(I, Liste, V)` | `V` est le `I`-ième élément de `Liste` |
| `labeling([], L)` | **valuation** : énumère les solutions restantes |
| `sum(L, #=, S)` | la somme des variables de `L` vaut `S` |

**Sans `labeling`, rien ne sort.** Les contraintes réduisent les domaines ;
c'est la valuation qui produit les solutions.
MD,
                        'worked_example' => <<<'MD'
**Traduire des phrases en contraintes** — c'est là que se gagnent les points.

| Phrase de l'énoncé | Générer & Tester | PLC |
|---|---|---|
| « le bungalow de Bourgogne est 2 h après celui de la famille de 4 » | `H2 is Heure1 + 20` | `HB #= H_F4 + 20` |
| « la Bourgogne passe avant la Picardie » | `H2 < H5` | `HB #< HP` |
| « la Picardie est le bungalow n° 9 » | `N5 is 9` | `NP #= 9` |
| « la Picardie n'est pas la famille de 3 » | `F5 \= 3` | `FP #\= 3` |
| « ce n'est ni l'Alsace ni la Bourgogne » | `X \= alsace, X \= bourgogne` | idem (atomes, pas des entiers) |
| « les cinq horaires sont différents » | `elementMembre(...)` | `all_distinct(Heures)` |

**Deux astuces que le corrigé emploie et qu'il faut reprendre :**

*Convertir les horaires en entiers.* `9h30` devient `95`, `11h00` devient `110`.
CLP(FD) ne travaille que sur des entiers. On peut aussi tout passer en minutes
(`9h30 = 570`), mais la première convention est celle du corrigé.

*Utiliser `element/3` pour les positions.* Quand une contrainte porte sur le **rang**
et non sur la valeur — « le bungalow juste avant celui d'Alsace » — on indexe :

```prolog
HeuresClassees = [95, 110, 130, 150, 165],
element(Ind_HA,   HeuresClassees, HA),
element(Ind_Reg5, HeuresClassees, H_Reg5),
Ind_Reg5 #= Ind_HA - 1.
```
MD,
                        'pitfalls' => <<<'MD'
- **Oublier `labeling/2`.** Les contraintes sont posées, les domaines réduits… et rien
  ne s'affiche. C'est l'erreur qui coûte le plus cher, parce qu'elle donne l'impression
  que le programme est faux alors qu'il lui manque une ligne.
- **Oublier `:- use_module(library(clpfd)).`** Sans elle, `#=` n'existe pas.
- **Mélanger `is` et `#=`.** `is` évalue immédiatement et exige que la droite soit
  entièrement connue ; `#=` pose une contrainte qui peut se propager dans les deux sens.
  En PLC, on écrit `#=`.
- **Mélanger `\=` et `#\=`.** Sur des atomes (`alsace`), c'est `\=`.
  Sur des variables entières contraintes, c'est `#\=`.
- **Répondre « Générer & Tester » à la question sur le backtracking.** La question 2
  demande un **réordonnancement** : les tests doivent remonter au plus près de la
  génération des variables qu'ils concernent. Sans réordonnancement, aucun point.
MD,
                        'examiner_expects' => <<<'MD'
Pour les trois questions du même problème :

- [ ] **Q1 Générer & Tester** : le prédicat de génération (`elementMembre/2` ou
      `select/3`), puis **tous** les tests, dans un ordre quelconque.
- [ ] **Q2 Backtracking** : le **même** code, avec chaque test **remonté** juste après
      la génération des variables qu'il utilise. Commentez le réordonnancement.
- [ ] **Q3 PLC** : `use_module(library(clpfd))`, déclaration des domaines avec `ins`,
      `all_distinct`, les contraintes en `#=` / `#\=` / `#<`, et **`labeling/2` à la fin**.
- [ ] Chaque phrase de l'énoncé doit se retrouver, **numérotée en commentaire**
      (`% Phrase 5`). Le corrigé officiel le fait, et cela permet au correcteur de
      cocher ligne à ligne.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => "Votre programme PLC pose tous les domaines et toutes les contraintes, mais n'affiche aucune solution. Que manque-t-il ?",
                        'back' => "**`labeling([], Variables).`**\n\nLes contraintes réduisent les domaines ; c'est la **valuation** qui énumère les solutions. Sans elle, rien ne sort.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'CLP(FD) : comment déclarer un domaine ?',
                        'back' => "```prolog\nX ins 1..9              % intervalle\nL ins {95,110,130,150}  % ensemble explicite\n```\n\nEt ne pas oublier :\n```prolog\n:- use_module(library(clpfd)).\n```",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'En PLC, faut-il écrire `X is Y + 20` ou `X #= Y + 20` ?',
                        'back' => "**`X #= Y + 20`**\n\n`is` **évalue** et exige que la droite soit entièrement connue. `#=` pose une **contrainte** qui se propage dans les deux sens : elle peut déduire `Y` à partir de `X`.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que fait `all_distinct(L)` ? En quoi diffère-t-il de `all_different(L)` ?',
                        'back' => "Les deux imposent que les variables de `L` soient **deux à deux différentes**.\n\n`all_distinct/1` a une **propagation plus forte** (consistance globale), donc élague davantage. `all_different/1` est plus rapide mais moins puissant.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Question 2 d'un devoir MIA : « réécrire en backtracking ». Qu'attend-on exactement ?",
                        'back' => "**Un réordonnancement pour échouer au plus tôt.**\n\nChaque test remonte juste après la génération des variables qu'il concerne, au lieu d'être groupé à la fin. Le code reste le même — seul l'ordre change.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Une contrainte porte sur le **rang** et non sur la valeur (« juste avant »). Quel prédicat ?',
                        'back' => "**`element(Index, Liste, Valeur)`**\n\n```prolog\nHeures = [95,110,130,150,165],\nelement(I1, Heures, HA),\nelement(I2, Heures, HB),\nI2 #= I1 - 1.\n```",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => '`\\=` ou `#\\=` : lequel employer ?',
                        'back' => "**`\\=`** — non-unifiabilité Prolog. Pour les **atomes** : `Region \\= alsace`.\n\n**`#\\=`** — contrainte d'inégalité CLP(FD). Pour les **variables entières contraintes** : `FP #\\= 3`.",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Les cinq ateliers — Générer & Tester, backtracking, PLC',
                        'origin' => 'genere',
                        'est_minutes' => 45,
                        'difficulty' => 4,
                        'statement' => <<<'MD'
Cinq ateliers se tiennent dans un centre de formation : **poterie**, **dessin**,
**cuisine**, **danse**, **théâtre**. Chacun a une salle (1 à 5), un horaire
(9 h, 10 h, 11 h, 14 h, 15 h) et un effectif (6, 8, 10, 12, 15 participants).

Contraintes :

1. Les cinq salles sont différentes, les cinq horaires aussi, les cinq effectifs aussi.
2. La cuisine a lieu deux heures après la poterie.
3. Le dessin est en salle 3.
4. L'atelier de 15 participants n'est pas la danse.
5. Le théâtre a lieu avant la cuisine.
6. L'atelier de salle 1 compte 6 participants.
7. La danse compte plus de participants que le dessin.
8. L'atelier de 9 h est en salle 2.

**Question 1.** Écrivez `ateliersGetT/1` en **Générer & Tester**.
**Question 2.** Réécrivez en `ateliersBck/1` pour **échouer au plus tôt**.
**Question 3.** Écrivez `ateliersPlc/1` en **PLC** avec `library(clpfd)`.

Numérotez chaque contrainte en commentaire, comme dans le corrigé du devoir.
MD,
                        'hint' => "Convertissez les horaires en entiers pour pouvoir écrire « deux heures après » : 9, 10, 11, 14, 15 conviennent tels quels. En PLC, n'oubliez pas `labeling/2` à la fin.",
                        'method' => <<<'MD'
1. **Choisissez la structure de données.** Une liste de termes composés est le plus
   lisible : `atelier(poterie, S1, H1, E1)`, etc.
2. **Q1** — générez d'abord toutes les variables avec `elementMembre/2`, puis posez
   les tests.
3. **Q2** — reprenez le code de Q1 et remontez chaque test juste après la génération
   du groupe de variables qu'il utilise. Les contraintes sur les horaires remontent
   après la génération des horaires, etc.
4. **Q3** — déclarez les domaines avec `ins`, ajoutez `all_distinct`, traduisez chaque
   contrainte en `#=` / `#<` / `#\=`, et terminez par `labeling`.
MD,
                        'solution' => <<<'MD'
**Question 1 — Générer & Tester**

```prolog
:- use_module(library(lists)).

elementMembre([], _).
elementMembre([X|L1], L2) :-
    select(X, L2, L3),
    elementMembre(L1, L3).

ateliersGetT(A) :-
    A = [atelier(poterie, S1, H1, E1), atelier(dessin,  S2, H2, E2),
         atelier(cuisine, S3, H3, E3), atelier(danse,   S4, H4, E4),
         atelier(theatre, S5, H5, E5)],
    % Contrainte 1 : domaines
    elementMembre([S1,S2,S3,S4,S5], [1,2,3,4,5]),
    elementMembre([H1,H2,H3,H4,H5], [9,10,11,14,15]),
    elementMembre([E1,E2,E3,E4,E5], [6,8,10,12,15]),
    % Contrainte 2
    H3 is H1 + 2,
    % Contrainte 3
    S2 =:= 3,
    % Contrainte 4
    E4 =\= 15,
    % Contrainte 5
    H5 < H3,
    % Contrainte 6
    member(atelier(_, 1, _, 6), A),
    % Contrainte 7
    E4 > E2,
    % Contrainte 8
    member(atelier(_, 2, 9, _), A).
```

**Question 2 — Backtracking, échouer au plus tôt**

```prolog
ateliersBck(A) :-
    A = [atelier(poterie, S1, H1, E1), atelier(dessin,  S2, H2, E2),
         atelier(cuisine, S3, H3, E3), atelier(danse,   S4, H4, E4),
         atelier(theatre, S5, H5, E5)],
    % Les salles d'abord : deux contraintes ne dépendent que d'elles
    elementMembre([S1,S2,S3,S4,S5], [1,2,3,4,5]),
    S2 =:= 3,                                   % Contrainte 3
    % Puis les horaires
    elementMembre([H1,H2,H3,H4,H5], [9,10,11,14,15]),
    H3 is H1 + 2,                               % Contrainte 2
    H5 < H3,                                    % Contrainte 5
    member(atelier(_, 2, 9, _), A),             % Contrainte 8
    % Les effectifs en dernier : les contraintes qui les concernent sont les plus tardives
    elementMembre([E1,E2,E3,E4,E5], [6,8,10,12,15]),
    E4 =\= 15,                                  % Contrainte 4
    E4 > E2,                                    % Contrainte 7
    member(atelier(_, 1, _, 6), A).             % Contrainte 6
```

Le gain : `S2 =:= 3` élimine les quatre cinquièmes des affectations de salles
**avant** de générer le moindre horaire.

**Question 3 — PLC**

```prolog
:- use_module(library(clpfd)).

ateliersPlc(A) :-
    A = [atelier(poterie, S1, H1, E1), atelier(dessin,  S2, H2, E2),
         atelier(cuisine, S3, H3, E3), atelier(danse,   S4, H4, E4),
         atelier(theatre, S5, H5, E5)],
    % Contrainte 1 : domaines et distinction
    Salles    = [S1,S2,S3,S4,S5], Salles    ins 1..5,
    Horaires  = [H1,H2,H3,H4,H5], Horaires  ins {9,10,11,14,15},
    Effectifs = [E1,E2,E3,E4,E5], Effectifs ins {6,8,10,12,15},
    all_distinct(Salles), all_distinct(Horaires), all_distinct(Effectifs),
    % Contrainte 2
    H3 #= H1 + 2,
    % Contrainte 3
    S2 #= 3,
    % Contrainte 4
    E4 #\= 15,
    % Contrainte 5
    H5 #< H3,
    % Contrainte 6
    member(atelier(_, 1, _, 6), A),
    % Contrainte 7
    E4 #> E2,
    % Contrainte 8
    member(atelier(_, 2, 9, _), A),
    % Valuation — sans elle, aucune solution ne sort
    append([Salles, Horaires, Effectifs], Variables),
    labeling([], Variables).
```
MD,
                        'rubric' => [
                            ['label' => 'Q1 : un prédicat de génération (`elementMembre/2` ou `select/3`)', 'points' => 2],
                            ['label' => 'Q1 : les huit contraintes traduites', 'points' => 3],
                            ['label' => 'Q2 : les tests sont **remontés** après la génération des variables concernées', 'points' => 3],
                            ['label' => 'Q2 : le réordonnancement est commenté ou justifié', 'points' => 1],
                            ['label' => 'Q3 : `use_module(library(clpfd))` présent', 'points' => 1],
                            ['label' => 'Q3 : domaines déclarés avec `ins` et `all_distinct`', 'points' => 2],
                            ['label' => 'Q3 : contraintes en `#=` / `#<` / `#\\=`, pas en `is` / `<`', 'points' => 2],
                            ['label' => 'Q3 : `labeling/2` en fin de prédicat', 'points' => 2],
                            ['label' => 'Chaque contrainte est numérotée en commentaire', 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ================== Ch3 — Algorithmes de recherche ================== */
            'Ch3' => [
                'lessons' => [
                    [
                        'title' => 'Largeur, profondeur, A* et AO*',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Tous ces algorithmes font la même chose : ils entretiennent une **liste d'états à
explorer** et en tirent un état à chaque tour. Ce qui les distingue tient en une
question — **lequel on tire ?**

- Le plus **ancien** de la liste → parcours en **largeur**.
- Le plus **récent** → parcours en **profondeur**.
- Celui de **coût estimé minimal** → **A\***.

Le cours fournit une animation déroulée pour chacun : `AnimLarg.pdf`, `AnimProf.pdf`,
`AnimAEtoile.pdf`, `AnimAOEtoile.pdf`, `AnimBranch_Bound.pdf`. À l'examen, on demande
de dérouler l'algorithme à la main sur un petit graphe — pas de le programmer.
MD,
                        'formalism' => <<<'MD'
**Le squelette commun**

```
OUVERTS ← {état initial}
FERMÉS  ← ∅
tant que OUVERTS ≠ ∅ :
    n ← extraire(OUVERTS)          ← c'est ici que les algorithmes diffèrent
    si n est un but : renvoyer le chemin
    FERMÉS ← FERMÉS ∪ {n}
    pour chaque successeur s de n non déjà dans FERMÉS :
        OUVERTS ← OUVERTS ∪ {s}
```

| Algorithme | `extraire` | Complet ? | Optimal ? |
|---|---|---|---|
| **Largeur** | le plus ancien (file) | oui | oui, si les arcs ont un coût uniforme |
| **Profondeur** | le plus récent (pile) | non (boucles) | non |
| **Best-First** | h(n) minimal | non | non |
| **A\*** | **f(n) = g(n) + h(n)** minimal | oui | **oui, si h est admissible** |

**A\* — les trois fonctions**

- **g(n)** — coût réel déjà parcouru depuis le départ jusqu'à n.
- **h(n)** — coût **estimé** de n jusqu'au but : l'heuristique.
- **f(n) = g(n) + h(n)** — estimation du coût total du chemin passant par n.

**Admissibilité.** h est admissible si elle ne **surestime jamais** le coût réel
restant : `h(n) ≤ h*(n)` pour tout n. C'est la condition de l'optimalité de A\*.
Avec `h(n) = 0`, A\* dégénère en algorithme de Dijkstra.

**AO\*** traite les graphes **ET/OU** : certains nœuds exigent de résoudre **tous**
leurs successeurs (nœud ET), d'autres un seul (nœud OU). On ne cherche plus un chemin
mais un **sous-graphe solution**.
MD,
                        'worked_example' => <<<'MD'
**Dérouler A\* à la main — la présentation attendue.**

Graphe : arcs `A→B (2)`, `A→C (4)`, `B→D (5)`, `C→D (1)`, `D→G (3)`.
Heuristique : `h(A)=7, h(B)=6, h(C)=2, h(D)=3, h(G)=0`. But : `G`.

| Tour | OUVERTS (n : g + h = f) | Extrait | Successeurs ajoutés |
|---|---|---|---|
| 1 | A : 0+7=7 | **A** | B : 2+6=8 · C : 4+2=6 |
| 2 | C : 6 · B : 8 | **C** | D : 5+3=8 |
| 3 | B : 8 · D : 8 | **B** | D par B : 7+3=10 — écarté, 8 < 10 |
| 4 | D : 8 | **D** | G : 8+0=8 |
| 5 | G : 8 | **G** | **but atteint** |

**Chemin : A → C → D → G, coût 8.**

Trois exigences de présentation, et elles portent les points :

1. **Un tableau**, une ligne par tour.
2. Pour chaque nœud, **le détail `g + h = f`**, pas seulement `f`.
3. Une ligne indiquant **pourquoi** un nœud déjà connu n'est pas remplacé
   (« D par B coûterait 10, on garde 8 »).

Écrire seulement « A, C, D, G » sans le tableau, c'est donner le résultat sans la
preuve — et c'est précisément ce que le correcteur d'AGC vous a reproché.
MD,
                        'pitfalls' => <<<'MD'
- **Ne donner que f** sans détailler g et h. Le correcteur ne peut pas vérifier.
- **Oublier de rouvrir un nœud** quand un chemin meilleur est trouvé (dans la version
  du cours où FERMÉS peut être révisé).
- **Confondre Best-First et A\*.** Best-First trie sur `h` seul, A\* sur `g + h`.
  Best-First n'est pas optimal.
- **Affirmer que A\* est optimal sans mentionner l'admissibilité.** C'est une condition,
  pas un acquis.
- **Traiter un graphe ET/OU comme un graphe ordinaire.** Sur un nœud ET, il faut
  résoudre **tous** les successeurs : le coût est leur somme, pas leur minimum.
MD,
                        'examiner_expects' => <<<'MD'
- [ ] Un **tableau de déroulement**, une ligne par itération.
- [ ] Le contenu de **OUVERTS** à chaque tour, avec `g + h = f` pour chaque nœud.
- [ ] Le **nœud extrait**, entouré ou signalé.
- [ ] Le **chemin final** et son **coût**.
- [ ] Pour toute question sur l'optimalité : la mention explicite de l'**admissibilité**
      de l'heuristique.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => 'A* : que valent g, h et f ?',
                        'back' => "**g(n)** — coût **réel** déjà parcouru du départ à n.\n**h(n)** — coût **estimé** de n au but (l'heuristique).\n**f(n) = g(n) + h(n)** — estimation du coût total via n.\n\nA\\* extrait toujours le nœud de **f minimal**.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'À quelle condition A* est-il optimal ?',
                        'back' => "**Si l'heuristique est admissible** : `h(n) ≤ h*(n)` pour tout n — elle ne **surestime jamais** le coût réel restant.\n\nAffirmer l'optimalité sans mentionner l'admissibilité coûte le point.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Best-First et A* : quelle différence ?',
                        'back' => "**Best-First** trie sur **h seul** — glouton, ni complet ni optimal.\n\n**A\\*** trie sur **f = g + h** — optimal si h est admissible.\n\nOublier le g, c'est retomber sur Best-First.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => 'Largeur, profondeur, A* : quelle est la seule ligne qui change ?',
                        'back' => "**Celle qui extrait de OUVERTS.**\n\n- Largeur → le plus **ancien** (file)\n- Profondeur → le plus **récent** (pile)\n- A\\* → celui de **f minimal**\n\nTout le reste du squelette est identique.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que vaut A* avec h(n) = 0 partout ?',
                        'back' => "**L'algorithme de Dijkstra** — recherche à coût uniforme.\n\nh = 0 est trivialement admissible, donc A\\* reste optimal, mais il perd tout guidage et explore beaucoup plus.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Graphe ET/OU : comment se calcule le coût d’un nœud ET ?',
                        'back' => "**La somme** des coûts de **tous** ses successeurs — il faut tous les résoudre.\n\nSur un nœud **OU**, c'est le **minimum** : un seul successeur suffit.\n\nC'est ce que traite AO\\*, et on cherche un **sous-graphe solution**, pas un chemin.",
                        'difficulty' => 5,
                    ],
                ],
            ],

            /* ================== Ch5 — Systèmes experts ================== */
            'Ch5' => [
                'lessons' => [
                    [
                        'title' => 'Chaînage avant et chaînage arrière',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
Un système expert, c'est trois pièces : une **base de faits** (ce qu'on sait), une
**base de règles** (ce qu'on sait déduire), et un **moteur d'inférence** (qui les
combine).

Le moteur peut travailler dans les deux sens :

- **Chaînage avant** — je pars de ce que je sais et je déduis tout ce que je peux,
  jusqu'à saturation. *Que puis-je conclure ?*
- **Chaînage arrière** — je pars du but et je cherche ce qui le prouverait.
  *Comment démontrer ceci ?*

Ce chapitre est tombé en 2024-2025 **et** en 2025-2026 — dont l'épreuve que vous
avez passée, où la partie correspondante a été notée 1,5.
MD,
                        'formalism' => <<<'MD'
**Le cycle du moteur d'inférence**, en trois temps répétés jusqu'à l'arrêt :

1. **Filtrage** — repérer toutes les règles dont les prémisses sont satisfaites
   par la base de faits. C'est l'**ensemble de conflit**.
2. **Résolution de conflit** — en choisir une (première trouvée, plus spécifique,
   priorité déclarée…).
3. **Exécution** — appliquer sa conclusion, qui enrichit la base de faits.

Arrêt quand l'ensemble de conflit est vide (saturation) ou que le but est atteint.

**Chaînage avant** — *data-driven*. On part des faits, on sature la base.
Adapté quand on a beaucoup de données et peu d'hypothèses à tester.

**Chaînage arrière** — *goal-driven*. On part du but ; pour chaque règle qui le
conclut, on cherche à établir ses prémisses, récursivement. C'est le mécanisme de
Prolog. Adapté quand on a un but précis et beaucoup de faits potentiels.
MD,
                        'worked_example' => <<<'MD'
**Base de règles**

```
R1 : A ∧ B  →  C
R2 : C      →  D
R3 : A      →  E
R4 : D ∧ E  →  F
```

**Base de faits initiale : {A, B}. But : F.**

**Chaînage avant** — présenter un tableau, cycle par cycle :

| Cycle | Ensemble de conflit | Règle choisie | Fait ajouté | Base de faits |
|---|---|---|---|---|
| 1 | R1, R3 | **R1** | C | A, B, C |
| 2 | R3, R2 | **R2** | D | A, B, C, D |
| 3 | R3 | **R3** | E | A, B, C, D, E |
| 4 | R4 | **R4** | F | A, B, C, D, E, **F** |

**But atteint au cycle 4.**

*Note :* la stratégie retenue est « première règle applicable dans l'ordre ».
Une autre stratégie donnerait un ordre différent mais le même résultat final —
il faut **dire laquelle on applique**.

**Chaînage arrière** — présenter un arbre :

```
But : F
└── R4 : D ∧ E
    ├── D
    │   └── R2 : C
    │       └── C
    │           └── R1 : A ∧ B
    │               ├── A  ✓ (fait)
    │               └── B  ✓ (fait)
    └── E
        └── R3 : A
            └── A  ✓ (fait)
```

**F est démontré.** Toutes les feuilles sont des faits de la base initiale.
MD,
                        'pitfalls' => <<<'MD'
- **Ne pas annoncer la stratégie de résolution de conflit.** « Première règle
  applicable », « règle la plus spécifique »… Sans elle, le déroulement paraît
  arbitraire.
- **Oublier l'ensemble de conflit.** Le tableau doit montrer **toutes** les règles
  applicables à chaque cycle, pas seulement celle qu'on retient.
- **Confondre les deux sens.** Chaînage avant → tableau de cycles. Chaînage arrière
  → arbre de preuve. Rendre un arbre pour une question de chaînage avant ne rapporte rien.
- **S'arrêter au but en chaînage avant** sans le dire, alors que la saturation
  continuerait. Précisez : « arrêt car le but F est atteint ».
MD,
                        'examiner_expects' => <<<'MD'
**Chaînage avant** : un tableau à quatre colonnes — cycle, ensemble de conflit,
règle choisie, fait ajouté — plus la mention explicite de la stratégie de résolution
de conflit et de la condition d'arrêt.

**Chaînage arrière** : un arbre de preuve où chaque feuille est soit un fait de la
base, soit un échec signalé.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => "Les trois temps du cycle d'un moteur d'inférence ?",
                        'back' => "1. **Filtrage** — repérer les règles applicables : l'**ensemble de conflit**.\n2. **Résolution de conflit** — en choisir une.\n3. **Exécution** — appliquer sa conclusion.\n\nRépété jusqu'à saturation ou atteinte du but.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Chaînage avant ou arrière : quelle présentation attend-on ?',
                        'back' => "**Avant** → un **tableau** : cycle, ensemble de conflit, règle choisie, fait ajouté.\n\n**Arrière** → un **arbre de preuve**, feuilles = faits de la base.\n\nRendre l'un pour l'autre ne rapporte rien.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce que l'ensemble de conflit ?",
                        'back' => "**L'ensemble des règles dont toutes les prémisses sont satisfaites** par la base de faits courante.\n\nIl doit apparaître dans votre tableau à chaque cycle, pas seulement la règle retenue.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quel chaînage Prolog utilise-t-il ?',
                        'back' => "**Le chaînage arrière** (*goal-driven*).\n\nProlog part du but de la requête et cherche récursivement les clauses qui l'établissent — c'est la résolution SLD.",
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */

    /**
     * Examen blanc au format observé : cinq parties, deux heures.
     * L'épreuve du 28 août dure 120 minutes.
     */
    private function mockExam(Subject $subject): void
    {
        $examen = MockExam::updateOrCreate(
            ['slug' => 'mia-blanc-prolog-contraintes-recherche'],
            [
                'subject_id' => $subject->id,
                'title' => 'MIA blanc n°1 — Prolog, contraintes, recherche',
                'instructions' => <<<'MD'
Durée : **2 heures**, comme l'épreuve du 28 août de 15 h à 17 h.
Documents autorisés : aucun. Pas de machine — l'épreuve se compose sur feuille.

Les parties reprennent les chapitres qui reviennent le plus souvent dans la matrice
examens/chapitres : **0 (Prolog)**, **4 (contraintes)**, **3 (recherche)**,
**5 (systèmes experts)**.

Rédigez le code comme sur une copie : indenté, avec les contraintes numérotées en
commentaire. Un programme PLC sans `labeling` est incomplet.
MD,
                'duration_min' => 120,
                'total_points' => 20,
                'origin' => 'genere',
                'year' => 2026,
            ]
        );

        $ch = fn (string $code) => Chapter::where('subject_id', $subject->id)
            ->where('code', $code)->value('id');

        $questions = [
            [
                'number' => 'Partie I — Prolog',
                'chapter_id' => $ch('Ch0'),
                'points' => 5,
                'statement' => <<<'MD'
```prolog
parent(marie, paul).
parent(marie, julie).
parent(paul, lea).
parent(jean, paul).

homme(paul).
homme(jean).
femme(marie).
femme(julie).
femme(lea).
```

**1.** Donnez la réponse complète de Prolog à chacune de ces requêtes : *(2 pts)*
```prolog
?- parent(marie, X).
?- parent(X, paul).
?- parent(lea, X).
?- homme(X), parent(X, _).
```

**2.** Écrivez la règle `mere/2` : `mere(X, Y)` est vraie si X est la mère de Y. *(1 pt)*

**3.** Écrivez la règle `grandParent/2`. Donnez ensuite la réponse
à `?- grandParent(X, lea).` *(2 pts)*
MD,
                'solution' => <<<'MD'
**1.**
```prolog
?- parent(marie, X).
X = paul ;
X = julie.

?- parent(X, paul).
X = marie ;
X = jean.

?- parent(lea, X).
false.

?- homme(X), parent(X, _).
X = paul ;
X = jean.
```

**2.**
```prolog
mere(X, Y) :- femme(X), parent(X, Y).
```

**3.**
```prolog
grandParent(X, Y) :- parent(X, Z), parent(Z, Y).
```
```prolog
?- grandParent(X, lea).
X = marie ;
X = jean.
```
`Z` s'unifie avec `paul` dans les deux cas : `parent(marie, paul)` puis
`parent(jean, paul)`, et `parent(paul, lea)`.
MD,
                'rubric' => [
                    ['label' => '`parent(marie, X)` → paul puis julie, les deux', 'points' => 0.5],
                    ['label' => '`parent(X, paul)` → marie puis jean, les deux', 'points' => 0.5],
                    ['label' => '`parent(lea, X)` → `false.` explicitement', 'points' => 0.5],
                    ['label' => '`homme(X), parent(X, _)` → paul puis jean', 'points' => 0.5],
                    ['label' => '`mere/2` conjugue `femme/1` et `parent/2`', 'points' => 1],
                    ['label' => '`grandParent/2` utilise une variable intermédiaire Z', 'points' => 1],
                    ['label' => '`grandParent(X, lea)` → marie puis jean', 'points' => 1],
                ],
            ],
            [
                'number' => 'Partie II — Programmation par contraintes',
                'chapter_id' => $ch('Ch4'),
                'points' => 7,
                'statement' => <<<'MD'
Quatre conférences — **IA**, **Réseaux**, **Bases de données**, **Sécurité** — se
tiennent le même jour. Chacune a une salle (A, B, C, D), un créneau (9, 11, 14, 16)
et un effectif (30, 50, 80, 120).

1. Les salles, créneaux et effectifs sont tous distincts.
2. La conférence IA a lieu trois heures avant Sécurité.
3. Réseaux est en salle C.
4. La conférence de 120 personnes n'est pas Bases de données.
5. IA a plus de participants que Réseaux.
6. La conférence de 9 h accueille 30 personnes.

**1.** Écrivez `confGetT/1` en Générer & Tester. *(3 pts)*
**2.** Réécrivez en `confPlc/1` avec `library(clpfd)`. *(4 pts)*

Numérotez chaque contrainte en commentaire.
MD,
                'solution' => <<<'MD'
**1. Générer & Tester**

```prolog
:- use_module(library(lists)).

elementMembre([], _).
elementMembre([X|L1], L2) :- select(X, L2, L3), elementMembre(L1, L3).

confGetT(C) :-
    C = [conf(ia, SI, HI, EI), conf(reseaux,  SR, HR, ER),
         conf(bd, SB, HB, EB), conf(securite, SS, HS, ES)],
    % 1 : domaines
    elementMembre([SI,SR,SB,SS], [a,b,c,d]),
    elementMembre([HI,HR,HB,HS], [9,11,14,16]),
    elementMembre([EI,ER,EB,ES], [30,50,80,120]),
    % 2
    HS is HI + 3,
    % 3
    SR == c,
    % 4
    EB =\= 120,
    % 5
    EI > ER,
    % 6
    member(conf(_, _, 9, 30), C).
```

**2. PLC**

```prolog
:- use_module(library(clpfd)).

confPlc(C) :-
    C = [conf(ia, SI, HI, EI), conf(reseaux,  SR, HR, ER),
         conf(bd, SB, HB, EB), conf(securite, SS, HS, ES)],
    % 1 : domaines et distinction
    Salles    = [SI,SR,SB,SS], Salles    ins 1..4,   % a=1, b=2, c=3, d=4
    Creneaux  = [HI,HR,HB,HS], Creneaux  ins {9,11,14,16},
    Effectifs = [EI,ER,EB,ES], Effectifs ins {30,50,80,120},
    all_distinct(Salles), all_distinct(Creneaux), all_distinct(Effectifs),
    % 2
    HS #= HI + 3,
    % 3
    SR #= 3,
    % 4
    EB #\= 120,
    % 5
    EI #> ER,
    % 6
    member(conf(_, _, 9, 30), C),
    % Valuation
    append([Salles, Creneaux, Effectifs], Variables),
    labeling([], Variables).
```

Les salles sont codées en entiers : CLP(FD) ne contraint que des entiers.
La contrainte 2 impose `HI = 11` et `HS = 14`, seul couple d'écart 3 dans le domaine.
MD,
                'rubric' => [
                    ['label' => 'Q1 : prédicat de génération avec `select/3`', 'points' => 1],
                    ['label' => 'Q1 : les six contraintes traduites', 'points' => 2],
                    ['label' => 'Q2 : `use_module(library(clpfd))`', 'points' => 0.5],
                    ['label' => 'Q2 : domaines déclarés avec `ins`', 'points' => 1],
                    ['label' => 'Q2 : `all_distinct` sur les trois groupes', 'points' => 1],
                    ['label' => 'Q2 : contraintes en `#=` / `#>` / `#\\=`, pas en `is` / `>`', 'points' => 1],
                    ['label' => 'Q2 : `labeling/2` présent en fin de prédicat', 'points' => 0.5],
                ],
            ],
            [
                'number' => 'Partie III — Recherche heuristique',
                'chapter_id' => $ch('Ch3'),
                'points' => 5,
                'statement' => <<<'MD'
Soit le graphe orienté valué suivant, de sommet initial **S** et de but **G** :

```
S → A (3)    S → B (1)
A → C (4)    B → C (7)    B → D (5)
C → G (2)    D → G (3)
```

Heuristique : `h(S)=7, h(A)=5, h(B)=6, h(C)=2, h(D)=3, h(G)=0`.

**1.** Déroulez **A\*** sous forme de tableau. Pour chaque itération, donnez le
contenu de OUVERTS avec le détail `g + h = f` de chaque nœud, et le nœud extrait. *(3 pts)*

**2.** Donnez le chemin solution et son coût. *(1 pt)*

**3.** L'heuristique est-elle admissible ? Justifiez, et dites ce que cela implique. *(1 pt)*
MD,
                'solution' => <<<'MD'
**1.**

| Tour | OUVERTS (g + h = f) | Extrait | Ajouts |
|---|---|---|---|
| 1 | S : 0+7=7 | **S** | A : 3+5=8 · B : 1+6=7 |
| 2 | B : 7 · A : 8 | **B** | C par B : 8+2=10 · D : 6+3=9 |
| 3 | A : 8 · D : 9 · C : 10 | **A** | C par A : 7+2=9 → meilleur que 10, on met à jour C : 9 |
| 4 | C : 9 · D : 9 | **C** *(à égalité, on prend le premier inséré)* | G par C : 9+0=9 |
| 5 | D : 9 · G : 9 | **D** | G par D : 9+0=9 — pas meilleur, on garde |
| 6 | G : 9 | **G** | **but atteint** |

**2.** Chemin **S → A → C → G**, coût **3 + 4 + 2 = 9**.

*(Le chemin S → B → D → G coûte également 1 + 5 + 3 = 9 : les deux sont optimaux.)*

**3.** Vérifions `h(n) ≤ h*(n)` pour chaque nœud :

| n | h(n) | h*(n) réel | admissible ? |
|---|---|---|---|
| S | 7 | 9 | ✓ |
| A | 5 | 6 | ✓ |
| B | 6 | 8 | ✓ |
| C | 2 | 2 | ✓ (égalité permise) |
| D | 3 | 3 | ✓ |
| G | 0 | 0 | ✓ |

**L'heuristique est admissible** : elle ne surestime jamais. **A\* est donc optimal**
sur ce graphe, et le chemin de coût 9 trouvé est bien le meilleur.
MD,
                'rubric' => [
                    ['label' => 'Un tableau de déroulement, une ligne par itération', 'points' => 1],
                    ['label' => 'Le détail `g + h = f` donné pour chaque nœud, pas seulement f', 'points' => 1],
                    ['label' => 'La mise à jour de C quand un meilleur chemin est trouvé est signalée', 'points' => 1],
                    ['label' => 'Chemin S → A → C → G (ou S → B → D → G), coût 9', 'points' => 1],
                    ['label' => 'Admissibilité vérifiée nœud par nœud, et optimalité de A* conclue', 'points' => 1],
                ],
            ],
            [
                'number' => 'Partie IV — Système expert',
                'chapter_id' => $ch('Ch5'),
                'points' => 3,
                'statement' => <<<'MD'
**Base de règles**

```
R1 : fievre ∧ toux        →  grippe
R2 : grippe               →  repos
R3 : fievre               →  hydratation
R4 : repos ∧ hydratation  →  guerison
R5 : courbatures          →  fievre
```

**Base de faits initiale : {courbatures, toux}. But : `guerison`.**

**1.** Déroulez le **chaînage avant** sous forme de tableau : cycle, ensemble de
conflit, règle choisie, fait ajouté. Précisez votre stratégie de résolution de
conflit et la condition d'arrêt. *(2 pts)*

**2.** Donnez l'**arbre de preuve** du chaînage arrière pour le but `guerison`. *(1 pt)*
MD,
                'solution' => <<<'MD'
**1. Chaînage avant.** Stratégie : première règle applicable dans l'ordre R1 → R5.

| Cycle | Ensemble de conflit | Règle choisie | Fait ajouté | Base de faits |
|---|---|---|---|---|
| 1 | R5 | **R5** | fievre | courbatures, toux, fievre |
| 2 | R1, R3 | **R1** | grippe | + grippe |
| 3 | R3, R2 | **R2** | repos | + repos |
| 4 | R3 | **R3** | hydratation | + hydratation |
| 5 | R4 | **R4** | guerison | + **guerison** |

**Arrêt au cycle 5** : le but `guerison` est atteint.

**2. Chaînage arrière.**

```
But : guerison
└── R4 : repos ∧ hydratation
    ├── repos
    │   └── R2 : grippe
    │       └── grippe
    │           └── R1 : fievre ∧ toux
    │               ├── fievre
    │               │   └── R5 : courbatures
    │               │       └── courbatures  ✓ (fait)
    │               └── toux  ✓ (fait)
    └── hydratation
        └── R3 : fievre
            └── R5 : courbatures  ✓ (déjà établi)
```

**`guerison` est démontré** : toutes les feuilles sont des faits de la base initiale.
MD,
                'rubric' => [
                    ['label' => 'Tableau à quatre colonnes, un cycle par ligne', 'points' => 0.5],
                    ['label' => "L'ensemble de conflit est donné à chaque cycle, pas seulement la règle retenue", 'points' => 0.5],
                    ['label' => 'La stratégie de résolution de conflit est annoncée', 'points' => 0.5],
                    ['label' => "La condition d'arrêt est explicitée", 'points' => 0.5],
                    ['label' => 'Arbre de preuve du chaînage arrière, feuilles = faits de la base', 'points' => 1],
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