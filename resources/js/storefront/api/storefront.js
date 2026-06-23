const API_BASE = '/api/s';

async function apiFetch(endpoint, options = {}) {
  const res = await fetch(`${API_BASE}${endpoint}`, {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      ...options.headers,
    },
    ...options,
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: res.statusText }));
    throw new Error(error.message || `Erro ${res.status}`);
  }

  return res.json();
}

export function getStore(slug) {
  return apiFetch(`/${slug}`);
}

export function getStoreProducts(slug, params = {}) {
  const query = new URLSearchParams(params).toString();
  return apiFetch(`/${slug}/products${query ? `?${query}` : ''}`);
}

export function getStoreProduct(slug, productId) {
  return apiFetch(`/${slug}/products/${productId}`);
}

export function getCategories(slug) {
  return apiFetch(`/${slug}/categories`);
}

export function searchProducts(slug, q) {
  return apiFetch(`/${slug}/search?q=${encodeURIComponent(q)}`);
}

export function getCart(slug) {
  return apiFetch(`/${slug}/cart`);
}

export function updateCart(slug, productId, quantity) {
  return apiFetch(`/${slug}/cart`, {
    method: 'POST',
    body: JSON.stringify({ product_id: productId, quantity }),
  });
}

export function clearCart(slug) {
  return apiFetch(`/${slug}/cart`, { method: 'DELETE' });
}

export function interpretQuery(slug, query) {
  return apiFetch(`/${slug}/cart/interpret`, {
    method: 'POST',
    body: JSON.stringify({ query }),
  });
}

export function createOrder(slug, data) {
  return apiFetch(`/${slug}/orders`, {
    method: 'POST',
    body: JSON.stringify(data),
  });
}

export function getOrderByReference(slug, reference) {
  return apiFetch(`/${slug}/orders/by-reference/${reference}`);
}

export function getOrder(slug, orderId) {
  return apiFetch(`/${slug}/orders/${orderId}`);
}

export function getOrders(slug) {
  return apiFetch(`/${slug}/orders`);
}

export function submitReview(slug, orderId, rating, comment) {
  return apiFetch(`/${slug}/orders/${orderId}/review`, {
    method: 'POST',
    body: JSON.stringify({ rating, comment }),
  });
}

export function uploadPaymentProof(slug, orderId, file) {
  const formData = new FormData();
  formData.append('proof', file);
  return fetch(`${API_BASE}/${slug}/orders/${orderId}/payment-proof`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    },
    body: formData,
  }).then(res => res.json());
}
