// 購物車管理器測試套件
// 測試 CartManager 的所有功能

describe('CartManager', () => {
    let cartManager;
    let mockStorage;
    let mockEventBus;

    beforeEach(() => {
        // 設置 Mock 服務
        mockStorage = {
            getItem: jest.fn().mockReturnValue(null),
            setItem: jest.fn(),
            removeItem: jest.fn(),
            clear: jest.fn()
        };
        
        mockEventBus = TestUtils.createMockEventBus();
        
        // 創建 CartManager 實例
        cartManager = new CartManager(mockStorage);
        cartManager.eventBus = mockEventBus;
    });

    afterEach(() => {
        TestUtils.cleanup();
    });

    describe('constructor', () => {
        it('should initialize with empty cart', () => {
            expect(cartManager.items).toEqual([]);
        });

        it('should set storage service', () => {
            expect(cartManager.storage).toBe(mockStorage);
        });

        it('should load existing cart from storage', () => {
            const existingCart = [TestDataFactory.createCartItem()];
            mockStorage.getItem.mockReturnValue(existingCart);
            
            const newCartManager = new CartManager(mockStorage);
            
            expect(newCartManager.items).toEqual(existingCart);
            expect(mockStorage.getItem).toHaveBeenCalledWith('cart');
        });
    });

    describe('addItem', () => {
        it('should add new item to empty cart', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, 1);
            
            expect(cartManager.items).toHaveLength(1);
            expect(cartManager.items[0]).toMatchObject({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: 1
            });
        });

        it('should increase quantity for existing item', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, 1);
            cartManager.addItem(item, 2);
            
            expect(cartManager.items).toHaveLength(1);
            expect(cartManager.items[0].quantity).toBe(3);
        });

        it('should save cart to storage', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, 1);
            
            expect(mockStorage.setItem).toHaveBeenCalledWith('cart', cartManager.items);
        });

        it('should emit cart updated event', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, 1);
            
            expect(mockEventBus.emit).toHaveBeenCalledWith(
                'cart:item-added',
                expect.objectContaining({
                    item: expect.objectContaining({ id: item.id }),
                    quantity: 1
                })
            );
        });

        it('should handle zero quantity', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, 0);
            
            expect(cartManager.items).toHaveLength(0);
        });

        it('should handle negative quantity', () => {
            const item = TestDataFactory.createMenuItem();
            
            cartManager.addItem(item, -1);
            
            expect(cartManager.items).toHaveLength(0);
        });
    });

    describe('removeItem', () => {
        beforeEach(() => {
            const item = TestDataFactory.createCartItem();
            cartManager.items = [item];
        });

        it('should remove item by id', () => {
            const item = cartManager.items[0];
            
            cartManager.removeItem(item.id);
            
            expect(cartManager.items).toHaveLength(0);
        });

        it('should save cart to storage after removal', () => {
            const item = cartManager.items[0];
            
            cartManager.removeItem(item.id);
            
            expect(mockStorage.setItem).toHaveBeenCalledWith('cart', []);
        });

        it('should emit cart updated event', () => {
            const item = cartManager.items[0];
            
            cartManager.removeItem(item.id);
            
            expect(mockEventBus.emit).toHaveBeenCalledWith(
                'cart:item-removed',
                expect.objectContaining({
                    itemId: item.id
                })
            );
        });

        it('should handle non-existent item', () => {
            const originalLength = cartManager.items.length;
            
            cartManager.removeItem('non-existent-id');
            
            expect(cartManager.items).toHaveLength(originalLength);
        });
    });

    describe('updateQuantity', () => {
        beforeEach(() => {
            const item = TestDataFactory.createCartItem({ quantity: 2 });
            cartManager.items = [item];
        });

        it('should update item quantity', () => {
            const item = cartManager.items[0];
            
            cartManager.updateQuantity(item.id, 5);
            
            expect(cartManager.items[0].quantity).toBe(5);
        });

        it('should remove item when quantity is zero', () => {
            const item = cartManager.items[0];
            
            cartManager.updateQuantity(item.id, 0);
            
            expect(cartManager.items).toHaveLength(0);
        });

        it('should handle negative quantity', () => {
            const item = cartManager.items[0];
            const originalQuantity = item.quantity;
            
            cartManager.updateQuantity(item.id, -1);
            
            expect(cartManager.items[0].quantity).toBe(originalQuantity);
        });

        it('should save cart to storage', () => {
            const item = cartManager.items[0];
            
            cartManager.updateQuantity(item.id, 3);
            
            expect(mockStorage.setItem).toHaveBeenCalledWith('cart', cartManager.items);
        });

        it('should emit quantity updated event', () => {
            const item = cartManager.items[0];
            
            cartManager.updateQuantity(item.id, 3);
            
            expect(mockEventBus.emit).toHaveBeenCalledWith(
                'cart:quantity-updated',
                expect.objectContaining({
                    itemId: item.id,
                    quantity: 3
                })
            );
        });
    });

    describe('getCart', () => {
        it('should return cart items', () => {
            const items = TestDataFactory.createCart(2);
            cartManager.items = items;
            
            const result = cartManager.getCart();
            
            expect(result).toEqual(items);
        });

        it('should return empty array when cart is empty', () => {
            const result = cartManager.getCart();
            
            expect(result).toEqual([]);
        });
    });

    describe('getTotalItems', () => {
        it('should return total quantity of all items', () => {
            const items = [
                TestDataFactory.createCartItem({ quantity: 2 }),
                TestDataFactory.createCartItem({ quantity: 3 }),
                TestDataFactory.createCartItem({ quantity: 1 })
            ];
            cartManager.items = items;
            
            const total = cartManager.getTotalItems();
            
            expect(total).toBe(6);
        });

        it('should return 0 for empty cart', () => {
            const total = cartManager.getTotalItems();
            
            expect(total).toBe(0);
        });
    });

    describe('getTotalPrice', () => {
        it('should calculate total price correctly', () => {
            const items = [
                TestDataFactory.createCartItem({ price: 100, quantity: 2 }),
                TestDataFactory.createCartItem({ price: 150, quantity: 1 })
            ];
            cartManager.items = items;
            
            const total = cartManager.getTotalPrice();
            
            expect(total).toBe(350); // (100*2) + (150*1)
        });

        it('should return 0 for empty cart', () => {
            const total = cartManager.getTotalPrice();
            
            expect(total).toBe(0);
        });
    });

    describe('clearCart', () => {
        beforeEach(() => {
            const items = TestDataFactory.createCart(3);
            cartManager.items = items;
        });

        it('should clear all items', () => {
            cartManager.clearCart();
            
            expect(cartManager.items).toHaveLength(0);
        });

        it('should clear storage', () => {
            cartManager.clearCart();
            
            expect(mockStorage.removeItem).toHaveBeenCalledWith('cart');
        });

        it('should emit cart cleared event', () => {
            cartManager.clearCart();
            
            expect(mockEventBus.emit).toHaveBeenCalledWith('cart:cleared', {});
        });
    });

    describe('findItem', () => {
        beforeEach(() => {
            const items = TestDataFactory.createCart(3);
            cartManager.items = items;
        });

        it('should find item by id', () => {
            const targetItem = cartManager.items[1];
            
            const found = cartManager.findItem(targetItem.id);
            
            expect(found).toBe(targetItem);
        });

        it('should return null for non-existent item', () => {
            const found = cartManager.findItem('non-existent-id');
            
            expect(found).toBeNull();
        });
    });

    describe('isValidItem', () => {
        it('should validate required properties', () => {
            const validItem = TestDataFactory.createMenuItem();
            
            expect(cartManager.isValidItem(validItem)).toBe(true);
        });

        it('should reject item without id', () => {
            const invalidItem = TestDataFactory.createMenuItem({ id: null });
            
            expect(cartManager.isValidItem(invalidItem)).toBe(false);
        });

        it('should reject item without name', () => {
            const invalidItem = TestDataFactory.createMenuItem({ name: '' });
            
            expect(cartManager.isValidItem(invalidItem)).toBe(false);
        });

        it('should reject item with invalid price', () => {
            const invalidItem = TestDataFactory.createMenuItem({ price: -10 });
            
            expect(cartManager.isValidItem(invalidItem)).toBe(false);
        });
    });

    describe('error handling', () => {
        it('should handle storage errors gracefully', () => {
            mockStorage.setItem.mockImplementation(() => {
                throw new Error('Storage error');
            });
            
            const item = TestDataFactory.createMenuItem();
            
            expect(() => cartManager.addItem(item, 1)).not.toThrow();
        });

        it('should handle invalid item data', () => {
            const invalidItem = { id: null };
            
            expect(() => cartManager.addItem(invalidItem, 1)).not.toThrow();
            expect(cartManager.items).toHaveLength(0);
        });
    });
});

console.log('🛒 CartManager 測試套件載入完成');
