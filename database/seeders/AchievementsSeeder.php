<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Logros de Asistencia
            [
                'name' => 'Puntual Principiante',
                'slug' => 'puntual-principiante',
                'description' => 'Asiste 5 días consecutivos sin faltas',
                'icon' => 'calendar-check',
                'category' => 'attendance',
                'rarity' => 'common',
                'points' => 10,
                'requirements' => json_encode(['streak_days' => 5]),
                'is_active' => true,
            ],
            [
                'name' => 'Asistencia Perfecta',
                'slug' => 'asistencia-perfecta',
                'description' => 'Asiste 30 días consecutivos sin faltas',
                'icon' => 'star',
                'category' => 'attendance',
                'rarity' => 'epic',
                'points' => 50,
                'requirements' => json_encode(['streak_days' => 30]),
                'is_active' => true,
            ],
            [
                'name' => 'Dedicación Total',
                'slug' => 'dedicacion-total',
                'description' => '100% de asistencia en el mes',
                'icon' => 'trophy',
                'category' => 'attendance',
                'rarity' => 'legendary',
                'points' => 100,
                'requirements' => json_encode(['monthly_attendance' => 100]),
                'is_active' => true,
            ],

            // Logros de Calificaciones
            [
                'name' => 'Estudiante Ejemplar',
                'slug' => 'estudiante-ejemplar',
                'description' => 'Obtén un promedio de 9 o más',
                'icon' => 'school',
                'category' => 'grades',
                'rarity' => 'rare',
                'points' => 25,
                'requirements' => json_encode(['average_grade' => 9]),
                'is_active' => true,
            ],
            [
                'name' => 'Excelencia Académica',
                'slug' => 'excelencia-academica',
                'description' => 'Obtén 10 en 5 evaluaciones',
                'icon' => 'medal',
                'category' => 'grades',
                'rarity' => 'epic',
                'points' => 50,
                'requirements' => json_encode(['perfect_grades' => 5]),
                'is_active' => true,
            ],
            [
                'name' => 'Genio',
                'slug' => 'genio',
                'description' => 'Mantén un promedio de 10 durante todo el semestre',
                'icon' => 'brain',
                'category' => 'grades',
                'rarity' => 'legendary',
                'points' => 150,
                'requirements' => json_encode(['semester_average' => 10]),
                'is_active' => true,
            ],

            // Logros de Participación
            [
                'name' => 'Participativo',
                'slug' => 'participativo',
                'description' => 'Participa en 10 actividades',
                'icon' => 'hand-raised',
                'category' => 'participation',
                'rarity' => 'common',
                'points' => 15,
                'requirements' => json_encode(['participations' => 10]),
                'is_active' => true,
            ],
            [
                'name' => 'Líder de Clase',
                'slug' => 'lider-clase',
                'description' => 'Participa en 50 actividades',
                'icon' => 'account-star',
                'category' => 'participation',
                'rarity' => 'rare',
                'points' => 30,
                'requirements' => json_encode(['participations' => 50]),
                'is_active' => true,
            ],

            // Logros Especiales
            [
                'name' => 'Primera Victoria',
                'slug' => 'primera-victoria',
                'description' => 'Completa tu primera semana de clases',
                'icon' => 'flag-checkered',
                'category' => 'special',
                'rarity' => 'common',
                'points' => 5,
                'requirements' => json_encode(['first_week' => true]),
                'is_active' => true,
            ],
            [
                'name' => 'Racha Imparable',
                'slug' => 'racha-imparable',
                'description' => 'Gana puntos durante 7 días consecutivos',
                'icon' => 'fire',
                'category' => 'special',
                'rarity' => 'rare',
                'points' => 40,
                'requirements' => json_encode(['points_streak' => 7]),
                'is_active' => true,
            ],
            [
                'name' => 'Top 3',
                'slug' => 'top-3',
                'description' => 'Entra al top 3 del ranking',
                'icon' => 'podium',
                'category' => 'special',
                'rarity' => 'epic',
                'points' => 75,
                'requirements' => json_encode(['ranking_position' => 3]),
                'is_active' => true,
            ],
            [
                'name' => 'Campeón',
                'slug' => 'campeon',
                'description' => 'Alcanza el #1 en el ranking',
                'icon' => 'crown',
                'category' => 'special',
                'rarity' => 'legendary',
                'points' => 200,
                'requirements' => json_encode(['ranking_position' => 1]),
                'is_active' => true,
            ],
            [
                'name' => 'Mejora Continua',
                'slug' => 'mejora-continua',
                'description' => 'Sube tu promedio en 1 punto',
                'icon' => 'trending-up',
                'category' => 'grades',
                'rarity' => 'rare',
                'points' => 35,
                'requirements' => json_encode(['grade_improvement' => 1]),
                'is_active' => true,
            ],
        ];

        DB::table('achievements')->insert($achievements);
    }
}



