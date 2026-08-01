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
 * MIA — socle depuis zéro, et les cinq chapitres jamais couverts.
 *
 * L'effort suit la matrice examens/chapitres de l'enseignant : le chapitre 8
 * (apprentissage) revient dans la quasi-totalité des annales depuis 2010 et
 * n'avait aucune fiche. Les chapitres 1, 6, 7 et 9 sont rares : fiche courte,
 * suffisante pour le QCM transversal.
 */
class MiaSocleSeeder extends Seeder
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

            /* ==================== Ch0 — Prolog depuis zéro ==================== */
            'Ch0' => [
                'lessons' => [
                    [
                        'title' => 'Prolog depuis zéro — programmer en décrivant',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
En Java, vous dites à la machine **comment** faire : boucle, condition, affectation.

En Prolog, vous décrivez **ce qui est vrai**, et la machine se débrouille.

Un exemple avant toute théorie. Vous écrivez ces trois lignes :

```prolog
parent(marie, paul).
parent(paul, lea).
grandParent(X, Y) :- parent(X, Z), parent(Z, Y).
```

Les deux premières sont des **faits** : Marie est parent de Paul, Paul est parent de
Léa. La troisième est une **règle** : X est grand-parent de Y s'il existe un Z tel
que X est parent de Z et Z est parent de Y.

Vous n'avez écrit aucune boucle, aucun `if`. Pourtant vous pouvez déjà demander :

```prolog
?- grandParent(marie, lea).
true.
```

Prolog a trouvé tout seul que Z devait valoir `paul`. C'est ça, la programmation
logique : on décrit, la machine cherche.
MD,
                        'formalism' => <<<'MD'
**Les trois briques, et rien d'autre**

Un **fait** — une affirmation, terminée par un point :
```prolog
lundi(fabrice).
cuisine(xavier, plat).
```

Une **règle** — une affirmation conditionnelle. `:-` se lit « **si** »,
la virgule « **et** », le point-virgule « **ou** » :
```prolog
occupe(X) :- lundi(X), cuisine(X, _).
```
*« X est occupé si X vient le lundi et que X cuisine quelque chose. »*

Une **requête** — une question posée à la base, précédée de `?-` :
```prolog
?- occupe(fabrice).
```

**La règle de nommage — l'erreur numéro un**

| Écriture | Ce que c'est |
|---|---|
| `fabrice`, `plat`, `lundi` | **atome** — commence par une **minuscule**. Une valeur constante. |
| `X`, `Personne`, `_L` | **variable** — commence par une **majuscule** ou `_`. Un trou à remplir. |
| `_` | variable **anonyme** — « peu importe la valeur » |
| `42`, `9.5` | nombre |
| `bung(alsace, 95, 9)` | **terme composé** — un nom suivi d'arguments |

Écrire `?- cuisine(x, plat).` avec un `x` minuscule demande si **l'atome `x`**
cuisine un plat. La réponse sera `false`, et ce n'était pas la question.

**L'unification — le seul mécanisme de Prolog**

Deux termes s'**unifient** si l'on peut les rendre identiques en donnant des valeurs
aux variables. Trois cas :

1. Deux atomes identiques s'unifient. `plat` avec `plat` : oui. `plat` avec `pain` : non.
2. Une variable libre s'unifie avec **n'importe quoi**, et prend sa valeur.
3. Deux termes composés s'unifient s'ils ont le **même nom**, le **même nombre
   d'arguments**, et que leurs arguments s'unifient **deux à deux**.

```
cuisine(X, plat)  ∪  cuisine(xavier, plat)   →  X = xavier      ✓
cuisine(X, plat)  ∪  cuisine(louis, pain)    →  échec (plat ≠ pain)
cuisine(X, Y)     ∪  cuisine(louis, pain)    →  X = louis, Y = pain
```

**Comment Prolog répond à une requête**

1. Il cherche la **première** clause dont la tête s'unifie avec la question.
2. Si c'est une règle, il doit maintenant prouver son corps, but par but.
3. S'il échoue quelque part, il **revient en arrière** — c'est le *backtracking* —
   et essaie la clause suivante.
4. Il répète jusqu'à avoir énuméré **toutes** les solutions.

Ce dernier point est celui qu'on oublie : Prolog ne donne pas *une* réponse,
il les donne **toutes**, séparées par `;`.
MD,
                        'worked_example' => <<<'MD'
**Dérouler une requête à la main.** C'est ce qu'on demande à l'examen, sur feuille,
sans machine.

Base :
```prolog
mardi(antoine).
mardi(louis).
mardi(xavier).

cuisine(xavier, plat).
cuisine(louis, pain).

cuisinierDeMardi(X) :- mardi(X), cuisine(X, _).
```

**Requête : `?- cuisinierDeMardi(X).`**

| Étape | Ce que fait Prolog | Résultat |
|---|---|---|
| 1 | Unifie avec la tête de la règle → il faut prouver `mardi(X), cuisine(X, _)` | |
| 2 | Premier but : `mardi(X)`. Première clause → **X = antoine** | |
| 3 | Second but : `cuisine(antoine, _)`. Aucune clause ne correspond | **échec** |
| 4 | **Backtracking** : retour au premier but, clause suivante → **X = louis** | |
| 5 | `cuisine(louis, _)` s'unifie avec `cuisine(louis, pain)` | ✅ **X = louis** |
| 6 | On demande la suite : retour au premier but → **X = xavier** | |
| 7 | `cuisine(xavier, _)` s'unifie avec `cuisine(xavier, plat)` | ✅ **X = xavier** |
| 8 | Plus de clause `mardi` | fin |

**La réponse complète :**
```prolog
X = louis ;
X = xavier.
```

Notez l'ordre : `louis` avant `xavier`, parce que c'est l'ordre des clauses
`mardi/1` dans le fichier. Prolog ne trie pas, il parcourt.

**Deux pièges de notation à connaître**

`=` **unifie**, `is` **calcule** :
```prolog
?- X = 3+4.       X = 3+4.      % le terme, non évalué
?- X is 3+4.      X = 7.        % évalué
```

`\+` est la **négation par échec** : `\+ But` réussit si `But` échoue.
```prolog
libre(X) :- mardi(X), \+ cuisine(X, _).
```
*« X est libre s'il vient le mardi et qu'on ne peut pas prouver qu'il cuisine. »*
MD,
                        'pitfalls' => <<<'MD'
- **Ne donner qu'une solution.** Prolog les énumère **toutes**. C'est ce qui a coûté
  des points en mai : les résultats de requête étaient incomplets.
- **Oublier `false.`** Quand aucune solution n'existe, `false.` **est** la réponse.
  Laisser un blanc, c'est laisser la question sans réponse.
- **Variable en minuscule.** `?- cuisine(x, plat).` ne demande pas ce que vous croyez.
- **Confondre `=` et `is`.** `X = 3+4` unifie avec le terme ; `X is 3+4` calcule.
- **Oublier le point final.** Chaque clause et chaque requête se termine par un point.
- **Ignorer l'ordre des clauses.** Il détermine l'ordre des solutions, et l'examen
  le vérifie.
MD,
                        'examiner_expects' => <<<'MD'
Pour chaque requête :

- [ ] **Toutes** les solutions, dans l'ordre de parcours des clauses.
- [ ] Séparées par **`;`**, la dernière terminée par un **point**.
- [ ] **`false.`** écrit explicitement quand il n'y en a aucune.

Pour une règle : le `:-`, les virgules entre les buts, le point final, et des
variables en majuscule.
MD,
                        'source_refs' => [['label' => 'mainMOIA.pdf § 0.3 — Didacticiel PROLOG']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'En Prolog, que signifient `:-` et la virgule ?',
                        'back' => "**`:-`** se lit « **si** ».\n**La virgule** se lit « **et** ».\n**Le point-virgule** se lit « **ou** ».\n\n`occupe(X) :- lundi(X), cuisine(X, _).`\n→ *X est occupé si X vient lundi et que X cuisine.*",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce que le backtracking en Prolog ?",
                        'back' => "**Quand un but échoue, Prolog revient au but précédent et essaie la clause suivante.**\n\nC'est ce qui lui permet d'énumérer **toutes** les solutions au lieu de s'arrêter à la première.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Que fait `\\+` en Prolog ?',
                        'back' => "**La négation par échec** : `\\+ But` réussit si `But` **échoue**.\n\n```prolog\nlibre(X) :- mardi(X), \\+ cuisine(X, _).\n```\n\n*« … et qu'on ne peut pas prouver qu'il cuisine. »*",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Dans quel ordre Prolog rend-il ses solutions ?",
                        'back' => "**Dans l'ordre des clauses du fichier**, de haut en bas.\n\nProlog ne trie pas, il parcourt. L'examen vérifie cet ordre.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ==================== Ch8 — Apprentissage (poids 5) ==================== */
            'Ch8' => [
                'lessons' => [
                    [
                        'title' => 'Apprentissage — arbres de décision et réseaux de neurones',
                        'est_minutes' => 30,
                        'intuition' => <<<'MD'
Jusqu'ici, on donnait les règles à la machine. **Apprendre**, c'est l'inverse :
on donne des **exemples**, et la machine en déduit les règles.

Le corpus du cours est le jeu de données **iris** : 150 fleurs, quatre mesures
chacune — longueur et largeur du sépale, longueur et largeur du pétale — et
l'espèce à laquelle elles appartiennent. On veut une machine qui, devant une fleur
inconnue, annonce son espèce.

Ce chapitre revient dans la **quasi-totalité des annales depuis 2010**. C'est,
avec Prolog et les contraintes, l'un des trois piliers de l'épreuve.
MD,
                        'formalism' => <<<'MD'
**Les trois familles d'apprentissage**

| Famille | On dispose de | On cherche |
|---|---|---|
| **Supervisé** | exemples **étiquetés** (fleur → espèce) | prédire l'étiquette d'un cas nouveau |
| **Non supervisé** | exemples **sans étiquette** | trouver des groupes naturels |
| **Par renforcement** | des récompenses | une stratégie qui maximise le gain |

Le cas *iris* est **supervisé** : chaque fleur du corpus porte son espèce.

**Le vocabulaire à ne pas confondre**

- Un **attribut** (ou variable, ou *feature*) : une mesure. Ici, la longueur du pétale.
- Une **classe** (ou étiquette) : ce qu'on veut prédire. Ici, l'espèce.
- Un **exemple** : une ligne du tableau, avec ses attributs et sa classe.
- L'ensemble d'**apprentissage** sert à construire le modèle ;
  l'ensemble de **test** sert à le juger. **Ils doivent être disjoints.**

**Les arbres de décision**

Un arbre de décision est une suite de questions. Chaque **nœud interne** teste un
attribut, chaque **feuille** donne une classe.

```
                 largeur pétale < 0.8 ?
                    ╱              ╲
                 oui                non
                  │                  │
              Setosa        longueur pétale < 4.9 ?
                                ╱          ╲
                             oui            non
                              │              │
                        Versicolor       Virginica
```

Pour classer une fleur, on descend l'arbre en répondant aux questions.
Trois comparaisons suffisent.

**Comment choisir la question à poser ?** On prend celle qui **sépare le mieux**.
Le cours emploie l'**entropie** pour le mesurer.

L'entropie d'un ensemble mesure son **désordre** :

```
H(S) = − Σ  p(c) · log₂ p(c)
```

où `p(c)` est la proportion d'exemples de la classe `c` dans S.

- Ensemble **pur** (une seule classe) → **H = 0**. Aucun désordre.
- Ensemble à deux classes **équilibré** → **H = 1**. Désordre maximal.

Le **gain d'information** d'un attribut A est la réduction d'entropie qu'il apporte :

```
Gain(S, A) = H(S) − Σ  (|Sᵥ| / |S|) · H(Sᵥ)
```

On somme sur les valeurs `v` de A, en pondérant chaque sous-ensemble `Sᵥ` par sa
taille. **On choisit l'attribut de gain maximal**, et l'on recommence sur chaque
branche. C'est l'algorithme **ID3**.

**Les réseaux de neurones**

Un **neurone** fait trois choses :

1. Il reçoit des entrées `x₁ … xₙ`, chacune avec un **poids** `w₁ … wₙ`.
2. Il calcule la somme pondérée : `s = Σ wᵢ·xᵢ + b`, où `b` est le **biais**.
3. Il passe `s` dans une **fonction d'activation** et rend le résultat.

```
   x₁ ──w₁──╲
   x₂ ──w₂───▶ ( Σ wᵢxᵢ + b ) ──▶ f ──▶ sortie
   x₃ ──w₃──╱
```

Fonctions d'activation courantes :

| Nom | Formule | Sortie |
|---|---|---|
| **Seuil** | 1 si s > 0, sinon 0 | binaire |
| **Sigmoïde** | 1 / (1 + e⁻ˢ) | entre 0 et 1 |
| **ReLU** | max(0, s) | 0 ou positif |

Un **perceptron** est un neurone seul. Sa limite est célèbre : il ne sait séparer que
des données **linéairement séparables** — il ne peut pas apprendre le XOR.

Un **réseau multicouche** empile des neurones : une couche d'entrée, une ou plusieurs
couches **cachées**, une couche de sortie. Il lève la limite du perceptron.

L'apprentissage consiste à **ajuster les poids** pour réduire l'erreur, par
**rétropropagation du gradient** : on calcule l'erreur en sortie, on la propage
vers l'arrière, et l'on corrige chaque poids proportionnellement à sa responsabilité.
MD,
                        'worked_example' => <<<'MD'
**Calculer une entropie et un gain, à la main.**

Corpus de 14 jours ; on veut prédire s'il faut jouer au tennis.
9 fois « oui », 5 fois « non ».

**Entropie de départ**

```
H(S) = − (9/14)·log₂(9/14) − (5/14)·log₂(5/14)
     = − 0,643 · (−0,637) − 0,357 · (−1,486)
     = 0,410 + 0,530
     = 0,940
```

Proche de 1 : l'ensemble est très mélangé.

**Gain de l'attribut « Temps »**, qui prend trois valeurs :

| Valeur | Effectif | oui | non | Entropie |
|---|---|---|---|---|
| Ensoleillé | 5 | 2 | 3 | 0,971 |
| Couvert | 4 | 4 | 0 | **0** (pur) |
| Pluvieux | 5 | 3 | 2 | 0,971 |

```
Gain(S, Temps) = 0,940 − [ (5/14)·0,971 + (4/14)·0 + (5/14)·0,971 ]
               = 0,940 − [ 0,347 + 0 + 0,347 ]
               = 0,940 − 0,694
               = 0,246
```

Si l'attribut « Vent » donne un gain de 0,048 et « Humidité » 0,151, alors
**on choisit « Temps »** comme racine de l'arbre — c'est lui qui réduit le plus
le désordre.

Remarquez la branche « Couvert » : entropie **0**, tous les exemples disent oui.
Elle devient immédiatement une **feuille**, sans autre question.

**Un neurone, à la main.**

Entrées `x = (1, 0, 1)`, poids `w = (0,5 ; −0,3 ; 0,8)`, biais `b = −0,2`,
activation seuil.

```
s = 0,5·1 + (−0,3)·0 + 0,8·1 + (−0,2)
  = 0,5 + 0 + 0,8 − 0,2
  = 1,1

f(1,1) = 1   car 1,1 > 0
```

La sortie vaut **1**.

Avec une sigmoïde : `f(1,1) = 1 / (1 + e⁻¹·¹) = 1 / (1 + 0,333) = 0,750`.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre entropie et gain.** L'entropie mesure le désordre d'**un** ensemble ;
  le gain mesure la **réduction** de désordre qu'apporte un attribut.
- **Oublier la pondération dans le gain.** Chaque sous-ensemble compte
  proportionnellement à sa **taille**, pas à égalité.
- **Prendre l'attribut de plus petit gain.** On veut le **maximum** : celui qui
  ordonne le plus.
- **Tester sur les données d'apprentissage.** Un modèle qui récite son corpus n'a
  rien appris. Les ensembles doivent être **disjoints**.
- **Croire qu'un perceptron seul apprend le XOR.** Il ne sépare que du linéairement
  séparable. Il faut une couche cachée.
- **Oublier le biais** dans le calcul d'un neurone. `s = Σ wᵢxᵢ + b`, le `+ b` compte.
MD,
                        'examiner_expects' => <<<'MD'
Pour un calcul d'entropie ou de gain :

- [ ] La **formule posée** avant l'application numérique.
- [ ] Le détail des **sous-ensembles** avec leurs effectifs.
- [ ] La **pondération par la taille** visible dans le calcul.
- [ ] La **conclusion** : quel attribut est retenu, et pourquoi.

Pour un neurone : la somme pondérée **avec le biais**, puis l'activation appliquée,
avec la valeur numérique finale.
MD,
                        'source_refs' => [['label' => 'mainMOIA.pdf § 8 — Apprentissage'], ['label' => 'irisMIA.csv']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => "Formule de l'entropie d'un ensemble S ?",
                        'back' => "**H(S) = − Σ p(c) · log₂ p(c)**\n\nOù p(c) est la proportion de la classe c dans S.\n\nEnsemble **pur** → H = 0. Deux classes **équilibrées** → H = 1.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => "Formule du gain d'information d'un attribut A ?",
                        'back' => "**Gain(S, A) = H(S) − Σ (|Sᵥ| / |S|) · H(Sᵥ)**\n\nOn somme sur les valeurs v de A, **pondérées par la taille** de chaque sous-ensemble.\n\nOn retient l'attribut de gain **maximal**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Un sous-ensemble a une entropie de 0. Que faut-il en faire ?',
                        'back' => "**Il devient une feuille**, sans autre question.\n\nEntropie 0 signifie que tous ses exemples appartiennent à la **même classe** : il n'y a plus rien à séparer.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Que calcule un neurone, exactement ?',
                        'back' => "**s = Σ wᵢ·xᵢ + b**, puis **f(s)**.\n\nSomme pondérée des entrées, **plus le biais**, passée dans la fonction d'activation.\n\nOublier le `+ b` est l'erreur classique.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quelle est la limite du perceptron simple ?',
                        'back' => "**Il ne sépare que des données linéairement séparables.**\n\nIl ne peut pas apprendre le **XOR**. Il faut une **couche cachée** — un réseau multicouche — pour lever cette limite.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Supervisé, non supervisé, par renforcement : la différence ?',
                        'back' => "**Supervisé** — exemples **étiquetés**, on prédit l'étiquette.\n**Non supervisé** — pas d'étiquette, on cherche des **groupes**.\n**Par renforcement** — des **récompenses**, on cherche une stratégie.\n\nLe corpus iris est supervisé.",
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Peut-on évaluer un modèle sur ses données d’apprentissage ?',
                        'back' => "**Non.** Un modèle qui récite son corpus n'a rien appris.\n\nLes ensembles d'**apprentissage** et de **test** doivent être **disjoints**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Les trois fonctions d’activation du cours ?',
                        'back' => "**Seuil** — 1 si s > 0, sinon 0. Sortie binaire.\n**Sigmoïde** — 1 / (1 + e⁻ˢ). Sortie entre 0 et 1.\n**ReLU** — max(0, s). Zéro ou positif.",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => 'Construire un arbre de décision — entropie et gain',
                        'origin' => 'genere',
                        'est_minutes' => 40,
                        'difficulty' => 4,
                        'statement' => <<<'MD'
On veut prédire si un étudiant **réussit** un module, à partir de trois attributs.
Corpus de 10 étudiants :

| N° | Assiduité | Devoirs rendus | Groupe de TD | Réussite |
|---|---|---|---|---|
| 1 | forte | oui | A | oui |
| 2 | forte | oui | B | oui |
| 3 | forte | non | A | oui |
| 4 | faible | oui | A | oui |
| 5 | faible | oui | B | non |
| 6 | faible | non | A | non |
| 7 | faible | non | B | non |
| 8 | forte | non | B | oui |
| 9 | faible | oui | A | oui |
| 10 | faible | non | B | non |

**1.** Calculez l'**entropie** du corpus complet. *(2 pts)*

**2.** Calculez le **gain d'information** de chacun des trois attributs.
Détaillez les sous-ensembles. *(6 pts)*

**3.** Quel attribut choisissez-vous comme **racine** ? Justifiez. *(1 pt)*

**4.** Poursuivez la construction et **dessinez l'arbre complet**. *(3 pts)*

**5.** Classez l'étudiant suivant : assiduité faible, devoirs rendus oui, groupe B.
Que prédit votre arbre ? *(1 pt)*

Rappel : `log₂(x) = ln(x) / ln(2)`. Quelques valeurs utiles :
`log₂(0,5) = −1`, `log₂(0,4) ≈ −1,322`, `log₂(0,6) ≈ −0,737`,
`log₂(0,25) = −2`, `log₂(0,75) ≈ −0,415`, `log₂(1/3) ≈ −1,585`, `log₂(2/3) ≈ −0,585`.
MD,
                        'hint' => "Commencez par compter les « oui » et les « non » du corpus complet. Puis, pour chaque attribut, séparez le corpus selon ses valeurs et recomptez dans chaque paquet.",
                        'method' => <<<'MD'
1. **Entropie du corpus** : comptez les oui et les non, calculez les proportions,
   appliquez `H = − Σ p·log₂ p`.
2. **Pour chaque attribut** : découpez le corpus en sous-ensembles selon ses valeurs,
   calculez l'entropie de chacun, puis la **moyenne pondérée par les tailles**.
   Le gain est la différence avec l'entropie de départ.
3. Un sous-ensemble d'entropie **0** devient une feuille immédiatement.
4. Sur les branches non pures, **recommencez** avec les attributs restants.
MD,
                        'solution' => <<<'MD'
**1. Entropie du corpus**

Comptage : **6 oui**, **4 non**, sur 10.

```
H(S) = − (6/10)·log₂(6/10) − (4/10)·log₂(4/10)
     = − 0,6 · (−0,737) − 0,4 · (−1,322)
     = 0,442 + 0,529
     = 0,971
```

**2. Gain de chaque attribut**

**Assiduité**

| Valeur | Exemples | oui | non | Entropie |
|---|---|---|---|---|
| forte | 1, 2, 3, 8 | 4 | 0 | **0** (pur) |
| faible | 4, 5, 6, 7, 9, 10 | 2 | 4 | 0,918 |

```
H(faible) = − (2/6)·log₂(2/6) − (4/6)·log₂(4/6)
          = − 0,333·(−1,585) − 0,667·(−0,585)
          = 0,528 + 0,390 = 0,918

Gain(Assiduité) = 0,971 − [ (4/10)·0 + (6/10)·0,918 ]
                = 0,971 − 0,551
                = 0,420
```

**Devoirs rendus**

| Valeur | Exemples | oui | non | Entropie |
|---|---|---|---|---|
| oui | 1, 2, 4, 5, 9 | 4 | 1 | 0,722 |
| non | 3, 6, 7, 8, 10 | 2 | 3 | 0,971 |

```
H(oui) = − 0,8·log₂(0,8) − 0,2·log₂(0,2) = 0,258 + 0,464 = 0,722

Gain(Devoirs) = 0,971 − [ (5/10)·0,722 + (5/10)·0,971 ]
              = 0,971 − [ 0,361 + 0,486 ]
              = 0,971 − 0,847
              = 0,124
```

**Groupe de TD**

| Valeur | Exemples | oui | non | Entropie |
|---|---|---|---|---|
| A | 1, 3, 4, 6, 9 | 4 | 1 | 0,722 |
| B | 2, 5, 7, 8, 10 | 2 | 3 | 0,971 |

```
Gain(Groupe) = 0,971 − [ (5/10)·0,722 + (5/10)·0,971 ] = 0,124
```

**3. La racine**

| Attribut | Gain |
|---|---|
| **Assiduité** | **0,420** |
| Devoirs | 0,124 |
| Groupe | 0,124 |

**On retient l'Assiduité** : c'est elle qui réduit le plus le désordre. Sa branche
« forte » est même **pure** — quatre exemples, tous en réussite.

**4. L'arbre**

La branche « forte » est une feuille. Il reste à traiter « faible »
(exemples 4, 5, 6, 7, 9, 10 : 2 oui, 4 non, H = 0,918).

*Gain de « Devoirs » sur ce sous-ensemble :*

| Valeur | Exemples | oui | non | Entropie |
|---|---|---|---|---|
| oui | 4, 5, 9 | 2 | 1 | 0,918 |
| non | 6, 7, 10 | 0 | 3 | **0** (pur) |

```
Gain = 0,918 − [ (3/6)·0,918 + (3/6)·0 ] = 0,918 − 0,459 = 0,459
```

*Gain de « Groupe » sur ce sous-ensemble :*

| Valeur | Exemples | oui | non | Entropie |
|---|---|---|---|---|
| A | 4, 6, 9 | 2 | 1 | 0,918 |
| B | 5, 7, 10 | 0 | 3 | **0** (pur) |

```
Gain = 0,459
```

Égalité. On retient **Groupe** (ou Devoirs, en le justifiant). Reste la branche
« faible / A » : exemples 4, 6, 9 → 2 oui, 1 non. On la départage par « Devoirs » :
4 et 9 ont rendu et réussi, 6 n'a pas rendu et a échoué. Branche pure.

```
                    Assiduité
                  ╱          ╲
              forte          faible
                │               │
             OUI (4/4)       Groupe
                            ╱      ╲
                           A        B
                           │        │
                       Devoirs    NON (3/3)
                       ╱     ╲
                     oui     non
                      │       │
                    OUI     NON
```

**5. Classification**

Assiduité **faible** → branche droite.
Groupe **B** → **NON**.

L'arbre prédit un **échec**. Notez qu'il n'a même pas eu besoin de l'information
« devoirs rendus » : sur cette branche, le groupe suffit à trancher.
MD,
                        'rubric' => [
                            ['label' => 'Entropie du corpus : formule posée puis 0,971', 'points' => 2],
                            ['label' => 'Assiduité : sous-ensembles détaillés, branche « forte » pure', 'points' => 2],
                            ['label' => 'Assiduité : gain 0,420 avec pondération par les tailles visible', 'points' => 2],
                            ['label' => 'Devoirs et Groupe : gains 0,124 chacun', 'points' => 2],
                            ['label' => 'Racine = Assiduité, justifiée par le gain maximal', 'points' => 1],
                            ['label' => 'Arbre poursuivi sur la branche non pure', 'points' => 2],
                            ['label' => 'Arbre dessiné, feuilles étiquetées', 'points' => 1],
                            ['label' => 'Classification : échec, avec le chemin suivi', 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ==================== Ch6 — Jeux ==================== */
            'Ch6' => [
                'lessons' => [
                    [
                        'title' => 'Minimax et élagage alpha-bêta',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Deux joueurs, chacun voulant gagner. L'un cherche à **maximiser** son score,
l'autre à le **minimiser** — d'où le nom.

L'idée est simple : je regarde tous mes coups possibles, puis toutes les réponses
de l'adversaire, et ainsi de suite. Au bout, j'évalue les positions. Puis je remonte
en supposant que **chacun joue au mieux**.

Ce chapitre n'apparaît **jamais** dans la matrice des annales depuis 2010. Révisez-le
pour le QCM transversal, pas pour un exercice.
MD,
                        'formalism' => <<<'MD'
**Minimax**

L'arbre de jeu alterne deux types de nœuds :

- Nœud **MAX** — c'est à moi de jouer, je prends le **maximum** des enfants.
- Nœud **MIN** — c'est à l'adversaire, il prend le **minimum**.

Les **feuilles** portent la valeur d'une fonction d'**évaluation** de la position.

```
              MAX                    3
            ╱     ╲
        MIN         MIN          3        2
       ╱ │ ╲       ╱ │ ╲
      3  12  8    2  4  6      feuilles
```

Le nœud MIN de gauche prend `min(3,12,8) = 3`, celui de droite `min(2,4,6) = 2`.
La racine MAX prend `max(3,2) = 3`.

Complexité : **O(bᵈ)** où `b` est le facteur de branchement et `d` la profondeur.
C'est exponentiel — d'où l'élagage.

**L'élagage alpha-bêta**

Il donne **exactement le même résultat** que Minimax, en explorant moins de nœuds.

Deux valeurs circulent :

- **α** — le meilleur score déjà garanti pour MAX. Elle ne fait que croître.
- **β** — le meilleur score déjà garanti pour MIN. Elle ne fait que décroître.

**La coupure : dès que α ≥ β, on arrête d'explorer cette branche.**

Pourquoi ? Parce que le joueur du dessus a déjà mieux ailleurs et ne choisira jamais
cette branche : l'explorer davantage ne changera rien.

Dans le meilleur cas — si les coups sont ordonnés du plus prometteur au moins bon —
la complexité tombe à **O(b^{d/2})**. À profondeur égale, on explore la racine du
nombre de nœuds : c'est ce qui permet aux programmes d'échecs de voir deux fois
plus loin.
MD,
                        'worked_example' => <<<'MD'
**Dérouler alpha-bêta sur l'arbre ci-dessus.**

On explore de gauche à droite. Au départ, `α = −∞`, `β = +∞`.

| Étape | Nœud | α | β | Action |
|---|---|---|---|---|
| 1 | MIN gauche | −∞ | +∞ | on descend |
| 2 | feuille 3 | −∞ | **3** | β descend à 3 |
| 3 | feuille 12 | −∞ | 3 | 12 > 3, β inchangé |
| 4 | feuille 8 | −∞ | 3 | β inchangé → MIN gauche vaut **3** |
| 5 | racine MAX | **3** | +∞ | α monte à 3 |
| 6 | MIN droite | 3 | +∞ | on descend |
| 7 | feuille 2 | 3 | **2** | β descend à 2 |
| 8 | — | 3 | 2 | **α ≥ β : coupure** |

Les feuilles **4 et 6 ne sont jamais évaluées**.

**Pourquoi la coupure est-elle légitime ?** Le nœud MIN de droite vaudra au plus 2,
puisqu'il prend le minimum et qu'il a déjà trouvé 2. Or MAX a déjà 3 garanti à
gauche. MAX ne choisira donc jamais la branche droite, quelles que soient les feuilles
restantes. Les explorer serait du travail perdu.

**Le résultat est identique à Minimax : 3.** L'élagage ne change jamais la réponse,
seulement le temps de calcul.
MD,
                        'pitfalls' => <<<'MD'
- **Croire que l'élagage change le résultat.** Il donne **exactement** la même valeur
  que Minimax.
- **Confondre α et β.** α appartient à MAX et **croît** ; β appartient à MIN et **décroît**.
- **Oublier de dire quelles feuilles ne sont pas évaluées.** C'est précisément ce
  qu'on demande de montrer.
- **Annoncer O(b^{d/2}) sans condition.** Ce gain suppose un **bon ordonnancement**
  des coups. Au pire cas, alpha-bêta reste en O(bᵈ).
MD,
                        'examiner_expects' => <<<'MD'
Un **tableau de déroulement** avec les valeurs de α et β à chaque étape, la
**coupure signalée** au moment où `α ≥ β`, et la **liste des nœuds non évalués**.
MD,
                        'source_refs' => [['label' => 'AnimAlphaBeta.pdf']],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Minimax : que fait un nœud MAX ? un nœud MIN ?',
                        'back' => "**MAX** prend le **maximum** de ses enfants — c'est mon tour.\n**MIN** prend le **minimum** — c'est celui de l'adversaire.\n\nLes feuilles portent la valeur de la fonction d'évaluation.",
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Quelle est la condition de coupure en alpha-bêta ?',
                        'back' => "**α ≥ β**\n\nα = meilleur garanti pour MAX (croît). β = meilleur garanti pour MIN (décroît).\n\nDès que α ≥ β, le joueur du dessus a mieux ailleurs : inutile de continuer.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'L’élagage alpha-bêta change-t-il le résultat de Minimax ?',
                        'back' => "**Non, jamais.** Il donne **exactement** la même valeur.\n\nIl explore seulement moins de nœuds : **O(b^{d/2})** au mieux contre O(bᵈ), à condition que les coups soient bien ordonnés.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ==================== Ch7 — Planification ==================== */
            'Ch7' => [
                'lessons' => [
                    [
                        'title' => 'Planification et graphe de potentiels',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
**Planifier**, c'est ordonner des tâches qui dépendent les unes des autres, pour
finir au plus tôt.

On ne peut pas poser la charpente avant les murs, ni les murs avant les fondations.
Certaines tâches, en revanche, se font en parallèle. La question est : **quelle est
la durée minimale du chantier**, et quelles tâches ne supportent aucun retard ?

Le cours traite aussi le **job-shop** : des tâches à répartir sur des machines.

Chapitre rare — tombé en 2014-2015 et 2017-2018 seulement.
MD,
                        'formalism' => <<<'MD'
**Le graphe de potentiels**

Chaque **tâche** est un sommet. Un **arc** de A vers B, valué par la durée de A,
signifie « B ne peut commencer qu'après la fin de A ».

Deux dates par tâche :

- **Date au plus tôt** — le plus tôt où la tâche peut démarrer.
  Calculée **de gauche à droite** : `tôt(B) = max( tôt(A) + durée(A) )` sur tous
  les prédécesseurs A.
- **Date au plus tard** — le plus tard où elle peut démarrer sans retarder le projet.
  Calculée **de droite à gauche** : `tard(A) = min( tard(B) − durée(A) )` sur tous
  les successeurs B.

La **marge** d'une tâche vaut `tard − tôt`.

**Le chemin critique** est l'ensemble des tâches de **marge nulle**. Elles ne
supportent aucun retard : décaler l'une d'elles d'un jour décale tout le projet
d'un jour.

Sa longueur donne la **durée minimale** du projet.
MD,
                        'worked_example' => <<<'MD'
**Un petit projet.**

| Tâche | Durée | Prédécesseurs |
|---|---|---|
| A | 3 | — |
| B | 2 | A |
| C | 4 | A |
| D | 2 | B, C |
| E | 3 | C |
| F | 1 | D, E |

**Dates au plus tôt** — de gauche à droite :

```
tôt(A) = 0
tôt(B) = tôt(A) + 3 = 3
tôt(C) = tôt(A) + 3 = 3
tôt(D) = max( tôt(B)+2, tôt(C)+4 ) = max(5, 7) = 7
tôt(E) = tôt(C) + 4 = 7
tôt(F) = max( tôt(D)+2, tôt(E)+3 ) = max(9, 10) = 10
```

**Durée minimale du projet : 10 + 1 = 11.**

**Dates au plus tard** — de droite à gauche, en partant de `tard(F) = 10` :

```
tard(F) = 10
tard(E) = tard(F) − 3 = 7
tard(D) = tard(F) − 2 = 8
tard(C) = min( tard(D)−4, tard(E)−4 ) = min(4, 3) = 3
tard(B) = tard(D) − 2 = 6
tard(A) = min( tard(B)−3, tard(C)−3 ) = min(3, 0) = 0
```

**Le tableau des marges :**

| Tâche | tôt | tard | marge | critique ? |
|---|---|---|---|---|
| A | 0 | 0 | **0** | ✅ |
| B | 3 | 6 | 3 | |
| C | 3 | 3 | **0** | ✅ |
| D | 7 | 8 | 1 | |
| E | 7 | 7 | **0** | ✅ |
| F | 10 | 10 | **0** | ✅ |

**Chemin critique : A → C → E → F**, de longueur 3 + 4 + 3 + 1 = **11**.

La tâche B dispose de 3 jours de marge, D d'un jour. On peut les retarder d'autant
sans conséquence.
MD,
                        'pitfalls' => <<<'MD'
- **Prendre le minimum pour les dates au plus tôt.** C'est le **maximum** :
  la tâche attend que **tous** ses prédécesseurs soient finis.
- **Prendre le maximum pour les dates au plus tard.** C'est le **minimum** :
  il faut satisfaire la contrainte la plus serrée.
- **Oublier de conclure sur le chemin critique.** C'est ce qu'on demande.
- **Confondre marge et durée.** La marge est `tard − tôt`, pas la durée de la tâche.
MD,
                        'examiner_expects' => <<<'MD'
Un **tableau à quatre colonnes** — tâche, date au plus tôt, date au plus tard,
marge — le **chemin critique** identifié par ses marges nulles, et la **durée
minimale** du projet.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => 'Comment calcule-t-on les dates au plus tôt ? au plus tard ?',
                        'back' => "**Au plus tôt** — de gauche à droite, avec un **max** :\n`tôt(B) = max( tôt(A) + durée(A) )` sur les prédécesseurs.\n\n**Au plus tard** — de droite à gauche, avec un **min** :\n`tard(A) = min( tard(B) − durée(A) )` sur les successeurs.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce que le chemin critique ?",
                        'back' => "**L'ensemble des tâches de marge nulle** (`tard − tôt = 0`).\n\nElles ne supportent aucun retard : décaler l'une décale tout le projet. Sa longueur donne la **durée minimale**.",
                        'difficulty' => 4,
                    ],
                ],
            ],

            /* ==================== Ch9 — Méthodes incomplètes ==================== */
            'Ch9' => [
                'lessons' => [
                    [
                        'title' => 'Recherche locale et métaheuristiques',
                        'est_minutes' => 15,
                        'intuition' => <<<'MD'
Les méthodes vues jusqu'ici sont **complètes** : elles trouvent la solution optimale,
ou prouvent qu'il n'y en a pas. Le prix à payer est le temps — souvent exponentiel.

Sur un problème à un million de variables, on renonce à l'optimum et l'on cherche
**une bonne solution, vite**. C'est le domaine des méthodes **incomplètes** :
elles ne garantissent rien, mais elles rendent une réponse.

Le principe est toujours le même : partir d'une solution quelconque, la **modifier
un peu**, garder si c'est mieux, recommencer.
MD,
                        'formalism' => <<<'MD'
**Le squelette de la recherche locale**

```
s ← une solution initiale, au hasard
tant que le temps le permet :
    s' ← un voisin de s          (une petite modification)
    si qualité(s') > qualité(s) :
        s ← s'
renvoyer s
```

Le **voisinage** est l'ensemble des solutions obtenues par une modification
élémentaire : échanger deux éléments, changer la valeur d'une variable.

**Le problème : les optima locaux**

Cette méthode s'arrête dès qu'aucun voisin n'est meilleur. Or ce point n'est pas
forcément le meilleur du problème — c'est un sommet, mais peut-être pas le plus haut.

```
   qualité
      │        ╱╲  ← optimum global
      │   ╱╲  ╱  ╲
      │  ╱  ╲╱    ╲
      │ ╱  optimum ╲
      │╱   local    ╲
      └──────────────── solutions
```

Coincé sur le petit sommet, l'algorithme ne voit aucun voisin meilleur et s'arrête.

**Les trois échappatoires du cours**

| Méthode | Idée |
|---|---|
| **Recuit simulé** | accepter parfois une solution **moins bonne**, avec une probabilité qui **décroît** au fil du temps |
| **Recherche tabou** | interdire de revenir sur les derniers coups joués, via une **liste tabou** |
| **Algorithmes génétiques** | faire évoluer une **population** de solutions par croisement et mutation |

**Le recuit simulé** tire son nom de la métallurgie. On accepte une dégradation
avec la probabilité `e^{−Δ/T}`, où `Δ` est la perte de qualité et `T` une
« température » que l'on fait baisser. Chaud au début — on explore largement ;
froid à la fin — on se stabilise.

**Complet contre incomplet** — la distinction à énoncer :

| | Complet | Incomplet |
|---|---|---|
| Trouve l'optimum | **garanti** | non garanti |
| Prouve l'absence de solution | **oui** | **non** |
| Coût | souvent exponentiel | maîtrisé |
| Exemples du cours | backtracking, PLC, A* | recherche locale, recuit, tabou, génétique |
MD,
                        'worked_example' => <<<'MD'
**Les 8 reines par recherche locale.**

Placer 8 reines sur un échiquier sans qu'aucune n'en attaque une autre.

*Représentation.* Une reine par colonne. Une solution est un tableau de 8 nombres :
`s[i]` est la ligne de la reine de la colonne i. Cela élimine d'office les conflits
de colonne.

*Qualité.* Le nombre de paires de reines qui s'attaquent. **On cherche 0.**

*Voisinage.* Déplacer **une** reine dans sa colonne. Il y a 8 × 7 = 56 voisins.

*Déroulement.*

```
s  = [1, 5, 8, 6, 3, 7, 2, 4]      conflits = 4
     ↓ on essaie les 56 voisins, on garde le meilleur
s' = [1, 5, 8, 6, 3, 7, 4, 2]      conflits = 2
s'' = [1, 6, 8, 3, 7, 4, 2, 5]     conflits = 0   ✓
```

*Le piège.* Sur certaines configurations, aucun des 56 voisins n'améliore le compte
alors qu'il reste des conflits : c'est un **optimum local**. La parade est de
relancer depuis une position au hasard, ou d'accepter temporairement une dégradation
— c'est exactement ce que fait le recuit simulé.
MD,
                        'pitfalls' => <<<'MD'
- **Affirmer qu'une méthode incomplète trouve l'optimum.** Elle ne le garantit pas.
- **Croire qu'elle peut prouver l'absence de solution.** Elle ne le peut jamais :
  ne pas avoir trouvé n'est pas une preuve.
- **Oublier de définir le voisinage.** C'est le cœur de la méthode, et il change tout.
- **Confondre recuit simulé et recherche tabou.** Le recuit accepte des dégradations
  avec une probabilité décroissante ; le tabou interdit de revenir en arrière.
MD,
                        'examiner_expects' => <<<'MD'
La **définition du voisinage** posée explicitement, la **fonction de qualité**,
et la distinction **complet / incomplet** avec ce que chacun garantit — en
particulier que l'incomplet **ne prouve jamais l'absence de solution**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'definition',
                        'front' => 'Méthode complète ou incomplète : quelle différence ?',
                        'back' => "**Complète** — trouve l'optimum ou **prouve** qu'il n'y en a pas. Souvent exponentielle.\n\n**Incomplète** — rend une bonne solution vite, mais **ne garantit rien** et **ne prouve jamais l'absence de solution**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce qu'un optimum local, et comment s'en échapper ?",
                        'back' => "**Une solution dont aucun voisin n'est meilleur, sans être la meilleure du problème.**\n\nTrois parades : le **recuit simulé** (accepter une dégradation avec probabilité décroissante), la **recherche tabou** (interdire le retour en arrière), les **algorithmes génétiques**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Recuit simulé : avec quelle probabilité accepte-t-on une dégradation ?',
                        'back' => "**e^{−Δ/T}**\n\nΔ = la perte de qualité, T = la « température ».\n\nT est **élevée au début** (on explore) puis **décroît** (on se stabilise). Nom emprunté à la métallurgie.",
                        'difficulty' => 5,
                    ],
                ],
            ],

            /* ==================== Ch1 — Introduction ==================== */
            'Ch1' => [
                'lessons' => [
                    [
                        'title' => 'Ce que recouvre l\'intelligence artificielle',
                        'est_minutes' => 10,
                        'intuition' => <<<'MD'
Chapitre court, jamais évalué seul, mais il alimente le **QCM transversal** qui
accompagne chaque épreuve. Une lecture suffit.
MD,
                        'formalism' => <<<'MD'
**Les repères historiques**

| Période | Ce qui se passe |
|---|---|
| **1950** | Turing propose son test : une machine est intelligente si l'on ne peut la distinguer d'un humain en conversation |
| **1956** | Conférence de **Dartmouth** — l'expression « intelligence artificielle » est forgée |
| **1960-70** | Âge d'or : premiers programmes de démonstration, Prolog (1972) |
| **1970-80** | Premier « hiver de l'IA » — les promesses ne sont pas tenues |
| **1980** | Les **systèmes experts** en entreprise |
| **1990-2000** | Retour des approches statistiques, apprentissage automatique |
| **2010-** | Apprentissage profond, réseaux à nombreuses couches |

**Les deux grandes approches**

- **Symbolique** — on manipule des symboles et des règles. C'est Prolog, les systèmes
  experts, la logique. Le raisonnement est **explicable** : on peut retracer pourquoi.
- **Connexionniste** — on ajuste des poids numériques sur des exemples. Ce sont les
  réseaux de neurones. Performant, mais **difficilement explicable**.

Le module couvre surtout la première, avec le chapitre 8 en ouverture sur la seconde.

**Ce qu'un système à base d'IA sait faire**, selon le polycopié : représenter des
connaissances, raisonner, résoudre des problèmes sous contraintes, apprendre à
partir d'exemples, planifier.
MD,
                        'worked_example' => <<<'MD'
**Situer les chapitres du module sur les deux approches :**

| Chapitre | Approche |
|---|---|
| 0 — Prolog | symbolique |
| 2 — Représentation des connaissances | symbolique |
| 3 — Algorithmes de recherche | symbolique |
| 4 — Contraintes | symbolique |
| 5 — Systèmes experts | symbolique |
| 6 — Jeux | symbolique |
| 7 — Planification | symbolique |
| **8 — Apprentissage** | **connexionniste et statistique** |
| 9 — Méthodes incomplètes | métaheuristiques |

Le module est très majoritairement **symbolique** — ce qui explique le poids de
Prolog et des contraintes dans les annales.
MD,
                        'pitfalls' => <<<'MD'
- **Confondre le test de Turing et la machine de Turing.** Le premier est un critère
  d'intelligence (1950), la seconde un modèle de calcul (1936). Ils sont du même
  auteur, et ils tombent tous les deux — dans deux modules différents.
- **Dater la naissance de l'IA de 1950.** C'est **1956**, à Dartmouth.
MD,
                        'examiner_expects' => <<<'MD'
La date de **1956** et le lieu **Dartmouth**, la distinction **symbolique /
connexionniste** avec un exemple de chacune, et le **test de Turing** correctement
distingué de la **machine de Turing**.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Test de Turing et machine de Turing : quelle différence ?',
                        'back' => "**Test de Turing (1950)** — critère d'intelligence : une machine est intelligente si l'on ne peut la distinguer d'un humain en conversation.\n\n**Machine de Turing (1936)** — modèle de calcul, au programme d'**EP**.\n\nMême auteur, deux notions sans rapport.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Approche symbolique ou connexionniste ?',
                        'back' => "**Symbolique** — symboles et règles. Prolog, systèmes experts, logique. Raisonnement **explicable**.\n\n**Connexionniste** — poids numériques ajustés sur des exemples. Réseaux de neurones. Performant mais **peu explicable**.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Quand et où l'expression « intelligence artificielle » a-t-elle été forgée ?",
                        'back' => "**En 1956, à la conférence de Dartmouth.**\n\nPas en 1950 — c'est la date du test de Turing.",
                    ],
                ],
            ],
        ];
    }
}