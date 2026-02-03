<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\JsonAuthController;
use App\Http\Controllers\Maestro\DashboardController as MaestroDashboardController;
use App\Http\Controllers\Alumnado\DashboardController as AlumnadoDashboardController;
use App\Http\Controllers\Padre\DashboardController as PadreDashboardController;
use App\Http\Controllers\Maestro\StudentsController;
use App\Http\Controllers\Maestro\GradesController;

Route::middleware(['web'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [JsonAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [JsonAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [JsonAuthController::class, 'logout'])->name('logout');

    Route::middleware('session.auth')->group(function () {

        Route::middleware('session.role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', function () {
                return view('dashboards.admin');
            });
        });

        Route::middleware('session.role:maestro')->prefix('maestro')->group(function () {
            /* Propósito: Dashboard inicial del maestro con collage de gráficas por materia. */
            Route::get('/dashboard', [MaestroDashboardController::class, 'overview'])
                ->name('maestro.dashboard');

            /* Propósito: Dashboard detallado (tabla de alumnos y calificaciones) por materia. */
            Route::get('/detalle', [MaestroDashboardController::class, 'index'])
                ->name('maestro.dashboard.detail');

            Route::get('/alumnos/{studentId}', [StudentsController::class, 'show'])
                ->name('maestro.students.show');

            Route::post('/alumnos/{studentId}/materias/{subjectId}/calificaciones', [GradesController::class, 'updateRow'])
                ->name('maestro.grades.updateRow');

            Route::get('/grupos', function () {
                return view('maestro.grupos');
            });
        });

        Route::middleware(['session.auth', 'session.role:alumnado,padre'])->prefix('alumnado')->group(function () {
            Route::get('/dashboard', [AlumnadoDashboardController::class, 'index'])->name('alumnado.dashboard');

            Route::get('/tareas', function () {
                return view('alumnado.tareas');
            });
        });

        Route::middleware('session.role:padre')->prefix('padre')->group(function () {

            Route::get('/dashboard', [PadreDashboardController::class, 'dashboard'])
                ->name('padre.dashboard');

            Route::get('/seleccionar-alumno', [PadreDashboardController::class, 'showSelectStudent'])
                ->name('padre.selectStudent');

            Route::post('/seleccionar-alumno', [PadreDashboardController::class, 'selectStudent'])
                ->name('padre.selectStudent.post');
        });
    });
});
