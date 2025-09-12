<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Alert;
use App\Models\Event;
use App\Models\Group;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications()->take(10)->get();
        $stats = [];

        // Datos comunes para todos los roles
        $stats['groups'] = Group::all();
        $stats['subjects'] = Subject::all();
        $stats['teachers'] = Teacher::all();
        $stats['upcoming_events'] = Event::where('date', '>=', now())
            ->orderBy('date')
            ->take(5)
            ->get();
        $stats['schedules'] = Schedule::with(['group', 'subject', 'teacher'])->get()->map(function($schedule) {
            return [
                'id' => $schedule->id,
                'group_name' => $schedule->group ? $schedule->group->name : '',
                'subject_name' => $schedule->subject ? $schedule->subject->name : '',
                'teacher_name' => $schedule->teacher ? ($schedule->teacher->user->name ?? '') : '',
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'room' => $schedule->room,
            ];
        });
        $stats['attendances'] = Attendance::with(['student', 'subject'])->get()->map(function($attendance) {
            return [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'subject_id' => $attendance->subject_id,
                'date' => $attendance->date,
                'status' => $attendance->status,
                'justification_type' => $attendance->justification_type ?? null,
                'justification_document' => $attendance->justification_document ?? null,
                'observations' => $attendance->observations ?? null,
                'student_name' => $attendance->student->nombre . ' ' . $attendance->student->apellido_paterno,
                'subject_name' => $attendance->subject->name,
            ];
        });
        $stats['grades'] = Grade::with(['student', 'subject'])->get()->map(function($grade) {
            return [
                'id' => $grade->id,
                'student_name' => $grade->student ? $grade->student->nombre . ' ' . $grade->student->apellido_paterno : '',
                'student_matricula' => $grade->student ? $grade->student->matricula : '',
                'subject_name' => $grade->subject ? $grade->subject->name : '',
                'evaluation_type' => $grade->evaluation_type,
                'score' => $grade->score,
                'promedio_final' => $grade->promedio_final,
                'estado' => $grade->estado,
                'puntos_faltantes' => $grade->puntos_faltantes,
                'feedback' => $grade->feedback,
                'evaluation_date' => $grade->evaluation_date,
                'period' => $grade->period,
                'opportunity' => $grade->opportunity,
                'competencies' => $grade->competencies,
                'observations' => $grade->observations,
            ];
        });
        $stats['alerts'] = Alert::with(['student'])->get()->map(function($alert) {
            return [
                'id' => $alert->id,
                'student_name' => $alert->student ? $alert->student->user->name : '',
                'type' => $alert->type,
                'title' => $alert->title,
                'description' => $alert->description,
                'urgency' => $alert->urgency,
                'status' => $alert->status,
            ];
        });
        $stats['events'] = Event::all()->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'date' => $event->date,
                'type' => $event->type,
                'priority' => $event->priority,
            ];
        });
        // Rúbricas
        $stats['rubrics'] = \App\Models\Rubric::all();

        // Lista de alumnos siempre disponible
        $stats['students'] = \App\Models\Student::with('group')->orderBy('apellido_paterno')->get()->map(function($student) {
            return [
                'id' => $student->id,
                'matricula' => $student->matricula,
                'nombre' => $student->nombre,
                'apellido_paterno' => $student->apellido_paterno,
                'apellido_materno' => $student->apellido_materno,
                'group_id' => $student->group_id,
                'group_name' => $student->group ? $student->group->name : '',
                'birth_date' => $student->birth_date,
            ];
        });

        // Estructura agrupada de calificaciones por alumno y materia
        $students = \App\Models\Student::with(['grades.subject'])->get();
        $stats['studentsGrades'] = $students->map(function ($student) {
            $gradesBySubject = $student->grades->groupBy('subject_id')->map(function ($grades) {
                return $grades->map(function ($grade) {
                    return [
                        'id' => $grade->id,
                        'subject_id' => $grade->subject_id,
                        'subject_name' => $grade->subject->name ?? '',
                        'evaluation_type' => $grade->evaluation_type,
                        'evaluation_date' => $grade->date,
                        'score' => $grade->score,
                        'feedback' => $grade->feedback,
                        'period' => $grade->period,
                        'opportunity' => $grade->opportunity,
                        'competencies' => $grade->competencies,
                        'observations' => $grade->observations,
                    ];
                });
            });
            return [
                'id' => $student->id,
                'matricula' => $student->matricula,
                'nombre' => $student->nombre,
                'apellido_paterno' => $student->apellido_paterno,
                'apellido_materno' => $student->apellido_materno,
                'grades_by_subject' => $gradesBySubject,
            ];
        });

        if ($user->role === 'admin') {
            $stats = array_merge($stats, [
                'total_students' => Student::count(),
                'total_teachers' => Teacher::count(),
                'total_subjects' => Subject::count(),
                'recent_alerts' => Alert::latest()->take(5)->get(),
                'attendance_summary' => Attendance::where('date', now()->format('Y-m-d'))
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->get(),
            ]);
        } elseif ($user->role === 'teacher') {
            $teacher = $user->teacher;
            $stats = array_merge($stats, [
                'my_subjects' => $teacher->schedules()->with('subject')->get(),
                'today_classes' => $teacher->schedules()
                    ->where('day', now()->format('l'))
                    ->with(['subject', 'group'])
                    ->get(),
                'recent_grades' => Grade::whereHas('subject', function ($query) use ($teacher) {
                    $query->whereIn('id', $teacher->schedules()->pluck('subject_id'));
                })->with(['student', 'subject'])->latest()->take(5)->get(),
                'students' => Student::whereHas('group', function ($query) use ($teacher) {
                    $query->whereIn('id', $teacher->schedules()->pluck('group_id'));
                })->with(['group', 'attendance'])->get(),
            ]);
        } elseif ($user->role === 'student') {
            $student = $user->student;
            $stats = array_merge($stats, [
                'my_attendance' => $student->attendances()
                    ->where('date', '>=', now()->subDays(30))
                    ->get(),
                'my_grades' => $student->grades()->with('subject')->latest()->get(),
                'my_alerts' => $student->alerts()->latest()->get(),
                'my_schedule' => Schedule::whereHas('group', function ($query) use ($student) {
                    $query->where('id', $student->group_id);
                })->with(['subject', 'teacher'])->get(),
            ]);
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
} 