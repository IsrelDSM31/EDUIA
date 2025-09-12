<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChangeLogExport;
use Illuminate\Support\Facades\Auth;

class ChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ChangeLog::with(['user']);

        // Filtros opcionales
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('student_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('model', function($subQ) use ($request) {
                    $subQ->where('student_id', $request->student_id);
                });
            });
        }

        if ($request->filled('subject_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('model', function($subQ) use ($request) {
                    $subQ->where('subject_id', $request->subject_id);
                });
            });
        }

        $changeLogs = $query->latest()->paginate(20);

        // Solo procesar si hay registros
        if ($changeLogs->count() > 0) {
            $changeLogs->getCollection()->transform(function ($log) {
                $log->formatted_changes = $this->formatChanges($log);
                $log->model_info = $this->getModelInfo($log);
                return $log;
            });
        }

        return Inertia::render('ChangeLog/Index', [
            'logs' => $changeLogs,
            'filters' => $request->only(['model_type', 'action', 'student_id', 'subject_id']),
            'students' => Student::select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get(),
            'subjects' => Subject::select('id', 'name')->get(),
        ]);
    }

    public function show($id)
    {
        $changeLog = ChangeLog::with(['user'])->findOrFail($id);
        $changeLog->formatted_changes = $this->formatChanges($changeLog);
        $changeLog->model_info = $this->getModelInfo($changeLog);

        return Inertia::render('ChangeLog/Show', [
            'log' => $changeLog,
        ]);
    }

    public function export(Request $request)
    {
        $query = ChangeLog::with(['user']);
        // Filtros opcionales (igual que en index)
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        $changeLogs = $query->latest()->get();
        return Excel::download(new ChangeLogExport($changeLogs), 'historial_cambios.xlsx');
    }

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        $changeLog = ChangeLog::findOrFail($id);
        $comments = $changeLog->comments ?? [];
        $comments[] = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'comment' => $request->comment,
            'created_at' => now()->toDateTimeString(),
        ];
        $changeLog->comments = $comments;
        $changeLog->save();
        return back()->with('success', 'Comentario agregado');
    }

    private function formatChanges($log)
    {
        $changes = $log->changes;
        $formatted = [];

        if ($log->action === 'update') {
            $before = $changes['before'] ?? [];
            $after = $changes['after'] ?? [];

            foreach ($after as $field => $newValue) {
                $oldValue = $before[$field] ?? null;
                if ($oldValue !== $newValue) {
                    $formatted[] = [
                        'field' => $this->getFieldLabel($field),
                        'old_value' => $this->formatValue($field, $oldValue),
                        'new_value' => $this->formatValue($field, $newValue),
                    ];
                }
            }
        } elseif ($log->action === 'delete') {
            $before = $changes['before'] ?? [];
            $formatted[] = [
                'field' => 'Registro completo',
                'old_value' => 'Eliminado',
                'new_value' => 'N/A',
            ];
        }

        return $formatted;
    }

    private function getModelInfo($log)
    {
        $modelClass = $log->model_type;
        
        if ($modelClass === 'App\Models\Grade') {
            $grade = \App\Models\Grade::with(['student', 'subject'])->find($log->model_id);
            if ($grade) {
                return [
                    'type' => 'Calificación',
                    'student_name' => $grade->student ? $grade->student->nombre . ' ' . $grade->student->apellido_paterno : 'N/A',
                    'subject_name' => $grade->subject ? $grade->subject->name : 'N/A',
                ];
            }
        } elseif ($modelClass === 'App\Models\Attendance') {
            $attendance = \App\Models\Attendance::with(['student', 'subject'])->find($log->model_id);
            if ($attendance) {
                return [
                    'type' => 'Asistencia',
                    'student_name' => $attendance->student ? $attendance->student->nombre . ' ' . $attendance->student->apellido_paterno : 'N/A',
                    'subject_name' => $attendance->subject ? $attendance->subject->name : 'N/A',
                ];
            }
        }

        return [
            'type' => 'Desconocido',
            'student_name' => 'N/A',
            'subject_name' => 'N/A',
        ];
    }

    private function getFieldLabel($field)
    {
        $labels = [
            'evaluations' => 'Evaluaciones',
            'promedio_final' => 'Promedio Final',
            'estado' => 'Estado',
            'puntos_faltantes' => 'Puntos Faltantes',
            'status' => 'Estado de Asistencia',
            'notes' => 'Notas',
            'justification_type' => 'Tipo de Justificación',
            'observations' => 'Observaciones',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    private function formatValue($field, $value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_null($value)) {
            return 'Sin valor';
        }

        return (string) $value;
    }
} 