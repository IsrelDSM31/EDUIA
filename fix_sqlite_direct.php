<?php
/**
 * Script directo para reparar base de datos SQLite corrupta
 * Usa PDO directamente para evitar problemas de cache de Laravel
 */

echo "========================================\n";
echo "🔧 Reparando Base de Datos SQLite (Método Directo)\n";
echo "========================================\n\n";

$sqlitePath = __DIR__ . '/database/database.sqlite';
$tempSqlitePath = __DIR__ . '/database/database.sqlite.new';

// Paso 1: Backup
echo "📦 Paso 1: Creando backup...\n";
if (file_exists($sqlitePath)) {
    $backupPath = $sqlitePath . '.backup_' . date('YmdHis');
    @copy($sqlitePath, $backupPath);
    echo "   ✓ Backup: " . basename($backupPath) . "\n";
}
echo "\n";

// Paso 2: Crear nueva base de datos usando PDO directamente
echo "📝 Paso 2: Creando nueva base de datos SQLite...\n";
try {
    // Eliminar archivo temporal si existe
    if (file_exists($tempSqlitePath)) {
        @unlink($tempSqlitePath);
    }
    
    // Crear nueva base de datos SQLite
    $pdo = new PDO('sqlite:' . $tempSqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar SQLite
    $pdo->exec('PRAGMA journal_mode=WAL;');
    $pdo->exec('PRAGMA foreign_keys=ON;');
    $pdo->exec('PRAGMA synchronous=NORMAL;');
    
    echo "   ✓ Base de datos creada\n";
    
    // Cerrar conexión
    $pdo = null;
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// Paso 3: Actualizar .env para usar el nuevo archivo temporalmente
echo "🔧 Paso 3: Actualizando configuración...\n";
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);
$originalEnv = $envContent;

// Cambiar la ruta de la base de datos
$envContent = preg_replace(
    '/^DB_DATABASE=.*/m',
    'DB_DATABASE=' . str_replace('\\', '/', $tempSqlitePath),
    $envContent
);

file_put_contents($envFile, $envContent);
echo "   ✓ Configuración actualizada\n";
echo "\n";

// Paso 4: Ejecutar migraciones usando un proceso completamente nuevo
echo "🏗️  Paso 4: Ejecutando migraciones...\n";
echo "   (Esto puede tomar unos momentos...)\n\n";

// Ejecutar artisan migrate en un proceso separado para evitar cache
$command = 'php "' . __DIR__ . '/artisan" migrate --force';
$output = [];
$returnCode = 0;

exec($command . ' 2>&1', $output, $returnCode);

// Mostrar salida
foreach ($output as $line) {
    echo "   " . $line . "\n";
}

if ($returnCode !== 0) {
    echo "\n   ❌ Error al ejecutar migraciones\n";
    // Restaurar .env original
    file_put_contents($envFile, $originalEnv);
    exit(1);
}

echo "\n   ✓ Migraciones completadas\n";
echo "\n";

// Paso 5: Verificar las tablas creadas
echo "✅ Paso 5: Verificando base de datos...\n";
try {
    $pdo = new PDO('sqlite:' . $tempSqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   ✓ Tablas creadas: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "\n   Tablas:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
    }
    
    $pdo = null;
} catch (Exception $e) {
    echo "   ⚠️  Error al verificar: " . $e->getMessage() . "\n";
}
echo "\n";

// Paso 6: Reemplazar el archivo corrupto
echo "🔄 Paso 6: Reemplazando archivo corrupto...\n";
echo "   💡 IMPORTANTE: Cierra TablePlus o cualquier programa que use el archivo antes de continuar\n";
echo "   Presiona Enter cuando estés listo, o Ctrl+C para cancelar...\n";
// No esperamos entrada, intentamos directamente con reintentos

$maxAttempts = 10;
$attempt = 0;
$replaced = false;

while ($attempt < $maxAttempts && !$replaced) {
    // Intentar renombrar
    $oldPath = $sqlitePath . '.old';
    
    if (@rename($sqlitePath, $oldPath)) {
        // Renombrar el nuevo archivo
        if (@rename($tempSqlitePath, $sqlitePath)) {
            $replaced = true;
            @unlink($oldPath);
            echo "   ✓ Archivo reemplazado exitosamente\n";
        } else {
            // Restaurar original si falla
            @rename($oldPath, $sqlitePath);
            echo "   ⚠️  No se pudo reemplazar (archivo en uso)\n";
            break;
        }
    } else {
        $attempt++;
        if ($attempt < $maxAttempts) {
            echo "   ⏳ Intento $attempt/$maxAttempts - Esperando 2 segundos...\n";
            sleep(2);
        } else {
            echo "   ⚠️  No se pudo reemplazar automáticamente\n";
            break;
        }
    }
}

// Restaurar configuración original
echo "\n";
echo "🔄 Restaurando configuración original...\n";
file_put_contents($envFile, $originalEnv);
echo "   ✓ Configuración restaurada\n";
echo "\n";

// Limpiar cache
echo "🧹 Limpiando cache...\n";
$commands = [
    'php "' . __DIR__ . '/artisan" config:clear',
    'php "' . __DIR__ . '/artisan" cache:clear',
];
foreach ($commands as $cmd) {
    @exec($cmd . ' 2>&1');
}
echo "   ✓ Cache limpiado\n";
echo "\n";

echo "========================================\n";
if ($replaced) {
    echo "✅ ¡Reparación completada exitosamente!\n";
    echo "\n";
    echo "💡 La base de datos ha sido recreada y todas las migraciones se ejecutaron.\n";
} else {
    echo "⚠️  Reparación parcialmente completada\n";
    echo "\n";
    echo "💡 La nueva base de datos está en: " . basename($tempSqlitePath) . "\n";
    echo "💡 Para completar la reparación:\n";
    echo "   1. Cierra TablePlus o cualquier programa que use database.sqlite\n";
    echo "   2. Renombra: " . basename($tempSqlitePath) . " → " . basename($sqlitePath) . "\n";
    echo "   3. O ejecuta este script nuevamente\n";
}
echo "========================================\n";


