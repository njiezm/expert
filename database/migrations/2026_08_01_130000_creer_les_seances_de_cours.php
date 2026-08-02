<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * « Suivre le cours » — le cours narratif, de A à Z.
 *
 * Les fiches (`lessons`) sont un outil de révision : cinq blocs, consultables
 * dans le désordre, qui supposent la notion déjà rencontrée. Une séance est
 * autre chose : un cours suivi, linéaire, qui construit la notion depuis rien,
 * comme un enseignant au tableau. On ne saute pas de séance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->string('slug');

            // Ce qu'il faut avoir suivi avant d'ouvrir cette séance.
            $table->text('prerequis')->nullable();
            // « Aujourd'hui, on va voir… »
            $table->text('intro')->nullable();
            // Le cours lui-même, en Markdown.
            $table->longText('body');
            // « Ce qu'il faut retenir de cette séance. »
            $table->text('recap')->nullable();

            $table->unsignedSmallInteger('duree_min')->default(30);
            $table->timestamps();

            $table->unique(['subject_id', 'slug']);
            $table->index(['subject_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};