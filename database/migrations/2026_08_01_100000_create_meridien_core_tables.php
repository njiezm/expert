<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les 5 matières du Master, plus la matière transversale « Rigueur »
        // issue du diagnostic des copies.
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();          // ALO, EP, AGC, SPP, MIA, RIG
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('tagline')->nullable();
            $table->string('color', 9);                   // teinte de la matière
            $table->timestampTz('exam_at')->nullable();
            $table->unsignedSmallInteger('exam_duration_min')->nullable();
            $table->string('exam_mode', 16)->default('amphi');   // amphi | distance_nuit
            $table->decimal('initial_grade', 4, 2)->nullable();  // note obtenue en session 1
            $table->string('initial_session')->nullable();
            $table->string('initial_centre')->nullable();
            $table->unsignedTinyInteger('priority')->default(3); // 1 = le plus urgent
            $table->boolean('is_transversal')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Découpage pédagogique : l'unité de travail et de mesure de la maîtrise.
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('code', 24);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->unsignedTinyInteger('exam_weight')->default(3);  // 1..5, poids au barème
            $table->unsignedTinyInteger('difficulty')->default(3);   // 1..5
            $table->unsignedSmallInteger('est_minutes')->default(60);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'slug']);
        });

        // Les 132 documents de public/pdfs, indexés et tagués.
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 16)->index();   // cours|td|exercice|devoir|corrige|annale|copie|annexe
            $table->string('title');
            $table->string('filename');
            $table->string('relative_path', 512)->unique();
            $table->string('extension', 12);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->boolean('is_scan')->default(false);   // texte non extractible
            $table->boolean('has_text')->default(false);
            $table->longText('text_content')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_solution')->default(false);  // corrigé associé
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subjects');
    }
};