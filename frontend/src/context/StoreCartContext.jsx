import { createContext, useContext, useState, useEffect, useCallback } from 'react';

const StoreCartContext = createContext(null);

export function StoreCartProvider({ children }) {
  const [cart, setCart] = useState(() => {
    try { return JSON.parse(localStorage.getItem('store_cart') || '[]'); } catch { return []; }
  });

  useEffect(() => {
    localStorage.setItem('store_cart', JSON.stringify(cart));
  }, [cart]);

  const addItem = useCallback((product, qty = 1) => {
    setCart(prev => {
      const existing = prev.find(i => i.product_id === product.id);
      if (existing) {
        return prev.map(i => i.product_id === product.id ? { ...i, quantity: i.quantity + qty } : i);
      }
      return [...prev, { product_id: product.id, name: product.name, price: product.sale_price, quantity: qty, image: product.image }];
    });
  }, []);

  const updateQuantity = useCallback((productId, quantity) => {
    if (quantity < 1) return;
    setCart(prev => prev.map(i => i.product_id === productId ? { ...i, quantity } : i));
  }, []);

  const removeItem = useCallback((productId) => {
    setCart(prev => prev.filter(i => i.product_id !== productId));
  }, []);

  const clearCart = useCallback(() => setCart([]), []);

  const total = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
  const count = cart.reduce((sum, i) => sum + i.quantity, 0);

  return (
    <StoreCartContext.Provider value={{ cart, addItem, updateQuantity, removeItem, clearCart, total, count }}>
      {children}
    </StoreCartContext.Provider>
  );
}

export const useStoreCart = () => useContext(StoreCartContext);
