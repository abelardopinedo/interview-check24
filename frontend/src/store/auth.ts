import { reactive, computed } from 'vue';
import axios from 'axios';

interface AuthState {
  token: string | null;
  user: any | null;
}

const getCookie = (name: string): string | null => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift() || null;
  return null;
};

const setCookie = (name: string, value: string, days: number) => {
  const date = new Date();
  date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
  const expires = `expires=${date.toUTCString()}`;
  document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
};

const deleteCookie = (name: string) => {
  document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;SameSite=Lax`;
};

const state = reactive<AuthState>({
  token: getCookie('jwt_token'),
  user: null,
});

export const useAuth = () => {
  const isAuthenticated = computed(() => !!state.token);

  const login = async (username: string, password: string) => {
    try {
      const response = await axios.post('/api/login_check', {
        username,
        password,
      });
      const token = response.data.token;
      state.token = token;
      setCookie('jwt_token', token, 1);
      
      // Set axios default header
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      return true;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      if (state.token) {
        await axios.post('/api/logout');
      }
    } catch (error) {
      console.warn('Backend logout notification failed', error);
    } finally {
      state.token = null;
      state.user = null;
      deleteCookie('jwt_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  };

  const initializeAuth = () => {
    if (state.token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`;
    }
  };

  return {
    state,
    isAuthenticated,
    login,
    logout,
    initializeAuth,
  };
};
