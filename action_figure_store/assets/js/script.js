// script.js

document.addEventListener('DOMContentLoaded', function() {
    // 平滑滾動到錨點
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});

// 統一 API 請求 utility
export async function apiRequest(url, options = {}) {
    try {
        const res = await fetch(url, options);
        const result = await res.json();
        if (!result.success) throw new Error(result.error || 'API 錯誤');
        return result.data;
    } catch (err) {
        throw err;
    }
}
