import { reactive, computed } from 'vue';
import { authApi } from '../api/auth';
import apiClient from '../api/client';
import { getCookie, setCookie, deleteCookie } from '../utils/cookie';
import router from '../router';

interface AuthState {
  token: string | null;
  user: {
    username: string;
    roles: string[];
  } | null;
}

const state = reactive<AuthState>({
  token: getCookie('jwt_token'),
  user: null,
});

export const useAuth = () => {
  const isAuthenticated = computed(() => !!state.token);

  const login = async (username: string, password: string) => {
    try {
      const data = await authApi.login({ username, password });
      const token = data.token;
      state.token = token;
      setCookie('jwt_token', token, 1);
      
      return true;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      if (state.token) {
        await authApi.logout();
      }
    } catch (error) {
      console.warn('Backend logout notification failed', error);
    } finally {
      state.token = null;
      state.user = null;
      deleteCookie('jwt_token');
    }
  };

  const initializeAuth = () => {
    // Add interceptor to handle expired tokens
    apiClient.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          state.token = null;
          state.user = null;
          deleteCookie('jwt_token');
          router.push('/admin/login');
        }
        return Promise.reject(error);
      }
    );
  };

  return {
    state,
    isAuthenticated,
    login,
    logout,
    initializeAuth,
  };
};
