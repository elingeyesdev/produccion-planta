import React from 'react';
import { View, Text, SafeAreaView, ScrollView, TouchableOpacity, ActivityIndicator, Image } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function RawMaterialDetailScreen({ route, navigation }: any) {
  const { materialId } = route.params;

  const { data: material, isLoading, error } = useQuery({
    queryKey: ['rawMaterial', materialId],
    queryFn: () => rawMaterialsApi.getRawMaterial(materialId),
  });

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  if (error || !material) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 p-6">
        <CustomIcon name="alert" size={48} color="#EF4444" />
        <Text className="text-red-500 text-center mt-4">Error al cargar materia prima</Text>
        <TouchableOpacity
          className="bg-blue-600 px-6 py-3 rounded-lg mt-4"
          onPress={() => navigation.goBack()}
        >
          <Text className="text-white font-semibold">Volver</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const getStockStatus = () => {
    const available = Number(material.available_quantity || 0);
    const minimum = Number(material.material_base?.minimum_stock || 0);
    const maximum = Number(material.material_base?.maximum_stock || 0);

    if (available <= 0) {
      return { label: 'Agotado', color: 'bg-red-600', textColor: 'text-red-600' };
    } else if (minimum > 0 && available <= minimum) {
      return { label: 'Bajo Stock', color: 'bg-yellow-500', textColor: 'text-yellow-600' };
    } else if (maximum > 0 && available >= maximum) {
      return { label: 'Stock Alto', color: 'bg-green-600', textColor: 'text-green-600' };
    }
    return { label: 'Disponible', color: 'bg-blue-600', textColor: 'text-blue-600' };
  };

  const status = getStockStatus();

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1">
        {/* Header with Image */}
        <View className={`${status.color} p-6`}>
          <View className="items-center">
            {material.material_base?.image_url ? (
              <Image
                source={{ uri: material.material_base.image_url }}
                className="w-32 h-32 rounded-full bg-white mb-4"
                resizeMode="cover"
              />
            ) : (
              <View className="w-32 h-32 rounded-full bg-white items-center justify-center mb-4">
                <CustomIcon name="cube" size={64} color="#2563EB" />
              </View>
            )}
            <Text className="text-white text-2xl font-bold mb-1">
              {material.material_base?.name || 'Sin nombre'}
            </Text>
            <Text className="text-white/90 mb-2">{material.material_base?.code}</Text>
            <View className="bg-white px-4 py-2 rounded-full">
              <Text className={`font-bold ${status.textColor}`}>{status.label}</Text>
            </View>
          </View>
        </View>

        <View className="p-4">
          {/* Stock Information */}
          <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
            <Text className="text-lg font-bold text-gray-900 mb-4">Información de Stock</Text>
            
            <View className="flex-row justify-between mb-3 pb-3 border-b border-gray-100">
              <Text className="text-gray-600">Cantidad Disponible</Text>
              <Text className="font-bold text-gray-900 text-lg">
                {Number(material.available_quantity || 0).toFixed(2)} {material.material_base?.unit?.abbreviation || ''}
              </Text>
            </View>

            <View className="flex-row justify-between mb-3 pb-3 border-b border-gray-100">
              <Text className="text-gray-600">Cantidad Total</Text>
              <Text className="font-semibold text-gray-900">
                {Number(material.quantity || 0).toFixed(2)} {material.material_base?.unit?.abbreviation || ''}
              </Text>
            </View>

            {material.material_base?.minimum_stock && (
              <View className="flex-row justify-between mb-3 pb-3 border-b border-gray-100">
                <Text className="text-gray-600">Stock Mínimo</Text>
                <Text className="font-semibold text-yellow-600">
                  {Number(material.material_base.minimum_stock).toFixed(2)} {material.material_base?.unit?.abbreviation || ''}
                </Text>
              </View>
            )}

            {material.material_base?.maximum_stock && (
              <View className="flex-row justify-between">
                <Text className="text-gray-600">Stock Máximo</Text>
                <Text className="font-semibold text-green-600">
                  {Number(material.material_base.maximum_stock).toFixed(2)} {material.material_base?.unit?.abbreviation || ''}
                </Text>
              </View>
            )}
          </View>

          {/* Reception Information */}
          <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
            <Text className="text-lg font-bold text-gray-900 mb-4">Información de Recepción</Text>
            
            <View className="mb-3">
              <Text className="text-sm text-gray-600">Proveedor</Text>
              <Text className="text-base text-gray-900 mt-1 font-semibold">
                {material.supplier?.business_name || material.supplier?.trading_name || 'N/A'}
              </Text>
            </View>

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Lote del Proveedor</Text>
              <Text className="text-base text-gray-900 mt-1">{material.supplier_batch || 'N/A'}</Text>
            </View>

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Número de Factura</Text>
              <Text className="text-base text-gray-900 mt-1">{material.invoice_number || 'N/A'}</Text>
            </View>

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Fecha de Recepción</Text>
              <Text className="text-base text-gray-900 mt-1">
                {material.receipt_date ? new Date(material.receipt_date).toLocaleDateString() : 'N/A'}
              </Text>
            </View>

            {material.expiration_date && (
              <View className="mb-3">
                <Text className="text-sm text-gray-600">Fecha de Vencimiento</Text>
                <Text className="text-base text-gray-900 mt-1">
                  {new Date(material.expiration_date).toLocaleDateString()}
                </Text>
              </View>
            )}

            <View className="mb-3">
              <Text className="text-sm text-gray-600">Conformidad de Recepción</Text>
              <View className="flex-row items-center mt-1">
                <CustomIcon 
                  name={material.receipt_conformity ? "checkmark-circle" : "close-circle"} 
                  size={20} 
                  color={material.receipt_conformity ? "#10B981" : "#EF4444"} 
                />
                <Text className={`ml-2 font-semibold ${material.receipt_conformity ? 'text-green-600' : 'text-red-600'}`}>
                  {material.receipt_conformity ? 'Conforme' : 'No Conforme'}
                </Text>
              </View>
            </View>

            {material.observations && (
              <View>
                <Text className="text-sm text-gray-600">Observaciones</Text>
                <Text className="text-base text-gray-900 mt-1">{material.observations}</Text>
              </View>
            )}
          </View>

          {/* Material Base Information */}
          {material.material_base && (
            <View className="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
              <Text className="text-lg font-bold text-gray-900 mb-4">Información del Material Base</Text>
              
              {material.material_base.description && (
                <View className="mb-3">
                  <Text className="text-sm text-gray-600">Descripción</Text>
                  <Text className="text-base text-gray-900 mt-1">{material.material_base.description}</Text>
                </View>
              )}

              <View className="mb-3">
                <Text className="text-sm text-gray-600">Categoría</Text>
                <Text className="text-base text-gray-900 mt-1">
                  {material.material_base.category?.name || 'N/A'}
                </Text>
              </View>

              <View>
                <Text className="text-sm text-gray-600">Unidad de Medida</Text>
                <Text className="text-base text-gray-900 mt-1">
                  {material.material_base.unit?.name || 'N/A'} ({material.material_base.unit?.abbreviation || ''})
                </Text>
              </View>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Back Button */}
      <View className="p-4 bg-white border-t border-gray-200">
        <TouchableOpacity
          className="py-3 rounded-lg"
          onPress={() => navigation.goBack()}
        >
          <Text className="text-gray-700 font-semibold text-center">Volver</Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}
