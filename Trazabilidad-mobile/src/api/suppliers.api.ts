import { apiClient } from './client';

// Supplier interface matching Spanish database schema (table: proveedor)
export interface Supplier {
  proveedor_id: number;
  razon_social: string;
  nombre_comercial?: string;
  nit?: string;
  contacto?: string;
  telefono?: string;
  email?: string;
  direccion?: string;
  activo: boolean;
}

export const suppliersApi = {
  getSuppliers: async () => {
    try {
      const response = await apiClient.get('/suppliers');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getSuppliers error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getSupplier: async (id: number) => {
    const response = await apiClient.get<Supplier>(`/suppliers/${id}`);
    return response.data;
  },

  createSupplier: async (data: Partial<Supplier>) => {
    const response = await apiClient.post<Supplier>('/suppliers', data);
    return response.data;
  },

  updateSupplier: async (id: number, data: Partial<Supplier>) => {
    const response = await apiClient.put<Supplier>(`/suppliers/${id}`, data);
    return response.data;
  },

  deleteSupplier: async (id: number) => {
    const response = await apiClient.delete(`/suppliers/${id}`);
    return response.data;
  },
};
