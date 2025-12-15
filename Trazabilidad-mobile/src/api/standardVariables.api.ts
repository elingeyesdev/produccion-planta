import { apiClient } from './client';

// StandardVariable interface matching Spanish database schema (table: variable_estandar)
// Includes both Spanish field names and English aliases
export interface StandardVariable {
  variable_id: number;
  codigo: string;
  code?: string;
  nombre: string;
  name?: string;
  unidad?: string;
  unit?: string;
  descripcion?: string;
  description?: string;
  activo: boolean;
  active?: boolean;
}

export interface CreateStandardVariableData {
  nombre: string;
  unidad?: string;
  descripcion?: string;
  activo?: boolean;
}

export const standardVariablesApi = {
  getStandardVariables: async (): Promise<StandardVariable[]> => {
    try {
      const response = await apiClient.get('/standard-variables');
      const data = response.data.data || response.data;
      return data;
    } catch (error: any) {
      console.error('getStandardVariables error:', error);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getStandardVariable: async (id: number): Promise<StandardVariable> => {
    const response = await apiClient.get<StandardVariable>(`/standard-variables/${id}`);
    return response.data;
  },

  createStandardVariable: async (data: CreateStandardVariableData): Promise<StandardVariable> => {
    const response = await apiClient.post<StandardVariable>('/standard-variables', data);
    return response.data;
  },

  updateStandardVariable: async (id: number, data: Partial<CreateStandardVariableData>): Promise<StandardVariable> => {
    const response = await apiClient.put<StandardVariable>(`/standard-variables/${id}`, data);
    return response.data;
  },

  deleteStandardVariable: async (id: number): Promise<void> => {
    await apiClient.delete(`/standard-variables/${id}`);
  },
};
