<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        return Inertia::render('AcademicPeriods/Index', [
            'academic_periods' => AcademicPeriod::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'type' => 'required|string',
            'important_events' => 'nullable|array',
            'important_events.*.date' => 'required|date',
            'important_events.*.description' => 'required|string',
            'evaluation_parameters' => 'required|array',
            'evaluation_parameters.*.name' => 'required|string',
            'evaluation_parameters.*.weight' => 'required|numeric|min:0|max:100',
            'evaluation_parameters.*.description' => 'required|string',
        ]);

        AcademicPeriod::create($request->all());

        return redirect()->back()->with('success', 'Periodo académico creado correctamente.');
    }
} 