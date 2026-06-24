import { Link, useParams, useNavigate } from 'react-router-dom';
import { ShoppingCart, Store, Menu, X, Package } from 'lucide-react';
import { useState, useEffect } from 'react';
import { useStoreCart } from '../../context/StoreCartContext';
import api from '../../services/api';

export default function StoreLayout({ children }) {
  const { slug } = useParams();
  const navigate = useNavigate();
  const { count } = useStoreCart();
  const [store, setStore] = useState(null);
  const [menuOpen, setMenuOpen] = useState(false);
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    api.get(`/s/${slug}`).then(res => setStore(res.data.data)).catch(() => {});
    api.get(`/s/${slug}/categories`).then(res => setCategories(res.data.data || [])).catch(() => {});
  }, [slug]);

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="bg-white shadow-sm sticky top-0 z-50" style={{ borderTop: store ? `4px solid ${store.primary_color}` : undefined }}>
        <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
          <Link to={`/loja/${slug}`} className="flex items-center gap-2">
            {store?.logo ? <img src={store.logo} alt="" className="h-8 w-8 rounded" /> : <Store className="h-6 w-6" style={{ color: store?.primary_color }} />}
            <span className="text-lg font-bold text-slate-900">{store?.name || 'Loja'}</span>
          </Link>

          <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
            <Link to={`/loja/${slug}`} className="hover:text-blue-600 transition">Início</Link>
            <Link to={`/loja/${slug}/produtos`} className="hover:text-blue-600 transition">Produtos</Link>
            {categories.slice(0, 4).map(cat => (
              <Link key={cat.id} to={`/loja/${slug}/produtos?category_id=${cat.id}`} className="hover:text-blue-600 transition">{cat.name}</Link>
            ))}
          </nav>

          <div className="flex items-center gap-3">
            <button onClick={() => navigate(`/loja/${slug}/carrinho`)} className="relative p-2 text-slate-600 hover:text-blue-600 transition">
              <ShoppingCart className="h-5 w-5" />
              {count > 0 && <span className="absolute -top-0.5 -right-0.5 bg-blue-600 text-white text-xs rounded-full h-4 min-w-[16px] flex items-center justify-center px-1">{count}</span>}
            </button>
            <button onClick={() => setMenuOpen(!menuOpen)} className="md:hidden p-2 text-slate-600">{menuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}</button>
          </div>
        </div>

        {menuOpen && (
          <div className="md:hidden border-t border-slate-100 px-4 py-3 space-y-2 text-sm font-medium text-slate-600">
            <Link to={`/loja/${slug}`} className="block py-1" onClick={() => setMenuOpen(false)}>Início</Link>
            <Link to={`/loja/${slug}/produtos`} className="block py-1" onClick={() => setMenuOpen(false)}>Produtos</Link>
            {categories.map(cat => (
              <Link key={cat.id} to={`/loja/${slug}/produtos?category_id=${cat.id}`} className="block py-1 pl-4" onClick={() => setMenuOpen(false)}>{cat.name}</Link>
            ))}
          </div>
        )}
      </header>

      <main className="max-w-7xl mx-auto px-4 py-6">
        {children}
      </main>

      <footer className="bg-white border-t border-slate-200 mt-12 py-8 text-center text-sm text-slate-500">
        <p className="flex items-center justify-center gap-1"><Package className="h-4 w-4" /> {store?.name || 'MarketAO ERP'} &copy; {new Date().getFullYear()}</p>
      </footer>
    </div>
  );
}
