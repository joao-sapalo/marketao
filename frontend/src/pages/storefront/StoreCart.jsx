import { Link, useParams } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { ShoppingCart, Package, Trash2, Minus, Plus, ArrowLeft, ShoppingBag, AlertTriangle } from 'lucide-react';
import { useStoreCart } from '../../context/StoreCartContext';

export default function StoreCart() {
  const { slug } = useParams();
  const { cart, updateQuantity, removeItem, clearCart, total, count } = useStoreCart();

  return (
    <div>
      <Link to={`/loja/${slug}`} className="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-6 transition">
        <ArrowLeft className="h-4 w-4" /> Continuar a comprar
      </Link>

      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold text-slate-900 flex items-center gap-2">
          <ShoppingCart className="h-5 w-5" /> Carrinho {count > 0 && <span className="text-sm font-normal text-slate-500">({count} itens)</span>}
        </h1>
        {cart.length > 0 && (
          <button onClick={clearCart} className="text-sm text-red-600 hover:text-red-700 font-medium">Limpar carrinho</button>
        )}
      </div>

      {cart.length === 0 ? (
        <div className="text-center py-16 text-slate-400">
          <ShoppingCart className="h-16 w-16 mx-auto mb-4" />
          <p className="text-lg font-medium text-slate-500 mb-1">Carrinho vazio</p>
          <p className="text-sm mb-6">Adicione produtos para começar</p>
          <Link to={`/loja/${slug}/produtos`} className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
            <ShoppingBag className="h-4 w-4" /> Ver Produtos
          </Link>
        </div>
      ) : (
        <div className="space-y-3">
          <AnimatePresence>
            {cart.map((item) => (
              <motion.div key={item.product_id} initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }}
                className="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
                <div className="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                  {item.image ? <img src={item.image} alt="" className="w-full h-full object-cover" /> : <div className="w-full h-full flex items-center justify-center text-slate-300"><Package className="h-6 w-6" /></div>}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="font-medium text-slate-900 truncate">{item.name}</p>
                  <p className="text-blue-600 font-bold">{Number(item.price).toLocaleString()} Kz</p>
                </div>
                <div className="flex items-center border border-slate-200 rounded-lg">
                  <button onClick={() => updateQuantity(item.product_id, item.quantity - 1)} className="p-1.5 text-slate-500 hover:text-slate-700"><Minus className="h-3.5 w-3.5" /></button>
                  <span className="px-3 py-1.5 text-sm font-medium min-w-[32px] text-center">{item.quantity}</span>
                  <button onClick={() => updateQuantity(item.product_id, item.quantity + 1)} className="p-1.5 text-slate-500 hover:text-slate-700"><Plus className="h-3.5 w-3.5" /></button>
                </div>
                <p className="font-bold text-slate-900 w-24 text-right">{Number(item.price * item.quantity).toLocaleString()} Kz</p>
                <button onClick={() => removeItem(item.product_id)} className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition"><Trash2 className="h-4 w-4" /></button>
              </motion.div>
            ))}
          </AnimatePresence>

          <div className="bg-white rounded-xl shadow-sm p-6 mt-4">
            <div className="flex justify-between items-center text-lg font-bold text-slate-900 mb-4">
              <span>Total</span>
              <span className="text-blue-600">{total.toLocaleString()} Kz</span>
            </div>
            <Link to={`/loja/${slug}/checkout`}
              className="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition flex items-center justify-center gap-2">
              <ShoppingBag className="h-5 w-5" /> Finalizar Encomenda
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
