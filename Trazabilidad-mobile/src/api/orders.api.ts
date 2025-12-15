import { apiClient } from './client';

// Re-export types from customers for compatibility
export { CustomerOrder, OrderProduct, OrderDestination } from './customers.api';
import type { CustomerOrder, OrderProduct } from './customers.api';

export const ordersApi = {
  getOrders: async () => {
    try {
      const response = await apiClient.get('/customer-orders');
      console.log('getOrders response:', response.data);
      const data = (response.data as any).data || response.data;
      console.log('Orders extracted:', data);
      return data;
    } catch (error: any) {
      console.log('getOrders error:', error.response?.status, error.response?.data || error.message);
      if (error.response?.status === 404 || error.response?.status === 500) {
        return [];
      }
      throw error;
    }
  },

  getOrder: async (id: number) => {
    const response = await apiClient.get<CustomerOrder>(`/customer-orders/${id}`);
    return response.data;
  },

  createOrder: async (data: any) => {
    const response = await apiClient.post('/customer-orders', data);
    return response.data;
  },

  cancelOrder: async (id: number) => {
    const response = await apiClient.post(`/customer-orders/${id}/cancel`);
    return response.data;
  },

  // Order Approval Methods
  getPendingOrders: async () => {
    try {
      const response = await apiClient.get('/order-approval/pending');
      const data = (response.data as any).data || response.data;
      return data;
    } catch (error: any) {
      console.log('getPendingOrders error:', error.response?.status);
      return [];
    }
  },

  getOrderApproval: async (id: number) => {
    const response = await apiClient.get<CustomerOrder>(`/order-approval/${id}`);
    return response.data;
  },

  approveOrder: async (id: number) => {
    const response = await apiClient.post(`/order-approval/${id}/approve`);
    return response.data;
  },

  approveProduct: async (orderId: number, productId: number, observations?: string) => {
    const response = await apiClient.post(`/order-approval/${orderId}/product/${productId}/approve`, {
      observaciones: observations
    });
    return response.data;
  },

  rejectProduct: async (orderId: number, productId: number, reason: string) => {
    const response = await apiClient.post(`/order-approval/${orderId}/product/${productId}/reject`, {
      razon_rechazo: reason
    });
    return response.data;
  },
};
