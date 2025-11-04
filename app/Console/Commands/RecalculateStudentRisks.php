<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentRisk;
use App\Http\Controllers\StudentRiskController;
use Illuminate\Console\Command;

class RecalculateStudentRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risks:recalculate {--all : Recalcular todos los estudiantes, incluso los que ya tienen análisis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcular riesgos académicos de todos los estudiantes con la nueva lógica mejorada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalculando riesgos académicos con la nueva lógica mejorada...');
        
        $controller = new StudentRiskController();
        $allStudents = Student::with(['grades', 'attendances'])->get();
        $total = $allStudents->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $recalculated = 0;
        $errors = 0;

        foreach ($allStudents as $student) {
            try {
                // Recalcular riesgo
                $controller->calculateRiskScore($student);
                $recalculated++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError con estudiante ID {$student->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Proceso completado:");
        $this->info("   - Estudiantes recalculados: {$recalculated}");
        if ($errors > 0) {
            $this->warn("   - Errores: {$errors}");
        }
        $this->info("\n💡 Los riesgos ahora se calculan con la lógica mejorada que considera:");
        $this->info("   - Promedio académico");
        $this->info("   - Tasa de asistencia");
        $this->info("   - Materias reprobadas");
        $this->info("   - Mejora reciente");
        
        return Command::SUCCESS;
    }
}
