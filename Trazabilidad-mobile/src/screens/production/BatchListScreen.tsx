import React from 'react';
import { View, Text, FlatList, ActivityIndicator, SafeAreaView, TouchableOpacity, Alert } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { productionApi } from '../../api/production.api';
import { BatchCard } from '../../components/production/BatchCard';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function BatchListScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const { data: batches, isLoading, error, refetch } = useQuery({
    queryKey: ['batches'],
    queryFn: productionApi.getBatches,
  });

  // Refetch batches when screen comes into focus (after creating a new batch)
  useFocusEffect(
    React.useCallback(() => {
      console.log('BatchListScreen focused - refetching batches');
      refetch();
    }, [refetch])
  );

  const deleteMutation = useMutation({
    mutationFn: productionApi.deleteBatch,
    onSuccess: () => {
      // Invalidate all batch-related queries
      queryClient.invalidateQueries({ queryKey: ['batches'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-batches'] });
      queryClient.invalidateQueries({ queryKey: ['batch'] });
      // Force refetch of current data
      refetch();
      Alert.alert('Éxito', 'Lote de producción eliminado');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar lote');
    },
  });

  const handleDelete = (batchId: number, productName: string) => {
    Alert.alert(
      'Confirmar eliminación',
      `¿Está seguro que desea eliminar el lote "${productName}"?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        { 
          text: 'Eliminar', 
          style: 'destructive',
          onPress: () => deleteMutation.mutate(batchId)
        }
      ]
    );
  };

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  if (error) {
    return (
      <View className="flex-1 justify-center items-center p-6">
        <Text className="text-red-500 text-center mb-4">Error al cargar lotes</Text>
        <Button title="Reintentar" onPress={() => refetch()} />
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <FlatList
        data={batches}
        keyExtractor={(item) => item.batch_id.toString()}
        renderItem={({ item }) => (
          <View>
            <BatchCard 
              batch={item} 
              onPress={() => navigation.navigate('BatchDetail', { batchId: item.batch_id })} 
            />
            <TouchableOpacity 
              className="absolute top-12 right-4 bg-red-500 p-1.5 rounded-lg"
              onPress={() => handleDelete(item.batch_id, item.product_name)}
            >
              <CustomIcon name="trash" size={16} color="white" />
            </TouchableOpacity>
          </View>
        )}
        contentContainerStyle={{ padding: 16 }}
        ListHeaderComponent={
          <View className="flex-row justify-between items-center mb-4">
            <Text className="text-2xl font-bold text-gray-900">Lotes de Producción</Text>
            <TouchableOpacity 
              className="bg-blue-600 p-2 rounded-lg"
              onPress={() => navigation.navigate('CreateBatch')}
            >
              <CustomIcon name="add" size={24} color="white" />
            </TouchableOpacity>
          </View>
        }
        ListEmptyComponent={
          <Text className="text-center text-gray-500 mt-10">No se encontraron lotes</Text>
        }
      />
    </SafeAreaView>
  );
}
