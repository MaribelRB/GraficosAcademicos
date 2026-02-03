<?php

namespace App\Http\Controllers\Maestro;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsController
{
    /* Propósito: Muestra la ficha completa del alumno validando acceso del maestro. */
    public function show(Request $request, $studentId)
    {
        $sessionUser = $request->session()->get('auth.user');
        $teacherId = (int)($sessionUser['id'] ?? 0);
        $studentId = (int)$studentId;

        /* Propósito: Validar que el alumno exista y sea alumnado. */
        $student = DB::table('users')
            ->where('id', $studentId)
            ->where('role', 'alumnado')
            ->select('id', 'name', 'email')
            ->first();

        if (!$student) {
            abort(404);
        }

        /* Propósito: Obtener materias asignadas al maestro (control de acceso). */
        $teacherSubjectIds = DB::table('teacher_subject')
            ->where('teacher_id', $teacherId)
            ->pluck('subject_id')
            ->toArray();

        /* Propósito: Obtener materias del alumno que además estén dentro de las materias del maestro. */
        $studentSubjects = DB::table('subjects')
            ->join('student_subject', 'subjects.id', '=', 'student_subject.subject_id')
            ->where('student_subject.student_id', $studentId)
            ->whereIn('subjects.id', $teacherSubjectIds)
            ->select('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        /* Propósito: Si el maestro no comparte materias con el alumno, se bloquea el acceso. */
        if ($studentSubjects->count() === 0) {
            abort(403);
        }

        /* Propósito: Obtener catálogo de periodos (se espera 1..6). */
        $periods = DB::table('periods')
            ->select('id', 'number', 'name')
            ->orderBy('number')
            ->get();

        /* Propósito: Obtener todas las calificaciones del alumno en las materias visibles para el maestro. */
        $subjectIds = $studentSubjects->pluck('id')->toArray();

        $grades = DB::table('grades')
            ->where('student_id', $studentId)
            ->whereIn('subject_id', $subjectIds)
            ->select('subject_id', 'period_id', 'grade')
            ->get();

        /* Propósito: Indexar calificaciones por materia y periodo para acceso rápido en la vista. */
        $gradesMap = [];
        foreach ($grades as $g) {
            $sid = (int)$g->subject_id;
            $pid = (int)$g->period_id;

            if (!isset($gradesMap[$sid])) {
                $gradesMap[$sid] = [];
            }

            $gradesMap[$sid][$pid] = $g->grade !== null ? (float)$g->grade : null;
        }

        /* Propósito: Calcular promedio por materia con base en calificaciones capturadas. */
        $averages = [];
        foreach ($studentSubjects as $sub) {
            $sid = (int)$sub->id;

            $sum = 0.0;
            $count = 0;

            foreach ($periods as $p) {
                $pid = (int)$p->id;

                if (isset($gradesMap[$sid]) && array_key_exists($pid, $gradesMap[$sid])) {
                    $val = $gradesMap[$sid][$pid];
                    if ($val !== null) {
                        $sum += (float)$val;
                        $count++;
                    }
                }
            }

            $averages[$sid] = $count > 0 ? round($sum / $count, 2) : null;
        }

        $periodNumberById = [];
        foreach ($periods as $p) {
            $periodNumberById[(int)$p->id] = (int)$p->number;
        }

        $chartSeriesBySubject = [];
        foreach ($studentSubjects as $sub) {
            $sid = (int)$sub->id;
            $chartSeriesBySubject[$sid] = [];

            foreach ($periods as $p) {
                $pid = (int)$p->id;
                $x = $periodNumberById[$pid];

                $y = null;
                if (isset($gradesMap[$sid]) && array_key_exists($pid, $gradesMap[$sid])) {
                    $y = $gradesMap[$sid][$pid];
                }

                /* Propósito: Incluir solo puntos con calificación capturada. */
                if ($y !== null) {
                    $chartSeriesBySubject[$sid][] = [
                        'x' => $x,
                        'y' => (float)$y,
                    ];
                }
            }
        }

        return view('maestro.students.show', [
            'teacherName' => $sessionUser['name'] ?? 'Maestro',
            'student' => $student,
            'subjects' => $studentSubjects,
            'periods' => $periods,
            'gradesMap' => $gradesMap,
            'averages' => $averages,
            'chartSeriesBySubject' => $chartSeriesBySubject,
        ]);
    }
}
