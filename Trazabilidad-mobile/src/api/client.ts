import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// TODO: Make this configurable via env vars
const API_BASE_URL = 'http://10.26.3.97:8001/api'; 

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - Add token to requests
apiClient.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor - Handle 401 errors
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem('auth_token');
      // We can't easily navigate here without a navigation ref or store callback
      // The store will handle the state change when it detects the token is gone or invalid
    }
    return Promise.reject(error);
  }
);
