const CACHE_NAME = 'iaedu1-v1.0.0';
const urlsToCache = [
    '/dashboard',
    '/students',
    '/grades',
    '/attendance',
    '/schedule',
    '/alerts',
    '/css/app.css',
    '/js/app.js',
    '/images/fondo.jpg',
    '/favicon.ico'
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
    // No interceptar peticiones de navegación a la raíz para evitar problemas de redirección
    if (event.request.url === 'http://127.0.0.1:8000/' || 
        event.request.url === 'http://localhost:8000/' ||
        event.request.url === 'http://127.0.0.1:8000' || 
        event.request.url === 'http://localhost:8000') {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Si está en cache, devolverlo
                if (response) {
                    return response;
                }

                // Si no está en cache, hacer la petición
                return fetch(event.request, {
                    redirect: 'follow' // Permitir redirecciones
                })
                    .then((response) => {
                        // Verificar que la respuesta sea válida
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clonar la respuesta
                        const responseToCache = response.clone();

                        // Guardar en cache para futuras peticiones
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // Si falla la petición y es una página, mostrar página offline
                        if (event.request.destination === 'document') {
                            return caches.match('/offline.html');
                        }
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