import axios from 'axios';
import { getCookie } from '../utils/cookie';

const apiClient = axios.create({
  baseURL: '/',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor to include the JWT token if available
apiClient.interceptors.request.use((config) => {
  const token = getCookie('jwt_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default apiClient;
