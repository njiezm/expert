<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vos créneaux réels : soirées en semaine, week-ends, télétravail, congés.
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->date('day')->index();
            $table->string('label', 16);       // soiree|weekend|teletravail|conge|examen
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('minutes');
            $table->boolean('is_locked')->default(false);  // jour d'examen : intouchable
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Ce que le moteur de planning décide d'y mettre.
        Schema::create('study_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_slot_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('day')->index();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity', 16);    // cours|drill|exercice|examen_blanc|revision
            $table->unsignedSmallInteger('planned_minutes');
            $table->unsignedSmallInteger('done_minutes')->default(0);
            $table->string('status', 16)->default('planifie'); // planifie|en_cours|fait|reporte
            $table->text('rationale')->nullable();  // pourquoi le moteur a mis ça ici
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Maîtrise consolidée par chapitre : la mesure de l'objectif « 100 % ».
        Schema::create('chapter_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('mastery')->default(0);      // 0..100
            $table->unsignedSmallInteger('lessons_done')->default(0);
            $table->unsignedSmallInteger('lessons_total')->default(0);
            $table->unsignedSmallInteger('cards_mature')->default(0);
            $table->unsignedSmallInteger('cards_total')->default(0);
            $table->unsignedSmallInteger('exercises_done')->default(0);
            $table->unsignedSmallInteger('exercises_total')->default(0);
            $table->unsignedSmallInteger('gaps_open')->default(0);
            $table->unsignedInteger('minutes_spent')->default(0);
            $table->timestampTz('last_touched_at')->nullable();
            $table->timestamps();
        });

        // Journal brut : alimente les courbes et le recalcul du planning.
        Schema::create('study_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 24)->index();   // lesson_read|card_reviewed|exercise_done|mock_finished
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('minutes')->default(0);
            $table->timestampTz('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_events');
        Schema::dropIfExists('chapter_progress');
        Schema::dropIfExists('study_blocks');
        Schema::dropIfExists('availability_slots');
    }
};