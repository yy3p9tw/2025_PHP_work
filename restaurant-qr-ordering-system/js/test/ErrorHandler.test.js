// ErrorHandler 測試套件
// 測試統一錯誤處理系統的所有功能

describe('ErrorHandler', () => {
    let errorHandler;
    let originalConsoleError;
    let mockConsoleError;

    beforeEach(() => {
        // 重置單例實例
        ErrorHandler.instance = null;
        errorHandler = ErrorHandler.getInstance();
        
        // Mock console.error
        originalConsoleError = console.error;
        mockConsoleError = jest.fn();
        console.error = mockConsoleError;
    });

    afterEach(() => {
        // 恢復 console.error
        console.error = originalConsoleError;
        
        // 清理錯誤日誌
        errorHandler.errorLog = [];
        
        TestUtils.cleanup();
    });

    describe('singleton pattern', () => {
        it('should return same instance', () => {
            const instance1 = ErrorHandler.getInstance();
            const instance2 = ErrorHandler.getInstance();
            
            expect(instance1).toBe(instance2);
        });

        it('should maintain error log across instances', () => {
            const instance1 = ErrorHandler.getInstance();
            const testError = new Error('Test error');
            instance1.handleError(testError);
            
            const instance2 = ErrorHandler.getInstance();
            
            expect(instance2.errorLog).toHaveLength(1);
        });
    });

    describe('handleError - synchronous error handling', () => {
        it('should log error information', () => {
            const testError = new Error('Test error message');
            const context = { component: 'TestComponent', action: 'testAction' };
            
            errorHandler.handleError(testError, context);
            
            expect(errorHandler.errorLog).toHaveLength(1);
            expect(errorHandler.errorLog[0]).toMatchObject({
                message: 'Test error message',
                context,
                timestamp: expect.any(String)
            });
        });

        it('should include stack trace', () => {
            const testError = new Error('Test error');
            
            errorHandler.handleError(testError);
            
            expect(errorHandler.errorLog[0].stack).toBeDefined();
            expect(typeof errorHandler.errorLog[0].stack).toBe('string');
        });

        it('should handle AppError instances', () => {
            const appError = new AppError('App error', 'TEST_ERROR', { test: true });
            
            errorHandler.handleError(appError);
            
            const loggedError = errorHandler.errorLog[0];
            expect(loggedError.code).toBe('TEST_ERROR');
            expect(loggedError.context.test).toBe(true);
        });

        it('should display user-friendly message', () => {
            const testError = new Error('Technical error message');
            const mockDisplayMessage = jest.spyOn(errorHandler, 'displayUserFriendlyMessage');
            
            errorHandler.handleError(testError);
            
            expect(mockDisplayMessage).toHaveBeenCalledWith(testError);
        });

        it('should limit error log size', () => {
            const originalMaxLogSize = errorHandler.maxLogSize;
            errorHandler.maxLogSize = 3;
            
            // 添加超過限制的錯誤
            for (let i = 0; i < 5; i++) {
                errorHandler.handleError(new Error(`Error ${i}`));
            }
            
            expect(errorHandler.errorLog).toHaveLength(3);
            expect(errorHandler.errorLog[0].message).toBe('Error 2'); // 最早的被移除
            
            errorHandler.maxLogSize = originalMaxLogSize;
        });
    });

    describe('handleAsyncError - asynchronous error handling', () => {
        it('should handle successful promise', async () => {
            const successValue = 'success';
            const successPromise = Promise.resolve(successValue);
            
            const result = await errorHandler.handleAsyncError(successPromise);
            
            expect(result).toBe(successValue);
            expect(errorHandler.errorLog).toHaveLength(0);
        });

        it('should handle rejected promise', async () => {
            const testError = new Error('Async error');
            const failingPromise = Promise.reject(testError);
            const context = { type: 'async-test' };
            
            await expect(
                errorHandler.handleAsyncError(failingPromise, context)
            ).rejects.toThrow('Async error');
            
            expect(errorHandler.errorLog).toHaveLength(1);
            expect(errorHandler.errorLog[0]).toMatchObject({
                message: 'Async error',
                context: { ...context, type: 'async' }
            });
        });

        it('should preserve original error for rethrowing', async () => {
            const originalError = new AppError('Original error', 'ORIGINAL_CODE');
            const failingPromise = Promise.reject(originalError);
            
            try {
                await errorHandler.handleAsyncError(failingPromise);
            } catch (caughtError) {
                expect(caughtError).toBe(originalError);
                expect(caughtError.code).toBe('ORIGINAL_CODE');
            }
        });
    });

    describe('AppError class', () => {
        it('should create error with all properties', () => {
            const message = 'Custom error message';
            const code = 'CUSTOM_ERROR';
            const context = { component: 'TestComponent' };
            
            const appError = new AppError(message, code, context);
            
            expect(appError.message).toBe(message);
            expect(appError.code).toBe(code);
            expect(appError.context).toEqual(context);
            expect(appError.name).toBe('AppError');
            expect(appError.timestamp).toBeDefined();
        });

        it('should use default values', () => {
            const appError = new AppError('Test message');
            
            expect(appError.code).toBe('UNKNOWN_ERROR');
            expect(appError.context).toEqual({});
        });

        it('should maintain stack trace', () => {
            const appError = new AppError('Test message');
            
            expect(appError.stack).toBeDefined();
            expect(appError.stack).toContain('AppError');
        });
    });

    describe('error categorization', () => {
        it('should categorize validation errors', () => {
            const validationError = new AppError(
                'Empty cart', 
                ERROR_CODES.VALIDATION.EMPTY_CART
            );
            
            errorHandler.handleError(validationError);
            
            const category = errorHandler.categorizeError(validationError);
            expect(category).toBe('VALIDATION');
        });

        it('should categorize network errors', () => {
            const networkError = new AppError(
                'Connection failed', 
                ERROR_CODES.NETWORK.CONNECTION_FAILED
            );
            
            const category = errorHandler.categorizeError(networkError);
            expect(category).toBe('NETWORK');
        });

        it('should handle unknown error codes', () => {
            const unknownError = new AppError('Unknown error', 'UNKNOWN_CODE');
            
            const category = errorHandler.categorizeError(unknownError);
            expect(category).toBe('UNKNOWN');
        });
    });

    describe('user-friendly messages', () => {
        it('should provide friendly message for validation errors', () => {
            const validationError = new AppError(
                'Technical validation error', 
                ERROR_CODES.VALIDATION.EMPTY_CART
            );
            
            const friendlyMessage = errorHandler.getFriendlyMessage(validationError);
            
            expect(friendlyMessage).toContain('購物車');
            expect(friendlyMessage).not.toContain('Technical');
        });

        it('should provide friendly message for network errors', () => {
            const networkError = new AppError(
                'Connection timeout', 
                ERROR_CODES.NETWORK.TIMEOUT
            );
            
            const friendlyMessage = errorHandler.getFriendlyMessage(networkError);
            
            expect(friendlyMessage).toContain('網路');
            expect(friendlyMessage).toContain('稍後再試');
        });

        it('should provide generic message for unknown errors', () => {
            const unknownError = new Error('Random error');
            
            const friendlyMessage = errorHandler.getFriendlyMessage(unknownError);
            
            expect(friendlyMessage).toContain('發生未預期的錯誤');
        });
    });

    describe('global error handlers', () => {
        it('should setup global error listeners', () => {
            const addEventListenerSpy = jest.spyOn(window, 'addEventListener');
            
            errorHandler.setupGlobalErrorHandlers();
            
            expect(addEventListenerSpy).toHaveBeenCalledWith(
                'error', 
                expect.any(Function)
            );
            expect(addEventListenerSpy).toHaveBeenCalledWith(
                'unhandledrejection', 
                expect.any(Function)
            );
            
            addEventListenerSpy.mockRestore();
        });

        it('should handle global JavaScript errors', () => {
            const mockEvent = {
                error: new Error('Global error'),
                filename: 'test.js',
                lineno: 42,
                colno: 10
            };
            
            errorHandler.handleGlobalError(mockEvent);
            
            expect(errorHandler.errorLog).toHaveLength(1);
            expect(errorHandler.errorLog[0]).toMatchObject({
                message: 'Global error',
                context: {
                    type: 'global',
                    filename: 'test.js',
                    line: 42,
                    column: 10
                }
            });
        });

        it('should handle unhandled promise rejections', () => {
            const mockEvent = {
                reason: new Error('Unhandled rejection'),
                promise: Promise.reject()
            };
            
            errorHandler.handleUnhandledRejection(mockEvent);
            
            expect(errorHandler.errorLog).toHaveLength(1);
            expect(errorHandler.errorLog[0]).toMatchObject({
                message: 'Unhandled rejection',
                context: {
                    type: 'unhandledRejection'
                }
            });
        });
    });

    describe('error recovery', () => {
        it('should provide recovery suggestions', () => {
            const validationError = new AppError(
                'Invalid table number', 
                ERROR_CODES.VALIDATION.INVALID_TABLE
            );
            
            const suggestions = errorHandler.getRecoverySuggestions(validationError);
            
            expect(suggestions).toContain('重新輸入');
            expect(suggestions.length).toBeGreaterThan(0);
        });

        it('should attempt automatic recovery for certain errors', () => {
            const storageError = new AppError(
                'Storage quota exceeded', 
                ERROR_CODES.DATA.STORAGE_ERROR
            );
            
            const mockClearOldData = jest.spyOn(errorHandler, 'clearOldData');
            mockClearOldData.mockImplementation(() => true);
            
            const recovered = errorHandler.attemptRecovery(storageError);
            
            expect(recovered).toBe(true);
            expect(mockClearOldData).toHaveBeenCalled();
        });
    });

    describe('error reporting', () => {
        it('should format error report', () => {
            const testError = new AppError('Test error', 'TEST_CODE', { test: true });
            errorHandler.handleError(testError);
            
            const report = errorHandler.generateErrorReport();
            
            expect(report).toContain('錯誤統計報告');
            expect(report).toContain('TEST_CODE');
            expect(report).toContain('Test error');
        });

        it('should export error log', () => {
            const errors = [
                new Error('Error 1'),
                new Error('Error 2')
            ];
            
            errors.forEach(error => errorHandler.handleError(error));
            
            const exportedData = errorHandler.exportErrorLog();
            
            expect(exportedData).toContain('Error 1');
            expect(exportedData).toContain('Error 2');
            expect(() => JSON.parse(exportedData)).not.toThrow();
        });
    });

    describe('performance impact', () => {
        it('should not significantly impact performance', () => {
            const startTime = performance.now();
            
            // 處理多個錯誤
            for (let i = 0; i < 100; i++) {
                errorHandler.handleError(new Error(`Error ${i}`));
            }
            
            const endTime = performance.now();
            const duration = endTime - startTime;
            
            // 應該在合理時間內完成（100個錯誤在50ms內）
            expect(duration).toBeLessThan(50);
        });

        it('should batch error notifications', async () => {
            const mockNotify = jest.spyOn(errorHandler, 'notifyUser').mockImplementation();
            
            // 快速觸發多個錯誤
            for (let i = 0; i < 5; i++) {
                errorHandler.handleError(new Error(`Batch error ${i}`));
            }
            
            // 等待批次處理
            await TestUtils.sleep(100);
            
            // 應該只通知一次（批次處理）
            expect(mockNotify).toHaveBeenCalledTimes(1);
        });
    });

    describe('integration with other systems', () => {
        it('should emit error events', () => {
            const mockEventBus = TestUtils.createMockEventBus();
            errorHandler.eventBus = mockEventBus;
            
            const testError = new Error('Test error');
            errorHandler.handleError(testError);
            
            expect(mockEventBus.emit).toHaveBeenCalledWith(
                'error:occurred',
                expect.objectContaining({
                    error: testError
                })
            );
        });

        it('should integrate with logging service', () => {
            const mockLogger = {
                error: jest.fn(),
                warn: jest.fn()
            };
            errorHandler.logger = mockLogger;
            
            const testError = new Error('Test error');
            errorHandler.handleError(testError);
            
            expect(mockLogger.error).toHaveBeenCalledWith(
                expect.stringContaining('Test error')
            );
        });
    });
});

console.log('🛡️ ErrorHandler 測試套件載入完成');
