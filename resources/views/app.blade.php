<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}" id="csrf-token-meta">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#8B1538">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
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
        @if(file_exists(public_path('hot')))
            @viteReactRefresh
        @endif
        @vite(['resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
            // Desregistrar Service Workers antiguos primero y forzar actualización
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for(let registration of registrations) {
                        registration.unregister().then(() => {
                            console.log('Service Worker antiguo desregistrado');
                        });
                    }
                });
                
                // Esperar un momento antes de registrar el nuevo
                window.addEventListener('load', () => {
                    setTimeout(() => {
                        navigator.serviceWorker.register('/sw.js', {
                            updateViaCache: 'none' // Forzar actualización del Service Worker
                        })
                            .then((registration) => {
                                console.log('SW registrado: ', registration);
                                // Verificar si hay una actualización disponible
                                registration.update();
                            })
                            .catch((registrationError) => {
                                console.log('SW registro falló: ', registrationError);
                            });
                    }, 1000);
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

            // Actualizar token CSRF inmediatamente al cargar la página
            // Esto evita el error 419 que aparece por un segundo
            (function() {
                // Función para actualizar el token CSRF desde el meta tag
                function updateCsrfToken() {
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        const newToken = metaTag.getAttribute('content');
                        if (newToken && window.axios) {
                            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                            return true;
                        }
                    }
                    return false;
                }
                
                // Función para obtener token desde cookie como fallback
                function getCsrfTokenFromCookie() {
                    const name = 'XSRF-TOKEN';
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) {
                        const token = parts.pop().split(';').shift();
                        if (token && window.axios) {
                            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                            return true;
                        }
                    }
                    return false;
                }
                
                // Inicializar inmediatamente (no esperar al DOM)
                function initCsrfToken() {
                    // Intentar actualizar desde el meta tag
                    if (!updateCsrfToken()) {
                        // Si no hay meta tag, intentar desde la cookie
                        getCsrfTokenFromCookie();
                    }
                }
                
                // Ejecutar inmediatamente si el meta tag ya está disponible
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    initCsrfToken();
                }
                
                // También ejecutar cuando el DOM esté listo (por si acaso)
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCsrfToken);
                } else {
                    initCsrfToken();
                }
                
                // Observar cambios en el meta tag
                if (metaTag) {
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'content') {
                                updateCsrfToken();
                            }
                        });
                    });
                    observer.observe(metaTag, { attributes: true, attributeFilter: ['content'] });
                } else {
                    // Si el meta tag no existe, esperar a que se cree
                    const metaObserver = new MutationObserver(function(mutations) {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) {
                            initCsrfToken();
                            metaObserver.disconnect();
                        }
                    });
                    metaObserver.observe(document.head || document.documentElement, {
                        childList: true,
                        subtree: true
                    });
                }
                
                // Interceptar peticiones de Inertia para actualizar el token si es necesario
                if (window.Inertia) {
                    const originalVisit = window.Inertia.visit;
                    window.Inertia.visit = function(...args) {
                        // Asegurar que el token esté actualizado antes de la petición
                        updateCsrfToken() || getCsrfTokenFromCookie();
                        return originalVisit.apply(this, args);
                    };
                }
            })();
        </script>
    </body>
</html>
