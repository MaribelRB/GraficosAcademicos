<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* Propósito: Almacena calificaciones por alumno, materia y periodo. */
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('period_id');

            /* Propósito: Calificación numérica (0.0 a 10.0, ajustable). */
            $table->decimal('grade', 5, 2)->nullable();

            /* Propósito: Campo opcional para observaciones del maestro. */
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            /* Propósito: Evita duplicados: un alumno solo tiene una calificación por materia y periodo. */
            $table->unique(['student_id', 'subject_id', 'period_id'], 'uq_grades_student_subject_period');

            /* Propósito: Relación con alumnos (users). */
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');

            /* Propósito: Relación con materias. */
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');

            /* Propósito: Relación con periodos. */
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });
    }

    /* Propósito: Revierte la creación de la tabla de calificaciones. */
    public function down()
    {
        Schema::dropIfExists('grades');
    }
};
