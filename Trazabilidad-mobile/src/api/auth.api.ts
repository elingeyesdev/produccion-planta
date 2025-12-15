import { apiClient } from './client';

export interface LoginRequest {
  username: string;
  password: string;
}

export interface RegisterRequest {
  nombre: string;
  apellido: string;
  usuario: string;
  email: string;
  password: string;
}

// Response matches API controller output structure
export interface AuthResponse {
  token: string;
  operator: {
    operator_id: number;
    first_name: string;
    last_name: string;
    username: string;
    email: string;
    role?: {
      role_id?: string;
      name?: string;
    };
  };
}

// Spanish field names for the operator model
export interface Operador {
  operador_id: number;
  nombre: string;
  apellido: string;
  usuario: string;
  email?: string;
  activo: boolean;
}

export const authApi = {
  login: async (data: LoginRequest) => {
    console.log('Login attempt:', { username: data.username, url: apiClient.defaults.baseURL + '/auth/login' });
    try {
      const response = await apiClient.post<AuthResponse>('/auth/login', data);
      console.log('Login success:', response.status);
      return response.data;
    } catch (error: any) {
      // Handle different types of errors with detailed logging
      console.log('=== LOGIN ERROR DETAILS ===');
      console.log('Error object:', error);
      console.log('Error type:', error.constructor.name);
      
      if (error.response) {
        // Server responded with error status
        console.log('Login error - Server responded:', {
          status: error.response.status,
          statusText: error.response.statusText,
          data: error.response.data,
          headers: error.response.headers,
          message: error.response.data?.message || error.response.data?.error || 'Error del servidor'
        });
      } else if (error.request) {
        // Request was made but no response received
        console.log('Login error - No response received:', {
          message: error.message || 'Error de conexión',
          code: error.code,
          errno: error.errno,
          syscall: error.syscall,
          address: error.address,
          port: error.port,
          url: apiClient.defaults.baseURL + '/auth/login',
          timeout: error.code === 'ECONNABORTED' ? 'Request timeout' : 'No timeout'
        });
      } else {
        // Something else happened
        console.log('Login error - Other:', {
          message: error.message || 'Error desconocido',
          stack: error.stack
        });
      }
      console.log('=== END ERROR DETAILS ===');
      throw error;
    }
  },

  register: async (data: RegisterRequest) => {
    // Map to expected backend fields
    const payload = {
      first_name: data.nombre,
      last_name: data.apellido,
      username: data.usuario,
      email: data.email,
      password: data.password,
    };
    const response = await apiClient.post('/auth/register', payload);
    return response.data;
  },

  getCurrentUser: async () => {
    const response = await apiClient.get('/auth/me');
    return response.data;
  },

  logout: async () => {
    const response = await apiClient.post('/auth/logout');
    return response.data;
  },
};
