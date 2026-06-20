import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import {
  ShoppingCart, ShoppingBag, TrendingUp, AlertTriangle,
  ArrowDownCircle, ArrowUpCircle, DollarSign, RefreshCw,
} from 'lucide-react';
import { LineChart, Line, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';

const statCards = [
  { key: 'total_sales_today', label: 'Vendas Hoje', icon: ShoppingCart, gradient: 'from-blue-500 to-blue-600', value: '0 Kz' },
  { key: 'total_purchases', label: 'Total Compras', icon: ShoppingBag, gradient: 'from-green-500 to-green-600', value: '0 Kz' },
  { key: 'monthly_profit', label: 'Lucro do Mês', icon: TrendingUp, gradient: 'from-emerald-500 to-emerald-600', value: '0 Kz' },
  { key: 'low_stock_count', label: 'Produtos em Falta', icon: AlertTriangle, gradient: 'from-red-500 to-red-600', value: '0' },
  { key: 'accounts_receivable', label: 'Contas a Receber', icon: ArrowDownCircle, gradient: 'from-yellow-500 to-yellow-600', value: '0 Kz' },
  { key: 'accounts_payable', label: 'Contas a Pagar', icon: ArrowUpCircle, gradient: 'from-orange-500 to-orange-600', value: '0 Kz' },
];

const COLORS = ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];

export default function Dashboard() {
  const { user } = useAuth();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const fetchData = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.get('/dashboard');
      setData(res.data.data || res.data);
    } catch {
      setError('Erro ao carregar dados do dashboard.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchData(); }, []);

  const stats = data?.stats || {};
  const salesChart = data?.sales_chart || [];
  const categoryChart = data?.category_chart || [];
  const recentSales = data?.recent_sales || [];
  const lowStock = data?.low_stock || [];

  const container = { hidden: {}, show: { transition: { staggerChildren: 0.08 } } };
  const itemAnim = { hidden: { opacity: 0, y: 20 }, show: { opacity: 1, y: 0 } };

  if (loading) {
    return (
      <div className="p-6 space-y-6">
        <div className="h-8 bg-slate-200 dark:bg-slate-700 rounded w-64 animate-pulse" />
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-32 bg-slate-200 dark:bg-slate-700 rounded-xl animate-pulse" />
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6 flex flex-col items-center justify-center min-h-[60vh]">
        <AlertTriangle className="w-16 h-16 text-red-400 mb-4" />
        <p className="text-slate-600 dark:text-slate-400 mb-4">{error}</p>
        <button
          onClick={fetchData}
          className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition"
        >
          <RefreshCw className="w-4 h-4" /> Tentar novamente
        </button>
      </div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.3 }}
      className="p-6 space-y-6"
    >
      <div>
        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
          Bem-vindo, {user?.name || 'Usuário'}
        </h1>
        <p className="text-slate-500 dark:text-slate-400">Visão geral do seu negócio</p>
      </div>

      <motion.div variants={container} initial="hidden" animate="show" className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {statCards.map((card) => {
          const Icon = card.icon;
          const val = stats[card.key];
          return (
            <motion.div
              key={card.key}
              variants={itemAnim}
              className={`bg-gradient-to-br ${card.gradient} rounded-xl p-6 text-white shadow-lg`}
            >
              <div className="flex items-center justify-between mb-4">
                <Icon className="w-8 h-8 opacity-80" />
                <span className="text-3xl font-bold">{val ?? card.value}</span>
              </div>
              <p className="text-sm opacity-80">{card.label}</p>
            </motion.div>
          );
        })}
      </motion.div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          className="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6"
        >
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Vendas (12 meses)</h2>
          {salesChart.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={salesChart}>
                <CartesianGrid strokeDasharray="3 3" stroke="#334155" />
                <XAxis dataKey="month" stroke="#94a3b8" />
                <YAxis stroke="#94a3b8" />
                <Tooltip
                  contentStyle={{ background: '#1e293b', border: 'none', borderRadius: '8px', color: '#fff' }}
                />
                <Line type="monotone" dataKey="total" stroke="#2563EB" strokeWidth={2} dot={{ fill: '#2563EB' }} />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <div className="h-[300px] flex items-center justify-center text-slate-400">Sem dados</div>
          )}
        </motion.div>

        <motion.div
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6"
        >
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Vendas por Categoria</h2>
          {categoryChart.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie data={categoryChart} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={100} label>
                  {categoryChart.map((_, index) => (
                    <Cell key={index} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip
                  contentStyle={{ background: '#1e293b', border: 'none', borderRadius: '8px', color: '#fff' }}
                />
              </PieChart>
            </ResponsiveContainer>
          ) : (
            <div className="h-[300px] flex items-center justify-center text-slate-400">Sem dados</div>
          )}
        </motion.div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6"
        >
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Últimas Vendas</h2>
          {recentSales.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <th className="text-left py-2">Cliente</th>
                    <th className="text-right py-2">Total</th>
                    <th className="text-right py-2">Data</th>
                  </tr>
                </thead>
                <tbody>
                  {recentSales.map((sale, i) => (
                    <tr key={i} className="border-b border-slate-100 dark:border-slate-700/50 text-slate-700 dark:text-slate-300">
                      <td className="py-2">{sale.customer_name || sale.customer?.name || '-'}</td>
                      <td className="text-right py-2 font-medium">{Number(sale.total || 0).toLocaleString()} Kz</td>
                      <td className="text-right py-2 text-slate-500">{sale.date ? new Date(sale.date).toLocaleDateString() : '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-slate-400 text-center py-8">Nenhuma venda recente</p>
          )}
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
          className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6"
        >
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Produtos com Stock Baixo</h2>
          {lowStock.length > 0 ? (
            <div className="space-y-3">
              {lowStock.map((product, i) => (
                <div key={i} className="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                  <div>
                    <p className="font-medium text-slate-900 dark:text-white text-sm">{product.name}</p>
                    <p className="text-xs text-slate-500 dark:text-slate-400">Stock: {product.quantity} / Mín: {product.min_stock}</p>
                  </div>
                  <AlertTriangle className="w-5 h-5 text-red-500" />
                </div>
              ))}
            </div>
          ) : (
            <p className="text-slate-400 text-center py-8">Nenhum produto em falta</p>
          )}
        </motion.div>
      </div>
    </motion.div>
  );
}
