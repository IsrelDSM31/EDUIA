<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Imports\AttendanceImport;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        return Inertia::render('Attendance/Index', [
            'attendances' => Attendance::with(['student', 'subject'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        // Log the request for debugging
        \Log::info('Attendance store request', [
            'user_id' => Auth::id(),
            'has_csrf_token' => $request->hasHeader('X-CSRF-TOKEN'),
            'session_id' => $request->session()->getId(),
            'request_data' => $request->all()
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::create($validated);
        // Registro en bitácora para creación
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Attendance::class,
            'model_id' => $attendance->id,
            'action' => 'create',
            'changes' => [
                'after' => $attendance->toArray(),
            ],
        ]);

        // Recargar con relaciones y agregar los campos necesarios para el frontend
        $attendance = Attendance::with(['student', 'subject'])->find($attendance->id);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'attendance' => array_merge(
                    $attendance->toArray(),
                    [
                        'student_name' => $attendance->student->nombre . ' ' . $attendance->student->apellido_paterno,
                        'subject_name' => $attendance->subject->name,
                    ]
                )
            ]);
        }
        return redirect()->back()->with('success', 'Asistencia registrada correctamente.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string'
        ]);

        $oldData = $attendance->toArray();
        $attendance->update($validated);
        // Registro en bitácora
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Attendance::class,
            'model_id' => $attendance->id,
            'action' => 'update',
            'changes' => [
                'before' => $oldData,
                'after' => $attendance->fresh()->toArray(),
            ],
        ]);
        return redirect()->back()->with('success', 'Asistencia actualizada correctamente.');
    }

    public function destroy(Attendance $attendance)
    {
        $oldData = $attendance->toArray();
        $attendance->delete();
        // Registro en bitácora
        ChangeLog::create([
            'user_id' => Auth::id(),
            'model_type' => Attendance::class,
            'model_id' => $attendance->id,
            'action' => 'delete',
            'changes' => [
                'before' => $oldData,
                'after' => null,
            ],
        ]);
        return redirect()->back()->with('success', 'Registro de asistencia eliminado correctamente.');
    }

    public function justify(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'justification_type' => 'required|string',
            'observaciones' => 'nullable|string',
            // 'file' => 'nullable|file|mimes:pdf,jpg,png,doc,docx'
        ]);

        // Buscar la última inasistencia sin justificar
        $attendance = Attendance::where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('status', 'absent')
            ->whereNull('justification_type')
            ->orderBy('date', 'desc')
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'No hay inasistencias para justificar.'], 404);
        }

        $attendance->justification_type = $validated['justification_type'];
        $attendance->observations = $validated['observaciones'] ?? null;

        // Si hay archivo, guárdalo (opcional)
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('justifications', 'public');
            $attendance->justification_document = $path;
        }

        $attendance->save();

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    public function export()
    {
        return Excel::download(new AttendanceExport, 'asistencias.xlsx', \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="asistencias.xlsx"'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new AttendanceImport, $request->file('file'));
        return back()->with('success', 'Importación de asistencias completada');
    }
} 