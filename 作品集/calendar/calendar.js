/**
 * 現代化萬年曆 JavaScript
 */

class ModernCalendar {
    constructor() {
        this.currentYear = new Date().getFullYear();
        this.currentMonth = new Date().getMonth() + 1;
        this.selectedDate = null;
        this.holidays = window.calendarData?.holidays || {};
        this.events = window.calendarData?.events || {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.renderCalendar();
        this.updateTitle();
    }

    bindEvents() {
        // 導航按鈕事件
        $('#prevMonth').on('click', () => this.previousMonth());
        $('#nextMonth').on('click', () => this.nextMonth());
        $('#today').on('click', () => this.goToToday());
        
        // 搜尋功能
        $('#searchBtn').on('click', () => this.searchDate());
        $('#clearSearch').on('click', () => this.clearSearch());
        $('#dateSearch').on('keypress', (e) => {
            if (e.which === 13) this.searchDate();
        });

        // 響應式處理
        $(window).on('resize', () => this.handleResize());
    }

    renderCalendar() {
        const calendarGrid = $('#calendarGrid');
        
        // 保留星期標題，清除日期內容
        calendarGrid.find('.calendar-day').remove();
        
        // 獲取月份數據
        $.get('api.php', {
            api: 'calendar',
            year: this.currentYear,
            month: this.currentMonth
        }, (data) => {
            this.renderDays(data);
        }, 'json').fail(() => {
            // 如果API失敗，使用本地數據
            this.renderDaysLocal();
        });
    }

    renderDays(days) {
        const calendarGrid = $('#calendarGrid');
        
        days.forEach((day, index) => {
            const dayElement = this.createDayElement(day, index);
            calendarGrid.append(dayElement);
        });

        // 添加動畫效果
        this.animateCalendar();
    }

    createDayElement(day, index) {
        const dayDiv = $('<div>').addClass('calendar-day');
        
        if (!day) {
            // 空白天數
            return dayDiv.addClass('empty-day');
        }

        // 基本樣式
        dayDiv.addClass('active-day').data('date', day.date);
        
        // 特殊日期樣式
        if (day.isToday) dayDiv.addClass('today');
        if (day.isWeekend) dayDiv.addClass('weekend');
        if (day.holiday) dayDiv.addClass('holiday');
        if (day.event) dayDiv.addClass('event');
        if (this.selectedDate === day.date) dayDiv.addClass('selected');

        // 日期內容
        const dayNumber = $('<div>').addClass('day-number').text(day.day);
        const dayContent = $('<div>').addClass('day-content');
        
        // 節日顯示
        if (day.holiday) {
            const holidayLabel = $('<div>').addClass('holiday-label').text(day.holiday);
            dayContent.append(holidayLabel);
        }
        
        // 活動顯示
        if (day.event) {
            const eventLabel = $('<div>').addClass('event-label').text(day.event);
            dayContent.append(eventLabel);
        }

        // 週數顯示（小字）
        if (day.day <= 7 && day.weekOfYear) {
            const weekLabel = $('<div>').addClass('week-label').text(`W${day.weekOfYear}`);
            dayContent.append(weekLabel);
        }

        dayDiv.append(dayNumber, dayContent);

        // 點擊事件
        dayDiv.on('click', () => this.selectDate(day.date));

        // 工具提示
        if (day.holiday || day.event) {
            const tooltip = [];
            if (day.holiday) tooltip.push(`節日: ${day.holiday}`);
            if (day.event) tooltip.push(`活動: ${day.event}`);
            dayDiv.attr('title', tooltip.join('\n'));
        }

        return dayDiv;
    }

    renderDaysLocal() {
        // 本地渲染邏輯（當API不可用時）
        const firstDay = new Date(this.currentYear, this.currentMonth - 1, 1);
        const lastDay = new Date(this.currentYear, this.currentMonth, 0);
        const firstDayWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        
        const calendarGrid = $('#calendarGrid');
        
        // 前一個月的空白天數
        for (let i = 0; i < firstDayWeek; i++) {
            calendarGrid.append($('<div>').addClass('calendar-day empty-day'));
        }
        
        // 當月天數
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDate = `${this.currentYear}-${String(this.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = currentDate === new Date().toISOString().split('T')[0];
            const isWeekend = [0, 6].includes(new Date(currentDate).getDay());
            
            const dayData = {
                date: currentDate,
                day: day,
                isToday: isToday,
                isWeekend: isWeekend,
                holiday: this.holidays[currentDate],
                event: this.events[currentDate]
            };
            
            const dayElement = this.createDayElement(dayData);
            calendarGrid.append(dayElement);
        }

        this.animateCalendar();
    }

    animateCalendar() {
        // 淡入動畫
        $('.calendar-day').hide().each(function(index) {
            $(this).delay(index * 20).fadeIn(200);
        });
    }

    selectDate(date) {
        this.selectedDate = date;
        
        // 更新選中樣式
        $('.calendar-day').removeClass('selected');
        $('.calendar-day').each(function() {
            if ($(this).data('date') === date) {
                $(this).addClass('selected');
            }
        });

        // 顯示日期詳情
        this.showDayDetail(date);
    }

    showDayDetail(date) {
        $.get('api.php', {
            api: 'day-detail',
            date: date
        }, (data) => {
            this.renderDayDetail(data);
        }, 'json').fail(() => {
            // 本地數據
            const data = {
                formatted_date: new Date(date).toLocaleDateString('zh-TW'),
                holiday: this.holidays[date],
                event: this.events[date],
                isToday: date === new Date().toISOString().split('T')[0]
            };
            this.renderDayDetail(data);
        });
    }

    renderDayDetail(data) {
        let content = `
            <div class="day-detail">
                <h6 class="text-primary">${data.formatted_date || data.date}</h6>
        `;

        if (data.weekday) {
            content += `<p><strong>星期:</strong> 星期${data.weekday}</p>`;
        }

        if (data.holiday) {
            content += `<p><span class="badge bg-danger">${data.holiday}</span></p>`;
        }

        if (data.event) {
            content += `<p><span class="badge bg-success">${data.event}</span></p>`;
        }

        if (data.isToday) {
            content += `<p><span class="badge bg-warning">今天</span></p>`;
        }

        if (data.weekOfYear) {
            content += `<p><small class="text-muted">第 ${data.weekOfYear} 週</small></p>`;
        }

        if (data.lunarInfo) {
            if (data.lunarInfo.zodiac) {
                content += `<p><small>生肖: ${data.lunarInfo.zodiac}</small></p>`;
            }
            if (data.lunarInfo.constellation) {
                content += `<p><small>星座: ${data.lunarInfo.constellation}</small></p>`;
            }
        }

        content += '</div>';

        $('#modalTitle').text('日期詳情');
        $('#modalBody').html(content);
        
        const modal = new bootstrap.Modal(document.getElementById('dayDetailModal'));
        modal.show();
    }

    previousMonth() {
        if (this.currentMonth === 1) {
            this.currentMonth = 12;
            this.currentYear--;
        } else {
            this.currentMonth--;
        }
        this.updateCalendar();
    }

    nextMonth() {
        if (this.currentMonth === 12) {
            this.currentMonth = 1;
            this.currentYear++;
        } else {
            this.currentMonth++;
        }
        this.updateCalendar();
    }

    goToToday() {
        const today = new Date();
        this.currentYear = today.getFullYear();
        this.currentMonth = today.getMonth() + 1;
        this.updateCalendar();
        
        // 自動選中今天
        setTimeout(() => {
            const todayDate = today.toISOString().split('T')[0];
            this.selectDate(todayDate);
        }, 500);
    }

    updateCalendar() {
        this.updateTitle();
        this.renderCalendar();
        this.selectedDate = null;
    }

    updateTitle() {
        const monthNames = [
            '一月', '二月', '三月', '四月', '五月', '六月',
            '七月', '八月', '九月', '十月', '十一月', '十二月'
        ];
        
        const title = `${this.currentYear} 年 ${monthNames[this.currentMonth - 1]}`;
        $('#monthYearTitle').text(title);
    }

    searchDate() {
        const searchDate = $('#dateSearch').val();
        if (!searchDate) {
            this.showNotification('請選擇要搜尋的日期', 'warning');
            return;
        }

        const date = new Date(searchDate);
        this.currentYear = date.getFullYear();
        this.currentMonth = date.getMonth() + 1;
        
        this.updateCalendar();
        
        setTimeout(() => {
            this.selectDate(searchDate);
            this.showNotification('已找到並選中該日期', 'success');
        }, 500);
    }

    clearSearch() {
        $('#dateSearch').val('');
        $('.calendar-day').removeClass('selected');
        this.selectedDate = null;
        this.showNotification('已清除搜尋', 'info');
    }

    showNotification(message, type = 'info') {
        // 創建通知
        const alertClass = {
            'success': 'alert-success',
            'warning': 'alert-warning',
            'error': 'alert-danger',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);
        
        // 自動移除
        setTimeout(() => {
            notification.alert('close');
        }, 3000);
    }

    handleResize() {
        // 響應式處理邏輯
        const windowWidth = $(window).width();
        if (windowWidth < 768) {
            $('.calendar-container').addClass('mobile-view');
        } else {
            $('.calendar-container').removeClass('mobile-view');
        }
    }
}

// 初始化日曆
$(document).ready(function() {
    const calendar = new ModernCalendar();
    
    // 全局快捷鍵
    $(document).on('keydown', function(e) {
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                calendar.previousMonth();
                break;
            case 'ArrowRight':
                e.preventDefault();
                calendar.nextMonth();
                break;
            case 'Home':
                e.preventDefault();
                calendar.goToToday();
                break;
        }
    });

    // 載入完成效果
    $('.main-content').hide().fadeIn(1000);
});
