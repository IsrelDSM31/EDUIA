<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Schedule/Index', [
            'schedules' => Schedule::with(['group', 'subject', 'teacher'])
                ->get()
                ->groupBy('day'),
            'groups' => Group::all(),
            'subjects' => Subject::all(),
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string',
        ]);

        // Verificar si hay conflictos de horario
        $conflicts = Schedule::where('day', $request->day)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })
            ->where(function ($query) use ($request) {
                $query->where('room', $request->room)
                    ->orWhere('teacher_id', $request->teacher_id)
                    ->orWhere('group_id', $request->group_id);
            })
            ->exists();

        if ($conflicts) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Existe un conflicto de horario.'], 409);
            }
            return redirect()->back()->withErrors(['message' => 'Existe un conflicto de horario.']);
        }

        $schedule = Schedule::create($request->all());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'schedule' => $schedule]);
        }
        return redirect()->back()->with('success', 'Horario creado correctamente.');
    }
}
