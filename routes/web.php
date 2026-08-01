<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibliothequeController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\DrillController;
use App\Http\Controllers\ExamenBlancController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\TableauDeBordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Méridien
|--------------------------------------------------------------------------
| Application mono-utilisateur : un seul compte, protégé par le middleware
| « auth ». Aucune inscription publique.
*/

Route::get('/connexion', [AuthController::class, 'show'])->name('connexion');
Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('deconnexion');

Route::middleware('auth')->group(function () {

    Route::get('/', [TableauDeBordController::class, 'index'])->name('tableau-de-bord');

    /* ---------------- Matières et chapitres ---------------- */
    Route::get('/matieres/{subject:slug}', [MatiereController::class, 'show'])->name('matieres.show');
    Route::get('/matieres/{subject:slug}/{chapter:slug}', [MatiereController::class, 'chapter'])->name('chapitres.show');

    /* ---------------- Cours digestes ---------------- */
    Route::get('/cours/{lesson}', [LessonController::class, 'show'])->name('cours.show');
    Route::post('/cours/{lesson}/lu', [LessonController::class, 'markRead'])->name('cours.lu');

    /* ---------------- Diagnostic ---------------- */
    Route::get('/diagnostic', [DiagnosticController::class, 'index'])->name('diagnostic.index');
    Route::get('/diagnostic/{subject:slug}', [DiagnosticController::class, 'show'])->name('diagnostic.show');
    Route::post('/lacunes/{gap}/statut', [DiagnosticController::class, 'updateGap'])->name('lacunes.statut');

    /* ---------------- Drill (répétition espacée) ---------------- */
    Route::get('/drill', [DrillController::class, 'index'])->name('drill.index');
    Route::get('/drill/session', [DrillController::class, 'session'])->name('drill.session');
    Route::post('/drill/{flashcard}/noter', [DrillController::class, 'review'])->name('drill.noter');

    /* ---------------- Exercices ---------------- */
    Route::get('/exercices', [ExerciceController::class, 'index'])->name('exercices.index');
    Route::get('/exercices/{exercise}', [ExerciceController::class, 'show'])->name('exercices.show');
    Route::post('/exercices/{exercise}', [ExerciceController::class, 'submit'])->name('exercices.soumettre');

    /* ---------------- Examens blancs ---------------- */
    Route::get('/examens', [ExamenBlancController::class, 'index'])->name('examens.index');
    Route::get('/examens/{mockExam:slug}', [ExamenBlancController::class, 'show'])->name('examens.show');
    Route::post('/examens/{mockExam:slug}/demarrer', [ExamenBlancController::class, 'start'])->name('examens.demarrer');
    Route::get('/composition/{session}', [ExamenBlancController::class, 'compose'])->name('examens.composer');
    Route::post('/composition/{session}/rendre', [ExamenBlancController::class, 'finish'])->name('examens.rendre');
    Route::get('/composition/{session}/correction', [ExamenBlancController::class, 'review'])->name('examens.correction');
    Route::post('/composition/{session}/noter', [ExamenBlancController::class, 'grade'])->name('examens.noter');

    /* ---------------- Planning ---------------- */
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::post('/planning/recalculer', [PlanningController::class, 'rebuild'])->name('planning.recalculer');
    Route::get('/planning/creneaux', [PlanningController::class, 'slots'])->name('planning.creneaux');
    Route::post('/planning/creneaux', [PlanningController::class, 'saveSlots'])->name('planning.creneaux.enregistrer');
    Route::post('/planning/blocs/{block}', [PlanningController::class, 'updateBlock'])->name('planning.bloc');

    /* ---------------- Bibliothèque ---------------- */
    Route::get('/bibliotheque', [BibliothequeController::class, 'index'])->name('bibliotheque.index');
    Route::get('/bibliotheque/{resource}', [BibliothequeController::class, 'show'])->name('bibliotheque.show');
});