import { apiClient } from './client';

// Customer interface matching Spanish database schema (table: cliente)
export interface Customer {
  cliente_id: number;
  razon_social: string;
  nombre_comercial?: string;
  nit?: string;
  direccion?: string;
  telefono?: string;
  email?: string;
  contacto?: string;
  activo?: boolean;
}

// CustomerOrder interface matching Spanish database schema (table: pedido_cliente)
export interface CustomerOrder {
  pedido_id: number;
  cliente_id: number;
  numero_pedido: string;
  nombre: string;
  estado: 'pendiente' | 'aprobado' | 'rechazado' | 'cancelado' | 'en_produccion' | 'completado';
  fecha_creacion?: string;
  fecha_entrega?: string;
  descripcion?: string;
  observaciones?: string;
  editable_hasta?: string;
  aprobado_en?: string;
  aprobado_por?: number;
  razon_rechazo?: string;
  customer?: Customer;
  orderProducts?: OrderProduct[];
}

// OrderProduct interface matching Spanish database schema (table: producto_pedido)
export interface OrderProduct {
  producto_pedido_id: number;
  pedido_id: number;
  producto_id: number;
  cantidad: number;
  precio?: number;
  estado: 'pendiente' | 'aprobado' | 'rechazado';
  razon_rechazo?: string;
  observaciones?: string;
  product?: {
    producto_id: number;
    nombre: string;
    codigo: string;
    precio_unitario?: number;
    unit?: {
      nombre: string;
      codigo: string;
    };
  };
}

// OrderDestination interface (table: destino_pedido)
export interface OrderDestination {
  destino_id: number;
  pedido_id: number;
  direccion: string;
  latitud?: number;
  longitud?: number;
  referencia?: string;
  nombre_contacto?: string;
  telefono_contacto?: string;
  instrucciones_entrega?: string;
  products?: OrderDestinationProduct[];
}

// OrderDestinationProduct interface (table: producto_destino_pedido)
export interface OrderDestinationProduct {
  producto_destino_id: number;
  destino_id: number;
  producto_pedido_id: number;
  cantidad: number;
  observaciones?: string;
}

// Payload for creating orders - matches backend validation
export interface CreateOrderPayload {
  cliente_id: number;
  nombre: string;
  descripcion?: string;
  fecha_entrega?: string;
  observaciones?: string;
  editable_hasta?: string;
  products: {
    producto_id: number;
    cantidad: number;
    precio?: number;
    observaciones?: string;
  }[];
  destinations: {
    direccion: string;
    latitud?: number | null;
    longitud?: number | null;
    referencia?: string;
    nombre_contacto?: string;
    telefono_contacto?: string;
    instrucciones_entrega?: string;
    products: {
      order_product_index: number;
      cantidad: number;
      observaciones?: string;
    }[];
  }[];
}

export const customersApi = {
  // Customers
  getCustomers: async () => {
    try {
      const response = await apiClient.get('/customers');
      // Handle paginated response
      return response.data.data || response.data;
    } catch (error: any) {
      console.log('getCustomers error:', error.response?.status, error.response?.data);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getCustomer: async (id: number) => {
    const response = await apiClient.get<Customer>(`/customers/${id}`);
    return response.data;
  },

  createCustomer: async (data: Partial<Customer>) => {
    const response = await apiClient.post<Customer>('/customers', data);
    return response.data;
  },

  updateCustomer: async (id: number, data: Partial<Customer>) => {
    const response = await apiClient.put<Customer>(`/customers/${id}`, data);
    return response.data;
  },

  deleteCustomer: async (id: number) => {
    const response = await apiClient.delete(`/customers/${id}`);
    return response.data;
  },

  // Customer Orders
  getCustomerOrders: async () => {
    try {
      console.log('Fetching customer orders...');
      const response = await apiClient.get('/customer-orders');
      console.log('Customer orders API response:', response.data);
      
      // Handle paginated response - extract data array
      const orders = response.data.data || response.data;
      console.log('Customer orders received:', orders?.length || 0);
      return orders || [];
    } catch (error: any) {
      console.log('getCustomerOrders error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getCustomerOrder: async (id: number) => {
    const response = await apiClient.get<CustomerOrder>(`/customer-orders/${id}`);
    return response.data;
  },

  createCustomerOrder: async (data: CreateOrderPayload) => {
    const response = await apiClient.post('/customer-orders', data);
    return response.data;
  },

  updateCustomerOrder: async (id: number, data: Partial<CustomerOrder>) => {
    const response = await apiClient.put<CustomerOrder>(`/customer-orders/${id}`, data);
    return response.data;
  },

  cancelCustomerOrder: async (id: number) => {
    const response = await apiClient.post(`/customer-orders/${id}/cancel`);
    return response.data;
  },

  deleteCustomerOrder: async (id: number) => {
    const response = await apiClient.delete(`/customer-orders/${id}`);
    return response.data;
  },
};