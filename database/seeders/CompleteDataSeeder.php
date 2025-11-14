<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CompleteDataSeeder extends Seeder
{
    /**
     * Seed datos completos para que el sistema funcione correctamente
     */
    public function run(): void
    {
        // 1. Crear período académico actual
        echo "📅 Creando período académico...\n";
        try {
            $period = AcademicPeriod::firstOrCreate(
                [
                    'name' => 'Ciclo Escolar ' . date('Y') . '-' . (date('Y') + 1),
                ],
                [
                    'start_date' => date('Y') . '-08-01',
                    'end_date' => (date('Y') + 1) . '-07-31',
                    'type' => 'Anual',
                    'important_events' => json_encode([]),
                    'evaluation_parameters' => json_encode([
                        'min_grade' => 6.0,
                        'max_grade' => 10.0,
                        'passing_grade' => 6.0
                    ]),
                ]
            );
            echo "   ✓ Período académico creado\n\n";
        } catch (\Exception $e) {
            // Intentar con estructura alternativa del modelo
            try {
                $period = AcademicPeriod::firstOrCreate(
                    [
                        'nombre' => 'Ciclo Escolar ' . date('Y') . '-' . (date('Y') + 1),
                    ],
                    [
                        'fecha_inicio' => date('Y') . '-08-01',
                        'fecha_fin' => (date('Y') + 1) . '-07-31',
                        'estado' => 'activo',
                    ]
                );
                echo "   ✓ Período académico creado\n\n";
            } catch (\Exception $e2) {
                echo "   ⚠️  No se pudo crear período académico: " . $e2->getMessage() . "\n\n";
            }
        }

        // 2. Asignar materias a profesores
        echo "👨‍🏫 Asignando materias a profesores...\n";
        $teachers = Teacher::all();
        $subjects = Subject::all();

        if ($teachers->count() > 0 && $subjects->count() > 0) {
            foreach ($teachers as $teacher) {
                // Asignar todas las materias al profesor
                foreach ($subjects as $subject) {
                    if (!$teacher->subjects()->where('subject_id', $subject->id)->exists()) {
                        $teacher->subjects()->attach($subject->id);
                    }
                }
            }
            echo "   ✓ Materias asignadas a profesores\n\n";
        }

        // 3. Crear horarios (schedules) para conectar grupos, materias y profesores
        echo "📋 Creando horarios...\n";
        $groups = Group::take(5)->get(); // Usar los primeros 5 grupos
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $timeSlots = [
            ['08:00', '09:30'],
            ['09:30', '11:00'],
            ['11:00', '12:30'],
            ['12:30', '14:00'],
        ];

        $scheduleCount = 0;
        foreach ($groups as $groupIndex => $group) {
            foreach ($subjects as $subjectIndex => $subject) {
                $teacher = $teachers->first();
                if (!$teacher) continue;

                $dayIndex = ($groupIndex + $subjectIndex) % count($days);
                $timeIndex = $subjectIndex % count($timeSlots);
                $timeSlot = $timeSlots[$timeIndex];

                Schedule::firstOrCreate(
                    [
                        'group_id' => $group->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'day' => $days[$dayIndex],
                    ],
                    [
                        'start_time' => $timeSlot[0],
                        'end_time' => $timeSlot[1],
                        'room' => 'Aula ' . ($groupIndex + 1),
                    ]
                );
                $scheduleCount++;
            }
        }
        echo "   ✓ Horarios creados: $scheduleCount\n\n";

        // 4. Crear más estudiantes para que el sistema tenga datos suficientes
        echo "👨‍🎓 Creando estudiantes adicionales...\n";
        $studentNames = [
            ['Carlos', 'Rodríguez', 'Martínez'],
            ['Ana', 'Hernández', 'García'],
            ['Luis', 'González', 'Pérez'],
            ['Laura', 'Sánchez', 'López'],
            ['Miguel', 'Ramírez', 'Torres'],
            ['Patricia', 'Flores', 'Morales'],
            ['Roberto', 'Castro', 'Jiménez'],
            ['Sofía', 'Ruiz', 'Vargas'],
            ['Diego', 'Mendoza', 'Ortega'],
            ['Carmen', 'Delgado', 'Ramos'],
            ['Fernando', 'Silva', 'Cruz'],
            ['Gabriela', 'Mora', 'Guzmán'],
            ['Javier', 'Vega', 'Reyes'],
            ['Isabel', 'Cortés', 'Moreno'],
            ['Ricardo', 'Navarro', 'Aguilar'],
            ['Elena', 'Peña', 'Medina'],
            ['Óscar', 'Rivas', 'Campos'],
            ['Adriana', 'Soto', 'Guerrero'],
            ['Andrés', 'Contreras', 'Palacios'],
            ['Daniela', 'Villa', 'Espinoza'],
        ];

        $groups = Group::all();
        $createdStudents = 0;

        foreach ($studentNames as $index => $nameData) {
            $group = $groups[$index % $groups->count()];
            
            $studentUser = User::firstOrCreate(
                ['email' => strtolower($nameData[0]) . '.' . strtolower($nameData[1]) . '@eduai.com'],
                [
                    'name' => $nameData[0] . ' ' . $nameData[1] . ' ' . $nameData[2],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                ]
            );

            if (!Student::where('user_id', $studentUser->id)->exists()) {
                Student::create([
                    'user_id' => $studentUser->id,
                    'matricula' => '2024' . str_pad($index + 2, 3, '0', STR_PAD_LEFT),
                    'nombre' => $nameData[0],
                    'apellido_paterno' => $nameData[1],
                    'apellido_materno' => $nameData[2],
                    'birth_date' => now()->subYears(17)->subMonths(rand(0, 11))->subDays(rand(0, 30)),
                    'blood_type' => ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'][rand(0, 7)],
                    'allergies' => rand(0, 1) ? 'Ninguna' : 'Polen, Polvo',
                    'emergency_contact' => json_encode([
                        'name' => 'Contacto ' . $nameData[1],
                        'phone' => '987654' . str_pad($index, 4, '0', STR_PAD_LEFT),
                        'relationship' => 'Padre'
                    ]),
                    'parent_data' => json_encode([
                        'father_name' => $nameData[1] . ' Padre',
                        'father_phone' => '987654' . str_pad($index, 4, '0', STR_PAD_LEFT),
                        'mother_name' => $nameData[2] . ' Madre',
                        'mother_phone' => '987655' . str_pad($index, 4, '0', STR_PAD_LEFT)
                    ]),
                    'group_id' => $group->id,
                ]);
                $createdStudents++;
            }
        }
        echo "   ✓ Estudiantes creados: $createdStudents\n\n";

        // 5. Crear más profesores
        echo "👨‍🏫 Creando profesores adicionales...\n";
        $teacherData = [
            ['María', 'González', 'Matemáticas'],
            ['Pedro', 'Martínez', 'Inglés'],
            ['Ana', 'López', 'Química'],
            ['José', 'Hernández', 'Programación'],
        ];

        $createdTeachers = 0;
        foreach ($teacherData as $index => $teacherInfo) {
            $teacherUser = User::firstOrCreate(
                ['email' => strtolower($teacherInfo[0]) . '.' . strtolower($teacherInfo[1]) . '@eduai.com'],
                [
                    'name' => $teacherInfo[0] . ' ' . $teacherInfo[1],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                ]
            );

            if (!Teacher::where('user_id', $teacherUser->id)->exists()) {
                $teacher = Teacher::create([
                    'user_id' => $teacherUser->id,
                    'professional_license' => 'PROF' . str_pad($index + 2, 6, '0', STR_PAD_LEFT),
                    'specialization' => $teacherInfo[2],
                    'education_level' => 'Licenciatura',
                    'experience_years' => rand(3, 15),
                    'availability' => json_encode([
                        'Lunes' => ['08:00-14:00'],
                        'Martes' => ['08:00-14:00'],
                        'Miércoles' => ['08:00-14:00'],
                    ]),
                ]);

                // Asignar materias relacionadas
                $relatedSubject = Subject::where('name', 'LIKE', '%' . substr($teacherInfo[2], 0, 3) . '%')->first();
                if ($relatedSubject) {
                    $teacher->subjects()->attach($relatedSubject->id);
                }
                $createdTeachers++;
            }
        }
        echo "   ✓ Profesores creados: $createdTeachers\n\n";

        // 6. Resumen final
        echo "========================================\n";
        echo "✅ Datos completos creados exitosamente\n";
        echo "========================================\n\n";
        
        $stats = [
            'Períodos académicos' => AcademicPeriod::count(),
            'Usuarios' => User::count(),
            'Estudiantes' => Student::count(),
            'Profesores' => Teacher::count(),
            'Grupos' => Group::count(),
            'Materias' => Subject::count(),
            'Horarios' => Schedule::count(),
        ];

        foreach ($stats as $label => $count) {
            echo "   $label: $count\n";
        }
        echo "\n";
    }
}

