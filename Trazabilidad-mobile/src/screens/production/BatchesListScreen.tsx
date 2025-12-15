import React from 'react';
import { View, Text, SafeAreaView, ScrollView, ActivityIndicator, TouchableOpacity, FlatList } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { productionApi } from '../../api/production.api';
import { CustomIcon } from '../../components/common/CustomIcon';

import { useFocusEffect } from '@react-navigation/native';
import { useCallback } from 'react';

export default function BatchesListScreen({ navigation }: any) {

  const { data: batches, isLoading, error, refetch } = useQuery({
    queryKey: ['batches'],
    queryFn: productionApi.getBatches,
  });

  useFocusEffect(
    useCallback(() => {
      refetch();
    }, [refetch])
  );

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  };

  const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
      pending: 'PENDIENTE',
      in_progress: 'EN PROGRESO',
      completed: 'COMPLETADO',
      failed: 'FALLIDO',
    };
    return statusMap[status] || status;
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
        <Text className="text-red-500 text-center mb-4">Error al cargar lotes</Text>
        <TouchableOpacity
          className="bg-blue-600 px-4 py-2 rounded-lg"
          onPress={() => refetch()}
        >
          <Text className="text-white font-medium">Reintentar</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View className="flex-1 bg-gray-50">
      <FlatList
        data={batches}
        keyExtractor={(item) => item.batch_id.toString()}
        contentContainerStyle={{ padding: 16 }}
        renderItem={({ item }) => (
          <TouchableOpacity
            className="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 p-4"
            onPress={() => {
              if (item.status === 'completed' || item.status === 'failed') {
                navigation.navigate('CertificationLog', { batchId: item.batch_id });
              } else {
                navigation.navigate('BatchDetail', { batchId: item.batch_id });
              }
            }}
          >
            <View className="flex-row justify-between items-start mb-3">
              <View className="flex-1">
                <Text className="text-lg font-bold text-gray-900">{item.name || item.batch_code || `Lote #${item.batch_id}`}</Text>
                <Text className="text-xs text-blue-600 font-medium">{item.batch_code || `LOTE-${item.batch_id}`}</Text>
              </View>
              <View className={`px-3 py-1 rounded-full ${statusColors[item.status]?.split(' ')[0] || 'bg-gray-100'}`}>
                <Text className={`text-xs font-medium ${statusColors[item.status]?.split(' ')[1] || 'text-gray-800'}`}>
                  {getStatusText(item.status)}
                </Text>
              </View>
            </View>

            <View className="flex-row justify-between mb-2">
              <View className="flex-1">
                <Text className="text-xs text-gray-500">Cantidad</Text>
                <Text className="text-sm font-semibold text-gray-900">{item.quantity} unidades</Text>
              </View>
              <View className="flex-1">
                <Text className="text-xs text-gray-500">Operador</Text>
                <Text className="text-sm font-semibold text-gray-900">{item.operator_name}</Text>
              </View>
            </View>

            <View className="flex-row items-center mt-2 pt-2 border-t border-gray-100">
              <CustomIcon name="time" size={14} color="#6B7280" />
              <Text className="text-xs text-gray-600 ml-1">
                {new Date(item.start_date).toLocaleDateString()}
              </Text>
            </View>
          </TouchableOpacity>
        )}
        ListEmptyComponent={
          <View className="items-center py-10">
            <CustomIcon name="assignment" size={64} color="#D1D5DB" />
            <Text className="text-gray-500 text-center mt-4">No hay lotes de producción</Text>
          </View>
        }
      />

      {/* Floating Action Button */}
      <TouchableOpacity
        className="absolute bottom-6 right-6 bg-blue-600 w-14 h-14 rounded-full justify-center items-center shadow-lg"
        onPress={() => navigation.navigate('CreateBatch')}
      >
        <CustomIcon name="add" size={30} color="white" />
      </TouchableOpacity>
    </View>
  );
}
