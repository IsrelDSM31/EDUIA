<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index()
    {
        return Inertia::render('Teachers/Index', [
            'teachers' => Teacher::with(['user', 'schedules.subject'])
                ->latest()
                ->paginate(10),
            'subjects' => Subject::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'professional_license' => 'required|string|unique:teachers,professional_license',
            'specialization' => 'required|string',
            'education_level' => 'required|string',
            'experience_years' => 'required|integer',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
            'availability' => 'required|array',
            'availability.*.day' => 'required|string',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('password'), // Temporal, se debe cambiar en el primer inicio de sesión
            'role' => 'teacher',
        ]);

        $teacher = Teacher::create(array_merge(
            $request->except(['name', 'email', 'subjects']),
            ['user_id' => $user->id]
        ));

        $teacher->subjects()->attach($request->subjects);

        return redirect()->back()->with('success', 'Docente creado correctamente.');
    }

    public function show(Teacher $teacher)
    {
        return Inertia::render('Teachers/Show', [
            'teacher' => $teacher->load(['user', 'subjects', 'schedules.group']),
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'professional_license' => 'required|string|unique:teachers,professional_license,' . $teacher->id,
            'specialization' => 'required|string',
            'education_level' => 'required|string',
            'experience_years' => 'required|integer',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
            'availability' => 'required|array',
            'availability.*.day' => 'required|string',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
        ]);

        $teacher->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $teacher->update($request->except(['name', 'email', 'subjects']));
        $teacher->subjects()->sync($request->subjects);

        return redirect()->back()->with('success', 'Docente actualizado correctamente.');
    }
} 