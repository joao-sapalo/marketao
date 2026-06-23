import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { useParams, Link } from 'react-router-dom';
import { getOrder } from '../api/storefront';
import OrderTimeline from '../components/OrderTimeline';

export default function OrderTracking({ slug }) {
  const { orderId } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchOrder = () => {
    getOrder(slug, orderId)
      .then(data => setOrder(data.data || data))
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  };

  useEffect(() => { fetchOrder(); }, [slug, orderId]);

  useEffect(() => {
    const interval = setInterval(fetchOrder, 15000);
    return () => clearInterval(interval);
  }, [slug, orderId]);

  if (loading) {
    return <div className="flex justify-center py-32"><div className="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600" /></div>;
  }

  if (error || !order) {
    return <div className="text-center py-20 text-gray-400">Pedido não encontrado</div>;
  }

  const payMethods = ['cash', 'transfer', 'multicaixa'];
  const showPayment = order.payment_method !== 0 && order.payment_status === 0;

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-2xl mx-auto">
      <div className="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm mb-6">
        <div className="flex items-center justify-between mb-4">
          <div>
            <p className="text-sm text-gray-400">Referência</p>
            <h1 className="text-xl font-bold text-gray-900">{order.reference}</h1>
          </div>
          <span className={`px-3 py-1 rounded-full text-sm font-medium ${
            order.status === 4 ? 'bg-green-100 text-green-700' :
            order.status === 5 ? 'bg-red-100 text-red-700' :
            order.status === 0 ? 'bg-yellow-100 text-yellow-700' :
            'bg-blue-100 text-blue-700'
          }`}>{order.status_label}</span>
        </div>
        <OrderTimeline status={order.status} />
      </div>

      {showPayment && order.store?.bank_name && (
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}
          className="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 mb-6">
          <h3 className="font-semibold text-lg mb-3">💳 Dados de Pagamento</h3>
          <div className="space-y-2 text-sm">
            <p><span className="text-gray-500">Banco:</span> <strong>{order.store.bank_name}</strong></p>
            <p><span className="text-gray-500">Titular:</span> <strong>{order.store.bank_holder}</strong></p>
            <p><span className="text-gray-500">IBAN:</span> <strong className="text-xs">{order.store.bank_iban}</strong></p>
            <p><span className="text-gray-500">Referência:</span> <strong className="text-blue-600">{order.payment_reference}</strong></p>
          </div>
          <p className="text-xs text-yellow-600 mt-2">⚠ Usa esta referência na descrição da transferência</p>
        </motion.div>
      )}

      <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm mb-6">
        <div className="p-4 border-b border-gray-100 font-semibold flex items-center gap-2">
          <span>📦</span> Itens do Pedido
        </div>
        <div className="divide-y divide-gray-50">
          {order.items?.map((item, i) => (
            <motion.div key={i} initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: i * 0.1 }}
              className="p-4 flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">{item.product_name}</p>
                <p className="text-sm text-gray-400">{item.quantity} × {new Intl.NumberFormat('pt-AO').format(item.unit_price)} AOA</p>
              </div>
              <p className="font-semibold">{new Intl.NumberFormat('pt-AO').format(item.total)} AOA</p>
            </motion.div>
          ))}
        </div>
        <div className="p-4 bg-gray-50 flex items-center justify-between">
          <span className="font-semibold">Total</span>
          <span className="text-xl font-bold text-blue-600">{new Intl.NumberFormat('pt-AO').format(order.total)} AOA</span>
        </div>
      </div>

      {order.status === 4 && !order.review && (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center">
          <Link to={`/loja/${slug}/orders/${order.id}/review`}
            className="inline-flex items-center gap-2 px-6 py-3 bg-yellow-400 text-gray-900 rounded-xl hover:bg-yellow-500 font-medium transition-colors shadow-sm">
            ⭐ Avaliar Pedido
          </Link>
        </motion.div>
      )}

      {order.store?.whatsapp && (
        <div className="text-center mt-6">
          <a href={`https://wa.me/244${order.store.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 font-medium transition-colors shadow-sm">
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Fala com a loja no WhatsApp
          </a>
        </div>
      )}
    </motion.div>
  );
}
