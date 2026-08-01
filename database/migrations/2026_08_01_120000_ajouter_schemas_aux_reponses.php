<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Support des diagrammes de classes dans les réponses.
 *
 * L'épreuve d'ALO de janvier a été notée 0 parce que les trois questions de
 * conception ont reçu du pseudo-code là où un schéma était demandé. S'entraîner
 * à rédiger ne sert donc à rien : il faut s'entraîner à **dessiner**. D'où un
 * éditeur de diagrammes intégré, dont la production est stockée ici.
 *
 * Le format est un JSON { nodes, links, labels } produit par l'éditeur SVG.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // Déclenche l'affichage de l'éditeur plutôt que d'une simple zone de texte.
            $table->boolean('needs_diagram')->default(false)->after('rubric');
        });

        Schema::table('exercise_attempts', function (Blueprint $table) {
            $table->json('diagram')->nullable()->after('answer');
        });

        Schema::table('mock_exam_questions', function (Blueprint $table) {
            $table->boolean('needs_diagram')->default(false)->after('rubric');
        });

        Schema::table('mock_exam_answers', function (Blueprint $table) {
            $table->json('diagram')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        Schema::table('mock_exam_answers', fn (Blueprint $t) => $t->dropColumn('diagram'));
        Schema::table('mock_exam_questions', fn (Blueprint $t) => $t->dropColumn('needs_diagram'));
        Schema::table('exercise_attempts', fn (Blueprint $t) => $t->dropColumn('diagram'));
        Schema::table('exercises', fn (Blueprint $t) => $t->dropColumn('needs_diagram'));
    }
};