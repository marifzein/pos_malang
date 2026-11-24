self.addEventListener("install", (event) => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener("fetch", (event) => {
    // Biarkan semua request langsung ke server agar data stok & transaksi selalu real-time
    event.respondWith(fetch(event.request));
});
