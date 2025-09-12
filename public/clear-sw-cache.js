// Script para limpiar el cache del Service Worker
if ('serviceWorker' in navigator) {
    // Desregistrar el Service Worker actual
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
            registration.unregister();
            console.log('Service Worker desregistrado');
        }
    });

    // Limpiar el cache
    if ('caches' in window) {
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    return caches.delete(cacheName);
                })
            );
        }).then(function() {
            console.log('Cache limpiado');
            // Recargar la página para aplicar los cambios
            window.location.reload();
        });
    }
} 