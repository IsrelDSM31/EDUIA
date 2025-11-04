<?php
/**
 * Script: Volver a MySQL
 * Restaura la configuración original de MySQL
 */

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

echo "========================================\n";
echo "🔄 Restaurando configuración MySQL\n";
echo "========================================\n\n";

if (!file_exists($envFile)) {
    die("❌ Archivo .env no encontrado\n");
}

// Leer .env
$envContent = file_get_contents($envFile);

// Backup
copy($envFile, $envFile . '.backup_sqlite_' . date('YmdHis'));
echo "✓ Backup creado\n\n";

// Restaurar configuración MySQL
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $envContent);
$envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $envContent);
$envContent = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=3306', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=iaedu1', $envContent);
$envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=root', $envContent);
$envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=', $envContent);

// Guardar
file_put_contents($envFile, $envContent);

echo "✅ Configuración restaurada a MySQL\n\n";
echo "Valores actualizados:\n";
echo "DB_CONNECTION=mysql\n";
echo "DB_HOST=127.0.0.1\n";
echo "DB_PORT=3306\n";
echo "DB_DATABASE=iaedu1\n";
echo "DB_USERNAME=root\n";
echo "DB_PASSWORD=\n\n";

echo "========================================\n";


