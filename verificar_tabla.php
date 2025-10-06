<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "═══════════════════════════════════════\n";
echo "Verificando Base de Datos\n";
echo "═══════════════════════════════════════\n\n";

echo "Base de datos: " . config('database.connections.mysql.database') . "\n\n";

// Verificar si la tabla existe
$tableExists = Schema::hasTable('personal_access_tokens');

echo "Tabla 'personal_access_tokens' existe: " . ($tableExists ? "✅ SÍ\n" : "❌ NO\n");

if ($tableExists) {
    $columns = Schema::getColumnListing('personal_access_tokens');
    echo "Columnas (" . count($columns) . "): " . implode(', ', $columns) . "\n";
    
    $count = DB::table('personal_access_tokens')->count();
    echo "Registros en la tabla: " . $count . "\n";
}

echo "\n═══════════════════════════════════════\n";

