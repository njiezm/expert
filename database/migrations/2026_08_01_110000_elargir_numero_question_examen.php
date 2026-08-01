<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les intitulés d'exercice des sujets d'ALO ne tiennent pas en 16 caractères :
 * l'énoncé réel numérote « Conception : Question 1 », et les examens blancs
 * reprennent un libellé parlant plutôt qu'un simple numéro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mock_exam_questions', function (Blueprint $table) {
            $table->string('number', 120)->change();
        });
    }

    public function down(): void
    {
        Schema::table('mock_exam_questions', function (Blueprint $table) {
            $table->string('number', 16)->change();
        });
    }
};