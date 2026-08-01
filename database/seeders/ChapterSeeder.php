<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Découpage des cinq matières, extrait des sommaires réels des polycopiés.
 *
 * `exam_weight` (1 à 5) pèse le chapitre au barème et pilote le planning.
 * Pour MIA il est calculé sur la matrice examens/chapitres fournie par
 * l'enseignant, qui recense quinze années d'épreuves : le chapitre 6 (jeux)
 * n'y apparaît jamais, le chapitre 4 (contraintes) presque toujours.
 *
 * `code` correspond au préfixe des fichiers source, ce qui permet de rattacher
 * automatiquement les documents à leur chapitre.
 */
class ChapterSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->structure() as $code => $chapters) {
            $subject = Subject::where('code', $code)->first();

            if (! $subject) {
                continue;
            }

            foreach ($chapters as $i => $data) {
                $chapter = Chapter::updateOrCreate(
                    ['subject_id' => $subject->id, 'slug' => Str::slug($data['title'])],
                    [
                        'code' => $data['code'],
                        'title' => $data['title'],
                        'summary' => $data['summary'],
                        'exam_weight' => $data['weight'],
                        'difficulty' => $data['difficulty'] ?? 3,
                        'est_minutes' => $data['minutes'] ?? 60,
                        'position' => $i + 1,
                    ]
                );

                $this->attachResources($subject, $chapter, $data['files'] ?? []);
            }
        }
    }

    /** Rattache les documents dont le nom correspond aux motifs du chapitre. */
    private function attachResources(Subject $subject, Chapter $chapter, array $patterns): void
    {
        foreach ($patterns as $pattern) {
            Resource::where('subject_id', $subject->id)
                ->where('filename', 'ilike', $pattern)
                ->update(['chapter_id' => $chapter->id]);
        }
    }

    private function structure(): array
    {
        return [

            /* =====================================================================
             | SPP — 10 chapitres, un par paire « exXxx » / « cXxx » du polycopié.
             | Note obtenue : 1,5/20. L'exercice 1 de l'épreuve portait sur la
             | formalisation en logique propositionnelle : 3 réponses fausses sur 5.
             ===================================================================== */
            'SPP' => [
                [
                    'code' => 'Intro', 'title' => 'Vérification déductive — introduction', 'weight' => 2, 'difficulty' => 2, 'minutes' => 40,
                    'summary' => "Ce qu'est prouver un programme, et en quoi cela diffère de le tester. Prise en main de Why3.",
                    'files' => ['exIntro.pdf', 'cIntro.pdf'],
                ],
                [
                    'code' => 'Prop', 'title' => 'Logique propositionnelle', 'weight' => 5, 'difficulty' => 3, 'minutes' => 75,
                    'summary' => "Connecteurs, tables de vérité, équivalences, et surtout la formalisation d'énoncés en langue naturelle — condition nécessaire contre condition suffisante.",
                    'files' => ['exProp.pdf', 'cProp.pdf'],
                ],
                [
                    'code' => 'Pred', 'title' => 'Logique du premier ordre', 'weight' => 5, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => 'Quantificateurs, portée des variables, formalisation de propriétés sur des domaines.',
                    'files' => ['exPred.pdf', 'cPred.pdf'],
                ],
                [
                    'code' => 'Theories', 'title' => 'Théories du premier ordre', 'weight' => 3, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => 'Axiomatisation, modèles, théories décidables et usage par les solveurs SMT.',
                    'files' => ['exTheories.pdf', 'cTheories.pdf'],
                ],
                [
                    'code' => 'Types', 'title' => 'Types inductifs', 'weight' => 3, 'difficulty' => 3, 'minutes' => 60,
                    'summary' => 'Définition de types par constructeurs, filtrage, structures de données algébriques.',
                    'files' => ['exTypes.pdf', 'cTypes.pdf'],
                ],
                [
                    'code' => 'Calculs', 'title' => 'Calculs inductifs', 'weight' => 3, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => 'Fonctions définies par récursion structurelle et leur terminaison.',
                    'files' => ['exCalculs.pdf', 'cCalculs.pdf'],
                ],
                [
                    'code' => 'Contrats', 'title' => 'Contrats : spécification, programmation, preuve', 'weight' => 4, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => 'Précondition, postcondition, invariant, variant. Écrire une spécification avant le code.',
                    'files' => ['exContrats.pdf', 'cContrats.pdf'],
                ],
                [
                    'code' => 'Hoare', 'title' => 'Logique de Hoare', 'weight' => 5, 'difficulty' => 5, 'minutes' => 90,
                    'summary' => "Triplets {P} S {Q}, règles d'inférence, preuve en tableau, invariant de boucle. Le cœur du module.",
                    'files' => ['exHoare.pdf', 'cHoare.pdf', 'annotations.pdf'],
                ],
                [
                    'code' => 'Recur', 'title' => 'Récurrence', 'weight' => 4, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => 'Preuves par récurrence sur les entiers, choix de la propriété à démontrer.',
                    'files' => ['exRecur.pdf', 'cRecur.pdf'],
                ],
                [
                    'code' => 'Induction', 'title' => 'Induction structurelle', 'weight' => 4, 'difficulty' => 5, 'minutes' => 75,
                    'summary' => 'Preuves sur les structures inductives : listes, arbres, termes.',
                    'files' => ['exPreuveInduction.pdf', 'cPreuveInduction.pdf'],
                ],
            ],

            /* =====================================================================
             | ALO — note obtenue : 0/20, et première épreuve du rattrapage.
             | Le polycopié alo_V9 compte 3 chapitres, mais les 9 patrons de
             | conception sont traités séparément et pèsent l'essentiel du barème.
             ===================================================================== */
            'ALO' => [
                [
                    'code' => 'DP-Method', 'title' => "L'épreuve de conception : la méthode", 'weight' => 5, 'difficulty' => 2, 'minutes' => 60,
                    'summary' => "Depuis 2025, trois exercices de conception à 5 points pèsent 15 points sur 20. Ils suivent tous le même moule : un diagramme de classes annoté, et trois patrons à reconnaître dans trois « points d'attention ». Exercice formulaire, donc entraînable.",
                    'files' => ['ALO_Examen_2025_01.pdf', 'ALO_Examen_2025_01_Corrige.pdf'],
                ],
                [
                    'code' => 'C1-Objet', 'title' => 'Le modèle objet', 'weight' => 5, 'difficulty' => 3, 'minutes' => 75,
                    'summary' => 'Abstraction, encapsulation, messages, typage, héritage simple et multiple, classes abstraites, interfaces.',
                    'files' => ['alo_V9.pdf'],
                ],
                [
                    'code' => 'C1-Concept', 'title' => 'Modélisation et principes de conception', 'weight' => 5, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => "Identification des classes, relations d'utilisation, d'agrégation et de composition, principe de substitution de Liskov.",
                ],
                [
                    'code' => 'C2-Java', 'title' => 'Concepts objet en Java', 'weight' => 4, 'difficulty' => 3, 'minutes' => 90,
                    'summary' => 'Classes, constructeurs, this, attributs et méthodes de classe, polymorphisme, héritage, transtypage, classes abstraites, interfaces.',
                    'files' => ['IntroductionJavaV1.pdf'],
                ],
                [
                    'code' => 'C2-Coll', 'title' => 'Collections, entrées-sorties, JDBC', 'weight' => 3, 'difficulty' => 3, 'minutes' => 60,
                    'summary' => 'Les collections Java, les flux, et l’accès aux bases de données.',
                ],
                [
                    'code' => 'DP-Struct', 'title' => 'Patrons structurels : Composite, Décorateur', 'weight' => 5, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => 'Composer des objets en arborescence ; ajouter des responsabilités sans hériter.',
                ],
                [
                    'code' => 'DP-Creat', 'title' => 'Patrons créateurs : Builder, Singleton', 'weight' => 5, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => 'Builder fluent, builder Command, builder officiel avec Director. Singleton et ses pièges.',
                ],
                [
                    'code' => 'DP-Comp', 'title' => 'Patrons comportementaux : État, Observateur, Stratégie, Visiteur', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => "Changer de comportement selon l'état, notifier des abonnés, interchanger un algorithme, parcourir une structure sans la modifier.",
                ],
                [
                    'code' => 'DP-MVC', 'title' => 'Architecture MVC', 'weight' => 4, 'difficulty' => 3, 'minutes' => 45,
                    'summary' => 'Modèle, vue, contrôleur : découpage des responsabilités, illustré par Swing.',
                ],
            ],

            /* =====================================================================
             | EP — Théorie de la calculabilité et algorithmique. Note : 7/20.
             | L'épreuve « Graphopolis » portait sur la construction d'une machine
             | de Turing et le comptage des actions élémentaires.
             ===================================================================== */
            'EP' => [
                [
                    'code' => 'C2', 'title' => 'Problème, algorithme, calculabilité', 'weight' => 3, 'difficulty' => 2, 'minutes' => 45,
                    'summary' => "Ce qu'est un problème, ce qu'est un algorithme, et pourquoi tous les problèmes n'en admettent pas.",
                    'files' => ['cours_ep.pdf'],
                ],
                [
                    'code' => 'C3', 'title' => 'Machine de Turing', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => "Alphabet, mot, langage. Définition formelle du septuplet, configurations, calculs, mots et langages reconnus.",
                    'files' => ['td1_new.pdf', 'td1_new_correction.pdf'],
                ],
                [
                    'code' => 'C4', 'title' => 'Variations sur la machine de Turing', 'weight' => 4, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => 'Plusieurs têtes et rubans, machine non déterministe, machine à 3 symboles, et leurs équivalences.',
                    'files' => ['td2_new.pdf', 'td2-new-correction.pdf'],
                ],
                [
                    'code' => 'C5', 'title' => 'Décidabilité et indécidabilité', 'weight' => 5, 'difficulty' => 5, 'minutes' => 90,
                    'summary' => "Thèse de Church, codage d'une machine, premier langage indécidable, réductions, problème de l'arrêt.",
                    'files' => ['td3_new.pdf', 'td3_new_correction.pdf'],
                ],
                [
                    'code' => 'C6', 'title' => 'Classes de complexité', 'weight' => 4, 'difficulty' => 4, 'minutes' => 60,
                    'summary' => "Complexité d'une machine, classes, comparaison de fonctions, problèmes traitables, thèse de l'invariance.",
                    'files' => ['td4_new.pdf', 'td4-new-correction.pdf'],
                ],
                [
                    'code' => 'C7', 'title' => "Comparaison d'algorithmes", 'weight' => 4, 'difficulty' => 3, 'minutes' => 60,
                    'summary' => 'Opérations fondamentales, nombre d’opérations, complexité en moyenne et au pire.',
                ],
                [
                    'code' => 'C8', 'title' => 'Tris par comparaisons', 'weight' => 4, 'difficulty' => 3, 'minutes' => 75,
                    'summary' => 'Sélection, bulles, insertion, tri rapide : analyse en comparaisons et en affectations.',
                    'files' => ['td5_new.pdf', 'td5_new_correction.pdf'],
                ],
            ],

            /* =====================================================================
             | AGC — Note : 7/20 (Ex1:2, Ex2:2, Ex3:3).
             | Annotations du correcteur sur l'exercice 1 : « justifier »,
             | « évaluation ? », « alors ce n'est plus une matrice ».
             ===================================================================== */
            'AGC' => [
                [
                    'code' => 'G1', 'title' => 'Graphes : notions et représentation', 'weight' => 5, 'difficulty' => 3, 'minutes' => 75,
                    'summary' => "Vocabulaire des graphes, et surtout le choix de représentation : matrice d'adjacence contre listes d'adjacence, avec le coût en mémoire et en temps de chaque opération.",
                    'files' => ['AGC-cours.pdf', 'AGC-introduction.pdf'],
                ],
                [
                    'code' => 'G2', 'title' => 'Chemins, parcours et connexité', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => 'Parcours en largeur et en profondeur, recherche de chemins, plus courts et plus longs chemins, connexité et forte connexité.',
                    'files' => ['ExosAlgoGraphes.pdf'],
                ],
                [
                    'code' => 'G3', 'title' => 'Arbres et arbre couvrant minimal', 'weight' => 4, 'difficulty' => 3, 'minutes' => 60,
                    'summary' => 'Caractérisation des arbres, arbre couvrant de poids minimal, caractérisation des graphes.',
                ],
                [
                    'code' => 'PL', 'title' => 'Programmation linéaire', 'weight' => 4, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => "Mise en équation d'un problème linéaire, forme générale, algorithme du simplexe.",
                    'files' => ['ExosProgLin.pdf'],
                ],
                [
                    'code' => 'PD', 'title' => 'Programmation dynamique', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => 'Mémoïsation, sous-structure optimale, construction du tableau, généralisation.',
                    'files' => ['ExosProgDyn.pdf'],
                ],
                [
                    'code' => 'PG', 'title' => 'Programmation gloutonne', 'weight' => 4, 'difficulty' => 3, 'minutes' => 60,
                    'summary' => "Arbre couvrant minimal, coloration de graphes, choix d'activités, et quand la stratégie gloutonne est optimale.",
                ],
                [
                    'code' => 'CY', 'title' => 'Recherche de cycles', 'weight' => 4, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => "Cycles eulériens et problème du postier chinois, cycles hamiltoniens et voyageur de commerce, algorithmes d'approximation.",
                ],
            ],

            /* =====================================================================
             | MIA — Note : 3,34/20 (5/30).
             | Les poids proviennent de la matrice examens/chapitres officielle,
             | qui couvre les épreuves de 2010-2011 à 2025-2026.
             ===================================================================== */
            'MIA' => [
                [
                    'code' => 'Ch0', 'title' => 'Prolog et programmation logique', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => "Faits, règles, requêtes, unification, backtracking. Présent dans presque toutes les épreuves depuis 2010. Partie notée 1,25/6 en mai.",
                    'files' => ['mainMOIA.pdf', 'mainMOIA (1).pdf'],
                ],
                [
                    'code' => 'Ch1', 'title' => "Introduction à l'IA", 'weight' => 1, 'difficulty' => 1, 'minutes' => 25,
                    'summary' => "Historique et périmètre. Jamais évalué seul, mais alimente le QCM transversal.",
                ],
                [
                    'code' => 'Ch2', 'title' => 'Représentation des connaissances', 'weight' => 5, 'difficulty' => 4, 'minutes' => 75,
                    'summary' => 'Logiques classiques et non classiques, règles de production, objets structurés. Chapitre le plus régulièrement évalué.',
                ],
                [
                    'code' => 'Ch3', 'title' => 'Algorithmes de recherche', 'weight' => 4, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => 'Parcours en largeur et en profondeur, heuristiques, Best-First, A* et AO*. Les animations du cours déroulent chaque algorithme pas à pas.',
                    'files' => ['AnimLarg.pdf', 'AnimProf.pdf', 'AnimAEtoile.pdf', 'AnimAOEtoile.pdf', 'AnimBranch_Bound.pdf'],
                ],
                [
                    'code' => 'Ch4', 'title' => 'Programmation par contraintes', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => 'Définition des contraintes, méthodes de résolution, consistances et énumération. Présent dans la quasi-totalité des annales.',
                ],
                [
                    'code' => 'Ch5', 'title' => 'Systèmes experts', 'weight' => 3, 'difficulty' => 3, 'minutes' => 50,
                    'summary' => 'Base de faits, base de règles, moteur d’inférence. Chaînage avant et arrière. Tombé en 2024-2025 et en 2025-2026.',
                ],
                [
                    'code' => 'Ch6', 'title' => 'Algorithmes des jeux', 'weight' => 2, 'difficulty' => 3, 'minutes' => 50,
                    'summary' => "Minimax et élagage alpha-bêta. Absent de la matrice des annales, mais peut alimenter le QCM.",
                    'files' => ['AnimAlphaBeta.pdf'],
                ],
                [
                    'code' => 'Ch7', 'title' => 'Planification', 'weight' => 2, 'difficulty' => 3, 'minutes' => 45,
                    'summary' => 'Contextes de planification, graphe de potentiels. Rare mais déjà tombé (2014-2015, 2017-2018).',
                ],
                [
                    'code' => 'Ch8', 'title' => 'Apprentissage', 'weight' => 5, 'difficulty' => 4, 'minutes' => 90,
                    'summary' => "Approches d'apprentissage, arbres de décision, réseaux de neurones. Présent dans presque toutes les annales.",
                    'files' => ['irisMIA (2).csv'],
                ],
                [
                    'code' => 'Ch9', 'title' => 'Méthodes incomplètes', 'weight' => 2, 'difficulty' => 3, 'minutes' => 45,
                    'summary' => 'Recherche locale et métaheuristiques, quand la complétude devient trop coûteuse.',
                ],
            ],

            /* =====================================================================
             | RIG — la matière transversale, déduite des annotations du correcteur.
             ===================================================================== */
            'RIG' => [
                [
                    'code' => 'R1', 'title' => 'Démontrer au lieu de décrire', 'weight' => 5, 'difficulty' => 3, 'minutes' => 45,
                    'summary' => "Une affirmation sans justification ne vaut rien. Chiffrer une complexité, produire un contre-exemple, nommer la règle appliquée.",
                ],
                [
                    'code' => 'R2', 'title' => 'Trancher', 'weight' => 5, 'difficulty' => 2, 'minutes' => 30,
                    'summary' => "Proposer deux réponses en espérant qu'une soit bonne est sanctionné. Choisir, et assumer le choix par un argument.",
                ],
                [
                    'code' => 'R3', 'title' => 'Rester dans le référentiel', 'weight' => 4, 'difficulty' => 2, 'minutes' => 30,
                    'summary' => "Le barème ne connaît que le vocabulaire du polycopié. Une notion importée d'ailleurs ne rapporte pas de points.",
                ],
                [
                    'code' => 'R4', 'title' => 'Gérer le temps et le barème', 'weight' => 4, 'difficulty' => 2, 'minutes' => 30,
                    'summary' => "Répartir le temps selon les points, traiter ce qui rapporte, ne jamais laisser une question vide. Six heures d'épreuve le 26 août.",
                ],
            ],
        ];
    }
}