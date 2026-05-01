import apiClient from './client';

export const authApi = {
  login: async (credentials: { username: string; password: string }) => {
    const response = await apiClient.post('/api/login_check', credentials);
    return response.data;
  },

  logout: async () => {
    const response = await apiClient.post('/api/logout');
    return response.data;
  }
};
