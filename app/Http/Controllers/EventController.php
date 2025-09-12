<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        return Inertia::render('Events/Index', [
            'events' => Event::latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'type' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'participants' => 'nullable|array',
        ]);

        $event = Event::create($request->all());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'event' => $event]);
        }
        return redirect()->back()->with('success', 'Evento creado correctamente.');
    }
} 