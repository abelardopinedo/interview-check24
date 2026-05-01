import apiClient from './client';

export const adminApi = {
  getLogs: async (params: any) => {
    const response = await apiClient.get('/api/admin/logs', { params });
    return response.data;
  },

  getLogDetails: async (id: number) => {
    const response = await apiClient.get(`/api/admin/logs/${id}`);
    return response.data;
  },

  getPerformanceStats: async () => {
    const response = await apiClient.get('/api/admin/logs/stats/performance');
    return response.data;
  }
};
