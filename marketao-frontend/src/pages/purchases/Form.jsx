import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
  ClipboardList, Truck, Calendar, Search, Trash2, Plus, StickyNote,
  Save, Loader2, ArrowLeft, DollarSign,
} from 'lucide-react';
import { createService } from '../../services/crudService';

const purchaseService = createService('purchases');

export default function PurchaseForm() {
  const navigate = useNavigate();
  const [suppliers, setSuppliers] = useState([]);
  const [products, setProducts] = useState([]);
  const [form, setForm] = useState({ supplier_id: '', date: new Date().toISOString().split('T')[0], notes: '' });
  const [items, setItems] = useState([]);
  const [productSearch, setProductSearch] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    Promise.all([
      createService('suppliers').getAll({ perPage: 100 }),
      createService('products').getAll({ perPage: 100 }),
    ]).then(([sRes, pRes]) => {
      const sd = sRes.data.data || sRes.data;
      setSuppliers(Array.isArray(sd) ? sd : sd.data || []);
      const pd = pRes.data.data || pRes.data;
      setProducts(Array.isArray(pd) ? pd : pd.data || []);
    }).catch(() => setError('Erro ao carregar dados.'));
  }, []);

  const filteredProducts = products.filter(
    (p) =>
      p.name?.toLowerCase().includes(productSearch.toLowerCase()) ||
      p.code?.toLowerCase().includes(productSearch.toLowerCase())
  );

  const addItem = (product) => {
    const existing = items.find((i) => i.product_id === product.id);
    if (existing) {
      setItems((prev) => prev.map((i) => (i.product_id === product.id ? { ...i, qty: i.qty + 1 } : i)));
    } else {
      setItems((prev) => [...prev, { product_id: product.id, name: product.name, price: Number(product.purchase_price || 0), qty: 1 }]);
    }
    setProductSearch('');
  };

  const updateItem = (productId, field, value) => {
    setItems((prev) => prev.map((i) => (i.product_id === productId ? { ...i, [field]: value } : i)));
  };

  const removeItem = (productId) => {
    setItems((prev) => prev.filter((i) => i.product_id !== productId));
  };

  const total = items.reduce((sum, item) => sum + item.price * item.qty, 0);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    if (!form.supplier_id) { setError('Selecione um fornecedor.'); return; }
    if (items.length === 0) { setError('Adicione pelo menos um produto.'); return; }
    setLoading(true);
    try {
      await purchaseService.create({
        supplier_id: form.supplier_id,
        date: form.date,
        notes: form.notes,
        total,
        items: items.map(({ product_id, qty, price }) => ({ product_id, quantity: qty, price })),
      });
      setSuccess('Compra registada com sucesso!');
      setTimeout(() => navigate('/purchases'), 1500);
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao salvar compra.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6">
      <button onClick={() => navigate('/purchases')} className="flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mb-4 transition">
        <ArrowLeft className="w-4 h-4" /> Voltar
      </button>

      <div className="max-w-4xl mx-auto">
        <div className="flex items-center gap-3 mb-6">
          <ClipboardList className="w-8 h-8 text-blue-600" />
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Nova Compra</h1>
            <p className="text-sm text-slate-500 dark:text-slate-400">Registe uma nova entrada de compra</p>
          </div>
        </div>

        {error && <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3 mb-6 text-sm">{error}</div>}
        {success && <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-lg px-4 py-3 mb-6 text-sm">{success}</motion.div>}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Fornecedor <span className="text-red-500">*</span></label>
                <div className="relative">
                  <Truck className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                  <select name="supplier_id" value={form.supplier_id} onChange={(e) => setForm((p) => ({ ...p, supplier_id: e.target.value }))} className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition appearance-none">
                    <option value="">Selecione...</option>
                    {suppliers.map((s) => (<option key={s.id} value={s.id}>{s.name}</option>))}
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data</label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                  <input type="date" name="date" value={form.date} onChange={(e) => setForm((p) => ({ ...p, date: e.target.value }))} className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
            <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Produtos</h2>

            <div className="relative mb-4">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input
                type="text"
                value={productSearch}
                onChange={(e) => setProductSearch(e.target.value)}
                placeholder="Buscar produtos para adicionar..."
                className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
              />
            </div>

            {productSearch && (
              <div className="mb-4 max-h-40 overflow-y-auto bg-slate-50 dark:bg-slate-700/30 rounded-lg border border-slate-200 dark:border-slate-600">
                {filteredProducts.map((product) => (
                  <button
                    key={product.id}
                    type="button"
                    onClick={() => addItem(product)}
                    className="w-full text-left px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm transition flex justify-between"
                  >
                    <span>{product.name} <span className="text-slate-400">({product.code})</span></span>
                    <span className="font-medium">{Number(product.purchase_price || 0).toLocaleString()} Kz</span>
                  </button>
                ))}
                {filteredProducts.length === 0 && <p className="px-4 py-2 text-sm text-slate-400">Nenhum produto encontrado</p>}
              </div>
            )}

            <AnimatePresence>
              {items.map((item) => (
                <motion.div
                  key={item.product_id}
                  initial={{ opacity: 0, height: 0 }}
                  animate={{ opacity: 1, height: 'auto' }}
                  exit={{ opacity: 0, height: 0 }}
                  className="flex items-center gap-3 py-2 border-b border-slate-100 dark:border-slate-700/50 last:border-0"
                >
                  <div className="flex-1">
                    <p className="text-sm font-medium text-slate-900 dark:text-white">{item.name}</p>
                  </div>
                  <div className="flex items-center gap-2">
                    <input
                      type="number"
                      value={item.qty}
                      onChange={(e) => updateItem(item.product_id, 'qty', Math.max(1, parseInt(e.target.value) || 1))}
                      className="w-16 px-2 py-1 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-900 dark:text-white text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <input
                      type="number"
                      step="0.01"
                      value={item.price}
                      onChange={(e) => updateItem(item.product_id, 'price', parseFloat(e.target.value) || 0)}
                      className="w-24 px-2 py-1 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-900 dark:text-white text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <span className="text-sm font-medium text-slate-900 dark:text-white w-24 text-right">{(item.price * item.qty).toLocaleString()} Kz</span>
                    <button type="button" onClick={() => removeItem(item.product_id)} className="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"><Trash2 className="w-4 h-4" /></button>
                  </div>
                </motion.div>
              ))}
            </AnimatePresence>
            {items.length === 0 && <p className="text-sm text-slate-400 text-center py-4">Nenhum produto adicionado</p>}

            <div className="flex justify-end items-center gap-4 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <span className="text-lg font-bold text-slate-900 dark:text-white">Total: <span className="text-blue-600">{total.toLocaleString()} Kz</span></span>
            </div>
          </div>

          <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
            <div>
              <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Observações</label>
              <div className="relative">
                <StickyNote className="absolute left-3 top-3 w-5 h-5 text-slate-400" />
                <textarea name="notes" value={form.notes} onChange={(e) => setForm((p) => ({ ...p, notes: e.target.value }))} rows={3} placeholder="Observações..." className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none" />
              </div>
            </div>
          </div>

          <div className="flex justify-end gap-3">
            <button type="button" onClick={() => navigate('/purchases')} className="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancelar</button>
            <button type="submit" disabled={loading} className="flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-60">
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
              {loading ? 'Salvando...' : 'Registar Compra'}
            </button>
          </div>
        </form>
      </div>
    </motion.div>
  );
}
