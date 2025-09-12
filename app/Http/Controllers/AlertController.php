<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertController extends Controller
{
    public function index()
    {
        return Inertia::render('Alerts/Index', [
            'alerts' => Alert::with(['student'])
                ->latest()
                ->paginate(10),
            'students' => Student::with('group')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'urgency' => 'required|in:low,medium,high',
            'evidence' => 'nullable|array',
            'suggested_actions' => 'required|array',
            'intervention_plan' => 'required|array',
            'intervention_plan.objectives' => 'required|array',
            'intervention_plan.strategies' => 'required|array',
            'intervention_plan.responsible' => 'required|array',
            'intervention_plan.timeline' => 'required|array',
        ]);

        $alert = Alert::create($request->except('evidence'));

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('evidence');
                $alert->evidence = array_merge($alert->evidence ?? [], [$path]);
            }
            $alert->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'alert' => $alert]);
        }
        return redirect()->back()->with('success', 'Alerta creada correctamente.');
    }
} 