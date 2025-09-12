<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

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

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', 'resources/css/app.css'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
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

            // Instalación de PWA
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Mostrar botón de instalación si es necesario
                const installButton = document.getElementById('install-button');
                if (installButton) {
                    installButton.style.display = 'block';
                    installButton.addEventListener('click', () => {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                console.log('Usuario aceptó la instalación');
                            }
                            deferredPrompt = null;
                        });
                    });
                }
            });

            // Detectar si la app está instalada
            window.addEventListener('appinstalled', (evt) => {
                console.log('Aplicación instalada');
            });
        </script>
    </body>
</html>
