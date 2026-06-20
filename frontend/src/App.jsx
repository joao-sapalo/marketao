import { BrowserRouter, Routes, Route, Navigate, Link } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import MainLayout from './layouts/MainLayout';
import Login from './pages/auth/Login';
import Register from './pages/auth/Register';
import OtpVerification from './pages/auth/OtpVerification';
import ForgotPassword from './pages/auth/ForgotPassword';
import ResetPassword from './pages/auth/ResetPassword';
import Dashboard from './pages/Dashboard';
import CustomersIndex from './pages/customers/Index';
import CustomersForm from './pages/customers/Form';
import SuppliersIndex from './pages/suppliers/Index';
import SuppliersForm from './pages/suppliers/Form';
import CategoriesIndex from './pages/categories/Index';
import CategoriesForm from './pages/categories/Form';
import ProductsIndex from './pages/products/Index';
import ProductsForm from './pages/products/Form';
import SalesIndex from './pages/sales/Index';
import SalesPDV from './pages/sales/PDV';
import PurchasesIndex from './pages/purchases/Index';
import PurchasesForm from './pages/purchases/Form';
import StockIndex from './pages/stock/Index';
import CashIndex from './pages/cash/Index';
import Receivable from './pages/financial/Receivable';
import Payable from './pages/financial/Payable';
import Reports from './pages/reports/Index';

function PrivateRoute({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="flex items-center justify-center h-screen bg-slate-900"><div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>;
  return user ? children : <Navigate to="/login" />;
}

function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800 p-4">
      <div className="text-center">
        <h1 className="text-6xl font-bold text-slate-300 dark:text-slate-600 mb-4">404</h1>
        <p className="text-lg text-slate-600 dark:text-slate-400 mb-6">Página não encontrada</p>
        <Link to="/" className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition font-medium">Voltar ao Início</Link>
      </div>
    </div>
  );
}

function PublicRoute({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="flex items-center justify-center h-screen bg-slate-900"><div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>;
  return user ? <Navigate to="/" /> : children;
}

function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<PublicRoute><Login /></PublicRoute>} />
      <Route path="/register" element={<PublicRoute><Register /></PublicRoute>} />
      <Route path="/verify-otp" element={<PublicRoute><OtpVerification /></PublicRoute>} />
      <Route path="/forgot-password" element={<PublicRoute><ForgotPassword /></PublicRoute>} />
      <Route path="/reset-password" element={<PublicRoute><ResetPassword /></PublicRoute>} />
      <Route path="/" element={<PrivateRoute><MainLayout><Dashboard /></MainLayout></PrivateRoute>} />
      <Route path="/customers" element={<PrivateRoute><MainLayout><CustomersIndex /></MainLayout></PrivateRoute>} />
      <Route path="/customers/new" element={<PrivateRoute><MainLayout><CustomersForm /></MainLayout></PrivateRoute>} />
      <Route path="/customers/:id/edit" element={<PrivateRoute><MainLayout><CustomersForm /></MainLayout></PrivateRoute>} />
      <Route path="/suppliers" element={<PrivateRoute><MainLayout><SuppliersIndex /></MainLayout></PrivateRoute>} />
      <Route path="/suppliers/new" element={<PrivateRoute><MainLayout><SuppliersForm /></MainLayout></PrivateRoute>} />
      <Route path="/suppliers/:id/edit" element={<PrivateRoute><MainLayout><SuppliersForm /></MainLayout></PrivateRoute>} />
      <Route path="/categories" element={<PrivateRoute><MainLayout><CategoriesIndex /></MainLayout></PrivateRoute>} />
      <Route path="/categories/new" element={<PrivateRoute><MainLayout><CategoriesForm /></MainLayout></PrivateRoute>} />
      <Route path="/categories/:id/edit" element={<PrivateRoute><MainLayout><CategoriesForm /></MainLayout></PrivateRoute>} />
      <Route path="/products" element={<PrivateRoute><MainLayout><ProductsIndex /></MainLayout></PrivateRoute>} />
      <Route path="/products/new" element={<PrivateRoute><MainLayout><ProductsForm /></MainLayout></PrivateRoute>} />
      <Route path="/products/:id/edit" element={<PrivateRoute><MainLayout><ProductsForm /></MainLayout></PrivateRoute>} />
      <Route path="/sales" element={<PrivateRoute><MainLayout><SalesIndex /></MainLayout></PrivateRoute>} />
      <Route path="/sales/pdv" element={<PrivateRoute><MainLayout><SalesPDV /></MainLayout></PrivateRoute>} />
      <Route path="/purchases" element={<PrivateRoute><MainLayout><PurchasesIndex /></MainLayout></PrivateRoute>} />
      <Route path="/purchases/new" element={<PrivateRoute><MainLayout><PurchasesForm /></MainLayout></PrivateRoute>} />
      <Route path="/stock" element={<PrivateRoute><MainLayout><StockIndex /></MainLayout></PrivateRoute>} />
      <Route path="/stock/new" element={<PrivateRoute><MainLayout><StockIndex /></MainLayout></PrivateRoute>} />
      <Route path="/cash" element={<PrivateRoute><MainLayout><CashIndex /></MainLayout></PrivateRoute>} />
      <Route path="/financial/receivable" element={<PrivateRoute><MainLayout><Receivable /></MainLayout></PrivateRoute>} />
      <Route path="/financial/payable" element={<PrivateRoute><MainLayout><Payable /></MainLayout></PrivateRoute>} />
      <Route path="/reports" element={<PrivateRoute><MainLayout><Reports /></MainLayout></PrivateRoute>} />
      <Route path="*" element={<NotFound />} />
    </Routes>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <ThemeProvider>
        <AuthProvider>
          <AppRoutes />
        </AuthProvider>
      </ThemeProvider>
    </BrowserRouter>
  );
}
