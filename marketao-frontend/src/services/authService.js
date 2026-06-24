import api from './api';

export const authService = {
  login: (credentials) => api.post('/login', credentials),
  register: (data) => api.post('/register', data),
  logout: () => api.post('/logout'),
  user: () => api.get('/user'),
  updateProfile: (data) => api.put('/profile', data),
  sendOtp: (data) => api.post('/send-otp', data),
  verifyOtp: (data) => api.post('/verify-otp', data),
  forgotPassword: (data) => api.post('/forgot-password', data),
  resetPassword: (data) => api.post('/reset-password', data),
};
