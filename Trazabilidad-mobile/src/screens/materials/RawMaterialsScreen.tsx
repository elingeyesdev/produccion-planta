import React from 'react';
import { View, Text, SafeAreaView, FlatList, TouchableOpacity, ActivityIndicator, Alert } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { CustomIcon } from '../../components/common/CustomIcon';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { Button } from '../../components/common/Button';

export default function RawMaterialsScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const { data: materials, isLoading, error, refetch } = useQuery({
    queryKey: ['rawMaterials'],
    queryFn: rawMaterialsApi.getRawMaterials,
  });

  // Refetch materials when screen comes into focus (after creating a new material)
  useFocusEffect(
    React.useCallback(() => {
      console.log('RawMaterialsScreen focused - refetching materials');
      refetch();
    }, [refetch])
  );

  const deleteMutation = useMutation({
    mutationFn: rawMaterialsApi.deleteRawMaterial,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawMaterials'] });
      Alert.alert('Éxito', 'Materia prima eliminada');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar materia prima');
    },
  });

  const handleDelete = (materialId: number, materialName: string) => {
    Alert.alert(
      'Confirmar eliminación',
      `¿Está seguro que desea eliminar "${materialName}"?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        { 
          text: 'Eliminar', 
          style: 'destructive',
          onPress: () => deleteMutation.mutate(materialId)
        }
      ]
    );
  };

  const renderMaterial = ({ item }: any) => (
    <TouchableOpacity 
      className="bg-white p-4 mb-3 rounded-lg shadow-sm"
      onPress={() => navigation.navigate('MaterialDetail', { materialId: item.raw_material_id })}
    >
      <View className="flex-row justify-between items-start">
        <View className="flex-1">
          <Text className="text-lg font-semibold text-gray-900">
            {item.material_base?.name || 'Unknown Material'}
          </Text>
          <Text className="text-gray-600 mb-1">
            Lote: {item.supplier_batch || 'N/A'}
          </Text>
          <Text className="text-gray-600 mb-1">
            Proveedor: {item.supplier?.business_name || item.supplier?.trading_name || 'Desconocido'}
          </Text>
          <Text className="text-gray-700">
            {item.quantity} {item.material_base?.unit?.name || 'units'}
          </Text>
          {item.expiration_date && (
            <Text className="text-gray-500 text-xs">
              Vence: {new Date(item.expiration_date).toLocaleDateString()}
            </Text>
          )}
        </View>
        <TouchableOpacity 
          className="bg-red-500 p-2 rounded-lg"
          onPress={() => handleDelete(item.raw_material_id, item.material_base?.name || 'Material')}
        >
          <CustomIcon name="trash" size={20} color="white" />
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );

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
        <Text className="text-red-500 text-center mb-4">Error al cargar materiales</Text>
        <Button title="Reintentar" onPress={() => refetch()} />
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <View className="flex-1 p-4">
        <View className="flex-row justify-between items-center mb-4">
          <Text className="text-xl font-bold text-gray-900">Materias Primas</Text>
          <TouchableOpacity 
            className="bg-blue-600 p-2 rounded-lg"
            onPress={() => navigation.navigate('CreateMaterial')}
          >
            <CustomIcon name="add" size={24} color="white" />
          </TouchableOpacity>
        </View>

        <FlatList
          data={materials || []}
          renderItem={renderMaterial}
          keyExtractor={(item, index) => item?.raw_material_id?.toString() || index.toString()}
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <Text className="text-center text-gray-500 mt-10">No se encontraron materiales</Text>
          }
        />
      </View>
    </SafeAreaView>
  );
}