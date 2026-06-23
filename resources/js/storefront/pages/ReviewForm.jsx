import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { useParams, useNavigate } from 'react-router-dom';
import { getOrder, submitReview } from '../api/storefront';
import StarRating from '../components/StarRating';

export default function ReviewForm({ slug }) {
  const { orderId } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [rating, setRating] = useState(0);
  const [comment, setComment] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [done, setDone] = useState(false);

  useEffect(() => {
    getOrder(slug, orderId).then(data => {
      setOrder(data.data || data);
    }).catch(() => {}).finally(() => setLoading(false));
  }, [slug, orderId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (rating === 0) { alert('Selecciona uma avaliação de 1 a 5 estrelas.'); return; }
    setSubmitting(true);
    try {
      await submitReview(slug, order.id, rating, comment);
      setDone(true);
      setTimeout(() => navigate(`/loja/${slug}/orders/${order.id}`), 2000);
    } catch (err) {
      alert(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center py-20"><div className="animate-spin rounded-full h-10 w-10 border-4 border-blue-200 border-t-blue-600" /></div>;
  }

  if (!order) {
    return <div className="text-center py-20 text-gray-400">Pedido não encontrado</div>;
  }

  if (order.review) {
    return (
      <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center py-20">
        <span className="text-6xl block mb-4">✅</span>
        <p className="text-lg text-gray-600 mb-2">Já avaliaste este pedido!</p>
        <p className="text-sm text-gray-400">Obrigado pelo teu feedback.</p>
      </motion.div>
    );
  }

  if (order.status !== 4) {
    return (
      <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center py-20">
        <span className="text-6xl block mb-4">⏳</span>
        <p className="text-lg text-gray-600">Só podes avaliar após a entrega do pedido.</p>
      </motion.div>
    );
  }

  if (done) {
    return (
      <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="text-center py-20">
        <span className="text-6xl block mb-4">⭐</span>
        <p className="text-xl font-bold text-gray-900 mb-2">Avaliação enviada!</p>
        <p className="text-gray-400">Obrigado por ajudaes a melhorar.</p>
      </motion.div>
    );
  }

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-md mx-auto">
      <div className="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
        <div className="text-center mb-6">
          <span className="text-5xl block mb-3">⭐</span>
          <h1 className="text-2xl font-bold text-gray-900">Avaliar Pedido</h1>
          <p className="text-sm text-gray-400 mt-1">{order.reference}</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="text-center">
            <p className="text-sm font-medium text-gray-700 mb-3">A tua avaliação</p>
            <div className="flex justify-center">
              <StarRating value={rating} onChange={setRating} />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Comentário (opcional)</label>
            <textarea value={comment} onChange={e => setComment(e.target.value)} rows={3} maxLength={500}
              placeholder="Partilha a tua experiência..."
              className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none resize-none" />
          </div>

          <motion.button type="submit" disabled={submitting}
            whileHover={{ scale: 1.01 }} whileTap={{ scale: 0.98 }}
            className="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm disabled:opacity-70">
            {submitting ? 'A enviar...' : 'Enviar Avaliação'}
          </motion.button>
        </form>
      </div>
    </motion.div>
  );
}
