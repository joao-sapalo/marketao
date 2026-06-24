import api from './api';

export const createService = (endpoint) => ({
  getAll: (params) => api.get(`/${endpoint}`, { params }),
  getById: (id) => api.get(`/${endpoint}/${id}`),
  create: (data) => api.post(`/${endpoint}`, data),
  update: (id, data) => api.put(`/${endpoint}/${id}`, data),
  delete: (id) => api.delete(`/${endpoint}/${id}`),
});
