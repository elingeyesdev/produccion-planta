import { create } from 'zustand';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authApi } from '../api/auth.api';

interface User {
  operator_id: number;
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  role: {
    role_id: string;
    name: string;
  };
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  loadToken: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  login: async (username, password) => {
    try {
      const response = await authApi.login({ username, password });
      await AsyncStorage.setItem('auth_token', response.token);
      set({
        user: response.operator,
        token: response.token,
        isAuthenticated: true,
      });
    } catch (error) {
      throw error;
    }
  },

  logout: async () => {
    try {
      await authApi.logout();
    } catch (error) {
      // Continue with logout even if API call fails
    } finally {
      await AsyncStorage.removeItem('auth_token');
      set({
        user: null,
        token: null,
        isAuthenticated: false,
      });
    }
  },

  loadToken: async () => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        // Optionally verify token with getCurrentUser
        // const user = await authApi.getCurrentUser();
        set({
          // user, 
          token,
          isAuthenticated: true,
          isLoading: false,
        });
      } else {
        set({ isLoading: false });
      }
    } catch (error) {
      await AsyncStorage.removeItem('auth_token');
      set({ isLoading: false });
    }
  },
}));
