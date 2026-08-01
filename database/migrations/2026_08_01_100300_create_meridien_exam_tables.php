<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_min');
            $table->decimal('total_points', 5, 2)->default(20);
            $table->string('origin', 16)->default('annale');  // annale | genere
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('mock_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 16);            // « Ex 1.2 »
            $table->text('statement');
            $table->decimal('points', 5, 2)->default(1);
            $table->text('solution')->nullable();
            $table->json('rubric')->nullable();      // attendus du correcteur
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Une passation. Le mode reproduit les conditions réelles vécues :
        // « amphi » (Besançon, jour) ou « distance_nuit » (Martinique, épreuve nocturne).
        Schema::create('mock_exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_exam_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 16)->default('amphi');
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->unsignedInteger('elapsed_sec')->default(0);
            $table->unsignedInteger('allowed_sec');
            $table->boolean('was_timed_out')->default(false);
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('mock_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mock_exam_question_id')->constrained()->cascadeOnDelete();
            $table->longText('answer')->nullable();
            $table->json('rubric_check')->nullable();
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->unsignedInteger('duration_sec')->default(0);
            $table->timestamps();

            $table->unique(['mock_exam_session_id', 'mock_exam_question_id'], 'mea_session_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_exam_answers');
        Schema::dropIfExists('mock_exam_sessions');
        Schema::dropIfExists('mock_exam_questions');
        Schema::dropIfExists('mock_exams');
    }
};