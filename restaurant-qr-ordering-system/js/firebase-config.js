// Firebase 配置檔案
// 注意：實際使用時需要替換為您的 Firebase 專案配置

const firebaseConfig = {
  // 請到 Firebase Console 獲取您的配置
  // 1. 前往 https://console.firebase.google.com
  // 2. 建立新專案或選擇現有專案
  // 3. 點擊「專案設定」→「一般」→「您的應用程式」
  // 4. 點擊「網路應用程式」圖示，註冊應用程式
  // 5. 複製配置物件到此處
  
  apiKey: "your-api-key",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "123456789",
  appId: "your-app-id"
};

// 初始化 Firebase
import { initializeApp } from 'firebase/app';
import { getFirestore } from 'firebase/firestore';
import { getStorage } from 'firebase/storage';

const app = initializeApp(firebaseConfig);

// 初始化服務
export const db = getFirestore(app);
export const storage = getStorage(app);

// 資料庫集合名稱
export const COLLECTIONS = {
  MENU: 'menu',
  ORDERS: 'orders',
  CATEGORIES: 'categories'
};

console.log('Firebase 初始化完成');
