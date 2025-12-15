import { apiClient } from './client';

// ProductionBatch interface matching Spanish database schema (table: lote_produccion)
export interface ProductionBatch {
  lote_id: number;
  pedido_id: number;
  codigo_lote: string;
  nombre: string;
  fecha_creacion: string;
  hora_inicio?: string;
  hora_fin?: string;
  cantidad_objetivo?: number;
  cantidad_producida?: number;
  observaciones?: string;
  order?: {
    pedido_id: number;
    numero_pedido: string;
    descripcion?: string;
    customer?: {
      cliente_id: number;
      razon_social: string;
      nombre_comercial?: string;
    };
  };
  final_evaluation?: {
    evaluacion_id: number;
    reason: string;
    fecha_evaluacion?: string;
  };
}

// BatchRawMaterial interface matching Spanish database schema (table: lote_materia_prima)
export interface BatchRawMaterial {
  lote_material_id: number;
  lote_id: number;
  materia_prima_id: number;
  cantidad_planificada: number;
  cantidad_usada?: number;
  raw_material?: {
    materia_prima_id: number;
    lote_proveedor?: string;
    materialBase?: {
      nombre: string;
    };
  };
}

export interface ProcessTransformation {
  lote_id: number;
  proceso_maquina_id: number;
  operador_id: number;
  hora_inicio: string;
  hora_fin?: string;
  variables: Record<string, any>;
  notas?: string;
}

export const productionApi = {
  // Production Batches
  getProductionBatches: async () => {
    try {
      console.log('Calling API:', apiClient.defaults.baseURL + '/production-batches');
      const response = await apiClient.get('/production-batches');
      console.log('API Response status:', response.status);
      const batches = response.data.data || response.data;
      console.log('Extracted batches:', batches?.length || 0);
      return batches;
    } catch (error: any) {
      console.log('Production batches API error:', error.response?.status, error.response?.data);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getProductionBatch: async (id: number) => {
    const response = await apiClient.get<ProductionBatch>(`/production-batches/${id}`);
    return response.data;
  },

  createProductionBatch: async (data: Partial<ProductionBatch>) => {
    const response = await apiClient.post<ProductionBatch>('/production-batches', data);
    return response.data;
  },

  updateProductionBatch: async (id: number, data: Partial<ProductionBatch>) => {
    const response = await apiClient.put<ProductionBatch>(`/production-batches/${id}`, data);
    return response.data;
  },

  deleteProductionBatch: async (id: number) => {
    const response = await apiClient.delete(`/production-batches/${id}`);
    return response.data;
  },

  // Batch Raw Materials
  getBatchRawMaterials: async () => {
    const response = await apiClient.get<BatchRawMaterial[]>('/batch-raw-materials');
    return response.data;
  },

  getBatchRawMaterial: async (id: number) => {
    const response = await apiClient.get<BatchRawMaterial>(`/batch-raw-materials/${id}`);
    return response.data;
  },

  createBatchRawMaterial: async (data: Partial<BatchRawMaterial>) => {
    const response = await apiClient.post<BatchRawMaterial>('/batch-raw-materials', data);
    return response.data;
  },

  updateBatchRawMaterial: async (id: number, data: Partial<BatchRawMaterial>) => {
    const response = await apiClient.put<BatchRawMaterial>(`/batch-raw-materials/${id}`, data);
    return response.data;
  },

  deleteBatchRawMaterial: async (id: number) => {
    const response = await apiClient.delete(`/batch-raw-materials/${id}`);
    return response.data;
  },

  // Process Transformation
  registerProcessTransformation: async (batchId: number, processMachineId: number, data: Partial<ProcessTransformation>) => {
    const response = await apiClient.post(`/process-transformation/batch/${batchId}/machine/${processMachineId}`, data);
    return response.data;
  },

  getProcessTransformationForm: async (batchId: number, processMachineId: number) => {
    const response = await apiClient.get(`/process-transformation/batch/${batchId}/machine/${processMachineId}`);
    return response.data;
  },

  getBatchProcess: async (batchId: number) => {
    const response = await apiClient.get(`/process-transformation/batch/${batchId}`);
    return response.data;
  },

  // Process Evaluation
  finalizeProcessEvaluation: async (batchId: number, data: any) => {
    const response = await apiClient.post(`/process-evaluation/finalize/${batchId}`, data);
    return response.data;
  },

  getProcessEvaluationLog: async (batchId: number) => {
    const response = await apiClient.get(`/process-evaluation/log/${batchId}`);
    return response.data;
  },

  // Legacy compatibility methods (map Spanish to English for existing UI components)
  getBatches: async () => {
    try {
      console.log('Fetching production batches...');
      const batches = await productionApi.getProductionBatches();
      
      return batches.map((batch: any) => {
        // The API now returns English field names from ProductionBatchResource
        // batch_id, name, batch_code, status, product_name, etc.
        
        // Determine status - use API-provided status or fallback to evaluation check
        let status = batch.status || 'pending';
        if (!batch.status && (batch.final_evaluation || batch.finalEvaluation)) {
          const evaluation = batch.final_evaluation || batch.finalEvaluation;
          const reason = evaluation.reason || evaluation.razon || '';
          status = reason.toLowerCase().includes('falló') || reason.toLowerCase().includes('fallo') 
            ? 'not_certified' 
            : 'certified';
        }
        
        // Map both English (from resource) and Spanish (fallback) field names
        return {
          batch_id: batch.batch_id || batch.lote_id,
          name: batch.name || batch.nombre,
          batch_code: batch.batch_code || batch.codigo_lote,
          product_name: batch.product_name || batch.order?.description || batch.order?.descripcion || batch.name || batch.nombre || 'Unknown Product',
          status: status,
          start_date: batch.start_date || batch.creation_date || batch.hora_inicio || batch.fecha_creacion,
          end_date: batch.end_time || batch.hora_fin,
          quantity: parseFloat(String(batch.quantity || batch.target_quantity || batch.cantidad_objetivo || 0)),
          operator_name: batch.operator_name || 'Production Team',
        };
      });
    } catch (error: any) {
      console.log('getBatches error:', error.response?.status, error.response?.data || error.message);
      throw error;
    }
  },

  getBatch: async (id: number) => {
    try {
      console.log('Fetching batch with id:', id);
      const batch: any = await productionApi.getProductionBatch(id);
      console.log('Batch data received:', batch);
      
      // Determine status - use API-provided status or fallback to evaluation check
      let status = batch.status || 'pending';
      if (!batch.status && (batch.final_evaluation || batch.finalEvaluation)) {
        const evaluation = batch.final_evaluation || batch.finalEvaluation;
        const reason = evaluation.reason || evaluation.razon || '';
        status = reason.toLowerCase().includes('falló') || reason.toLowerCase().includes('fallo') 
          ? 'not_certified' 
          : 'certified';
      }
      
      // Map both English (from resource) and Spanish (fallback) field names
      return {
        batch_id: batch.batch_id || batch.lote_id,
        name: batch.name || batch.nombre,
        batch_code: batch.batch_code || batch.codigo_lote,
        product_name: batch.product_name || batch.order?.description || batch.order?.descripcion || batch.name || batch.nombre || 'Unknown Product',
        status: status,
        start_date: batch.start_date || batch.creation_date || batch.hora_inicio || batch.fecha_creacion,
        end_date: batch.end_time || batch.hora_fin,
        quantity: parseFloat(String(batch.quantity || batch.target_quantity || batch.cantidad_objetivo || 0)),
        operator_name: batch.operator_name || 'Production Team',
        final_evaluation: batch.final_evaluation || batch.finalEvaluation || null,
        order: batch.order || null,
      };
    } catch (error: any) {
      console.log('getBatch error:', error.response?.status, error.response?.data || error.message);
      throw error;
    }
  },

  createBatch: async (data: any) => {
    console.log('createBatch called with:', data);
    // Map to Spanish field names if needed
    const payload = {
      pedido_id: data.order_id || data.pedido_id,
      nombre: data.name || data.nombre,
      cantidad_objetivo: data.target_quantity || data.cantidad_objetivo,
      observaciones: data.observations || data.observaciones,
      raw_materials: data.raw_materials,
    };
    const response = await apiClient.post('/production-batches', payload);
    return response.data;
  },

  deleteBatch: async (id: number) => {
    try {
      console.log('Deleting batch with id:', id);
      const response = await productionApi.deleteProductionBatch(id);
      console.log('Delete response:', response);
      return response;
    } catch (error: any) {
      console.log('deleteBatch error:', error.response?.status, error.response?.data || error.message);
      throw error;
    }
  },
};
