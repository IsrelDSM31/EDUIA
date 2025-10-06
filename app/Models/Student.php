<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Student",
 *     title="Student",
 *     description="Modelo de estudiante del sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID único del estudiante"),
 *     @OA\Property(property="matricula", type="string", example="2024001", description="Matrícula del estudiante"),
 *     @OA\Property(property="nombre", type="string", example="Juan", description="Nombre del estudiante"),
 *     @OA\Property(property="apellido_paterno", type="string", example="Pérez", description="Apellido paterno"),
 *     @OA\Property(property="apellido_materno", type="string", example="García", description="Apellido materno"),
 *     @OA\Property(property="group_id", type="integer", example=1, description="ID del grupo al que pertenece"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2005-03-15", description="Fecha de nacimiento"),
 *     @OA\Property(property="curp", type="string", example="PEGJ050315HDFXXX01", description="CURP del estudiante"),
 *     @OA\Property(property="blood_type", type="string", example="O+", description="Tipo de sangre"),
 *     @OA\Property(property="allergies", type="string", example="Polen", description="Alergias del estudiante"),
 *     @OA\Property(property="emergency_contact", type="object", description="Información de contacto de emergencia"),
 *     @OA\Property(property="parent_data", type="object", description="Datos de los padres"),
 *     @OA\Property(property="user_id", type="integer", example=1, description="ID del usuario asociado"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z", description="Fecha de última actualización")
 * )
 */
class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'matricula',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'group_id',
        'birth_date',
        'curp',
        'blood_type',
        'allergies',
        'emergency_contact',
        'parent_data',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'emergency_contact' => 'array',
        'parent_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function studentRisks(): HasMany
    {
        return $this->hasMany(StudentRisk::class);
    }

    public function risk()
    {
        return $this->hasOne(StudentRisk::class)->latestOfMany();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . ($this->apellido_materno ?? ''));
    }

    public function getAverageGradeAttribute(): float
    {
        return $this->grades()->avg('promedio_final') ?? 0.0;
    }

    public function getAttendancePercentageAttribute(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0.0;
        
        $present = $this->attendances()->where('estado', 'presente')->count();
        return round(($present / $total) * 100, 2);
    }
} 