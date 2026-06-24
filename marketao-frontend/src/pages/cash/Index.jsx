import { useState, useEffect, useCallback } from 'react';
import { motion } from 'framer-motion';
import {
  DollarSign, Plus, MinusCircle, Loader2, RefreshCw,
  AlertTriangle, Lock, Unlock, ArrowDownCircle, ArrowUpCircle,
} from 'lucide-react';
import { createService } from '../../services/crudService';

const cashService = createService('cash-registers');
const movementService = createService('cash-movements');

export default function CashIndex() {
  const [register, setRegister] = useState(null);
  const [movements, setMovements] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showOpenModal, setShowOpenModal] = useState(false);
  const [showMovementModal, setShowMovementModal] = useState(false);
  const [movementType, setMovementType] = useState('in');
  const [openingBalance, setOpeningBalance] = useState(0);
  const [movementForm, setMovementForm] = useState({ amount: '', description: '' });
  const [submitting, setSubmitting] = useState(false);

  const fetchData = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await cashService.getAll({ status: 'open' });
      const d = res.data.data || res.data;
      const openRegisters = Array.isArray(d) ? d : d.data || [];
      const current = openRegisters[0] || null;
      setRegister(current);
      if (current) {
        const mRes = await movementService.getAll({ cash_register_id: current.id, perPage: 100 });
        const md = mRes.data.data || mRes.data;
        setMovements(Array.isArray(md) ? md : md.data || []);
      } else {
        setMovements([]);
      }
    } catch {
      setError('Erro ao carregar dados do caixa.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleOpenRegister = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');
    try {
      await cashService.create({ opening_balance: openingBalance, status: 'open' });
      setShowOpenModal(false);
      setOpeningBalance(0);
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao abrir caixa.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleAddMovement = async (e) => {
    e.preventDefault();
    if (!movementForm.amount || !movementForm.description) {
      setError('Preencha o valor e a descrição.');
      return;
    }
    setSubmitting(true);
    setError('');
    try {
      await movementService.create({
        cash_register_id: register.id,
        type: movementType,
        amount: movementForm.amount,
        description: movementForm.description,
      });
      setShowMovementModal(false);
      setMovementForm({ amount: '', description: '' });
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao registar movimento.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleCloseRegister = async () => {
    if (!register) return;
    setSubmitting(true);
    setError('');
    try {
      await cashService.update(register.id, { status: 'closed' });
      fetchData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao fechar caixa.');
    } finally {
      setSubmitting(false);
    }
  };

  const totalIn = movements.filter((m) => m.type === 'in').reduce((s, m) => s + Number(m.amount || 0), 0);
  const totalOut = movements.filter((m) => m.type === 'out').reduce((s, m) => s + Number(m.amount || 0), 0);
  const currentBalance = (register ? Number(register.opening_balance || 0) : 0) + totalIn - totalOut;

  if (loading) {
    return (
      <div className="p-6 space-y-4">
        <div className="h-8 bg-slate-200 dark:bg-slate-700 rounded w-48 animate-pulse" />
        <div className="h-48 bg-slate-200 dark:bg-slate-700 rounded-xl animate-pulse" />
      </div>
    );
  }

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6 space-y-6">
      <div className="flex items-center gap-3 mb-2">
        <DollarSign className="w-8 h-8 text-blue-600" />
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Caixa</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">Gestão de caixa diário</p>
        </div>
      </div>

      {error && (
        <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3 text-sm">{error}</div>
      )}

      <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            {register ? (
              <span className="flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">
                <Unlock className="w-4 h-4" /> Caixa Aberto
              </span>
            ) : (
              <span className="flex items-center gap-2 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-sm font-medium">
                <Lock className="w-4 h-4" /> Caixa Fechado
              </span>
            )}
          </div>
          {register ? (
            <button onClick={handleCloseRegister} disabled={submitting} className="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-60">
              {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Lock className="w-4 h-4" />}
              Fechar Caixa
            </button>
          ) : (
            <button onClick={() => setShowOpenModal(true)} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
              <Unlock className="w-4 h-4" /> Abrir Caixa
            </button>
          )}
        </div>

        {register && (
          <>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div className="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
                <p className="text-sm text-slate-500 dark:text-slate-400">Saldo Inicial</p>
                <p className="text-2xl font-bold text-slate-900 dark:text-white">{Number(register.opening_balance || 0).toLocaleString()} Kz</p>
              </div>
              <div className="bg-green-50 dark:bg-green-900/10 rounded-xl p-4">
                <p className="text-sm text-green-600 dark:text-green-400">Entradas</p>
                <p className="text-2xl font-bold text-green-700 dark:text-green-400">{totalIn.toLocaleString()} Kz</p>
              </div>
              <div className="bg-red-50 dark:bg-red-900/10 rounded-xl p-4">
                <p className="text-sm text-red-600 dark:text-red-400">Saídas</p>
                <p className="text-2xl font-bold text-red-700 dark:text-red-400">{totalOut.toLocaleString()} Kz</p>
              </div>
            </div>

            <div className="bg-blue-50 dark:bg-blue-900/10 rounded-xl p-4 mb-6">
              <p className="text-sm text-blue-600 dark:text-blue-400">Saldo Atual</p>
              <p className="text-3xl font-bold text-blue-700 dark:text-blue-400">{currentBalance.toLocaleString()} Kz</p>
            </div>

            <div className="flex gap-3 mb-6">
              <button onClick={() => { setMovementType('in'); setShowMovementModal(true); }} className="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                <ArrowDownCircle className="w-4 h-4" /> Entrada
              </button>
              <button onClick={() => { setMovementType('out'); setShowMovementModal(true); }} className="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                <ArrowUpCircle className="w-4 h-4" /> Saída
              </button>
            </div>

            <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-3">Movimentos</h3>
            {movements.length === 0 ? (
              <p className="text-slate-400 text-center py-4">Nenhum movimento registado</p>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                      <th className="text-left py-2">Data</th>
                      <th className="text-center py-2">Tipo</th>
                      <th className="text-right py-2">Valor</th>
                      <th className="text-left py-2">Descrição</th>
                    </tr>
                  </thead>
                  <tbody>
                    {movements.map((m, i) => (
                      <tr key={m.id || i} className="border-b border-slate-100 dark:border-slate-700/50 text-slate-700 dark:text-slate-300">
                        <td className="py-2">{m.date ? new Date(m.date).toLocaleDateString() : '-'}</td>
                        <td className="py-2 text-center">
                          <span className={`inline-block px-2 py-0.5 text-xs font-medium rounded-full ${m.type === 'in' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'}`}>
                            {m.type === 'in' ? 'Entrada' : 'Saída'}
                          </span>
                        </td>
                        <td className="py-2 text-right font-medium">{Number(m.amount || 0).toLocaleString()} Kz</td>
                        <td className="py-2 text-slate-500">{m.description || '-'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </>
        )}

        {!register && (
          <div className="text-center py-8 text-slate-400">
            <Lock className="w-12 h-12 mx-auto mb-3" />
            <p>O caixa está fechado. Clique em "Abrir Caixa" para começar.</p>
          </div>
        )}
      </div>

      {showOpenModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <motion.div initial={{ scale: 0.9, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} className="bg-white dark:bg-slate-800 rounded-xl shadow-xl p-6 max-w-sm w-full">
            <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Abrir Caixa</h2>
            <form onSubmit={handleOpenRegister} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Saldo Inicial</label>
                <input type="number" step="0.01" value={openingBalance} onChange={(e) => setOpeningBalance(parseFloat(e.target.value) || 0)} placeholder="0,00" className="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
              </div>
              <div className="flex justify-end gap-3">
                <button type="button" onClick={() => setShowOpenModal(false)} className="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancelar</button>
                <button type="submit" disabled={submitting} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-60">
                  {submitting && <Loader2 className="w-4 h-4 animate-spin" />} Abrir
                </button>
              </div>
            </form>
          </motion.div>
        </div>
      )}

      {showMovementModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <motion.div initial={{ scale: 0.9, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} className="bg-white dark:bg-slate-800 rounded-xl shadow-xl p-6 max-w-sm w-full">
            <h2 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">
              {movementType === 'in' ? 'Registar Entrada' : 'Registar Saída'}
            </h2>
            <form onSubmit={handleAddMovement} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Valor</label>
                <input type="number" step="0.01" value={movementForm.amount} onChange={(e) => setMovementForm((p) => ({ ...p, amount: e.target.value }))} placeholder="0,00" className="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
              </div>
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descrição</label>
                <input type="text" value={movementForm.description} onChange={(e) => setMovementForm((p) => ({ ...p, description: e.target.value }))} placeholder="Descrição do movimento" className="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
              </div>
              <div className="flex justify-end gap-3">
                <button type="button" onClick={() => setShowMovementModal(false)} className="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancelar</button>
                <button type="submit" disabled={submitting} className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-60">
                  {submitting && <Loader2 className="w-4 h-4 animate-spin" />} Registar
                </button>
              </div>
            </form>
          </motion.div>
        </div>
      )}
    </motion.div>
  );
}
