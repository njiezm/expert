<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Seance;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Le cours d'AGC, seconde partie : glouton, dynamique, programmation linéaire,
 * cycles.
 *
 * C'est ici que se trouvent les deux tiers des points de l'épreuve. L'exercice 2
 * porte chaque année sur la programmation dynamique ou gloutonne, l'exercice 3
 * sur la programmation linéaire — et l'exercice 3 pose les mêmes quatre
 * questions depuis au moins deux sessions.
 *
 * L'exercice 2 de janvier — la plus grande sous-séquence commune — avait été
 * traité en double boucle naïve, avec l'annotation « il ne faut pas revenir au
 * début à chaque fois », et la question de complexité laissée vide. Les séances
 * 9 et 10 y répondent en entier.
 *
 * Note de vérification : le contre-exemple donné par le corrigé officiel à la
 * question 2.5 (« abcd » / « aecd ») ne met pas son propre algorithme en défaut
 * — celui-ci y trouve bien l'optimum « acd ». Les contre-exemples proposés ici
 * ont été vérifiés en déroulant l'algorithme.
 */
class CoursAgc2Seeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'AGC')->first();

        if (! $subject) {
            return;
        }

        // Les sept premières séances sont posées par CoursAgcSeeder.
        $depart = 7;

        foreach ($this->seances() as $i => $seance) {
            $chapter = isset($seance['chapitre'])
                ? Chapter::where('subject_id', $subject->id)->where('code', $seance['chapitre'])->first()
                : null;

            unset($seance['chapitre']);

            Seance::updateOrCreate(
                ['subject_id' => $subject->id, 'slug' => Str::slug($seance['title'])],
                $seance + ['chapter_id' => $chapter?->id, 'position' => $depart + $i + 1]
            );
        }
    }

    /* ==================================================================== */

    private function seances(): array
    {
        return [

            /* ================= Séance 8 ================= */
            [
                'title' => 'Le glouton : quand il marche, quand il ment',
                'chapitre' => 'PG',
                'duree_min' => 35,
                'prerequis' => "La séance 7 : Kruskal et Prim sont des algorithmes gloutons. On généralise.",
                'intro' => <<<'MD'
La programmation gloutonne est la stratégie la plus simple qui soit : **à chaque
étape, prendre ce qui paraît le mieux sur le moment, et ne jamais revenir
dessus.**

Elle a deux visages. Sur l'arbre couvrant minimal, elle donne l'optimum —
Kruskal et Prim en sont la preuve. Sur d'autres problèmes, elle se trompe, parfois
lourdement.

Tout le chapitre consiste à savoir **dans quel cas on est**, et à le **justifier**.
En janvier, la question 2.2 demandait de « proposer une solution gloutonne et
justifier son caractère glouton », et la question 2.5 de prouver si elle donnait
l'optimum. Deux questions sur ce seul point.
MD,
                'body' => <<<'MD'
## La définition

> Un algorithme est **glouton** s'il construit la solution **étape par étape**,
> en faisant à chaque étape le choix qui **optimise le critère localement**, et
> **sans jamais remettre un choix en cause**.

Trois éléments, et il faut les nommer quand on « justifie le caractère glouton » :

1. une construction **incrémentale** ;
2. un **critère local** de choix — le plus petit poids, la fin la plus proche, le
   plus grand rapport ;
3. **aucun retour en arrière**.

## Les deux propriétés qui rendent le glouton correct

Le cours les appelle les « éléments de stratégie gloutonne ». **Ce sont elles
qu'on vous demande de vérifier.**

### 1. La propriété du choix glouton

> Il existe une solution optimale qui **contient le choix localement optimal**.

Autrement dit : prendre le meilleur maintenant ne ferme jamais la porte à
l'optimum.

### 2. La sous-structure optimale

> Une solution optimale du problème contient les solutions optimales de ses
> sous-problèmes.

Une fois le premier choix fait, il reste un problème de même nature, plus petit,
qu'il suffit de résoudre optimalement.

**Si les deux tiennent, le glouton donne l'optimum. Si la première tombe, il se
trompe.**

Notez bien : la sous-structure optimale est aussi la propriété de la
programmation dynamique. **La différence entre les deux méthodes tient à la
première propriété.**

## Le choix d'activités — le cas d'école

C'est l'exemple du cours, et il faut le savoir dérouler.

> On dispose de `n` activités, chacune avec une heure de début `s[i]` et une
> heure de fin `f[i]`. Deux activités sont **compatibles** si elles ne se
> chevauchent pas. Trouver le **plus grand nombre** d'activités compatibles.

**La bonne stratégie : trier par heure de fin croissante, et prendre chaque
activité compatible avec la dernière retenue.**

```
Fonction choixActivites(s, f)     % f trié par ordre croissant
début
    A ← {1}                        % la première activité qui se termine
    k ← 1
    pour i ← 2 à n faire
        si s[i] ≥ f[k] alors       % compatible avec la dernière retenue
            A ← A ∪ {i}
            k ← i
    retourner A
```

**Le critère est « la fin la plus proche », et c'est le point à justifier :**

> En prenant l'activité qui se termine le plus tôt, on **laisse le maximum de
> temps libre** pour les suivantes. Aucune autre première activité ne peut donc
> permettre d'en caser davantage.

Complexité : **O(n log n)** avec le tri, **O(n)** si les activités sont déjà
triées.

### Les mauvais critères

Instructif, parce qu'ils semblent tous raisonnables :

| Critère | Marche ? |
|---|---|
| la **fin** la plus proche | **oui** |
| le **début** le plus tôt | non — une activité qui commence tôt peut durer toute la journée |
| la **plus courte** durée | non |
| celle qui **chevauche le moins** d'autres | non |

**Le seul qui marche est « la fin la plus proche ».** C'est exactement pourquoi
« justifier le caractère glouton » ne suffit pas : il faut aussi justifier **ce
critère-là**.

## La coloration de graphes — le glouton qui approche

> Colorer les sommets d'un graphe de sorte que **deux sommets voisins n'aient
> jamais la même couleur**, avec le moins de couleurs possible.

Le glouton : parcourir les sommets dans un ordre, et donner à chacun **la plus
petite couleur non utilisée par ses voisins**.

Résultat : ça colore correctement, mais **pas forcément avec le minimum de
couleurs**. Le nombre obtenu dépend même de l'ordre de parcours.

C'est un **algorithme d'approximation** : il donne une **borne supérieure** sur
le nombre chromatique, rapidement, sans garantir l'optimum. Le vocabulaire compte.

## Quand le glouton ment

Deux contre-exemples à connaître, parce qu'ils s'écrivent en trois lignes.

### Le rendu de monnaie

Rendre 6 centimes avec les pièces 1, 3 et 4.

- **Glouton** — prendre la plus grosse pièce possible : 4, puis 1, puis 1.
  **Trois pièces.**
- **Optimum** : 3 + 3. **Deux pièces.**

Le glouton fonctionne sur le système européen (1, 2, 5, 10…) mais **pas sur un
système quelconque**. Un système où il marche est dit *canonique*.

### Le sac à dos

Un sac de capacité 10. Trois objets :

| Objet | Poids | Valeur |
|---|---|---|
| A | 6 | 30 |
| B | 5 | 20 |
| C | 5 | 20 |

- **Glouton** par valeur décroissante : on prend A (30), il reste 4 de place,
  rien ne rentre. **Total 30.**
- **Optimum** : B + C. **Total 40.**

Il faut distinguer deux variantes, et c'est un piège classique :

| Variante | Méthode correcte |
|---|---|
| **sac à dos fractionnaire** (on peut couper les objets) | **glouton** par valeur/poids décroissant — **optimal** |
| **sac à dos entier** (objet entier ou rien) | **programmation dynamique** |

## Comment rédiger

### « Proposer une solution gloutonne et justifier son caractère glouton »

Trois phrases :

1. **Le critère local** : « à chaque étape, on prend … »
2. **L'absence de retour en arrière** : « ce choix n'est jamais remis en cause. »
3. **Pourquoi ce critère** : « on prend … parce que cela laisse le plus de … »

### « Cette solution donne-t-elle toujours l'optimum ? Prouvez. »

Deux cas, deux formes de preuve — et elles ne se ressemblent pas :

| Réponse | Ce qu'il faut écrire |
|---|---|
| **Non** | **un contre-exemple**, et on **déroule l'algorithme dessus** pour montrer ce qu'il produit, puis on donne l'optimum. |
| **Oui** | vérifier les **deux propriétés** : choix glouton et sous-structure optimale. |

**Un contre-exemple non déroulé ne prouve rien**, et c'est une erreur qu'on
trouve jusque dans les corrigés. Le contre-exemple donné par le corrigé officiel
de janvier — les chaînes « abcd » et « aecd » — ne met en réalité **pas** en
défaut son propre algorithme : celui-ci y trouve bien la solution optimale
« acd ». On verra à la séance suivante des contre-exemples vérifiés.

La règle : **écrivez le contre-exemple, puis déroulez l'algorithme dessus, ligne
par ligne, et comparez à l'optimum.** Trois lignes de plus, et la preuve tient.
MD,
                'recap' => <<<'MD'
- **Glouton** = construction incrémentale + **critère local** + **aucun retour en
  arrière**. Nommer les trois quand on « justifie le caractère glouton ».
- Il donne l'optimum si **(1) propriété du choix glouton** — un optimum contient
  le choix local — **et (2) sous-structure optimale**.
- La sous-structure optimale vaut aussi pour la programmation dynamique. **C'est
  la première propriété qui distingue les deux méthodes.**
- **Choix d'activités : trier par fin croissante.** C'est le seul critère qui
  marche, parce qu'il laisse le maximum de temps libre.
- **Coloration** : le glouton colore correctement mais pas avec le minimum. C'est
  un **algorithme d'approximation** — il donne une **borne supérieure**.
- Contre-exemples à retenir : **rendu de monnaie 6 avec {1,3,4}** (glouton 3
  pièces, optimum 2) et le **sac à dos entier**.
- **Sac fractionnaire → glouton optimal. Sac entier → programmation dynamique.**
- Une preuve par contre-exemple exige de **dérouler l'algorithme** dessus.
MD,
            ],

            /* ================= Séance 9 ================= */
            [
                'title' => 'La programmation dynamique : reconnaître le problème',
                'chapitre' => 'PD',
                'duree_min' => 35,
                'prerequis' => "La séance 8. C'est le chapitre de l'exercice 2 de janvier, noté 2 sur 6.",
                'intro' => <<<'MD'
Voici le chapitre où votre copie de janvier a le plus perdu, et pour une raison
qui tient en une annotation :

> « **Il ne faut pas revenir au début à chaque fois** »

Cette phrase est un diagnostic parfait. Elle dit que votre boucle interne
**recalculait** ce qui avait déjà été calculé. Or c'est très exactement le
symptôme qui doit déclencher le réflexe :

> **Si on recalcule les mêmes choses, c'est de la programmation dynamique.**

Aujourd'hui : comment reconnaître le cas, et le plan en quatre questions que
votre enseignant utilise pour l'évaluer.
MD,
                'body' => <<<'MD'
## L'idée

> **Résoudre chaque sous-problème une seule fois, et retenir le résultat.**

Rien de plus. Toute la difficulté est de repérer que les sous-problèmes se
répètent, et de trouver la bonne façon de les indexer.

## Les deux conditions

Un problème relève de la programmation dynamique si :

1. **il possède la sous-structure optimale** — la solution optimale se construit à
   partir des solutions optimales de sous-problèmes ;
2. **les sous-problèmes se chevauchent** — le même sous-problème revient plusieurs
   fois dans l'arbre des appels.

**La deuxième condition est la clef**, parce que c'est elle qui distingue la
programmation dynamique de « diviser pour régner ».

| | Sous-structure optimale | Sous-problèmes | Méthode |
|---|---|---|---|
| Diviser pour régner | oui | **indépendants** | récursion simple *(tri fusion)* |
| **Programmation dynamique** | oui | **qui se chevauchent** | **mémoriser** |
| Glouton | oui | un seul choix, jamais repris | choix local |

## Fibonacci — l'exemple du cours

La version naïve :

```
Fonction FiboRec(n)
début
    si n ≤ 2 alors retourner 1
    retourner FiboRec(n−1) + FiboRec(n−2)
```

Déroulez `FiboRec(5)` :

```
                    F(5)
              ┌──────┴──────┐
            F(4)           F(3)
          ┌──┴──┐        ┌──┴──┐
        F(3)   F(2)    F(2)   F(1)
      ┌──┴──┐
    F(2)   F(1)
```

**`F(3)` est calculé deux fois. `F(2)` trois fois.** Et ça empire
exponentiellement : la complexité est en **O(φⁿ)**, où φ ≈ 1,618.

Voilà le chevauchement. Le remède est immédiat.

## Les deux formes

### Descendante — la mémoïsation

On garde la récursion, on ajoute un cache.

```
Fonction FiboMemo(n)
  Données : M — tableau initialisé à 0, partagé entre les appels
début
    si n ≤ 2 alors retourner 1
    si M[n] ≠ 0 alors retourner M[n]      % déjà calculé : on le rend
    M[n] ← FiboMemo(n−1) + FiboMemo(n−2)  % sinon on calcule et on retient
    retourner M[n]
```

Le mot du cours est **mémoïsation**. Deux lignes ajoutées, et on passe
d'exponentiel à linéaire.

### Ascendante — le remplissage de table

On abandonne la récursion et on remplit un tableau **du plus petit au plus
grand**.

```
Fonction FiboDyn(n)
  Données : FiboVal — tableau d'entiers de dimension n
début
    FiboVal[1] ← 1
    FiboVal[2] ← 1
    pour i ← 3 à n faire
        FiboVal[i] ← FiboVal[i−1] + FiboVal[i−2]
    retourner FiboVal[n]
```

**O(n) en temps, O(n) en mémoire.**

| | Mémoïsation | Table |
|---|---|---|
| forme | récursive | itérative |
| ordre | descendant | **ascendant** |
| calcule | seulement les sous-problèmes utiles | **tous** |
| risque | débordement de pile | — |

**En épreuve, préférez la table.** Elle est plus facile à dérouler à la main, et
c'est la forme que les corrigés emploient.

## Le plan en quatre questions

Votre enseignant structure l'exercice de programmation dynamique toujours de la
même manière. Voici les questions de la session 2024-2025, mot pour mot :

> 2.1 Donner la **structure de la solution optimale** en fonction des
> sous-problèmes.
> 2.2 **Définir récursivement** la solution optimale et **montrer que
> l'application directe** de cette définition donne un algorithme **exponentiel**.
> 2.3 Donner l'**algorithme** de calcul d'une solution optimale en programmation
> dynamique.
> 2.4 Quelle est la **complexité** de cet algorithme ?

**C'est le plan officiel, et il est aussi la méthode.** Suivez-le même quand les
questions ne sont pas posées ainsi.

### 1 — La structure de la solution optimale

On répond à : **de quels sous-problèmes dépend le résultat ?** On nomme la
quantité qu'on va calculer.

> « Notons `C[i][j]` la longueur de la plus grande sous-séquence commune aux
> `i` premiers caractères de A et aux `j` premiers de B. »

**Cette phrase est la plus importante de tout l'exercice.** Tant qu'on n'a pas
nommé la quantité et dit précisément de quoi elle dépend, on ne peut rien écrire
d'autre.

### 2 — La définition récursive

On écrit la relation de récurrence, **avec ses cas de base**, et on montre que
l'appliquer directement explose.

L'argument d'explosion est toujours le même : **on dessine l'arbre des appels et
on montre qu'un même sous-problème y apparaît plusieurs fois.** Puis on compte :
« il y a `2ⁿ` appels alors qu'il n'existe que `n×m` sous-problèmes distincts. »

### 3 — L'algorithme

On remplit la table. Trois choses à préciser, et on les oublie souvent :

- les **dimensions** de la table ;
- l'**initialisation** — les cas de base ;
- l'**ordre de remplissage** — il faut que chaque case dépende de cases déjà
  remplies.

### 4 — La complexité

Elle se lit sur la table :

> **nombre de cases × coût de remplissage d'une case.**

Pour une table `n × m` remplie en O(1) par case : **O(n·m)**, en temps comme en
mémoire.

**Cette question a été laissée vide en janvier.** Elle se répond en une ligne, et
elle valait des points.

## Bellman-Ford, un exemple sur les graphes

Le plus court chemin depuis une source, avec poids négatifs autorisés, est un
problème de programmation dynamique.

> `d[k][y]` = le coût du plus court chemin de la source à `y` **en au plus `k`
> arêtes**.
>
> `d[k][y] = min( d[k−1][y] , min sur les arcs (x,y) de d[k−1][x] + poids(x,y) )`

On remplit pour `k` de 1 à `n−1` — un chemin élémentaire a au plus `n−1` arêtes.
**Complexité O(n·m).**

Reconnaissez la structure : une table, un indice qui croît, chaque case
construite à partir de la ligne précédente. **C'est toujours la même forme.**

## Le réflexe à installer

Trois signaux dans un énoncé, et c'est de la programmation dynamique :

| Le signal | Exemple |
|---|---|
| « **plus grand** / **plus petit** / **optimal** » avec des **choix successifs** | plus grande sous-séquence, plus court chemin |
| une solution récursive qui **recalcule** | « il ne faut pas revenir au début à chaque fois » |
| deux dimensions naturelles — deux chaînes, une grille | sous-séquence, parcours de damier |

Et le signal négatif : **si un critère local suffit et qu'on peut le prouver,
c'est du glouton.** Sinon, c'est dynamique.
MD,
                'recap' => <<<'MD'
- **Résoudre chaque sous-problème une seule fois, et retenir le résultat.**
- Deux conditions : **sous-structure optimale** et **chevauchement des
  sous-problèmes**. Le chevauchement est ce qui distingue de « diviser pour
  régner ».
- **Mémoïsation** (descendante, récursive + cache) ou **table** (ascendante,
  itérative). **En épreuve, la table.**
- Fibonacci naïf : **O(φⁿ)**. Avec table : **O(n)**.
- **Le plan officiel en quatre questions** : structure de la solution optimale ·
  définition récursive + explosion · algorithme · complexité.
- La phrase qui débloque tout : **« Notons `C[i][j]` … »** — nommer la quantité
  et dire de quoi elle dépend.
- Préciser toujours : **dimensions, initialisation, ordre de remplissage**.
- **Complexité = nombre de cases × coût par case.**
- Signal d'alerte : **« il ne faut pas revenir au début à chaque fois »** = des
  sous-problèmes recalculés = programmation dynamique.
MD,
            ],

            /* ================= Séance 10 ================= */
            [
                'title' => 'La plus grande sous-séquence commune, de bout en bout',
                'chapitre' => 'PD',
                'duree_min' => 40,
                'prerequis' => "La séance 9, et son plan en quatre questions. On refait l'exercice 2 de janvier en entier.",
                'intro' => <<<'MD'
On applique le plan de la séance précédente à l'exercice de janvier, les cinq
questions, avec la table remplie case par case.

C'est l'exercice le plus classique de tout le domaine, et il vaut la peine de le
connaître par cœur : la même table, avec la même récurrence, résout la distance
d'édition, le plus long préfixe commun, et l'alignement de séquences.

Une précision d'abord, parce qu'elle change la lecture du sujet : **l'énoncé
demandait explicitement une solution gloutonne** aux questions 2.2 et 2.3, puis
demandait à la question 2.5 si elle était optimale. La réponse attendue est
**non** — et la vraie solution est dynamique.
MD,
                'body' => <<<'MD'
## Le problème

> Une **sous-séquence** d'une chaîne est une suite de caractères qui respecte
> l'ordre de la chaîne initiale, **sans être forcément contigus**. Ainsi `ojur`
> est une sous-séquence de `Bonjour`.
>
> Calculer une **plus grande sous-séquence commune** à deux chaînes `A` et `B`.

Le mot à ne pas manquer : **sous-séquence**, pas sous-chaîne. Une sous-chaîne est
contiguë ; une sous-séquence ne l'est pas.

## Question 2.1 — l'exemple, et le problème du glouton

> *Quelle est la plus grande sous-séquence entre `aaatt` et `taa` ? Quel problème
> cela pose-t-il dans le cas d'une solution gloutonne ?*

La plus grande sous-séquence commune est **`aa`**, de longueur 2.

Vérifions qu'on ne fait pas mieux. `taa` contient un `t` et deux `a`. Dans
`aaatt`, le `t` n'apparaît qu'**après** tous les `a`. Or dans `taa`, le `t` est
**avant** les `a`. Impossible donc de prendre le `t` **et** un `a` en respectant
l'ordre dans les deux chaînes. Il reste `aa`.

**Le problème pour un glouton :** une stratégie naturelle consiste à parcourir la
plus courte chaîne, `taa`, et à chercher ses caractères l'un après l'autre dans
`aaatt`. Le premier caractère est `t`, qu'on trouve — mais tout à la fin de
`aaatt`. Après l'avoir consommé, il ne reste plus rien à apparier, et on obtient
`t`, de longueur 1, au lieu de 2.

**Voilà le défaut du glouton : un choix localement possible ferme l'accès à une
meilleure solution globale.**

## Question 2.2 — la solution gloutonne

Le critère : parcourir `A` de gauche à droite et, pour chaque caractère,
l'apparier à sa **première occurrence disponible** dans `B`.

```
Fonction pgssGloutonne(A, B, n, m)
  Sorties : pgss — une sous-séquence commune
début
    j ← 0                            % position courante dans B
    pgss ← ∅
    pour i ← 1 à n faire
        k ← j ; trouve ← faux
        tant que (k < m) ∧ (¬trouve) faire
            si A[i] = B[k] alors
                trouve ← vrai
                j ← k + 1            % on n'y reviendra pas
                pgss ← pgss + A[i]
            sinon k ← k + 1
    retourner pgss
```

**La justification du caractère glouton :** on apparie chaque caractère le plus
tôt possible dans `B`, ce qui laisse le maximum de caractères disponibles après
lui ; et **on ne revient jamais** sur un appariement, puisque `j` ne fait
qu'avancer.

## Question 2.4 — la complexité

Deux boucles imbriquées sur les deux chaînes : **O(n·m)**.

C'est cette question qui était vide sur votre copie. Une ligne. Et voici la
remarque qui vaut le point supplémentaire :

> La solution dynamique de la question suivante a **la même complexité O(n·m)** —
> et elle, elle donne l'optimum. **Le glouton ne coûte pas moins cher ; il rend
> seulement une moins bonne réponse.**

## Question 2.5 — est-elle optimale ?

**Non.** Et il faut le prouver par un contre-exemple **déroulé**.

### Un contre-exemple vérifié

`A = cab`, `B = abc`.

| i | `A[i]` | recherche dans `B` à partir de `j` | résultat |
|---|---|---|---|
| 1 | `c` | j=0 : `a`≠`c`, `b`≠`c`, `c`=`c` en position 2 | pris, `j ← 3`, pgss = `c` |
| 2 | `a` | j=3 : fin de `B` | non trouvé |
| 3 | `b` | j=3 : fin de `B` | non trouvé |

Le glouton rend **`c`**, de longueur **1**.

Or `ab` est une sous-séquence de `cab` (positions 2 et 3) et de `abc` (positions
1 et 2). **L'optimum est 2.**

Un contre-exemple encore plus net : `A = xayb`, `B = aybx`. Le glouton apparie
le `x` de `A` avec le `x` final de `B`, épuise `B`, et rend **`x`** — longueur 1,
alors que **`ayb`** est commune aux deux, longueur **3**.

### Attention au contre-exemple du corrigé

Le corrigé officiel propose « abcd » et « aecd », en affirmant que le glouton
rend `cd`. En déroulant l'algorithme ci-dessus, on obtient `acd` — soit
l'optimum. **Ce contre-exemple ne met pas l'algorithme en défaut.**

La leçon vaut plus que l'anecdote : **un contre-exemple doit être déroulé.** Ne
l'annoncez jamais sans montrer, ligne par ligne, ce que l'algorithme produit
dessus.

## Question 2.3 — la vraie solution

Appliquons le plan en quatre temps de la séance 9.

### 1. La structure de la solution optimale

> Notons **`C[i][j]`** la longueur de la plus grande sous-séquence commune aux
> **`i` premiers caractères de `A`** et aux **`j` premiers caractères de `B`**.
>
> La réponse cherchée est `C[n][m]`.

### 2. La définition récursive

Regardons les deux derniers caractères, `A[i]` et `B[j]`. Deux cas seulement :

**Ils sont égaux.** Alors on a tout intérêt à les apparier, et il reste à
résoudre le problème sur `A[1..i−1]` et `B[1..j−1]` :

```
C[i][j] = C[i−1][j−1] + 1
```

**Ils diffèrent.** L'un des deux, au moins, ne servira pas. On essaie donc les
deux façons d'en abandonner un, et on garde la meilleure :

```
C[i][j] = max( C[i−1][j] , C[i][j−1] )
```

Et les cas de base : une chaîne vide n'a aucune sous-séquence commune.

```
C[i][0] = 0    pour tout i
C[0][j] = 0    pour tout j
```

**Pourquoi l'application directe explose.** Dans le second cas, chaque appel en
engendre **deux**. L'arbre des appels a donc une taille de l'ordre de `2^(n+m)`.
Or il n'existe que **`(n+1)×(m+1)` sous-problèmes distincts** : les mêmes couples
`(i, j)` reviennent un nombre exponentiel de fois. **C'est le chevauchement.**

### 3. L'algorithme

```
Fonction pgssDyn(A, B, n, m)
  Données  : C — table d'entiers de dimension (n+1) × (m+1)
  Résultat : la longueur de la plus grande sous-séquence commune
début
    pour i ← 0 à n faire C[i][0] ← 0        % initialisation : chaîne vide
    pour j ← 0 à m faire C[0][j] ← 0
    pour i ← 1 à n faire                     % ligne par ligne
        pour j ← 1 à m faire                 % de gauche à droite
            si A[i] = B[j] alors
                C[i][j] ← C[i−1][j−1] + 1    % on apparie les deux caractères
            sinon
                C[i][j] ← max(C[i−1][j], C[i][j−1])
    retourner C[n][m]
```

**L'ordre de remplissage est ligne par ligne, de gauche à droite.** C'est ce qui
garantit que les trois cases dont on a besoin — au-dessus, à gauche, en diagonale
— sont déjà calculées.

### 4. La complexité

`(n+1) × (m+1)` cases, remplies en **O(1)** chacune :

> **O(n·m) en temps, O(n·m) en mémoire.**

## La table pour `aaatt` et `taa`

Remplissons-la entièrement. En ligne `A = aaatt`, en colonne `B = taa`.

| | | **t** | **a** | **a** |
|---|---|---|---|---|
| | **0** | 0 | 0 | 0 |
| **a** | 0 | 0 | **1** | 1 |
| **a** | 0 | 0 | 1 | **2** |
| **a** | 0 | 0 | 1 | 2 |
| **t** | 0 | **1** | 1 | 2 |
| **t** | 0 | 1 | 1 | 2 |

Suivons deux cases, pour être sûr du mécanisme :

- **ligne `a`, colonne `a`** *(la première)* : les caractères sont égaux, donc
  `C = C[diagonale] + 1 = 0 + 1 = 1`.
- **ligne `t`, colonne `t`** : égaux, `C = C[diagonale] + 1 = 0 + 1 = 1`.
- **ligne `t`, colonne `a`** *(la dernière)* : `t ≠ a`, donc
  `C = max(au-dessus, à gauche) = max(2, 1) = 2`.

**Résultat : `C[5][3] = 2`.** C'est bien la longueur de `aa`, comme annoncé à la
question 2.1.

## Retrouver la sous-séquence, pas seulement sa longueur

La table donne la longueur. Pour la chaîne, on **remonte** depuis `C[n][m]` :

```
Fonction reconstruire(C, A, B, n, m)
début
    S ← ∅ ; i ← n ; j ← m
    tant que i > 0 ∧ j > 0 faire
        si A[i] = B[j] alors
            S ← A[i] + S            % on ajoute EN TÊTE
            i ← i − 1 ; j ← j − 1   % on va en diagonale
        sinon si C[i−1][j] ≥ C[i][j−1] alors i ← i − 1
        sinon j ← j − 1
    retourner S
```

On remonte de la fin vers le début, donc **on ajoute chaque caractère en tête**.
Coût **O(n + m)**, négligeable devant le remplissage.

## Le tableau de comparaison

C'est la conclusion de l'exercice, et elle vaut d'être écrite :

| | Glouton | Programmation dynamique |
|---|---|---|
| Temps | O(n·m) | **O(n·m)** |
| Mémoire | O(1) | O(n·m) |
| Résultat | une sous-séquence commune | **la plus grande** |
| Optimal | **non** | **oui** |

**À complexité en temps égale, le glouton rend une réponse strictement moins
bonne.** C'est la phrase qui montre qu'on a compris l'exercice.
MD,
                'recap' => <<<'MD'
- **Sous-séquence** ≠ sous-chaîne : elle n'est **pas contiguë**.
- `pgsc(aaatt, taa) = aa`, longueur **2**. Le `t` est après les `a` dans l'une et
  avant dans l'autre : on ne peut pas prendre les deux.
- **`C[i][j]`** = longueur de la plus grande sous-séquence commune aux `i`
  premiers de A et `j` premiers de B.
- **Si `A[i] = B[j]` : `C[i−1][j−1] + 1`. Sinon : `max(C[i−1][j], C[i][j−1])`.**
  Cas de base : ligne 0 et colonne 0 à zéro.
- Remplissage **ligne par ligne, de gauche à droite**. **O(n·m)** en temps et en
  mémoire.
- Reconstruction en remontant depuis `C[n][m]`, en ajoutant **en tête**.
- **Le glouton coûte le même O(n·m) et rend une moins bonne réponse.**
- Contre-exemples **vérifiés** : `cab`/`abc` → glouton 1, optimum 2 ;
  `xayb`/`aybx` → glouton 1, optimum 3.
- **Un contre-exemple doit être déroulé** — celui du corrigé officiel ne met pas
  son propre algorithme en défaut.
MD,
            ],

            /* ================= Séance 11 ================= */
            [
                'title' => 'Écrire un programme linéaire',
                'chapitre' => 'PL',
                'duree_min' => 30,
                'prerequis' => "Aucun prérequis des séances précédentes. C'est l'exercice 3, celui qui pose les mêmes questions chaque année.",
                'intro' => <<<'MD'
On aborde l'exercice le plus rentable de l'épreuve.

L'exercice 3 pose les mêmes quatre questions d'une session à l'autre — écrire le
programme linéaire, résoudre graphiquement, résoudre par le simplexe, donner la
solution — et seuls les chiffres changent. Des tablettes de chocolat en
2024-2025, des caisses de fruits en janvier 2026.

Il n'y a rien à inventer. Il y a une méthode à appliquer. Aujourd'hui, la
modélisation : les trois premières questions.
MD,
                'body' => <<<'MD'
## Ce qu'est un programme linéaire

Trois éléments, et l'énoncé les demande dans cet ordre :

1. les **variables de décision** — ce qu'on cherche ;
2. la **fonction objectif** — ce qu'on maximise ou minimise ;
3. les **contraintes** — ce qui limite.

« Linéaire » veut dire que tout est du premier degré : pas de `x²`, pas de
`x₁·x₂`, pas de racine.

## L'énoncé de janvier

> Un producteur a **24 kilos de poires** et **32 kilos de pommes**, et dispose de
> **12 caisses**. Il fait :
> — des caisses de **3 kg de poires et 3 kg de pommes**, vendues **5 €**,
> — des caisses de **2 kg de poires et 3 kg de pommes**, vendues **4 €**,
> — des caisses de **1 kg de poires et 2 kg de pommes**, vendues **1 €**.
> Il veut le meilleur profit, **en vendant toutes ses caisses**.

## Question 3.1 — les variables

**La question à se poser : de quoi choisit-on la quantité ?**

Ce n'est pas les kilos de fruits — ils sont donnés. C'est le **nombre de caisses
de chaque type**.

> `x₁` = le nombre de caisses à 5 € · `x₂` = les caisses à 4 € · `x₃` = les
> caisses à 1 €.

**Définissez vos variables en toutes lettres, avec leur unité.** C'est un point
gratuit, et sans lui rien de ce qui suit ne se comprend.

## Question 3.2 — la fonction objectif

« Il souhaite tirer le meilleur profit ». On **maximise** la recette :

```
maximiser   z = 5x₁ + 4x₂ + x₃
```

Chaque variable est pondérée par son prix. Dites toujours **maximiser** ou
**minimiser** : c'est la moitié de la réponse.

## Question 3.3 — les contraintes

**Une ressource = une contrainte.** On les prend une par une.

**Les pommes.** Chaque caisse de type 1 en consomme 3, de type 2 en consomme 3,
de type 3 en consomme 2. Le stock est de 32 kg :

```
3x₁ + 3x₂ + 2x₃ ≤ 32
```

**Les poires.** 3, 2 et 1 kg respectivement, pour 24 kg de stock :

```
3x₁ + 2x₂ + x₃ ≤ 24
```

**Les caisses.** Il en a douze, et il veut **toutes** les vendre. C'est donc une
**égalité**, pas une inégalité :

```
x₁ + x₂ + x₃ = 12
```

**Et la positivité**, qu'on oublie une fois sur deux :

```
x₁, x₂, x₃ ≥ 0
```

### Le programme complet

```
maximiser        5x₁ + 4x₂ +  x₃
sous            3x₁ + 3x₂ + 2x₃ ≤ 32     (pommes)
                3x₁ + 2x₂ +  x₃ ≤ 24     (poires)
                 x₁ +  x₂ +  x₃ = 12     (caisses)
                 x₁,   x₂,   x₃ ≥ 0
```

**Annotez chaque contrainte de son nom, entre parenthèses.** Le correcteur suit,
et vous ne les mélangez pas au moment de tracer les droites.

## Le piège de l'égalité

C'est celui qui coûte le plus cher.

> « Il **dispose de** 12 caisses » et « **dans l'hypothèse où il vend toutes ses
> caisses** ».

La seconde phrase transforme une inégalité en **égalité**. Sans elle, ce serait
`x₁ + x₂ + x₃ ≤ 12`.

Et ça change tout : c'est cette égalité qui permettra, à la séance suivante,
d'éliminer une variable et de passer à deux dimensions.

## Le vocabulaire des énoncés

| L'énoncé dit | On écrit |
|---|---|
| « dispose de », « au plus », « ne peut dépasser » | `≤` |
| « au moins », « il faut au minimum » | `≥` |
| « exactement », « toutes », « la totalité » | **`=`** |
| « le meilleur profit », « le plus grand bénéfice » | maximiser |
| « le moindre coût », « minimiser les pertes » | minimiser |

## La méthode, en six temps

Écrivez-la et appliquez-la sans réfléchir :

1. **Un tableau des données** avant toute chose : une ligne par type de produit,
   une colonne par ressource, plus le prix.
2. **Nommer les variables** en toutes lettres, avec leur unité.
3. **La fonction objectif** : préciser max ou min.
4. **Une contrainte par ressource**, annotée de son nom.
5. **Chercher les mots « toutes », « exactement »** → une égalité.
6. **Ne pas oublier la positivité.**

Pour l'exercice de janvier, le tableau du premier temps :

| Type | Poires | Pommes | Prix |
|---|---|---|---|
| `x₁` | 3 | 3 | 5 € |
| `x₂` | 2 | 3 | 4 € |
| `x₃` | 1 | 2 | 1 € |
| **Stock** | **24** | **32** | — |

Une fois ce tableau écrit, **les contraintes se lisent en colonnes** et la
fonction objectif se lit dans la dernière colonne. C'est mécanique.
MD,
                'recap' => <<<'MD'
- Trois éléments : **variables de décision, fonction objectif, contraintes**.
- **Les variables sont ce dont on choisit la quantité** — ici les caisses, pas les
  kilos.
- Les définir **en toutes lettres avec leur unité**.
- **Une ressource = une contrainte**, annotée de son nom.
- **« Toutes », « exactement » → une égalité `=`.** C'est le piège de janvier, et
  c'est ce qui permettra d'éliminer une variable.
- **Ne pas oublier `x ≥ 0`.**
- Commencer par un **tableau des données** : les contraintes se lisent ensuite en
  colonnes.
- Le programme de janvier : max `5x₁+4x₂+x₃` sous `3x₁+3x₂+2x₃ ≤ 32`,
  `3x₁+2x₂+x₃ ≤ 24`, `x₁+x₂+x₃ = 12`, `x ≥ 0`.
MD,
            ],

            /* ================= Séance 12 ================= */
            [
                'title' => 'La méthode graphique et le simplexe',
                'chapitre' => 'PL',
                'duree_min' => 40,
                'prerequis' => "La séance 11. On résout le programme linéaire de janvier par les deux méthodes.",
                'intro' => <<<'MD'
Deux méthodes pour résoudre le même programme, et l'énoncé demande les deux.

La **méthode graphique** est rapide mais ne marche qu'à deux variables. Le
**simplexe** marche toujours mais prend du temps.

Le programme de janvier a trois variables — la méthode graphique semble donc
exclue. Et pourtant l'énoncé la demande. C'est là qu'est toute l'astuce de
l'exercice, et on commence par elle.
MD,
                'body' => <<<'MD'
## L'astuce : éliminer une variable

Rappelons le programme :

```
maximiser        5x₁ + 4x₂ +  x₃
sous            3x₁ + 3x₂ + 2x₃ ≤ 32     (pommes)
                3x₁ + 2x₂ +  x₃ ≤ 24     (poires)
                 x₁ +  x₂ +  x₃ = 12     (caisses)
```

La méthode graphique se fait dans le plan : **deux variables maximum**. Il y en a
trois.

**Mais la troisième contrainte est une égalité.** On peut donc en extraire une
variable :

```
x₃ = 12 − x₁ − x₂
```

et la remplacer partout. Reprenons chaque ligne :

**L'objectif :**
```
5x₁ + 4x₂ + (12 − x₁ − x₂) = 4x₁ + 3x₂ + 12
```

**Les pommes :**
```
3x₁ + 3x₂ + 2(12 − x₁ − x₂) ≤ 32
3x₁ + 3x₂ + 24 − 2x₁ − 2x₂  ≤ 32
                  x₁ +   x₂ ≤ 8
```

**Les poires :**
```
3x₁ + 2x₂ + (12 − x₁ − x₂) ≤ 24
             2x₁ +      x₂ ≤ 12
```

**Et la positivité de la variable éliminée**, qu'il ne faut pas perdre :
`x₃ ≥ 0` devient `x₁ + x₂ ≤ 12`. Ici elle est impliquée par `x₁ + x₂ ≤ 8`, donc
sans effet — **mais il faut l'écrire et le dire.** C'est un point de rigueur, et
le sujet pénalise le manque de justification.

### Le programme réduit

```
maximiser    4x₁ + 3x₂ + 12
sous          x₁ +  x₂ ≤ 8      (pommes)
             2x₁ +  x₂ ≤ 12     (poires)
              x₁,   x₂ ≥ 0
```

**Deux variables. La méthode graphique s'applique.**

Notez que la constante `+12` ne change pas le point optimal : on peut maximiser
`4x₁ + 3x₂` et rajouter 12 à la fin. **Mais il ne faut pas oublier de la
rajouter.**

## La méthode graphique

### 1. Tracer les droites

Chaque contrainte devient une droite, obtenue en remplaçant `≤` par `=`. On la
trace par ses deux intersections avec les axes.

| Contrainte | Droite | Si `x₁ = 0` | Si `x₂ = 0` |
|---|---|---|---|
| `x₁ + x₂ ≤ 8` | `x₂ = −x₁ + 8` | (0, 8) | (8, 0) |
| `2x₁ + x₂ ≤ 12` | `x₂ = −2x₁ + 12` | (0, 12) | (6, 0) |

### 2. Hachurer la zone réalisable

Pour chaque contrainte, du côté qui la satisfait. **Testez avec l'origine
(0,0)** : `0 + 0 ≤ 8` est vrai, donc l'origine est du bon côté.

La **zone réalisable** est l'intersection de tous les demi-plans, plus le quart
de plan positif. C'est un **polygone convexe**.

### 3. Identifier les sommets

**L'optimum d'un programme linéaire est toujours atteint en un sommet du
polygone.** C'est le théorème fondamental, et il faut le citer.

Les sommets ici :

- **(0, 0)** — l'origine
- **(0, 8)** — intersection de `x₁ + x₂ = 8` avec l'axe vertical
- **(6, 0)** — intersection de `2x₁ + x₂ = 12` avec l'axe horizontal
- **(4, 4)** — intersection des deux droites

Le dernier se calcule :
```
x₁ + x₂ = 8          →   x₂ = 8 − x₁
2x₁ + x₂ = 12        →   2x₁ + 8 − x₁ = 12   →   x₁ = 4,  x₂ = 4
```

### 4. Évaluer l'objectif en chaque sommet

C'est la façon la plus sûre de conclure, et la plus facile à vérifier.

| Sommet | `4x₁ + 3x₂ + 12` | Valeur |
|---|---|---|
| (0, 0) | 0 + 0 + 12 | 12 |
| (0, 8) | 0 + 24 + 12 | 36 |
| (6, 0) | 24 + 0 + 12 | 36 |
| **(4, 4)** | 16 + 12 + 12 | **40** |

> **L'optimum est en (4, 4), et vaut 40 €.**

### La droite de niveau

Le corrigé présente la chose autrement : on trace une droite de pente `−4/3` —
la pente de l'objectif `4x₁ + 3x₂` — et on la déplace vers la droite jusqu'au
dernier point de contact avec la zone. Ce point est (4, 4).

Les deux présentations sont valables. **Évaluer les sommets est plus sûr**,
parce qu'un tracé approximatif peut induire en erreur, et parce qu'on peut
vérifier les calculs.

### 5. Revenir aux variables d'origine

```
x₃ = 12 − x₁ − x₂ = 12 − 4 − 4 = 4
```

> **4 caisses de chaque type. Bénéfice : 5×4 + 4×4 + 1×4 = 20 + 16 + 4 = 40 €.**

**Vérifiez les contraintes d'origine** — trente secondes, et ça élimine toute
erreur de calcul :

- pommes : 3(4) + 3(4) + 2(4) = 32 ≤ 32 ✓ *(saturée)*
- poires : 3(4) + 2(4) + 1(4) = 24 ≤ 24 ✓ *(saturée)*
- caisses : 4 + 4 + 4 = 12 ✓

Les deux contraintes de stock sont **saturées** : il ne reste ni pomme ni poire.
C'est cohérent avec un optimum au croisement des deux droites.

## Le simplexe

### La forme standard

On introduit une **variable d'écart** par inégalité, pour transformer chaque `≤`
en `=`. La variable d'écart mesure **ce qui reste** de la ressource.

```
maximiser  z = 4x₁ + 3x₂
avec       x₃ =  8 − x₁ − x₂       (écart sur les pommes)
           x₄ = 12 − 2x₁ − x₂      (écart sur les poires)
```

*(Ici `x₃` et `x₄` désignent les écarts, pas la troisième caisse. Le corrigé
réutilise ces noms — dites clairement ce que vos lettres désignent.)*

On met la constante `+12` de côté ; on la rajoutera à la fin.

### Le vocabulaire

| Terme | Sens |
|---|---|
| **variables en base** | celles exprimées en fonction des autres — ici `x₃`, `x₄` |
| **variables hors base** | mises à zéro — ici `x₁`, `x₂` |
| **solution de base** | celle obtenue en annulant les hors-base |
| **pivoter** | faire entrer une hors-base en base, et en sortir une autre |

**Solution de départ :** `(x₁, x₂, x₃, x₄) = (0, 0, 8, 12)`, avec `z = 0`.
On ne produit rien, tout le stock reste.

### Pivot 1

**Qui entre ?** La variable hors base au **plus grand coefficient** dans
l'objectif. Dans `z = 4x₁ + 3x₂`, c'est **`x₁`** (coefficient 4).

**Qui sort ?** Celle qui devient négative en premier quand `x₁` augmente :

```
x₃ = 8 − x₁ ≥ 0     →  x₁ ≤ 8
x₄ = 12 − 2x₁ ≥ 0   →  x₁ ≤ 6      ← la plus stricte
```

**`x₄` sort.** On exprime `x₁` depuis sa ligne :

```
x₁ = 6 − ½x₂ − ½x₄
```

Et on remplace partout :

```
z  = 4(6 − ½x₂ − ½x₄) + 3x₂        = 24 +  x₂ − 2x₄
x₃ = 8 − (6 − ½x₂ − ½x₄) − x₂      =  2 − ½x₂ + ½x₄
x₁ =                                  6 − ½x₂ − ½x₄
```

**Nouvelle solution :** `(6, 0, 2, 0)`, `z = 24`.

### Pivot 2

Dans `z = 24 + x₂ − 2x₄`, le seul coefficient positif est celui de `x₂`.
**`x₂` entre.**

```
x₃ = 2 − ½x₂ ≥ 0   →  x₂ ≤ 4      ← la plus stricte
x₁ = 6 − ½x₂ ≥ 0   →  x₂ ≤ 12
```

**`x₃` sort.** On exprime `x₂` depuis la ligne de `x₃` :

```
x₃ = 2 − ½x₂ + ½x₄     →     x₂ = 4 − 2x₃ + x₄
```

Et on remplace :

```
z  = 24 + (4 − 2x₃ + x₄) − 2x₄  =  28 − 2x₃ − x₄
x₂ =       4 − 2x₃ + x₄
x₁ = 6 − ½(4 − 2x₃ + x₄) − ½x₄  =   4 +  x₃ −  x₄
```

**Nouvelle solution :** `(4, 4, 0, 0)`, `z = 28`.

### Le critère d'arrêt

> `z = 28 − 2x₃ − x₄`. **Tous les coefficients de l'objectif sont négatifs** :
> augmenter une variable ne peut que faire baisser `z`. **On est à l'optimum.**

C'est la phrase à écrire pour conclure.

### Le résultat

`z = 28`, plus la constante `+12` mise de côté au départ : **40 €**.
Et `x₁ = x₂ = 4`, donc `x₃ (caisses) = 12 − 4 − 4 = 4`.

**Les deux méthodes donnent le même résultat.** Dites-le : c'est la vérification,
et l'énoncé la demande implicitement en posant les deux questions.

> Attention si vous relisez le corrigé officiel : au second pivot, il écrit
> « z = 10 + (4 − 2x₃ + x₄) − 2x₄ » et conclut « z = 14 », avant d'annoncer
> correctement 28 quelques lignes plus bas. Le 10 devrait être 24. Si vous
> trouvez 28, vous avez raison — et la contradiction interne du corrigé vous
> rappelle de **toujours vérifier votre valeur finale contre l'autre méthode**.

## La méthode du simplexe, en cinq temps

1. **Forme standard** : une variable d'écart par inégalité, `≤` devient `=`.
2. **Solution de base** : hors-base à zéro. Donner `z`.
3. **Qui entre ?** Le plus grand coefficient positif dans l'objectif.
4. **Qui sort ?** La contrainte la plus stricte. Exprimer, substituer partout.
5. **Arrêt** quand tous les coefficients de l'objectif sont négatifs ou nuls.

**Numérotez vos pivots et donnez `z` après chacun.** Un pivot juste sur trois
rapporte des points ; une page de calculs sans repère n'en rapporte aucun.
MD,
                'recap' => <<<'MD'
- **Une contrainte d'égalité permet d'éliminer une variable** et de repasser à
  deux dimensions. Reporter la **positivité de la variable éliminée**.
- Programme réduit de janvier : max `4x₁+3x₂+12` sous `x₁+x₂ ≤ 8` et
  `2x₁+x₂ ≤ 12`.
- **L'optimum est toujours en un sommet du polygone.** Le citer.
- Méthode sûre : **calculer les sommets et évaluer l'objectif en chacun**.
  Ici (0,0)→12, (0,8)→36, (6,0)→36, **(4,4)→40**.
- Revenir aux variables d'origine et **vérifier les contraintes initiales**.
- Simplexe : **variable d'écart par inégalité** ; entre celle du **plus grand
  coefficient**, sort la **contrainte la plus stricte**.
- **Arrêt quand tous les coefficients de l'objectif sont ≤ 0.**
- Résultat : `z = 28`, plus la constante 12 → **40 €**, soit **4 caisses de
  chaque type**.
- **Numéroter les pivots et donner `z` après chacun.**
MD,
            ],

            /* ================= Séance 13 ================= */
            [
                'title' => 'Les cycles, et composer la copie du 26 août',
                'chapitre' => 'CY',
                'duree_min' => 30,
                'prerequis' => "L'ensemble du cours. Dernière séance : un chapitre court, puis la stratégie du jour J.",
                'intro' => <<<'MD'
Un chapitre bref pour finir — les cycles eulériens et hamiltoniens — puis la
partie la plus utile de la séance : **comment composer votre copie le 26 août**.

Ce jour-là, vous avez AGC de 15 h à 18 h, **puis SPP de 20 h à 23 h**. Six heures
d'épreuve, deux heures d'intervalle. La façon de gérer l'AGC doit tenir compte de
ce qui vient après.
MD,
                'body' => <<<'MD'
## Deux problèmes qui se ressemblent et n'ont rien à voir

C'est le contraste central du chapitre, et il tombe régulièrement en question de
cours.

| | **Cycle eulérien** | **Cycle hamiltonien** |
|---|---|---|
| passe une fois par chaque | **arête** | **sommet** |
| condition connue ? | **oui, simple** | **non** |
| difficulté | **polynomial** | **NP-complet** |

Deux définitions presque identiques, deux mondes.

## Le cycle eulérien

> Un **cycle eulérien** passe **une et une seule fois par chaque arête**, et
> revient à son point de départ.

Le théorème d'Euler, à connaître par cœur :

> Un graphe connexe admet un cycle eulérien **si et seulement si tous ses sommets
> sont de degré pair**.
>
> Il admet un **chemin** eulérien — sans revenir au départ — si et seulement s'il
> a **exactement zéro ou deux sommets de degré impair**. Dans le cas de deux, le
> chemin part de l'un et arrive à l'autre.

L'intuition : chaque fois qu'on entre dans un sommet par une arête, il faut en
ressortir par une autre. Les arêtes vont donc **par paires** en chaque sommet —
d'où le degré pair. Les deux exceptions sont le départ et l'arrivée d'un chemin.

Rappelez-vous la séance 2 : **le nombre de sommets de degré impair est toujours
pair.** C'est pour ça qu'on ne peut pas en avoir exactement un.

### Le problème du postier chinois

> Parcourir **toutes les arêtes au moins une fois**, et revenir au départ, **au
> moindre coût**.

- Si le graphe est eulérien, la réponse est le cycle eulérien : coût = somme des
  poids.
- Sinon, il faut **repasser** par certaines arêtes. On apparie les sommets de
  degré impair par les plus courts chemins, on duplique ces arêtes pour rendre
  tous les degrés pairs, et on cherche le cycle eulérien du graphe obtenu.

**C'est polynomial.** Le problème reste facile.

## Le cycle hamiltonien et le voyageur de commerce

> Un **cycle hamiltonien** passe **une et une seule fois par chaque sommet**.

Aucune condition simple n'est connue. **Décider si un graphe en admet un est
NP-complet.**

Le **voyageur de commerce** en est la version pondérée :

> Trouver le cycle hamiltonien de **poids minimal**.

Une recherche exhaustive coûte `(n−1)!/2` — inutilisable au-delà d'une vingtaine
de villes.

### Les algorithmes d'approximation

Puisqu'on ne sait pas résoudre exactement, on approche. Le vocabulaire compte :

> Un **algorithme d'approximation de facteur ρ** rend toujours une solution dont
> le coût est au plus **ρ fois** l'optimum.

Deux approches du cours :

- **le plus proche voisin** — un glouton : à chaque étape, aller à la ville
  non visitée la plus proche. Rapide, sans garantie.
- **par l'arbre couvrant minimal** — construire l'ACM, le parcourir en
  profondeur, court-circuiter les répétitions. **Facteur 2** si les distances
  respectent l'inégalité triangulaire.

Ce dernier point est joli, et il boucle le cours : **l'arbre couvrant minimal de
la séance 7 sert à approcher le voyageur de commerce.**

## Le tableau de synthèse du module

Un dernier passage, pour la relecture de la veille.

| Problème | Méthode | Complexité |
|---|---|---|
| Parcourir un graphe | largeur (file) ou profondeur (pile) | O(n + m) |
| Plus court chemin, non pondéré | parcours en largeur | O(n + m) |
| Plus court chemin, poids positifs | **Dijkstra** | O(n²) |
| Plus court chemin, poids négatifs | Bellman-Ford | O(n·m) |
| Toutes les paires | matriciel | O(n³) |
| Connexité | un parcours | O(n + m) |
| Forte connexité | un parcours dans `G`, un dans `G⁻¹` | O(n + m) |
| Arbre couvrant minimal | **Kruskal** (creux) ou **Prim** (dense) | O(m log m) · O(n²) |
| Trier avec un arbre | ABR + parcours **infixe** | O(n log n), O(n²) au pire |
| Choix d'activités | glouton, **trier par fin** | O(n log n) |
| Plus grande sous-séquence | **dynamique**, table | O(n·m) |
| Optimiser sous contraintes | **graphique** (2 var.) ou **simplexe** | — |
| Cycle eulérien | **tous degrés pairs** | O(n + m) |
| Cycle hamiltonien | — | **NP-complet** |

## Composer la copie du 26 août

Trois heures, trois exercices, et une épreuve de SPP le soir.

### La répartition

| Temps | Quoi |
|---|---|
| 0 – 10 min | **Lire tout le sujet.** Repérer les barèmes. **Souligner les verbes** : « proposer », « donner », « évaluer », « justifier ». |
| 10 – 50 min | **L'exercice 3, la programmation linéaire.** Le plus mécanique. On commence par ce qui est sûr. |
| 50 – 110 min | **L'exercice 2**, dynamique ou glouton. Suivre le plan en quatre questions. |
| 110 – 165 min | **L'exercice 1**, les graphes. Le plus long à rédiger. |
| 165 – 180 min | **Relire.** Vérifier qu'aucune complexité ne manque et qu'aucune question n'est vide. |

**Commencer par l'exercice 3** est délibéré. Il est mécanique, il rapporte, et il
met en confiance. Les exercices sont annoncés indépendants — l'énoncé le dit
lui-même.

### Les cinq réflexes

1. **Le verbe commande la forme.** « Proposer » → du français. « Donner
   l'algorithme » → code **plus** explication **plus** commentaires. « Évaluer »
   → un chiffre.
2. **Toujours donner la complexité**, même quand elle n'est pas demandée. Une
   ligne : « chaque sommet est traité une fois et chaque arête examinée une fois,
   d'où O(n + m) ».
3. **Deux phrases sous chaque algorithme.** C'est ce qui manquait à la question
   1.4 de janvier, et c'est ce qui l'a fait passer à zéro.
4. **Aucune question vide.** Même une phrase de principe rapporte. La question
   2.4 de janvier — la complexité — demandait une ligne, et elle est restée
   blanche.
5. **Une preuve par contre-exemple se déroule.** Donner le contre-exemple, faire
   tourner l'algorithme dessus, comparer à l'optimum.

### À cause de SPP à 20 h

Trois consignes particulières à cette journée :

**Ne vous acharnez pas.** Si une question résiste plus de dix minutes, écrivez ce
que vous savez, marquez-la, et passez. Vous y reviendrez s'il reste du temps.
S'acharner coûte de l'énergie, et vous en aurez besoin à 20 h.

**Sortez à l'heure, pas après.** Utilisez les trois heures pour relire, mais ne
restez pas à ruminer. Trente minutes de pause en plus valent mieux que trois
lignes de plus.

**Entre 18 h et 20 h : manger, marcher, ne pas réviser SPP.** Les deux heures ne
serviront à rien en révision, et elles serviront beaucoup en récupération. Le
cours de SPP aura été revu avant.

## Le mot de la fin

Reprenez la séance 1. Sur les treize points manquants de janvier, une part
importante venait de la **forme** : un algorithme sans explication, un « évaluer »
sans chiffre, une complexité laissée blanche.

Ce n'est pas une consolation, c'est une information : **ces points-là sont les
plus faciles à récupérer.** Ils ne demandent pas de savoir plus, mais d'écrire
autrement — et vous savez maintenant exactement quoi écrire.
MD,
                'recap' => <<<'MD'
- **Eulérien = chaque arête une fois. Hamiltonien = chaque sommet une fois.**
- **Cycle eulérien ⟺ graphe connexe et tous les degrés pairs.**
  Chemin eulérien ⟺ zéro ou **exactement deux** sommets de degré impair.
- **Postier chinois** : dupliquer des arêtes pour rendre tous les degrés pairs.
  Polynomial.
- **Hamiltonien et voyageur de commerce : NP-complets.** On approche — le plus
  proche voisin (glouton), ou l'**arbre couvrant minimal** (facteur 2 sous
  inégalité triangulaire).
- Le 26 août : **exercice 3 d'abord** (mécanique et sûr), puis 2, puis 1.
- **Le verbe commande la forme. Toujours la complexité. Deux phrases sous chaque
  algorithme. Aucune question vide. Un contre-exemple se déroule.**
- **SPP est à 20 h** : ne pas s'acharner, sortir à l'heure, et ne pas réviser
  entre les deux.
MD,
            ],

        ];
    }
}