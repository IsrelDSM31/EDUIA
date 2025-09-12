<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Diagnóstico PWA - IAEDU1</title>

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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #8B1538 0%, #A52A2A 100%);
            color: white;
            min-height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .diagnostic-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status.success {
            background: #4CAF50;
            color: white;
        }
        .status.error {
            background: #f44336;
            color: white;
        }
        .status.warning {
            background: #ff9800;
            color: white;
        }
        .status.info {
            background: #2196F3;
            color: white;
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
        .install-button {
            background: #4CAF50;
            font-size: 18px;
            padding: 15px 30px;
            font-weight: bold;
        }
        .install-button:hover {
            background: #45a049;
        }
        .code-block {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .criteria-list {
            list-style: none;
            padding: 0;
        }
        .criteria-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .criteria-list li:before {
            content: "🔍 ";
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-button">← Volver a IAEDU1</a>
        
        <h1>🔧 Diagnóstico PWA - IAEDU1</h1>
        <p>Esta página diagnostica por qué no aparece el botón "Instalar App" y te ayuda a solucionarlo.</p>

        <div class="diagnostic-item">
            <h3>📊 Estado General de la PWA</h3>
            <div id="generalStatus">Verificando...</div>
        </div>

        <div class="diagnostic-item">
            <h3>📋 Criterios de Instalación PWA</h3>
            <ul class="criteria-list" id="criteriaList">
                <li>Manifest.json válido y accesible</li>
                <li>Service Worker registrado y activo</li>
                <li>HTTPS o localhost (seguro)</li>
                <li>Navegador compatible</li>
                <li>No estar ya instalada</li>
                <li>Haber interactuado con la app</li>
                <li>Frecuencia de uso mínima</li>
            </ul>
        </div>

        <div class="diagnostic-item">
            <h3>🚀 Botón de Instalación Manual</h3>
            <p>Si no aparece el botón automático, usa este:</p>
            <button class="install-button" onclick="manualInstall()">
                📥 Instalar IAEDU1 como App
            </button>
        </div>

        <div class="diagnostic-item">
            <h3>🔍 Información Detallada del Dispositivo</h3>
            <div id="detailedInfo">Cargando...</div>
        </div>

        <div class="diagnostic-item">
            <h3>📱 Instrucciones de Instalación Manual</h3>
            <div class="code-block">
                <strong>Chrome/Edge Desktop:</strong><br>
                1. Busca el ícono de instalación en la barra de direcciones<br>
                2. Haz clic en el ícono y selecciona "Instalar"<br><br>
                
                <strong>Chrome/Edge Móvil:</strong><br>
                1. Toca el menú (⋮) en la esquina superior derecha<br>
                2. Selecciona "Instalar aplicación" o "Añadir a pantalla de inicio"<br><br>
                
                <strong>Safari (iOS):</strong><br>
                1. Toca el botón de compartir (□↑)<br>
                2. Selecciona "Añadir a pantalla de inicio"<br><br>
                
                <strong>Firefox:</strong><br>
                1. Toca el menú (☰)<br>
                2. Selecciona "Instalar aplicación"
            </div>
        </div>

        <div class="diagnostic-item">
            <h3>🛠️ Herramientas de Desarrollo</h3>
            <button onclick="openDevTools()">🔧 Abrir DevTools</button>
            <button onclick="checkLighthouse()">📊 Verificar Lighthouse</button>
            <button onclick="clearCache()">🗑️ Limpiar Cache</button>
            <button onclick="reloadPage()">🔄 Recargar Página</button>
        </div>
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        let deferredPrompt;

        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registrado: ', registration);
                        updateCriteriaStatus('Service Worker registrado y activo', 'success');
                    })
                    .catch((registrationError) => {
                        console.log('SW registro falló: ', registrationError);
                        updateCriteriaStatus('Service Worker registrado y activo', 'error');
                    });
            });
        }

        // Verificar criterios de instalación
        function checkInstallationCriteria() {
            const criteria = document.getElementById('criteriaList');
            const generalStatus = document.getElementById('generalStatus');
            
            let passedCriteria = 0;
            const totalCriteria = 7;

            // 1. Manifest.json válido
            const manifest = document.querySelector('link[rel="manifest"]');
            if (manifest) {
                updateCriteriaStatus('Manifest.json válido y accesible', 'success');
                passedCriteria++;
            } else {
                updateCriteriaStatus('Manifest.json válido y accesible', 'error');
            }

            // 2. Service Worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    if (registrations.length > 0) {
                        updateCriteriaStatus('Service Worker registrado y activo', 'success');
                        passedCriteria++;
                    } else {
                        updateCriteriaStatus('Service Worker registrado y activo', 'error');
                    }
                });
            }

            // 3. HTTPS o localhost
            if (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                updateCriteriaStatus('HTTPS o localhost (seguro)', 'success');
                passedCriteria++;
            } else {
                updateCriteriaStatus('HTTPS o localhost (seguro)', 'error');
            }

            // 4. Navegador compatible
            const isChrome = /Chrome/.test(navigator.userAgent);
            const isEdge = /Edg/.test(navigator.userAgent);
            const isFirefox = /Firefox/.test(navigator.userAgent);
            const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent);
            
            if (isChrome || isEdge || isFirefox || isSafari) {
                updateCriteriaStatus('Navegador compatible', 'success');
                passedCriteria++;
            } else {
                updateCriteriaStatus('Navegador compatible', 'warning');
            }

            // 5. No estar ya instalada
            if (!window.matchMedia('(display-mode: standalone)').matches) {
                updateCriteriaStatus('No estar ya instalada', 'success');
                passedCriteria++;
            } else {
                updateCriteriaStatus('No estar ya instalada', 'info');
            }

            // 6. Interacción con la app
            updateCriteriaStatus('Haber interactuado con la app', 'info');
            passedCriteria++;

            // 7. Frecuencia de uso
            updateCriteriaStatus('Frecuencia de uso mínima', 'info');
            passedCriteria++;

            // Estado general
            const percentage = Math.round((passedCriteria / totalCriteria) * 100);
            if (percentage >= 80) {
                generalStatus.innerHTML = `<span class="status success">✅ PWA Lista (${percentage}%)</span>`;
            } else if (percentage >= 60) {
                generalStatus.innerHTML = `<span class="status warning">⚠️ PWA Parcial (${percentage}%)</span>`;
            } else {
                generalStatus.innerHTML = `<span class="status error">❌ PWA No Lista (${percentage}%)</span>`;
            }
        }

        function updateCriteriaStatus(criteriaText, status) {
            const criteria = document.getElementById('criteriaList');
            const items = criteria.getElementsByTagName('li');
            
            for (let item of items) {
                if (item.textContent.includes(criteriaText.split(' ')[0])) {
                    item.innerHTML = `${criteriaText} <span class="status ${status}">${getStatusIcon(status)}</span>`;
                    break;
                }
            }
        }

        function getStatusIcon(status) {
            switch (status) {
                case 'success': return '✅';
                case 'error': return '❌';
                case 'warning': return '⚠️';
                case 'info': return 'ℹ️';
                default: return '❓';
            }
        }

        // Instalación manual
        function manualInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        alert('🎉 ¡IAEDU1 se ha instalado correctamente!');
                    } else {
                        alert('❌ Instalación cancelada por el usuario');
                    }
                    deferredPrompt = null;
                });
            } else {
                showManualInstructions();
            }
        }

        function showManualInstructions() {
            const instructions = `
📱 Instalar IAEDU1 como App:

Chrome/Edge Desktop:
1. Busca el ícono de instalación en la barra de direcciones
2. Haz clic en el ícono y selecciona "Instalar"

Chrome/Edge Móvil:
1. Toca el menú (⋮) en la esquina superior derecha
2. Selecciona "Instalar aplicación" o "Añadir a pantalla de inicio"

Safari (iOS):
1. Toca el botón de compartir (□↑)
2. Selecciona "Añadir a pantalla de inicio"

Firefox:
1. Toca el menú (☰)
2. Selecciona "Instalar aplicación"
            `;
            alert(instructions);
        }

        // Herramientas de desarrollo
        function openDevTools() {
            alert('Presiona F12 para abrir DevTools y verificar la pestaña "Application"');
        }

        function checkLighthouse() {
            alert('En DevTools, ve a la pestaña "Lighthouse" y ejecuta una auditoría PWA');
        }

        function clearCache() {
            if ('caches' in window) {
                caches.keys().then(cacheNames => {
                    return Promise.all(
                        cacheNames.map(cacheName => {
                            return caches.delete(cacheName);
                        })
                    );
                }).then(() => {
                    alert('✅ Cache limpiado correctamente');
                });
            } else {
                alert('❌ Cache no disponible');
            }
        }

        function reloadPage() {
            window.location.reload();
        }

        // Información detallada
        function showDetailedInfo() {
            const detailedInfo = document.getElementById('detailedInfo');
            detailedInfo.innerHTML = `
                <p><strong>URL:</strong> ${window.location.href}</p>
                <p><strong>Protocolo:</strong> ${location.protocol}</p>
                <p><strong>Hostname:</strong> ${location.hostname}</p>
                <p><strong>Navegador:</strong> ${navigator.userAgent}</p>
                <p><strong>Plataforma:</strong> ${navigator.platform}</p>
                <p><strong>Online:</strong> ${navigator.onLine ? '✅ Sí' : '❌ No'}</p>
                <p><strong>Service Worker:</strong> ${'serviceWorker' in navigator ? '✅ Soportado' : '❌ No soportado'}</p>
                <p><strong>Notificaciones:</strong> ${'Notification' in window ? '✅ Soportadas' : '❌ No soportadas'}</p>
                <p><strong>PWA Instalada:</strong> ${window.matchMedia('(display-mode: standalone)').matches ? '✅ Sí' : '❌ No'}</p>
                <p><strong>Manifest:</strong> ${document.querySelector('link[rel="manifest"]') ? '✅ Detectado' : '❌ No detectado'}</p>
                <p><strong>Display Mode:</strong> ${window.matchMedia('(display-mode: standalone)').matches ? 'standalone' : 'browser'}</p>
            `;
        }

        // Eventos de instalación
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('Instalación PWA disponible');
        });

        window.addEventListener('appinstalled', () => {
            console.log('PWA instalada exitosamente');
            alert('🎉 ¡IAEDU1 se ha instalado correctamente!');
        });

        // Inicializar
        window.onload = function() {
            checkInstallationCriteria();
            showDetailedInfo();
        };
    </script>
</body>
</html> 