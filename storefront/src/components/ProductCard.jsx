import React from 'react';
import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';

const stockConfig = {
  disponivel: { text: 'Disponível', class: 'text-green-600 bg-green-50 border-green-200' },
  ultimas_unidades: { text: 'Últimas unidades', class: 'text-orange-500 bg-orange-50 border-orange-200' },
  esgotado: { text: 'Esgotado', class: 'text-red-500 bg-red-50 border-red-200' },
};

export default function ProductCard({ product, slug, index = 0 }) {
  const stock = stockConfig[product.stock_label] || stockConfig.disponivel;
  const isOut = product.stock_label === 'esgotado';

  return (
    <motion.div
      initial={{ opacity: 0, y: 30 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: index * 0.05 }}
    >
      <Link to={`/${slug}/products/${product.id}`}
        className="block bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg hover:border-blue-100 transition-all duration-300 group">
        <div className="aspect-square bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-6 relative overflow-hidden">
          {product.image ? (
            <img src={product.image} alt={product.name} className="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500" />
          ) : (
            <span className="text-6xl opacity-30 group-hover:scale-125 transition-transform duration-500">📦</span>
          )}
          <span className={`absolute top-3 left-3 px-2 py-0.5 rounded-full text-xs font-medium border ${stock.class}`}>
            {stock.text}
          </span>
        </div>
        <div className="p-4">
          <h3 className="font-medium text-gray-900 truncate group-hover:text-blue-600 transition-colors">{product.name}</h3>
          <p className="text-xl font-bold text-blue-600 mt-1">
            {new Intl.NumberFormat('pt-AO').format(product.sale_price)} AOA
          </p>
        </div>
      </Link>
    </motion.div>
  );
}
