<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentStudentTable extends Migration
{
    /* Propósito: Crear tabla pivote para relacionar padres con alumnos (N:M). */
    public function up()
    {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('student_id');

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['parent_id', 'student_id']);
            $table->index(['student_id']);
        });
    }

    /* Propósito: Revertir la creación de la tabla pivote. */
    public function down()
    {
        Schema::dropIfExists('parent_student');
    }
}
