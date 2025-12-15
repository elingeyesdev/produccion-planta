import { apiClient } from './client';

// Machine interface (re-exported from machines.api.ts)
export interface Machine {
  maquina_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  imagen_url?: string;
  activo: boolean;
}

// ProcessMachineVariable interface matching Spanish database schema
export interface ProcessMachineVariable {
  variable_id?: number;
  proceso_maquina_id?: number;
  variable_estandar_id?: number;
  // English aliases for frontend use
  standard_variable_id?: number;
  valor_minimo?: number;
  valor_maximo?: number;
  valor_objetivo?: number;
  // English aliases
  min_value?: number;
  max_value?: number;
  target_value?: number;
  obligatorio?: boolean;
  mandatory?: boolean;
  standardVariable?: {
    variable_id: number;
    codigo: string;
    nombre: string;
    // English aliases
    code?: string;
    name?: string;
    unidad?: string;
    unit?: string;
  };
}

// ProcessMachine interface with both Spanish and English field names
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
  machine?: Machine;
  variables?: ProcessMachineVariable[];
}

// Process interface matching Spanish database schema (table: proceso)
export interface Process {
  proceso_id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  activo: boolean;
  // English aliases
  process_id?: number;
  code?: string;
  name?: string;
  description?: string;
  active?: boolean;
  processMachines?: ProcessMachine[];
  process_machines?: ProcessMachine[];
}

export interface CreateProcessData {
  nombre?: string;
  name?: string;
  descripcion?: string;
  description?: string;
  activo?: boolean;
  active?: boolean;
  process_machines?: Omit<ProcessMachine, 'proceso_maquina_id' | 'proceso_id' | 'machine'>[];
}

export const processesApi = {
  getProcesses: async (includeMachines: boolean = false) => {
    try {
      const url = includeMachines ? '/processes?include=machines' : '/processes';
      const response = await apiClient.get(url);
      const data = response.data.data || response.data;
      return data;
    } catch (error: any) {
      console.error('getProcesses error:', error);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getProcess: async (id: number): Promise<Process> => {
    const response = await apiClient.get<Process>(`/processes/${id}`);
    return response.data;
  },

  createProcess: async (data: CreateProcessData): Promise<Process> => {
    const response = await apiClient.post<Process>('/processes', data);
    return response.data;
  },

  updateProcess: async (id: number, data: Partial<CreateProcessData>): Promise<Process> => {
    const response = await apiClient.put<Process>(`/processes/${id}`, data);
    return response.data;
  },

  deleteProcess: async (id: number): Promise<void> => {
    await apiClient.delete(`/processes/${id}`);
  },
};
