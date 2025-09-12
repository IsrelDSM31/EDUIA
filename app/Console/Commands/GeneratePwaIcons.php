<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneratePwaIcons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pwa:generate-icons {--force : Forzar regeneración de íconos existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera todos los íconos necesarios para la PWA de IAEDU1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎨 Generando íconos PWA para IAEDU1...');

        $iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
        $publicPath = public_path();
        $generated = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($iconSizes));
        $progressBar->start();

        foreach ($iconSizes as $size) {
            $filename = "icon-{$size}x{$size}.png";
            $filepath = $publicPath . '/' . $filename;

            // Verificar si el archivo existe y no se fuerza la regeneración
            if (file_exists($filepath) && !$this->option('force')) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            try {
                $icon = $this->createIcon($size);
                if (imagepng($icon, $filepath)) {
                    $generated++;
                    $this->line("\n✅ Generado: $filename");
                } else {
                    $errors++;
                    $this->error("\n❌ Error al guardar: $filename");
                }
                imagedestroy($icon);
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Error al crear $filename: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('📊 Resumen de la generación:');
        $this->line("   ✅ Generados: $generated");
        $this->line("   ⏭️  Omitidos: $skipped");
        $this->line("   ❌ Errores: $errors");
        $this->line("   📁 Ubicación: $publicPath");

        if ($generated > 0) {
            $this->info('🎉 ¡Íconos generados correctamente!');
            $this->info('🔄 Recarga tu aplicación para ver los cambios.');
        }

        if ($errors > 0) {
            $this->error('⚠️  Algunos íconos no se pudieron generar.');
            return 1;
        }

        return 0;
    }

    /**
     * Crear un ícono con el tamaño especificado
     */
    private function createIcon($size)
    {
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
        $text = "IAEDU1";
        $fontSize = max(1, $size * 0.12);
        $textWidth = strlen($text) * $fontSize * 0.6;
        $textX = ($size - $textWidth) / 2;
        $textY = $size * 0.85;
        
        // Usar GD para texto simple
        imagestring($image, 5, $textX, $textY - 10, $text, $white);
        
        return $image;
    }
} 