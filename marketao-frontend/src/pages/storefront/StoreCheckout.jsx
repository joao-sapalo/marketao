import { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { ShoppingBag, Package, Loader2, CheckCircle, ArrowLeft, User, Phone, Mail, MapPin, CreditCard } from 'lucide-react';
import { useStoreCart } from '../../context/StoreCartContext';
import api from '../../services/api';

export default function StoreCheckout() {
  const { slug } = useParams();
  const navigate = useNavigate();
  const { cart, total, clearCart, count } = useStoreCart();
  const [form, setForm] = useState({ customer_name: '', customer_phone: '', customer_email: '', delivery_address: '', payment_method: 'cash', notes: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(null);

  const handleChange = (e) => setForm(p => ({ ...p, [e.target.name]: e.target.value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    if (cart.length === 0) { setError('Carrinho vazio.'); return; }
    setLoading(true);
    try {
      const res = await api.post(`/s/${slug}/checkout`, {
        items: cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
        ...form,
      });
      const data = res.data.data || res.data;
      setSuccess(data);
      clearCart();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao processar encomenda.');
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="max-w-lg mx-auto text-center py-12">
        <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }}>
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <CheckCircle className="h-10 w-10 text-green-600" />
          </div>
        </motion.div>
        <h1 className="text-2xl font-bold text-slate-900 mb-2">Encomenda Recebida!</h1>
        <p className="text-slate-500 mb-2">O seu pedido foi registado com sucesso.</p>
        <p className="text-sm text-slate-400">Nº da encomenda: <strong>#{success.sale_id}</strong></p>
        <p className="text-lg font-bold text-blue-600 mt-2">{Number(success.total).toLocaleString()} Kz</p>
        <p className="text-sm text-slate-500 mt-1">Estado: <span className="text-yellow-600 font-medium">Pendente</span></p>
        <Link to={`/loja/${slug}`} className="inline-flex items-center gap-2 mt-8 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
          <ArrowLeft className="h-4 w-4" /> Voltar à loja
        </Link>
      </div>
    );
  }

  if (count === 0) {
    navigate(`/loja/${slug}/carrinho`);
    return null;
  }

  return (
    <div className="max-w-2xl mx-auto">
      <Link to={`/loja/${slug}/carrinho`} className="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-6 transition">
        <ArrowLeft className="h-4 w-4" /> Voltar ao carrinho
      </Link>

      <h1 className="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
        <CreditCard className="h-5 w-5" /> Finalizar Encomenda
      </h1>

      {error && <div className="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-6 text-sm">{error}</div>}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="bg-white rounded-xl shadow-sm p-6 space-y-4">
          <h2 className="font-semibold text-slate-900">Dados do Cliente</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Nome</label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input type="text" name="customer_name" value={form.customer_name} onChange={handleChange} placeholder="Seu nome" className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
              <div className="relative">
                <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <input type="text" name="customer_phone" value={form.customer_phone} onChange={handleChange} placeholder="+244 900 000 000" className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <div className="relative">
              <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
              <input type="email" name="customer_email" value={form.customer_email} onChange={handleChange} placeholder="seu@email.com" className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Morada de Entrega</label>
            <div className="relative">
              <MapPin className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
              <textarea name="delivery_address" value={form.delivery_address} onChange={handleChange} rows={2} placeholder="Endereço completo..." className="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6 space-y-3">
          <h2 className="font-semibold text-slate-900">Itens ({count})</h2>
          {cart.map(item => (
            <div key={item.product_id} className="flex justify-between text-sm">
              <span className="text-slate-600">{item.name} <span className="text-slate-400">x{item.quantity}</span></span>
              <span className="font-medium text-slate-900">{Number(item.price * item.quantity).toLocaleString()} Kz</span>
            </div>
          ))}
          <div className="border-t pt-3 flex justify-between font-bold text-lg">
            <span className="text-slate-900">Total</span>
            <span className="text-blue-600">{total.toLocaleString()} Kz</span>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6">
          <h2 className="font-semibold text-slate-900 mb-3">Método de Pagamento</h2>
          <select name="payment_method" value={form.payment_method} onChange={handleChange}
            className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="cash">Pagamento na Entrega</option>
            <option value="transfer">Transferência Bancária</option>
            <option value="mobile">Pagamento Móvel</option>
          </select>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6">
          <label className="block text-sm font-medium text-slate-700 mb-1">Observações</label>
          <textarea name="notes" value={form.notes} onChange={handleChange} rows={2} placeholder="Alguma observação?" className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
        </div>

        <button type="submit" disabled={loading}
          className="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition flex items-center justify-center gap-2 disabled:opacity-60">
          {loading ? <Loader2 className="h-5 w-5 animate-spin" /> : <ShoppingBag className="h-5 w-5" />}
          {loading ? 'Processando...' : `Confirmar Encomenda — ${total.toLocaleString()} Kz`}
        </button>
      </form>
    </div>
  );
}
