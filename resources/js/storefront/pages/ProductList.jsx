import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { useSearchParams } from 'react-router-dom';
import { getStoreProducts } from '../api/storefront';
import ProductCard from '../components/ProductCard';

export default function ProductList({ slug }) {
  const [searchParams, setSearchParams] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const search = searchParams.get('search') || '';

  useEffect(() => {
    setLoading(true);
    getStoreProducts(slug, { search, per_page: 50 })
      .then(data => setProducts(data.data || []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [slug, search]);

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Produtos</h1>
        <form onSubmit={e => { e.preventDefault(); const fd = new FormData(e.target); setSearchParams({ search: fd.get('search') }); }}
          className="flex gap-2">
          <input type="text" name="search" defaultValue={search} placeholder="Pesquisar..."
            className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm w-48" />
          <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm transition-colors">🔍</button>
        </form>
      </div>

      {loading ? (
        <div className="flex justify-center py-20">
          <div className="animate-spin rounded-full h-10 w-10 border-4 border-blue-200 border-t-blue-600" />
        </div>
      ) : products.length > 0 ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {products.map((p, i) => <ProductCard key={p.id} product={p} slug={slug} index={i} />)}
        </div>
      ) : (
        <div className="text-center py-20">
          <span className="text-5xl block mb-4">🔍</span>
          <p className="text-gray-400">Nenhum produto encontrado</p>
        </div>
      )}
    </motion.div>
  );
}
