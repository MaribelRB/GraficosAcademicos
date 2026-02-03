<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* Propósito: Crea la tabla de materias. */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->timestamps();
        });
    }

    /* Propósito: Revierte la creación de la tabla de materias. */
    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
