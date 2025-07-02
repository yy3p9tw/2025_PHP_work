$(document).ready(function() {
    // 為所有主要內容區塊和卡片加上 .fade-in class
    $('main.container, .card, .list-group-item').addClass('fade-in');

    // 使用 Intersection Observer API 來觸發動畫
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                $(entry.target).addClass('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1 // 當元素 10% 進入視窗時觸發
    });

    // 觀察所有 .fade-in 元素
    $('.fade-in').each(function() {
        observer.observe(this);
    });
});