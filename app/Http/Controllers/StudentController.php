<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['group', 'user'])
            ->latest()
            ->get(); // Cambiar a get() en lugar de paginate() para mostrar todos
        
        return Inertia::render('Students/Index', [
            'students' => $students,
            'groups' => Group::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string|unique:students,matricula',
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'nullable|string',
            'group_id' => 'required|exists:groups,id',
            'birth_date' => 'required|date',
        ]);

        // Generar email único
        $baseEmail = strtolower(Str::slug($request->nombre . $request->apellido_paterno . $request->apellido_materno, '.')) . '@alumno.com';
        $email = $baseEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = strtolower(Str::slug($request->nombre . $request->apellido_paterno . $request->apellido_materno, '.')) . $counter . '@alumno.com';
            $counter++;
        }
        // Crear usuario básico automáticamente
        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido_paterno . ' ' . $request->apellido_materno,
            'email' => $email,
            'password' => bcrypt('password'), // Contraseña por defecto
            'role' => 'student',
        ]);

        $student = \App\Models\Student::create(array_merge($request->all(), [
            'user_id' => $user->id,
            'emergency_contact' => json_encode([
                'name' => '',
                'phone' => '',
                'relationship' => ''
            ]),
            'parent_data' => json_encode([]),
        ]));

        // Registro en bitácora para creación
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Student::class,
            'model_id' => $student->id,
            'action' => 'create',
            'changes' => [
                'after' => $student->toArray(),
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'student' => $student->load(['group', 'user'])]);
        }
        // Redirigir a la página de estudiantes para mostrar el nuevo registro
        return redirect()->route('students.index')->with('success', 'Estudiante creado correctamente.');
    }

    public function show(Student $student)
    {
        return Inertia::render('Students/Show', [
            'student' => $student->load(['group', 'user', 'attendances', 'grades.subject']),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'nullable|string',
            'group_id' => 'required|exists:groups,id',
            'curp' => 'nullable|string|unique:students,curp,' . $student->id,
            'birth_date' => 'required|date',
            'blood_type' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'nullable|string',
            'emergency_contact.phone' => 'nullable|string',
            'emergency_contact.relationship' => 'nullable|string',
            'parent_data' => 'nullable|array',
        ]);

        // Actualizar datos del estudiante
        $studentData = $request->except(['name', 'email', 'emergency_contact', 'parent_data']);
        
        // Convertir emergency_contact y parent_data a JSON si existen
        if ($request->has('emergency_contact')) {
            $studentData['emergency_contact'] = json_encode($request->emergency_contact);
        }
        if ($request->has('parent_data')) {
            $studentData['parent_data'] = json_encode($request->parent_data);
        }
        
        $oldData = $student->toArray();
        $student->update($studentData);
        
        // Actualizar usuario si existe - construir nombre completo desde los campos del estudiante
        if ($student->user) {
            $userData = [
                'name' => trim($request->nombre . ' ' . $request->apellido_paterno . ' ' . ($request->apellido_materno ?? '')),
            ];
            $student->user->update($userData);
        }

        // Registro en bitácora para actualización
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Student::class,
            'model_id' => $student->id,
            'action' => 'update',
            'changes' => [
                'before' => $oldData,
                'after' => $student->fresh()->toArray(),
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'student' => $student->load(['group', 'user'])]);
        }
        
        return redirect()->back()->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy($id)
    {
        $student = \App\Models\Student::findOrFail($id);
        // Eliminar todas las calificaciones del alumno
        $student->grades()->delete();
        // Eliminar el usuario relacionado si existe
        if ($student->user) {
            $student->user->delete();
        }
        // Eliminar el alumno
        $student->delete();
        return response()->json(['success' => true]);
    }

    public function export()
    {
        return Excel::download(new StudentsExport, 'alumnos.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        Excel::import(new StudentsImport, $request->file('file'));
        return back()->with('success', 'Importación completada');
    }
} 