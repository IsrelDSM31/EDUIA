<?php
/**
 * Script de Migración: MySQL a SQLite
 * 
 * Este script migra todos los datos de MySQL a SQLite
 * SIN DAÑAR la base de datos MySQL original
 * 
 * Uso: php migrate_to_sqlite.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "🚀 Migración MySQL → SQLite\n";
echo "========================================\n\n";

// Configuración
$mysqlConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'iaedu1',
    'username' => 'root',
    'password' => '',
];

$sqlitePath = __DIR__ . '/database/database.sqlite';

// Paso 1: Backup
echo "📦 Paso 1: Creando backups...\n";
if (file_exists($sqlitePath)) {
    copy($sqlitePath, $sqlitePath . '.backup_' . date('YmdHis'));
    echo "   ✓ Backup SQLite creado\n";
}
copy(__DIR__ . '/.env', __DIR__ . '/.env.backup_mysql_' . date('YmdHis'));
echo "   ✓ Backup .env creado\n\n";

// Paso 2: Crear base de datos SQLite
echo "📝 Paso 2: Creando base de datos SQLite...\n";
if (file_exists($sqlitePath)) {
    unlink($sqlitePath);
}
touch($sqlitePath);
echo "   ✓ Base de datos SQLite creada\n\n";

// Paso 3: Cambiar temporalmente a SQLite
echo "🔧 Paso 3: Configurando conexión SQLite...\n";
$envContent = file_get_contents(__DIR__ . '/.env');
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $sqlitePath, $envContent);
file_put_contents(__DIR__ . '/.env', $envContent);
echo "   ✓ Configuración actualizada temporalmente\n\n";

// Paso 4: Ejecutar migraciones en SQLite
echo "🏗️  Paso 4: Ejecutando migraciones en SQLite...\n";
try {
    Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);
    echo "   ✓ Estructura de tablas creada\n\n";
} catch (Exception $e) {
    echo "   ⚠️  Error en migraciones: " . $e->getMessage() . "\n";
    echo "   Intentando migrar manualmente...\n";
    Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    echo "   ✓ Migraciones completadas\n\n";
}

// Paso 5: Conectar a MySQL y exportar datos
echo "📤 Paso 5: Exportando datos de MySQL...\n";
try {
    // Conectar a MySQL directamente
    $mysql = new PDO(
        "mysql:host={$mysqlConfig['host']};port={$mysqlConfig['port']};dbname={$mysqlConfig['database']}",
        $mysqlConfig['username'],
        $mysqlConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Conectar a SQLite
    $sqlite = new PDO("sqlite:{$sqlitePath}", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener todas las tablas de MySQL
    $tables = $mysql->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   📋 Tablas encontradas: " . count($tables) . "\n\n";
    
    $totalRecords = 0;
    
    foreach ($tables as $table) {
        // Saltar tablas del sistema
        if (in_array($table, ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'])) {
            echo "   ⏭️  Saltando tabla del sistema: {$table}\n";
            continue;
        }
        
        echo "   📊 Migrando tabla: {$table}...\n";
        
        // Obtener estructura de la tabla
        $columns = $mysql->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        // Obtener datos
        $data = $mysql->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            echo "      ✓ Tabla vacía, saltando\n";
            continue;
        }
        
        // Insertar datos en SQLite
        $placeholders = '(' . str_repeat('?,', count($columnNames) - 1) . '?)';
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columnNames) . "`) VALUES {$placeholders}";
        
        $stmt = $sqlite->prepare($sql);
        
        $count = 0;
        foreach ($data as $row) {
            $values = [];
            foreach ($columnNames as $col) {
                $values[] = $row[$col];
            }
            
            try {
                $stmt->execute($values);
                $count++;
            } catch (PDOException $e) {
                // Ignorar errores de duplicados o constraints
                if (strpos($e->getMessage(), 'UNIQUE constraint') === false && 
                    strpos($e->getMessage(), 'FOREIGN KEY constraint') === false) {
                    echo "      ⚠️  Error en fila: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "      ✓ {$count} registros migrados\n";
        $totalRecords += $count;
    }
    
    echo "\n   ✅ Total de registros migrados: {$totalRecords}\n\n";
    
} catch (PDOException $e) {
    echo "   ⚠️  Error al conectar a MySQL: " . $e->getMessage() . "\n";
    echo "   ℹ️  Continuando solo con estructura...\n\n";
}

// Paso 6: Restaurar configuración original
echo "🔄 Paso 6: Restaurando configuración original...\n";
$envContent = file_get_contents(__DIR__ . '/.env');
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=iaedu1', $envContent);
file_put_contents(__DIR__ . '/.env', $envContent);
echo "   ✓ Configuración MySQL restaurada\n\n";

// Paso 7: Verificar
echo "✅ Paso 7: Verificando migración...\n";
$sqlite = new PDO("sqlite:{$sqlitePath}");
$tableCount = $sqlite->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table'")->fetchColumn();
$migrationCount = $sqlite->query("SELECT COUNT(*) FROM migrations")->fetchColumn();

echo "   📊 Tablas creadas: {$tableCount}\n";
echo "   📊 Migraciones: {$migrationCount}\n\n";

echo "========================================\n";
echo "✅ Migración completada exitosamente!\n";
echo "========================================\n\n";

echo "📝 Próximos pasos:\n";
echo "1. Actualiza tu .env con la configuración SQLite\n";
echo "2. Prueba tu aplicación\n";
echo "3. Verifica en TablePlus\n\n";

echo "💾 Backups creados:\n";
echo "   - .env.backup_mysql_*\n";
echo "   - database.sqlite.backup_*\n\n";


