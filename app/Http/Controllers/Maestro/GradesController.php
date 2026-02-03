<?php

namespace App\Http\Controllers\Maestro;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradesController
{
    /* Propósito: Guarda calificaciones por periodos (1 fila) validando permisos del maestro. */
    public function updateRow(Request $request, $studentId, $subjectId)
    {
        $sessionUser = $request->session()->get('auth.user');
        $teacherId = (int)($sessionUser['id'] ?? 0);

        $studentId = (int)$studentId;
        $subjectId = (int)$subjectId;

        /* Propósito: Validar que el maestro tenga asignada la materia. */
        $teacherHasSubject = DB::table('teacher_subject')
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$teacherHasSubject) {
            abort(403);
        }

        /* Propósito: Validar que el alumno esté inscrito en la materia. */
        $studentInSubject = DB::table('student_subject')
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$studentInSubject) {
            abort(403);
        }

        /* Propósito: Validar payload de calificaciones. */
        $data = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        /* Propósito: Obtener periodos válidos (1..6) y mapear ids existentes. */
        $periodIds = DB::table('periods')->orderBy('number')->pluck('id')->toArray();

        DB::beginTransaction();
        try {
            foreach ($periodIds as $periodId) {
                $key = (string)$periodId;

                $gradeValue = null;
                if (isset($data['grades']) && array_key_exists($key, $data['grades'])) {
                    $val = $data['grades'][$key];

                    /* Propósito: Normalizar vacío a null. */
                    if ($val === '' || $val === null) {
                        $gradeValue = null;
                    } else {
                        $gradeValue = (float)$val;
                    }
                } else {
                    continue;
                }

                /* Propósito: Insertar o actualizar calificación por alumno, materia y periodo. */
                DB::table('grades')->updateOrInsert(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'period_id' => (int)$periodId,
                    ],
                    [
                        'grade' => $gradeValue,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('maestro.dashboard', ['subject_id' => $subjectId])
                ->with('status', 'Calificaciones guardadas.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['grades' => 'No se pudieron guardar las calificaciones.']);
        }
    }
}
