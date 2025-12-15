import React, { useCallback } from 'react';
import { View, Text, FlatList, TouchableOpacity, ActivityIndicator } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { productionApi } from '../../api/production.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function CertificatesScreen({ navigation }: any) {
  const { data: batches, isLoading, error, refetch } = useQuery({
    queryKey: ['batches'],
    queryFn: productionApi.getBatches,
  });

  // Refetch batches when screen comes into focus
  useFocusEffect(
    useCallback(() => {
      refetch();
    }, [refetch])
  );

  // Filter only certified and not certified batches
  const certifiedBatches = batches?.filter((batch: any) => 
    batch.status === 'completed' || batch.status === 'failed'
  ) || [];

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
        <Text className="text-red-500 text-center mb-4 mt-4">Error al cargar certificados</Text>
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
    <View className="flex-1 bg-gray-50">
      <FlatList
        data={certifiedBatches}
        keyExtractor={(item) => item.batch_id.toString()}
        contentContainerStyle={{ padding: 16 }}
        renderItem={({ item }) => {
          const isCertified = item.status === 'completed';
          
          return (
            <TouchableOpacity
              className="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 p-4"
              onPress={() => navigation.navigate('CertificationLog', { batchId: item.batch_id })}
            >
              <View className="flex-row justify-between items-start mb-3">
                <View className="flex-1">
                  <Text className="text-lg font-bold text-gray-900">{item.product_name}</Text>
                  <Text className="text-xs text-blue-600 font-medium">Lote #{item.batch_id}</Text>
                </View>
                <View className={`${isCertified ? 'bg-green-100' : 'bg-red-100'} px-3 py-1 rounded-full`}>
                  <Text className={`text-xs font-medium ${isCertified ? 'text-green-800' : 'text-red-800'}`}>
                    {isCertified ? 'CERTIFICADO' : 'NO CERTIFICADO'}
                  </Text>
                </View>
              </View>

              <View className="flex-row items-center mb-2">
                <CustomIcon name="assignment" size={16} color="#6B7280" />
                <Text className="text-sm text-gray-600 ml-2">
                  {isCertified ? 'Proceso completado exitosamente' : 'Proceso completado con fallos'}
                </Text>
              </View>

              <View className="flex-row items-center pt-3 border-t border-gray-100">
                <View className="flex-1 flex-row items-center">
                  <CustomIcon name="time" size={14} color="#6B7280" />
                  <Text className="text-xs text-gray-500 ml-1">
                    {new Date(item.start_date).toLocaleDateString()}
                  </Text>
                </View>
                <View className="flex-row items-center">
                  <Text className="text-xs text-blue-600 font-semibold mr-1">Ver Certificado</Text>
                  <CustomIcon name="arrow-forward" size={14} color="#2563EB" />
                </View>
              </View>
            </TouchableOpacity>
          );
        }}
        ListEmptyComponent={
          <View className="items-center py-16">
            <CustomIcon name="assignment" size={64} color="#D1D5DB" />
            <Text className="text-gray-500 text-center mt-4 font-medium">No hay lotes certificados</Text>
            <Text className="text-gray-400 text-center text-sm mt-1">
              Los lotes certificados aparecerán aquí
            </Text>
          </View>
        }
      />
    </View>
  );
}
