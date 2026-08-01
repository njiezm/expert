<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fiche de cours digeste. La structure en 5 temps est imposée :
        // on ne lit pas un polycopié, on parcourt un chemin.
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('intuition')->nullable();         // l'idée en langage courant
            $table->text('formalism')->nullable();         // la définition exacte attendue
            $table->text('worked_example')->nullable();    // un exemple déroulé
            $table->text('pitfalls')->nullable();          // les pièges classiques
            $table->text('examiner_expects')->nullable();  // ce que le correcteur veut lire
            $table->json('source_refs')->nullable();       // {resource_id, pages}
            $table->unsignedSmallInteger('est_minutes')->default(15);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['chapter_id', 'slug']);
        });

        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gap_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 16)->default('definition'); // definition|formule|methode|piege
            $table->text('front');
            $table->text('back');
            $table->text('hint')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(3);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // État de répétition espacée (SM-2), une ligne par carte.
        Schema::create('flashcard_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flashcard_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->unsignedSmallInteger('repetitions')->default(0);
            $table->unsignedSmallInteger('lapses')->default(0);
            $table->date('due_on')->nullable()->index();
            $table->timestampTz('last_reviewed_at')->nullable();
            $table->timestamps();
        });

        // Journal des révisions, pour tracer la courbe de mémorisation.
        Schema::create('flashcard_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flashcard_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quality');       // 0..5 (SM-2)
            $table->unsignedSmallInteger('duration_sec')->default(0);
            $table->decimal('ease_after', 4, 2)->nullable();
            $table->unsignedSmallInteger('interval_after')->nullable();
            $table->timestampTz('reviewed_at');
            $table->timestamps();
        });

        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('statement');
            $table->text('hint')->nullable();      // palier 1 : un coup de pouce
            $table->text('method')->nullable();    // palier 2 : la méthode, sans le résultat
            $table->text('solution')->nullable();  // palier 3 : la solution complète
            $table->json('rubric')->nullable();    // grille d'attendus : [{label, points}]
            $table->string('origin', 16)->default('td'); // td|devoir|annale|genere
            $table->unsignedTinyInteger('difficulty')->default(3);
            $table->unsignedSmallInteger('est_minutes')->default(20);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->longText('answer')->nullable();
            $table->unsignedTinyInteger('reveal_level')->default(0); // 0 rien, 1 indice, 2 méthode, 3 solution
            $table->json('rubric_check')->nullable();  // cases cochées de la grille
            $table->unsignedTinyInteger('self_score')->nullable();  // 0..100
            $table->unsignedInteger('duration_sec')->default(0);
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('flashcard_reviews');
        Schema::dropIfExists('flashcard_states');
        Schema::dropIfExists('flashcards');
        Schema::dropIfExists('lessons');
    }
};