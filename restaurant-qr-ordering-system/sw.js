// Service Worker - 餐廳點餐系統
// 版本號：當內容更新時需要更改此版本號
const CACHE_NAME = 'restaurant-ordering-v1';
const urlsToCache = [
  '/',
  '/index.html',
  '/menu.html',
  '/cart.html',
  '/css/styles.css',
  '/js/app.js',
  '/js/firebase-config.js',
  '/manifest.json',
  // 圖標和圖片
  '/images/icon-192x192.png',
  '/images/icon-512x512.png'
];

// 安裝事件 - 快取資源
self.addEventListener('install', event => {
  console.log('Service Worker: 安裝中...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: 快取檔案');
        return cache.addAll(urlsToCache);
      })
      .then(() => {
        console.log('Service Worker: 安裝完成');
        return self.skipWaiting(); // 立即啟用新的SW
      })
      .catch(error => {
        console.error('Service Worker: 安裝失敗', error);
      })
  );
});

// 啟用事件 - 清理舊快取
self.addEventListener('activate', event => {
  console.log('Service Worker: 啟用中...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: 刪除舊快取', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('Service Worker: 啟用完成');
      return self.clients.claim(); // 立即控制所有頁面
    })
  );
});

// 攔截網路請求
self.addEventListener('fetch', event => {
  // 只處理 GET 請求
  if (event.request.method !== 'GET') {
    return;
  }

  // 忽略非同源請求
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // 如果在快取中找到，直接返回
        if (response) {
          console.log('Service Worker: 從快取提供', event.request.url);
          return response;
        }

        // 否則從網路獲取
        return fetch(event.request)
          .then(response => {
            // 檢查回應是否有效
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // 複製回應（因為回應只能使用一次）
            const responseToCache = response.clone();

            // 將新的回應加入快取
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });

            return response;
          })
          .catch(error => {
            console.log('Service Worker: 網路請求失敗', error);
            
            // 如果是導航請求且失敗，返回離線頁面
            if (event.request.destination === 'document') {
              return caches.match('/index.html');
            }
            
            // 對於其他請求，可以返回預設的離線回應
            return new Response('離線模式：無法載入資源', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// 背景同步 - 當網路恢復時同步離線資料
self.addEventListener('sync', event => {
  console.log('Service Worker: 背景同步', event.tag);
  
  if (event.tag === 'background-sync-orders') {
    event.waitUntil(syncOfflineOrders());
  }
});

// 推送通知處理
self.addEventListener('push', event => {
  console.log('Service Worker: 收到推送通知', event);
  
  const options = {
    body: event.data ? event.data.text() : '您有新的訂單狀態更新',
    icon: '/images/icon-192x192.png',
    badge: '/images/icon-96x96.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: '查看詳情',
        icon: '/images/checkmark.png'
      },
      {
        action: 'close',
        title: '關閉',
        icon: '/images/xmark.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('餐廳點餐系統', options)
  );
});

// 通知點擊處理
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: 通知被點擊', event);
  
  event.notification.close();

  if (event.action === 'explore') {
    // 開啟應用程式
    event.waitUntil(
      clients.openWindow('/')
    );
  } else if (event.action === 'close') {
    // 關閉通知
    event.notification.close();
  } else {
    // 預設行為：開啟應用程式
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// 同步離線訂單資料
async function syncOfflineOrders() {
  try {
    console.log('Service Worker: 開始同步離線訂單');
    
    // 獲取離線儲存的訂單
    const offlineOrders = await getOfflineOrders();
    
    if (offlineOrders.length === 0) {
      console.log('Service Worker: 沒有離線訂單需要同步');
      return;
    }

    // 同步每個離線訂單
    for (const order of offlineOrders) {
      try {
        await syncSingleOrder(order);
        await removeOfflineOrder(order.id);
        console.log('Service Worker: 訂單同步成功', order.id);
      } catch (error) {
        console.error('Service Worker: 訂單同步失敗', order.id, error);
      }
    }
    
  } catch (error) {
    console.error('Service Worker: 同步離線訂單失敗', error);
  }
}

// 獲取離線訂單
async function getOfflineOrders() {
  // 這裡應該從 IndexedDB 或其他客戶端儲存獲取離線訂單
  // 暫時返回空陣列
  return [];
}

// 同步單個訂單
async function syncSingleOrder(order) {
  // 這裡應該將訂單發送到伺服器
  // 實際實作時需要連接 Firebase
  const response = await fetch('/api/orders', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(order)
  });
  
  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }
  
  return response.json();
}

// 移除已同步的離線訂單
async function removeOfflineOrder(orderId) {
  // 這裡應該從本地儲存中移除已同步的訂單
  console.log('Service Worker: 移除離線訂單', orderId);
}
