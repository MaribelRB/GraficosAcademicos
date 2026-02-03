<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /* Propósito: Agrega el campo role a la tabla users. */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('alumnado')->after('email');
        });
    }

    /* Propósito: Revierte la adición del campo role. */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
