import { NavLink } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
  LayoutDashboard, Users, Truck, Package, Tags,
  ShoppingCart, ShoppingBag, Warehouse, Banknote,
  ArrowDownCircle, ArrowUpCircle, BarChart3,
  LogOut, ChevronLeft, ChevronRight,
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const navItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/customers', label: 'Clientes', icon: Users },
  { to: '/suppliers', label: 'Fornecedores', icon: Truck },
  { to: '/products', label: 'Produtos', icon: Package },
  { to: '/categories', label: 'Categorias', icon: Tags },
  { to: '/sales', label: 'Vendas', icon: ShoppingCart },
  { to: '/purchases', label: 'Compras', icon: ShoppingBag },
  { to: '/stock', label: 'Estoque', icon: Warehouse },
  { to: '/cash', label: 'Caixa', icon: Banknote },
  { to: '/financial/receivable', label: 'Contas a Receber', icon: ArrowDownCircle },
  { to: '/financial/payable', label: 'Contas a Pagar', icon: ArrowUpCircle },
  { to: '/reports', label: 'Relatórios', icon: BarChart3 },
];

export default function Sidebar({ open, onClose }) {
  const { user, logout } = useAuth();

  return (
    <>
      {open && (
        <div
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={onClose}
        />
      )}
      <aside
        className={`
          fixed lg:static inset-y-0 left-0 z-50 flex flex-col
          bg-slate-900 dark:bg-slate-950 text-white
          transition-all duration-300 ease-in-out
          ${open ? 'w-64' : '-translate-x-full lg:translate-x-0 lg:w-64'}
        `}
      >
        <div className="flex items-center justify-between h-16 px-4 border-b border-slate-700/50 shrink-0 min-w-64">
          <span className="text-xl font-bold tracking-wider">MarketAO</span>
          <button onClick={onClose} className="lg:hidden p-1 rounded hover:bg-slate-700">
            <ChevronLeft size={20} />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto py-4 space-y-1 min-w-64">
          {navItems.map(({ to, label, icon: Icon }) => (
            <NavLink
              key={to}
              to={to}
              end={to === '/'}
              onClick={onClose}
              className={({ isActive }) =>
                `flex items-center gap-3 px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors ${
                  isActive
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                    : 'text-slate-400 hover:text-white hover:bg-slate-800'
                }`
              }
            >
              <Icon size={20} className="shrink-0" />
              <span>{label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="border-t border-slate-700/50 p-4 min-w-64">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">
              {user?.name?.charAt(0)?.toUpperCase() || 'U'}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium truncate">{user?.name || 'Usuário'}</p>
              <p className="text-xs text-slate-400 truncate">{user?.email || ''}</p>
            </div>
          </div>
          <button
            onClick={logout}
            className="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
          >
            <LogOut size={16} />
            <span>Sair</span>
          </button>
        </div>
      </aside>
    </>
  );
}
