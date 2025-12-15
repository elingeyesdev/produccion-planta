import { apiClient } from './client';

// ProcessMachineVariable interface matching Spanish database schema
// Includes both Spanish and English field names for frontend compatibility
export interface ProcessMachineVariable {
  variable_id?: number;
  proceso_maquina_id?: number;
  variable_estandar_id?: number;
  standard_variable_id?: number;
  valor_minimo?: number;
  min_value?: number;
  valor_maximo?: number;
  max_value?: number;
  valor_objetivo?: number;
  target_value?: number;
  obligatorio?: boolean;
  mandatory?: boolean;
  standardVariable?: {
    variable_id: number;
    codigo: string;
    code?: string;
    nombre: string;
    name?: string;
    unidad?: string;
    unit?: string;
  };
}

// ProcessMachine interface matching Spanish database schema (table: proceso_maquina)
// Includes both Spanish and English field names for frontend compatibility
export interface ProcessMachine {
  proceso_maquina_id?: number;
  process_machine_id?: number;
  proceso_id?: number;
  process_id?: number;
  maquina_id?: number;
  machine_id?: number;
  orden_paso?: number;
  step_order?: number;
  nombre?: string;
  name?: string;
  descripcion?: string;
  description?: string;
  tiempo_estimado?: number;
  estimated_time?: number;
  machine?: {
    maquina_id: number;
    machine_id?: number;
    nombre: string;
    name?: string;
    codigo: string;
    code?: string;
  };
  variables?: ProcessMachineVariable[];
}

export interface CertificationLog {
  machines: {
    orden_paso: number;
    nombre_maquina: string;
    variables_registradas: Record<string, number>;
    cumple_estandar: boolean;
    fecha_registro: string;
  }[];
  final_result: {
    estado: string;
    razon: string;
    fecha_evaluacion: string;
    inspector: string;
  };
}

// ProcessMachineRecord interface matching Spanish database schema (table: registro_proceso_maquina)
export interface ProcessMachineRecord {
  registro_id: number;
  lote_id: number;
  proceso_maquina_id: number;
  operador_id: number;
  variables_registradas: Record<string, number>;
  cumple_estandar: boolean;
  observaciones?: string;
  fecha_registro: string;
}

export interface CreateProcessMachineRecordData {
  lote_id?: number;
  batch_id?: number;
  proceso_maquina_id?: number;
  process_machine_id?: number;
  variables_registradas?: Record<string, number>;
  entered_variables?: Record<string, number>;
  observaciones?: string;
  observations?: string;
}

export const certificationApi = {
  // Get batches pending certification
  getPendingCertification: async () => {
    try {
      const response = await apiClient.get('/batches/pending-certification');
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getPendingCertification error:', error.response?.status);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  // Assign process to batch
  assignProcess: async (batchId: number, processId: number) => {
    const response = await apiClient.post(`/batches/${batchId}/assign-process`, {
      proceso_id: processId,
    });
    return response.data;
  },

  // Get process machines for a batch
  getProcessMachines: async (batchId: number): Promise<{
    process_machines: ProcessMachine[];
    completed_records: number[];
    proceso_id: number;
  }> => {
    const response = await apiClient.get(`/batches/${batchId}/process-machines`);
    return response.data;
  },

  // Record variables for a machine step
  recordVariables: async (data: CreateProcessMachineRecordData): Promise<ProcessMachineRecord> => {
    const batchId = data.lote_id || data.batch_id;
    const processMachineId = data.proceso_maquina_id || data.process_machine_id;
    
    // Use the ProcessTransformationController endpoint which has the validation logic
    const response = await apiClient.post(
      `/process-transformation/batch/${batchId}/machine/${processMachineId}`, 
      data
    );
    return response.data;
  },

  // Finalize certification
  finalizeCertification: async (batchId: number, observations?: string) => {
    const response = await apiClient.post(`/batches/${batchId}/finalize-certification`, {
      observaciones: observations,
    });
    return response.data;
  },

  // Get certification log
  getCertificationLog: async (batchId: number): Promise<CertificationLog> => {
    const response = await apiClient.get(`/batches/${batchId}/certification-log`);
    return response.data;
  },
};
