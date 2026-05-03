import type { Quote } from '../types/Quote';
import type { RequestQuote } from '../types/RequestQuote';
import type { Provider, UpdateProvider } from '@/types/insurance';
import apiClient from './client';

export const insuranceApi = {
  calculateQuotes: async (payload: RequestQuote): Promise<Quote[]> => {
    const response = await apiClient.post<Quote[]>('/api/calculate', payload);
    return response.data;
  },

  getProviders: async (): Promise<Provider[]> => {
    const response = await apiClient.get<Provider[]>('/api/providers');
    return response.data;
  },

  updateProvider: async (id: number, data: UpdateProvider): Promise<Provider> => {
    const response = await apiClient.patch<Provider>(`/api/providers/${id}`, data);
    return response.data;
  }
};
