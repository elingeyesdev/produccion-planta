import { apiClient } from './client';

// Machine interface matching Spanish database schema (table: maquina)
// Includes both Spanish field names and English aliases
export interface Machine {
  maquina_id: number;
  machine_id?: number;
  codigo: string;
  code?: string;
  nombre: string;
  name?: string;
  descripcion?: string;
  description?: string;
  imagen_url?: string;
  image_url?: string;
  activo: boolean;
  active?: boolean;
}

export const machinesApi = {
  getMachines: async () => {
    try {
      const response = await apiClient.get('/machines');
      const data = response.data.data || response.data;
      return data;
    } catch (error: any) {
      console.error('getMachines error:', error);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getMachine: async (id: number) => {
    const response = await apiClient.get<Machine>(`/machines/${id}`);
    return response.data;
  },

  createMachine: async (data: Partial<Machine>) => {
    const response = await apiClient.post<Machine>('/machines', data);
    return response.data;
  },

  updateMachine: async (id: number, data: Partial<Machine>) => {
    const response = await apiClient.put<Machine>(`/machines/${id}`, data);
    return response.data;
  },

  deleteMachine: async (id: number) => {
    const response = await apiClient.delete(`/machines/${id}`);
    return response.data;
  },
};
