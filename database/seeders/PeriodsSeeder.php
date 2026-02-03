<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodsSeeder extends Seeder
{
    /* Propósito: Inserta los 6 periodos académicos en la tabla periods. */
    public function run()
    {
        for ($i = 1; $i <= 6; $i++) {
            DB::table('periods')->updateOrInsert(
                ['number' => $i],
                [
                    'name' => 'Periodo ' . $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
