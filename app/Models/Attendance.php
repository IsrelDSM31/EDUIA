<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Attendance",
 *     title="Attendance",
 *     description="Modelo de asistencia del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la asistencia"),
 *     @OA\Property(property="student_id", type="integer", example=1, description="ID del estudiante"),
 *     @OA\Property(property="subject_id", type="integer", example=1, description="ID de la materia"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Fecha de la asistencia"),
 *     @OA\Property(property="status", type="string", enum={"present","absent","late"}, example="present", description="Estado de la asistencia"),
 *     @OA\Property(property="justification_type", type="string", example="medical", description="Tipo de justificación"),
 *     @OA\Property(property="justification_document", type="string", example="document.pdf", description="Documento de justificación"),
 *     @OA\Property(property="observations", type="string", example="Llegó a tiempo", description="Observaciones adicionales"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de última actualización")
 * )
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'date',
        'status',
        'justification_type',
        'justification_document',
        'observations',
    ];

    protected $casts = [
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