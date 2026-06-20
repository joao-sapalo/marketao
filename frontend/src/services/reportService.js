import api from './api';

export const reportService = {
  sales: (params) => api.get('/reports/sales', { params }),
  products: (params) => api.get('/reports/products', { params }),
  financial: (params) => api.get('/reports/financial', { params }),
};
