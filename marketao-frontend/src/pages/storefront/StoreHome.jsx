import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Package, Search, ArrowRight, ShoppingBag, Phone, Mail, MapPin } from 'lucide-react';
import { useStoreCart } from '../../context/StoreCartContext';
import api from '../../services/api';

export default function StoreHome() {
  const { slug } = useParams();
  const { addItem } = useStoreCart();
  const [store, setStore] = useState(null);
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api.get(`/s/${slug}`),
      api.get(`/s/${slug}/products?perPage=8`),
      api.get(`/s/${slug}/categories`),
    ]).then(([sRes, pRes, cRes]) => {
      setStore(sRes.data.data);
      setProducts(pRes.data.data?.data || pRes.data.data || []);
      setCategories(cRes.data.data || []);
    }).catch(() => {}).finally(() => setLoading(false));
  }, [slug]);

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-600" /></div>;

  return (
    <div className="space-y-12">
      {store?.cover_image && (
        <div className="relative -mx-4 -mt-6 h-64 md:h-96 overflow-hidden">
          <img src={store.cover_image} alt="" className="w-full h-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
          <div className="absolute bottom-8 left-8 text-white">
            <h1 className="text-3xl md:text-5xl font-bold mb-2">{store.name}</h1>
            <p className="text-lg text-white/80 max-w-xl">{store.description}</p>
          </div>
        </div>
      )}

      {!store?.cover_image && (
        <div className="text-center py-12">
          <Package className="h-16 w-16 mx-auto mb-4 text-blue-600" />
          <h1 className="text-3xl md:text-4xl font-bold text-slate-900 mb-2">{store?.name}</h1>
          <p className="text-slate-500 max-w-lg mx-auto">{store?.description}</p>
        </div>
      )}

      {store && (store.phone || store.email || store.address) && (
        <div className="flex flex-wrap justify-center gap-6 text-sm text-slate-500">
          {store.phone && <span className="flex items-center gap-1.5"><Phone className="h-4 w-4" /> {store.phone}</span>}
          {store.email && <span className="flex items-center gap-1.5"><Mail className="h-4 w-4" /> {store.email}</span>}
          {store.address && <span className="flex items-center gap-1.5"><MapPin className="h-4 w-4" /> {store.address}{store.city ? `, ${store.city}` : ''}</span>}
        </div>
      )}

      {categories.length > 0 && (
        <section>
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-bold text-slate-900">Categorias</h2>
          </div>
          <div className="flex flex-wrap gap-2">
            {categories.map(cat => (
              <Link key={cat.id} to={`/loja/${slug}/produtos?category_id=${cat.id}`}
                className="px-4 py-2 bg-white rounded-full shadow-sm text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                {cat.name}
              </Link>
            ))}
          </div>
        </section>
      )}

      <section>
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-bold text-slate-900">Produtos</h2>
          <Link to={`/loja/${slug}/produtos`} className="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
            Ver todos <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {products.map((product, i) => (
            <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.05 }}
              className="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
              <Link to={`/loja/${slug}/produto/${product.id}`}>
                <div className="aspect-square bg-slate-100 overflow-hidden">
                  {product.image ? (
                    <img src={product.image} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-slate-300"><Package className="h-12 w-12" /></div>
                  )}
                </div>
              </Link>
              <div className="p-3">
                <Link to={`/loja/${slug}/produto/${product.id}`} className="text-sm font-medium text-slate-900 line-clamp-2 hover:text-blue-600 transition">{product.name}</Link>
                <p className="text-lg font-bold text-blue-600 mt-1">{Number(product.sale_price).toLocaleString()} Kz</p>
                <button onClick={() => addItem(product)} className="mt-2 w-full py-1.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center justify-center gap-1.5">
                  <ShoppingBag className="h-4 w-4" /> Adicionar
                </button>
              </div>
            </motion.div>
          ))}
        </div>
      </section>
    </div>
  );
}
