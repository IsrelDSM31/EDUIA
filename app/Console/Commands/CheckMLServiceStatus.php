<?php

namespace App\Console\Commands;

use App\Services\RiskPredictionService;
use Illuminate\Console\Command;

class CheckMLServiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar el estado del servicio ML de IA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando estado del servicio ML...');
        $this->newLine();

        $mlService = app(RiskPredictionService::class);
        
        // Verificar si el servicio está disponible
        $isAvailable = $mlService->isMLServiceAvailable();
        
        if ($isAvailable) {
            $this->info('✅ Servicio ML está ACTIVO y funcionando');
            $this->info('   El análisis de riesgo está usando Inteligencia Artificial');
            
            // Intentar obtener información del modelo
            try {
                $modelInfo = $mlService->getModelInfo();
                if ($modelInfo) {
                    $this->newLine();
                    $this->info('📊 Información del Modelo:');
                    if (isset($modelInfo['model_type'])) {
                        $this->line('   Tipo: ' . $modelInfo['model_type']);
                    }
                    if (isset($modelInfo['features'])) {
                        $this->line('   Características: ' . implode(', ', $modelInfo['features']));
                    }
                    if (isset($modelInfo['accuracy'])) {
                        $this->line('   Precisión: ' . ($modelInfo['accuracy'] * 100) . '%');
                    }
                }
            } catch (\Exception $e) {
                // No es crítico si no podemos obtener la info del modelo
            }
        } else {
            $this->error('❌ Servicio ML NO está disponible');
            $this->warn('   El análisis de riesgo está usando reglas heurísticas (fallback)');
            $this->newLine();
            $this->info('💡 Para activar el servicio ML:');
            $this->line('   1. Abre una terminal en: ml_service');
            $this->line('   2. Ejecuta: python predict_service.py');
            $this->line('   3. O usa: start_service.bat (Windows)');
            $this->newLine();
            $this->line('   El servicio debe estar corriendo en: http://localhost:5000');
        }
        
        $this->newLine();
        $this->info('📝 Nota: El sistema funciona en ambos casos:');
        $this->line('   - Con ML: Usa predicciones de Machine Learning');
        $this->line('   - Sin ML: Usa reglas heurísticas (fallback automático)');
        
        return Command::SUCCESS;
    }
}
