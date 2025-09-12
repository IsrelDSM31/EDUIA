<?php
/**
 * Generador Automático de Íconos PWA - IAEDU1
 * Este script genera todos los íconos necesarios para la PWA
 */

// Configuración
$iconSizes = [
    72, 96, 128, 144, 152, 192, 384, 512
];

$outputDir = __DIR__; // Carpeta public/

// Función para crear ícono
function createIcon($size) {
    // Crear imagen
    $image = imagecreatetruecolor($size, $size);
    
    // Colores
    $darkRed = imagecolorallocate($image, 139, 21, 56);   // #8B1538
    $red = imagecolorallocate($image, 165, 42, 42);       // #A52A2A
    $white = imagecolorallocate($image, 255, 255, 255);   // Blanco
    $gold = imagecolorallocate($image, 255, 215, 0);      // #FFD700
    $orange = imagecolorallocate($image, 255, 107, 53);   // #FF6B35
    
    // Fondo degradado
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $ratio = ($x + $y) / ($size * 2);
            $r = $darkRed[0] + ($red[0] - $darkRed[0]) * $ratio;
            $g = $darkRed[1] + ($red[1] - $darkRed[1]) * $ratio;
            $b = $darkRed[2] + ($red[2] - $darkRed[2]) * $ratio;
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
    
    // Cuerpo del lápiz
    imagefilledrectangle($image, $pencilX, $pencilY, $pencilX + $pencilWidth, $pencilY + $pencilLength, $gold);
    
    // Punta del lápiz
    $points = [
        $pencilX, $pencilY,
        $pencilX + $pencilWidth, $pencilY,
        $pencilX + $pencilWidth * 0.5, $pencilY - $size * 0.08
    ];
    imagefilledpolygon($image, $points, 3, $orange);
    
    // Texto "IAEDU1"
    $fontSize = $size * 0.12;
    $text = "IAEDU1";
    $textWidth = strlen($text) * $fontSize * 0.6;
    $textX = ($size - $textWidth) / 2;
    $textY = $size * 0.85;
    
    // Usar GD para texto simple
    imagestring($image, 5, $textX, $textY - 10, $text, $white);
    
    return $image;
}

// Generar íconos
$generated = [];
$errors = [];

foreach ($iconSizes as $size) {
    $filename = "icon-{$size}x{$size}.png";
    $filepath = $outputDir . '/' . $filename;
    
    try {
        $icon = createIcon($size);
        if (imagepng($icon, $filepath)) {
            $generated[] = $filename;
        } else {
            $errors[] = "Error al guardar $filename";
        }
        imagedestroy($icon);
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
    'error_count' => count($errors)
]);
?> 