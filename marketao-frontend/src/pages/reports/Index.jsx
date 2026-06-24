import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import {
  BarChart3, FileText, Package, DollarSign, Download, Calendar,
  RefreshCw, Loader2,
} from 'lucide-react';
import { BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from 'recharts';
import api from '../../services/api';

const COLORS = ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];

const dateRanges = [
  { key: 'today', label: 'Hoje' },
  { key: 'week', label: 'Esta Semana' },
  { key: 'month', label: 'Este Mês' },
  { key: 'year', label: 'Este Ano' },
  { key: 'custom', label: 'Personalizado' },
];

const tabs = [
  { key: 'sales', label: 'Vendas', icon: BarChart3 },
  { key: 'products', label: 'Produtos', icon: Package },
  { key: 'financial', label: 'Financeiro', icon: DollarSign },
];

export default function Reports() {
  const [activeTab, setActiveTab] = useState('sales');
  const [dateRange, setDateRange] = useState('month');
  const [customFrom, setCustomFrom] = useState('');
  const [customTo, setCustomTo] = useState('');
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const fetchData = async () => {
    setLoading(true);
    setError('');
    try {
      const params = { range: dateRange };
      if (dateRange === 'custom') {
        if (customFrom) params.date_from = customFrom;
        if (customTo) params.date_to = customTo;
      }
      const res = await api.get(`/reports/${activeTab}`, { params });
      setData(res.data.data || res.data);
    } catch {
      setError('Erro ao carregar relatório.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchData(); }, [activeTab, dateRange]);

  const handleExport = () => {
    // Placeholder for export functionality
    alert('Exportação será implementada em breve.');
  };

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6 space-y-6">
      <div className="flex items-center gap-3">
        <BarChart3 className="w-8 h-8 text-blue-600" />
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Relatórios</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">Análise e exportação de dados</p>
        </div>
      </div>

      <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-4">
        <div className="flex flex-wrap items-center gap-3">
          <Calendar className="w-5 h-5 text-slate-400" />
          {dateRanges.map((dr) => (
            <button
              key={dr.key}
              onClick={() => setDateRange(dr.key)}
              className={`px-3 py-1.5 text-sm font-medium rounded-lg transition ${
                dateRange === dr.key
                  ? 'bg-blue-600 text-white'
                  : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
              }`}
            >
              {dr.label}
            </button>
          ))}
          {dateRange === 'custom' && (
            <div className="flex items-center gap-2">
              <input type="date" value={customFrom} onChange={(e) => setCustomFrom(e.target.value)} className="px-2 py-1.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <span className="text-slate-400">até</span>
              <input type="date" value={customTo} onChange={(e) => setCustomTo(e.target.value)} className="px-2 py-1.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <button onClick={fetchData} className="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"><RefreshCw className="w-4 h-4" /></button>
            </div>
          )}
        </div>
      </div>

      <div className="flex gap-2">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          return (
            <button
              key={tab.key}
              onClick={() => setActiveTab(tab.key)}
              className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition ${
                activeTab === tab.key
                  ? 'bg-blue-600 text-white'
                  : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700'
              }`}
            >
              <Icon className="w-4 h-4" /> {tab.label}
            </button>
          );
        })}
        <button
          onClick={handleExport}
          className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition ml-auto"
        >
          <Download className="w-4 h-4" /> Exportar
        </button>
      </div>

      {error && (
        <div className="flex items-center justify-between bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3">
          <span>{error}</span>
          <button onClick={fetchData} className="flex items-center gap-1 text-sm font-medium hover:underline"><RefreshCw className="w-4 h-4" /> Tentar novamente</button>
        </div>
      )}

      {loading ? (
        <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
          <div className="h-8 bg-slate-200 dark:bg-slate-700 rounded w-48 mb-6 animate-pulse" />
          <div className="h-64 bg-slate-200 dark:bg-slate-700 rounded-xl animate-pulse" />
        </div>
      ) : (
        <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
          {activeTab === 'sales' && <SalesReport data={data} />}
          {activeTab === 'products' && <ProductsReport data={data} />}
          {activeTab === 'financial' && <FinancialReport data={data} />}
        </div>
      )}
    </motion.div>
  );
}

function SalesReport({ data }) {
  const sales = data?.sales || [];
  const chartData = data?.chart || [];

  return (
    <div className="space-y-6">
      <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Relatório de Vendas</h2>
      {chartData.length > 0 && (
        <ResponsiveContainer width="100%" height={300}>
          <LineChart data={chartData}>
            <CartesianGrid strokeDasharray="3 3" stroke="#334155" />
            <XAxis dataKey="label" stroke="#94a3b8" />
            <YAxis stroke="#94a3b8" />
            <Tooltip contentStyle={{ background: '#1e293b', border: 'none', borderRadius: '8px', color: '#fff' }} />
            <Line type="monotone" dataKey="value" stroke="#2563EB" strokeWidth={2} dot={{ fill: '#2563EB' }} />
          </LineChart>
        </ResponsiveContainer>
      )}
      {sales.length > 0 ? (
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                <th className="text-left py-2">Data</th>
                <th className="text-left py-2">Cliente</th>
                <th className="text-right py-2">Total</th>
              </tr>
            </thead>
            <tbody>
              {sales.map((sale, i) => (
                <tr key={i} className="border-b border-slate-100 dark:border-slate-700/50 text-slate-700 dark:text-slate-300">
                  <td className="py-2">{sale.date ? new Date(sale.date).toLocaleDateString() : '-'}</td>
                  <td className="py-2">{sale.customer_name || '-'}</td>
                  <td className="py-2 text-right font-medium">{Number(sale.total || 0).toLocaleString()} Kz</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        <p className="text-slate-400 text-center py-8">Nenhum dado de vendas para o período selecionado.</p>
      )}
    </div>
  );
}

function ProductsReport({ data }) {
  const lowStock = data?.low_stock || [];
  const stockByCategory = data?.stock_by_category || [];

  return (
    <div className="space-y-6">
      <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Relatório de Produtos</h2>
      {stockByCategory.length > 0 && (
        <ResponsiveContainer width="100%" height={250}>
          <PieChart>
            <Pie data={stockByCategory} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={80} label>
              {stockByCategory.map((_, index) => (
                <Cell key={index} fill={COLORS[index % COLORS.length]} />
              ))}
            </Pie>
            <Tooltip contentStyle={{ background: '#1e293b', border: 'none', borderRadius: '8px', color: '#fff' }} />
          </PieChart>
        </ResponsiveContainer>
      )}
      {lowStock.length > 0 && (
        <div>
          <h3 className="text-md font-medium text-slate-900 dark:text-white mb-3">Produtos com Stock Baixo</h3>
          <div className="space-y-2">
            {lowStock.map((p, i) => (
              <div key={i} className="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/10 rounded-lg">
                <div>
                  <p className="font-medium text-slate-900 dark:text-white text-sm">{p.name}</p>
                  <p className="text-xs text-slate-500">Stock: {p.quantity} / Mín: {p.min_stock}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
      {lowStock.length === 0 && stockByCategory.length === 0 && (
        <p className="text-slate-400 text-center py-8">Nenhum dado de produtos disponível.</p>
      )}
    </div>
  );
}

function FinancialReport({ data }) {
  const income = data?.total_income || 0;
  const expenses = data?.total_expenses || 0;
  const balance = income - expenses;

  return (
    <div className="space-y-6">
      <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Relatório Financeiro</h2>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-green-50 dark:bg-green-900/10 rounded-xl p-4">
          <p className="text-sm text-green-600 dark:text-green-400">Receitas</p>
          <p className="text-2xl font-bold text-green-700 dark:text-green-400">{Number(income).toLocaleString()} Kz</p>
        </div>
        <div className="bg-red-50 dark:bg-red-900/10 rounded-xl p-4">
          <p className="text-sm text-red-600 dark:text-red-400">Despesas</p>
          <p className="text-2xl font-bold text-red-700 dark:text-red-400">{Number(expenses).toLocaleString()} Kz</p>
        </div>
        <div className={`rounded-xl p-4 ${balance >= 0 ? 'bg-blue-50 dark:bg-blue-900/10' : 'bg-red-50 dark:bg-red-900/10'}`}>
          <p className="text-sm text-slate-500 dark:text-slate-400">Saldo</p>
          <p className={`text-2xl font-bold ${balance >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-red-700 dark:text-red-400'}`}>
            {Number(balance).toLocaleString()} Kz
          </p>
        </div>
      </div>
      {income === 0 && expenses === 0 && (
        <p className="text-slate-400 text-center py-8">Nenhum dado financeiro disponível.</p>
      )}
    </div>
  );
}
