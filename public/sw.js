// ==========================================
// Кэш-версия. Меняй при каждом обновлении!
// ==========================================
const CACHE_VERSION = 'v1.0.15';
const STATIC_CACHE = `static-kanban-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dynamic-kanban-${CACHE_VERSION}`;
const IMAGE_CACHE = `images-kanban-${CACHE_VERSION}`;

// ==========================================
// Что кэшируем сразу при установке (App Shell)
// ==========================================
const PRECACHE_URLS = [
    '/board/',
    '/css/app.css',
    '/js/app.js',
    '/offline.html',
];

// ==========================================
// БЕЗОПАСНОЕ ДОБАВЛЕНИЕ В КЭШ
// Вместо addAll — добавляем по одному с обработкой ошибок
// ==========================================
async function safePrecache(cache, urls) {
    const results = await Promise.allSettled(
        urls.map(async (url) => {
            try {
                const response = await fetch(url, { cache: 'no-cache' });
                if (!response.ok) {
                    console.warn(`[SW] ⚠️ Не удалось закэшировать ${url}: HTTP ${response.status}`);
                    return { url, success: false, status: response.status };
                }
                await cache.put(url, response);
                console.log(`[SW] ✅ Закэшировано: ${url}`);
                return { url, success: true };
            } catch (error) {
                console.warn(`[SW] ⚠️ Ошибка кэширования ${url}:`, error.message);
                return { url, success: false, error: error.message };
            }
        })
    );

    const failed = results
        .filter(r => r.status === 'fulfilled' && !r.value.success)
        .map(r => r.value);

    if (failed.length > 0) {
        console.warn(`[SW] ⚠️ ${failed.length} из ${urls.length} файлов не закэшированы:`, failed);
    } else {
        console.log(`[SW] ✅ Все ${urls.length} файлов успешно закэшированы`);
    }

    return results;
}

// ==========================================
// INSTALL — пре-кэшируем App Shell
// ==========================================
self.addEventListener('install', (event) => {
    console.log('[SW] Install');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Precaching app shell');
                // Безопасное кэширование — не падает при недоступных файлах
                return safePrecache(cache, PRECACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// ==========================================
// ACTIVATE — чистим старые кэши
// ==========================================
self.addEventListener('activate', (event) => {
    console.log('[SW] Activate');
    const currentCaches = [STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE];

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => !currentCaches.includes(name))
                        .map((name) => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// ==========================================
// FETCH — основная логика кэширования
// ==========================================
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;
    if (!url.protocol.startsWith('http')) return;

    // 1. Статика с хэшами (Vite build)
    if (url.pathname.startsWith('/assets/') ||
        url.pathname.match(/\.(js|css|woff2?|ttf|eot)(\?.*)?$/)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // 2. Картинки — cache-first с лимитом
    if (request.destination === 'image' ||
        url.pathname.match(/\.(png|jpg|jpeg|gif|svg|webp|ico)(\?.*)?$/)) {
        event.respondWith(cacheFirstWithLimit(request, IMAGE_CACHE, 100));
        return;
    }

    // 3. API запросы — network-first
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/board/api/')) {
        event.respondWith(networkFirst(request, DYNAMIC_CACHE));
        return;
    }

    // 4. HTML страницы — stale-while-revalidate
    if (request.destination === 'document' ||
        url.pathname.startsWith('/board')) {
        event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
        return;
    }

    // 5. Всё остальное — network-first
    event.respondWith(networkFirst(request, DYNAMIC_CACHE));
});

// ==========================================
// Стратегии кэширования
// ==========================================

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return offlineFallback(request);
    }
}

async function cacheFirstWithLimit(request, cacheName, maxItems) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            await cache.put(request, response.clone());
            await trimCache(cacheName, maxItems);
        }
        return response;
    } catch (error) {
        return offlineFallback(request);
    }
}

async function networkFirst(request, cacheName) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cached = await caches.match(request);
        return cached || offlineFallback(request);
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request)
        .then((networkResponse) => {
            if (networkResponse.ok) {
                cache.put(request, networkResponse.clone());
            }
            return networkResponse;
        })
        .catch(() => cached);

    return cached || (await fetchPromise) || offlineFallback(request);
}

async function offlineFallback(request) {
    if (request.destination === 'document') {
        const offlinePage = await caches.match('/offline.html');
        if (offlinePage) return offlinePage;
    }
    if (request.url.includes('/api/')) {
        return new Response(JSON.stringify({ offline: true }), {
            headers: { 'Content-Type': 'application/json' }
        });
    }
    return new Response('Offline', { status: 503, statusText: 'Offline' });
}

async function trimCache(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxItems) {
        await cache.delete(keys[0]);
        await trimCache(cacheName, maxItems);
    }
}

// ==========================================
// PUSH-УВЕДОМЛЕНИЯ
// ==========================================
self.addEventListener('push', (event) => {
    console.log("[SW] push");
    let data = { title: 'Уведомление', body: 'Новое сообщение', icon: '/icons/icon-192x192.png' };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge || data.icon,
        vibrate: [100, 50, 100],
        data: { url: data.url || '/board/#/menu' },
        actions: data.actions || []
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );

    console.log('[SW] Отправляем в BroadcastChannel...');
    const channel = new BroadcastChannel('push-notifications');
    channel.postMessage({
        type: 'PUSH_RECEIVED',
        title: data.title || 'Заголовок',
        body: data.body || '',
        url: data.url || '/',
        notificationType: data.notificationType || 'success'
    });
    console.log('[SW] Сообщение отправлено');
    channel.close();
});

// Клик по уведомлению
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/board/#/menu';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                for (const client of windowClients) {
                    if (client.url.includes('/board') && 'focus' in client) {
                        client.postMessage({ type: 'NAVIGATE', url: urlToOpen });
                        client.postMessage({ type: 'NOTIFICATION_CLICKED', url: urlToOpen });
                        return client.focus();
                    }
                }
                return clients.openWindow(urlToOpen);
            })
    );
});

// ==========================================
// Сообщения от клиента
// ==========================================
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    if (event.data === 'CLEAR_CACHE') {
        caches.keys().then((names) => {
            names.forEach((name) => caches.delete(name));
        });
    }
});
