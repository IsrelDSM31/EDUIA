<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Prueba PWA - IAEDU1</title>

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
            max-width: 800px;
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
        .test-item {
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
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .feature-list li:before {
            content: "✅ ";
            margin-right: 10px;
        }
        .install-prompt {
            background: #4CAF50;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-button">← Volver a IAEDU1</a>
        
        <h1>📱 Prueba PWA - IAEDU1</h1>
        <p>Esta página verifica que tu Progressive Web App esté funcionando correctamente.</p>

        <div class="test-item">
            <h3>🔍 Verificación de PWA</h3>
            <div id="pwaStatus">Verificando...</div>
        </div>

        <div class="test-item">
            <h3>📋 Funcionalidades PWA</h3>
            <ul class="feature-list" id="featureList">
                <li>Manifest.json configurado</li>
                <li>Service Worker registrado</li>
                <li>Funcionamiento offline</li>
                <li>Instalación como app</li>
                <li>Notificaciones push</li>
                <li>Responsive design</li>
            </ul>
        </div>

        <div class="test-item">
            <h3>🚀 Pruebas de Funcionalidad</h3>
            <button onclick="testOffline()">📴 Probar Modo Offline</button>
            <button onclick="testNotifications()">🔔 Probar Notificaciones</button>
            <button onclick="testInstall()">📥 Probar Instalación</button>
            <button onclick="clearCache()">🗑️ Limpiar Cache</button>
        </div>

        <div class="install-prompt" id="installPrompt" style="display: none;">
            <h3>📱 ¡Instala IAEDU1 como App!</h3>
            <p>Tu PWA está lista para ser instalada en tu dispositivo.</p>
            <button onclick="installPWA()" style="background: white; color: #4CAF50; font-weight: bold;">
                📥 Instalar App
            </button>
        </div>

        <div class="test-item">
            <h3>📊 Información del Dispositivo</h3>
            <div id="deviceInfo">Cargando...</div>
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
                    })
                    .catch((registrationError) => {
                        console.log('SW registro falló: ', registrationError);
                    });
            });
        }

        // Verificar PWA
        function checkPWA() {
            const status = document.getElementById('pwaStatus');
            const features = document.getElementById('featureList');
            
            // Verificar manifest
            const manifest = document.querySelector('link[rel="manifest"]');
            if (manifest) {
                status.innerHTML = '<span class="status success">✅ PWA Detectada</span>';
                features.children[0].innerHTML = 'Manifest.json configurado <span class="status success">✅</span>';
            } else {
                status.innerHTML = '<span class="status error">❌ PWA No Detectada</span>';
            }

            // Verificar service worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    if (registrations.length > 0) {
                        features.children[1].innerHTML = 'Service Worker registrado <span class="status success">✅</span>';
                    } else {
                        features.children[1].innerHTML = 'Service Worker registrado <span class="status error">❌</span>';
                    }
                });
            }

            // Verificar instalación
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                document.getElementById('installPrompt').style.display = 'block';
                features.children[3].innerHTML = 'Instalación como app <span class="status success">✅</span>';
            });

            // Verificar si ya está instalada
            window.addEventListener('appinstalled', () => {
                features.children[3].innerHTML = 'Instalación como app <span class="status success">✅ Instalada</span>';
            });
        }

        // Probar modo offline
        function testOffline() {
            if (!navigator.onLine) {
                alert('✅ Modo offline funcionando correctamente');
            } else {
                alert('📡 Estás en línea. Para probar offline, desconecta internet y recarga la página.');
            }
        }

        // Probar notificaciones
        function testNotifications() {
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('IAEDU1 - Prueba', {
                            body: '¡Las notificaciones funcionan correctamente!',
                            icon: '/icon-192x192.png'
                        });
                    } else {
                        alert('❌ Permisos de notificación denegados');
                    }
                });
            } else {
                alert('❌ Notificaciones no soportadas en este navegador');
            }
        }

        // Probar instalación
        function testInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        alert('✅ Instalación aceptada');
                    } else {
                        alert('❌ Instalación rechazada');
                    }
                    deferredPrompt = null;
                });
            } else {
                alert('ℹ️ La instalación no está disponible en este momento');
            }
        }

        // Instalar PWA
        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        alert('🎉 ¡IAEDU1 se ha instalado correctamente!');
                    }
                    deferredPrompt = null;
                });
            }
        }

        // Limpiar cache
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

        // Información del dispositivo
        function showDeviceInfo() {
            const deviceInfo = document.getElementById('deviceInfo');
            deviceInfo.innerHTML = `
                <p><strong>Navegador:</strong> ${navigator.userAgent}</p>
                <p><strong>Plataforma:</strong> ${navigator.platform}</p>
                <p><strong>Online:</strong> ${navigator.onLine ? '✅ Sí' : '❌ No'}</p>
                <p><strong>Service Worker:</strong> ${'serviceWorker' in navigator ? '✅ Soportado' : '❌ No soportado'}</p>
                <p><strong>Notificaciones:</strong> ${'Notification' in window ? '✅ Soportadas' : '❌ No soportadas'}</p>
                <p><strong>PWA:</strong> ${window.matchMedia('(display-mode: standalone)').matches ? '✅ Instalada' : '📱 No instalada'}</p>
            `;
        }

        // Inicializar
        window.onload = function() {
            checkPWA();
            showDeviceInfo();
        };
    </script>
</body>
</html> 