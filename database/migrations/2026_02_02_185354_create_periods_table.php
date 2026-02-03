<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* Propósito: Crea el catálogo de periodos académicos (1 a 6). */
    public function up()
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number'); /* 1..6 */
            $table->string('name', 60);           /* Periodo 1, Periodo 2, etc. */
            $table->timestamps();

            $table->unique('number');
        });
    }

    /* Propósito: Revierte la creación del catálogo de periodos. */
    public function down()
    {
        Schema::dropIfExists('periods');
    }
};
