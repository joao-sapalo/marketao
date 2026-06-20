import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { ClipboardList, Plus, Eye, Loader2, RefreshCw, AlertTriangle } from 'lucide-react';
import { createService } from '../../services/crudService';

const purchaseService = createService('purchases');

export default function PurchaseIndex() {
  const navigate = useNavigate();
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  const fetchData = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await purchaseService.getAll({ page, perPage: 10 });
      const d = res.data.data || res.data;
      setData(Array.isArray(d) ? d : d.data || []);
      setLastPage(d.last_page || d.lastPage || 1);
      setTotal(d.total || 0);
    } catch {
      setError('Erro ao carregar compras.');
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6 space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <ClipboardList className="w-8 h-8 text-blue-600" />
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Compras</h1>
            <p className="text-sm text-slate-500 dark:text-slate-400">{total} registos</p>
          </div>
        </div>
        <button onClick={() => navigate('/purchases/new')} className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
          <Plus className="w-4 h-4" /> Nova Compra
        </button>
      </div>

      {error && (
        <div className="flex items-center justify-between bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3">
          <span>{error}</span>
          <button onClick={fetchData} className="flex items-center gap-1 text-sm font-medium hover:underline"><RefreshCw className="w-4 h-4" /> Tentar novamente</button>
        </div>
      )}

      {loading ? (
        <div className="space-y-3">{Array.from({ length: 5 }).map((_, i) => (<div key={i} className="h-16 bg-slate-200 dark:bg-slate-700 rounded-xl animate-pulse" />))}</div>
      ) : data.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-16 text-slate-400">
          <ClipboardList className="w-16 h-16 mb-4" />
          <p className="text-lg font-medium">Nenhuma compra encontrada</p>
          <p className="text-sm">Clique em "Nova Compra" para adicionar.</p>
        </div>
      ) : (
        <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                  <th className="text-left px-4 py-3 font-medium">ID</th>
                  <th className="text-left px-4 py-3 font-medium">Fornecedor</th>
                  <th className="text-right px-4 py-3 font-medium">Total</th>
                  <th className="text-left px-4 py-3 font-medium">Data</th>
                  <th className="text-left px-4 py-3 font-medium">Observações</th>
                  <th className="text-right px-4 py-3 font-medium">Ações</th>
                </tr>
              </thead>
              <tbody>
                {data.map((purchase, i) => (
                  <motion.tr
                    key={purchase.id}
                    initial={{ opacity: 0, x: -10 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.03 }}
                    className="border-t border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition"
                  >
                    <td className="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">#{purchase.id}</td>
                    <td className="px-4 py-3 font-medium text-slate-900 dark:text-white">{purchase.supplier?.name || purchase.supplier_name || '-'}</td>
                    <td className="px-4 py-3 text-right font-medium text-slate-900 dark:text-white">{Number(purchase.total || 0).toLocaleString()} Kz</td>
                    <td className="px-4 py-3 text-slate-600 dark:text-slate-400">{purchase.date ? new Date(purchase.date).toLocaleDateString() : '-'}</td>
                    <td className="px-4 py-3 text-slate-600 dark:text-slate-400 max-w-[200px] truncate">{purchase.notes || '-'}</td>
                    <td className="px-4 py-3 text-right">
                      <button className="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><Eye className="w-4 h-4" /></button>
                    </td>
                  </motion.tr>
                ))}
              </tbody>
            </table>
          </div>
          {lastPage > 1 && (
            <div className="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700">
              <span className="text-sm text-slate-500">Página {page} de {lastPage}</span>
              <div className="flex gap-2">
                <button onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page === 1} className="px-3 py-1 text-sm rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Anterior</button>
                <button onClick={() => setPage((p) => Math.min(lastPage, p + 1))} disabled={page === lastPage} className="px-3 py-1 text-sm rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Seguinte</button>
              </div>
            </div>
          )}
        </div>
      )}
    </motion.div>
  );
}
