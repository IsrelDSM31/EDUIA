const CACHE_NAME = 'iaedu1-v1.0.1'; // Incrementar versión para forzar actualización
// Solo cachear recursos estáticos, NO rutas dinámicas
const urlsToCache = [
    '/images/fondo.jpg',
    '/favicon.ico',
    '/manifest.json'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Cache abierto');
                // Usar addAll con manejo de errores por archivo
                return Promise.all(
                    urlsToCache.map(url => 
                        cache.add(url).catch(err => {
                            console.warn('No se pudo cachear:', url, err);
                        })
                    )
                );
            })
    );
});

// Activación del Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Eliminando cache antiguo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Interceptar peticiones
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Ignorar peticiones de extensiones de Chrome
    if (url.protocol === 'chrome-extension:' || url.protocol === 'chrome:' || url.protocol === 'moz-extension:') {
        return;
    }
    
    // Ignorar peticiones a Vite HMR
    if (url.hostname === 'localhost' && (url.port === '5173' || url.port === '5174')) {
        return;
    }
    
    // CRÍTICO: NO interceptar peticiones POST, PUT, DELETE, PATCH - deben ir directo al servidor
    const method = event.request.method;
    if (method !== 'GET' && method !== 'HEAD') {
        // Permitir que las peticiones mutativas pasen directamente sin cache
        return;
    }
    
    // No interceptar peticiones de navegación a la raíz para evitar problemas de redirección
    if (url.pathname === '/' && (url.hostname === '127.0.0.1' || url.hostname === 'localhost')) {
        return;
    }
    
    // Ignorar peticiones que no son HTTP/HTTPS
    if (!event.request.url.startsWith('http')) {
        return;
    }

    // NO cachear peticiones a rutas de API o rutas dinámicas
    const isApiRoute = url.pathname.startsWith('/api/') || 
                       url.pathname.startsWith('/sanctum/') ||
                       url.pathname.includes('/students') ||
                       url.pathname.includes('/grades') ||
                       url.pathname.includes('/attendance') ||
                       url.pathname.includes('/alerts') ||
                       url.pathname.includes('/dashboard') ||
                       url.pathname.includes('/profile');
    
    // Solo cachear recursos estáticos (CSS, JS, imágenes, fuentes)
    const isStaticResource = url.pathname.match(/\.(css|js|jpg|jpeg|png|gif|svg|ico|woff|woff2|ttf|eot)$/i) ||
                             url.pathname.startsWith('/build/') ||
                             url.pathname.startsWith('/css/') ||
                             url.pathname.startsWith('/js/') ||
                             url.pathname.startsWith('/images/') ||
                             url.pathname.startsWith('/fonts/');

    // Si es una ruta dinámica o API, NO usar cache
    if (isApiRoute && !isStaticResource) {
        // Pasar directamente al servidor sin cache
        return fetch(event.request);
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Si está en cache y es un recurso estático, devolverlo
                if (response && isStaticResource) {
                    return response;
                }

                // Si no está en cache, hacer la petición
                return fetch(event.request, {
                    redirect: 'follow',
                    cache: isApiRoute ? 'no-store' : 'default' // No cachear rutas dinámicas
                })
                    .then((response) => {
                        // Solo cachear recursos estáticos exitosos
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Solo cachear recursos estáticos
                        if (!isStaticResource) {
                            return response;
                        }

                        // Verificar que el tipo de respuesta sea cacheable
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.indexOf('text/html') !== -1 && isApiRoute) {
                            // No cachear HTML de rutas dinámicas
                            return response;
                        }

                        // Clonar la respuesta solo para recursos estáticos
                        const responseToCache = response.clone();

                        // Guardar en cache solo recursos estáticos
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                try {
                                    cache.put(event.request, responseToCache);
                                } catch (error) {
                                    console.warn('Error al guardar en cache:', error);
                                }
                            });

                        return response;
                    })
                    .catch((error) => {
                        console.warn('Error en fetch:', error);
                        // Si falla la petición y es una página, mostrar página offline solo para recursos estáticos
                        if (event.request.destination === 'document' && isStaticResource) {
                            return caches.match('/offline.html');
                        }
                        throw error;
                    });
            })
    );
});

// Manejo de notificaciones push
self.addEventListener('push', (event) => {
    const options = {
        body: event.data ? event.data.text() : 'Nueva notificación de IAEDU1',
        icon: '/icon-192x192.png',
        badge: '/icon-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Ver más',
                icon: '/icon-96x96.png'
            },
            {
                action: 'close',
                title: 'Cerrar',
                icon: '/icon-96x96.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('IAEDU1 - Sistema Educativo', options)
    );
});

// Manejo de clics en notificaciones
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    } else if (event.action === 'close') {
        // Solo cerrar la notificación
    } else {
        // Clic en la notificación principal
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    }
});

// Sincronización en segundo plano
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        event.waitUntil(
            // Aquí puedes sincronizar datos cuando hay conexión
            console.log('Sincronizando datos en segundo plano...')
        );
    }
}); 