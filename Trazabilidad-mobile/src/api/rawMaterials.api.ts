import { apiClient } from './client';

// RawMaterialBase interface matching Spanish database schema (table: materia_prima_base)
export interface RawMaterialBase {
  material_id: number;
  categoria_id: number;
  unidad_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  cantidad_disponible: number;
  stock_minimo: number;
  stock_maximo?: number;
  imagen_url?: string;
  activo: boolean;
  category?: {
    categoria_id: number;
    nombre: string;
  };
  unit?: {
    unidad_id: number;
    codigo: string;
    nombre: string;
  };
}

// RawMaterial interface matching Spanish database schema (table: materia_prima)
export interface RawMaterial {
  materia_prima_id: number;
  material_id: number;
  proveedor_id: number;
  lote_proveedor?: string;
  numero_factura?: string;
  fecha_recepcion: string;
  fecha_vencimiento?: string;
  cantidad: number;
  cantidad_disponible: number;
  conformidad_recepcion?: boolean;
  observaciones?: string;
  materialBase?: RawMaterialBase;
  supplier?: {
    proveedor_id: number;
    razon_social: string;
    nombre_comercial?: string;
  };
}

// RawMaterialCategory interface matching Spanish database schema (table: categoria_materia_prima)
export interface RawMaterialCategory {
  categoria_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  activo: boolean;
}

export const rawMaterialsApi = {
  // Raw Material Bases
  getRawMaterialBases: async () => {
    try {
      const response = await apiClient.get('/raw-material-bases');
      console.log('getRawMaterialBases response:', response.data);
      const data = (response.data as any).data || response.data;
      return data;
    } catch (error: any) {
      console.log('getRawMaterialBases error:', error.response?.status, error.response?.data);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getRawMaterialBase: async (id: number) => {
    const response = await apiClient.get<RawMaterialBase>(`/raw-material-bases/${id}`);
    return response.data;
  },

  createRawMaterialBase: async (data: Partial<RawMaterialBase>) => {
    const response = await apiClient.post<RawMaterialBase>('/raw-material-bases', data);
    return response.data;
  },

  updateRawMaterialBase: async (id: number, data: Partial<RawMaterialBase>) => {
    const response = await apiClient.put<RawMaterialBase>(`/raw-material-bases/${id}`, data);
    return response.data;
  },

  deleteRawMaterialBase: async (id: number) => {
    const response = await apiClient.delete(`/raw-material-bases/${id}`);
    return response.data;
  },

  // Raw Materials
  getRawMaterials: async () => {
    try {
      console.log('Fetching raw materials...');
      const response = await apiClient.get('/raw-materials');
      console.log('Raw materials API response:', response.data);
      
      const materials = response.data.data || response.data;
      console.log('Raw materials received:', materials?.length || 0);
      return materials || [];
    } catch (error: any) {
      console.log('getRawMaterials error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getRawMaterial: async (id: number) => {
    const response = await apiClient.get<RawMaterial>(`/raw-materials/${id}`);
    return response.data;
  },

  createRawMaterial: async (data: Partial<RawMaterial>) => {
    const response = await apiClient.post<RawMaterial>('/raw-materials', data);
    return response.data;
  },

  updateRawMaterial: async (id: number, data: Partial<RawMaterial>) => {
    const response = await apiClient.put<RawMaterial>(`/raw-materials/${id}`, data);
    return response.data;
  },

  deleteRawMaterial: async (id: number) => {
    const response = await apiClient.delete(`/raw-materials/${id}`);
    return response.data;
  },

  // Raw Material Categories
  getRawMaterialCategories: async () => {
    try {
      const response = await apiClient.get('/raw-material-categories');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getRawMaterialCategories error:', error.response?.status);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getRawMaterialCategory: async (id: number) => {
    const response = await apiClient.get<RawMaterialCategory>(`/raw-material-categories/${id}`);
    return response.data;
  },

  createRawMaterialCategory: async (data: Partial<RawMaterialCategory>) => {
    const response = await apiClient.post<RawMaterialCategory>('/raw-material-categories', data);
    return response.data;
  },

  updateRawMaterialCategory: async (id: number, data: Partial<RawMaterialCategory>) => {
    const response = await apiClient.put<RawMaterialCategory>(`/raw-material-categories/${id}`, data);
    return response.data;
  },

  deleteRawMaterialCategory: async (id: number) => {
    const response = await apiClient.delete(`/raw-material-categories/${id}`);
    return response.data;
  },
};