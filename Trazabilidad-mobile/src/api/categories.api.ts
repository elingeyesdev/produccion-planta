import { apiClient } from './client';

// Category interface matching Spanish database schema (table: categoria_materia_prima)
export interface Category {
  categoria_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  activo: boolean;
}

export const categoriesApi = {
  getCategories: async () => {
    try {
      const response = await apiClient.get('/raw-material-categories');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getCategories error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getCategory: async (id: number) => {
    const response = await apiClient.get<Category>(`/raw-material-categories/${id}`);
    return response.data;
  },

  createCategory: async (data: Partial<Category>) => {
    const response = await apiClient.post<Category>('/raw-material-categories', data);
    return response.data;
  },
};
