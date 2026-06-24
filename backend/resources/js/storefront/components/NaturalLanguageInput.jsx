import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { interpretQuery } from '../api/storefront';

export default function NaturalLanguageInput({ slug, onItemsAdded }) {
  const [query, setQuery] = useState('');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!query.trim()) return;
    setLoading(true);
    setResult(null);
    try {
      const data = await interpretQuery(slug, query);
      setResult(data.data);
      if (data.data?.items?.length > 0 && onItemsAdded) {
        onItemsAdded(data.data.items);
      }
    } catch (err) {
      setResult({ error: err.message });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-2xl border border-gray-100 p-5 mb-6 shadow-sm">
      <form onSubmit={handleSubmit} className="flex gap-3">
        <div className="relative flex-1">
          <span className="absolute left-4 top-1/2 -translate-y-1/2 text-xl">💬</span>
          <input type="text" value={query} onChange={e => setQuery(e.target.value)}
            placeholder="O que precisas hoje? Ex: 2 sacos de arroz e 1 garrafão de água"
            className="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-gray-700 placeholder:text-gray-400 transition-all" />
        </div>
        <motion.button type="submit" disabled={loading || !query.trim()}
          whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}
          className="px-8 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
          {loading ? (
            <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          ) : 'Enviar'}
        </motion.button>
      </form>

      <AnimatePresence>
        {result && (
          <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }}
            className="mt-4 overflow-hidden">
            {result.error ? (
              <div className="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600">{result.error}</div>
            ) : result.items?.length > 0 ? (
              <div className="bg-green-50 border border-green-200 rounded-xl p-4">
                <p className="text-sm font-medium text-green-700 mb-2">✅ Itens adicionados:</p>
                <ul className="space-y-1">
                  {result.items.map((item, i) => (
                    <li key={i} className="text-sm text-green-600 flex items-center justify-between">
                      <span>{item.product_name} × {item.quantity}</span>
                      <span className="font-medium">{(item.unit_price * item.quantity).toLocaleString()} AOA</span>
                    </li>
                  ))}
                </ul>
              </div>
            ) : (
              <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
                ⚠ Não encontrei correspondência para: "{result.unmatched?.join(', ') || query}"
              </div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
