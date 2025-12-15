import React from 'react';
import { View, Text, SafeAreaView, ScrollView, ActivityIndicator, TouchableOpacity, Alert } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { productionApi } from '../../api/production.api';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function BatchDetailScreen({ route, navigation }: any) {
  const { batchId } = route.params;
  const queryClient = useQueryClient();
  
  const { data: batch, isLoading, error, refetch } = useQuery({
    queryKey: ['batch', batchId],
    queryFn: () => productionApi.getBatch(batchId),
  });

  const deleteMutation = useMutation({
    mutationFn: productionApi.deleteBatch,
    onSuccess: () => {
      // Invalidate all batch-related queries
      queryClient.invalidateQueries({ queryKey: ['batches'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-batches'] });
      queryClient.invalidateQueries({ queryKey: ['batch'] });
      Alert.alert('Éxito', 'Lote de producción eliminado', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar lote');
    },
  });

  const handleDelete = () => {
    Alert.alert(
      'Confirmar eliminación',
      `¿Está seguro que desea eliminar el lote "${batch?.name || 'Lote de Producción'}"?`,
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

  if (error || !batch) {
    return (
      <View className="flex-1 justify-center items-center p-6">
        <Text className="text-red-500 text-center mb-4">Error al cargar detalles del lote</Text>
        <Button title="Reintentar" onPress={() => refetch()} />
      </View>
    );
  }

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    certified: 'bg-green-100 text-green-800',
    not_certified: 'bg-red-100 text-red-800',
    failed: 'bg-red-100 text-red-800',
  };

  const statusLabels: Record<string, string> = {
    pending: 'PENDIENTE',
    in_progress: 'EN PROGRESO',
    completed: 'COMPLETADO',
    certified: 'CERTIFICADO',
    not_certified: 'NO CERTIFICADO',
    failed: 'FALLIDO',
  };

  const currentStatus = batch.status || 'in_progress';
  const statusColor = statusColors[currentStatus] || statusColors.in_progress;
  const statusLabel = statusLabels[currentStatus] || currentStatus.toUpperCase();

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <View className="bg-white rounded-lg p-6 mb-4">
          <View className="flex-row justify-between items-start mb-4">
            <Text className="text-2xl font-bold text-gray-900 flex-1 mr-4">{batch.name || 'Lote de Producción'}</Text>
            <View className="flex-row items-center space-x-2">
              <View className={`px-3 py-1 rounded-full ${statusColor.split(' ')[0]}`}>
                <Text className={`text-sm font-medium ${statusColor.split(' ')[1]}`}>
                  {statusLabel}
                </Text>
              </View>
              <TouchableOpacity 
                className="bg-red-500 p-2 rounded-lg ml-2"
                onPress={handleDelete}
                disabled={deleteMutation.isPending}
              >
                <CustomIcon name="trash" size={18} color="white" />
              </TouchableOpacity>
            </View>
          </View>

          <View className="space-y-4">
            <View>
              <Text className="text-gray-500 text-sm">Código de Lote</Text>
              <Text className="text-lg font-semibold">{batch.batch_code || `#${batch.batch_id}`}</Text>
            </View>

            <View className="flex-row justify-between">
              <View className="flex-1">
                <Text className="text-gray-500 text-sm">Cantidad</Text>
                <Text className="text-lg font-semibold">{batch.quantity} unidades</Text>
              </View>
              <View className="flex-1">
                <Text className="text-gray-500 text-sm">Operador</Text>
                <Text className="text-lg font-semibold">{batch.operator_name}</Text>
              </View>
            </View>

            <View className="flex-row justify-between">
              <View className="flex-1">
                <Text className="text-gray-500 text-sm">Fecha de Inicio</Text>
                <Text className="text-lg font-semibold">{new Date(batch.start_date).toLocaleDateString()}</Text>
              </View>
              {batch.end_date && (
                <View className="flex-1">
                  <Text className="text-gray-500 text-sm">Fecha de Fin</Text>
                  <Text className="text-lg font-semibold">{new Date(batch.end_date).toLocaleDateString()}</Text>
                </View>
              )}
            </View>
          </View>
        </View>

        {/* Actions based on batch status */}
        {(currentStatus === 'certified' || currentStatus === 'not_certified') ? (
          <View className="space-y-3">
            <Button
              title="Ver Registro de Certificación"
              onPress={() => navigation.navigate('CertificationLog', { batchId })}
              variant="primary"
            />
          </View>
        ) : (
          <View className="space-y-3">
            <Button
              title="Transformación de Proceso"
              onPress={() => navigation.navigate('ProcessTransformation', { batchId })}
              variant="primary"
            />
            
            <Button
              title="Control de Calidad"
              onPress={() => navigation.navigate('QualityControl', { batchId })}
              variant="secondary"
            />
            
            <Button
              title="Uso de Materiales"
              onPress={() => navigation.navigate('MaterialUsage', { batchId })}
              variant="outline"
            />
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}