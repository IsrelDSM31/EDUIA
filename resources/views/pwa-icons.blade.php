<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Generador de Íconos PWA - IAEDU1</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#8B1538">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="IAEDU1">
    <meta name="msapplication-TileColor" content="#8B1538">
    <meta name="msapplication-tap-highlight" content="no">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icon-152x152.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .icon-item {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .icon-canvas {
            border: 1px solid #ccc;
            margin: 10px 0;
        }
        button {
            background: #8B1538;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #A52A2A;
        }
        .download-all {
            background: #4CAF50;
            font-size: 16px;
            padding: 15px 30px;
        }
        .download-all:hover {
            background: #45a049;
        }
        .back-button {
            background: #8B1538;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background: #A52A2A;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-button">← Volver a IAEDU1</a>
        
        <h1>🎨 Generador de Íconos PWA - IAEDU1</h1>
        <p>Esta herramienta genera automáticamente todos los íconos necesarios para tu PWA.</p>
        
        <button class="download-all" onclick="generateAllIcons()">🔄 Generar Todos los Íconos</button>
        
        <div class="icon-grid" id="iconGrid">
            <!-- Los íconos se generarán aquí -->
        </div>
    </div>

    <script>
        const sizes = [72, 96, 128, 144, 152, 192, 384, 512];
        
        function createIcon(size) {
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            
            // Fondo
            ctx.fillStyle = '#8B1538';
            ctx.fillRect(0, 0, size, size);
            
            // Logo simplificado
            const centerX = size / 2;
            const centerY = size / 2;
            const logoSize = size * 0.6;
            
            // Libro base
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(centerX - logoSize/2, centerY - logoSize/3, logoSize, logoSize * 0.7);
            
            // Páginas
            ctx.fillStyle = '#F0F0F0';
            ctx.fillRect(centerX - logoSize/2 + 5, centerY - logoSize/3 + 5, logoSize - 10, logoSize * 0.7 - 10);
            
            // Líneas de texto
            ctx.fillStyle = '#8B1538';
            ctx.lineWidth = size * 0.02;
            ctx.beginPath();
            ctx.moveTo(centerX - logoSize/3, centerY - logoSize/6);
            ctx.lineTo(centerX + logoSize/3, centerY - logoSize/6);
            ctx.moveTo(centerX - logoSize/3, centerY);
            ctx.lineTo(centerX + logoSize/3, centerY);
            ctx.moveTo(centerX - logoSize/3, centerY + logoSize/6);
            ctx.lineTo(centerX + logoSize/3, centerY + logoSize/6);
            ctx.stroke();
            
            // Texto IAEDU
            ctx.fillStyle = '#8B1538';
            ctx.font = `bold ${size * 0.15}px Arial`;
            ctx.textAlign = 'center';
            ctx.fillText('IAEDU', centerX, centerY + logoSize/2 + size * 0.1);
            
            return canvas;
        }
        
        function downloadIcon(canvas, filename) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
        
        function generateAllIcons() {
            const grid = document.getElementById('iconGrid');
            grid.innerHTML = '';
            
            sizes.forEach(size => {
                const iconItem = document.createElement('div');
                iconItem.className = 'icon-item';
                
                const canvas = createIcon(size);
                canvas.className = 'icon-canvas';
                
                const label = document.createElement('p');
                label.textContent = `${size}x${size}`;
                
                const downloadBtn = document.createElement('button');
                downloadBtn.textContent = '📥 Descargar';
                downloadBtn.onclick = () => downloadIcon(canvas, `icon-${size}x${size}.png`);
                
                iconItem.appendChild(label);
                iconItem.appendChild(canvas);
                iconItem.appendChild(downloadBtn);
                
                grid.appendChild(iconItem);
            });
        }
        
        // Generar íconos al cargar la página
        window.onload = generateAllIcons;
    </script>
</body>
</html> 