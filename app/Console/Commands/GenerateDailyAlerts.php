<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRisk;
use App\Models\Alert;
use Carbon\Carbon;

class GenerateDailyAlerts extends Command
{
    protected $signature = 'alerts:generate-daily';
    protected $description = 'Generar alertas diarias para estudiantes en riesgo';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        // Verificar si ya se generaron alertas hoy
        $existingToday = Alert::whereDate('created_at', $today)
            ->where('type', 'daily_risk_report')
            ->count();
        
        if ($existingToday > 0) {
            $this->info("Ya se generaron alertas hoy ({$existingToday} alertas).");
            return 0;
        }

        // Obtener estudiantes en riesgo alto
        $highRiskStudents = StudentRisk::whereIn('risk_level', ['alto', 'high'])
            ->with('student')
            ->get();

        $created = 0;
        foreach ($highRiskStudents as $risk) {
            if ($risk->student) {
                Alert::create([
                    'student_id' => $risk->student->id,
                    'type' => 'daily_risk_report',
                    'title' => 'Estudiante en Riesgo Alto',
                    'description' => 'El estudiante ' . trim($risk->student->nombre . ' ' . $risk->student->apellido_paterno . ' ' . $risk->student->apellido_materno) . ' requiere atención inmediata debido a su nivel de riesgo alto.',
                    'urgency' => 'high',
                    'evidence' => [
                        'risk_level' => $risk->risk_level,
                        'risk_score' => $risk->risk_score,
                        'date' => $today,
                    ],
                    'suggested_actions' => [
                        'Tutoría inmediata',
                        'Reunión con padres',
                        'Plan de recuperación',
                    ],
                    'intervention_plan' => [
                        'status' => 'pending',
                        'priority' => 'urgent',
                    ],
                ]);
                $created++;
            }
        }

        // Obtener estudiantes en riesgo medio
        $mediumRiskStudents = StudentRisk::whereIn('risk_level', ['medio', 'medium'])
            ->with('student')
            ->get();

        foreach ($mediumRiskStudents as $risk) {
            if ($risk->student) {
                Alert::create([
                    'student_id' => $risk->student->id,
                    'type' => 'daily_risk_report',
                    'title' => 'Estudiante en Riesgo Medio',
                    'description' => 'El estudiante ' . trim($risk->student->nombre . ' ' . $risk->student->apellido_paterno . ' ' . $risk->student->apellido_materno) . ' requiere atención preventiva.',
                    'urgency' => 'medium',
                    'evidence' => [
                        'risk_level' => $risk->risk_level,
                        'risk_score' => $risk->risk_score,
                        'date' => $today,
                    ],
                    'suggested_actions' => [
                        'Refuerzo académico',
                        'Seguimiento quincenal',
                    ],
                    'intervention_plan' => [
                        'status' => 'monitoring',
                        'priority' => 'moderate',
                    ],
                ]);
                $created++;
            }
        }

        $this->info("Alertas generadas exitosamente: {$created}");
        return 0;
    }
}







