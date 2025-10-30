<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Channel;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarMessagingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear eventos de calendario
        $events = [
            [
                'title' => 'Examen de Matemáticas',
                'description' => 'Examen del capítulo 5: Álgebra avanzada',
                'type' => 'exam',
                'start_date' => now()->addDays(3)->setTime(9, 0),
                'end_date' => now()->addDays(3)->setTime(11, 0),
                'subject_name' => 'Matemáticas',
            ],
            [
                'title' => 'Reunión de Padres',
                'description' => 'Reunión informativa del trimestre',
                'type' => 'meeting',
                'start_date' => now()->addDays(7)->setTime(15, 0),
                'end_date' => now()->addDays(7)->setTime(17, 0),
                'subject_name' => null,
            ],
            [
                'title' => 'Examen de Historia',
                'description' => 'Evaluación sobre la independencia nacional',
                'type' => 'exam',
                'start_date' => now()->addDays(10)->setTime(10, 0),
                'end_date' => now()->addDays(10)->setTime(12, 0),
                'subject_name' => 'Historia',
            ],
            [
                'title' => 'Día Festivo',
                'description' => 'Día de la independencia',
                'type' => 'holiday',
                'start_date' => now()->addDays(15),
                'end_date' => now()->addDays(15),
                'subject_name' => null,
            ],
            [
                'title' => 'Entrega de Proyectos',
                'description' => 'Fecha límite para entrega de proyectos finales',
                'type' => 'assignment',
                'start_date' => now()->addDays(14)->setTime(23, 59),
                'end_date' => null,
                'subject_name' => 'Ciencias',
            ],
        ];

        foreach ($events as $event) {
            CalendarEvent::create($event);
        }

        echo "✅ Eventos de calendario creados exitosamente\n";

        // Crear canales por materia
        $subjects = Subject::all();
        
        foreach ($subjects as $subject) {
            Channel::create([
                'name' => $subject->name,
                'slug' => Str::slug($subject->name),
                'subject_id' => $subject->id,
                'members_count' => 0, // Se actualizará con lógica real
            ]);
        }

        echo "✅ Canales de materias creados exitosamente\n";
        echo "✅ Seeder completado. Datos de ejemplo agregados.\n";
    }
}






