import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TouchableOpacity, ActivityIndicator, Alert, Modal, TextInput } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { customersApi, CustomerOrder } from '../../api/customers.api';
import { ordersApi, OrderProduct } from '../../api/orders.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function OrderDetailScreen({ route, navigation }: any) {
  const { orderId, isApproval } = route.params;
  const queryClient = useQueryClient();
  const [rejectModalVisible, setRejectModalVisible] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<OrderProduct | null>(null);
  const [rejectionReason, setRejectionReason] = useState('');

  const { data: order, isLoading, error, refetch } = useQuery<CustomerOrder>({
    queryKey: ['customerOrder', orderId],
    queryFn: async () => {
      if (isApproval) {
        const result = await ordersApi.getOrderApproval(orderId);
        return result as unknown as CustomerOrder;
      }
      return customersApi.getCustomerOrder(orderId);
    },
  });

  const deleteMutation = useMutation({
    mutationFn: customersApi.deleteCustomerOrder,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customerOrders'] });
      Alert.alert('Éxito', 'Pedido eliminado correctamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar pedido');
    },
  });

  const approveOrderMutation = useMutation({
    mutationFn: ordersApi.approveOrder,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pendingOrders'] });
      queryClient.invalidateQueries({ queryKey: ['customerOrders'] });
      Alert.alert('Éxito', 'Pedido aprobado correctamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al aprobar pedido');
    },
  });

  const cancelOrderMutation = useMutation({
    mutationFn: ordersApi.cancelOrder,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pendingOrders'] });
      queryClient.invalidateQueries({ queryKey: ['customerOrders'] });
      Alert.alert('Éxito', 'Pedido cancelado correctamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al cancelar pedido');
    },
  });

  const approveProductMutation = useMutation({
    mutationFn: ({ productId }: { productId: number }) => 
      ordersApi.approveProduct(orderId, productId),
    onSuccess: () => {
      refetch();
      Alert.alert('Éxito', 'Producto aprobado');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al aprobar producto');
    },
  });

  const rejectProductMutation = useMutation({
    mutationFn: ({ productId, reason }: { productId: number, reason: string }) => 
      ordersApi.rejectProduct(orderId, productId, reason),
    onSuccess: () => {
      setRejectModalVisible(false);
      setRejectionReason('');
      setSelectedProduct(null);
      refetch();
      Alert.alert('Éxito', 'Producto rechazado');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al rechazar producto');
    },
  });

  const handleDelete = () => {
    Alert.alert(
      'Confirmar Eliminación',
      `¿Estás seguro que deseas eliminar este pedido?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: () => deleteMutation.mutate(orderId),
        },
      ]
    );
  };

  const handleRejectProduct = (product: OrderProduct) => {
    setSelectedProduct(product);
    setRejectModalVisible(true);
  };

  const confirmRejectProduct = () => {
    if (!selectedProduct || !rejectionReason.trim()) {
      Alert.alert('Error', 'Debes ingresar una razón para el rechazo');
      return;
    }
    rejectProductMutation.mutate({ 
      productId: selectedProduct.producto_pedido_id, 
      reason: rejectionReason 
    });
  };

  const getStatusInfo = (estado?: string) => {
    if (estado === 'aprobado') return { label: 'Aprobado', bg: 'bg-green-600', icon: 'checkmark-circle' };
    if (estado === 'rechazado') return { label: 'Rechazado', bg: 'bg-red-600', icon: 'close-circle' };
    if (estado === 'cancelado') return { label: 'Cancelado', bg: 'bg-gray-600', icon: 'close-circle' };
    if (estado === 'completado') return { label: 'Completado', bg: 'bg-green-600', icon: 'checkmark-circle' };
    if (estado === 'en_produccion') return { label: 'En Producción', bg: 'bg-blue-600', icon: 'refresh-circle' };
    return { label: 'Pendiente', bg: 'bg-yellow-500', icon: 'time' };
  };

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  if (error || !order) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 p-6">
        <CustomIcon name="alert" size={48} color="#EF4444" />
        <Text className="text-red-500 text-center mt-4">Error al cargar pedido</Text>
      </View>
    );
  }

  const status = getStatusInfo(order.estado);

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1">
        {/* Status Header */}
        <View className={`${status.bg} p-6`}>
          <View className="items-center">
            <View className="bg-white w-16 h-16 rounded-full items-center justify-center mb-3">
              <CustomIcon name={status.icon} size={32} color={status.bg.replace('bg-', '#')} />
            </View>
            <Text className="text-white text-2xl font-bold mb-1">{status.label}</Text>

          </View>
        </View>

        <View className="p-4">
          {/* Customer Info */}
          <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
            <Text className="text-lg font-bold text-gray-900 mb-3">Información del Cliente</Text>
            <View className="space-y-2">
              <View className="flex-row items-center mb-2">
                <CustomIcon name="business" size={16} color="#6B7280" />
                <Text className="text-gray-900 font-semibold ml-2">
                  {order.customer?.razon_social || order.customer?.nombre_comercial || 'N/A'}
                </Text>
              </View>
              {order.customer?.contacto && (
                <View className="flex-row items-center mb-2">
                  <CustomIcon name="person" size={16} color="#6B7280" />
                  <Text className="text-gray-700 ml-2">{order.customer.contacto}</Text>
                </View>
              )}
            </View>
          </View>

          {/* Order Details */}
          <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
            <Text className="text-lg font-bold text-gray-900 mb-3">Detalles del Pedido</Text>
            
            <View className="mb-3">
              <Text className="text-sm text-gray-600">Nombre</Text>
              <Text className="text-base text-gray-900 mt-1">{order.nombre || 'Sin nombre'}</Text>
            </View>

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Descripción</Text>
              <Text className="text-base text-gray-900 mt-1">{order.descripcion || 'Sin descripción'}</Text>
            </View>

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Estado</Text>
              <Text className="text-base font-semibold text-gray-900 mt-1">
                {status.label}
              </Text>
            </View>

            {order.fecha_entrega && (
              <View className="mb-3">
                <Text className="text-sm text-gray-600">Fecha de Entrega</Text>
                <Text className="text-base text-gray-900 mt-1">
                  {new Date(order.fecha_entrega).toLocaleDateString()}
                </Text>
              </View>
            )}
          </View>

          {/* Products List (for Approval) */}
          {order.orderProducts && order.orderProducts.length > 0 && (
            <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
              <Text className="text-lg font-bold text-gray-900 mb-3">Productos</Text>
              {order.orderProducts.map((product: OrderProduct) => (
                <View key={product.producto_pedido_id} className="border-b border-gray-100 py-3 last:border-0">
                  <View className="flex-row justify-between items-start">
                    <View className="flex-1">
                      <Text className="font-semibold text-gray-900">{product.product?.nombre}</Text>
                      <Text className="text-sm text-gray-600">
                        {product.cantidad} {product.product?.unit?.codigo || 'unidades'}
                      </Text>
                      {product.precio && (
                        <Text className="text-sm text-green-600 font-semibold">
                          Precio: ${product.precio}
                        </Text>
                      )}
                      {product.observaciones && (
                        <Text className="text-xs text-gray-500 mt-1 italic">{product.observaciones}</Text>
                      )}
                      {product.razon_rechazo && (
                        <Text className="text-xs text-red-500 mt-1">Motivo rechazo: {product.razon_rechazo}</Text>
                      )}
                    </View>
                    <View className="items-end">
                      <View className={`px-2 py-1 rounded-full ${
                        product.estado === 'aprobado' ? 'bg-green-100' : 
                        product.estado === 'rechazado' ? 'bg-red-100' : 'bg-yellow-100'
                      }`}>
                        <Text className={`text-xs font-semibold ${
                          product.estado === 'aprobado' ? 'text-green-800' : 
                          product.estado === 'rechazado' ? 'text-red-800' : 'text-yellow-800'
                        }`}>
                          {product.estado.charAt(0).toUpperCase() + product.estado.slice(1)}
                        </Text>
                      </View>
                    </View>
                  </View>

                  {/* Product Actions */}
                  {isApproval && product.estado === 'pendiente' && (
                    <View className="flex-row mt-3 justify-end space-x-2">
                      <TouchableOpacity 
                        className="bg-red-50 px-3 py-2 rounded-lg mr-2"
                        onPress={() => handleRejectProduct(product)}
                      >
                        <Text className="text-red-600 font-semibold text-xs">Rechazar</Text>
                      </TouchableOpacity>
                      <TouchableOpacity 
                        className="bg-green-50 px-3 py-2 rounded-lg"
                        onPress={() => approveProductMutation.mutate({ productId: product.producto_pedido_id })}
                      >
                        <Text className="text-green-600 font-semibold text-xs">Aprobar</Text>
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              ))}
            </View>
          )}
        </View>
      </ScrollView>

      {/* Action Buttons */}
      <View className="p-4 bg-white border-t border-gray-200">
        {isApproval ? (
          <View className="space-y-3">
            <TouchableOpacity
              className="bg-green-600 py-4 rounded-xl shadow-lg mb-3"
              onPress={() => approveOrderMutation.mutate(orderId)}
              disabled={approveOrderMutation.isPending}
            >
              <Text className="text-white font-bold text-lg text-center">
                {approveOrderMutation.isPending ? 'Aprobando...' : 'Aprobar Pedido Completo'}
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              className="bg-red-100 py-3 rounded-xl mb-3"
              onPress={() => cancelOrderMutation.mutate(orderId)}
              disabled={cancelOrderMutation.isPending}
            >
              <Text className="text-red-700 font-bold text-center">
                {cancelOrderMutation.isPending ? 'Cancelando...' : 'Cancelar Pedido'}
              </Text>
            </TouchableOpacity>
          </View>
        ) : (
          <TouchableOpacity
            className="bg-red-600 py-4 rounded-xl shadow-lg mb-3"
            onPress={handleDelete}
            disabled={deleteMutation.isPending}
          >
            <Text className="text-white font-bold text-lg text-center">
              {deleteMutation.isPending ? 'Eliminando...' : 'Eliminar Pedido'}
            </Text>
          </TouchableOpacity>
        )}
        
        <TouchableOpacity
          className="py-3 rounded-lg"
          onPress={() => navigation.goBack()}
        >
          <Text className="text-gray-700 font-semibold text-center">Volver</Text>
        </TouchableOpacity>
      </View>

      {/* Reject Modal */}
      <Modal
        visible={rejectModalVisible}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setRejectModalVisible(false)}
      >
        <View className="flex-1 bg-black/50 justify-center p-4">
          <View className="bg-white rounded-xl p-6">
            <Text className="text-xl font-bold text-gray-900 mb-4">Rechazar Producto</Text>
            <Text className="text-gray-600 mb-2">
              ¿Por qué rechazas {selectedProduct?.product?.nombre}?
            </Text>
            <TextInput
              className="border border-gray-300 rounded-lg p-3 mb-4 min-h-[100px]"
              multiline
              placeholder="Escribe la razón del rechazo..."
              value={rejectionReason}
              onChangeText={setRejectionReason}
              textAlignVertical="top"
            />
            <View className="flex-row justify-end space-x-3">
              <TouchableOpacity
                className="px-4 py-2 rounded-lg bg-gray-100 mr-2"
                onPress={() => setRejectModalVisible(false)}
              >
                <Text className="text-gray-700 font-semibold">Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                className="px-4 py-2 rounded-lg bg-red-600"
                onPress={confirmRejectProduct}
              >
                <Text className="text-white font-semibold">Rechazar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}
