import { apiClient } from './client';

// UnitOfMeasure interface matching Spanish database schema (table: unidad_medida)
export interface UnitOfMeasure {
  unidad_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  activo: boolean;
}

export const unitsApi = {
  getUnits: async () => {
    try {
      const response = await apiClient.get('/unit-of-measures');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getUnits error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getUnit: async (id: number) => {
    const response = await apiClient.get<UnitOfMeasure>(`/unit-of-measures/${id}`);
    return response.data;
  },

  createUnit: async (data: Partial<UnitOfMeasure>) => {
    const response = await apiClient.post<UnitOfMeasure>('/unit-of-measures', data);
    return response.data;
  },
};
