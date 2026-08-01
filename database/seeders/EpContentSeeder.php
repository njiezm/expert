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
 * Contenu d'EP — 7/20, épreuve du 25 août.
 *
 * Le sujet de janvier, « Graphopolis », demandait de construire une machine de
 * Turing. La copie répond en prose : « il existe une machine de Turing qui décide… »
 * sans donner le septuplet ni la fonction de transition. Le correcteur a mis « ? ».
 *
 * Le chapitre 3 est donc traité autour d'un gabarit de rédaction : le septuplet
 * d'abord, la table de transition ensuite, la prose jamais.
 */
class EpContentSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'EP')->first();

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

            /* ============ C3 — Machine de Turing ============ */
            'C3' => [
                'lessons' => [
                    [
                        'title' => 'Construire une machine, pas la décrire',
                        'est_minutes' => 25,
                        'intuition' => <<<'MD'
Voici ce que vous avez écrit en janvier, à la question 1 du sujet « Graphopolis » :

> « Il existe une Machine de Turing qui décide des ensembles stable par un graphe
> G = (V,E) tel que nous obtenons soit une réponse correcte soit un arrêt de la
> machine. Dans notre cas les conditions sont mécaniques et sans ambiguïté
> permettant un traitement direct des données… »

Le correcteur a mis un « ? » en marge.

Cette réponse **paraphrase l'énoncé**. Elle affirme qu'une machine existe, sans
jamais en donner une. Or « construire une machine de Turing » a un sens technique
précis et fermé : c'est écrire **sept objets**, puis **une table**.

Il n'y a pas de rédaction à inventer. Il y a un gabarit à remplir.
MD,
                        'formalism' => <<<'MD'
**Le septuplet — à écrire en premier, toujours.**

Une machine de Turing déterministe à un ruban est un 7-uplet :

**M = (Q, Σ, Γ, δ, q₀, q_accept, q_reject)**

| Composant | Ce que c'est |
|---|---|
| **Q** | l'ensemble **fini** des états |
| **Σ** | l'alphabet **d'entrée**, ne contient pas le blanc |
| **Γ** | l'alphabet **de ruban**, avec Σ ⊂ Γ et ␣ ∈ Γ |
| **δ** | la fonction de transition **Q × Γ → Q × Γ × {G, D}** |
| **q₀** | l'état initial, q₀ ∈ Q |
| **q_accept** | l'état acceptant |
| **q_reject** | l'état rejetant, distinct de q_accept |

**Configuration.** Un mot `u q v` : le contenu du ruban à gauche de la tête, l'état
courant, puis le contenu à partir de la tête. La configuration initiale sur l'entrée
w est `q₀ w`.

**Calcul.** Une suite de configurations reliées par δ. Il **accepte** s'il atteint
q_accept, **rejette** s'il atteint q_reject, et **boucle** s'il ne s'arrête pas.

**Décidable contre semi-décidable** — la distinction qui rapporte des points :

| | La machine… |
|---|---|
| **Langage décidable** (récursif) | s'arrête **toujours**, et répond oui ou non |
| **Langage semi-décidable** (récursivement énumérable) | s'arrête et accepte sur les mots du langage ; peut **boucler** sur les autres |

Un langage est décidable **si et seulement si** lui et son complémentaire sont
semi-décidables.
MD,
                        'worked_example' => <<<'MD'
**Construire M reconnaissant L = { aⁿbⁿ | n ≥ 1 }.**

*Principe :* barrer un `a`, aller barrer le `b` correspondant, revenir, recommencer.

**1. Le septuplet.**

- Q = {q₀, q₁, q₂, q₃, q_accept, q_reject}
- Σ = {a, b}
- Γ = {a, b, X, Y, ␣}
- q₀ initial, q_accept, q_reject

**2. La table de transition.**

| État | Lu | → État | Écrit | Déplacement | Rôle |
|---|---|---|---|---|---|
| q₀ | a | q₁ | X | D | barrer un `a` |
| q₀ | Y | q₃ | Y | D | tous les `a` traités |
| q₀ | ␣, b, X | q_reject | — | — | rejet |
| q₁ | a | q₁ | a | D | avancer vers les `b` |
| q₁ | Y | q₁ | Y | D | franchir les `b` déjà barrés |
| q₁ | b | q₂ | Y | G | barrer le `b` correspondant |
| q₁ | ␣, X | q_reject | — | — | pas de `b` disponible |
| q₂ | a, Y | q₂ | (idem) | G | revenir à gauche |
| q₂ | X | q₀ | X | D | reprendre au `a` suivant |
| q₃ | Y | q₃ | Y | D | vérifier qu'il ne reste que des `Y` |
| q₃ | ␣ | q_accept | ␣ | D | **accepter** |
| q₃ | a, b | q_reject | — | — | rejet |

**3. Trace sur `aabb`** — un tableau de configurations :

```
q₀aabb ⊢ Xq₁abb ⊢ Xaq₁bb ⊢ Xq₂aYb ⊢ q₂XaYb ⊢ Xq₀aYb
       ⊢ XXq₁Yb ⊢ XXYq₁b ⊢ XXq₂YY ⊢ Xq₂XYY ⊢ XXq₀YY
       ⊢ XXYq₃Y ⊢ XXYYq₃␣ ⊢ q_accept
```

**Accepté.**

**4. Complexité.** Chaque paire (a, b) demande un aller-retour sur le ruban,
soit O(n) déplacements. Il y a n/2 paires. **O(n²) actions élémentaires.**

Ces quatre blocs — septuplet, table, trace, complexité — sont le gabarit complet.
Aucun ne peut être remplacé par de la prose.
MD,
                        'pitfalls' => <<<'MD'
- **Affirmer qu'une machine existe sans la construire.** C'est l'erreur de janvier.
  « Il existe une machine de Turing qui… » ne vaut aucun point.
- **Oublier un composant du septuplet.** Le plus souvent Γ, ou la distinction Σ ⊂ Γ.
  Le blanc ␣ appartient à Γ mais **jamais** à Σ.
- **Donner δ en prose.** δ est une **table**, ou une liste de quintuplets
  `(q, x) → (q', y, D)`. Une phrase ne se vérifie pas.
- **Oublier q_reject.** Une machine qui « ne fait rien » sur une entrée invalide
  n'est pas définie : il faut un état rejetant explicite.
- **Confondre décidable et semi-décidable.** Décidable = s'arrête **toujours**.
- **S'arrêter avant la complexité.** Le comptage des actions élémentaires est presque
  toujours demandé, et c'est exactement ce que votre copie a laissé en suspens.
MD,
                        'examiner_expects' => <<<'MD'
Pour toute question « construisez une machine de Turing qui… » :

- [ ] Le **septuplet complet**, chaque composant énuméré.
- [ ] La **table de transition**, ligne par ligne, avec le déplacement.
- [ ] Le **rôle de chaque état**, en une ligne — c'est ce qui rend la table lisible.
- [ ] Une **trace** sur un petit exemple, sous forme de suite de configurations.
- [ ] Le **comptage des actions élémentaires**, conclu par une classe de complexité.

Cinq blocs. Aucune phrase du type « la machine vérifie que… » ne remplace l'un d'eux.
MD,
                        'source_refs' => [
                            ['label' => 'cours_ep.pdf § 3 — Machine de Turing'],
                            ['label' => 'td1_new.pdf et sa correction'],
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'formule',
                        'front' => 'Le septuplet d’une machine de Turing ?',
                        'back' => "**M = (Q, Σ, Γ, δ, q₀, q_accept, q_reject)**\n\n- **Q** états · **Σ** alphabet d'entrée · **Γ** alphabet de ruban (Σ ⊂ Γ, ␣ ∈ Γ)\n- **δ : Q × Γ → Q × Γ × {G, D}**\n- q₀ initial, q_accept, q_reject distincts",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => "Le blanc ␣ appartient-il à Σ ou à Γ ?",
                        'back' => "**À Γ seulement.**\n\nΣ est l'alphabet d'**entrée** et ne contient jamais le blanc — sinon on ne pourrait pas savoir où finit le mot. On a **Σ ⊂ Γ** et **␣ ∈ Γ \\ Σ**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Décidable ou semi-décidable : quelle différence ?',
                        'back' => "**Décidable** (récursif) — la machine s'arrête **toujours** et répond oui ou non.\n\n**Semi-décidable** (récursivement énumérable) — elle s'arrête et accepte sur les mots du langage, mais peut **boucler** sur les autres.\n\nL est décidable ⟺ L et son complémentaire sont semi-décidables.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "« Construisez une machine de Turing qui… » : les cinq blocs attendus ?",
                        'back' => "1. Le **septuplet** complet.\n2. La **table de transition**.\n3. Le **rôle de chaque état**.\n4. Une **trace** sur un exemple.\n5. Le **comptage des actions** et la classe de complexité.\n\nAucune prose ne remplace un de ces blocs — c'est ce qui a coûté les points en janvier.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => "Qu'est-ce qu'une configuration de machine de Turing ?",
                        'back' => "Un mot **`u q v`** : le ruban à gauche de la tête (`u`), l'état courant (`q`), le ruban à partir de la tête (`v`).\n\nConfiguration initiale sur l'entrée w : **`q₀ w`**.",
                    ],
                    [
                        'kind' => 'formule',
                        'front' => "Machine reconnaissant aⁿbⁿ : quelle complexité, et pourquoi ?",
                        'back' => "**O(n²)**\n\nChaque paire (a, b) exige un aller-retour sur le ruban, soit O(n) déplacements ; il y a n/2 paires.\n\nUne machine à deux rubans y parviendrait en O(n).",
                        'difficulty' => 4,
                    ],
                ],
                'exercises' => [
                    [
                        'title' => "Construire une machine — le gabarit en cinq blocs",
                        'origin' => 'td',
                        'est_minutes' => 40,
                        'difficulty' => 4,
                        'statement' => <<<'MD'
Construisez une machine de Turing déterministe à un ruban qui reconnaît le langage

**L = { w ∈ {0,1}\* | w contient un nombre pair de 1 }**

**1.** Donnez le **septuplet complet**. *(2 pts)*
**2.** Donnez la **table de transition** et le **rôle de chaque état**. *(3 pts)*
**3.** Donnez la **trace** du calcul sur l'entrée `1011`. *(2 pts)*
**4.** Comptez les **actions élémentaires** et donnez la classe de complexité. *(2 pts)*
**5.** Le langage L est-il **décidable** ? Justifiez. *(1 pt)*

Rappel de la consigne de rédaction : aucune phrase du type « la machine vérifie
que… » ne remplace un de ces blocs.
MD,
                        'hint' => "Deux états suffisent pour compter la parité : « nombre de 1 lus jusqu'ici pair » et « impair ». La tête ne fait qu'avancer vers la droite.",
                        'method' => <<<'MD'
1. Identifiez l'information à retenir : ici, **la parité seule**. Elle tient dans l'état.
2. Un état par valeur de cette information : `qPair`, `qImpair`.
3. Écrivez δ pour chaque couple (état, symbole lu), y compris le blanc.
4. Le blanc signale la fin du mot : c'est là qu'on accepte ou qu'on rejette.
5. Comptez : combien de déplacements pour un mot de longueur n ?
MD,
                        'solution' => <<<'MD'
**1. Le septuplet.**

- **Q** = {q_pair, q_impair, q_accept, q_reject}
- **Σ** = {0, 1}
- **Γ** = {0, 1, ␣}
- **δ** : donnée en question 2
- **q₀** = q_pair
- **q_accept**, **q_reject**

**2. La table de transition.**

| État | Lu | → État | Écrit | Dépl. |
|---|---|---|---|---|
| q_pair | 0 | q_pair | 0 | D |
| q_pair | 1 | q_impair | 1 | D |
| q_pair | ␣ | q_accept | ␣ | D |
| q_impair | 0 | q_impair | 0 | D |
| q_impair | 1 | q_pair | 1 | D |
| q_impair | ␣ | q_reject | ␣ | D |

**Rôle des états :**
- `q_pair` — un nombre **pair** de 1 a été lu jusqu'ici. État initial (zéro est pair).
- `q_impair` — un nombre **impair** de 1 a été lu.
- `q_accept` / `q_reject` — terminaux.

La machine n'écrit jamais rien de nouveau et ne revient jamais en arrière :
elle se contente de parcourir le mot en mémorisant la parité dans son état.

**3. Trace sur `1011`.**

```
q_pair 1011
⊢ 1 q_impair 011
⊢ 10 q_impair 11
⊢ 101 q_pair 1
⊢ 1011 q_impair ␣
⊢ q_reject
```

Le mot `1011` contient **trois** 1, nombre impair : il est **rejeté**. Correct.

*Vérification sur `1010` :* deux 1, la machine finit en `q_pair` sur le blanc,
donc **accepte**.

**4. Comptage des actions élémentaires.**

La tête se déplace d'une case vers la droite à chaque transition, sans jamais
revenir. Pour un mot de longueur n, il y a **n transitions** pour lire le mot,
plus **une** pour lire le blanc final.

**Total : n + 1 actions élémentaires, soit O(n).**
Le langage est donc décidé en **temps linéaire**, et appartient à la classe **P**.

**5. Décidabilité.**

**Oui, L est décidable.** La machine construite **s'arrête sur toute entrée** :
elle avance systématiquement vers la droite et rencontre nécessairement le blanc
terminal après n étapes. Elle répond alors toujours par acceptation ou rejet,
jamais par une boucle. C'est exactement la définition d'un langage décidable.
MD,
                        'rubric' => [
                            ['label' => 'Les sept composants du septuplet sont énumérés', 'points' => 1],
                            ['label' => 'Σ = {0,1} et Γ = {0,1,␣} distingués, le blanc hors de Σ', 'points' => 1],
                            ['label' => 'Table de transition complète, avec le cas du blanc pour chaque état', 'points' => 2],
                            ['label' => 'Le rôle de chaque état est explicité en une ligne', 'points' => 1],
                            ['label' => 'Trace sur 1011 sous forme de suite de configurations', 'points' => 2],
                            ['label' => 'Comptage : n + 1 actions, conclu en O(n) et classe P', 'points' => 2],
                            ['label' => "Décidabilité justifiée par l'arrêt garanti sur toute entrée", 'points' => 1],
                        ],
                    ],
                ],
            ],

            /* ============ C5 — Décidabilité ============ */
            'C5' => [
                'lessons' => [
                    [
                        'title' => 'Indécidabilité et réductions',
                        'est_minutes' => 22,
                        'intuition' => <<<'MD'
Il existe des problèmes qu'**aucun** programme ne peut résoudre — pas par manque de
puissance de calcul, mais par impossibilité logique. Le plus célèbre est le
**problème de l'arrêt** : décider si un programme donné, sur une entrée donnée,
va s'arrêter.

Une fois cette impossibilité établie, on la propage : pour montrer qu'un problème B
est indécidable, on montre que **savoir résoudre B permettrait de résoudre l'arrêt**.
C'est la **réduction**, et c'est le seul outil du chapitre.

Le sens de la réduction est l'erreur classique, et elle est fatale.
MD,
                        'formalism' => <<<'MD'
**Thèse de Church-Turing.** Tout ce qui est effectivement calculable l'est par une
machine de Turing. Ce n'est pas un théorème mais une thèse : elle relie une notion
intuitive à une définition formelle.

**Codage.** Toute machine de Turing M peut être codée par un mot ⟨M⟩. C'est ce qui
permet à une machine d'en prendre une autre en entrée — et rend la diagonalisation
possible.

**Les problèmes indécidables du cours**

| Problème | Définition | Statut |
|---|---|---|
| A_TM (acceptation, ou universel) | { ⟨M, w⟩ \| M accepte w } | **indécidable**, semi-décidable |
| HALT_TM (arrêt) | { ⟨M, w⟩ \| M s'arrête sur w } | **indécidable**, semi-décidable |
| E_TM (vacuité) | { ⟨M⟩ \| L(M) = ∅ } | indécidable |
| EQ_TM (équivalence) | { ⟨M₁, M₂⟩ \| L(M₁) = L(M₂) } | indécidable, **non** semi-décidable |

**Le schéma de réduction — à reproduire tel quel**

Pour montrer que **B est indécidable**, on réduit un problème A déjà connu indécidable
**à** B, ce qui s'écrit **A ≤ B** :

1. **Supposer** qu'il existe une machine `R` qui **décide B**.
2. **Construire**, à partir de `R`, une machine `S` qui **déciderait A**.
3. Or A est indécidable : **contradiction**.
4. Donc `R` n'existe pas, et **B est indécidable**.

**Le sens est vital.** On réduit **le problème connu indécidable vers le nouveau** :
`A_TM ≤ B`. L'écrire dans l'autre sens (`B ≤ A_TM`) ne démontre rien — cela dirait
seulement que B n'est pas plus dur que A, ce qui est sans conséquence.
MD,
                        'worked_example' => <<<'MD'
**Montrer que HALT_TM est indécidable, par réduction depuis A_TM.**

*Rappel :* A_TM = { ⟨M, w⟩ | M accepte w }, connu indécidable.
HALT_TM = { ⟨M, w⟩ | M s'arrête sur w }.

**1. Supposons** qu'une machine **R décide HALT_TM** : sur ⟨M, w⟩, elle s'arrête
toujours et répond « M s'arrête sur w » ou « M ne s'arrête pas sur w ».

**2. Construisons S**, qui déciderait A_TM :

> **S = « sur l'entrée ⟨M, w⟩ :**
> 1. Exécuter **R** sur ⟨M, w⟩.
> 2. Si R **rejette** (M ne s'arrête pas sur w), alors **rejeter** —
>    M n'accepte certainement pas w.
> 3. Si R **accepte** (M s'arrête sur w), alors **simuler M sur w**.
>    Cette simulation **termine**, puisque R vient de le garantir.
> 4. Si la simulation accepte, **accepter** ; sinon, **rejeter**. »

**3. S décide A_TM.** Elle s'arrête sur toute entrée : l'étape 1 termine par
hypothèse, l'étape 3 termine parce que R l'a garanti. Et sa réponse est correcte
par construction.

**4. Contradiction.** A_TM est indécidable, donc aucune machine ne le décide.
S ne peut donc pas exister, ce qui n'est possible que si **R n'existe pas**.

**Conclusion : HALT_TM est indécidable.** ∎

Notez le mécanisme : c'est **l'étape 3 qui exige la garantie d'arrêt**. Sans elle,
S pourrait boucler et ne déciderait rien. C'est exactement ce que HALT_TM apporte,
et c'est là que se joue la démonstration.
MD,
                        'pitfalls' => <<<'MD'
- **Réduire dans le mauvais sens.** Pour montrer que B est indécidable, on écrit
  `A_TM ≤ B`, jamais l'inverse. C'est l'erreur qui annule toute la démonstration.
- **Oublier de justifier que la machine construite s'arrête.** Une machine qui
  « décide » mais peut boucler ne décide rien. C'est le cœur de l'argument.
- **Confondre indécidable et semi-décidable.** A_TM et HALT_TM sont **semi-décidables** :
  on peut simuler et accepter si ça s'arrête. Leur **complémentaire** ne l'est pas.
- **Confondre indécidable et NP-difficile.** L'indécidabilité est une impossibilité
  absolue ; la NP-difficulté est une question de coût. SAT est décidable.
- **Ne pas conclure.** La démonstration doit se terminer par « donc R n'existe pas,
  donc B est indécidable ».
MD,
                        'examiner_expects' => <<<'MD'
Pour une démonstration d'indécidabilité, quatre étapes numérotées :

- [ ] **1.** « Supposons qu'une machine R décide B. »
- [ ] **2.** La construction explicite de S, écrite comme un algorithme numéroté.
- [ ] **3.** La justification que **S s'arrête toujours** et répond correctement.
- [ ] **4.** « Or A est indécidable, contradiction. Donc B est indécidable. » ∎

Et le sens de la réduction énoncé clairement : `A_TM ≤ B`.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'piege',
                        'front' => 'Pour montrer que B est indécidable, dans quel sens réduire ?',
                        'back' => "**A_TM ≤ B** — du problème **connu indécidable** vers le **nouveau**.\n\nÉcrire `B ≤ A_TM` ne démontre rien : cela dirait seulement que B n'est pas plus dur que A.\n\nC'est l'erreur qui annule toute la démonstration.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'methode',
                        'front' => "Les quatre étapes d'une preuve d'indécidabilité par réduction ?",
                        'back' => "1. **Supposer** qu'une machine R décide B.\n2. **Construire** S qui déciderait A grâce à R.\n3. **Justifier** que S s'arrête toujours et répond juste.\n4. **Contradiction** : A est indécidable, donc R n'existe pas. ∎",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que sont A_TM et HALT_TM ?',
                        'back' => "**A_TM** = { ⟨M, w⟩ | M **accepte** w } — problème de l'acceptation, ou universel.\n\n**HALT_TM** = { ⟨M, w⟩ | M **s'arrête** sur w } — problème de l'arrêt.\n\nTous deux **indécidables** mais **semi-décidables**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'piege',
                        'front' => 'Indécidable et NP-difficile : est-ce la même chose ?',
                        'back' => "**Non, et les confondre coûte cher.**\n\n**Indécidable** — aucun algorithme n'existe, jamais. Impossibilité absolue.\n\n**NP-difficile** — un algorithme existe, mais on n'en connaît pas de polynomial. SAT est décidable et NP-complet.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Énoncez la thèse de Church-Turing.',
                        'back' => "**Tout ce qui est effectivement calculable l'est par une machine de Turing.**\n\nCe n'est pas un théorème : c'est une **thèse**, qui relie une notion intuitive (« calculable ») à une définition formelle. Elle ne se démontre pas.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Quand un langage est-il décidable, en termes de semi-décidabilité ?',
                        'back' => "**L est décidable ⟺ L et son complémentaire L̄ sont tous deux semi-décidables.**\n\nOn lance les deux machines en parallèle : l'une des deux s'arrête nécessairement.",
                        'difficulty' => 5,
                    ],
                ],
            ],

            /* ============ C6 — Complexité ============ */
            'C6' => [
                'lessons' => [
                    [
                        'title' => 'Compter les actions, conclure sur une classe',
                        'est_minutes' => 18,
                        'intuition' => <<<'MD'
Sur votre copie, à la question 2 de « Graphopolis », vous êtes arrivé à
`|V| · |E|` pour l'énumération des couples — un raisonnement juste. Puis vous vous
êtes arrêté.

Un comptage qui n'aboutit pas à une **majoration en O()** et à une **classe** laisse
sur la table les points de la conclusion. Le calcul intermédiaire ne vaut presque rien
si la phrase finale manque.

La règle est simple : **tout comptage se termine par « donc le problème est dans la
classe … »**.
MD,
                        'formalism' => <<<'MD'
**Les définitions du cours**

- **Complexité en temps** d'une machine M : la fonction `t(n)` qui donne le nombre
  **maximal** d'actions élémentaires effectuées sur une entrée de longueur n.
  C'est le **pire cas**.
- **f = O(g)** s'il existe c > 0 et n₀ tels que `f(n) ≤ c · g(n)` pour tout n ≥ n₀.

**Les classes**

| Classe | Définition |
|---|---|
| **TIME(f(n))** | langages décidés par une MT déterministe en O(f(n)) |
| **P** | ⋃ₖ TIME(nᵏ) — décidables en temps **polynomial déterministe** |
| **NP** | décidables en temps polynomial par une MT **non déterministe** ; équivalent : **vérifiables** en temps polynomial |
| **NP-complet** | dans NP, et tout problème de NP s'y réduit en temps polynomial |

**Thèse de l'invariance.** Tous les modèles de calcul raisonnables se simulent
mutuellement avec un surcoût **polynomial**. C'est ce qui rend la classe P robuste :
elle ne dépend pas du modèle choisi.

**Le coût des variantes** — question classique :

| Variante | Simulation par une MT à un ruban | Surcoût |
|---|---|---|
| k rubans | O(t(n)²) | quadratique |
| non déterministe | O(2^{O(t(n))}) | **exponentiel** |
| alphabet à 3 symboles | O(t(n) · log \|Γ\|) | logarithmique |

Le surcoût des multi-rubans est **polynomial** : ils décident donc les mêmes langages
en temps polynomial. C'est la thèse de l'invariance en action.
MD,
                        'worked_example' => <<<'MD'
**Le gabarit de comptage, en quatre lignes.**

> *« Sur une entrée de taille n, combien d'actions élémentaires effectue votre
> machine ? »*

**1. Identifier la boucle dominante.** *« La machine effectue un aller-retour sur le
ruban pour chaque symbole marqué. »*

**2. Compter une itération.** *« Un aller-retour parcourt au plus n cases,
soit 2n déplacements. »*

**3. Compter les itérations.** *« Il y a au plus n symboles à marquer. »*

**4. Multiplier, majorer, conclure.** *« Total : au plus 2n² déplacements, soit
**O(n²)** actions élémentaires. Le problème est donc décidé en temps polynomial :
**il appartient à la classe P**. »*

**Appliqué à « Graphopolis ».** Le sujet demandait de tester les couples (u, v)
d'un graphe G = (V, E).

> Il y a **|V|²** couples (u, v) possibles. Pour chacun, tester si (u,v) ∈ E demande
> de parcourir la liste des arêtes, soit **O(|E|)** actions.
>
> Total : **O(|V|² · |E|)** actions élémentaires.
>
> Avec |E| ≤ |V|², cela donne **O(|V|⁴)** dans le pire cas : un polynôme en la taille
> de l'entrée. **Le problème est donc dans P.**

C'est cette dernière phrase — trois mots — qui manquait sur votre copie.
MD,
                        'pitfalls' => <<<'MD'
- **S'arrêter au produit sans majorer.** `|V| · |E|` est un calcul, pas une réponse.
  Il faut la notation O() et la classe.
- **Oublier « pire cas ».** La complexité en temps est définie sur le **maximum**,
  sauf mention contraire de l'énoncé.
- **Confondre P et NP.** NP n'est pas « non polynomial » mais « **n**on déterministe
  **p**olynomial ». Tout problème de P est dans NP.
- **Croire qu'un surcoût exponentiel de simulation change la décidabilité.** Une
  machine non déterministe ne décide pas plus de langages qu'une déterministe :
  elle le fait seulement plus vite.
- **Oublier que la taille de l'entrée est en bits.** Un algorithme en O(W) où W est
  une valeur numérique est **pseudo-polynomial**, pas polynomial.
MD,
                        'examiner_expects' => <<<'MD'
Toute question de comptage se termine par :

- [ ] Le **nombre d'actions** exprimé en fonction de la taille de l'entrée.
- [ ] Une **majoration en O()**.
- [ ] Une **phrase de classe** : « donc le problème est dans P », « donc il est décidé
      en temps exponentiel », etc.

Sans la troisième ligne, le comptage ne rapporte que la moitié des points.
MD,
                    ],
                ],
                'cards' => [
                    [
                        'kind' => 'methode',
                        'front' => "Vous avez compté |V| · |E| actions. Que manque-t-il pour finir la réponse ?",
                        'back' => "**La majoration et la classe.**\n\n« Avec |E| ≤ |V|², cela fait **O(|V|⁴)**, un polynôme en la taille de l'entrée : **le problème est dans P**. »\n\nC'est exactement cette phrase qui manquait sur votre copie de janvier.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Que signifie NP ?',
                        'back' => "**N**on déterministe **P**olynomial — décidable en temps polynomial par une machine **non déterministe**.\n\nPas « non polynomial ». Définition équivalente : les problèmes dont une solution se **vérifie** en temps polynomial. Et **P ⊆ NP**.",
                        'difficulty' => 4,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => 'Coût de simulation d’une MT à k rubans par une MT à un ruban ?',
                        'back' => "**O(t(n)²)** — surcoût **quadratique**, donc polynomial.\n\nLes deux modèles décident donc les mêmes langages en temps polynomial : c'est la **thèse de l'invariance**.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'formule',
                        'front' => "Coût de simulation d'une MT non déterministe par une MT déterministe ?",
                        'back' => "**O(2^{O(t(n))})** — surcoût **exponentiel**.\n\nElle décide les mêmes langages, mais le passage au déterministe coûte cher. C'est là que se loge la question P = NP.",
                        'difficulty' => 5,
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Énoncez la thèse de l’invariance.',
                        'back' => "**Tous les modèles de calcul raisonnables se simulent mutuellement avec un surcoût polynomial.**\n\nC'est ce qui rend la classe **P** robuste : elle ne dépend pas du modèle de machine choisi.",
                    ],
                    [
                        'kind' => 'definition',
                        'front' => 'Définition formelle de f = O(g) ?',
                        'back' => "**∃ c > 0, ∃ n₀, ∀ n ≥ n₀ : f(n) ≤ c · g(n)**\n\nUne majoration **asymptotique** : les constantes et les petites valeurs de n sont ignorées.",
                    ],
                ],
            ],
        ];
    }

    /* ==================================================================== */

    private function mockExam(Subject $subject): void
    {
        $examen = MockExam::updateOrCreate(
            ['slug' => 'ep-blanc-turing-decidabilite-complexite'],
            [
                'subject_id' => $subject->id,
                'title' => 'EP blanc n°1 — Turing, décidabilité, complexité',
                'instructions' => <<<'MD'
Durée : **2 heures**, comme l'épreuve du 25 août de 14 h à 16 h.
Documents autorisés : aucun.

**Consigne de rédaction, tirée de vos annotations de janvier.** « Construire une
machine de Turing » signifie donner le **septuplet** puis la **table de transition**.
Une phrase du type « il existe une machine qui vérifie que… » ne vaut aucun point :
le correcteur a mis « ? » en marge.

Tout comptage d'actions élémentaires se conclut par une **majoration en O()** et une
**classe de complexité**.
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
                'number' => 'Exercice 1 — Construction de machine',
                'chapter_id' => $ch('C3'),
                'points' => 8,
                'statement' => <<<'MD'
Construisez une machine de Turing déterministe à un ruban qui reconnaît

**L = { w ∈ {a,b}\* | w commence et finit par le même symbole, et |w| ≥ 2 }**

**1.** Donnez le **septuplet complet**. *(2 pts)*
**2.** Donnez la **table de transition** et le **rôle de chaque état**. *(3 pts)*
**3.** Donnez la **trace** sur l'entrée `abba`. *(1 pt)*
**4.** Comptez les **actions élémentaires** et concluez sur la classe. *(2 pts)*
MD,
                'solution' => <<<'MD'
**1. Le septuplet.**

- **Q** = {q₀, q_a, q_b, q_finA, q_finB, q_accept, q_reject}
- **Σ** = {a, b}
- **Γ** = {a, b, X, ␣}
- **q₀** initial, **q_accept**, **q_reject**

**2. Table de transition.**

| État | Lu | → État | Écrit | Dépl. | Rôle |
|---|---|---|---|---|---|
| q₀ | a | q_a | X | D | premier symbole = a, on le marque |
| q₀ | b | q_b | X | D | premier symbole = b, on le marque |
| q₀ | ␣ | q_reject | ␣ | D | mot vide |
| q_a | a, b | q_a | (idem) | D | avancer jusqu'au bout |
| q_a | ␣ | q_finA | ␣ | G | fin atteinte, revenir d'une case |
| q_b | a, b | q_b | (idem) | D | avancer jusqu'au bout |
| q_b | ␣ | q_finB | ␣ | G | fin atteinte, revenir d'une case |
| q_finA | a | q_accept | a | D | dernier = a = premier ✓ |
| q_finA | b, X | q_reject | — | — | dernier ≠ a, ou mot d'un seul symbole |
| q_finB | b | q_accept | b | D | dernier = b = premier ✓ |
| q_finB | a, X | q_reject | — | — | dernier ≠ b, ou mot d'un seul symbole |

**Rôle des états :** `q₀` lit et mémorise le premier symbole en marquant sa case
d'un `X` ; `q_a` et `q_b` transportent cette mémoire jusqu'au bout du mot ;
`q_finA` et `q_finB` comparent le dernier symbole à celui mémorisé.

Le `X` écrit en première position garantit le rejet des mots d'un seul caractère :
en revenant d'une case depuis le blanc, on retombe sur le `X`, ce qui déclenche
`q_reject` — la condition |w| ≥ 2 est ainsi assurée.

**3. Trace sur `abba`.**

```
q₀ abba
⊢ X q_a bba
⊢ Xb q_a ba
⊢ Xbb q_a a
⊢ Xbba q_a ␣
⊢ Xbb q_finA a
⊢ q_accept
```

Le mot commence par `a` et finit par `a` : **accepté**.

**4. Comptage.**

La tête parcourt le mot de gauche à droite (n déplacements), lit le blanc
(1 déplacement), revient d'une case (1 déplacement), puis conclut.

**Total : n + 2 actions élémentaires, soit O(n).**

Le langage est décidé en **temps linéaire** : il appartient à la classe **P**.
MD,
                'rubric' => [
                    ['label' => 'Les sept composants du septuplet énumérés', 'points' => 1],
                    ['label' => 'Γ contient un symbole de marquage et le blanc, Σ non', 'points' => 1],
                    ['label' => 'Table complète, incluant le comportement sur le blanc', 'points' => 2],
                    ['label' => 'Le rôle de chaque état est explicité', 'points' => 1],
                    ['label' => 'Le rejet des mots de longueur 1 est traité explicitement', 'points' => 1],
                    ['label' => 'Trace sur abba en suite de configurations', 'points' => 1],
                    ['label' => 'Comptage conclu par O(n) ET la classe P', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 2 — Indécidabilité',
                'chapter_id' => $ch('C5'),
                'points' => 7,
                'statement' => <<<'MD'
Soit le problème

**VIDE_TM = { ⟨M⟩ | M est une machine de Turing et L(M) = ∅ }**

autrement dit : « la machine M n'accepte aucun mot ».

**1.** Rappelez la définition de A_TM et son statut. *(1 pt)*

**2.** Montrez que **VIDE_TM est indécidable**, par réduction depuis A_TM.
Suivez les quatre étapes vues en cours. *(4 pts)*

**3.** Quelle est la différence entre un problème **indécidable** et un problème
**NP-difficile** ? Donnez un exemple de chaque. *(2 pts)*
MD,
                'solution' => <<<'MD'
**1.** **A_TM = { ⟨M, w⟩ | M accepte w }**, le problème de l'acceptation.
Il est **indécidable** mais **semi-décidable** : on peut simuler M sur w et accepter
si la simulation accepte, mais on ne peut pas conclure au rejet en temps fini.

**2. VIDE_TM est indécidable.**

**Étape 1 — Supposons** qu'une machine **R décide VIDE_TM** : sur ⟨M⟩, elle s'arrête
toujours et répond « L(M) = ∅ » ou « L(M) ≠ ∅ ».

**Étape 2 — Construisons S**, qui déciderait A_TM.

Sur l'entrée ⟨M, w⟩, on fabrique d'abord une machine auxiliaire M_w :

> **M_w = « sur l'entrée x :**
> 1. Si **x ≠ w**, **rejeter**.
> 2. Si **x = w**, simuler **M** sur **w** et répondre comme elle. »

Par construction :
- si **M accepte w**, alors **L(M_w) = {w} ≠ ∅** ;
- si **M n'accepte pas w**, alors **L(M_w) = ∅**.

D'où :

> **S = « sur l'entrée ⟨M, w⟩ :**
> 1. Construire la description ⟨M_w⟩ — opération purement syntaxique, qui termine.
> 2. Exécuter **R** sur ⟨M_w⟩.
> 3. Si R **accepte** (L(M_w) = ∅), **rejeter**.
> 4. Si R **rejette** (L(M_w) ≠ ∅), **accepter**. »

**Étape 3 — S décide A_TM.** Elle s'arrête sur toute entrée : l'étape 1 est une
construction syntaxique finie, et l'étape 2 termine par hypothèse sur R.
Sa réponse est correcte d'après l'équivalence établie ci-dessus.

Remarquons que **S ne simule jamais M** : elle se contente de fabriquer une
description. C'est ce qui garantit l'arrêt.

**Étape 4 — Contradiction.** A_TM est indécidable, donc S ne peut exister.
Or S se construit à partir de R : c'est donc **R qui n'existe pas**.

**Conclusion : VIDE_TM est indécidable.** ∎

**3.**

**Indécidable** — aucun algorithme n'existe, quelle que soit la puissance de calcul.
Impossibilité **absolue** et démontrée. *Exemple : le problème de l'arrêt, HALT_TM.*

**NP-difficile** — un algorithme existe et termine toujours ; simplement, on n'en
connaît aucun de complexité polynomiale. C'est une question de **coût**, pas de
possibilité. *Exemple : SAT, qui est décidable et NP-complet.*

La confusion est fréquente : **un problème NP-difficile reste décidable**.
MD,
                'rubric' => [
                    ['label' => 'A_TM correctement défini et son statut donné (indécidable, semi-décidable)', 'points' => 1],
                    ['label' => 'Étape 1 : hypothèse d’existence de R explicitée', 'points' => 1],
                    ['label' => 'Étape 2 : construction de M_w, avec l’équivalence L(M_w) = ∅ ⟺ M n’accepte pas w', 'points' => 1],
                    ['label' => 'Étape 2 : construction de S, écrite comme un algorithme numéroté', 'points' => 1],
                    ['label' => 'Étape 3 : justification que S s’arrête toujours', 'points' => 1],
                    ['label' => 'Étape 4 : contradiction et conclusion explicite', 'points' => 1],
                    ['label' => 'Q3 : distinction correcte, avec un exemple de chaque', 'points' => 1],
                ],
            ],
            [
                'number' => 'Exercice 3 — Complexité',
                'chapter_id' => $ch('C6'),
                'points' => 5,
                'statement' => <<<'MD'
Une machine de Turing M reçoit en entrée un graphe G = (V, E) codé par sa matrice
d'adjacence, et doit décider si G contient un **triangle** — trois sommets
deux à deux adjacents.

**1.** Décrivez l'algorithme employé par M, en quelques lignes. *(1 pt)*

**2.** Comptez les **actions élémentaires** en fonction de |V|, puis majorez en O()
et **concluez sur la classe de complexité**. *(2 pts)*

**3.** Quelle est la **taille de l'entrée** en nombre de bits ? Le problème est-il
polynomial en cette taille ? *(1 pt)*

**4.** Une machine de Turing **non déterministe** ferait-elle mieux ? À quel coût
une machine déterministe la simulerait-elle ? *(1 pt)*
MD,
                'solution' => <<<'MD'
**1.** M énumère tous les triplets de sommets (u, v, w) distincts et, pour chacun,
teste si les trois arêtes (u,v), (v,w) et (u,w) sont présentes dans la matrice
d'adjacence. Si un triplet passe le test, M accepte ; si tous échouent, M rejette.

**2.** Il y a **C(|V|, 3) = O(|V|³)** triplets. Pour chaque triplet, trois accès à la
matrice ; chaque accès demande de se déplacer sur le ruban jusqu'à la case voulue,
soit **O(|V|²)** déplacements au pire.

Total : **O(|V|³ · |V|²) = O(|V|⁵)** actions élémentaires.

C'est un **polynôme** en |V| : le problème est décidé en temps polynomial,
**il appartient à la classe P**.

**3.** La matrice d'adjacence occupe **|V|² bits**, donc la taille de l'entrée est
**n = |V|²**, soit |V| = √n.

La complexité s'écrit alors `O(|V|⁵) = O(n^{2,5})` — un polynôme en n.
**Le problème est bien polynomial en la taille de l'entrée**, et donc dans P au sens
strict de la définition.

Cette vérification compte : une complexité polynomiale en |V| ne serait pas
automatiquement polynomiale en la taille du codage si celui-ci était logarithmique.

**4.** Une machine **non déterministe** devinerait le triplet en une étape, puis
vérifierait les trois arêtes : **O(|V|²)** actions, soit un gain net.

Une machine déterministe simulant cette machine non déterministe le ferait avec un
surcoût **exponentiel**, en **O(2^{O(t(n))})**. Ici cela n'a pas d'importance
pratique, puisque le problème est déjà dans P — il n'y a rien à gagner à passer par
le non déterminisme.
MD,
                'rubric' => [
                    ['label' => 'Algorithme décrit : énumération des triplets et test des trois arêtes', 'points' => 1],
                    ['label' => 'Comptage : O(|V|³) triplets × O(|V|²) par accès', 'points' => 1],
                    ['label' => 'Majoration O(|V|⁵) **et** conclusion « appartient à P »', 'points' => 1],
                    ['label' => 'Taille de l’entrée n = |V|² identifiée, complexité réexprimée en n', 'points' => 1],
                    ['label' => 'Non déterminisme : gain en O(|V|²), simulation en surcoût exponentiel', 'points' => 1],
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