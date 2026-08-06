// Service Worker Básico para PWA
const CACHE_NAME = 'acceso-app-v1';

// Instalación
self.addEventListener('install', (event) => {
    console.log('Service Worker: Instalado');
    self.skipWaiting();
});

// Activación
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activo');
});

// Intercepción de peticiones (Para que funcione offline en el futuro)
self.addEventListener('fetch', (event) => {
    // Por ahora, solo devolvemos lo que viene de la red
    event.respondWith(fetch(event.request));
});