import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { getStore, getStoreProducts, getCategories } from '../api/storefront';
import TrustScore from '../components/TrustScore';
import NaturalLanguageInput from '../components/NaturalLanguageInput';
import ProductCard from '../components/ProductCard';

const stagger = {
  animate: { transition: { staggerChildren: 0.05 } },
};

export default function StoreHome({ slug }) {
  const [store, setStore] = useState(null);
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [activeCategory, setActiveCategory] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      getStore(slug),
      getStoreProducts(slug, { per_page: 50 }),
      getCategories(slug),
    ]).then(([storeData, prodData, catData]) => {
      setStore(storeData.store || storeData);
      setProducts(prodData.data || []);
      setCategories(catData.data || []);
    }).catch(() => {}).finally(() => setLoading(false));
  }, [slug]);

  const filtered = activeCategory
    ? products.filter(p => p.category?.id === activeCategory)
    : products;

  if (loading) {
    return (
      <div className="flex items-center justify-center py-32">
        <div className="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600" />
      </div>
    );
  }

  return (
    <motion.div initial="initial" animate="animate" variants={stagger}>
      {store && <TrustScore store={store} />}

      {store?.cover_image && (
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
          className="rounded-2xl overflow-hidden mb-6 shadow-sm">
          <img src={store.cover_image} alt="" className="w-full h-48 md:h-72 object-cover" />
        </motion.div>
      )}

      <NaturalLanguageInput slug={slug} />

      {categories.length > 0 && (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="flex flex-wrap gap-2 mb-6">
          <button onClick={() => setActiveCategory(null)}
            className={`px-4 py-2 rounded-full text-sm font-medium transition-all ${!activeCategory ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}>
            Todos
          </button>
          {categories.map(cat => (
            <button key={cat.id} onClick={() => setActiveCategory(cat.id)}
              className={`px-4 py-2 rounded-full text-sm font-medium transition-all ${activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}>
              {cat.name}
            </button>
          ))}
        </motion.div>
      )}

      {filtered.length > 0 ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {filtered.map((product, i) => (
            <ProductCard key={product.id} product={product} slug={slug} index={i} />
          ))}
        </div>
      ) : (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="text-center py-20">
          <span className="text-6xl block mb-4">🛍️</span>
          <p className="text-gray-400 text-lg">Nenhum produto disponível</p>
        </motion.div>
      )}
    </motion.div>
  );
}
