import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const clinicId = localStorage.getItem('clinic_id');
    const token = localStorage.getItem('token');
    if (clinicId) config.headers['X-Clinic-Id'] = clinicId;
    if (token) config.headers['Authorization'] = `Bearer ${token}`;
  }
  return config;
});

export default api;
