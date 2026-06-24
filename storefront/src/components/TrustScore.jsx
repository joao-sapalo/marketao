import React from 'react';
import { motion } from 'framer-motion';

function Metric({ icon, label, value, color }) {
  return (
    <motion.div initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }} className="flex items-center gap-2">
      <span className={`text-lg ${color}`}>{icon}</span>
      <span className="text-sm text-gray-600"><strong className="text-gray-900">{value}</strong> {label}</span>
    </motion.div>
  );
}

export default function TrustScore({ store }) {
  if (!store?.trust_score || store.trust_score <= 0) return null;

  const stars = Math.round(store.trust_score / 20);

  return (
    <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}
      className="bg-white rounded-2xl border border-gray-100 p-5 mb-6 shadow-sm">
      <div className="flex flex-wrap items-center gap-x-6 gap-y-3">
        <div className="flex items-center gap-2">
          <div className="flex">
            {[1,2,3,4,5].map(i => (
              <span key={i} className={`text-lg ${i <= stars ? 'text-yellow-400' : 'text-gray-200'}`}>★</span>
            ))}
          </div>
          <span className="text-lg font-bold text-gray-900">{store.trust_score.toFixed(1)}</span>
        </div>
        <Metric icon="✓" color="text-green-500" value={store.confirmed_orders} label="pedidos confirmados" />
        {store.avg_delivery_days > 0 && (
          <Metric icon="⚡" color="text-blue-500" value={`${store.avg_delivery_days.toFixed(1)}d`} label="entrega média" />
        )}
        <Metric icon="👥" color="text-purple-500" value={store.total_orders} label="clientes" />
      </div>
    </motion.div>
  );
}
