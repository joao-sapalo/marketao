import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Search, ShoppingCart, Plus, Minus, Trash2, User, DollarSign, StickyNote,
  Barcode, Loader2, ArrowLeft, CreditCard, X,
} from 'lucide-react';
import { createService } from '../../services/crudService';

const productService = createService('products');
const customerService = createService('customers');
const saleService = createService('sales');

export default function PDV() {
  const navigate = useNavigate();
  const [products, setProducts] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [search, setSearch] = useState('');
  const [barcode, setBarcode] = useState('');
  const [cart, setCart] = useState([]);
  const [selectedCustomer, setSelectedCustomer] = useState('');
  const [discount, setDiscount] = useState(0);
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(false);
  const [selling, setSelling] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showProducts, setShowProducts] = useState(true);

  useEffect(() => {
    Promise.all([
      productService.getAll({ perPage: 100 }),
      customerService.getAll({ perPage: 100 }),
    ]).then(([pRes, cRes]) => {
      const pd = pRes.data.data || pRes.data;
      const cd = cRes.data.data || cRes.data;
      setProducts(Array.isArray(pd) ? pd : pd.data || []);
      setCustomers(Array.isArray(cd) ? cd : cd.data || []);
    }).catch(() => setError('Erro ao carregar dados.'));
  }, []);

  const filteredProducts = products.filter(
    (p) =>
      p.name?.toLowerCase().includes(search.toLowerCase()) ||
      p.code?.toLowerCase().includes(search.toLowerCase())
  );

  const addToCart = useCallback((product) => {
    setCart((prev) => {
      const existing = prev.find((item) => item.product_id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.product_id === product.id
            ? { ...item, qty: item.qty + 1 }
            : item
        );
      }
      return [...prev, { product_id: product.id, name: product.name, price: Number(product.sale_price || 0), qty: 1 }];
    });
  }, []);

  const handleBarcode = (e) => {
    e.preventDefault();
    const product = products.find(
      (p) => p.code === barcode || p.barcode === barcode
    );
    if (product) {
      addToCart(product);
      setBarcode('');
    } else {
      setError('Produto não encontrado para este código.');
      setTimeout(() => setError(''), 2000);
    }
  };

  const updateQty = (productId, delta) => {
    setCart((prev) =>
      prev
        .map((item) =>
          item.product_id === productId
            ? { ...item, qty: Math.max(1, item.qty + delta) }
            : item
        )
        .filter((item) => item.qty > 0)
    );
  };

  const removeFromCart = (productId) => {
    setCart((prev) => prev.filter((item) => item.product_id !== productId));
  };

  const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const total = Math.max(0, subtotal - discount);

  const handleCompleteSale = async () => {
    if (cart.length === 0) { setError('Adicione produtos ao carrinho.'); return; }
    setSelling(true);
    setError('');
    try {
      await saleService.create({
        customer_id: selectedCustomer || null,
        items: cart.map(({ product_id, qty, price }) => ({ product_id, quantity: qty, unit_price: price })),
        discount,
        notes,
        status: 'completed',
      });
      setSuccess('Venda concluída com sucesso!');
      setTimeout(() => {
        setCart([]);
        setSelectedCustomer('');
        setDiscount(0);
        setNotes('');
        setSuccess('');
        navigate('/sales');
      }, 1500);
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao finalizar venda.');
    } finally {
      setSelling(false);
    }
  };

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="h-[calc(100vh-4rem)] flex flex-col">
      <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/sales')} className="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <CreditCard className="w-6 h-6 text-blue-600" />
          <h1 className="text-xl font-bold text-slate-900 dark:text-white">Ponto de Venda</h1>
        </div>
      </div>

      {error && (
        <div className="mx-4 mt-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-2 text-sm">{error}</div>
      )}
      {success && (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="mx-4 mt-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-lg px-4 py-2 text-sm">{success}</motion.div>
      )}

      <div className="flex-1 flex flex-col lg:flex-row overflow-hidden">
        <div className="flex-1 flex flex-col overflow-hidden border-r border-slate-200 dark:border-slate-700">
          <div className="p-4 space-y-3 border-b border-slate-200 dark:border-slate-700">
            <form onSubmit={handleBarcode} className="flex gap-2">
              <div className="relative flex-1">
                <Barcode className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                <input
                  type="text"
                  value={barcode}
                  onChange={(e) => setBarcode(e.target.value)}
                  placeholder="Código de barras..."
                  className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                />
              </div>
              <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium">Adicionar</button>
            </form>
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Buscar produtos..."
                className="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
              />
            </div>
          </div>

          <div className="flex-1 overflow-y-auto p-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
              {filteredProducts.map((product) => (
                <motion.button
                  key={product.id}
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  onClick={() => addToCart(product)}
                  className="text-left bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-700 transition"
                >
                  <p className="font-medium text-slate-900 dark:text-white text-sm truncate">{product.name}</p>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{product.code}</p>
                  <p className="text-lg font-bold text-blue-600 mt-2">{Number(product.sale_price || 0).toLocaleString()} Kz</p>
                  <p className="text-xs text-slate-400">Stock: {product.quantity ?? 0}</p>
                </motion.button>
              ))}
              {filteredProducts.length === 0 && (
                <div className="col-span-full text-center py-8 text-slate-400">Nenhum produto encontrado</div>
              )}
            </div>
          </div>
        </div>

        <div className="w-full lg:w-96 flex flex-col bg-white dark:bg-slate-800">
          <div className="p-4 border-b border-slate-200 dark:border-slate-700 space-y-3">
            <div className="relative">
              <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <select
                value={selectedCustomer}
                onChange={(e) => setSelectedCustomer(e.target.value)}
                className="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
              >
                <option value="">Cliente avulso</option>
                {customers.map((c) => (<option key={c.id} value={c.id}>{c.name}</option>))}
              </select>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto p-4 space-y-2">
            <AnimatePresence>
              {cart.map((item) => (
                <motion.div
                  key={item.product_id}
                  initial={{ opacity: 0, x: 50 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 50 }}
                  className="flex items-center gap-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3"
                >
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-slate-900 dark:text-white truncate">{item.name}</p>
                    <p className="text-xs text-slate-500">{Number(item.price).toLocaleString()} Kz</p>
                  </div>
                  <div className="flex items-center gap-1">
                    <button onClick={() => updateQty(item.product_id, -1)} className="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500"><Minus className="w-3.5 h-3.5" /></button>
                    <span className="w-8 text-center text-sm font-medium text-slate-900 dark:text-white">{item.qty}</span>
                    <button onClick={() => updateQty(item.product_id, 1)} className="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500"><Plus className="w-3.5 h-3.5" /></button>
                  </div>
                  <p className="text-sm font-medium text-slate-900 dark:text-white w-20 text-right">{(item.price * item.qty).toLocaleString()} Kz</p>
                  <button onClick={() => removeFromCart(item.product_id)} className="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"><Trash2 className="w-4 h-4" /></button>
                </motion.div>
              ))}
            </AnimatePresence>
            {cart.length === 0 && (
              <div className="flex flex-col items-center justify-center h-full text-slate-400">
                <ShoppingCart className="w-12 h-12 mb-2" />
                <p className="text-sm">Carrinho vazio</p>
              </div>
            )}
          </div>

          <div className="p-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
            <div className="relative">
              <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input
                type="number"
                value={discount}
                onChange={(e) => setDiscount(Number(e.target.value) || 0)}
                placeholder="Desconto (Kz)"
                className="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
              />
            </div>
            <div className="relative">
              <StickyNote className="absolute left-3 top-3 w-5 h-5 text-slate-400" />
              <textarea
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                rows={2}
                placeholder="Observações..."
                className="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"
              />
            </div>
            <div className="flex justify-between items-center py-2">
              <span className="text-sm text-slate-500 dark:text-slate-400">Subtotal</span>
              <span className="font-medium text-slate-900 dark:text-white">{subtotal.toLocaleString()} Kz</span>
            </div>
            <div className="flex justify-between items-center text-lg font-bold">
              <span className="text-slate-900 dark:text-white">Total</span>
              <span className="text-blue-600">{total.toLocaleString()} Kz</span>
            </div>
            <button
              onClick={handleCompleteSale}
              disabled={selling || cart.length === 0}
              className="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {selling ? <Loader2 className="w-5 h-5 animate-spin" /> : <CreditCard className="w-5 h-5" />}
              {selling ? 'Finalizando...' : 'Finalizar Venda'}
            </button>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
