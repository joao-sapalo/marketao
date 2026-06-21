import { useEffect, useState } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Package, ShoppingBag, Search, SlidersHorizontal, X } from 'lucide-react';
import { useStoreCart } from '../../context/StoreCartContext';
import api from '../../services/api';

export default function StoreProducts() {
  const { slug } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const { addItem } = useStoreCart();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState(searchParams.get('search') || '');
  const [categoryId, setCategoryId] = useState(searchParams.get('category_id') || '');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const fetchProducts = () => {
    setLoading(true);
    const params = { page, perPage: 20 };
    if (search) params.search = search;
    if (categoryId) params.category_id = categoryId;
    api.get(`/s/${slug}/products`, { params })
      .then(res => {
        const d = res.data.data;
        setProducts(Array.isArray(d) ? d : d.data || []);
        setLastPage(d.last_page || 1);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    api.get(`/s/${slug}/categories`).then(res => setCategories(res.data.data || [])).catch(() => {});
  }, [slug]);

  useEffect(() => { fetchProducts(); }, [slug, page, search, categoryId]);

  const applyFilters = () => {
    setPage(1);
    const params = {};
    if (search) params.search = search;
    if (categoryId) params.category_id = categoryId;
    setSearchParams(params);
  };

  return (
    <div>
      <div className="flex flex-col sm:flex-row gap-4 mb-6">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input type="text" value={search} onChange={e => setSearch(e.target.value)} onKeyDown={e => e.key === 'Enter' && applyFilters()}
            placeholder="Buscar produtos..." className="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <select value={categoryId} onChange={e => { setCategoryId(e.target.value); setPage(1); }}
          className="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todas as categorias</option>
          {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
        </select>
      </div>

      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-bold text-slate-900">Produtos</h1>
        {(search || categoryId) && (
          <button onClick={() => { setSearch(''); setCategoryId(''); setPage(1); setSearchParams({}); }}
            className="text-sm text-red-600 hover:text-red-700 flex items-center gap-1"><X className="h-4 w-4" /> Limpar filtros</button>
        )}
      </div>

      {loading ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {Array.from({ length: 8 }).map((_, i) => <div key={i} className="bg-white rounded-xl h-72 animate-pulse" />)}
        </div>
      ) : products.length === 0 ? (
        <div className="text-center py-16 text-slate-400"><Package className="h-12 w-12 mx-auto mb-3" /><p>Nenhum produto encontrado</p></div>
      ) : (
        <>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {products.map((product, i) => (
              <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.03 }}
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
          {lastPage > 1 && (
            <div className="flex justify-center gap-2 mt-8">
              <button disabled={page === 1} onClick={() => setPage(p => p - 1)} className="px-4 py-2 text-sm bg-white border rounded-lg disabled:opacity-40 hover:bg-slate-50">Anterior</button>
              <span className="px-4 py-2 text-sm text-slate-500">Página {page} de {lastPage}</span>
              <button disabled={page === lastPage} onClick={() => setPage(p => p + 1)} className="px-4 py-2 text-sm bg-white border rounded-lg disabled:opacity-40 hover:bg-slate-50">Seguinte</button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
