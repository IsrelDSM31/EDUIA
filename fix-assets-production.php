<?php
/**
 * Script para forzar uso de assets compilados
 * Resuelve el problema de que Vite intente conectarse cuando no está disponible
 */

echo "========================================\n";
echo "🔧 Forzando uso de Assets Compilados\n";
echo "========================================\n\n";

// Eliminar archivo hot si existe (esto fuerza usar assets compilados)
$hotFile = __DIR__ . '/public/hot';
if (file_exists($hotFile)) {
    unlink($hotFile);
    echo "✓ Archivo 'hot' eliminado\n";
} else {
    echo "✓ No hay archivo 'hot' (ya usa assets compilados)\n";
}
echo "\n";

// Verificar que los assets compilados existan
$manifestFile = __DIR__ . '/public/build/manifest.json';
$manifestFileAlt = __DIR__ . '/public/build/.vite/manifest.json';
if (!file_exists($manifestFile)) {
    // Verificar si está en la ubicación alternativa
    if (file_exists($manifestFileAlt)) {
        copy($manifestFileAlt, $manifestFile);
        echo "✓ Manifest copiado desde ubicación alternativa\n";
    }
}
if (!file_exists($manifestFile)) {
    echo "⚠️  Assets no compilados. Compilando ahora...\n";
    echo "   Esto puede tomar unos minutos...\n\n";
    
    $output = [];
    $returnCode = 0;
    exec('npm run build 2>&1', $output, $returnCode);
    
    foreach ($output as $line) {
        echo "   " . $line . "\n";
    }
    
    if ($returnCode !== 0) {
        echo "\n❌ Error al compilar assets\n";
        echo "   Asegúrate de que Node.js esté instalado\n";
        exit(1);
    }
    
    echo "\n✓ Assets compilados correctamente\n";
} else {
    echo "✓ Assets compilados encontrados\n";
}
echo "\n";

// Limpiar cache de Laravel
echo "🧹 Limpiando cache de Laravel...\n";
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    echo "✓ Cache limpiado\n";
} catch (Exception $e) {
    echo "⚠️  Advertencia: " . $e->getMessage() . "\n";
}
echo "\n";

echo "========================================\n";
echo "✅ Configuración completada!\n";
echo "========================================\n";
echo "\n";
echo "💡 Ahora puedes iniciar el servidor con:\n";
echo "   php artisan serve\n";
echo "\n";
echo "💡 O usa el script:\n";
echo "   start-server.bat\n";
echo "\n";

