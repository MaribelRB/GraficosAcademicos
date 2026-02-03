<?php

namespace App\Http\Controllers\Alumnado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    /* Propósito: Renderiza el dashboard del alumno; si el rol es padre, permite cambiar entre alumnos relacionados. */
    public function index(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $viewerRole = (string)($sessionUser['role'] ?? '');
        $viewerId = (int)($sessionUser['id'] ?? 0);

        /* Propósito: Resolver el alumno objetivo dependiendo del rol. */
        $studentId = $this->resolveStudentId($request, $viewerRole, $viewerId);

        /* Propósito: Cargar información del alumno objetivo. */
        $studentUser = DB::table('users')
            ->where('id', $studentId)
            ->where('role', 'alumnado')
            ->select('id', 'name', 'email')
            ->first();

        if (!$studentUser) {
            abort(404);
        }

        /* Propósito: Si el visor es padre, obtener lista de alumnos relacionados para el selector. */
        $parentStudents = [];
        if ($viewerRole === 'padre') {
            $rows = DB::table('users')
                ->join('parent_student', 'users.id', '=', 'parent_student.student_id')
                ->where('parent_student.parent_id', $viewerId)
                ->where('users.role', 'alumnado')
                ->select('users.id', 'users.name', 'users.email')
                ->orderBy('users.name')
                ->get();

            foreach ($rows as $r) {
                $parentStudents[] = [
                    'id' => (int)$r->id,
                    'name' => (string)$r->name,
                    'email' => (string)$r->email,
                ];
            }
        }

        /* Propósito: Obtener periodos (se espera 1..6). */
        $periods = DB::table('periods')
            ->select('id', 'number', 'name')
            ->orderBy('number')
            ->get();

        /* Propósito: Mapear id de periodo a número para uso en frontend. */
        $periodNumbers = [];
        foreach ($periods as $p) {
            $periodNumbers[(int)$p->id] = (int)$p->number;
        }

        /* Propósito: Obtener materias inscritas del alumno. */
        $subjects = DB::table('subjects')
            ->join('student_subject', 'subjects.id', '=', 'student_subject.subject_id')
            ->where('student_subject.student_id', $studentId)
            ->select('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        $subjectIds = [];
        foreach ($subjects as $s) {
            $subjectIds[] = (int)$s->id;
        }

        /* Propósito: Obtener todas las calificaciones del alumno para sus materias y periodos. */
        $gradeRows = collect();
        if (count($subjectIds) > 0) {
            $gradeRows = DB::table('grades')
                ->where('student_id', $studentId)
                ->whereIn('subject_id', $subjectIds)
                ->select('subject_id', 'period_id', 'grade')
                ->get();
        }

        /* Propósito: Indexar calificaciones por materia y periodo. */
        $gradesMap = [];
        foreach ($gradeRows as $gr) {
            $sid = (int)$gr->subject_id;
            $pid = (int)$gr->period_id;

            if (!isset($gradesMap[$sid])) {
                $gradesMap[$sid] = [];
            }

            $gradesMap[$sid][$pid] = $gr->grade !== null ? (float)$gr->grade : null;
        }

        /* Propósito: Promedios por materia y conteo de calificaciones capturadas usando SQL. */
        $avgRows = collect();
        if (count($subjectIds) > 0) {
            $avgRows = DB::table('grades')
                ->where('student_id', $studentId)
                ->whereIn('subject_id', $subjectIds)
                ->whereNotNull('grade')
                ->select('subject_id', DB::raw('AVG(grade) as avg_grade'), DB::raw('COUNT(grade) as graded_count'))
                ->groupBy('subject_id')
                ->get();
        }

        $averages = [];
        $gradedCountBySubject = [];
        foreach ($avgRows as $r) {
            $averages[(int)$r->subject_id] = round((float)$r->avg_grade, 2);
            $gradedCountBySubject[(int)$r->subject_id] = (int)$r->graded_count;
        }

        /* Propósito: Promedio general del alumno. */
        $generalAverage = null;
        if (count($subjectIds) > 0) {
            $generalRow = DB::table('grades')
                ->where('student_id', $studentId)
                ->whereIn('subject_id', $subjectIds)
                ->whereNotNull('grade')
                ->select(DB::raw('AVG(grade) as avg_grade'))
                ->first();

            if ($generalRow && $generalRow->avg_grade !== null) {
                $generalAverage = round((float)$generalRow->avg_grade, 2);
            }
        }

        /* Propósito: Preparar tarjetas para pasteles. */
        $subjectCards = [];
        foreach ($subjects as $s) {
            $sid = (int)$s->id;

            $subjectCards[] = [
                'id' => $sid,
                'name' => (string)$s->name,
                'average' => array_key_exists($sid, $averages) ? $averages[$sid] : null,
                'graded_count' => array_key_exists($sid, $gradedCountBySubject) ? $gradedCountBySubject[$sid] : 0,
            ];
        }

        return view('alumnado.dashboard', [
            'viewerRole' => $viewerRole,

            /* Propósito: Datos del alumno objetivo mostrado. */
            'studentName' => (string)$studentUser->name,
            'studentEmail' => (string)$studentUser->email,
            'selectedStudentId' => (int)$studentUser->id,

            /* Propósito: Lista de alumnos del padre para selector. */
            'parentStudents' => $parentStudents,

            'generalAverage' => $generalAverage,
            'subjectCards' => $subjectCards,
            'periods' => $periods,
            'periodNumbers' => $periodNumbers,
            'gradesMap' => $gradesMap,
            'averages' => $averages,
        ]);
    }

    /* Propósito: Resuelve el id del alumno según rol (alumno: propio; padre: alumno relacionado y seleccionado). */
    private function resolveStudentId(Request $request, string $viewerRole, int $viewerId): int
    {
        if ($viewerRole !== 'padre') {
            return $viewerId;
        }

        $requestedStudentId = (int)$request->query('student_id', 0);

        /* Propósito: Si se solicita un alumno, validar relación padre->alumno. */
        if ($requestedStudentId > 0) {
            $allowed = DB::table('parent_student')
                ->where('parent_id', $viewerId)
                ->where('student_id', $requestedStudentId)
                ->exists();

            if (!$allowed) {
                abort(403);
            }

            $request->session()->put('padre.selected_student_id', $requestedStudentId);
            return $requestedStudentId;
        }

        /* Propósito: Si no hay query, usar el alumno seleccionado previamente en sesión. */
        $selected = (int)$request->session()->get('padre.selected_student_id', 0);
        if ($selected > 0) {
            $allowed = DB::table('parent_student')
                ->where('parent_id', $viewerId)
                ->where('student_id', $selected)
                ->exists();

            if ($allowed) {
                return $selected;
            }
        }

        /* Propósito: Si no hay seleccionado, usar el primer alumno relacionado. */
        $first = DB::table('parent_student')
            ->where('parent_id', $viewerId)
            ->orderBy('student_id')
            ->first();

        if (!$first) {
            abort(403);
        }

        $request->session()->put('padre.selected_student_id', (int)$first->student_id);
        return (int)$first->student_id;
    }
}
