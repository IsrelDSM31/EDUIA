<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentRisk;

$risks = StudentRisk::with('student')->get();
foreach(['alto','medio','bajo'] as $nivel) {
    echo "\n\n--- Alumnos en riesgo $nivel ---\n";
    foreach($risks->where('risk_level',$nivel) as $r) {
        $nombre = ($r->student->nombre ?? 'N/A') . ' ' . ($r->student->apellido_paterno ?? '');
        echo 'Alumno: ' . $nombre . ' | Puntaje: ' . $r->risk_score . "\n";
        echo 'Recomendaciones: ';
        $recs = null;
        try {
            $recs = json_decode($r->intervention_recommendations,true);
        } catch (\Throwable $e) {}
        if($recs && is_array($recs) && count($recs) > 0) {
            foreach($recs as $rec) {
                echo ($rec['message'] ?? (is_string($rec) ? $rec : json_encode($rec))) . ' | ';
            }
        } else {
            echo 'Sin recomendaciones.';
        }
        echo "\n";
    }
} 