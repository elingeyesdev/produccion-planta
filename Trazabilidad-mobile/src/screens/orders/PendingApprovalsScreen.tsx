import React, { useCallback } from 'react';
import { View, Text, SafeAreaView, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { CustomIcon } from '../../components/common/CustomIcon';
import { ordersApi, CustomerOrder } from '../../api/orders.api';

export default function PendingApprovalsScreen({ navigation }: any) {
  const { data: orders, isLoading, error, refetch, isRefetching } = useQuery({
    queryKey: ['pendingOrders'],
    queryFn: ordersApi.getPendingOrders,
  });

  useFocusEffect(
    useCallback(() => {
      refetch();
    }, [refetch])
  );

  const renderOrder = ({ item }: { item: CustomerOrder }) => (
    <TouchableOpacity 
      className="bg-white rounded-xl shadow-sm border border-gray-100 mb-3 overflow-hidden"
      onPress={() => navigation.navigate('OrderDetail', { orderId: item.order_id, isApproval: true })}
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
          <View className="bg-yellow-100 px-3 py-1 rounded-full">
            <Text className="text-xs font-semibold text-yellow-800">
              Pendiente
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
              {item.creation_date ? new Date(item.creation_date).toLocaleDateString() : 'N/A'}
            </Text>
          </View>
          
          <View className="flex-row items-center">
            <CustomIcon name="cube" size={14} color="#6B7280" />
            <Text className="text-xs text-gray-500 ml-1">
              {item.orderProducts?.length || 0} productos
            </Text>
          </View>
        </View>
      </View>
    </TouchableOpacity>
  );

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
        <Text className="text-red-500 text-center mb-4 mt-4">Error al cargar aprobaciones pendientes</Text>
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
        <FlatList
          data={orders || []}
          renderItem={renderOrder}
          keyExtractor={(item) => item.order_id.toString()}
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
              <CustomIcon name="checkmark-circle" size={64} color="#D1D5DB" />
              <Text className="text-gray-500 text-center mt-4 font-medium">No hay pedidos pendientes</Text>
              <Text className="text-gray-400 text-center text-sm mt-1">
                ¡Todo está al día!
              </Text>
            </View>
          }
        />
      </View>
    </SafeAreaView>
  );
}
