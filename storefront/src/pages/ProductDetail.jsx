import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { useParams, Link } from 'react-router-dom';
import { getStoreProduct, updateCart } from '../api/storefront';

const stockConfig = {
  disponivel: { text: 'Disponível', class: 'text-green-600 bg-green-50 border-green-200' },
  ultimas_unidades: { text: 'Últimas unidades', class: 'text-orange-500 bg-orange-50 border-orange-200' },
  esgotado: { text: 'Esgotado', class: 'text-red-500 bg-red-50 border-red-200' },
};

export default function ProductDetail({ slug }) {
  const { productId } = useParams();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState(1);
  const [adding, setAdding] = useState(false);
  const [added, setAdded] = useState(false);

  useEffect(() => {
    setLoading(true);
    getStoreProduct(slug, productId)
      .then(data => setProduct(data.data || data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [slug, productId]);

  const handleAddToCart = async () => {
    setAdding(true);
    try {
      await updateCart(slug, productId, qty);
      setAdded(true);
      setTimeout(() => setAdded(false), 2000);
    } catch (err) {
      alert(err.message);
    } finally {
      setAdding(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center py-32"><div className="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600" /></div>;
  }

  if (!product) {
    return <div className="text-center py-20 text-gray-400">Produto não encontrado</div>;
  }

  const stock = stockConfig[product.stock_label] || stockConfig.disponivel;
  const isOut = product.stock_label === 'esgotado';

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <Link to={`/${slug}/products`} className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600 mb-6 transition-colors">
        ← Voltar para produtos
      </Link>

      <div className="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div className="md:flex">
          <div className="md:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 p-10 flex items-center justify-center min-h-80">
            {product.image ? (
              <img src={product.image} alt={product.name} className="max-h-72 object-contain" />
            ) : (
              <span className="text-8xl opacity-30">📦</span>
            )}
          </div>
          <div className="md:w-1/2 p-6 md:p-8 flex flex-col justify-center">
            <span className={`inline-block self-start px-3 py-1 rounded-full text-sm font-medium border mb-4 ${stock.class}`}>
              {stock.text}
            </span>
            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{product.name}</h1>
            {product.code && <p className="text-sm text-gray-400 mb-4">Código: {product.code}</p>}
            <p className="text-3xl font-bold text-blue-600 mb-6">
              {new Intl.NumberFormat('pt-AO').format(product.sale_price)} AOA
            </p>

            {!isOut && (
              <>
                <div className="flex items-center gap-4 mb-6">
                  <motion.button whileTap={{ scale: 0.9 }}
                    onClick={() => setQty(Math.max(1, qty - 1))}
                    className="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-xl font-medium transition-colors">−</motion.button>
                  <motion.div key={qty} initial={{ scale: 1.2 }} animate={{ scale: 1 }} className="w-16 text-center text-2xl font-bold">{qty}</motion.div>
                  <motion.button whileTap={{ scale: 0.9 }}
                    onClick={() => setQty(qty + 1)}
                    className="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-xl font-medium transition-colors">+</motion.button>
                </div>

                <motion.button whileHover={{ scale: 1.01 }} whileTap={{ scale: 0.98 }}
                  onClick={handleAddToCart} disabled={adding}
                  className={`w-full py-3.5 rounded-xl font-medium text-lg transition-all shadow-sm ${
                    added ? 'bg-green-500 text-white' : 'bg-blue-600 text-white hover:bg-blue-700'
                  } disabled:opacity-70`}>
                  {adding ? 'A adicionar...' : added ? '✓ Adicionado!' : 'Adicionar ao Carrinho'}
                </motion.button>
              </>
            )}

            {product.description && (
              <div className="mt-8 pt-6 border-t border-gray-100">
                <h3 className="font-semibold text-gray-900 mb-2">Descrição</h3>
                <p className="text-gray-600 text-sm leading-relaxed">{product.description}</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
}
