<?php
/**
 * Script Rápido: Cambiar a SQLite
 * 
 * Cambia la configuración de MySQL a SQLite
 * SIN migrar datos (solo estructura)
 * 
 * Uso: php switch_to_sqlite.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "🔄 Cambiando a SQLite\n";
echo "========================================\n\n";

// Backup
$envFile = __DIR__ . '/.env';
copy($envFile, $envFile . '.backup_' . date('YmdHis'));
echo "✓ Backup creado\n\n";

// Leer .env
$envContent = file_get_contents($envFile);

// Cambiar configuración
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
$envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=', $envContent);
$envContent = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . __DIR__ . '/database/database.sqlite', $envContent);
$envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=', $envContent);
$envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=', $envContent);

// Guardar
file_put_contents($envFile, $envContent);

echo "✅ Configuración actualizada a SQLite\n\n";

// Crear base de datos si no existe
$sqlitePath = __DIR__ . '/database/database.sqlite';
if (!file_exists($sqlitePath)) {
    touch($sqlitePath);
    echo "✓ Base de datos SQLite creada\n\n";
}

// Ejecutar migraciones
echo "🏗️  Ejecutando migraciones...\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo "✓ Migraciones completadas\n\n";
} catch (Exception $e) {
    echo "⚠️  Error: " . $e->getMessage() . "\n";
    echo "Ejecuta manualmente: php artisan migrate\n\n";
}

echo "========================================\n";
echo "✅ Cambio completado!\n";
echo "========================================\n\n";

echo "📝 Tu .env ahora tiene:\n";
echo "DB_CONNECTION=sqlite\n";
echo "DB_DATABASE=" . __DIR__ . "/database/database.sqlite\n";
echo "\n";


