import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { useNavigate, Link } from 'react-router-dom';
import { getCart, updateCart, createOrder } from '../api/storefront';

export default function CartPage({ slug }) {
  const [cart, setCart] = useState(null);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({ guest_name: '', guest_phone: '', guest_whatsapp: '', guest_email: '', payment_method: '0', notes: '' });
  const [submitting, setSubmitting] = useState(false);
  const [storeInfo, setStoreInfo] = useState(null);
  const [step, setStep] = useState(1);
  const navigate = useNavigate();

  useEffect(() => {
    Promise.all([
      getCart(slug),
      fetch(`/api/s/${slug}`).then(r => r.json()),
    ]).then(([cartData, storeData]) => {
      setCart(cartData.data);
      setStoreInfo(storeData.store || storeData);
    }).catch(() => {}).finally(() => setLoading(false));
  }, [slug]);

  const updateQty = async (productId, qty) => {
    await updateCart(slug, productId, qty);
    const cartData = await getCart(slug);
    setCart(cartData.data);
  };

  const handleCheckout = async () => {
    if (!form.guest_name || !form.guest_phone) {
      alert('Preenche nome e telefone.');
      return;
    }
    setSubmitting(true);
    try {
      const data = await createOrder(slug, {
        items: cart.items.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
        ...form,
        payment_method: parseInt(form.payment_method),
      });
      navigate(`/${slug}/orders/${data.data.id}`);
    } catch (err) {
      alert(err.message || 'Erro ao criar pedido.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center py-20"><div className="animate-spin rounded-full h-10 w-10 border-4 border-blue-200 border-t-blue-600" /></div>;
  }

  if (!cart?.items?.length) {
    return (
      <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center py-20">
        <span className="text-6xl block mb-4">🛒</span>
        <p className="text-gray-400 text-lg mb-6">Carrinho vazio</p>
        <Link to={`/${slug}/products`}
          className="inline-block px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
          Ver Produtos
        </Link>
      </motion.div>
    );
  }

  const paymentMethods = [
    { value: '0', label: 'Dinheiro na entrega', icon: '💵' },
    { value: '1', label: 'Transferência Bancária', icon: '🏦' },
    { value: '2', label: 'Multicaixa Express', icon: '💳' },
  ];

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-4xl mx-auto">
      <h1 className="text-2xl font-bold mb-6">Finalizar Pedido</h1>

      <div className="flex items-center gap-2 mb-8">
        {['Os teus dados', 'Pagamento', 'Confirmar'].map((label, i) => (
          <React.Fragment key={i}>
            <button onClick={() => setStep(i + 1)} className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all ${
              step >= i + 1 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-400'
            }`}>
              <span className="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">{i + 1}</span>
              {label}
            </button>
            {i < 2 && <div className="flex-1 h-0.5 bg-gray-100" />}
          </React.Fragment>
        ))}
      </div>

      <div className="grid md:grid-cols-3 gap-6">
        <div className="md:col-span-2 space-y-4">
          <AnimatePresence mode="wait">
            {step >= 1 && (
              <motion.div key="step1" initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 className="font-semibold text-lg mb-4">Os teus dados</h2>
                <div className="space-y-3">
                  <input type="text" placeholder="Nome *" value={form.guest_name} onChange={e => setForm({...form, guest_name: e.target.value})}
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
                  <input type="tel" placeholder="Telefone * (9XXXXXXX)" value={form.guest_phone} onChange={e => setForm({...form, guest_phone: e.target.value})}
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
                  <input type="tel" placeholder="WhatsApp (para notificações)" value={form.guest_whatsapp} onChange={e => setForm({...form, guest_whatsapp: e.target.value})}
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
                  <input type="email" placeholder="Email (opcional)" value={form.guest_email} onChange={e => setForm({...form, guest_email: e.target.value})}
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" />
                  <textarea placeholder="Notas (opcional)" value={form.notes} onChange={e => setForm({...form, notes: e.target.value})}
                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none resize-none h-20" />
                </div>
              </motion.div>
            )}

            {step >= 2 && (
              <motion.div key="step2" initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 className="font-semibold text-lg mb-4">Como queres pagar?</h2>
                <div className="space-y-2">
                  {paymentMethods.map(pm => (
                    <label key={pm.value} className={`flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all ${
                      form.payment_method === pm.value ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-200'
                    }`}>
                      <input type="radio" name="payment" value={pm.value} checked={form.payment_method === pm.value}
                        onChange={e => setForm({...form, payment_method: e.target.value})} className="sr-only" />
                      <span className="text-2xl">{pm.icon}</span>
                      <span className="font-medium">{pm.label}</span>
                    </label>
                  ))}
                </div>

                {form.payment_method === '1' && storeInfo?.bank_name && (
                  <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }}
                    className="mt-4 bg-gray-50 rounded-xl p-4 text-sm space-y-1">
                    <p><span className="text-gray-500">Banco:</span> <strong>{storeInfo.bank_name}</strong></p>
                    <p><span className="text-gray-500">Titular:</span> <strong>{storeInfo.bank_holder}</strong></p>
                    <p><span className="text-gray-500">IBAN:</span> <strong className="text-xs">{storeInfo.bank_iban}</strong></p>
                    <p className="text-yellow-600 text-xs mt-2">⚠ Usa a referência que será gerada na descrição da transferência</p>
                  </motion.div>
                )}
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        <div className="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm h-fit sticky top-24">
          <h3 className="font-semibold mb-4">Resumo</h3>
          <div className="space-y-3 mb-4">
            {cart.items.map((item, i) => (
              <motion.div key={item.product_id} initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: i * 0.05 }}
                className="flex items-center justify-between gap-2">
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium truncate">{item.name}</p>
                  <div className="flex items-center gap-2 mt-1">
                    <button onClick={() => updateQty(item.product_id, Math.max(1, item.quantity - 1))}
                      className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs hover:bg-gray-200">−</button>
                    <span className="text-sm font-medium w-4 text-center">{item.quantity}</span>
                    <button onClick={() => updateQty(item.product_id, item.quantity + 1)}
                      className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs hover:bg-gray-200">+</button>
                  </div>
                </div>
                <p className="text-sm font-semibold text-gray-900">{item.subtotal.toLocaleString()} AOA</p>
              </motion.div>
            ))}
          </div>
          <div className="border-t border-gray-100 pt-4">
            <div className="flex items-center justify-between mb-4">
              <span className="font-semibold">Total</span>
              <span className="text-xl font-bold text-blue-600">{cart.total.toLocaleString()} AOA</span>
            </div>
            <motion.button whileHover={{ scale: 1.01 }} whileTap={{ scale: 0.98 }}
              onClick={step < 2 ? () => setStep(step + 1) : handleCheckout} disabled={submitting}
              className="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm disabled:opacity-70">
              {submitting ? 'A processar...' : step < 2 ? 'Continuar' : 'Fazer Pedido'}
            </motion.button>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
