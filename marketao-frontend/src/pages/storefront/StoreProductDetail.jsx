import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Package, ShoppingBag, ArrowLeft, Minus, Plus, Check } from 'lucide-react';
import { useStoreCart } from '../../context/StoreCartContext';
import api from '../../services/api';

export default function StoreProductDetail() {
  const { slug, id } = useParams();
  const { addItem, cart } = useStoreCart();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState(1);
  const [added, setAdded] = useState(false);

  useEffect(() => {
    api.get(`/s/${slug}/products/${id}`)
      .then(res => setProduct(res.data.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [slug, id]);

  const handleAdd = () => {
    if (product) {
      addItem(product, qty);
      setAdded(true);
      setTimeout(() => setAdded(false), 2000);
    }
  };

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-600" /></div>;
  if (!product) return <div className="text-center py-20 text-slate-400"><Package className="h-12 w-12 mx-auto mb-3" /><p>Produto não encontrado</p></div>;

  return (
    <div>
      <Link to={`/loja/${slug}/produtos`} className="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-6 transition">
        <ArrowLeft className="h-4 w-4" /> Voltar aos produtos
      </Link>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="aspect-square bg-slate-100 rounded-2xl overflow-hidden">
          {product.image ? (
            <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-slate-300"><Package className="h-24 w-24" /></div>
          )}
        </motion.div>

        <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
          <h1 className="text-2xl md:text-3xl font-bold text-slate-900 mb-2">{product.name}</h1>
          {product.category && <span className="text-sm text-blue-600 font-medium">{product.category.name}</span>}

          <p className="text-3xl font-bold text-blue-600 my-4">{Number(product.sale_price).toLocaleString()} Kz</p>

          {product.description && <p className="text-slate-600 mb-6">{product.description}</p>}

          {product.quantity > 0 ? (
            <span className="inline-block text-sm text-green-600 font-medium mb-4 bg-green-50 px-3 py-1 rounded-full">Em stock ({product.quantity} unid.)</span>
          ) : (
            <span className="inline-block text-sm text-red-600 font-medium mb-4 bg-red-50 px-3 py-1 rounded-full">Fora de stock</span>
          )}

          <div className="flex items-center gap-4 mb-6">
            <div className="flex items-center border border-slate-200 rounded-lg">
              <button onClick={() => setQty(q => Math.max(1, q - 1))} className="p-2 text-slate-500 hover:text-slate-700"><Minus className="h-4 w-4" /></button>
              <span className="px-4 py-2 font-medium text-slate-900 min-w-[40px] text-center">{qty}</span>
              <button onClick={() => setQty(q => q + 1)} className="p-2 text-slate-500 hover:text-slate-700"><Plus className="h-4 w-4" /></button>
            </div>
          </div>

          <button onClick={handleAdd} disabled={product.quantity < 1}
            className={`w-full py-3 rounded-xl font-medium text-white transition flex items-center justify-center gap-2 ${added ? 'bg-green-600' : 'bg-blue-600 hover:bg-blue-700'} disabled:opacity-50`}>
            {added ? <><Check className="h-5 w-5" /> Adicionado</> : <><ShoppingBag className="h-5 w-5" /> Adicionar ao carrinho</>}
          </button>
        </motion.div>
      </div>
    </div>
  );
}
