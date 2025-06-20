// EventBus 測試套件
// 測試中央事件匯流排的所有功能

describe('EventBus', () => {
    let eventBus;

    beforeEach(() => {
        // 重置單例實例
        EventBus.instance = null;
        eventBus = EventBus.getInstance();
    });

    afterEach(() => {
        // 清理事件監聽器
        eventBus.events.clear();
        TestUtils.cleanup();
    });

    describe('singleton pattern', () => {
        it('should return same instance', () => {
            const instance1 = EventBus.getInstance();
            const instance2 = EventBus.getInstance();
            
            expect(instance1).toBe(instance2);
        });

        it('should maintain state across instances', () => {
            const instance1 = EventBus.getInstance();
            instance1.testProperty = 'test-value';
            
            const instance2 = EventBus.getInstance();
            
            expect(instance2.testProperty).toBe('test-value');
        });
    });

    describe('on - event registration', () => {
        it('should register event listener', () => {
            const callback = jest.fn();
            
            eventBus.on('test-event', callback);
            
            expect(eventBus.events.has('test-event')).toBe(true);
            expect(eventBus.events.get('test-event')).toHaveLength(1);
        });

        it('should register multiple listeners for same event', () => {
            const callback1 = jest.fn();
            const callback2 = jest.fn();
            
            eventBus.on('test-event', callback1);
            eventBus.on('test-event', callback2);
            
            expect(eventBus.events.get('test-event')).toHaveLength(2);
        });

        it('should validate callback function', () => {
            expect(() => {
                eventBus.on('test-event', 'not-a-function');
            }).toThrow('EventBus: 回調函數必須是函數類型');
        });

        it('should support options parameter', () => {
            const callback = jest.fn();
            const options = { once: true, priority: 1 };
            
            eventBus.on('test-event', callback, options);
            
            const listeners = eventBus.events.get('test-event');
            expect(listeners[0].options).toEqual(options);
        });

        it('should warn when max listeners exceeded', () => {
            const originalMaxListeners = eventBus.maxListeners;
            eventBus.maxListeners = 2;
            
            const consoleWarn = jest.spyOn(console, 'warn').mockImplementation();
            
            eventBus.on('test-event', jest.fn());
            eventBus.on('test-event', jest.fn());
            eventBus.on('test-event', jest.fn()); // 超過限制
            
            expect(consoleWarn).toHaveBeenCalledWith(
                expect.stringContaining('監聽器數量已達上限')
            );
            
            eventBus.maxListeners = originalMaxListeners;
            consoleWarn.mockRestore();
        });
    });

    describe('emit - event triggering', () => {
        it('should trigger registered listeners', () => {
            const callback = jest.fn();
            const eventData = { test: 'data' };
            
            eventBus.on('test-event', callback);
            eventBus.emit('test-event', eventData);
            
            expect(callback).toHaveBeenCalledWith(eventData);
        });

        it('should trigger multiple listeners', () => {
            const callback1 = jest.fn();
            const callback2 = jest.fn();
            const eventData = { test: 'data' };
            
            eventBus.on('test-event', callback1);
            eventBus.on('test-event', callback2);
            eventBus.emit('test-event', eventData);
            
            expect(callback1).toHaveBeenCalledWith(eventData);
            expect(callback2).toHaveBeenCalledWith(eventData);
        });

        it('should handle events with no listeners', () => {
            expect(() => {
                eventBus.emit('non-existent-event', {});
            }).not.toThrow();
        });

        it('should handle listener errors gracefully', () => {
            const errorCallback = jest.fn().mockImplementation(() => {
                throw new Error('Listener error');
            });
            const normalCallback = jest.fn();
            
            const consoleError = jest.spyOn(console, 'error').mockImplementation();
            
            eventBus.on('test-event', errorCallback);
            eventBus.on('test-event', normalCallback);
            
            expect(() => {
                eventBus.emit('test-event', {});
            }).not.toThrow();
            
            expect(errorCallback).toHaveBeenCalled();
            expect(normalCallback).toHaveBeenCalled();
            expect(consoleError).toHaveBeenCalled();
            
            consoleError.mockRestore();
        });

        it('should respect priority order', async () => {
            const callOrder = [];
            
            const lowPriorityCallback = () => callOrder.push('low');
            const highPriorityCallback = () => callOrder.push('high');
            const normalCallback = () => callOrder.push('normal');
            
            eventBus.on('test-event', lowPriorityCallback, { priority: 1 });
            eventBus.on('test-event', normalCallback);
            eventBus.on('test-event', highPriorityCallback, { priority: 10 });
            
            eventBus.emit('test-event', {});
            
            expect(callOrder).toEqual(['high', 'normal', 'low']);
        });
    });

    describe('off - event removal', () => {
        it('should remove specific listener', () => {
            const callback1 = jest.fn();
            const callback2 = jest.fn();
            
            eventBus.on('test-event', callback1);
            eventBus.on('test-event', callback2);
            
            eventBus.off('test-event', callback1);
            eventBus.emit('test-event', {});
            
            expect(callback1).not.toHaveBeenCalled();
            expect(callback2).toHaveBeenCalled();
        });

        it('should handle removal of non-existent listener', () => {
            const callback = jest.fn();
            
            expect(() => {
                eventBus.off('non-existent-event', callback);
            }).not.toThrow();
        });

        it('should remove all listeners when no callback specified', () => {
            const callback1 = jest.fn();
            const callback2 = jest.fn();
            
            eventBus.on('test-event', callback1);
            eventBus.on('test-event', callback2);
            
            eventBus.off('test-event');
            eventBus.emit('test-event', {});
            
            expect(callback1).not.toHaveBeenCalled();
            expect(callback2).not.toHaveBeenCalled();
        });
    });

    describe('once - one-time listeners', () => {
        it('should trigger listener only once', () => {
            const callback = jest.fn();
            
            eventBus.once('test-event', callback);
            
            eventBus.emit('test-event', { first: true });
            eventBus.emit('test-event', { second: true });
            
            expect(callback).toHaveBeenCalledTimes(1);
            expect(callback).toHaveBeenCalledWith({ first: true });
        });

        it('should automatically remove listener after triggering', () => {
            const callback = jest.fn();
            
            eventBus.once('test-event', callback);
            eventBus.emit('test-event', {});
            
            expect(eventBus.events.get('test-event')).toHaveLength(0);
        });
    });

    describe('debounce functionality', () => {
        it('should debounce function calls', async () => {
            const callback = jest.fn();
            const debouncedCallback = eventBus.debounce(callback, 100);
            
            debouncedCallback('call1');
            debouncedCallback('call2');
            debouncedCallback('call3');
            
            // 立即檢查 - 應該還沒有調用
            expect(callback).not.toHaveBeenCalled();
            
            // 等待防抖延遲
            await TestUtils.sleep(150);
            
            // 現在應該只調用一次，使用最後的參數
            expect(callback).toHaveBeenCalledTimes(1);
            expect(callback).toHaveBeenCalledWith('call3');
        });

        it('should reset debounce timer on subsequent calls', async () => {
            const callback = jest.fn();
            const debouncedCallback = eventBus.debounce(callback, 100);
            
            debouncedCallback('call1');
            
            await TestUtils.sleep(50);
            debouncedCallback('call2'); // 重置計時器
            
            await TestUtils.sleep(50);
            expect(callback).not.toHaveBeenCalled(); // 還在防抖期間
            
            await TestUtils.sleep(60);
            expect(callback).toHaveBeenCalledWith('call2');
        });
    });

    describe('throttle functionality', () => {
        it('should throttle function calls', async () => {
            const callback = jest.fn();
            const throttledCallback = eventBus.throttle(callback, 100);
            
            throttledCallback('call1');
            throttledCallback('call2');
            throttledCallback('call3');
            
            // 第一次調用應該立即執行
            expect(callback).toHaveBeenCalledTimes(1);
            expect(callback).toHaveBeenCalledWith('call1');
            
            // 等待節流期間過去
            await TestUtils.sleep(150);
            
            throttledCallback('call4');
            
            expect(callback).toHaveBeenCalledTimes(2);
            expect(callback).toHaveBeenCalledWith('call4');
        });
    });

    describe('batch operations', () => {
        it('should emit multiple events in batch', () => {
            const callback1 = jest.fn();
            const callback2 = jest.fn();
            
            eventBus.on('event1', callback1);
            eventBus.on('event2', callback2);
            
            const events = [
                { event: 'event1', data: { value: 1 } },
                { event: 'event2', data: { value: 2 } }
            ];
            
            eventBus.batchEmit(events);
            
            expect(callback1).toHaveBeenCalledWith({ value: 1 });
            expect(callback2).toHaveBeenCalledWith({ value: 2 });
        });

        it('should handle batch emit errors gracefully', () => {
            const errorCallback = jest.fn().mockImplementation(() => {
                throw new Error('Batch error');
            });
            const normalCallback = jest.fn();
            
            eventBus.on('event1', errorCallback);
            eventBus.on('event2', normalCallback);
            
            const events = [
                { event: 'event1', data: {} },
                { event: 'event2', data: {} }
            ];
            
            expect(() => {
                eventBus.batchEmit(events);
            }).not.toThrow();
            
            expect(normalCallback).toHaveBeenCalled();
        });
    });

    describe('namespaced events', () => {
        it('should support event namespaces', () => {
            const callback = jest.fn();
            
            eventBus.on('cart:item-added', callback);
            eventBus.emit('cart:item-added', { item: 'test' });
            
            expect(callback).toHaveBeenCalledWith({ item: 'test' });
        });

        it('should support wildcard listening', () => {
            const callback = jest.fn();
            
            eventBus.onWildcard('cart:*', callback);
            
            eventBus.emit('cart:item-added', { item: 'test' });
            eventBus.emit('cart:item-removed', { item: 'test' });
            eventBus.emit('order:created', { order: 'test' }); // 不應該觸發
            
            expect(callback).toHaveBeenCalledTimes(2);
        });
    });

    describe('debugging and monitoring', () => {
        it('should log events in debug mode', () => {
            eventBus.debugMode = true;
            const consoleLog = jest.spyOn(console, 'log').mockImplementation();
            
            eventBus.emit('test-event', { test: 'data' });
            
            expect(consoleLog).toHaveBeenCalledWith(
                expect.stringContaining('🚌 Event:'),
                'test-event',
                { test: 'data' }
            );
            
            consoleLog.mockRestore();
        });

        it('should collect event statistics', () => {
            const callback = jest.fn();
            
            eventBus.on('test-event', callback);
            eventBus.emit('test-event', {});
            eventBus.emit('test-event', {});
            
            const stats = eventBus.getEventStats();
            
            expect(stats['test-event']).toEqual({
                listenerCount: 1,
                emitCount: 2,
                lastEmitted: expect.any(Date)
            });
        });
    });

    describe('memory management', () => {
        it('should clean up removed listeners', () => {
            const callback = jest.fn();
            
            eventBus.on('test-event', callback);
            expect(eventBus.events.get('test-event')).toHaveLength(1);
            
            eventBus.off('test-event', callback);
            
            // 清理後應該移除空的事件數組
            expect(eventBus.events.has('test-event')).toBe(false);
        });

        it('should clear all listeners', () => {
            eventBus.on('event1', jest.fn());
            eventBus.on('event2', jest.fn());
            
            eventBus.clearAllListeners();
            
            expect(eventBus.events.size).toBe(0);
        });
    });

    describe('error handling', () => {
        it('should handle invalid event names', () => {
            const callback = jest.fn();
            
            expect(() => {
                eventBus.on('', callback);
            }).toThrow('EventBus: 事件名稱不能為空');
            
            expect(() => {
                eventBus.on(null, callback);
            }).toThrow('EventBus: 事件名稱必須是字串');
        });

        it('should handle circular reference in event data', () => {
            const callback = jest.fn();
            const circularData = { test: 'data' };
            circularData.self = circularData;
            
            eventBus.on('test-event', callback);
            
            expect(() => {
                eventBus.emit('test-event', circularData);
            }).not.toThrow();
            
            expect(callback).toHaveBeenCalled();
        });
    });
});

console.log('🚌 EventBus 測試套件載入完成');
