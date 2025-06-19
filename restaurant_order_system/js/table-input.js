// 座位輸入頁面 JavaScript
let tableNumber = '';

function inputNumber(num) {
    if (tableNumber.length < 3) {
        tableNumber += num;
        updateDisplay();
    }
}

function deleteNumber() {
    tableNumber = tableNumber.slice(0, -1);
    updateDisplay();
}

function clearNumber() {
    tableNumber = '';
    updateDisplay();
}

function updateDisplay() {
    const display = document.getElementById('tableDisplay');
    const confirmBtn = document.getElementById('confirmBtn');
    
    if (tableNumber === '') {
        display.textContent = '請輸入座位號碼';
        confirmBtn.disabled = true;
    } else {
        display.textContent = `${tableNumber} 號桌`;
        confirmBtn.disabled = false;
    }
}

function confirmTable() {
    if (tableNumber === '' || tableNumber === '0') {
        alert('請輸入有效的座位號碼');
        return;
    }
    
    // 儲存座位號碼到 localStorage（僅用於當前會話）
    localStorage.setItem('currentTable', tableNumber);
    
    // 跳轉到點餐頁面
    window.location.href = 'menu.html';
}

// 不檢查之前的座位號碼，每次都重新輸入
window.onload = function() {
    // 清除之前可能儲存的座位號碼
    localStorage.removeItem('currentTable');
};
