import React from 'react';
import { View, Text, SafeAreaView, FlatList, TouchableOpacity, ActivityIndicator, Alert, RefreshControl } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { CustomIcon } from '../../components/common/CustomIcon';
import { customersApi } from '../../api/customers.api';

export default function OrdersScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  
  const { data: orders, isLoading, error, refetch, isRefetching } = useQuery({
    queryKey: ['customerOrders'],
    queryFn: customersApi.getCustomerOrders,
  });

  const deleteMutation = useMutation({
    mutationFn: customersApi.deleteCustomerOrder,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customerOrders'] });
      Alert.alert('Éxito', 'Pedido eliminado correctamente');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar pedido');
    },
  });

  const handleDelete = (orderId: number, orderNumber: string) => {
    Alert.alert(
      'Confirmar Eliminación',
      `¿Estás seguro que deseas eliminar el pedido ${orderNumber}?`,
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

  const getStatusBadge = (priority: number) => {
    if (priority === 0) {
      return { label: 'Completado', bg: 'bg-green-100', text: 'text-green-800' };
    } else if (priority > 5) {
      return { label: 'Urgente', bg: 'bg-red-100', text: 'text-red-800' };
    } else if (priority > 0) {
      return { label: 'Pendiente', bg: 'bg-yellow-100', text: 'text-yellow-800' };
    }
    return { label: 'En Proceso', bg: 'bg-blue-100', text: 'text-blue-800' };
  };

  const renderOrder = ({ item }: any) => {
    const status = getStatusBadge(item.priority);
    
    return (
      <TouchableOpacity 
        className="bg-white rounded-xl shadow-sm border border-gray-100 mb-3 overflow-hidden"
        onPress={() => navigation.navigate('OrderDetail', { orderId: item.order_id })}
      >
        <View className="p-4">
          <View className="flex-row justify-between items-start mb-3">
            <View className="flex-1">
              <Text className="text-lg font-bold text-gray-900">
                {item.customer?.business_name || item.customer?.trading_name || 'Cliente Desconocido'}
              </Text>
              <Text className="text-xs text-blue-600 font-medium">
                {item.order_number || `#${item.order_id}`}
              </Text>
            </View>
            <View className={`px-3 py-1 rounded-full ${status.bg}`}>
              <Text className={`text-xs font-semibold ${status.text}`}>
                {status.label}
              </Text>
            </View>
          </View>

          <Text className="text-gray-700 mb-2" numberOfLines={2}>
            {item.description || 'Sin descripción'}
          </Text>

          <View className="flex-row items-center justify-between pt-3 border-t border-gray-100">
            <View className="flex-row items-center">
              <CustomIcon name="calendar" size={14} color="#6B7280" />
              <Text className="text-xs text-gray-500 ml-1">
                Entrega: {item.delivery_date ? new Date(item.delivery_date).toLocaleDateString() : 'N/A'}
              </Text>
            </View>
            
            <TouchableOpacity
              className="bg-red-50 p-2 rounded-lg"
              onPress={() => handleDelete(item.order_id, item.order_number || `#${item.order_id}`)}
            >
              <CustomIcon name="trash" size={16} color="#EF4444" />
            </TouchableOpacity>
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  if (error) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 p-6">
        <CustomIcon name="alert" size={48} color="#EF4444" />
        <Text className="text-red-500 text-center mb-4 mt-4">Error al cargar pedidos</Text>
        <TouchableOpacity
          className="bg-blue-600 px-6 py-3 rounded-lg"
          onPress={() => refetch()}
        >
          <Text className="text-white font-semibold">Reintentar</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <View className="flex-1">
        {/* Header */}
        <View className="bg-white p-4 border-b border-gray-200 flex-row justify-between items-center">
          <Text className="text-xl font-bold text-gray-900">Pedidos de Clientes</Text>
          <View className="flex-row">
            <TouchableOpacity
              className="bg-yellow-100 p-3 rounded-lg shadow-sm mr-2"
              onPress={() => navigation.navigate('PendingApprovals')}
            >
              <CustomIcon name="time" size={24} color="#854D0E" />
            </TouchableOpacity>
            <TouchableOpacity
              className="bg-blue-600 p-3 rounded-lg shadow-sm"
              onPress={() => navigation.navigate('CreateOrder')}
            >
              <CustomIcon name="add" size={24} color="white" />
            </TouchableOpacity>
          </View>
        </View>

        {/* Orders List */}
        <FlatList
          data={orders || []}
          renderItem={renderOrder}
          keyExtractor={(item, index) => item?.order_id?.toString() || index.toString()}
          contentContainerStyle={{ padding: 16 }}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={isRefetching}
              onRefresh={refetch}
              tintColor="#2563EB"
            />
          }
          ListEmptyComponent={
            <View className="items-center py-16">
              <CustomIcon name="cart" size={64} color="#D1D5DB" />
              <Text className="text-gray-500 text-center mt-4 font-medium">No hay pedidos registrados</Text>
              <Text className="text-gray-400 text-center text-sm mt-1">
                Crea un nuevo pedido para comenzar
              </Text>
            </View>
          }
        />
      </View>
    </SafeAreaView>
  );
}