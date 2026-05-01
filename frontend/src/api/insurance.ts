import apiClient from './client';
import type { Quote } from '../types/Quote';
import type { RequestQuote } from '../types/RequestQuote';

export const insuranceApi = {
  calculateQuotes: async (payload: RequestQuote): Promise<Quote[]> => {
    const response = await apiClient.post<Quote[]>('/api/calculate', payload);
    return response.data;
  },

  getProviders: async () => {
    const response = await apiClient.get('/api/providers');
    return response.data;
  },

  updateProvider: async (id: number, data: any) => {
    const response = await apiClient.patch(`/api/providers/${id}`, data);
    return response.data;
  }
};
