<?php
/**
 * Script: Volver a MySQL
 * 
 * Restaura la configuración de MySQL
 * 
 * Uso: php switch_to_mysql.php
 */

$envFile = __DIR__ . '/.env';

echo "========================================\n";
echo "🔄 Volviendo a MySQL\n";
echo "========================================\n\n";

// Leer .env
$envContent = file_get_contents($envFile);

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
echo "========================================\n";


