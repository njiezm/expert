<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours d'AGC, première partie : les graphes.
 *
 * Note obtenue en janvier : 7/20, répartis en Ex1 2/7, Ex2 2/6, Ex3 3/7.
 *
 * Le sujet imprime lui-même son barème de forme : « Proposer une solution »
 * appelle une explication en français, « Donner l'algorithme » appelle
 * l'algorithme *avec* son explication et ses commentaires, et « la note sera
 * diminuée si toutes les réponses ne sont pas justifiées ». Les annotations du
 * correcteur reprennent ces mots un à un : « justifier », « évaluation ? »,
 * « pas d'explication = 0 », « Où sont les tests ! ».
 *
 * C'est, pour la troisième fois sur cinq copies, une perte de format et non de
 * connaissance. Ces sept séances remettent le socle des graphes en place, et
 * chacune se termine par la façon d'écrire la réponse, pas seulement de la
 * trouver.
 */
class CoursAgcSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'AGC')->first();

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
                'title' => "Ce que l'épreuve demande, et comment elle est notée",
                'chapitre' => 'G1',
                'duree_min' => 25,
                'prerequis' => "Aucun. Cette séance ne contient aucune notion technique.",
                'intro' => <<<'MD'
Bonjour.

Avant tout contenu, une observation qui va vous surprendre : **votre sujet
d'AGC vous dit comment il vous note.** Pas dans un règlement caché — en haut de
la première page, en toutes lettres.

Et les annotations rouges de votre copie de janvier reprennent ces mots un à un.

On va lire ces consignes ensemble, parce que c'est là que sont partis une bonne
partie de vos treize points manquants — pas dans ce que vous ne saviez pas, mais
dans la façon dont vous l'avez écrit.
MD,
                'body' => <<<'MD'
## Ce qui est imprimé sur le sujet

Recopié mot pour mot de l'épreuve du 22 janvier :

> Pour les questions commençant par « **Proposer une solution** » vous devez
> **expliquer textuellement** comment fonctionne votre solution ; pour les
> questions commençant par « **Donner l'algorithme** » vous devez donner
> l'algorithme, **avec l'explication de son fonctionnement et des commentaires**.
>
> Pour les algorithmes, vous ne disposez pas d'autres objets/fonctions que des
> **listes, files ou piles** et leurs fonctions associées définies dans le cours.
>
> Attention, la note sera diminuée si :
> — le soin apporté à la rédaction et à la copie sont insuffisants,
> — **toutes les réponses ne sont pas justifiées** par un raisonnement clair et
> précis.

Trois règles, et elles valent des points indépendamment de vos connaissances.

## Les annotations de votre copie

| Question | Ce qu'a écrit le correcteur |
|---|---|
| 1.1 | « **justifier** » · « **évaluation ?** » · « pas vu dans le cours » |
| 1.2 | « alors ce n'est plus une matrice » |
| 1.3 | « comment ces nœuds sont initialisés ? » |
| 1.4 | « **pas d'explication = 0** » · « Où sont les tests ! » |
| 2.1 | « → pas Glouton » |
| 2.3 | « il ne faut pas revenir au début à chaque fois » · « incomplet » |
| 2.4 | *(laissée vide)* — « ? » |

Mettez les deux listes côte à côte. « Justifier », « pas d'explication = 0 » :
c'est la troisième règle du sujet. « Évaluation ? » : la question demandait
d'**évaluer** des structures, il fallait donc des coûts chiffrés.

**« Pas d'explication = 0 »** est la plus dure à lire, et la plus instructive.
Vous aviez écrit un algorithme. Il n'était pas noté zéro parce qu'il était faux :
il était noté zéro parce qu'il était **seul**.

## Les deux verbes, et ce qu'ils exigent

C'est la chose la plus rentable de tout ce cours. Apprenez-la par cœur.

| Le sujet écrit | Vous rendez |
|---|---|
| **« Proposer une solution »** | **du français.** Un ou deux paragraphes qui décrivent le principe. **Pas de code.** |
| **« Donner l'algorithme »** ou **« Donner une fonction »** | **du pseudo-code + une explication + des commentaires.** Les trois. |
| **« Évaluer »**, **« Donner la complexité »** | **un chiffre** : O(n), O(n²), O(nm). Jamais une phrase vague. |
| **« Justifier »**, **« Prouver »** | un **raisonnement**, ou un **contre-exemple**. |

Remarquez que le sujet enchaîne souvent les deux premiers : « Proposer une
solution pour… » puis « Donner l'algorithme d'une fonction qui implémente cette
proposition ». **C'est la même idée, demandée deux fois, dans deux formes
différentes.** Vous êtes payé deux fois pour la même réflexion — à condition de
changer de forme.

## Le squelette de l'épreuve

J'ai comparé les deux dernières sessions. La structure ne bouge pas.

| | Janvier 2026 | 2024-2025 |
|---|---|---|
| **Exercice 1** | Tri par arbre binaire — **7 pts** | Couper un graphe — **9 pts** |
| **Exercice 2** | Plus grande sous-séquence — **6 pts** | Parcours de damier — **7 pts** |
| **Exercice 3** | Vente de fruits — **7 pts** | Tablettes de chocolat — **4 pts** |

Traduit en chapitres :

| Exercice | Toujours | Poids |
|---|---|---|
| **1** | graphes : structure de données + algorithme | 7 à 9 pts |
| **2** | **programmation dynamique ou gloutonne** | 6 à 7 pts |
| **3** | **programmation linéaire** | 4 à 7 pts |

## L'exercice 3 est un cadeau

Regardez ses questions, sur les deux années :

> 3.1 Écrire le programme linéaire · 3.2 Résoudre par la méthode graphique ·
> 3.3 Résoudre par la méthode du simplexe · 3.4 Donner la solution

**Les mêmes quatre questions, deux années de suite.** Seuls les chiffres
changent : des tablettes de chocolat une année, des caisses de fruits l'autre.

C'est entièrement mécanique. Aucune invention. Vous y avez eu 3 sur 7 en
janvier ; avec les séances 11 et 12, ces points sont à vous.

## L'exercice 2 aussi a son gabarit

En 2024-2025, l'exercice de programmation dynamique posait ces quatre
questions :

> 2.1 Donner la **structure de la solution optimale** en fonction des
> sous-problèmes · 2.2 **Définir récursivement** la solution optimale et montrer
> que l'application directe donne un algorithme exponentiel · 2.3 Donner
> l'**algorithme** en programmation dynamique · 2.4 Quelle est la
> **complexité** ?

Retenez ces quatre questions : c'est le **plan officiel** de la programmation
dynamique dans ce cours. On y reviendra à la séance 9.

## Le plan des treize séances

| | Séance | Chapitre |
|---|---|---|
| 1 | Ce que l'épreuve demande, et comment elle est notée | — |
| 2 | Le vocabulaire des graphes | G1 |
| 3 | **Les trois représentations, et comment les comparer** | G1 |
| 4 | Les parcours : largeur, profondeur, connexité | G2 |
| 5 | Les plus courts chemins | G2 |
| 6 | **Les arbres et l'arbre binaire de recherche** | G3 |
| 7 | L'arbre couvrant de poids minimal | G3 |
| 8 | Le glouton : quand il marche, quand il ment | PG |
| 9 | **La programmation dynamique : reconnaître le problème** | PD |
| 10 | La plus grande sous-séquence commune, de bout en bout | PD |
| 11 | Écrire un programme linéaire | PL |
| 12 | **La méthode graphique et le simplexe** | PL |
| 13 | Les cycles, et composer la copie du 26 août | CY |

## Une contrainte de calendrier à garder en tête

Le 26 août, vous avez **AGC de 15 h à 18 h, puis SPP de 20 h à 23 h**. Six heures
d'épreuve dans la journée, séparées par deux heures.

Cela change la façon de composer l'AGC : il ne faut pas finir vidé. On en
reparlera précisément à la séance 13, mais retenez déjà le principe — **on ne
s'acharne pas sur une question bloquée en AGC**, parce que le prix se paiera à
20 h.
MD,
                'recap' => <<<'MD'
- **Le sujet imprime son barème de forme.** Les annotations du correcteur en
  reprennent les mots.
- **« Proposer une solution » → du français, pas de code.**
  **« Donner l'algorithme » → pseudo-code + explication + commentaires.**
  **« Évaluer » → un chiffre.** **« Justifier » → un raisonnement ou un
  contre-exemple.**
- « Pas d'explication = 0 » : l'algorithme était seul, pas faux.
- Le squelette ne bouge pas : **Ex1 graphes · Ex2 dynamique ou glouton · Ex3
  programmation linéaire.**
- **L'exercice 3 pose les mêmes quatre questions chaque année.** C'est mécanique.
- L'exercice de programmation dynamique a lui aussi son plan en quatre questions.
- Le 26 août : AGC 15 h-18 h **puis SPP 20 h-23 h**. On ne s'acharne pas.
MD,
            ],

            /* ================= Séance 2 ================= */
            [
                'title' => 'Le vocabulaire des graphes',
                'chapitre' => 'G1',
                'duree_min' => 30,
                'prerequis' => "Aucun. On repart des définitions, parce qu'une définition floue se voit tout de suite sur une copie.",
                'intro' => <<<'MD'
Une séance de vocabulaire. Ça paraît modeste, mais dans ce module chaque mot est
une définition précise, et le correcteur les attend.

« Justifier », en AGC, veut souvent dire : **employer le bon terme et dire
pourquoi il s'applique**. Un raisonnement juste écrit avec des mots approximatifs
perd la moitié de ses points.

On pose donc tout le vocabulaire d'un coup, avec les notations du cours.
MD,
                'body' => <<<'MD'
## La définition

Un **graphe** `G = (V, E)` est la donnée de :

- `V` — un ensemble de **sommets** *(vertices)*, de cardinal noté **n** ;
- `E` — un ensemble d'**arêtes** *(edges)*, de cardinal noté **m**.

Ces deux lettres, `n` et `m`, sont celles du cours. **Toutes vos complexités
s'exprimeront en fonction de n et m.**

## Orienté ou non

| | Non orienté | Orienté |
|---|---|---|
| l'élément de E | une **arête** `{x, y}` | un **arc** `(x, y)` |
| sens | pas de sens | de `x` vers `y` |
| `{x,y}` et `{y,x}` | c'est la même chose | **deux arcs différents** |
| exemple | un réseau d'amitié | un réseau routier à sens unique |

**Dites toujours de quel type de graphe vous parlez.** Beaucoup d'algorithmes ne
s'écrivent pas pareil dans les deux cas.

## Voisinage et degré

- `y` est **voisin** (ou **successeur**) de `x` s'il existe une arête de `x` vers
  `y`.
- Le **degré** d'un sommet est son nombre d'arêtes incidentes.
- En orienté, on distingue le **degré entrant** et le **degré sortant**.

Une propriété qui sert souvent :

> **La somme des degrés vaut 2m.** Chaque arête est comptée deux fois, une par
> extrémité.

Conséquence immédiate : **le nombre de sommets de degré impair est toujours
pair.** On s'en servira à la séance 13 pour les cycles eulériens.

## Densité — creux ou dense

Le mot vient partout dans les justifications de structure de données.

| | Condition | Nom |
|---|---|---|
| peu d'arêtes | `m ≈ n` | graphe **creux** *(peu dense)* |
| beaucoup d'arêtes | `m ≈ n²` | graphe **dense** |

Le maximum d'arêtes vaut `n(n−1)/2` en non orienté, `n(n−1)` en orienté.

**C'est la densité qui décide de la structure de données.** Un graphe creux
stocké en matrice, c'est une matrice pleine de zéros. On y revient à la séance
suivante.

## Chemins et cycles

| Terme | Sens |
|---|---|
| **chaîne** | suite de sommets reliés, sans tenir compte du sens |
| **chemin** | suite de sommets reliés **en suivant le sens** des arcs |
| **longueur** | le nombre d'**arêtes** parcourues, pas de sommets |
| **cycle** | une chaîne qui revient à son point de départ |
| **circuit** | un **chemin** qui revient à son point de départ (orienté) |
| **élémentaire** | qui ne repasse pas deux fois par le même sommet |
| **simple** | qui ne repasse pas deux fois par la même arête |

Deux confusions fréquentes, à éviter :

- **la longueur se compte en arêtes.** Le chemin `a → b → c` est de longueur 2,
  pas 3.
- **cycle** est le terme non orienté, **circuit** le terme orienté. Le correcteur
  le remarque.

## Connexité

| Terme | Définition |
|---|---|
| **connexe** | il existe une **chaîne** entre toute paire de sommets |
| **fortement connexe** | *(orienté)* il existe un **chemin** de x vers y **et** de y vers x, pour toute paire |
| **composante connexe** | un morceau maximal connexe |

La distinction est constamment testée : un graphe orienté peut être connexe sans
être fortement connexe. Prenez `a → b → c` : on peut aller de `a` à `c`, jamais
l'inverse. **Connexe, pas fortement connexe.**

## Arbres

Voici la définition du cours, et la première phrase du sujet de janvier :

> Un **arbre** est un graphe **connexe** et **sans cycle**.

Quatre propriétés équivalentes, à savoir citer — la deuxième est celle qu'on
utilise le plus :

1. `G` est connexe et sans cycle ;
2. `G` est connexe et possède exactement **`n − 1` arêtes** ;
3. il existe **une unique chaîne** entre deux sommets quelconques ;
4. `G` est sans cycle, et ajouter une arête crée un cycle.

Vocabulaire associé :

- **racine** — le sommet distingué, dans un arbre enraciné ;
- **père**, **fils**, **feuille** (sommet sans fils) ;
- **profondeur** d'un sommet — sa distance à la racine ;
- **hauteur** de l'arbre — la profondeur maximale ;
- **arbre binaire** — chaque sommet a **au plus deux fils**, un gauche et un
  droit.

Un **arbre couvrant** de `G` est un arbre qui contient **tous** les sommets de
`G` et un sous-ensemble de ses arêtes. Sujet de la séance 7.

## Sous-graphe

- `G' = (V', E')` est un **sous-graphe** de `G` si `V' ⊆ V` et `E' ⊆ E`.
- C'est le **sous-graphe induit** par `V'` si `E'` contient **toutes** les arêtes
  de `E` dont les deux extrémités sont dans `V'`.

Le sujet 2024-2025 portait précisément là-dessus : « calculer le nombre d'arêtes
d'un sous-graphe ». **La nuance induit / non induit décide de la réponse.**

## La fiche de vocabulaire

Pour la relecture de la veille :

```
G = (V, E)   n = |V| sommets   m = |E| arêtes
arête {x,y} non orientée · arc (x,y) orienté
Σ degrés = 2m  →  nb de sommets de degré impair est pair
creux m ≈ n · dense m ≈ n²   →  décide la structure de données
longueur = nombre d'ARÊTES
chaîne/cycle = non orienté · chemin/circuit = orienté
connexe (chaîne partout) · fortement connexe (chemin dans les DEUX sens)
arbre = connexe + sans cycle = connexe + (n−1) arêtes
arbre binaire = au plus 2 fils
```
MD,
                'recap' => <<<'MD'
- `G = (V, E)`, **`n` sommets, `m` arêtes**. Toutes les complexités s'écrivent
  avec ces deux lettres.
- **Arête** = non orienté · **arc** = orienté. **Chaîne/cycle** = non orienté ·
  **chemin/circuit** = orienté.
- **Σ degrés = 2m**, donc le nombre de sommets de degré impair est pair.
- **Creux `m ≈ n` · dense `m ≈ n²`.** C'est la densité qui décide de la structure
  de données.
- **La longueur se compte en arêtes.**
- **Connexe** ≠ **fortement connexe** : `a → b → c` est connexe, pas fortement.
- **Arbre = connexe + sans cycle = connexe + `n − 1` arêtes.**
  **Arbre binaire = au plus deux fils.**
- **Sous-graphe induit** : toutes les arêtes dont les deux bouts sont dedans.
MD,
            ],

            /* ================= Séance 3 ================= */
            [
                'title' => 'Les trois représentations, et comment les comparer',
                'chapitre' => 'G1',
                'duree_min' => 35,
                'prerequis' => "La séance 2, en particulier la densité. C'est la question 1.1 de janvier, celle annotée « évaluation ? ».",
                'intro' => <<<'MD'
Voici la question 1.1 de janvier :

> *Évaluer l'intérêt de chacune des structures de données permettant de mémoriser
> un graphe vues en cours pour mémoriser un arbre binaire.*

Trois annotations en face : « **justifier** », « **évaluation ?** », « **pas vu
dans le cours** ».

Ces trois mots disent trois choses différentes, et il faut les entendre
séparément. « Pas vu dans le cours » : vous avez proposé une structure qui n'est
pas au programme. « Évaluation ? » : le verbe était **évaluer**, donc on attendait
des **coûts chiffrés**. « Justifier » : chaque verdict devait être argumenté.

À la fin de cette séance, vous saurez répondre à cette question en dix lignes, et
vous aurez une grille qui marche pour n'importe quelle variante.
MD,
                'body' => <<<'MD'
## Les trois critères

Le cours en fixe trois. **Ce sont les trois colonnes de toute réponse à une
question de structure de données.**

| Critère | La question qu'il pose |
|---|---|
| **Coût d'accès** | combien coûte-t-il de savoir si l'arête (x,y) existe ? |
| **Coût de stockage** | combien de mémoire pour n sommets et m arêtes ? |
| **Dynamicité** | peut-on ajouter ou retirer un sommet, une arête, sans tout refaire ? |

Retenez ces trois mots. **Accès, stockage, dynamicité.** C'est la grille.

## Structure 1 — la matrice d'adjacence

Un tableau `A` de taille `n × n`, où `A[x][y]` vaut 1 s'il existe une arête de
`x` vers `y`, 0 sinon. Pour un graphe pondéré, on y met le poids.

| Critère | Verdict | Pourquoi |
|---|---|---|
| accès à une arête | **O(1)** | accès direct à la case |
| ajout d'une arête | **O(1)** | on écrit dans la case |
| stockage | **O(n²)** | toute la matrice, même vide |
| ajout d'un sommet | **O(n²)** | il faut **refaire la matrice** |

**Bonne pour les graphes denses, mauvaise pour les graphes creux** : si `m ≈ n`,
la matrice est presque entièrement à zéro.

Et surtout : **statique**. La taille est figée à la création.

## Structure 2 — FS / APS

C'est la structure propre à ce cours, et il faut la nommer exactement.

- **FS** — *File des Successeurs* : un tableau où l'on écrit à la suite les
  voisins du sommet 0, puis ceux du sommet 1, etc., séparés par **−1**.
- **APS** — *Adresse du Premier Successeur* : un tableau où la case `i` donne
  l'indice, dans FS, du premier voisin du sommet `i`.

L'exemple du cours, pour un graphe à quatre sommets `a, b, c, d` numérotés
0, 1, 2, 3 :

```
FS  :  1  2 −1  0  2 −1  0  1  3 −1  2 −1
APS :  0        3        6           10
```

Lisons-le. `APS[0] = 0` : les voisins de `a` commencent à l'indice 0 de FS, ce
sont `1, 2` jusqu'au séparateur. `APS[2] = 6` : les voisins de `c` commencent à
l'indice 6, ce sont `0, 1, 3`.

| Critère | Verdict | Pourquoi |
|---|---|---|
| accès à une arête | **O(1) + O(n)** | O(1) pour APS, puis parcours de FS |
| stockage | **n + (n + m)** | c'est le **minimum possible** |
| dynamicité | **très mauvaise** | insérer oblige à recréer les tableaux |

**La structure la plus économe en mémoire, et la plus rigide.** Deux tableaux
d'entiers, rien de plus.

## Structure 3 — les listes chaînées

Pour chaque sommet, une liste de ses voisins.

| Critère | Verdict | Pourquoi |
|---|---|---|
| accès à une arête | **O(degré)** | il faut parcourir la liste |
| stockage | **O(n + m)** | proportionnel au contenu réel |
| dynamicité | **excellente** | on ajoute un maillon |

**C'est la structure du dynamique.** Dès qu'un énoncé dit « le graphe est
construit au fur et à mesure », « incrémentalement », « on ajoute des
sommets » — c'est celle-là.

## La grille complète

Recopiez-la telle quelle. Elle répond, à elle seule, à toute question 1.1.

| | Matrice d'adjacence | FS / APS | Listes chaînées |
|---|---|---|---|
| **Accès à une arête** | **O(1)** | O(1) puis O(n) | O(degré) |
| **Stockage** | O(n²) | **n + (n+m)** — minimal | O(n + m) |
| **Ajout d'une arête** | **O(1)** | coûteux | **O(1)** |
| **Ajout d'un sommet** | refaire la matrice | refaire les tableaux | **O(1)** |
| **Dynamicité** | mauvaise | **très mauvaise** | **excellente** |
| **Bonne si** | graphe **dense** et **statique** | mémoire critique, graphe figé | graphe **creux** ou **évolutif** |

## Répondre à la question 1.1 de janvier

Le raisonnement tient en trois temps, et c'est ce qu'il fallait écrire.

**Premier temps — quel est le besoin ?** Le tri par arbre binaire **construit
l'arbre au fur et à mesure**, par insertions successives des valeurs du tableau.
Le besoin dominant est donc la **dynamicité**.

**Deuxième temps — on passe les trois structures à la grille.**

> **FS/APS** : structure entièrement statique, chaque insertion obligerait à
> recréer les deux tableaux. **Éliminée.**
>
> **Matrice d'adjacence** : l'ajout d'une arête coûte O(1), ce qui convient, mais
> l'ajout d'un **sommet** — et chaque valeur insérée est un nouveau sommet —
> impose de refaire la matrice, en O(n²). De plus un arbre est un graphe très
> creux : `m = n − 1`, donc la matrice serait presque vide, pour un coût O(n²).
> **Peu adaptée.**
>
> **Listes chaînées** : ajout d'un sommet en O(1), stockage O(n + m) = O(n)
> puisque l'arbre est creux. **C'est la bonne.**

**Troisième temps — l'information manquante.** Et c'est le point que le corrigé
souligne : aucune des trois structures ne mémorise, telle quelle, ce dont un
**arbre binaire** a besoin — savoir qui est le **fils gauche** et qui est le
**fils droit**. Une arête, dans un graphe, n'est pas étiquetée.

Il faut donc l'ajouter. Deux façons :

- dans la matrice, **coder la nature du lien** dans la valeur : 1 pour le père,
  2 pour le fils gauche, 3 pour le fils droit ;
- dans les listes, définir un type `Sommet` qui porte explicitement son fils
  gauche, son fils droit et son père.

**Cette remarque vaut à elle seule une part des points.** Elle montre qu'on a vu
que la question n'était pas « quelle structure pour un graphe » mais « quelle
structure pour un **arbre binaire** ».

## L'erreur de la question 1.2

Vous aviez écrit :

> « une matrice d'adjacence sous forme de listes de listes »

Annotation : « **alors ce n'est plus une matrice** ».

Le correcteur a raison, et l'erreur est instructive. Une matrice d'adjacence
**est** définie par son accès direct `A[x][y]` en O(1) — c'est ce qui la
caractérise et ce qui justifie son coût O(n²). Dès qu'on la range en listes
chaînées, on perd l'accès direct : on a une **liste d'adjacence**, qui est une
structure différente, avec d'autres coûts.

La règle à en tirer : **un nom de structure engage ses coûts.** Écrire « matrice »
promet O(1) en accès et O(n²) en stockage. Si votre implémentation ne tient pas
cette promesse, le nom est faux, et toute l'évaluation qui suit s'effondre.

## Comment rédiger une réponse « évaluer »

Quatre lignes, dans cet ordre. Le correcteur les cherche.

1. **Le besoin.** « L'arbre est construit par insertions successives : le critère
   dominant est la dynamicité. »
2. **Le tableau.** Les trois structures, les trois critères, des coûts chiffrés.
3. **Le verdict, justifié.** « On retient les listes chaînées **parce que** … »
4. **La réserve.** Ce que la structure ne donne pas et qu'il faut ajouter.

**Un coût chiffré à chaque ligne.** Le mot « évaluer » demande des O(·), pas des
adjectifs.
MD,
                'recap' => <<<'MD'
- Trois critères, toujours les mêmes : **accès, stockage, dynamicité.**
- **Matrice d'adjacence** : accès O(1), stockage O(n²), **statique**. Bonne pour
  un graphe **dense et figé**.
- **FS/APS** — *File des Successeurs* / *Adresse du Premier Successeur* : deux
  tableaux, stockage **minimal** `n + (n+m)`, **très rigide**.
- **Listes chaînées** : stockage O(n+m), ajout O(1), **dynamique**. Bonne pour un
  graphe **creux ou évolutif**.
- Pour un **arbre binaire** construit par insertions : listes chaînées — et il
  faut **ajouter l'information fils gauche / fils droit**, qu'aucune structure de
  graphe ne porte.
- **Un nom de structure engage ses coûts.** « Matrice sous forme de listes » n'est
  plus une matrice.
- Rédiger un « évaluer » : **besoin → tableau chiffré → verdict justifié →
  réserve.**
MD,
            ],

            /* ================= Séance 4 ================= */
            [
                'title' => 'Les parcours : largeur, profondeur, connexité',
                'chapitre' => 'G2',
                'duree_min' => 40,
                'prerequis' => "Les séances 2 et 3. Les parcours sont la brique de presque tous les algorithmes de l'exercice 1.",
                'intro' => <<<'MD'
Presque tout ce qu'on demande sur un graphe se ramène à un parcours : compter
des sommets, trouver un chemin, détecter un cycle, découper en composantes, trier
un arbre.

Deux parcours seulement, et ils ne diffèrent que par **une structure de données**.
C'est la plus belle économie du cours : changez une file en pile, et vous changez
d'algorithme.

Le sujet précise d'ailleurs que vous ne disposez que de **listes, files et
piles**. Ce n'est pas une restriction arbitraire : c'est un indice sur ce qu'on
attend de vous.
MD,
                'body' => <<<'MD'
## Le principe commun

Les deux parcours font la même chose :

1. on marque le sommet de départ et on le met dans une **structure d'attente** ;
2. tant que la structure n'est pas vide : on en **sort** un sommet, on le traite,
   et on y **met** ses voisins non encore marqués ;
3. le marquage évite de repasser deux fois.

**La seule différence est la nature de la structure d'attente.**

| Parcours | Structure | Ce que ça produit |
|---|---|---|
| **En largeur** | une **file** (FIFO — premier entré, premier sorti) | on visite niveau par niveau |
| **En profondeur** | une **pile** (LIFO — dernier entré, premier sorti) | on s'enfonce puis on remonte |

## Le parcours en largeur

```
Fonction parcoursLargeur(G, s)
début
    pour chaque sommet x de V faire marque[x] ← faux
    marque[s] ← vrai
    F ← file vide
    enfiler(F, s)
    tant que F non vide faire
        x ← défiler(F)
        traiter(x)
        pour chaque voisin y de x faire
            si non marque[y] alors
                marque[y] ← vrai
                enfiler(F, y)
```

**Le marquage se fait à l'enfilage, pas au défilage.** C'est l'erreur classique :
si on marque en sortant, un même sommet peut être enfilé plusieurs fois par des
voisins différents.

Ce que le parcours en largeur donne gratuitement :

- l'ordre de visite est celui des **distances croissantes** au départ ;
- donc il donne le **plus court chemin en nombre d'arêtes** — sur un graphe **non
  pondéré** uniquement.

## Le parcours en profondeur

Même code, une pile à la place de la file :

```
Fonction parcoursProfondeur(G, s)
début
    pour chaque sommet x de V faire marque[x] ← faux
    P ← pile vide
    empiler(P, s)
    tant que P non vide faire
        x ← dépiler(P)
        si non marque[x] alors
            marque[x] ← vrai
            traiter(x)
            pour chaque voisin y de x faire
                si non marque[y] alors empiler(P, y)
```

Ou, plus court, en récursif — la pile d'appels remplace la pile explicite :

```
Fonction profondeurRec(G, x)
début
    marque[x] ← vrai
    traiter(x)
    pour chaque voisin y de x faire
        si non marque[y] alors profondeurRec(G, y)
```

**Sachez écrire les deux formes.** La récursive est plus courte ; l'itérative
montre que vous avez compris que la récursivité *est* une pile. Et si l'énoncé
interdit la récursivité, il ne vous reste que la seconde.

## La complexité

La même pour les deux, et il faut savoir la justifier :

> Chaque sommet est traité **une fois** — grâce au marquage. Pour chaque sommet,
> on parcourt ses voisins ; au total, chaque arête est examinée une fois (deux en
> non orienté). D'où **O(n + m)**.

**Attention à la structure de données**, parce que ce résultat en dépend :

| Structure | Complexité du parcours |
|---|---|
| listes d'adjacence | **O(n + m)** |
| matrice d'adjacence | **O(n²)** — il faut balayer une ligne entière par sommet |

C'est un excellent point de justification, et il relie les séances 3 et 4.

## Ce que chaque parcours sert à faire

| Besoin | Parcours |
|---|---|
| plus court chemin en nombre d'arêtes | **largeur** |
| tester la connexité | l'un ou l'autre |
| composantes connexes | l'un ou l'autre |
| détecter un cycle | **profondeur** |
| parcourir un arbre dans l'ordre | **profondeur** |
| tri topologique | **profondeur** |

## La connexité

Le test tient en deux lignes, et c'est une question de cours fréquente :

> Lancer un parcours depuis un sommet quelconque. **Le graphe est connexe si et
> seulement si tous les sommets sont marqués à la fin.**

### Les composantes connexes

```
Fonction composantesConnexes(G)
début
    pour chaque sommet x faire composante[x] ← 0
    c ← 0
    pour chaque sommet x de V faire
        si composante[x] = 0 alors
            c ← c + 1
            parcourir depuis x en marquant composante[·] ← c
    retourner c
```

On relance un parcours depuis chaque sommet encore non marqué. Chaque relance
ouvre une nouvelle composante. La complexité reste **O(n + m)** : chaque sommet
n'est traité qu'une fois, tous parcours confondus.

### La forte connexité

Sur un graphe **orienté**, il faut des chemins dans les deux sens. La méthode du
cours :

1. parcourir depuis `x` dans `G` — on obtient les sommets **atteignables depuis**
   `x` ;
2. parcourir depuis `x` dans le **graphe inverse** `G⁻¹` (tous les arcs
   retournés) — on obtient les sommets **qui atteignent** `x` ;
3. `G` est fortement connexe si les deux parcours marquent **tous** les sommets.

Le graphe inverse se construit en O(n + m). La complexité totale reste O(n + m).

**Retenez le graphe inverse** : c'est l'astuce de la forte connexité, et un
exercice du cours porte précisément sur son calcul en représentation FS/APS.

## Les parcours d'arbre

Sur un arbre, le parcours en profondeur prend trois formes, selon le moment où
l'on traite le sommet. **C'est la clef de l'exercice 1 de janvier.**

| Nom | Ordre | Sur un arbre binaire de recherche |
|---|---|---|
| **préfixe** | sommet, gauche, droit | — |
| **infixe** | gauche, **sommet**, droit | **donne les valeurs triées** |
| **suffixe** | gauche, droit, sommet | — |

Le parcours **infixe** est celui qui trie. Retenez le mot : le sommet est traité
**au milieu**, entre les deux fils.

```
Fonction infixe(sommet)
début
    si sommet.gauche ≠ null alors infixe(sommet.gauche)
    traiter(sommet)
    si sommet.droit ≠ null alors infixe(sommet.droit)
```

## Comment rédiger un algorithme de parcours en épreuve

Le sujet exige « l'algorithme, avec l'explication de son fonctionnement et des
commentaires ». Voici le gabarit :

```
Fonction nomExplicite(paramètres)
  Données  : ce qu'on reçoit, et son type
  Résultat : ce qu'on rend
début
    ...                      % un commentaire par bloc
```

Puis, **sous** l'algorithme, deux ou trois phrases : « L'algorithme marque le
sommet de départ, puis… La complexité est en O(n + m) car chaque sommet est
traité une fois et chaque arête examinée une fois. »

**Toujours finir par la complexité, même si elle n'est pas demandée.** C'est
gratuit, et le sujet dit que les réponses non justifiées sont pénalisées.
MD,
                'recap' => <<<'MD'
- Les deux parcours sont **le même algorithme** ; seule la structure d'attente
  change : **file → largeur, pile → profondeur.**
- **On marque à l'entrée dans la structure**, pas à la sortie.
- La largeur donne le **plus court chemin en nombre d'arêtes** (non pondéré).
- Complexité **O(n + m)** en listes d'adjacence, **O(n²)** en matrice.
- **Connexe ⟺ tous les sommets marqués** après un seul parcours.
- Composantes : relancer depuis chaque sommet non marqué. Toujours O(n + m).
- **Forte connexité : un parcours dans `G`, un dans le graphe inverse `G⁻¹`.**
- Parcours d'arbre : **infixe = gauche, sommet, droit → donne les valeurs
  triées.**
- Sous chaque algorithme rendu : deux phrases d'explication **et la complexité**.
MD,
            ],

            /* ================= Séance 5 ================= */
            [
                'title' => 'Les plus courts chemins',
                'chapitre' => 'G2',
                'duree_min' => 35,
                'prerequis' => "La séance 4. On passe des graphes non pondérés aux graphes pondérés.",
                'intro' => <<<'MD'
Le parcours en largeur donne le plus court chemin quand toutes les arêtes se
valent. Dès qu'elles ont des **poids** différents, il ne suffit plus : le chemin
avec le moins d'arêtes n'est pas le moins cher.

Trois algorithmes dans le cours, et ils ne s'appliquent pas aux mêmes cas. Savoir
**lequel choisir et pourquoi** vaut autant de points que savoir le dérouler.
MD,
                'body' => <<<'MD'
## Les trois algorithmes

| Algorithme | Calcule | Poids négatifs | Complexité |
|---|---|---|---|
| **Parcours en largeur** | depuis un sommet, non pondéré | — | O(n + m) |
| **Dijkstra** | depuis **un** sommet | **non** | O(n²) |
| **Matriciel** *(Floyd-Warshall)* | entre **toutes** les paires | oui (sans circuit négatif) | **O(n³)** |
| **Bellman-Ford** | depuis un sommet | **oui** | O(n·m) |

La ligne à retenir : **Dijkstra ne supporte pas les poids négatifs.** C'est la
justification qu'on vous demandera.

Pourquoi ? Parce que Dijkstra **fige** un sommet dès qu'il le retient, en
supposant qu'on ne pourra plus faire mieux. Une arête négative rencontrée plus
tard pourrait raccourcir un chemin déjà figé.

## Dijkstra

Le principe :

1. `X` = les sommets **déjà traités** ; au départ, `X = {x₀}`.
2. `d[y]` = la meilleure distance connue de `x₀` à `y`. Au départ, `d[x₀] = 0` et
   `d[y] = ∞` partout ailleurs.
3. Répéter : **choisir le sommet `y` hors de `X` de plus petit `d[y]`**, le faire
   entrer dans `X`, et **relâcher** ses voisins :

   > si `d[y] + poids(y,z) < d[z]` alors `d[z] ← d[y] + poids(y,z)` et
   > `père[z] ← y`

4. S'arrêter quand tous les sommets sont dans `X`.

Le mot **relâchement** *(relaxation)* est celui du cours : employez-le.

### Le tableau à tenir en épreuve

C'est **la** forme attendue. Une ligne par itération, une colonne par sommet.

| Itération | Sommet choisi | a | b | c | d | e |
|---|---|---|---|---|---|---|
| 0 | — | **0** | ∞ | ∞ | ∞ | ∞ |
| 1 | a (0) | — | 4 (a) | 2 (a) | ∞ | ∞ |
| 2 | c (2) | — | 3 (c) | — | 6 (c) | ∞ |
| 3 | b (3) | — | — | — | 5 (b) | ∞ |
| 4 | d (5) | — | — | — | — | 9 (d) |

Quatre règles de tenue :

1. **Une ligne par itération**, jamais de ratures superposées.
2. Écrire **le père entre parenthèses** à côté de chaque distance : c'est ce qui
   permet de reconstruire le chemin, et c'est souvent la question suivante.
3. Barrer d'un tiret les sommets déjà entrés dans `X`.
4. À la fin, **reconstruire le chemin en remontant les pères**, et l'écrire.

### La reconstruction du chemin

```
Fonction chemin(pere, s, v)
début
    C ← pile vide
    x ← v
    tant que x ≠ s faire
        empiler(C, x)
        x ← pere[x]
    empiler(C, s)
    retourner C
```

On remonte de l'arrivée vers le départ, donc on obtient le chemin à l'envers :
**une pile le remet à l'endroit.** Voilà pourquoi le sujet vous autorise les
piles.

## La méthode matricielle

Elle calcule les plus courts chemins entre **toutes** les paires, à partir de la
matrice des poids.

```
Fonction plusCourtChMatriciel(G)
début
    A ← matrice des poids   % ∞ si pas d'arête, 0 sur la diagonale
    pour z ← 1 à n faire            % le sommet intermédiaire autorisé
        pour x ← 1 à n faire
            pour y ← 1 à n faire
                si A[x,z] + A[z,y] < A[x,y] alors
                    A[x,y] ← A[x,z] + A[z,y]
                    PCC[x,y] ← z            % on retient par où on passe
    retourner A, PCC
```

Trois boucles imbriquées : **O(n³)**.

**L'ordre des boucles n'est pas interchangeable.** La boucle sur `z` — le sommet
intermédiaire — doit être **la plus externe**. C'est l'erreur classique, et elle
casse l'algorithme : l'invariant est « après l'étape z, `A[x,y]` est le plus
court chemin n'utilisant que les sommets `1..z` comme intermédiaires ».

Cet algorithme est, au passage, un **exemple de programmation dynamique** : on
construit la solution en autorisant progressivement plus de sous-problèmes. On y
reviendra séance 9.

## Comment choisir, en épreuve

Une question fréquente : « quel algorithme utiliser, et pourquoi ? » La réponse
se déroule en trois tests :

1. **Les arêtes sont-elles pondérées ?** Non → parcours en largeur, O(n + m).
2. **Y a-t-il des poids négatifs ?** Oui → Bellman-Ford. Dijkstra est exclu, et
   il faut dire pourquoi.
3. **Veut-on une source ou toutes les paires ?** Une source → Dijkstra, O(n²).
   Toutes les paires → matriciel, O(n³).

Écrivez ces trois tests dans votre réponse. **C'est le raisonnement qui est noté,
pas seulement le nom de l'algorithme.**

## Les plus longs chemins

Le cours les traite dans la même section, et il faut connaître le piège :

> Chercher le plus **long** chemin dans un graphe quelconque est un problème
> **difficile** — on ne peut pas simplement inverser les signes et relancer
> Dijkstra, parce qu'on obtiendrait des poids négatifs.

En revanche, **sur un graphe sans circuit**, le plus long chemin se calcule
exactement comme le plus court, en remplaçant le minimum par un maximum. C'est
un cas classique de programmation dynamique.
MD,
                'recap' => <<<'MD'
- Non pondéré → **parcours en largeur**, O(n + m).
- **Dijkstra** : une source, **pas de poids négatifs**, O(n²). Il fige un sommet
  dès qu'il le retient — une arête négative ultérieure invaliderait ce choix.
- **Bellman-Ford** : accepte les poids négatifs, O(n·m).
- **Matriciel** *(Floyd-Warshall)* : toutes les paires, **O(n³)**, et la boucle
  sur le sommet intermédiaire `z` doit être **la plus externe**.
- Dijkstra en épreuve : **un tableau, une ligne par itération, le père entre
  parenthèses**, puis reconstruction du chemin **avec une pile**.
- Le mot du cours est **relâchement**.
- Choisir un algorithme = dérouler trois tests : pondéré ? négatif ? une source
  ou toutes les paires ?
- Le plus **long** chemin est difficile en général, facile **sans circuit**.
MD,
            ],

            /* ================= Séance 6 ================= */
            [
                'title' => "Les arbres et l'arbre binaire de recherche",
                'chapitre' => 'G3',
                'duree_min' => 40,
                'prerequis' => "Les séances 2, 3 et 4 — en particulier le parcours infixe. On traite l'exercice 1 de janvier en entier.",
                'intro' => <<<'MD'
On refait l'exercice 1 de janvier, les quatre questions, du début à la fin. Il
valait **7 points** et vous en avez eu 2.

Ce n'était pas un exercice difficile : c'était un **arbre binaire de recherche**,
la structure la plus classique de l'informatique. Le nom n'apparaissait pas dans
l'énoncé, mais tout le décrivait.

À la fin de la séance, vous saurez le reconnaître, le construire, et rédiger les
quatre réponses dans la forme que le sujet exige.
MD,
                'body' => <<<'MD'
## L'énoncé

> Trier les valeurs d'un tableau `T` de taille `N`, **toutes différentes**, en
> utilisant un **arbre binaire**. Le résultat va dans un tableau `TT`.
>
> 1.1 Évaluer l'intérêt de chacune des structures de données vues en cours.
> 1.2 Proposer une structure pour mémoriser facilement un arbre binaire. Justifier
> et décrire les fonctions associées, **sans en donner le code**.
> 1.3 **Proposer une solution** pour trier `T` en utilisant un arbre binaire.
> 1.4 **Donner une fonction** qui prend `T` et retourne `TT`.

Repérez d'emblée les verbes. **1.3 attend du français. 1.4 attend du code + une
explication.** C'est la séance 1 qui s'applique.

Et notez « toutes différentes » : ce n'est pas un détail de style, c'est ce qui
permet de ne pas gérer le cas d'égalité.

## L'arbre binaire de recherche

Voici la structure que l'énoncé décrit sans la nommer.

> Un **arbre binaire de recherche** est un arbre binaire tel que, pour tout
> sommet `s` :
> **toutes les valeurs du sous-arbre gauche sont inférieures à `s`**, et
> **toutes celles du sous-arbre droit lui sont supérieures.**

Et voici la propriété qui fait tout :

> **Le parcours infixe d'un arbre binaire de recherche donne les valeurs
> triées.**

C'est immédiat une fois la définition posée : infixe visite d'abord tout le
sous-arbre gauche — donc tout ce qui est plus petit — puis le sommet, puis tout
le sous-arbre droit. Par récurrence, c'est trié.

**Le tri par arbre binaire, c'est donc : insérer, puis parcourir en infixe.**

## Question 1.2 — la structure

*« Proposer une structure de données permettant de mémoriser facilement un arbre
binaire. Justifier son choix et décrire les fonctions associées, sans en donner
le code. »*

Trois choses demandées : une structure, une justification, des fonctions. Trois
choses à rendre.

**La structure.** Un type `Sommet` chaîné, qui porte sa valeur et des références
vers ses fils.

**La justification.** Reprenez la séance 3 : l'arbre est construit par insertions
successives, donc le critère dominant est la **dynamicité** ; les listes chaînées
ajoutent un sommet en O(1) quand la matrice imposerait O(n²) ; et un arbre est un
graphe creux (`m = n − 1`), donc la matrice gâcherait O(n²) de mémoire.

Et surtout : **une structure de graphe ordinaire ne distingue pas le fils gauche
du fils droit.** Il faut donc un type dédié.

**Les fonctions.** Cinq suffisent, et l'énoncé dit bien « sans en donner le
code » — on les décrit, on ne les écrit pas :

| Fonction | Ce qu'elle fait |
|---|---|
| `getValue()` | rend la valeur du sommet |
| `addLeft(v)` | crée un fils gauche portant la valeur `v` |
| `addRight(v)` | crée un fils droit portant la valeur `v` |
| `getLeft()` | rend le sommet fils gauche, ou `null` |
| `getRight()` | rend le sommet fils droit, ou `null` |

Le `null` compte : c'est lui qui dit « il n'y a pas de fils de ce côté », et
toute la suite de l'algorithme repose là-dessus.

L'annotation « **comment ces nœuds sont initialisés ?** » sur votre copie visait
exactement ce point. Il fallait dire qu'un sommet créé a ses deux fils à `null`.

## Question 1.3 — proposer la solution

*Du français. Pas de code.* Voici ce qui était attendu, et ça tient en un
paragraphe :

> L'algorithme construit un arbre binaire de recherche à partir des valeurs du
> tableau. La **première valeur devient la racine**. Chaque valeur suivante est
> insérée en partant de la racine et en descendant : si elle est **plus petite**
> que le sommet courant on va à **gauche**, sinon on va à **droite**. Quand on
> arrive à un endroit où il n'y a pas de fils du côté voulu, la valeur devient ce
> fils. Une fois toutes les valeurs placées, un **parcours infixe** (gauche,
> sommet, droit) recopie les valeurs dans `TT` : comme l'arbre est un arbre
> binaire de recherche, elles en sortent **triées par ordre croissant**.

Ce paragraphe répond à la question. Il dit **ce qu'on fait** et **pourquoi ça
marche**. C'est exactement ce que « proposer une solution » réclame.

## Question 1.4 — donner la fonction

*Code + explication + commentaires.* Trois algorithmes, parce que la fonction
demandée en appelle deux autres.

### L'insertion

```
Fonction ajoutVal(sommet, valeur)
  Données : sommet — le sommet courant ; valeur — la valeur à insérer
début
    si valeur < sommet.getValue() alors        % plus petit → à gauche
        g ← sommet.getLeft()
        si g = null alors sommet.addLeft(valeur)
        sinon ajoutVal(g, valeur)
    sinon                                       % plus grand → à droite
        d ← sommet.getRight()
        si d = null alors sommet.addRight(valeur)
        sinon ajoutVal(d, valeur)
```

### Le parcours infixe

```
Fonction collecteTri(sommet, TT, i)
  Données : i — indice courant dans TT, passé par référence
début
    g ← sommet.getLeft()
    si g ≠ null alors collecteTri(g, TT, i)     % tout le sous-arbre gauche
    TT[i] ← sommet.getValue()                   % puis le sommet lui-même
    i ← i + 1
    d ← sommet.getRight()
    si d ≠ null alors collecteTri(d, TT, i)     % puis le sous-arbre droit
```

**Le passage par référence de `i` est indispensable**, et c'est un point à
signaler explicitement : si `i` est passé par valeur, chaque appel récursif
repart du même indice et écrase les cases précédentes. Une phrase là-dessus dans
votre explication montre que vous avez vu le piège.

### La fonction demandée

```
Fonction triParArbre(T, n)
  Données  : T — tableau de n valeurs toutes différentes
  Résultat : TT — tableau des n valeurs triées
début
    racine ← nouveau Sommet(T[1])          % la première valeur est la racine
    pour k ← 2 à n faire
        ajoutVal(racine, T[k])             % insertion des n−1 autres
    i ← 1
    collecteTri(racine, TT, i)             % parcours infixe
    retourner TT
```

**Et en dessous, l'explication** — c'est elle qui manquait, et c'est elle qui
valait le « pas d'explication = 0 » :

> La fonction construit d'abord l'arbre binaire de recherche par insertions
> successives, puis en fait un parcours infixe qui remplit `TT` par valeurs
> croissantes. L'insertion d'une valeur coûte, au pire, la hauteur de l'arbre. Sur
> un arbre équilibré la hauteur est en O(log n), d'où un coût total de
> **O(n log n)** ; mais si le tableau est déjà trié, l'arbre dégénère en une
> chaîne de hauteur `n` et le coût devient **O(n²)**. Le parcours infixe visite
> chaque sommet une fois, en **O(n)**.

Cette complexité n'était pas demandée. **Donnez-la quand même** : le sujet
pénalise les réponses non justifiées, et le cas dégénéré est exactement le genre
de remarque qui rapporte.

## Le sens de l'inégalité — attention

Le corrigé officiel contient une contradiction qu'il faut connaître pour ne pas
s'y perdre. Sa **rédaction** dit « la valeur est mise à gauche si elle est plus
petite » — la convention standard, celle que j'ai écrite plus haut. Mais son
**pseudo-code** teste `si sommet.getValue() < valeur alors addLeft`, ce qui place
les valeurs **plus grandes** à gauche, et donnerait un tri **décroissant**.

Les deux conventions sont défendables — un tri décroissant reste un tri — mais
**il faut en choisir une et s'y tenir**. Sur votre copie :

> **Écrivez la convention en une phrase avant l'algorithme**, puis vérifiez que
> votre code la respecte.

C'est la même règle qu'on a vue en MIA : **relisez votre code contre votre
propre prose.** Ici, le corrigé officiel s'y est laissé prendre.

## « Où sont les tests ! »

Cette annotation, en face de la question 1.4, vise les **cas limites**. Dans un
algorithme sur les arbres, il y en a toujours trois, et il faut les traiter ou
au moins les mentionner :

1. **le tableau vide** (`n = 0`) — il n'y a pas de racine à créer ;
2. **le sommet sans fils** — le `si g = null` ;
3. **la racine** — elle n'a pas de père.

Une ligne suffit : « On suppose `n ≥ 1` ; sinon `TT` est vide. » Mais elle doit
être là.
MD,
                'recap' => <<<'MD'
- **Arbre binaire de recherche** : tout le sous-arbre gauche est plus petit que le
  sommet, tout le droit est plus grand.
- **Le parcours infixe d'un ABR donne les valeurs triées.** C'est tout l'exercice.
- Tri par arbre = **insérer les n valeurs, puis parcourir en infixe**.
- Coût : O(n log n) si l'arbre est équilibré, **O(n²) si le tableau est déjà
  trié** (l'arbre dégénère en chaîne). Le parcours est en O(n).
- 1.3 « proposer » → **un paragraphe en français**.
  1.4 « donner une fonction » → **code + explication + commentaires**.
- Décrire les cinq fonctions du type `Sommet`, et dire qu'un sommet naît avec ses
  **fils à `null`**.
- **L'indice `i` du parcours doit être passé par référence.** Le signaler.
- **Choisir une convention gauche/droite, l'écrire, et la respecter.** Le corrigé
  officiel se contredit sur ce point.
- Toujours mentionner les **cas limites** : tableau vide, sommet sans fils,
  racine.
MD,
            ],

            /* ================= Séance 7 ================= */
            [
                'title' => "L'arbre couvrant de poids minimal",
                'chapitre' => 'G3',
                'duree_min' => 30,
                'prerequis' => "Les séances 2 et 6. Cet algorithme est aussi le premier exemple de stratégie gloutonne — il prépare la séance 8.",
                'intro' => <<<'MD'
Un problème et deux algorithmes, très classiques, très demandés — et qui servent
de porte d'entrée à la programmation gloutonne, sujet de la séance suivante.

Le problème : relier tous les sommets d'un graphe pondéré **au moindre coût
total**. Câbler un réseau, poser des rails, relier des villes.

Les deux algorithmes font le même travail par deux chemins opposés, et savoir
dire **en quoi ils diffèrent** vaut autant que savoir les dérouler.
MD,
                'body' => <<<'MD'
## Le problème

> Étant donné un graphe **connexe, non orienté, pondéré**, trouver un **arbre
> couvrant** — un arbre qui contient tous les sommets — dont la **somme des poids
> des arêtes** est minimale.

Trois adjectifs dans l'énoncé, et chacun compte. **Connexe**, sinon il n'existe
pas d'arbre couvrant. **Non orienté**. **Pondéré**, sinon tous les arbres
couvrants se valent.

Rappel de la séance 2 : un arbre couvrant a exactement **`n − 1` arêtes**. C'est
la condition d'arrêt des deux algorithmes.

## Kruskal — par les arêtes

> **Trier les arêtes par poids croissant. Les prendre une par une, en gardant
> celle-ci si et seulement si elle ne crée pas de cycle. S'arrêter à `n − 1`
> arêtes.**

```
Fonction kruskal(G)
début
    trier les arêtes de E par poids croissant
    F ← (V, ∅)                      % forêt : tous les sommets, aucune arête
    pour chaque arête (x,y) dans l'ordre faire
        si x et y ne sont pas dans la même composante de F alors
            ajouter (x,y) à F       % sinon on créerait un cycle
    retourner F
```

Pendant l'exécution, `F` est une **forêt** — plusieurs arbres séparés — qui
fusionnent petit à petit. Ce n'est un arbre qu'à la fin.

**Complexité : O(m log m)**, dominée par le tri des arêtes.

## Prim — par les sommets

> **Partir d'un sommet. Faire grossir un seul arbre en ajoutant à chaque étape
> l'arête la moins chère qui relie l'arbre à un sommet extérieur.**

```
Fonction prim(G, x0)
début
    X ← {x0}                        % les sommets déjà dans l'arbre
    A ← ∅                           % les arêtes retenues
    tant que X ≠ V faire
        choisir l'arête (x,y) de poids minimal avec x ∈ X et y ∉ X
        ajouter (x,y) à A
        ajouter y à X
    retourner (X, A)
```

Ici, ce qu'on construit est **toujours un arbre**, du début à la fin. Il grossit
d'un sommet à chaque tour.

**Complexité : O(n²)** dans la version simple du cours.

## Les distinguer

C'est la question de cours la plus fréquente sur ce chapitre.

| | **Kruskal** | **Prim** |
|---|---|---|
| Ce qu'on trie | les **arêtes**, une fois pour toutes | rien : on cherche le minimum à chaque tour |
| Ce qu'on construit | une **forêt** qui fusionne | **un seul arbre** qui grossit |
| Le test à faire | « crée-t-on un **cycle** ? » | « le sommet est-il **hors de l'arbre** ? » |
| Complexité | **O(m log m)** | **O(n²)** |
| Meilleur si | graphe **creux** (peu d'arêtes à trier) | graphe **dense** |

**La dernière ligne est celle qui rapporte** : elle relie la densité, vue à la
séance 2, au choix de l'algorithme.

## Un exemple à dérouler

Cinq sommets, sept arêtes :

`AB=1 · AC=4 · BC=2 · BD=5 · CD=3 · CE=7 · DE=6`

### Kruskal

Arêtes triées : `AB=1, BC=2, CD=3, AC=4, BD=5, DE=6, CE=7`

| Arête | Cycle ? | Décision | Arbre |
|---|---|---|---|
| AB (1) | non | **prise** | AB |
| BC (2) | non | **prise** | AB, BC |
| CD (3) | non | **prise** | AB, BC, CD |
| AC (4) | **oui** (A-B-C) | rejetée | — |
| BD (5) | **oui** (B-C-D) | rejetée | — |
| DE (6) | non | **prise** | AB, BC, CD, DE |

Quatre arêtes = `n − 1` avec `n = 5`. **On s'arrête.** Poids total : 1+2+3+6 = **12**.

### Prim depuis A

| Étape | Arbre `X` | Arêtes sortantes | Minimum | On ajoute |
|---|---|---|---|---|
| 1 | {A} | AB=1, AC=4 | **AB=1** | B |
| 2 | {A,B} | AC=4, BC=2, BD=5 | **BC=2** | C |
| 3 | {A,B,C} | AC(interne), CD=3, CE=7, BD=5 | **CD=3** | D |
| 4 | {A,B,C,D} | CE=7, DE=6 | **DE=6** | E |

Même arbre, même poids **12**. Les deux algorithmes trouvent l'optimum — c'est un
théorème, pas une coïncidence.

**Présentez toujours sous cette forme tabulaire.** Une ligne par étape, la
décision et sa raison. Et **donnez le poids total à la fin** : c'est souvent la
dernière question.

## Pourquoi le glouton marche ici

C'est la transition vers la séance 8, et c'est une question de cours possible.

Les deux algorithmes sont **gloutons** : à chaque étape ils prennent le meilleur
choix local — l'arête la moins chère disponible — sans jamais revenir dessus.

Or on a vu en MIA, et on reverra séance 8, que le glouton se trompe souvent. Ici
il ne se trompe pas, et c'est une propriété remarquable :

> Le problème de l'arbre couvrant minimal possède la **propriété du choix
> glouton** : il existe toujours un arbre couvrant minimal contenant l'arête de
> poids minimal.

C'est ce théorème qui autorise Kruskal et Prim. **Retenez que le glouton doit
être justifié**, pas supposé. La séance 8 est entièrement là-dessus.
MD,
                'recap' => <<<'MD'
- Le problème suppose un graphe **connexe, non orienté, pondéré**. L'arbre
  couvrant a **`n − 1` arêtes** — c'est la condition d'arrêt.
- **Kruskal** : trier les arêtes, prendre celles qui **ne créent pas de cycle**.
  Une **forêt** qui fusionne. **O(m log m)**. Bon sur un graphe **creux**.
- **Prim** : partir d'un sommet, ajouter l'arête minimale **sortant de l'arbre**.
  **Un seul arbre** qui grossit. **O(n²)**. Bon sur un graphe **dense**.
- Les deux donnent le même poids optimal.
- Présentation en épreuve : **un tableau, une ligne par étape, la décision et sa
  raison**, puis le **poids total**.
- Les deux sont **gloutons**, et c'est légitime ici parce que le problème possède
  la **propriété du choix glouton** — ce qui n'est pas le cas en général.
MD,
            ],

        ];
    }
}