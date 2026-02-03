<?php

namespace App\Http\Controllers\Maestro;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function overview(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $teacherId = (int)($sessionUser['id'] ?? 0);

        /* Propósito: Obtener materias asignadas al maestro. */
        $subjects = DB::table('subjects')
            ->join('teacher_subject', 'subjects.id', '=', 'teacher_subject.subject_id')
            ->where('teacher_subject.teacher_id', $teacherId)
            ->select('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        $subjectIds = [];
        foreach ($subjects as $s) {
            $subjectIds[] = (int)$s->id;
        }

        /* Propósito: Obtener promedios por materia (promedio de todas las calificaciones capturadas de alumnos en esa materia). */
        $avgRows = collect();
        if (count($subjectIds) > 0) {
            $avgRows = DB::table('grades')
                ->whereIn('subject_id', $subjectIds)
                ->whereNotNull('grade')
                ->select(
                    'subject_id',
                    DB::raw('AVG(grade) as avg_grade'),
                    DB::raw('COUNT(grade) as grades_count'),
                    DB::raw('COUNT(DISTINCT student_id) as students_count')
                )
                ->groupBy('subject_id')
                ->get();
        }

        $avgBySubject = [];
        $gradesCountBySubject = [];
        $studentsCountBySubject = [];
        foreach ($avgRows as $r) {
            $sid = (int)$r->subject_id;
            $avgBySubject[$sid] = round((float)$r->avg_grade, 2);
            $gradesCountBySubject[$sid] = (int)$r->grades_count;
            $studentsCountBySubject[$sid] = (int)$r->students_count;
        }

        /* Propósito: Preparar tarjetas para la vista (una por materia). */
        $cards = [];
        foreach ($subjects as $s) {
            $sid = (int)$s->id;

            $cards[] = [
                'id' => $sid,
                'name' => (string)$s->name,
                'average' => array_key_exists($sid, $avgBySubject) ? $avgBySubject[$sid] : null,
                'students_count' => array_key_exists($sid, $studentsCountBySubject) ? $studentsCountBySubject[$sid] : 0,
                'grades_count' => array_key_exists($sid, $gradesCountBySubject) ? $gradesCountBySubject[$sid] : 0,
            ];
        }

        return view('maestro.overview', [
            'teacherName' => $sessionUser['name'] ?? 'Maestro',
            'cards' => $cards,
        ]);
    }
    /* Propósito: Renderiza el dashboard del maestro con filtro por materia y tabla de alumnos usando MySQL. */
    public function index(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $teacherId = (int)($sessionUser['id'] ?? 0);

        /* Propósito: Obtener materias asignadas al maestro para el filtro. */
        $subjects = DB::table('subjects')
            ->join('teacher_subject', 'subjects.id', '=', 'teacher_subject.subject_id')
            ->where('teacher_subject.teacher_id', $teacherId)
            ->select('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        /* Propósito: Normalizar materias en arreglo simple para la vista. */
        $filterSubjects = [];
        $allowedSubjectIds = [];
        foreach ($subjects as $s) {
            $filterSubjects[] = [
                'id' => (int)$s->id,
                'name' => (string)$s->name,
            ];
            $allowedSubjectIds[] = (int)$s->id;
        }

        /* Propósito: Determinar materia seleccionada y validar que pertenezca al maestro. */
        $selectedSubjectId = (int)$request->query('subject_id', 0);
        if ($selectedSubjectId !== 0 && !in_array($selectedSubjectId, $allowedSubjectIds, true)) {
            $selectedSubjectId = 0;
        }

        /* Propósito: Obtener nombre de materia seleccionada para encabezado. */
        $selectedSubjectName = '';
        if ($selectedSubjectId !== 0) {
            foreach ($filterSubjects as $fs) {
                if ((int)$fs['id'] === $selectedSubjectId) {
                    $selectedSubjectName = (string)$fs['name'];
                    break;
                }
            }
        }

        /* Propósito: Cargar los 6 periodos para construir columnas. */
        $periods = DB::table('periods')
            ->select('id', 'number', 'name')
            ->orderBy('number')
            ->get();

        /* Propósito: Consultar alumnos y calificaciones por periodo en la materia seleccionada. */
        $students = [];
        if ($selectedSubjectId !== 0) {

            /* Propósito: Obtener alumnos inscritos en la materia. */
            $studentRows = DB::table('users')
                ->join('student_subject', 'users.id', '=', 'student_subject.student_id')
                ->where('student_subject.subject_id', $selectedSubjectId)
                ->where('users.role', 'alumnado')
                ->select('users.id', 'users.name', 'users.email')
                ->orderBy('users.name')
                ->get();

            $studentIds = [];
            foreach ($studentRows as $sr) {
                $studentIds[] = (int)$sr->id;
            }

            /* Propósito: Obtener calificaciones de todos los alumnos de una sola vez para esta materia. */
            $gradeRows = DB::table('grades')
                ->where('subject_id', $selectedSubjectId)
                ->whereIn('student_id', $studentIds)
                ->select('student_id', 'period_id', 'grade')
                ->get();

            /* Propósito: Indexar calificaciones por alumno y periodo. */
            $gradesByStudent = [];
            foreach ($gradeRows as $gr) {
                $sid = (int)$gr->student_id;
                $pid = (int)$gr->period_id;

                if (!isset($gradesByStudent[$sid])) {
                    $gradesByStudent[$sid] = [];
                }

                $gradesByStudent[$sid][$pid] = $gr->grade !== null ? (float)$gr->grade : null;
            }

            /* Propósito: Construir filas para la vista con calificaciones y promedio. */
            foreach ($studentRows as $sr) {
                $sid = (int)$sr->id;

                $grades = [];
                $sum = 0.0;
                $count = 0;

                foreach ($periods as $p) {
                    $pid = (int)$p->id;

                    $val = null;
                    if (isset($gradesByStudent[$sid]) && array_key_exists($pid, $gradesByStudent[$sid])) {
                        $val = $gradesByStudent[$sid][$pid];
                    }

                    $grades[$pid] = $val;

                    if ($val !== null) {
                        $sum += (float)$val;
                        $count++;
                    }
                }

                $students[] = [
                    'id' => $sid,
                    'name' => (string)$sr->name,
                    'email' => (string)$sr->email,
                    'grades' => $grades,
                    'average' => $count > 0 ? round($sum / $count, 2) : null,
                    'graded_periods' => $count,
                ];
            }
        }

        $groupSeries = [];

        if ($selectedSubjectId !== 0) {
            $periodIdToNumber = [];
            foreach ($periods as $p) {
                $periodIdToNumber[(int)$p->id] = (int)$p->number;
            }

            $avgRows = DB::table('grades')
                ->where('subject_id', $selectedSubjectId)
                ->whereNotNull('grade')
                ->select('period_id', DB::raw('AVG(grade) as avg_grade'))
                ->groupBy('period_id')
                ->get();

            $avgByPeriodId = [];
            foreach ($avgRows as $r) {
                $avgByPeriodId[(int)$r->period_id] = (float)$r->avg_grade;
            }

            foreach ($periods as $p) {
                $pid = (int)$p->id;
                $x = (int)$p->number;

                if (array_key_exists($pid, $avgByPeriodId)) {
                    $groupSeries[] = [
                        'x' => $x,
                        'y' => round($avgByPeriodId[$pid], 2),
                    ];
                }
            }
        }

        return view('maestro.dashboard', [
            'teacherName' => $sessionUser['name'] ?? 'Maestro',
            'subjects' => $filterSubjects,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedSubjectName' => $selectedSubjectName,
            'periods' => $periods,
            'students' => $students,
            'groupSeries' => $groupSeries,
        ]);
    }
}
