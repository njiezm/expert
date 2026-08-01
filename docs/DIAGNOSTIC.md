# MÉRIDIEN — Diagnostic initial

> Établi le 2026-08-01 à partir des 5 copies d'examen notées
> (`public/pdfs/resultats s1 et s2/ZAMON/ZAMON/`).
> Ces copies sont des **scans** : aucun texte extractible, lecture visuelle page à page.

## Calendrier des rattrapages

| Date | Matière | Créneau | Durée |
|---|---|---|---|
| 24 août 2026 | ALO — Architectures Logicielles à Objet | 20h–23h | 3 h |
| 25 août 2026 | EP — Évaluation de Programmes | 14h–16h | 2 h |
| 26 août 2026 | AGC — Algorithmes sur les Graphes et Combinatoire | 15h–18h | 3 h |
| 26 août 2026 | SPP — Spécification et Preuves de Programmes | 20h–23h | 3 h |
| 28 août 2026 | MIA — Méthodes et Outils pour l'IA | 15h–17h | 2 h |

**Point critique : le 26 août = 6 h d'examen dans la journée** (AGC 15h–18h puis SPP 20h–23h).
À entraîner en double session, avec la gestion de fatigue associée.

## Résultats de la session initiale

| Code | Matière | Session | Centre | Note | Détail |
|---|---|---|---|---|---|
| ALO | Architectures Logicielles à Objet | Janvier 2026 (20/01) | Besançon | **0 / 20** | QCM largement faux, sections non traitées |
| SPP | Spécification et Preuves de Programmes | Mai 2026 (21/05) | Le Lamentin (Martinique) | **1,5 / 20** | Appréciation : « Quasiment aucun acquis » |
| MIA | Méthodes et Outils pour l'IA | Mai 2026 (22/05) | Greta-CFA Martinique | **3,34 / 20** | 5/30 — I:1,25 II:0,75 III:0,25 IV:1,25 V:1,5 |
| AGC | Algorithmes sur les Graphes et Combinatoire | Janvier 2026 (22/01) | Besançon | **7 / 20** | Ex1:2 Ex2:2 Ex3:3 |
| EP | Évaluation de Programmes | Janvier 2026 (20/01) | Besançon | **7 / 20** | Partie I « Graphopolis » — machines de Turing |

Ordre de priorité retenu (faiblesse × proximité de l'épreuve) :
**ALO → SPP → MIA → AGC → EP**

ALO cumule la pire note et l'examen le plus proche : priorité absolue.

## Faiblesse transversale identifiée — « la Rigueur »

Traitée dans la plateforme comme une **6ᵉ matière**, car elle coûte des points dans au moins
trois copies indépendamment des connaissances.

**Symptôme.** Les réponses sont rédigées en prose descriptive et qualitative
(« permet de… », « il sera pertinent de… », « peu pratique pour… ») là où le barème attend
une justification technique fermée.

**Preuves relevées par le correcteur :**

| Copie | Annotation | Ce qui manquait |
|---|---|---|
| AGC Q1.1 | « justifier » | Affirmation posée sans démonstration |
| AGC Q1.1 | « évaluation ? » | Aucune complexité chiffrée donnée |
| AGC Q1.1 | « pas vu dans le cours » | Notion inventée hors référentiel |
| AGC Q1.2 | « alors ce n'est plus une matrice » | Contradiction interne non détectée (« matrice d'adjacence sous forme de listes de listes ») |
| SPP Ex1 | « faux, choisir, pas équivalent » | Deux formalisations proposées au lieu d'une ; confusion condition nécessaire / suffisante |
| EP Q1 | « ? » | Paraphrase de l'énoncé, pas de construction formelle |

**Trois erreurs-types à drainer :**
1. **Décrire au lieu de démontrer** — pas de complexité, pas de contre-exemple, pas de règle nommée.
2. **Ne pas trancher** — proposer plusieurs réponses en espérant qu'une soit bonne. Sanctionné (SPP Ex1.3).
3. **Sortir du référentiel** — utiliser des notions absentes du cours. Le barème ne les reconnaît pas.

**Contre-mesure dans la plateforme.** Chaque exercice à rédaction est accompagné d'une grille
« ce que le correcteur attend » : la réponse est comparée non pas à un corrigé narratif mais
à une checklist d'attendus (quantificateur posé, complexité chiffrée, règle citée, réponse unique).

## La découverte majeure — ALO n'est pas une lacune de connaissances

Relecture de la copie ALO, page 2, annotation en rouge du correcteur :

> ⚠ **« Pour les 3 questions de modélisation il était demandé un schéma.
> Vous avez rendu du pseudo-code. Je n'ai donc rien à noter. »**

**Quinze points sur vingt annulés pour un problème de format.** Les trois réponses
sont rédigées en plan indenté — noms de classes, attributs et méthodes alignés par
tabulations — au lieu d'un diagramme de classes.

Et le fond était juste. Les trois copies contiennent les bons patrons :

| Conception | Ce qui était écrit | Patron correspondant |
|---|---|---|
| 1 — dossiers patients | `class Status_Etats` : actif, achevé, en cours de validation, « régissant les actions » | **État** |
| 1 | « 1 à n Consultations qui chacune admet 1 à n prescriptions » | **Composite** |
| 1 | `class Analyses_Medicales [Abstract]` → Sang, Radio | classe abstraite |
| 2 — simulateur | `class Stratégie` → Conduite Urbaine, Conduite Éco | **Stratégie**, nommée |
| 2 | `Evenements_Exterieur` + `alerter_autre_vehicule()` | **Observateur** |
| 3 — commandes | `class Status` + « règles de transition centralisées » | **État** |
| 3 | alertes vers Facturation et Service Client au changement de statut | **Observateur** |

**Conséquence sur la préparation.** Il ne faut pas repartir de zéro sur ALO.
L'effort porte sur le **tracé du diagramme** et l'**étiquetage des patrons**,
pas sur la théorie. C'est une compétence qui s'acquiert en quelques heures.

## SPP — l'épreuve portait sur Why3, pas sur la théorie

Relecture des pages 2 et 3 : trois exercices sur quatre demandaient d'écrire du
Why3 correct, **sur feuille et sans machine**.

| Exercice | Contenu | Note | Annotation |
|---|---|---|---|
| 1 | Formalisation propositionnelle | 2/5 | « faux, choisir, pas équivalent » |
| 2 | `let P1 (a:int, b:int) : int = if a then b` | **0** | « erreur de type » |
| 3 | `predicate transitif`, `asym` | **0** | « voir énoncé », « incomplet » |
| 4 | `length(Nil) = Nil = 0` | **0** | « cours pas connu » |
| 4 Q5 | `lemma l2 : 0 < len(l)` | **0** | « + forall », `<` corrigé en `≤` |

Trois erreurs récurrentes, toutes syntaxiques :
1. **Confusion `int` / `bool`** — une implication logique codée par un `if`.
2. **Variables libres dans les lemmes** — d'où le « + forall ».
3. **Définitions inductives écrites en types** au lieu de valeurs.

À quoi s'ajoute, une troisième fois sur la même copie, **deux formules superposées
au lieu d'une** (question 8).

## AGC — un problème de programmation dynamique traité en glouton

L'exercice 2 était la **plus longue sous-séquence commune**. Traité en double boucle
naïve. Noté 2 sur 6.

| Question | Annotation |
|---|---|
| 2.1 | **« → pas Glouton »** |
| 2.3 | « syntaxe : utiliser les tableaux » · **« il ne faut pas revenir au début à chaque fois »** · « incomplet » |
| 2.4 « la complexité de cette solution est » | **laissée vide**, le correcteur a mis « ? » |
| 1.4 | **« pas d'explication = 0 »** · « Où sont les tests ! » |
| 1.3 | « comment ces nœuds sont initialisés ? » |

« Il ne faut pas revenir au début à chaque fois » est le diagnostic exact : la boucle
interne recalcule, ce qui est le symptôme du chevauchement des sous-problèmes — donc
de la programmation dynamique manquée.

Et « pas d'explication = 0 » est la même faute qu'à ALO : le livrable rendu n'est pas
celui qui est attendu.

## EP — « deux boucles imbriquées … Θ(log n) »

Exercice 2, question 1 :

> « Ici nous avons **2 boucles imbriquées** avec des conditions. Plus n sera grand,
> plus le nombre d'actions élémentaires sera **logarithmiquement grand avec Θ(log n)**. »

Deux boucles imbriquées coûtent **O(n²)**. Le mot « logarithmique » est employé au sens
de « qui grandit beaucoup » — c'est l'inverse, et il revient à l'exercice 3
(« complexité logarithmique importante »).

Autres constats :

- **Exercice 2 question 3 : vide.** **Exercice 3 question 3 : vide.**
- Exercice 2 question 2 s'interrompt sur « si chaque calcul dure 10⁻⁶ seconde »
  sans faire le calcul.
- Définition de NP circulaire : « NP est par définition décidable donc p ⊆ NP est
  décidable » — le correcteur a mis « ? ».
- Exercice 4 (réduction) : quelques lignes de notation ensembliste, aucune construction.

## MIA — l'exercice portait sur le raisonnement par défaut

L'exercice 2 posait quatre phrases commençant toutes par « **en général** ».
Réponse rendue : `∀x manager(x) ⇒ expérimenté(x)`.

> Annotation : **« Non on veut des défauts »** — 0 point.

« En général » signale un **défaut au sens de Reiter**, pas une implication universelle.
Avec des implications classiques, la base devient d'ailleurs **incohérente** :
un stagiaire est manager, donc expérimenté, donc responsable — et la phrase 4 dit
qu'il ne l'est pas.

Erreur associée : `∀x stagiaire(x) ⟺ manager(x)`, une équivalence là où l'énoncé ne
donne qu'une direction.

Ce chapitre 2 — *les logiques non classiques* — est, d'après la matrice de l'enseignant,
l'un des plus régulièrement évalués depuis 2010.

## Lacunes de contenu repérées à la première lecture

- **SPP** — Traduction langue naturelle → logique propositionnelle. Confusion
  condition nécessaire / condition suffisante (« il faut que » vs « il suffit que »).
  3 réponses fausses sur 5 dès l'exercice 1. À reprendre depuis `cProp.pdf` / `cPred.pdf`.
- **AGC** — Choix et évaluation des structures de données pour les graphes
  (listes d'adjacence vs matrice d'adjacence), et coût associé. Tri par arbre binaire.
- **EP** — Construction formelle d'une machine de Turing, comptage des actions
  élémentaires, complexité. Sujet « Graphopolis ».
- **MIA** — Prolog : faits, requêtes, unification. Partie I notée 1,25 / 6.
- **ALO** — QCM de cours largement faux → le socle théorique n'est pas en place.
  Diagnostic à approfondir sur les 7 pages restantes.

## Statut de l'analyse

| Copie | Pages | Analysées | Reste |
|---|---|---|---|
| COPIE_ALO_ZAMON.pdf | 8 | **5** | 3 |
| COPIE_SPP_ZAMON_a.pdf | 4 | **3** | 1 |
| COPIE_MIA_ZAMON.pdf | 12 | **4** | 8 |
| COPIE_AGC_ZAMON_a.pdf | 6 | **3** | 3 |
| COPIE_EP_ZAMON.pdf | 4 | **3** | 1 |

Les cinq copies sont couvertes sur leurs exercices principaux. Le reste concerne des
pages de garde, des feuilles blanches et des fins d'exercice déjà caractérisées.

## Ce que la relecture change, en une phrase par matière

| Matière | Ce qu'on croyait | Ce que la copie montre |
|---|---|---|
| **ALO** | socle théorique absent | patrons maîtrisés, **schéma jamais dessiné** |
| **SPP** | logique de Hoare à revoir | l'épreuve portait sur **Why3 et les définitions inductives** |
| **AGC** | manque de rigueur | **problème de programmation dynamique non reconnu** |
| **EP** | machine de Turing mal construite | plus grave : **confusion sur ce qu'est un logarithme** |
| **MIA** | Prolog fragile | l'exercice portait sur le **raisonnement par défaut**, chapitre non révisé |

Trois de ces cinq erreurs ne sont pas des lacunes de connaissances mais des erreurs
de **format ou de reconnaissance du problème**. Elles se corrigent vite.

L'approfondissement se fait matière par matière, au moment de générer le contenu pédagogique
de chacune, afin que les fiches ciblent les erreurs réellement commises.