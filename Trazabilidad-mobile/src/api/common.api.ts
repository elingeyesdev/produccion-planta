import { apiClient } from './client';

export interface UnitOfMeasure {
  unit_of_measure_id: number;
  name: string;
  abbreviation: string;
  created_at: string;
  updated_at: string;
}

export interface Status {
  status_id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface MovementType {
  movement_type_id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface OperatorRole {
  operator_role_id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface Supplier {
  supplier_id: number;
  name: string;
  contact_person?: string;
  email?: string;
  phone?: string;
  address?: string;
  created_at: string;
  updated_at: string;
}

export interface StandardVariable {
  standard_variable_id: number;
  name: string;
  data_type: string;
  min_value?: number;
  max_value?: number;
  unit?: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface Machine {
  machine_id: number;
  name: string;
  model?: string;
  manufacturer?: string;
  serial_number?: string;
  installation_date?: string;
  status_id: number;
  created_at: string;
  updated_at: string;
  status?: Status;
}

export interface Process {
  process_id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface Operator {
  operator_id: number;
  first_name: string;
  last_name: string;
  username: string;
  email: string;
  operator_role_id: number;
  created_at: string;
  updated_at: string;
  role?: OperatorRole;
}

export const commonApi = {
  // Units of Measure
  getUnitsOfMeasure: async () => {
    const response = await apiClient.get<UnitOfMeasure[]>('/unit-of-measures');
    return response.data;
  },

  getUnitOfMeasure: async (id: number) => {
    const response = await apiClient.get<UnitOfMeasure>(`/unit-of-measures/${id}`);
    return response.data;
  },

  // Statuses
  getStatuses: async () => {
    const response = await apiClient.get<Status[]>('/statuses');
    return response.data;
  },

  getStatus: async (id: number) => {
    const response = await apiClient.get<Status>(`/statuses/${id}`);
    return response.data;
  },

  // Movement Types
  getMovementTypes: async () => {
    const response = await apiClient.get<MovementType[]>('/movement-types');
    return response.data;
  },

  // Operator Roles
  getOperatorRoles: async () => {
    const response = await apiClient.get<OperatorRole[]>('/operator-roles');
    return response.data;
  },

  // Suppliers
  getSuppliers: async () => {
    const response = await apiClient.get<Supplier[]>('/suppliers');
    return response.data;
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

  // Standard Variables
  getStandardVariables: async () => {
    const response = await apiClient.get<StandardVariable[]>('/standard-variables');
    return response.data;
  },

  // Machines
  getMachines: async () => {
    const response = await apiClient.get<Machine[]>('/machines');
    return response.data;
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

  // Processes
  getProcesses: async () => {
    const response = await apiClient.get<Process[]>('/processes');
    return response.data;
  },

  getProcess: async (id: number) => {
    const response = await apiClient.get<Process>(`/processes/${id}`);
    return response.data;
  },

  createProcess: async (data: Partial<Process>) => {
    const response = await apiClient.post<Process>('/processes', data);
    return response.data;
  },

  updateProcess: async (id: number, data: Partial<Process>) => {
    const response = await apiClient.put<Process>(`/processes/${id}`, data);
    return response.data;
  },

  deleteProcess: async (id: number) => {
    const response = await apiClient.delete(`/processes/${id}`);
    return response.data;
  },

  // Operators
  getOperators: async () => {
    const response = await apiClient.get<Operator[]>('/operators');
    return response.data;
  },

  getOperator: async (id: number) => {
    const response = await apiClient.get<Operator>(`/operators/${id}`);
    return response.data;
  },

  createOperator: async (data: Partial<Operator>) => {
    const response = await apiClient.post<Operator>('/operators', data);
    return response.data;
  },

  updateOperator: async (id: number, data: Partial<Operator>) => {
    const response = await apiClient.put<Operator>(`/operators/${id}`, data);
    return response.data;
  },

  deleteOperator: async (id: number) => {
    const response = await apiClient.delete(`/operators/${id}`);
    return response.data;
  },
};