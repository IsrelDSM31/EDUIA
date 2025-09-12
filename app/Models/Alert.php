<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Alert",
 *     title="Alert",
 *     description="Modelo de alerta del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único de la alerta"),
 *     @OA\Property(property="student_id", type="integer", example=1, description="ID del estudiante"),
 *     @OA\Property(property="tipo", type="string", example="academica", description="Tipo de alerta"),
 *     @OA\Property(property="mensaje", type="string", example="Bajo rendimiento académico", description="Mensaje de la alerta"),
 *     @OA\Property(property="estado", type="string", enum={"activa","resuelta"}, example="activa", description="Estado de la alerta"),
 *     @OA\Property(property="fecha", type="string", format="date", example="2024-01-15", description="Fecha de la alerta"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de última actualización")
 * )
 */
class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tipo',
        'mensaje',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
} 