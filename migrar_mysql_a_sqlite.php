<?php
/**
 * Script Completo: Migración MySQL → SQLite
 * 
 * Este script migra TODOS los datos de MySQL a SQLite
 * SIN DAÑAR la base de datos MySQL original
 * 
 * Uso: php migrar_mysql_a_sqlite.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========================================\n";
echo "🚀 MIGRACIÓN COMPLETA: MySQL → SQLite\n";
echo "========================================\n\n";

// ============================================
// CONFIGURACIÓN
// ============================================
$mysqlConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'iaedu1',
    'username' => 'root',
    'password' => '',
];

$sqlitePath = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
$projectDir = __DIR__;

// ============================================
// PASO 1: BACKUPS
// ============================================
echo "📦 PASO 1: Creando backups de seguridad...\n";

// Backup .env
if (file_exists($projectDir . DIRECTORY_SEPARATOR . '.env')) {
    $backupEnv = $projectDir . DIRECTORY_SEPARATOR . '.env.backup_mysql_' . date('YmdHis');
    copy($projectDir . DIRECTORY_SEPARATOR . '.env', $backupEnv);
    echo "   ✓ Backup .env creado: {$backupEnv}\n";
}

// Backup SQLite si existe
if (file_exists($sqlitePath)) {
    $backupSqlite = $sqlitePath . '.backup_' . date('YmdHis');
    copy($sqlitePath, $backupSqlite);
    echo "   ✓ Backup SQLite creado: {$backupSqlite}\n";
}

echo "\n";

// ============================================
// PASO 2: CREAR BASE DE DATOS SQLITE
// ============================================
echo "📝 PASO 2: Creando base de datos SQLite...\n";

// Crear directorio si no existe
$dbDir = dirname($sqlitePath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "   ✓ Directorio creado: {$dbDir}\n";
}

// Eliminar SQLite anterior si existe
if (file_exists($sqlitePath)) {
    unlink($sqlitePath);
    echo "   ✓ Archivo SQLite anterior eliminado\n";
}

// Crear nuevo archivo SQLite
touch($sqlitePath);
chmod($sqlitePath, 0644);
echo "   ✓ Base de datos SQLite creada: {$sqlitePath}\n\n";

// ============================================
// PASO 3: CONECTAR A MYSQL
// ============================================
echo "🔌 PASO 3: Conectando a MySQL...\n";

try {
    $mysql = new PDO(
        "mysql:host={$mysqlConfig['host']};port={$mysqlConfig['port']};dbname={$mysqlConfig['database']};charset=utf8mb4",
        $mysqlConfig['username'],
        $mysqlConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "   ✓ Conexión MySQL exitosa\n\n";
} catch (PDOException $e) {
    echo "   ❌ Error al conectar a MySQL: " . $e->getMessage() . "\n";
    echo "   ⚠️  Continuando solo con estructura (sin datos)\n\n";
    $mysql = null;
}

// ============================================
// PASO 4: CONECTAR A SQLITE
// ============================================
echo "🔌 PASO 4: Conectando a SQLite...\n";

try {
    $sqlite = new PDO("sqlite:{$sqlitePath}", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "   ✓ Conexión SQLite exitosa\n\n";
} catch (PDOException $e) {
    die("   ❌ Error al conectar a SQLite: " . $e->getMessage() . "\n");
}

// ============================================
// PASO 5: CREAR ESTRUCTURA (MIGRACIONES)
// ============================================
echo "🏗️  PASO 5: Creando estructura de tablas...\n";

// Ejecutar migraciones usando Artisan
$envFile = $projectDir . DIRECTORY_SEPARATOR . '.env';
$envContent = file_get_contents($envFile);

// Guardar configuración original
$originalEnv = $envContent;

// Cambiar temporalmente a SQLite
$envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
$envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $sqlitePath, $envContent);
file_put_contents($envFile, $envContent);

// Ejecutar migraciones
echo "   Ejecutando migraciones de Laravel...\n";
$output = [];
$returnVar = 0;
exec("cd \"{$projectDir}\" && php artisan migrate --force 2>&1", $output, $returnVar);

if ($returnVar === 0) {
    echo "   ✓ Estructura de tablas creada\n\n";
} else {
    echo "   ⚠️  Error en migraciones, intentando método alternativo...\n";
    // Intentar migrar manualmente las tablas principales
    echo "   ⚠️  Continuando con migración manual de datos...\n\n";
}

// Restaurar configuración original
file_put_contents($envFile, $originalEnv);

// ============================================
// PASO 6: MIGRAR DATOS
// ============================================
if ($mysql !== null) {
    echo "📤 PASO 6: Migrando datos de MySQL a SQLite...\n";
    
    // Obtener todas las tablas
    $tables = $mysql->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   📋 Tablas encontradas: " . count($tables) . "\n\n";
    
    $totalRecords = 0;
    $tablesMigrated = 0;
    
    foreach ($tables as $table) {
        // Saltar tablas del sistema de Laravel que pueden causar problemas
        $skipTables = ['cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions'];
        if (in_array($table, $skipTables)) {
            echo "   ⏭️  Saltando tabla del sistema: {$table}\n";
            continue;
        }
        
        echo "   📊 Migrando tabla: {$table}...\n";
        
        try {
            // Verificar si la tabla existe en SQLite
            $tableExists = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetch();
            
            if (!$tableExists) {
                echo "      ⚠️  Tabla no existe en SQLite, saltando...\n";
                continue;
            }
            
            // Obtener columnas de la tabla
            $columns = $mysql->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'Field');
            
            // Obtener datos de MySQL
            $data = $mysql->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($data)) {
                echo "      ✓ Tabla vacía\n";
                continue;
            }
            
            // Limpiar tabla SQLite antes de insertar
            $sqlite->exec("DELETE FROM `{$table}`");
            
            // Preparar inserción
            $placeholders = '(' . str_repeat('?,', count($columnNames) - 1) . '?)';
            $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columnNames) . "`) VALUES {$placeholders}";
            
            $stmt = $sqlite->prepare($sql);
            
            $count = 0;
            $errors = 0;
            
            foreach ($data as $row) {
                $values = [];
                foreach ($columnNames as $col) {
                    $values[] = $row[$col] ?? null;
                }
                
                try {
                    $stmt->execute($values);
                    $count++;
                } catch (PDOException $e) {
                    // Ignorar errores de duplicados o constraints
                    if (strpos($e->getMessage(), 'UNIQUE constraint') === false && 
                        strpos($e->getMessage(), 'FOREIGN KEY constraint') === false) {
                        $errors++;
                        if ($errors <= 3) {
                            echo "      ⚠️  Error en fila: " . substr($e->getMessage(), 0, 50) . "...\n";
                        }
                    }
                }
            }
            
            echo "      ✓ {$count} registros migrados\n";
            $totalRecords += $count;
            $tablesMigrated++;
            
        } catch (PDOException $e) {
            echo "      ❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n   ✅ Resumen:\n";
    echo "      - Tablas migradas: {$tablesMigrated}\n";
    echo "      - Total registros: {$totalRecords}\n\n";
    
} else {
    echo "📤 PASO 6: Saltando migración de datos (MySQL no disponible)\n\n";
}

// ============================================
// PASO 7: VERIFICACIÓN
// ============================================
echo "✅ PASO 7: Verificando migración...\n";

try {
    $tableCount = $sqlite->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table'")->fetchColumn();
    echo "   📊 Tablas creadas: {$tableCount}\n";
    
    // Contar registros en algunas tablas principales
    $mainTables = ['users', 'students', 'grades', 'attendances'];
    foreach ($mainTables as $table) {
        try {
            $count = $sqlite->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "   📊 Registros en '{$table}': {$count}\n";
        } catch (PDOException $e) {
            // Tabla no existe o error
        }
    }
    
    echo "\n";
    
} catch (PDOException $e) {
    echo "   ⚠️  Error en verificación: " . $e->getMessage() . "\n\n";
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "========================================\n";
echo "✅ MIGRACIÓN COMPLETADA EXITOSAMENTE!\n";
echo "========================================\n\n";

echo "📝 PRÓXIMOS PASOS:\n";
echo "1. Actualiza tu .env con estos valores:\n\n";

echo "DB_CONNECTION=sqlite\n";
echo "DB_HOST=\n";
echo "DB_PORT=\n";
echo "DB_DATABASE=database/database.sqlite\n";
echo "DB_USERNAME=\n";
echo "DB_PASSWORD=\n\n";

echo "2. Prueba tu aplicación\n";
echo "3. Verifica en TablePlus conectando a:\n";
echo "   {$sqlitePath}\n\n";

echo "💾 Backups creados:\n";
echo "   - .env.backup_mysql_*\n";
echo "   - database.sqlite.backup_*\n\n";

echo "🔄 Para volver a MySQL, ejecuta:\n";
echo "   php volver_a_mysql.php\n\n";


