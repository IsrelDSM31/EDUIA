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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #8B1538 0%, #A52A2A 100%);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .icon-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .icon-item canvas {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            margin-bottom: 10px;
        }
        button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .download-all {
            background: #4CAF50;
            font-size: 18px;
            padding: 15px 30px;
            font-weight: bold;
        }
        .download-all:hover {
            background: #45a049;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .status.success {
            background: rgba(76, 175, 80, 0.3);
            border: 1px solid #4CAF50;
        }
        .status.error {
            background: rgba(244, 67, 54, 0.3);
            border: 1px solid #f44336;
        }
        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        .instructions {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-button">← Volver a IAEDU1</a>
        
        <h1>🎨 Generador de Íconos PWA - IAEDU1</h1>
        <p>Esta página genera todos los íconos necesarios para la PWA. Haz clic en "Generar y Descargar Todos" para crear todos los íconos faltantes.</p>

        <div class="instructions">
            <h3>📋 Instrucciones:</h3>
            <ol>
                <li>Haz clic en <strong>"📥 Generar y Descargar Todos los Íconos"</strong></li>
                <li>Se descargarán 8 archivos PNG con diferentes tamaños</li>
                <li>Mueve todos los archivos descargados a la carpeta <code>public/</code> de tu proyecto</li>
                <li>Recarga la página principal para ver los íconos funcionando</li>
            </ol>
        </div>

        <div id="status" class="status"></div>

        <button class="download-all" onclick="generateAllIcons()">
            📥 Generar y Descargar Todos los Íconos
        </button>

        <button onclick="previewIcons()">
            👁️ Previsualizar Íconos
        </button>

        <button onclick="checkExistingIcons()">
            🔍 Verificar Íconos Existentes
        </button>

        <button onclick="generateIconsServer()" style="background: #2196F3;">
            ⚡ Generar Íconos en Servidor (Automático)
        </button>

        <button onclick="generateBasicIcons()" style="background: #FF9800;">
            🔧 Generar Íconos Básicos (Sin GD)
        </button>

        <div id="iconGrid" class="icon-grid"></div>
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registrado: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registro falló: ', registrationError);
                    });
            });
        }

        const iconSizes = [
            { size: 72, name: 'icon-72x72.png' },
            { size: 96, name: 'icon-96x96.png' },
            { size: 128, name: 'icon-128x128.png' },
            { size: 144, name: 'icon-144x144.png' },
            { size: 152, name: 'icon-152x152.png' },
            { size: 192, name: 'icon-192x192.png' },
            { size: 384, name: 'icon-384x384.png' },
            { size: 512, name: 'icon-512x512.png' }
        ];

        function createIcon(size, name) {
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');

            // Fondo degradado
            const gradient = ctx.createLinearGradient(0, 0, size, size);
            gradient.addColorStop(0, '#8B1538');
            gradient.addColorStop(1, '#A52A2A');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, size, size);

            // Borde redondeado
            ctx.globalCompositeOperation = 'destination-in';
            ctx.beginPath();
            ctx.roundRect(0, 0, size, size, size * 0.1);
            ctx.fill();

            // Restaurar composición
            ctx.globalCompositeOperation = 'source-over';

            // Símbolo de educación (libro y lápiz)
            ctx.fillStyle = 'white';
            ctx.strokeStyle = 'white';
            ctx.lineWidth = size * 0.02;

            // Libro
            const bookWidth = size * 0.4;
            const bookHeight = size * 0.3;
            const bookX = size * 0.3;
            const bookY = size * 0.35;

            ctx.fillRect(bookX, bookY, bookWidth, bookHeight);
            ctx.strokeRect(bookX, bookY, bookWidth, bookHeight);

            // Líneas del libro
            const lineSpacing = bookHeight / 4;
            for (let i = 1; i < 4; i++) {
                ctx.beginPath();
                ctx.moveTo(bookX + size * 0.05, bookY + lineSpacing * i);
                ctx.lineTo(bookX + bookWidth - size * 0.05, bookY + lineSpacing * i);
                ctx.stroke();
            }

            // Lápiz
            const pencilLength = size * 0.5;
            const pencilWidth = size * 0.04;
            const pencilX = size * 0.6;
            const pencilY = size * 0.25;

            // Cuerpo del lápiz
            ctx.fillStyle = '#FFD700';
            ctx.fillRect(pencilX, pencilY, pencilWidth, pencilLength);

            // Punta del lápiz
            ctx.fillStyle = '#FF6B35';
            ctx.beginPath();
            ctx.moveTo(pencilX, pencilY);
            ctx.lineTo(pencilX + pencilWidth, pencilY);
            ctx.lineTo(pencilX + pencilWidth * 0.5, pencilY - size * 0.08);
            ctx.closePath();
            ctx.fill();

            // Texto "IAEDU1"
            ctx.fillStyle = 'white';
            ctx.font = `bold ${size * 0.12}px Arial`;
            ctx.textAlign = 'center';
            ctx.fillText('IAEDU1', size / 2, size * 0.85);

            return canvas;
        }

        function downloadCanvas(canvas, filename) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        function generateAllIcons() {
            const status = document.getElementById('status');
            status.className = 'status';
            status.textContent = 'Generando íconos...';

            iconSizes.forEach((icon, index) => {
                setTimeout(() => {
                    const canvas = createIcon(icon.size, icon.name);
                    downloadCanvas(canvas, icon.name);
                    
                    if (index === iconSizes.length - 1) {
                        status.className = 'status success';
                        status.textContent = '✅ Todos los íconos han sido generados y descargados correctamente. Ahora mueve los archivos a la carpeta public/ de tu proyecto.';
                    }
                }, index * 500); // Descarga cada 500ms para evitar bloqueos
            });
        }

        function previewIcons() {
            const grid = document.getElementById('iconGrid');
            grid.innerHTML = '';

            iconSizes.forEach(icon => {
                const canvas = createIcon(icon.size, icon.name);
                
                const item = document.createElement('div');
                item.className = 'icon-item';
                
                const title = document.createElement('h3');
                title.textContent = `${icon.size}x${icon.size}`;
                
                const downloadBtn = document.createElement('button');
                downloadBtn.textContent = '📥 Descargar';
                downloadBtn.onclick = () => downloadCanvas(canvas, icon.name);
                
                item.appendChild(title);
                item.appendChild(canvas);
                item.appendChild(downloadBtn);
                
                grid.appendChild(item);
            });
        }

        function checkExistingIcons() {
            const status = document.getElementById('status');
            status.className = 'status';
            status.textContent = 'Verificando íconos existentes...';

            let existingCount = 0;
            let totalCount = iconSizes.length;

            iconSizes.forEach((icon, index) => {
                const img = new Image();
                img.onload = function() {
                    existingCount++;
                    checkComplete();
                };
                img.onerror = function() {
                    checkComplete();
                };
                img.src = '/' + icon.name;
            });

            function checkComplete() {
                if (existingCount + (totalCount - iconSizes.length) === totalCount) {
                    if (existingCount === totalCount) {
                        status.className = 'status success';
                        status.textContent = `✅ Todos los íconos están presentes (${existingCount}/${totalCount})`;
                    } else {
                        status.className = 'status error';
                        status.textContent = `❌ Faltan ${totalCount - existingCount} íconos (${existingCount}/${totalCount}). Usa el botón "Generar y Descargar Todos" para crearlos.`;
                    }
                }
            }
        }

        function generateIconsServer() {
            const status = document.getElementById('status');
            status.className = 'status';
            status.textContent = 'Generando íconos en el servidor...';

            // Intentar primero el generador simple
            fetch('/generate-icons-simple.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        status.className = 'status success';
                        status.textContent = `✅ Se generaron ${data.generated_count} íconos correctamente usando ${data.methods.join(', ')}. Recarga la página para ver los cambios.`;
                        
                        // Mostrar íconos generados
                        const generatedList = data.generated.join(', ');
                        status.innerHTML += `<br><small>Íconos generados: ${generatedList}</small>`;
                        
                        // Mostrar método usado
                        if (data.gd_available) {
                            status.innerHTML += `<br><small>Método: GD (extensión PHP)</small>`;
                        } else {
                            status.innerHTML += `<br><small>Método: SVG/PNG (sin dependencias)</small>`;
                        }
                        
                        // Recargar automáticamente después de 3 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else {
                        // Si falla, intentar con el generador original
                        return fetch('/generate-icons.php');
                    }
                })
                .then(response => {
                    if (response && response.json) {
                        return response.json();
                    }
                })
                .then(data => {
                    if (data && data.success) {
                        status.className = 'status success';
                        status.textContent = `✅ Se generaron ${data.generated_count} íconos correctamente. Recarga la página para ver los cambios.`;
                        
                        // Mostrar íconos generados
                        const generatedList = data.generated.join(', ');
                        status.innerHTML += `<br><small>Íconos generados: ${generatedList}</small>`;
                        
                        // Recargar automáticamente después de 3 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else if (data) {
                        status.className = 'status error';
                        status.textContent = `❌ Error al generar íconos: ${data.errors.join(', ')}`;
                    }
                })
                .catch(error => {
                    status.className = 'status error';
                    status.textContent = `❌ Error de conexión: ${error.message}. Usa el botón de descarga manual.`;
                });
        }

        function generateBasicIcons() {
            const status = document.getElementById('status');
            status.className = 'status';
            status.textContent = 'Generando íconos básicos...';

            fetch('/create-basic-icons.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        status.className = 'status success';
                        status.textContent = `✅ Se generaron ${data.generated_count} íconos básicos correctamente. Recarga la página para ver los cambios.`;
                        
                        // Mostrar íconos generados
                        const generatedList = data.generated.join(', ');
                        status.innerHTML += `<br><small>Íconos generados: ${generatedList}</small>`;
                        status.innerHTML += `<br><small>Método: PNG básico (sin dependencias)</small>`;
                        
                        // Recargar automáticamente después de 3 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else {
                        status.className = 'status error';
                        status.textContent = `❌ Error al generar íconos básicos: ${data.errors.join(', ')}`;
                    }
                })
                .catch(error => {
                    status.className = 'status error';
                    status.textContent = `❌ Error de conexión: ${error.message}. Usa el botón de descarga manual.`;
                });
        }

        // Extender Canvas con roundRect si no existe
        if (!CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function(x, y, width, height, radius) {
                this.beginPath();
                this.moveTo(x + radius, y);
                this.lineTo(x + width - radius, y);
                this.quadraticCurveTo(x + width, y, x + width, y + radius);
                this.lineTo(x + width, y + height - radius);
                this.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                this.lineTo(x + radius, y + height);
                this.quadraticCurveTo(x, y + height, x, y + height - radius);
                this.lineTo(x, y + radius);
                this.quadraticCurveTo(x, y, x + radius, y);
                this.closePath();
            };
        }

        // Verificar íconos al cargar la página
        window.onload = function() {
            checkExistingIcons();
        };
    </script>
</body>
</html> 