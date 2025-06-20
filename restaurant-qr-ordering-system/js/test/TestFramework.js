// 輕量級測試框架 - 適用於原生 JavaScript 專案
// 提供類似 Jest 的 API 和功能

class TestFramework {
    constructor() {
        this.tests = [];
        this.suites = [];
        this.currentSuite = null;
        this.results = {
            passed: 0,
            failed: 0,
            total: 0,
            errors: []
        };
    }

    // 描述測試套件
    describe(name, fn) {
        const suite = {
            name,
            tests: [],
            beforeEach: null,
            afterEach: null,
            beforeAll: null,
            afterAll: null
        };
        
        this.suites.push(suite);
        this.currentSuite = suite;
        
        console.group(`📋 測試套件: ${name}`);
        
        try {
            fn();
        } catch (error) {
            console.error(`測試套件 "${name}" 設置失敗:`, error);
        }
        
        console.groupEnd();
        this.currentSuite = null;
    }

    // 定義測試案例
    it(name, fn) {
        const test = {
            name,
            fn,
            suite: this.currentSuite ? this.currentSuite.name : 'Global'
        };
        
        if (this.currentSuite) {
            this.currentSuite.tests.push(test);
        } else {
            this.tests.push(test);
        }
    }

    // 生命週期鉤子
    beforeEach(fn) {
        if (this.currentSuite) {
            this.currentSuite.beforeEach = fn;
        }
    }

    afterEach(fn) {
        if (this.currentSuite) {
            this.currentSuite.afterEach = fn;
        }
    }

    beforeAll(fn) {
        if (this.currentSuite) {
            this.currentSuite.beforeAll = fn;
        }
    }

    afterAll(fn) {
        if (this.currentSuite) {
            this.currentSuite.afterAll = fn;
        }
    }

    // 運行所有測試
    async runTests() {
        console.log('🧪 開始運行測試...');
        this.results = { passed: 0, failed: 0, total: 0, errors: [] };

        // 運行全域測試
        for (const test of this.tests) {
            await this.runSingleTest(test);
        }

        // 運行測試套件
        for (const suite of this.suites) {
            await this.runTestSuite(suite);
        }

        this.printResults();
        return this.results;
    }

    // 運行單一測試套件
    async runTestSuite(suite) {
        console.group(`🧪 運行測試套件: ${suite.name}`);

        try {
            // beforeAll
            if (suite.beforeAll) {
                await suite.beforeAll();
            }

            // 運行測試
            for (const test of suite.tests) {
                try {
                    // beforeEach
                    if (suite.beforeEach) {
                        await suite.beforeEach();
                    }

                    await this.runSingleTest(test);

                    // afterEach
                    if (suite.afterEach) {
                        await suite.afterEach();
                    }
                } catch (error) {
                    console.error(`測試 "${test.name}" 生命週期錯誤:`, error);
                    this.results.failed++;
                    this.results.errors.push({ test: test.name, error });
                }
            }

            // afterAll
            if (suite.afterAll) {
                await suite.afterAll();
            }

        } catch (error) {
            console.error(`測試套件 "${suite.name}" 運行失敗:`, error);
        }

        console.groupEnd();
    }

    // 運行單一測試
    async runSingleTest(test) {
        this.results.total++;

        try {
            await test.fn();
            console.log(`✅ ${test.name}`);
            this.results.passed++;
        } catch (error) {
            console.error(`❌ ${test.name}:`, error.message);
            this.results.failed++;
            this.results.errors.push({ test: test.name, error });
        }
    }

    // 打印測試結果
    printResults() {
        const { passed, failed, total } = this.results;
        const passRate = ((passed / total) * 100).toFixed(1);

        console.log('\n📊 測試結果統計:');
        console.log(`總計: ${total} 個測試`);
        console.log(`✅ 通過: ${passed} 個 (${passRate}%)`);
        console.log(`❌ 失敗: ${failed} 個`);

        if (failed > 0) {
            console.group('❌ 失敗的測試:');
            this.results.errors.forEach(({ test, error }) => {
                console.error(`- ${test}: ${error.message}`);
            });
            console.groupEnd();
        }
    }
}

// 全域測試實例
const testFramework = new TestFramework();

// 導出全域函數
window.describe = testFramework.describe.bind(testFramework);
window.it = testFramework.it.bind(testFramework);
window.beforeEach = testFramework.beforeEach.bind(testFramework);
window.afterEach = testFramework.afterEach.bind(testFramework);
window.beforeAll = testFramework.beforeAll.bind(testFramework);
window.afterAll = testFramework.afterAll.bind(testFramework);

// 運行測試
window.runTests = testFramework.runTests.bind(testFramework);

// 斷言工具
class Expect {
    constructor(actual) {
        this.actual = actual;
    }

    toBe(expected) {
        if (this.actual !== expected) {
            throw new Error(`期望值 ${expected}，但得到 ${this.actual}`);
        }
        return this;
    }

    toEqual(expected) {
        if (JSON.stringify(this.actual) !== JSON.stringify(expected)) {
            throw new Error(`期望值 ${JSON.stringify(expected)}，但得到 ${JSON.stringify(this.actual)}`);
        }
        return this;
    }

    toBeNull() {
        if (this.actual !== null) {
            throw new Error(`期望值為 null，但得到 ${this.actual}`);
        }
        return this;
    }

    toBeUndefined() {
        if (this.actual !== undefined) {
            throw new Error(`期望值為 undefined，但得到 ${this.actual}`);
        }
        return this;
    }

    toBeTruthy() {
        if (!this.actual) {
            throw new Error(`期望值為真值，但得到 ${this.actual}`);
        }
        return this;
    }

    toBeFalsy() {
        if (this.actual) {
            throw new Error(`期望值為假值，但得到 ${this.actual}`);
        }
        return this;
    }

    toHaveLength(length) {
        if (!this.actual || this.actual.length !== length) {
            throw new Error(`期望長度為 ${length}，但得到 ${this.actual ? this.actual.length : 'undefined'}`);
        }
        return this;
    }

    toContain(item) {
        if (!this.actual || !this.actual.includes || !this.actual.includes(item)) {
            throw new Error(`期望包含 ${item}，但在 ${JSON.stringify(this.actual)} 中未找到`);
        }
        return this;
    }

    toThrow(expectedError) {
        if (typeof this.actual !== 'function') {
            throw new Error('toThrow 只能用於函數');
        }

        try {
            this.actual();
            throw new Error('期望函數拋出錯誤，但未拋出');
        } catch (error) {
            if (expectedError && error.message !== expectedError) {
                throw new Error(`期望錯誤訊息 "${expectedError}"，但得到 "${error.message}"`);
            }
        }
        return this;
    }

    async rejects() {
        if (!(this.actual instanceof Promise)) {
            throw new Error('rejects 只能用於 Promise');
        }

        try {
            await this.actual;
            throw new Error('期望 Promise 被拒絕，但被解決');
        } catch (error) {
            // 期望的行為
        }
        return this;
    }    async resolves() {
        if (!(this.actual instanceof Promise)) {
            throw new Error('resolves 只能用於 Promise');
        }

        try {
            await this.actual;
        } catch (error) {
            throw new Error(`期望 Promise 被解決，但被拒絕: ${error.message}`);
        }
        return this;
    }

    toMatchObject(expectedObject) {
        if (typeof this.actual !== 'object' || this.actual === null) {
            throw new Error(`期望值為物件，但得到 ${typeof this.actual}`);
        }

        const isMatch = this.objectMatches(this.actual, expectedObject);
        if (!isMatch) {
            throw new Error(`期望物件匹配 ${JSON.stringify(expectedObject)}，但實際為 ${JSON.stringify(this.actual)}`);
        }
        return this;
    }    objectMatches(actual, expected) {
        for (const key in expected) {
            if (expected.hasOwnProperty(key)) {
                if (!(key in actual)) {
                    return false;
                }
                
                const expectedValue = expected[key];
                const actualValue = actual[key];
                
                // 處理 expect.any() 的情況
                if (typeof expectedValue === 'function' && expectedValue.constructor) {
                    const expectedType = expectedValue.constructor;
                    if (expectedType === String && typeof actualValue !== 'string') return false;
                    if (expectedType === Number && typeof actualValue !== 'number') return false;
                    if (expectedType === Boolean && typeof actualValue !== 'boolean') return false;
                    if (expectedType === Array && !Array.isArray(actualValue)) return false;
                    if (expectedType === Object && (typeof actualValue !== 'object' || actualValue === null)) return false;
                    if (expectedType === Date && !(actualValue instanceof Date)) return false;
                    continue;
                }
                
                if (typeof expectedValue === 'object' && expectedValue !== null) {
                    if (!this.objectMatches(actualValue, expectedValue)) {
                        return false;
                    }
                } else if (actualValue !== expectedValue) {
                    return false;
                }
            }        }
        return true;
    }

    // Mock 函數斷言方法
    toHaveBeenCalled() {
        if (typeof this.actual !== 'function' || !this.actual.calls) {
            throw new Error('toHaveBeenCalled 只能用於 Mock 函數');
        }
        
        if (this.actual.calls.length === 0) {
            throw new Error('期望函數被調用，但未被調用');
        }
        return this;
    }

    toHaveBeenCalledTimes(expectedCalls) {
        if (typeof this.actual !== 'function' || !this.actual.calls) {
            throw new Error('toHaveBeenCalledTimes 只能用於 Mock 函數');
        }
        
        if (this.actual.calls.length !== expectedCalls) {
            throw new Error(`期望函數被調用 ${expectedCalls} 次，但被調用 ${this.actual.calls.length} 次`);
        }
        return this;
    }

    toHaveBeenCalledWith(...expectedArgs) {
        if (typeof this.actual !== 'function' || !this.actual.calls) {
            throw new Error('toHaveBeenCalledWith 只能用於 Mock 函數');
        }
        
        const matchingCall = this.actual.calls.find(call => 
            call.length === expectedArgs.length && 
            call.every((arg, index) => arg === expectedArgs[index])
        );
        
        if (!matchingCall) {
            throw new Error(`期望函數被調用時參數為 ${JSON.stringify(expectedArgs)}，但未找到匹配的調用`);
        }
        return this;
    }
}

// 全域 expect 函數
window.expect = (actual) => new Expect(actual);

// 添加 expect.any 支援
window.expect.any = (constructor) => {
    const anyMatcher = function() {};
    anyMatcher.constructor = constructor;
    anyMatcher.toString = () => `expect.any(${constructor.name})`;
    return anyMatcher;
};

// Jest 風格的 Mock 函數
class MockFunction {
    constructor() {
        this.calls = [];
        this.results = [];
        this.implementation = null;
    }

    mockImplementation(fn) {
        this.implementation = fn;
        return this;
    }

    mockReturnValue(value) {
        this.implementation = () => value;
        return this;
    }

    mockResolvedValue(value) {
        this.implementation = () => Promise.resolve(value);
        return this;
    }

    mockRejectedValue(error) {
        this.implementation = () => Promise.reject(error);
        return this;
    }

    mockClear() {
        this.calls = [];
        this.results = [];
        return this;
    }

    // 模擬函數調用
    call(...args) {
        this.calls.push(args);
        
        if (this.implementation) {
            try {
                const result = this.implementation(...args);
                this.results.push({ type: 'return', value: result });
                return result;
            } catch (error) {
                this.results.push({ type: 'throw', value: error });
                throw error;
            }
        }
        
        this.results.push({ type: 'return', value: undefined });
        return undefined;
    }

    // 檢查調用
    toHaveBeenCalled() {
        if (this.calls.length === 0) {
            throw new Error('期望函數被調用，但未被調用');
        }
        return this;
    }

    toHaveBeenCalledTimes(expectedCalls) {
        if (this.calls.length !== expectedCalls) {
            throw new Error(`期望函數被調用 ${expectedCalls} 次，但被調用 ${this.calls.length} 次`);
        }
        return this;
    }    toHaveBeenCalledWith(...expectedArgs) {
        const matchingCall = this.calls.find(call => 
            call.length === expectedArgs.length && 
            call.every((arg, index) => arg === expectedArgs[index])
        );
        
        if (!matchingCall) {
            throw new Error(`期望函數被調用時參數為 ${JSON.stringify(expectedArgs)}，但未找到匹配的調用`);
        }
        return this;
    }
}

// 創建 Mock 函數
window.jest = {
    fn: (implementation) => {
        const mock = new MockFunction();
        if (implementation) {
            mock.mockImplementation(implementation);
        }
        
        // 返回可調用的函數，並將 Mock 方法綁定到函數
        const mockFn = function(...args) {
            return mock.call(...args);
        };
        
        // 直接將所有方法綁定到 mockFn
        mockFn.mockImplementation = mock.mockImplementation.bind(mock);
        mockFn.mockReturnValue = mock.mockReturnValue.bind(mock);
        mockFn.mockResolvedValue = mock.mockResolvedValue.bind(mock);
        mockFn.mockRejectedValue = mock.mockRejectedValue.bind(mock);
        mockFn.mockClear = mock.mockClear.bind(mock);
        mockFn.toHaveBeenCalled = mock.toHaveBeenCalled.bind(mock);
        mockFn.toHaveBeenCalledTimes = mock.toHaveBeenCalledTimes.bind(mock);
        mockFn.toHaveBeenCalledWith = mock.toHaveBeenCalledWith.bind(mock);
        
        // 存取 calls 和 results
        Object.defineProperty(mockFn, 'calls', {
            get: () => mock.calls
        });
        Object.defineProperty(mockFn, 'results', {
            get: () => mock.results
        });
        
        return mockFn;
    },
    
    spyOn: (object, methodName) => {
        const originalMethod = object[methodName];
        const spy = jest.fn(originalMethod);
        
        object[methodName] = spy;
        
        spy.mockRestore = () => {
            object[methodName] = originalMethod;
        };
        
        return spy;
    }
};

console.log('🧪 測試框架載入完成');
