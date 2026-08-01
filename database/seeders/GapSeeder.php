<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\ExamPaper;
use App\Models\Gap;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Lacunes relevées sur les copies de la session initiale.
 *
 * Chaque entrée `evidence` est une annotation réellement portée par le
 * correcteur sur la copie, ou un fait vérifiable de la copie. Rien n'est
 * supposé : ce qui n'a pas été lu n'est pas listé.
 */
class GapSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->gaps() as $code => $entries) {
            $subject = Subject::where('code', $code)->first();

            if (! $subject) {
                continue;
            }

            $paper = ExamPaper::where('subject_id', $subject->id)->first();

            foreach ($entries as $i => $data) {
                $chapter = isset($data['chapter'])
                    ? Chapter::where('subject_id', $subject->id)->where('code', $data['chapter'])->first()
                    : null;

                Gap::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $data['title']],
                    [
                        'chapter_id' => $chapter?->id,
                        'exam_paper_id' => $paper?->id,
                        'kind' => $data['kind'],
                        'evidence' => $data['evidence'] ?? null,
                        'explanation' => $data['explanation'] ?? null,
                        'remedy' => $data['remedy'] ?? null,
                        'severity' => $data['severity'],
                        'position' => $i + 1,
                    ]
                );
            }
        }
    }

    private function gaps(): array
    {
        return [

            'SPP' => [
                [
                    'title' => 'Condition nécessaire confondue avec condition suffisante',
                    'chapter' => 'Prop',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'faux, choisir, pas équivalent',
                    'explanation' => "Question 3 : « le travail est une condition nécessaire à l'obtention de bonnes notes ». Deux formules ont été écrites, T ⇒ N puis ¬T ⇒ ¬N, présentées comme équivalentes. Elles ne le sont pas, et la bonne réponse est N ⇒ T.",
                    'remedy' => "Retenir la règle de lecture : « A est nécessaire à B » s'écrit B ⇒ A ; « A est suffisant pour B » s'écrit A ⇒ B. Le mot qui suit « nécessaire » est la conclusion de l'implication, pas son hypothèse.",
                ],
                [
                    'title' => 'Formalisation de « c\'est seulement si »',
                    'chapter' => 'Prop',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'evidence' => 'f (question 1)',
                    'explanation' => "« C'est seulement si un étudiant travaille qu'il a de bonnes notes » a été formalisé T ⇒ N. « Seulement si » introduit une condition nécessaire : la réponse attendue est N ⇒ T.",
                    'remedy' => "Construire un tableau de correspondance des tournures françaises vers l'implication, et le réciter avant l'épreuve : « si », « seulement si », « il faut que », « il suffit que », « à moins que », « malgré ».",
                ],
                [
                    'title' => 'Trois formalisations fausses sur cinq à l\'exercice 1',
                    'chapter' => 'Prop',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'Quasiment aucun acquis',
                    'explanation' => "L'exercice d'ouverture, le plus accessible du sujet, a été manqué aux trois quarts. Le reste de la copie n'a pas pu compenser : 1,5/20.",
                    'remedy' => "Reprendre exProp.pdf et cProp.pdf intégralement avant toute autre chose en SPP. Ce chapitre conditionne la logique des prédicats, les contrats et la logique de Hoare.",
                ],
            ],

            'AGC' => [
                [
                    'title' => 'Structures de données pour les graphes : aucun coût donné',
                    'chapter' => 'G1',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => 'évaluation ?',
                    'explanation' => "Question 1.1 : les structures candidates ont été décrites qualitativement (« peu pertinent », « permet un accès plus direct ») sans jamais donner de complexité. Le correcteur réclame explicitement l'évaluation. 2 points sur 7.",
                    'remedy' => "Mémoriser le tableau des coûts : matrice d'adjacence en O(n²) mémoire, test d'arête en O(1), parcours des voisins en O(n) ; listes d'adjacence en O(n+m) mémoire, test d'arête en O(deg), parcours des voisins en O(deg). Toute réponse sur le choix d'une structure doit citer ces chiffres.",
                ],
                [
                    'title' => 'Affirmation posée sans démonstration',
                    'chapter' => 'G1',
                    'kind' => 'rigueur',
                    'severity' => 4,
                    'evidence' => 'justifier',
                    'explanation' => "L'écartement des listes et tableaux est affirmé sans argument. Le correcteur a écrit « justifier » en marge.",
                    'remedy' => "Systématiser le triptyque : affirmation, puis raison, puis chiffre ou contre-exemple. Trois phrases minimum par choix technique.",
                ],
                [
                    'title' => 'Contradiction interne non détectée : « matrice sous forme de listes »',
                    'chapter' => 'G1',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'evidence' => "alors ce n'est plus une matrice",
                    'explanation' => "Question 1.2 propose « une matrice d'adjacence carrée sous forme de listes de listes ». Les deux représentations sont mutuellement exclusives ; l'énoncé de la réponse se contredit.",
                    'remedy' => "Avant de rendre, relire chaque définition posée et vérifier qu'elle reste cohérente avec le terme employé. Matrice et liste d'adjacence sont deux choix, pas un continuum.",
                ],
                [
                    'title' => 'Emploi d\'une notion absente du cours',
                    'chapter' => 'G1',
                    'kind' => 'rigueur',
                    'severity' => 3,
                    'evidence' => 'pas vu dans le cours',
                    'explanation' => "Une notion hors référentiel a été introduite. Le barème ne la reconnaît pas, donc elle ne rapporte rien, même si elle est juste par ailleurs.",
                    'remedy' => "S'en tenir au vocabulaire de AGC-cours.pdf. En cas de doute, employer le terme du polycopié plutôt qu'un synonyme trouvé ailleurs.",
                ],
            ],

            'EP' => [
                [
                    'title' => 'Machine de Turing paraphrasée au lieu d\'être construite',
                    'chapter' => 'C3',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => '?',
                    'explanation' => "Sujet « Graphopolis », question 1 : la réponse énonce en prose qu'« il existe une machine de Turing qui décide… » sans donner le septuplet, ni les états, ni la fonction de transition. Question 2 amorce trois états q1, q2, q3 mais ne formalise pas la transition.",
                    'remedy' => "S'entraîner à écrire systématiquement le septuplet complet (Q, Σ, Γ, δ, q₀, q_accept, q_reject) puis la table de transition, même quand la question semble n'appeler qu'une explication.",
                ],
                [
                    'title' => 'Comptage des actions élémentaires non abouti',
                    'chapter' => 'C6',
                    'kind' => 'contenu',
                    'severity' => 4,
                    'explanation' => "Le raisonnement arrive à |V|·|E| pour l'énumération des couples, mais s'arrête avant d'en tirer une classe de complexité exploitable.",
                    'remedy' => "Terminer tout comptage par une majoration en notation O, et rattacher le résultat à une classe (P, NP…). Un calcul non conclu ne vaut pas les points de la conclusion.",
                ],
            ],

            'ALO' => [
                [
                    'title' => 'QCM de cours largement faux',
                    'chapter' => 'C1-Objet',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'explanation' => "Le QCM d'ouverture, qui porte sur les définitions du modèle objet, comporte plusieurs réponses marquées fausses et plusieurs questions laissées vides. Note finale : 0/20.",
                    'remedy' => "Reprendre le chapitre 1 de alo_V9.pdf définition par définition, puis se tester sur les QCM des quatre annales disponibles (2022, 2023, 2024, 2025) avant tout travail sur les patrons.",
                ],
                [
                    'title' => "Le QCM d'ALO pénalise l'erreur : les cases vides étaient justifiées",
                    'chapter' => 'C1-Objet',
                    'kind' => 'methode',
                    'severity' => 3,
                    'evidence' => 'Mauvaise réponse = − 0,25 point (vous perdez des points)',
                    'explanation' => "Plusieurs items de votre QCM sont vides, ce qui semble être une perte sèche. Ce n'en est pas une : le barème d'ALO retire 0,25 point par erreur, contre 0 pour une abstention. Répondre au hasard sur quatre propositions a une espérance de −0,0625 point. Vos abstentions étaient donc le bon calcul ; le 0/20 vient d'ailleurs.",
                    'remedy' => "Retenir le seuil exact : répondre dès que vous pouvez éliminer assez de propositions pour dépasser une chance sur trois de tomber juste, s'abstenir en dessous. Sur une question à quatre propositions, éliminer deux d'entre elles suffit largement.",
                ],
                [
                    'title' => 'La conception vaut désormais 15 points sur 20',
                    'chapter' => 'DP-Method',
                    'kind' => 'methode',
                    'severity' => 5,
                    'explanation' => "Le format de l'épreuve a basculé. Le QCM valait 20 points en 2022, 15 en 2023 et 2024, et seulement 5 en 2025 — remplacé par trois exercices de conception à 5 points. Réviser le QCM en priorité serait travailler pour un quart de la note.",
                    'remedy' => "Concentrer l'effort sur la reconnaissance des patrons à partir d'un « point d'attention » rédigé, et sur le tracé d'un diagramme de classes annoté. C'est un exercice formulaire, donc entraînable.",
                ],
            ],

            'MIA' => [
                [
                    'title' => 'Prolog : requêtes et unification mal maîtrisées',
                    'chapter' => 'Ch0',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'evidence' => '?',
                    'explanation' => "Exercice 1, partie I notée 1,25 sur 6. Les faits sont posés correctement mais les résultats de requête sont incomplets, et le correcteur a marqué « ? » face à une réponse d'unification.",
                    'remedy' => "Dérouler à la main l'arbre de résolution SLD sur chaque requête plutôt que de deviner le résultat. Utiliser SWISH pour vérifier, mais s'entraîner d'abord sans machine — l'épreuve est sur feuille.",
                ],
                [
                    'title' => 'Score très bas sur l\'ensemble des cinq parties',
                    'kind' => 'contenu',
                    'severity' => 5,
                    'explanation' => "Détail du barème : I 1,25 · II 0,75 · III 0,25 · IV 1,25 · V 1,5, soit 5 sur 30. Aucune partie n'atteint la moitié : le problème est un socle absent, pas une lacune ponctuelle.",
                    'remedy' => "Suivre la matrice examens/chapitres : concentrer l'effort sur les chapitres 0, 2, 4 et 8, qui reviennent dans la quasi-totalité des annales depuis 2010.",
                ],
            ],

            'RIG' => [
                [
                    'title' => 'Décrire au lieu de démontrer',
                    'chapter' => 'R1',
                    'kind' => 'rigueur',
                    'severity' => 5,
                    'evidence' => 'justifier · évaluation ?',
                    'explanation' => "Présent sur AGC et EP. Les réponses expliquent ce qu'une notion « permet » sans jamais chiffrer, démontrer ni produire de contre-exemple. Le barème compte des éléments précis, pas une impression de compréhension.",
                    'remedy' => "Pour chaque réponse rédigée, vérifier avant de passer à la suite : y a-t-il un chiffre, une règle nommée, ou un contre-exemple ? Si non, la réponse est incomplète.",
                ],
                [
                    'title' => 'Ne pas trancher entre deux réponses',
                    'chapter' => 'R2',
                    'kind' => 'rigueur',
                    'severity' => 5,
                    'evidence' => 'faux, choisir, pas équivalent',
                    'explanation' => "Sur SPP, deux formalisations ont été proposées pour la même question, en espérant qu'une soit retenue. Le correcteur a explicitement demandé de choisir. Proposer deux réponses fait perdre les points des deux.",
                    'remedy' => "Une question, une réponse. Si l'hésitation persiste, écrire la réponse retenue puis, en une ligne, l'argument qui a fait pencher — jamais les deux formules côte à côte.",
                ],
                [
                    'title' => 'Sortir du référentiel du cours',
                    'chapter' => 'R3',
                    'kind' => 'rigueur',
                    'severity' => 4,
                    'evidence' => 'pas vu dans le cours',
                    'explanation' => "Sur AGC, une notion absente du polycopié a été mobilisée. Elle n'existe pas au barème.",
                    'remedy' => "Réviser sur les polycopiés, pas sur des ressources extérieures. Le vocabulaire de l'enseignant est celui de la grille de correction.",
                ],
                [
                    'title' => 'Gérer six heures d\'épreuve le 26 août',
                    'chapter' => 'R4',
                    'kind' => 'methode',
                    'severity' => 4,
                    'explanation' => "AGC de 15 h à 18 h, puis SPP de 20 h à 23 h. La seconde épreuve est la matière la plus faible (1,5/20) et tombe après trois heures de composition et une coupure courte.",
                    'remedy' => "S'entraîner au moins deux fois en double session : un examen blanc AGC l'après-midi, puis un examen blanc SPP le soir, le même jour. Préparer le repas et la pause de 18 h à 20 h à l'avance.",
                ],
            ],
        ];
    }
}