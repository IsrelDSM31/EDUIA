import axios from 'axios';
window.axios = axios;

// Configurar axios para enviar cookies en todas las peticiones
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Variable para rastrear si el token está listo
let csrfTokenReady = false;
let csrfTokenRetryCount = 0;
const MAX_CSRF_RETRIES = 10;

// Función para obtener el token CSRF desde el meta tag
function getCsrfTokenFromMeta() {
    const token = document.head?.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
}

// Función para obtener el token CSRF desde la cookie
function getCsrfTokenFromCookie() {
    const name = 'XSRF-TOKEN';
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
    return null;
}

// Función para obtener y actualizar el token CSRF desde múltiples fuentes
function getCsrfToken() {
    // Primero intentar desde el meta tag
    let token = getCsrfTokenFromMeta();
    
    // Si no está en el meta tag, intentar desde la cookie
    if (!token) {
        token = getCsrfTokenFromCookie();
    }
    
    return token;
}

// Función para esperar hasta que el token esté disponible
function waitForCsrfToken(maxAttempts = 20, delay = 50) {
    return new Promise((resolve) => {
        let attempts = 0;
        const checkToken = () => {
            const token = getCsrfToken();
            if (token) {
                csrfTokenReady = true;
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                resolve(token);
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(checkToken, delay);
            } else {
                // Si no se encuentra después de varios intentos, continuar de todos modos
                csrfTokenReady = true;
                resolve(null);
            }
        };
        checkToken();
    });
}

// Inicializar el token inmediatamente
(function initializeCsrfToken() {
    // Intentar obtener el token inmediatamente
    const immediateToken = getCsrfToken();
    if (immediateToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = immediateToken;
        csrfTokenReady = true;
    } else {
        // Si no está disponible, esperar con polling
        waitForCsrfToken().then((token) => {
            if (token) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
        });
    }
    
    // También escuchar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            const token = getCsrfToken();
            if (token) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                csrfTokenReady = true;
            }
        });
    }
})();

// Interceptor para actualizar el token CSRF en cada petición
window.axios.interceptors.request.use(function (config) {
    // Intentar obtener el token de múltiples fuentes
    let token = getCsrfToken();
    
    // Si no hay token, intentar esperar un momento
    if (!token && !csrfTokenReady && csrfTokenRetryCount < MAX_CSRF_RETRIES) {
        // Esperar un momento muy corto para que el token esté disponible
        const waitToken = getCsrfToken();
        if (waitToken) {
            token = waitToken;
            csrfTokenReady = true;
        }
        csrfTokenRetryCount++;
    }
    
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
        csrfTokenReady = true;
    }
    
    // Asegurar que el header X-Requested-With esté presente
    if (!config.headers['X-Requested-With']) {
        config.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    
    return config;
}, function (error) {
    return Promise.reject(error);
});

// Interceptor para manejar errores 419 (CSRF token expirado)
window.axios.interceptors.response.use(
    function (response) {
        // Actualizar el token CSRF si viene en la respuesta
        const newToken = response.headers['x-csrf-token'] || 
                        response.data?.csrf_token ||
                        response.data?.csrfToken;
        
        if (newToken) {
            const metaTag = document.head?.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
            csrfTokenReady = true;
        }
        
        return response;
    },
    function (error) {
        if (error.response && error.response.status === 419) {
            // Token CSRF expirado - intentar obtener uno nuevo sin mostrar error
            const newToken = getCsrfToken();
            
            if (newToken && error.config) {
                // Actualizar el token y reintentar la petición automáticamente
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                csrfTokenReady = true;
                
                // Actualizar el header de la petición original
                if (error.config.headers) {
                    error.config.headers['X-CSRF-TOKEN'] = newToken;
                }
                
                // Reintentar la petición automáticamente (solo una vez)
                if (!error.config._retry) {
                    error.config._retry = true;
                    return window.axios(error.config);
                }
            }
            
            // Si después de reintentar sigue fallando, solo entonces recargar
            const method = error.config?.method?.toUpperCase();
            if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                // Solo recargar si ya se reintentó
                if (error.config?._retry) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                }
            }
        }
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 */
