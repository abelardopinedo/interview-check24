import type { LogSummary, LogDetail, PerformanceStats, PaginatedResponse } from '@/types/admin';
import apiClient from './client';

export const adminApi = {
  getLogs: async (params?: Record<string, unknown>): Promise<PaginatedResponse<LogSummary>> => {
    const response = await apiClient.get<PaginatedResponse<LogSummary>>('/api/admin/logs', { params });
    return response.data;
  },

  getLogDetails: async (id: number): Promise<LogDetail> => {
    const response = await apiClient.get<LogDetail>(`/api/admin/logs/${id}`);
    return response.data;
  },

  getPerformanceStats: async (): Promise<PerformanceStats> => {
    const response = await apiClient.get<PerformanceStats>('/api/admin/logs/stats/performance');
    return response.data;
  }
};
