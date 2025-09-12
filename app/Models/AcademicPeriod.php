<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AcademicPeriod",
 *     title="AcademicPeriod",
 *     description="Modelo de período académico del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del período académico"),
 *     @OA\Property(property="nombre", type="string", example="Primer Semestre 2024", description="Nombre del período"),
 *     @OA\Property(property="fecha_inicio", type="string", format="date", example="2024-01-15", description="Fecha de inicio"),
 *     @OA\Property(property="fecha_fin", type="string", format="date", example="2024-06-15", description="Fecha de fin"),
 *     @OA\Property(property="estado", type="string", enum={"activo","inactivo","finalizado"}, example="activo", description="Estado del período"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de última actualización")
 * )
 */
class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
} 