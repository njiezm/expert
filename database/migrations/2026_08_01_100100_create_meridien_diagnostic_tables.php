<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les copies d'examen de la session initiale : le point de départ du diagnostic.
        Schema::create('exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_label');            // « Janvier 2026 »
            $table->date('sat_on')->nullable();
            $table->string('centre')->nullable();
            $table->decimal('grade', 5, 2)->nullable();
            $table->decimal('max_grade', 5, 2)->default(20);
            $table->text('appreciation')->nullable();   // mot du correcteur
            $table->json('score_breakdown')->nullable();// détail par exercice
            $table->unsignedSmallInteger('pages')->default(0);
            $table->unsignedSmallInteger('analysed_pages')->default(0);
            $table->timestamps();
        });

        // Une lacune = une chose précise ratée, rattachée à un chapitre.
        // C'est ce qui pilote la priorité du drill et du planning.
        Schema::create('gaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_paper_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 16)->default('contenu');  // contenu | rigueur | methode
            $table->string('title');
            $table->text('evidence')->nullable();      // annotation exacte du correcteur
            $table->text('explanation')->nullable();   // pourquoi c'était faux
            $table->text('remedy')->nullable();        // comment le corriger
            $table->unsignedTinyInteger('severity')->default(3);  // 1..5
            $table->string('status', 16)->default('ouverte');     // ouverte|en_cours|maitrisee
            $table->timestampTz('resolved_at')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaps');
        Schema::dropIfExists('exam_papers');
    }
};