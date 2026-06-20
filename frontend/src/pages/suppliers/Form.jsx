import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Truck, Phone, Mail, FileText, MapPin, Map, StickyNote, Save, Loader2, ArrowLeft } from 'lucide-react';
import { createService } from '../../services/crudService';

const supplierService = createService('suppliers');

export default function SupplierForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditing = Boolean(id);
  const [form, setForm] = useState({
    name: '', phone: '', email: '', nif: '', address: '', city: '', notes: '',
  });
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEditing);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    if (isEditing) {
      setFetching(true);
      supplierService.getById(id)
        .then((res) => {
          const c = res.data.data || res.data;
          setForm({
            name: c.name || '', phone: c.phone || '', email: c.email || '',
            nif: c.nif || '', address: c.address || '', city: c.city || '', notes: c.notes || '',
          });
        })
        .catch(() => setError('Erro ao carregar fornecedor.'))
        .finally(() => setFetching(false));
    }
  }, [id, isEditing]);

  const handleChange = (e) => {
    setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    if (!form.name) { setError('O nome é obrigatório.'); return; }
    setLoading(true);
    try {
      if (isEditing) {
        await supplierService.update(id, form);
      } else {
        await supplierService.create(form);
      }
      setSuccess(isEditing ? 'Fornecedor atualizado com sucesso!' : 'Fornecedor criado com sucesso!');
      setTimeout(() => navigate('/suppliers'), 1500);
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao salvar fornecedor.');
    } finally {
      setLoading(false);
    }
  };

  if (fetching) {
    return (
      <div className="p-6 space-y-4">
        <div className="h-8 bg-slate-200 dark:bg-slate-700 rounded w-48 animate-pulse" />
        <div className="h-96 bg-slate-200 dark:bg-slate-700 rounded-xl animate-pulse" />
      </div>
    );
  }

  const fields = [
    { name: 'name', label: 'Nome', icon: Truck, required: true, placeholder: 'Nome do fornecedor' },
    { name: 'phone', label: 'Telefone', icon: Phone, placeholder: '+244 900 000 000' },
    { name: 'email', label: 'Email', icon: Mail, type: 'email', placeholder: 'fornecedor@email.com' },
    { name: 'nif', label: 'NIF', icon: FileText, placeholder: 'Número de identificação fiscal' },
    { name: 'address', label: 'Endereço', icon: MapPin, placeholder: 'Endereço completo' },
    { name: 'city', label: 'Cidade', icon: Map, placeholder: 'Cidade' },
  ];

  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }} transition={{ duration: 0.3 }} className="p-6">
      <button onClick={() => navigate('/suppliers')} className="flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mb-4 transition">
        <ArrowLeft className="w-4 h-4" /> Voltar
      </button>

      <div className="max-w-2xl mx-auto">
        <div className="flex items-center gap-3 mb-6">
          <Truck className="w-8 h-8 text-blue-600" />
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
              {isEditing ? 'Editar Fornecedor' : 'Novo Fornecedor'}
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400">
              {isEditing ? 'Atualize os dados do fornecedor' : 'Preencha os dados do novo fornecedor'}
            </p>
          </div>
        </div>

        {error && (
          <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg px-4 py-3 mb-6 text-sm">{error}</div>
        )}
        {success && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-lg px-4 py-3 mb-6 text-sm">{success}</motion.div>
        )}

        <form onSubmit={handleSubmit} className="bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-slate-900/50 p-6 space-y-5">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            {fields.map((field) => {
              const Icon = field.icon;
              return (
                <div key={field.name} className={field.name === 'address' || field.name === 'city' ? 'md:col-span-2' : ''}>
                  <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {field.label} {field.required && <span className="text-red-500">*</span>}
                  </label>
                  <div className="relative">
                    <Icon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                      type={field.type || 'text'}
                      name={field.name}
                      value={form[field.name]}
                      onChange={handleChange}
                      placeholder={field.placeholder}
                      className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    />
                  </div>
                </div>
              );
            })}
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Observações</label>
            <div className="relative">
              <StickyNote className="absolute left-3 top-3 w-5 h-5 text-slate-400" />
              <textarea
                name="notes"
                value={form.notes}
                onChange={handleChange}
                rows={3}
                placeholder="Observações adicionais..."
                className="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
              />
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <button type="button" onClick={() => navigate('/suppliers')} className="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancelar</button>
            <button type="submit" disabled={loading} className="flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-60">
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
              {loading ? 'Salvando...' : 'Salvar'}
            </button>
          </div>
        </form>
      </div>
    </motion.div>
  );
}
