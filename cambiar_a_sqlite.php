<?php
/**
 * Script Rápido: Cambiar a SQLite
 * Solo cambia la configuración y ejecuta migraciones
 */

$projectDir = __DIR__;
$envFile = $projectDir . DIRECTORY_SEPARATOR . '.env';
$sqlitePath = $projectDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';

echo "========================================\n";
echo "🔄 Cambiando a SQLite\n";
echo "========================================\n\n";

// Backup
if (file_exists($envFile)) {
    copy($envFile, $envFile . '.backup_' . date('YmdHis'));
    echo "✓ Backup .env creado\n\n";
}

// Leer .env
$envContent = file_get_contents($envFile);

// Cambiar a SQLite
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
$envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=', $envContent);
$envContent = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=database/database.sqlite', $envContent);
$envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=', $envContent);
$envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=', $envContent);

// Guardar
file_put_contents($envFile, $envContent);
echo "✓ Configuración actualizada\n\n";

// Crear base de datos si no existe
$dbDir = dirname($sqlitePath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

if (!file_exists($sqlitePath)) {
    touch($sqlitePath);
    echo "✓ Base de datos SQLite creada\n\n";
}

// Ejecutar migraciones
echo "🏗️  Ejecutando migraciones...\n";
exec("cd \"{$projectDir}\" && php artisan migrate --force 2>&1", $output, $returnVar);

if ($returnVar === 0) {
    echo "✓ Migraciones completadas\n\n";
} else {
    echo "⚠️  Revisa los errores arriba\n\n";
}

echo "========================================\n";
echo "✅ Cambio completado!\n";
echo "========================================\n\n";

echo "📝 Tu .env ahora tiene:\n";
echo "DB_CONNECTION=sqlite\n";
echo "DB_DATABASE=database/database.sqlite\n\n";


