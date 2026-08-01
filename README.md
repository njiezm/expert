# MÉRIDIEN

Plateforme personnelle de préparation aux rattrapages du Master 1 I2A, session d'août 2026.

Le méridien est la ligne qui relie Besançon à la Martinique — les deux centres d'examen —
et le point de passage au zénith. Cinq nœuds sur l'arc : les cinq matières.

---

## Les échéances

| Date | Matière | Créneau | Durée | Note de session 1 |
|---|---|---|---|---|
| 24 août | **ALO** — Architectures Logicielles à Objet | 20h–23h | 3 h | 0 / 20 |
| 25 août | **EP** — Évaluation de Programmes | 14h–16h | 2 h | 7 / 20 |
| 26 août | **AGC** — Algorithmes sur les Graphes | 15h–18h | 3 h | 7 / 20 |
| 26 août | **SPP** — Spécification et Preuves | 20h–23h | 3 h | 1,5 / 20 |
| 28 août | **MIA** — Méthodes et Outils pour l'IA | 15h–17h | 2 h | 3,34 / 20 |

Le 26 août porte deux épreuves, soit six heures de composition dans la journée.

---

## Démarrer

PostgreSQL doit tourner (Laragon). L'application est accessible via le vhost Laragon
`http://expert.test`, ou par le serveur intégré :

```bash
php artisan serve      # http://localhost:8000
npm run dev            # rechargement à chaud pendant le développement
npm run build          # build de production
```

Identifiants : `njiezamon10@gmail.com` / `NjieZm190964@`
(surchargeables via `MERIDIEN_EMAIL` et `MERIDIEN_PASSWORD` dans `.env`).

Les binaires ne sont pas dans le PATH système ; sous Laragon :

```
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe
C:\laragon\bin\nodejs\node-v22\npm.cmd
C:\laragon\bin\postgresql\postgresql-15.14-1\bin\psql.exe
```

---

## Commandes

| Commande | Effet |
|---|---|
| `php artisan meridien:ingest` | Réindexe `public/pdfs` et réextrait le texte |
| `php artisan meridien:ingest --fresh` | Vide la table des ressources d'abord |
| `php artisan meridien:planning` | Régénère créneaux et blocs jusqu'à la dernière épreuve |
| `php artisan meridien:mastery` | Recalcule la maîtrise de tous les chapitres |
| `php artisan db:seed` | Rejoue les seeders de contenu (idempotents) |
| `php artisan test` | Suite de parcours, sur la base `meridien_test` |

L'extraction de texte s'appuie sur `pdftotext` et `pdfinfo` (fournis ici par MiKTeX ;
chemins surchargeables via `MERIDIEN_PDFTOTEXT` et `MERIDIEN_PDFINFO`).

---

## Architecture

**Laravel 13.23 · PHP 8.3 · PostgreSQL 15 · Tailwind 4 · Blade.** Aucun framework JS :
les comportements d'interface tiennent dans `resources/js/app.js`, attachés par attribut
de données.

### Modules

| Module | Route | Rôle |
|---|---|---|
| Tableau de bord | `/` | Compte à rebours, maîtrise, programme du jour |
| Planning | `/planning` | Rétroplanning, disponibilités, blocs de travail |
| Diagnostic | `/diagnostic` | Les 5 copies décortiquées, lacunes rattachées aux chapitres |
| Cours | `/cours/{fiche}` | Fiches digestes en 5 temps |
| Drill | `/drill` | Répétition espacée SM-2 |
| Exercices | `/exercices` | Correction progressive, grille d'attendus |
| Examens blancs | `/examens` | Chronométrés, deux modes de conditions réelles |
| Bibliothèque | `/bibliotheque` | 140 documents, recherche plein texte |

### Les trois moteurs

**`SpacedRepetition`** — SM-2 avec intervalles plafonnés à 10 jours : au-delà, une carte
sortirait de la fenêtre de révision avant le 28 août. Les cartes rattachées à une erreur
d'examen passent en tête de file.

**`PlanningEngine`** — allocation proportionnelle au besoin, et non par tourniquet.
Le besoin combine la maîtrise manquante et la sévérité de l'échec initial : un 1,5/20
ne demande pas le même volume qu'un 7/20. Trois règles dominent : une matière n'est plus
travaillée après son épreuve ; la veille lui est réservée ; les créneaux d'épreuve sont
verrouillés et ne peuvent recevoir de travail.

**`MasteryCalculator`** — la maîtrise combine quatre signaux, dont trois exigent une
production active : lecture 15 %, mémoire 30 %, pratique 35 %, lacunes refermées 20 %.
Lire pèse délibérément peu — la relecture donne un sentiment de maîtrise que les copies
ont démenti. Un exercice dont la solution a été ouverte est plafonné à 40 % de crédit.

### Les deux modes d'examen blanc

- **Amphi** — interface normale. Reproduit la session de janvier à Besançon.
- **Distance nocturne** — contraste abaissé, dominante chaude, aucune distraction.
  Reproduit la session de mai en Martinique.

Le chronomètre est calculé depuis l'échéance serveur : recharger la page ne rend pas de
temps. À l'expiration, la copie est rendue automatiquement. Les réponses sont sauvegardées
en local au fil de la frappe.

---

## Le diagnostic

Les cinq copies sont des scans : aucun texte extractible, lecture visuelle page à page.
Voir [`docs/DIAGNOSTIC.md`](docs/DIAGNOSTIC.md).

Au-delà des notes, les annotations des correcteurs révèlent une **faiblesse transversale**
présente sur trois copies indépendantes, traitée dans la plateforme comme une sixième
matière — **RIG, Rigueur de rédaction** :

1. **Décrire au lieu de démontrer** — « justifier », « évaluation ? »
2. **Ne pas trancher** — « faux, choisir, pas équivalent »
3. **Sortir du référentiel** — « pas vu dans le cours »

C'est ce que matérialise la **grille d'attendus** présente sur chaque exercice et chaque
question d'examen blanc : la réponse n'est pas comparée à un corrigé narratif mais à une
liste d'éléments que le barème compte, à cocher uniquement s'ils figurent littéralement
dans la copie.

---

## État du contenu

Le socle applicatif est complet et testé. Le contenu pédagogique se remplit matière par
matière.

| Matière | Chapitres | Fiches | Cartes | Exercices | Examens blancs |
|---|---|---|---|---|---|
| ALO | 9 | 6 | 34 | 2 | 2 × 3 h |
| EP | 7 | 4 | 24 | 1 | 2 × 2 h |
| AGC | 7 | 4 | 25 | 2 | 2 × 3 h |
| SPP | 10 | 11 | 44 | 4 | 2 × 3 h |
| MIA | 10 | 5 | 30 | 3 | 2 × 2 h |
| RIG | 4 | 4 | 12 | 3 | — |
| **Total** | **47** | **34** | **169** | **15** | **10 examens, 200 points** |

Chaque examen blanc reprend la **durée réelle** de son épreuve et son format observé
dans les annales. Toutes les questions portent une grille d'attendus.

Deux examens par matière : le premier à **J−7**, le second à **J−2** — les deux
créneaux que le moteur de planning réserve automatiquement. Les seconds sujets sont
construits autour des erreurs réellement commises en session 1.

### Ce que l'analyse des annales ALO a changé

Le format de l'épreuve a basculé : le QCM valait **20 points en 2022**, 15 en 2023
et 2024, et **seulement 5 en 2025** — remplacé par **trois exercices de conception à
5 points**, soit 15 sur 20.

Ces exercices sont formulaires. Chacun demande un diagramme de classes et trois patrons
à reconnaître dans trois « points d'attention », avec une clause décisive dans l'énoncé :
*« il faut identifier chaque pattern sur le schéma, si vous ne le faites pas il n'y a
pas de point attribué »*. Singleton et Builder sont hors scope.

D'où un chapitre supplémentaire, **« L'épreuve de conception : la méthode »**, qui porte
un décodeur point d'attention → patron construit sur les corrigés officiels 2024 et 2025.
Cinq patrons — Composite, État, Stratégie, Observateur, Décorateur — couvrent la
quasi-totalité des cas posés en quatre ans.

Le QCM d'ALO **pénalise l'erreur** (−0,25 par mauvaise réponse, 0 pour une abstention).
Le seuil de rentabilité est exactement **une chance sur trois** : au-dessus on répond,
en dessous on s'abstient.

Les 46 chapitres sont issus des sommaires réels des polycopiés. Les poids d'examen de MIA
proviennent de la matrice examens/chapitres de l'enseignant, qui couvre quinze années
d'épreuves : le chapitre 6 (jeux) n'y apparaît jamais, le chapitre 4 (contraintes) presque
toujours.

Les 140 documents sont consultables dès maintenant dans la bibliothèque, avec recherche
plein texte sur les 120 PDF dont le texte est extractible.

### Reste à produire

- Les 8 chapitres SPP restants.
- Le contenu ALO — priorité absolue : 0/20 et première épreuve.
- Le contenu MIA, AGC, EP.
- L'analyse des 29 pages de copies non encore relues (5 pages sur 34 traitées).
