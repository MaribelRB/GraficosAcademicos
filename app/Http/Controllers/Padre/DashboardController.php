<?php

namespace App\Http\Controllers\Padre;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    /* Propósito: Decide flujo del padre: selector si tiene varios hijos o redirección si solo tiene uno. */
    public function dashboard(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $parentId = (int)($sessionUser['id'] ?? 0);

        /* Propósito: Obtener alumnos asignados al padre. */
        $students = DB::table('users')
            ->join('parent_student', 'users.id', '=', 'parent_student.student_id')
            ->where('parent_student.parent_id', $parentId)
            ->where('users.role', 'alumnado')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get();

        if ($students->count() === 0) {
            return response()->view('padre.no-students', [], 200);
        }

        /* Propósito: Si ya hay alumno seleccionado en sesión y pertenece al padre, ir al dashboard del alumno. */
        $selectedStudentId = (int)$request->session()->get('padre.selected_student_id', 0);
        if ($selectedStudentId > 0) {
            $allowed = $students->firstWhere('id', $selectedStudentId);
            if ($allowed) {
                return redirect()->route('alumnado.dashboard', ['student_id' => $selectedStudentId]);
            }
        }

        /* Propósito: Si solo tiene un alumno, seleccionarlo automáticamente. */
        if ($students->count() === 1) {
            $only = $students->first();
            $request->session()->put('padre.selected_student_id', (int)$only->id);

            return redirect()->route('alumnado.dashboard', ['student_id' => (int)$only->id]);
        }

        /* Propósito: Si tiene varios, mostrar selector. */
        return view('padre.select-student', [
            'parentName' => $sessionUser['name'] ?? 'Padre',
            'students' => $students,
        ]);
    }

    /* Propósito: Muestra el selector de alumno (GET). */
    public function showSelectStudent(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $parentId = (int)($sessionUser['id'] ?? 0);

        /* Propósito: Obtener alumnos asignados al padre. */
        $students = DB::table('users')
            ->join('parent_student', 'users.id', '=', 'parent_student.student_id')
            ->where('parent_student.parent_id', $parentId)
            ->where('users.role', 'alumnado')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get();

        if ($students->count() === 0) {
            return response()->view('padre.no-students', [], 200);
        }

        return view('padre.select-student', [
            'parentName' => $sessionUser['name'] ?? 'Padre',
            'students' => $students,
        ]);
    }

    /* Propósito: Guarda en sesión el alumno seleccionado por el padre y redirige al dashboard del alumno (POST). */
    public function selectStudent(Request $request)
    {
        $sessionUser = $request->session()->get('auth.user');
        $parentId = (int)($sessionUser['id'] ?? 0);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'min:1'],
        ]);

        $studentId = (int)$data['student_id'];

        /* Propósito: Validar que el alumno pertenezca al padre. */
        $exists = DB::table('parent_student')
            ->where('parent_id', $parentId)
            ->where('student_id', $studentId)
            ->exists();

        if (!$exists) {
            return back()->withErrors(['student_id' => 'Alumno no permitido.']);
        }

        $request->session()->put('padre.selected_student_id', $studentId);

        return redirect()->route('alumnado.dashboard', ['student_id' => $studentId]);
    }
}
