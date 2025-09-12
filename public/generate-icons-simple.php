<?php
/**
 * Generador Simple de Íconos PWA - IAEDU1
 * No requiere extensión GD
 */

// Configuración
$iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Función para crear ícono SVG y convertirlo a PNG
function createIconSVG($size) {
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#8B1538;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#A52A2A;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Fondo con gradiente -->
    <rect width="' . $size . '" height="' . $size . '" rx="' . ($size * 0.1) . '" fill="url(#grad)"/>
    
    <!-- Libro -->
    <rect x="' . ($size * 0.3) . '" y="' . ($size * 0.35) . '" width="' . ($size * 0.4) . '" height="' . ($size * 0.3) . '" fill="white" stroke="white" stroke-width="' . ($size * 0.02) . '"/>
    
    <!-- Líneas del libro -->
    <line x1="' . ($size * 0.35) . '" y1="' . ($size * 0.425) . '" x2="' . ($size * 0.65) . '" y2="' . ($size * 0.425) . '" stroke="white" stroke-width="' . ($size * 0.01) . '"/>
    <line x1="' . ($size * 0.35) . '" y1="' . ($size * 0.475) . '" x2="' . ($size * 0.65) . '" y2="' . ($size * 0.475) . '" stroke="white" stroke-width="' . ($size * 0.01) . '"/>
    <line x1="' . ($size * 0.35) . '" y1="' . ($size * 0.525) . '" x2="' . ($size * 0.65) . '" y2="' . ($size * 0.525) . '" stroke="white" stroke-width="' . ($size * 0.01) . '"/>
    
    <!-- Lápiz -->
    <rect x="' . ($size * 0.6) . '" y="' . ($size * 0.25) . '" width="' . ($size * 0.04) . '" height="' . ($size * 0.5) . '" fill="#FFD700"/>
    <polygon points="' . ($size * 0.6) . ',' . ($size * 0.25) . ' ' . ($size * 0.64) . ',' . ($size * 0.25) . ' ' . ($size * 0.62) . ',' . ($size * 0.17) . '" fill="#FF6B35"/>
    
    <!-- Texto IAEDU1 -->
    <text x="' . ($size / 2) . '" y="' . ($size * 0.85) . '" font-family="Arial, sans-serif" font-size="' . ($size * 0.12) . '" font-weight="bold" text-anchor="middle" fill="white">IAEDU1</text>
</svg>';
    
    return $svg;
}

// Función para convertir SVG a PNG usando ImageMagick o alternativa
function svgToPng($svg, $size, $filename) {
    $svgFile = tempnam(sys_get_temp_dir(), 'icon_') . '.svg';
    $pngFile = tempnam(sys_get_temp_dir(), 'icon_') . '.png';
    
    // Guardar SVG temporal
    file_put_contents($svgFile, $svg);
    
    // Intentar convertir con ImageMagick
    $command = "magick convert \"$svgFile\" -resize {$size}x{$size} \"$pngFile\" 2>&1";
    $output = shell_exec($command);
    
    if (file_exists($pngFile)) {
        $pngContent = file_get_contents($pngFile);
        file_put_contents($filename, $pngContent);
        
        // Limpiar archivos temporales
        unlink($svgFile);
        unlink($pngFile);
        
        return true;
    }
    
    // Si ImageMagick no funciona, crear un PNG simple
    return createSimplePNG($size, $filename);
}

// Función para crear PNG simple sin dependencias
function createSimplePNG($size, $filename) {
    // Crear un PNG básico con colores sólidos
    $width = $size;
    $height = $size;
    
    // Header PNG
    $png = "\x89PNG\r\n\x1a\n";
    
    // IHDR chunk
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
    
    file_put_contents($filename, $png);
    return true;
}

// Función principal que crea íconos usando múltiples métodos
function createIcon($size, $filename) {
    // Método 1: Intentar con GD si está disponible
    if (function_exists('imagecreatetruecolor')) {
        return createIconGD($size, $filename);
    }
    
    // Método 2: Intentar con SVG + conversión
    $svg = createIconSVG($size);
    if (svgToPng($svg, $size, $filename)) {
        return true;
    }
    
    // Método 3: PNG simple como fallback
    return createSimplePNG($size, $filename);
}

// Función GD (si está disponible)
function createIconGD($size, $filename) {
    $image = imagecreatetruecolor($size, $size);
    
    // Colores
    $darkRed = imagecolorallocate($image, 139, 21, 56);
    $red = imagecolorallocate($image, 165, 42, 42);
    $white = imagecolorallocate($image, 255, 255, 255);
    $gold = imagecolorallocate($image, 255, 215, 0);
    $orange = imagecolorallocate($image, 255, 107, 53);
    
    // Fondo degradado
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $ratio = ($x + $y) / ($size * 2);
            $r = 139 + (165 - 139) * $ratio;
            $g = 21 + (42 - 21) * $ratio;
            $b = 56 + (42 - 56) * $ratio;
            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $y, $color);
        }
    }
    
    // Libro
    $bookWidth = $size * 0.4;
    $bookHeight = $size * 0.3;
    $bookX = $size * 0.3;
    $bookY = $size * 0.35;
    
    imagefilledrectangle($image, $bookX, $bookY, $bookX + $bookWidth, $bookY + $bookHeight, $white);
    imagerectangle($image, $bookX, $bookY, $bookX + $bookWidth, $bookY + $bookHeight, $white);
    
    // Líneas del libro
    $lineSpacing = $bookHeight / 4;
    for ($i = 1; $i < 4; $i++) {
        $y = $bookY + $lineSpacing * $i;
        imageline($image, $bookX + $size * 0.05, $y, $bookX + $bookWidth - $size * 0.05, $y, $white);
    }
    
    // Lápiz
    $pencilLength = $size * 0.5;
    $pencilWidth = $size * 0.04;
    $pencilX = $size * 0.6;
    $pencilY = $size * 0.25;
    
    imagefilledrectangle($image, $pencilX, $pencilY, $pencilX + $pencilWidth, $pencilY + $pencilLength, $gold);
    
    $points = [
        $pencilX, $pencilY,
        $pencilX + $pencilWidth, $pencilY,
        $pencilX + $pencilWidth * 0.5, $pencilY - $size * 0.08
    ];
    imagefilledpolygon($image, $points, 3, $orange);
    
    // Texto
    $text = "IAEDU1";
    imagestring($image, 5, ($size - strlen($text) * 8) / 2, $size * 0.75, $text, $white);
    
    $result = imagepng($image, $filename);
    imagedestroy($image);
    
    return $result;
}

// Generar íconos
$generated = [];
$errors = [];
$methods = [];

foreach ($iconSizes as $size) {
    $filename = "icon-{$size}x{$size}.png";
    $filepath = __DIR__ . '/' . $filename;
    
    try {
        if (createIcon($size, $filepath)) {
            $generated[] = $filename;
            $methods[] = function_exists('imagecreatetruecolor') ? 'GD' : 'SVG/PNG';
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
    'methods' => $methods,
    'total' => count($iconSizes),
    'generated_count' => count($generated),
    'error_count' => count($errors),
    'gd_available' => function_exists('imagecreatetruecolor'),
    'message' => count($errors) === 0 ? 
        'Íconos generados correctamente' : 
        'Algunos íconos no se pudieron generar'
]);
?> 