import React from 'react';
import { View, Text, SafeAreaView, ScrollView, TouchableOpacity, RefreshControl } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { CustomIcon } from '../../components/common/CustomIcon';
import { useAuthStore } from '../../store/authStore';
import { productionApi } from '../../api/production.api';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { customersApi } from '../../api/customers.api';

export default function HomeScreen({ navigation }: any) {
  const { user } = useAuthStore();

  // Fetch dashboard data
  const { data: batches, isLoading: batchesLoading, refetch: refetchBatches } = useQuery({
    queryKey: ['dashboard-batches'],
    queryFn: productionApi.getBatches,
  });

  const { data: materials, isLoading: materialsLoading, refetch: refetchMaterials } = useQuery({
    queryKey: ['dashboard-materials'],
    queryFn: rawMaterialsApi.getRawMaterials,
  });

  const { data: orders, isLoading: ordersLoading, refetch: refetchOrders } = useQuery({
    queryKey: ['dashboard-orders'],
    queryFn: customersApi.getCustomerOrders,
  });

  const isLoading = batchesLoading || materialsLoading || ordersLoading;

  // Refetch data when screen comes into focus (after navigating back from other screens)
  useFocusEffect(
    React.useCallback(() => {
      console.log('HomeScreen focused - refetching dashboard data');
      refetchBatches();
      refetchMaterials();
      refetchOrders();
    }, [refetchBatches, refetchMaterials, refetchOrders])
  );

  const onRefresh = () => {
    refetchBatches();
    refetchMaterials();
    refetchOrders();
  };

  // Calculate metrics
  const totalBatches = batches?.length || 0;
  const activeBatches = batches?.filter(b => b.status === 'in_progress')?.length || 0;
  const completedBatches = batches?.filter(b => b.status === 'completed')?.length || 0;
  const totalMaterials = materials?.length || 0;
  const lowStockMaterials = materials?.filter(m => m.available_quantity < 100)?.length || 0;
  const totalOrders = orders?.length || 0;
  const pendingOrders = orders?.length || 0; // All orders are pending in our current data

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView 
        className="flex-1"
        refreshControl={<RefreshControl refreshing={isLoading} onRefresh={onRefresh} />}
      >
        {/* Header */}
        <View className="bg-blue-600 px-6 py-8">
          <Text className="text-white text-2xl font-bold">Panel de Producción</Text>
          <Text className="text-blue-100 mt-1">¡Bienvenido de nuevo, {user?.first_name}!</Text>
          <Text className="text-blue-200 text-sm">{user?.role?.name}</Text>
        </View>

        {/* Key Metrics Cards */}
        <View className="px-4 -mt-4">
          <View className="flex-row flex-wrap justify-between">
            {/* Production Metrics */}
            <TouchableOpacity 
              className="bg-white rounded-lg p-4 mb-4 w-[48%] shadow-sm"
              onPress={() => navigation.navigate('Production')}
            >
              <View className="flex-row items-center justify-between">
                <View>
                  <Text className="text-2xl font-bold text-blue-600">{totalBatches}</Text>
                  <Text className="text-gray-600 text-sm">Total de Lotes</Text>
                </View>
                <View className="bg-blue-100 p-2 rounded-full">
                  <CustomIcon name="factory" size={24} color="#2563EB" />
                </View>
              </View>
            </TouchableOpacity>

            <TouchableOpacity 
              className="bg-white rounded-lg p-4 mb-4 w-[48%] shadow-sm"
              onPress={() => navigation.navigate('Production')}
            >
              <View className="flex-row items-center justify-between">
                <View>
                  <Text className="text-2xl font-bold text-green-600">{activeBatches}</Text>
                  <Text className="text-gray-600 text-sm">Lotes Activos</Text>
                </View>
                <View className="bg-green-100 p-2 rounded-full">
                  <CustomIcon name="flash-on" size={24} color="#16A34A" />
                </View>
              </View>
            </TouchableOpacity>

            {/* Materials Metrics */}
            <TouchableOpacity 
              className="bg-white rounded-lg p-4 mb-4 w-[48%] shadow-sm"
              onPress={() => navigation.navigate('Materials')}
            >
              <View className="flex-row items-center justify-between">
                <View>
                  <Text className="text-2xl font-bold text-purple-600">{totalMaterials}</Text>
                  <Text className="text-gray-600 text-sm">Materias Primas</Text>
                </View>
                <View className="bg-purple-100 p-2 rounded-full">
                  <CustomIcon name="inventory" size={24} color="#9333EA" />
                </View>
              </View>
            </TouchableOpacity>

            <TouchableOpacity 
              className="bg-white rounded-lg p-4 mb-4 w-[48%] shadow-sm"
              onPress={() => navigation.navigate('Materials')}
            >
              <View className="flex-row items-center justify-between">
                <View>
                  <Text className="text-2xl font-bold text-orange-600">{lowStockMaterials}</Text>
                  <Text className="text-gray-600 text-sm">Bajo Stock</Text>
                </View>
                <View className="bg-orange-100 p-2 rounded-full">
                  <CustomIcon name="warning" size={24} color="#EA580C" />
                </View>
              </View>
            </TouchableOpacity>
          </View>
        </View>

        {/* Quick Actions */}
        <View className="px-4 mt-2">
          <Text className="text-lg font-bold text-gray-900 mb-4">Acciones Rápidas</Text>
          <View className="flex-row justify-between">
            <TouchableOpacity 
              className="bg-white rounded-lg p-4 flex-1 mr-2 shadow-sm"
              onPress={() => navigation.navigate('Production')}
            >
              <CustomIcon name="add-circle" size={32} color="#3B82F6" style={{ alignSelf: 'center', marginBottom: 8 }} />
              <Text className="text-center font-medium text-gray-900">Nuevo Lote</Text>
            </TouchableOpacity>

            <TouchableOpacity 
              className="bg-white rounded-lg p-4 flex-1 mx-1 shadow-sm"
              onPress={() => navigation.navigate('Materials')}
            >
              <CustomIcon name="assessment" size={32} color="#3B82F6" style={{ alignSelf: 'center', marginBottom: 8 }} />
              <Text className="text-center font-medium text-gray-900">Verificar Stock</Text>
            </TouchableOpacity>

            <TouchableOpacity 
              className="bg-white rounded-lg p-4 flex-1 ml-2 shadow-sm"
              onPress={() => navigation.navigate('Orders')}
            >
              <CustomIcon name="assignment" size={32} color="#3B82F6" style={{ alignSelf: 'center', marginBottom: 8 }} />
              <Text className="text-center font-medium text-gray-900">Órdenes</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Recent Activity */}
        <View className="px-4 mt-6">
          <Text className="text-lg font-bold text-gray-900 mb-4">Actividad de Producción Reciente</Text>
          <View className="bg-white rounded-lg shadow-sm">
            {batches?.slice(0, 3).map((batch, index) => {
              // Status colors and labels mapping
              const statusConfig: Record<string, { bg: string; text: string; label: string }> = {
                pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'PENDIENTE' },
                in_progress: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'EN PROGRESO' },
                completed: { bg: 'bg-green-100', text: 'text-green-800', label: 'COMPLETADO' },
                certified: { bg: 'bg-green-100', text: 'text-green-800', label: 'CERTIFICADO' },
                not_certified: { bg: 'bg-red-100', text: 'text-red-800', label: 'NO CERTIFICADO' },
                failed: { bg: 'bg-red-100', text: 'text-red-800', label: 'FALLIDO' },
              };
              const config = statusConfig[batch.status] || statusConfig.in_progress;
              
              return (
                <TouchableOpacity 
                  key={batch.batch_id}
                  className={`p-4 ${index < 2 ? 'border-b border-gray-100' : ''}`}
                  onPress={() => navigation.navigate('Production', { screen: 'BatchDetail', params: { batchId: batch.batch_id } })}
                >
                  <View className="flex-row justify-between items-center">
                    <View className="flex-1">
                      <Text className="font-medium text-gray-900">{batch.product_name}</Text>
                      <Text className="text-gray-500 text-sm">Lote #{batch.batch_id}</Text>
                    </View>
                    <View className="items-end">
                      <View className={`px-2 py-1 rounded-full ${config.bg}`}>
                        <Text className={`text-xs font-medium ${config.text}`}>
                          {config.label}
                        </Text>
                      </View>
                      <Text className="text-gray-500 text-xs mt-1">{batch.quantity} unidades</Text>
                    </View>
                  </View>
                </TouchableOpacity>
              );
            })}
          </View>
        </View>


      </ScrollView>
    </SafeAreaView>
  );
}
