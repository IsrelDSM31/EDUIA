<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Grade",
 *     title="Grade",
 *     description="Modelo de calificación del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la calificación"),
 *     @OA\Property(property="student_id", type="integer", example=1, description="ID del estudiante"),
 *     @OA\Property(property="subject_id", type="integer", example=1, description="ID de la materia"),
 *     @OA\Property(property="promedio_final", type="number", format="float", example=8.5, description="Promedio final de la calificación"),
 *     @OA\Property(property="evaluations", type="object", description="Evaluaciones individuales"),
 *     @OA\Property(property="estado", type="string", example="Aprobado", description="Estado de la calificación"),
 *     @OA\Property(property="faltantes", type="integer", example=0, description="Número de evaluaciones faltantes"),
 *     @OA\Property(property="puntos_faltantes", type="number", format="float", example=0.0, description="Puntos faltantes"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Fecha de la calificación"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de última actualización")
 * )
 */
class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'promedio_final',
        'evaluations',
        'estado',
        'faltantes',
        'puntos_faltantes',
        'date',
    ];

    protected $casts = [
        'evaluations' => 'array',
        'promedio_final' => 'float',
        'puntos_faltantes' => 'float',
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
} 