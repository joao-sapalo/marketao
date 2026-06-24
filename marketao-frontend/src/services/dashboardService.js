import api from './api';

export const dashboardService = {
  getData: () => api.get('/dashboard'),
};
