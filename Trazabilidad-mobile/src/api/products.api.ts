import { apiClient } from './client';

// Product interface matching Spanish database schema
export interface Product {
  producto_id: number;
  codigo: string;
  nombre: string;
  tipo: 'organico' | 'marca_univalle' | 'comestibles';
  peso?: number;
  precio_unitario?: number;
  unidad_id?: number;
  descripcion?: string;
  activo: boolean;
  unit?: {
    unidad_id: number;
    codigo: string;
    nombre: string;
  };
}

export const productsApi = {
  getProducts: async () => {
    try {
      const response = await apiClient.get('/products');
      // Handle potential pagination or direct array response
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getProducts error:', error.response?.status, error.response?.data);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getProduct: async (id: number) => {
    const response = await apiClient.get<Product>(`/products/${id}`);
    return response.data;
  },

  createProduct: async (data: Partial<Product>) => {
    const response = await apiClient.post<Product>('/products', data);
    return response.data;
  },

  updateProduct: async (id: number, data: Partial<Product>) => {
    const response = await apiClient.put<Product>(`/products/${id}`, data);
    return response.data;
  },

  deleteProduct: async (id: number) => {
    const response = await apiClient.delete(`/products/${id}`);
    return response.data;
  },
};
