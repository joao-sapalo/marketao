import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import StoreLayout from './components/StoreLayout';
import StoreHome from './pages/StoreHome';
import ProductList from './pages/ProductList';
import ProductDetail from './pages/ProductDetail';
import CartPage from './pages/CartPage';
import OrderTracking from './pages/OrderTracking';
import ReviewForm from './pages/ReviewForm';

function App() {
  const pathParts = window.location.pathname.split('/');
  const slugIndex = pathParts.indexOf('loja') + 1;
  const slug = pathParts[slugIndex] || '';

  return (
    <BrowserRouter>
      <StoreLayout slug={slug}>
        <Routes>
          <Route path={`/loja/${slug}`} element={<StoreHome slug={slug} />} />
          <Route path={`/loja/${slug}/products`} element={<ProductList slug={slug} />} />
          <Route path={`/loja/${slug}/products/:productId`} element={<ProductDetail slug={slug} />} />
          <Route path={`/loja/${slug}/cart`} element={<CartPage slug={slug} />} />
          <Route path={`/loja/${slug}/orders/:orderId`} element={<OrderTracking slug={slug} />} />
          <Route path={`/loja/${slug}/orders/:orderId/review`} element={<ReviewForm slug={slug} />} />
        </Routes>
      </StoreLayout>
    </BrowserRouter>
  );
}

const root = document.getElementById('storefront-root');
if (root) {
  ReactDOM.createRoot(root).render(<App />);
}
