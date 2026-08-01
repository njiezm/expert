<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Les intitulés d'exercice des sujets d'ALO ne tiennent pas en 16 caractères :
 * l'énoncé réel numérote « Conception : Question 1 », et les examens blancs
 * reprennent un libellé parlant plutôt qu'un simple numéro.
 *
 * L'élargissement se fait en SQL direct plutôt que par `->change()`. Laravel
 * accompagne en effet tout `change()` d'une clause `DROP IDENTITY IF EXISTS`,
 * introduite seulement dans PostgreSQL 10 : sur une version antérieure — celle
 * de l'hébergement de production — la migration échoue sur une erreur de syntaxe.
 * Un simple ALTER TYPE passe sur toutes les versions.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE mock_exam_questions ALTER COLUMN number TYPE varchar(120)');
    }

    public function down(): void
    {
        // Tronqué au passage : les libellés longs ne tiendraient pas en 16 caractères.
        DB::statement('ALTER TABLE mock_exam_questions ALTER COLUMN number TYPE varchar(16) USING left(number, 16)');
    }
};