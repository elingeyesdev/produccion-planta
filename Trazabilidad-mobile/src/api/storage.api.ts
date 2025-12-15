import { apiClient } from './client';

// Storage interface matching Spanish database schema (table: almacenaje)
export interface Storage {
  almacenaje_id: number;
  lote_id: number;
  ubicacion: string;
  condicion?: string;
  cantidad: number;
  observaciones?: string;
  latitud_recojo?: number;
  longitud_recojo?: number;
  direccion_recojo?: string;
  referencia_recojo?: string;
  fecha_almacenaje?: string;
  fecha_retiro?: string;
  batch?: {
    lote_id: number;
    codigo_lote: string;
    nombre: string;
  };
}

// MaterialMovementLog interface matching Spanish database schema (table: log_movimiento_material)
export interface MaterialMovementLog {
  log_movimiento_id: number;
  material_id: number;
  tipo_movimiento_id: number;
  cantidad: number;
  fecha_movimiento: string;
  operador_id: number;
  observaciones?: string;
  raw_material?: {
    material_id: number;
    nombre: string;
  };
  movement_type?: {
    tipo_movimiento_id: number;
    nombre: string;
    descripcion?: string;
  };
  operator?: {
    operador_id: number;
    nombre: string;
    apellido: string;
    usuario: string;
  };
}

// MaterialRequest interface matching Spanish database schema
export interface MaterialRequest {
  solicitud_id: number;
  operador_id: number;
  material_id: number;
  cantidad_solicitada: number;
  unidad_id: number;
  estado: string;
  fecha_solicitud: string;
  observaciones?: string;
  operator?: {
    operador_id: number;
    nombre: string;
    apellido: string;
    usuario: string;
  };
  material?: {
    material_id: number;
    nombre: string;
    categoria?: {
      nombre: string;
    };
  };
  unit?: {
    unidad_id: number;
    nombre: string;
    codigo: string;
  };
}

// MaterialRequestDetail interface
export interface MaterialRequestDetail {
  detalle_id: number;
  solicitud_id: number;
  materia_prima_id?: number;
  cantidad_entregada: number;
  fecha_entrega?: string;
  observaciones?: string;
  material_request?: MaterialRequest;
  raw_material?: {
    materia_prima_id: number;
    lote_proveedor: string;
    materialBase?: {
      nombre: string;
    };
  };
}

export const storageApi = {
  // Storage
  getStorages: async () => {
    try {
      const response = await apiClient.get('/storages');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getStorages error:', error.response?.status, error.response?.data);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getStorage: async (id: number) => {
    const response = await apiClient.get<Storage>(`/storages/${id}`);
    return response.data;
  },

  getStoragesByBatch: async (batchId: number) => {
    const response = await apiClient.get<Storage[]>(`/storages/batch/${batchId}`);
    return response.data;
  },

  createStorage: async (data: Partial<Storage>) => {
    const response = await apiClient.post<Storage>('/storages', data);
    return response.data;
  },

  updateStorage: async (id: number, data: Partial<Storage>) => {
    const response = await apiClient.put<Storage>(`/storages/${id}`, data);
    return response.data;
  },

  deleteStorage: async (id: number) => {
    const response = await apiClient.delete(`/storages/${id}`);
    return response.data;
  },

  // Material Movement Logs
  getMaterialMovementLogs: async () => {
    try {
      const response = await apiClient.get('/material-movement-logs');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getMaterialMovementLogs error:', error.response?.status);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getMaterialMovementLog: async (id: number) => {
    const response = await apiClient.get<MaterialMovementLog>(`/material-movement-logs/${id}`);
    return response.data;
  },

  getMaterialMovementLogsByMaterial: async (materialId: number) => {
    const response = await apiClient.get<MaterialMovementLog[]>(`/material-movement-logs/material/${materialId}`);
    return response.data;
  },

  createMaterialMovementLog: async (data: Partial<MaterialMovementLog>) => {
    const response = await apiClient.post<MaterialMovementLog>('/material-movement-logs', data);
    return response.data;
  },

  updateMaterialMovementLog: async (id: number, data: Partial<MaterialMovementLog>) => {
    const response = await apiClient.put<MaterialMovementLog>(`/material-movement-logs/${id}`, data);
    return response.data;
  },

  deleteMaterialMovementLog: async (id: number) => {
    const response = await apiClient.delete(`/material-movement-logs/${id}`);
    return response.data;
  },

  // Material Requests
  getMaterialRequests: async () => {
    try {
      const response = await apiClient.get('/material-requests');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getMaterialRequests error:', error.response?.status);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getMaterialRequest: async (id: number) => {
    const response = await apiClient.get<MaterialRequest>(`/material-requests/${id}`);
    return response.data;
  },

  createMaterialRequest: async (data: Partial<MaterialRequest>) => {
    const response = await apiClient.post<MaterialRequest>('/material-requests', data);
    return response.data;
  },

  updateMaterialRequest: async (id: number, data: Partial<MaterialRequest>) => {
    const response = await apiClient.put<MaterialRequest>(`/material-requests/${id}`, data);
    return response.data;
  },

  deleteMaterialRequest: async (id: number) => {
    const response = await apiClient.delete(`/material-requests/${id}`);
    return response.data;
  },

  // Material Request Details
  getMaterialRequestDetails: async () => {
    try {
      const response = await apiClient.get('/material-request-details');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getMaterialRequestDetails error:', error.response?.status);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getMaterialRequestDetail: async (id: number) => {
    const response = await apiClient.get<MaterialRequestDetail>(`/material-request-details/${id}`);
    return response.data;
  },

  createMaterialRequestDetail: async (data: Partial<MaterialRequestDetail>) => {
    const response = await apiClient.post<MaterialRequestDetail>('/material-request-details', data);
    return response.data;
  },

  updateMaterialRequestDetail: async (id: number, data: Partial<MaterialRequestDetail>) => {
    const response = await apiClient.put<MaterialRequestDetail>(`/material-request-details/${id}`, data);
    return response.data;
  },

  deleteMaterialRequestDetail: async (id: number) => {
    const response = await apiClient.delete(`/material-request-details/${id}`);
    return response.data;
  },
};