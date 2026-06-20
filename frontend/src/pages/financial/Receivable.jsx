import { useState, useEffect, useCallback } from 'react';
import { motion } from 'framer-motion';
import { ArrowDownCircle, CheckCircle, Loader2, RefreshCw, AlertTriangle, Filter } from 'lucide-react';
import { createService } from '../../services/crudService';

const receivableService = createService('accounts-receivable');

const statusStyle = (status) => {
  const map = {
    pending: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    paid: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  };
  return map[status] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
};

const statusLabel = (s) => ({ pending: 'Pendente', paid: 'Pago', overdue: 'Vencido' }[s] || s);

export default function Receivable() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [payingId, setPayingId] = useState(null);

  const fetchData = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const params = { page, perPage: 10 };
      if (statusFilter) params.status = statusFilter;
      const res = await receivableService.getAll(params);
      const d = res.data.data || res.data;
      setData(Array.isArray(d) ? d : d.data || []);
      setLastPage(d.last_page || d.lastPage || 1);
      setTotal(d.total || 0);
    } catch {
      setError('Erro ao carregar contas a receber.');
    } finally {
      setLoading(false);
    }
  }, [page, statusFilter]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleMarkPaid = async (id) => {
    setPayingId(id);
    try {
      await receivableService.update(id, { status: 'paid' });
      fetchData();
    } catch {
      setError('Erro ao marcar como pago.');
    } finally {
      setPayingId(null);
    }
  };

  const totalAmount = data.reduce((s, item) => s + Number(item.amount || 0), 0);

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6 space-y-6">
      <div className="flex items-center gap-3">
        <ArrowDownCircle className="w-8 h-8 text-blue-600" />
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Contas a Receber</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">Total: {totalAmount.toLocaleString()} Kz</p>
        </div>
      </div>

      <div className="flex gap-3">
        <select value={statusFilter} onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }} className="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos os estados</option>
          <option value="pending">Pendente</option>
          <option value="paid">Pago</option>
          <option value="overdue">Vencido</option>
        </select>
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
          <ArrowDownCircle className="w-16 h-16 mb-4" />
          <p className="text-lg font-medium">Nenhuma conta a receber</p>
        </div>
      ) : (
        <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                  <th className="text-left px-4 py-3 font-medium">Cliente</th>
                  <th className="text-right px-4 py-3 font-medium">Valor</th>
                  <th className="text-left px-4 py-3 font-medium">Vencimento</th>
                  <th className="text-center px-4 py-3 font-medium">Estado</th>
                  <th className="text-right px-4 py-3 font-medium">Ações</th>
                </tr>
              </thead>
              <tbody>
                {data.map((item, i) => (
                  <motion.tr
                    key={item.id}
                    initial={{ opacity: 0, x: -10 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.03 }}
                    className="border-t border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition"
                  >
                    <td className="px-4 py-3 font-medium text-slate-900 dark:text-white">{item.customer?.name || item.customer_name || '-'}</td>
                    <td className="px-4 py-3 text-right font-medium text-slate-900 dark:text-white">{Number(item.amount || 0).toLocaleString()} Kz</td>
                    <td className="px-4 py-3 text-slate-600 dark:text-slate-400">{item.due_date ? new Date(item.due_date).toLocaleDateString() : '-'}</td>
                    <td className="px-4 py-3 text-center">
                      <span className={`inline-block px-2 py-0.5 text-xs font-medium rounded-full ${statusStyle(item.status)}`}>{statusLabel(item.status)}</span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      {item.status !== 'paid' && (
                        <button
                          onClick={() => handleMarkPaid(item.id)}
                          disabled={payingId === item.id}
                          className="flex items-center gap-1 ml-auto px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition disabled:opacity-60"
                        >
                          {payingId === item.id ? <Loader2 className="w-3 h-3 animate-spin" /> : <CheckCircle className="w-3 h-3" />}
                          Marcar Pago
                        </button>
                      )}
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
