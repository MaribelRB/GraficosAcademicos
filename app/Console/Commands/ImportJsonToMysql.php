<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportJsonToMysql extends Command
{
    /* Propósito: Nombre del comando para importar JSON a MySQL. */
    protected $signature = 'import:json-mysql {--truncate : Limpia tablas antes de importar}';

    /* Propósito: Descripción del comando. */
    protected $description = 'Importa users, subjects y relaciones desde storage/app/data/*.json a MySQL';

    /* Propósito: Ejecuta la importación de los archivos JSON hacia MySQL. */
    public function handle()
    {
        $truncate = (bool)$this->option('truncate');

        $users = $this->readArray('data/users.json');
        $subjects = $this->readArray('data/subjects.json');
        $teacherSubjects = $this->readArray('data/teacher_subjects.json');
        $studentSubjects = $this->readArray('data/student_subjects.json');

        DB::beginTransaction();

        try {
            /* Propósito: Limpia tablas si el usuario lo solicita. */
            if ($truncate) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                DB::table('student_subject')->truncate();
                DB::table('teacher_subject')->truncate();
                DB::table('subjects')->truncate();
                DB::table('users')->truncate();

                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            /* Propósito: Insertar usuarios conservando IDs del JSON. */
            foreach ($users as $u) {
                DB::table('users')->updateOrInsert(
                    ['id' => (int)$u['id']],
                    [
                        'name' => $u['name'] ?? $u['email'],
                        'email' => $u['email'],
                        'password' => $u['password'],
                        'role' => $u['role'] ?? 'alumnado',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            /* Propósito: Insertar materias conservando IDs del JSON. */
            foreach ($subjects as $s) {
                DB::table('subjects')->updateOrInsert(
                    ['id' => (int)$s['id']],
                    [
                        'name' => $s['name'] ?? ('Materia ' . (int)$s['id']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            /* Propósito: Insertar relación maestro-materia. */
            foreach ($teacherSubjects as $row) {
                DB::table('teacher_subject')->updateOrInsert(
                    [
                        'teacher_id' => (int)$row['teacher_id'],
                        'subject_id' => (int)$row['subject_id'],
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            /* Propósito: Insertar relación alumno-materia. */
            foreach ($studentSubjects as $row) {
                DB::table('student_subject')->updateOrInsert(
                    [
                        'student_id' => (int)$row['student_id'],
                        'subject_id' => (int)$row['subject_id'],
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();

            $this->info('Importación completada correctamente.');
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error al importar: ' . $e->getMessage());
            return 1;
        }
    }

    /* Propósito: Lee un JSON desde storage/app y devuelve un arreglo seguro. */
    private function readArray(string $path): array
    {
        if (!Storage::exists($path)) {
            return [];
        }

        $raw = Storage::get($path);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
