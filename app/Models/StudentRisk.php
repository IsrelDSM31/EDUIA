<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRisk extends Model
{
    protected $fillable = [
        'student_id',
        'risk_score',
        'risk_level',
        'performance_metrics',
        'behavior_patterns',
        'intervention_recommendations',
        'progress_metrics',
        'notes'
    ];

    protected $casts = [
        'performance_metrics' => 'array',
        'behavior_patterns' => 'array',
        'intervention_recommendations' => 'array',
        'progress_metrics' => 'array',
        'risk_score' => 'float'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
