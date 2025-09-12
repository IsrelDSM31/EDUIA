<?php
/**
 * Generador de Íconos Básicos - IAEDU1
 * Funciona sin extensión GD
 */

// Configuración
$iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Función para crear un PNG básico
function createBasicPNG($size, $filename) {
    // Crear un PNG básico con color sólido
    $width = $size;
    $height = $size;
    
    // Header PNG
    $png = "\x89PNG\r\n\x1a\n";
    
    // IHDR chunk (13 bytes)
    $ihdr = pack('N', $width) . pack('N', $height) . "\x08\x02\x00\x00\x00";
    $png .= pack('N', 13) . 'IHDR' . $ihdr . pack('N', crc32('IHDR' . $ihdr));
    
    // IDAT chunk (datos de imagen)
    $data = '';
    for ($y = 0; $y < $height; $y++) {
        $data .= "\x00"; // Filtro
        for ($x = 0; $x < $width; $x++) {
            // Color rojo sólido (#8B1538)
            $data .= "\x8B\x15\x38";
        }
    }
    
    $compressed = gzcompress($data);
    $png .= pack('N', strlen($compressed)) . 'IDAT' . $compressed . pack('N', crc32('IDAT' . $compressed));
    
    // IEND chunk
    $png .= pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
    
    return file_put_contents($filename, $png) !== false;
}

// Generar íconos
$generated = [];
$errors = [];

foreach ($iconSizes as $size) {
    $filename = "icon-{$size}x{$size}.png";
    $filepath = __DIR__ . '/' . $filename;
    
    try {
        if (createBasicPNG($size, $filepath)) {
            $generated[] = $filename;
        } else {
            $errors[] = "Error al guardar $filename";
        }
    } catch (Exception $e) {
        $errors[] = "Error al crear $filename: " . $e->getMessage();
    }
}

// Respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => count($errors) === 0,
    'generated' => $generated,
    'errors' => $errors,
    'total' => count($iconSizes),
    'generated_count' => count($generated),
    'error_count' => count($errors),
    'message' => count($errors) === 0 ? 
        'Íconos básicos generados correctamente' : 
        'Algunos íconos no se pudieron generar'
]);
?> 