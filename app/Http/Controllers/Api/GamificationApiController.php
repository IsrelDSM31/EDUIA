<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class GamificationApiController extends ApiController
{
    /**
     * Obtener puntos y nivel de un estudiante
     */
    public function getStudentPoints($studentId): JsonResponse
    {
        $points = DB::table('student_points')
            ->where('student_id', $studentId)
            ->first();

        if (!$points) {
            // Crear registro de puntos si no existe
            $points = $this->initializeStudentPoints($studentId);
        }

        // Calcular progreso al siguiente nivel
        $progress = $points->total_points % 100;
        $progressPercentage = ($progress / $points->points_to_next_level) * 100;

        return $this->successResponse([
            'student_id' => $points->student_id,
            'total_points' => $points->total_points,
            'level' => $points->level,
            'progress_to_next_level' => $progress,
            'progress_percentage' => round($progressPercentage, 2),
            'points_breakdown' => [
                'attendance' => $points->attendance_points,
                'grades' => $points->grade_points,
                'participation' => $points->participation_points,
                'achievements' => $points->achievement_points,
            ],
            'streak_days' => $points->streak_days,
        ], 'Student points retrieved successfully');
    }

    /**
     * Obtener logros de un estudiante
     */
    public function getStudentAchievements($studentId): JsonResponse
    {
        $unlockedAchievements = DB::table('student_achievements')
            ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
            ->where('student_achievements.student_id', $studentId)
            ->select('achievements.*', 'student_achievements.unlocked_at')
            ->orderBy('student_achievements.unlocked_at', 'desc')
            ->get();

        $totalAchievements = DB::table('achievements')->where('is_active', true)->count();
        $unlockedCount = $unlockedAchievements->count();

        return $this->successResponse([
            'unlocked' => $unlockedAchievements,
            'total_achievements' => $totalAchievements,
            'unlocked_count' => $unlockedCount,
            'completion_percentage' => $totalAchievements > 0 ? round(($unlockedCount / $totalAchievements) * 100, 2) : 0,
        ], 'Student achievements retrieved successfully');
    }

    /**
     * Obtener todos los logros disponibles
     */
    public function getAllAchievements(): JsonResponse
    {
        $achievements = DB::table('achievements')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderByRaw("FIELD(rarity, 'common', 'rare', 'epic', 'legendary')")
            ->get();

        $grouped = $achievements->groupBy('category');

        return $this->successResponse([
            'achievements' => $achievements,
            'by_category' => $grouped,
            'total' => $achievements->count(),
        ], 'All achievements retrieved successfully');
    }

    /**
     * Obtener ranking global
     */
    public function getGlobalRanking(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all_time'); // weekly, monthly, all_time
        $limit = $request->get('limit', 50);

        $query = DB::table('student_points')
            ->join('students', 'student_points.student_id', '=', 'students.id')
            ->select(
                'students.id',
                'students.nombre',
                'students.apellido_paterno',
                'students.apellido_materno',
                'students.matricula',
                'student_points.total_points',
                'student_points.level',
                'student_points.streak_days',
                DB::raw('RANK() OVER (ORDER BY student_points.total_points DESC) as rank')
            )
            ->orderBy('student_points.total_points', 'desc')
            ->limit($limit);

        $ranking = $query->get();

        // Formatear nombres
        $ranking = $ranking->map(function ($item) {
            $item->student_name = trim($item->nombre . ' ' . $item->apellido_paterno . ' ' . $item->apellido_materno);
            unset($item->nombre, $item->apellido_paterno, $item->apellido_materno);
            return $item;
        });

        return $this->successResponse([
            'ranking' => $ranking,
            'period' => $period,
            'total_students' => $ranking->count(),
        ], 'Ranking retrieved successfully');
    }

    /**
     * Obtener posición de un estudiante en el ranking
     */
    public function getStudentRank($studentId): JsonResponse
    {
        $allStudents = DB::table('student_points')
            ->select('student_id', 'total_points')
            ->orderBy('total_points', 'desc')
            ->get();

        $rank = $allStudents->search(function ($item) use ($studentId) {
            return $item->student_id == $studentId;
        }) + 1;

        $studentPoints = $allStudents->firstWhere('student_id', $studentId);

        if (!$studentPoints) {
            return $this->errorResponse('Student not found', 404);
        }

        // Obtener estudiantes cercanos
        $nearbyStudents = $allStudents->slice(max(0, $rank - 3), 5);

        return $this->successResponse([
            'rank' => $rank,
            'total_students' => $allStudents->count(),
            'points' => $studentPoints->total_points,
            'nearby_students' => $nearbyStudents,
        ], 'Student rank retrieved successfully');
    }

    /**
     * Obtener historial de puntos
     */
    public function getPointsHistory($studentId): JsonResponse
    {
        $history = DB::table('points_history')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $this->successResponse($history, 'Points history retrieved successfully');
    }

    /**
     * Agregar puntos a un estudiante
     */
    public function addPoints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:attendance,grade,participation,achievement',
            'description' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $studentId = $validated['student_id'];
        $points = $validated['points'];
        $type = $validated['type'];

        // Obtener o crear puntos del estudiante
        $studentPoints = DB::table('student_points')->where('student_id', $studentId)->first();
        
        if (!$studentPoints) {
            $studentPoints = $this->initializeStudentPoints($studentId);
        }

        // Actualizar puntos
        $newTotal = $studentPoints->total_points + $points;
        $newLevel = floor($newTotal / 100) + 1;

        $updateData = [
            'total_points' => $newTotal,
            'level' => $newLevel,
            $type . '_points' => $studentPoints->{$type . '_points'} + $points,
            'updated_at' => now(),
        ];

        DB::table('student_points')
            ->where('student_id', $studentId)
            ->update($updateData);

        // Registrar en historial
        DB::table('points_history')->insert([
            'student_id' => $studentId,
            'points' => $points,
            'type' => $type,
            'description' => $validated['description'],
            'metadata' => json_encode($validated['metadata'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verificar nuevos logros
        $this->checkAndUnlockAchievements($studentId);

        return $this->successResponse([
            'total_points' => $newTotal,
            'level' => $newLevel,
            'points_added' => $points,
        ], 'Points added successfully');
    }

    /**
     * Inicializar puntos de un estudiante
     */
    private function initializeStudentPoints($studentId)
    {
        DB::table('student_points')->insert([
            'student_id' => $studentId,
            'total_points' => 0,
            'attendance_points' => 0,
            'grade_points' => 0,
            'participation_points' => 0,
            'achievement_points' => 0,
            'level' => 1,
            'points_to_next_level' => 100,
            'streak_days' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('student_points')->where('student_id', $studentId)->first();
    }

    /**
     * Verificar y desbloquear logros
     */
    private function checkAndUnlockAchievements($studentId)
    {
        // Obtener logros no desbloqueados
        $unlockedIds = DB::table('student_achievements')
            ->where('student_id', $studentId)
            ->pluck('achievement_id');

        $achievements = DB::table('achievements')
            ->where('is_active', true)
            ->whereNotIn('id', $unlockedIds)
            ->get();

        // Obtener datos del estudiante
        $studentPoints = DB::table('student_points')->where('student_id', $studentId)->first();

        foreach ($achievements as $achievement) {
            $requirements = json_decode($achievement->requirements, true);
            $unlocked = false;

            // Verificar requisitos según categoría
            if ($achievement->category === 'attendance' && isset($requirements['streak_days'])) {
                if ($studentPoints->streak_days >= $requirements['streak_days']) {
                    $unlocked = true;
                }
            }

            if ($unlocked) {
                DB::table('student_achievements')->insert([
                    'student_id' => $studentId,
                    'achievement_id' => $achievement->id,
                    'unlocked_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Agregar puntos del logro
                DB::table('student_points')
                    ->where('student_id', $studentId)
                    ->increment('achievement_points', $achievement->points);
                DB::table('student_points')
                    ->where('student_id', $studentId)
                    ->increment('total_points', $achievement->points);
            }
        }
    }
}







