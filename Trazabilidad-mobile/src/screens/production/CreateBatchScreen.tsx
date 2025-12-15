import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, ActivityIndicator, Alert, TouchableOpacity } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import { productionApi } from '../../api/production.api';
import { ordersApi } from '../../api/orders.api';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';
import { Picker } from '@react-native-picker/picker';

export default function CreateBatchScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    order_id: '',
    name: '',
    target_quantity: '',
    observations: '',
  });

  const [selectedMaterials, setSelectedMaterials] = useState<Array<{
    raw_material_id: number;
    name: string;
    planned_quantity: string;
    unit: string;
    available_quantity: number;
  }>>([]);

  // Fetch customer orders for dropdown
  const { data: orders, isLoading: loadingOrders, error: ordersError } = useQuery({
    queryKey: ['customerOrders'],
    queryFn: ordersApi.getOrders,
    retry: false,
  });

  // Fetch raw materials for selection
  const { data: rawMaterials, isLoading: loadingMaterials, error: materialsError } = useQuery({
    queryKey: ['rawMaterials'],
    queryFn: rawMaterialsApi.getRawMaterials,
    retry: false,
  });

  const createMutation = useMutation({
    mutationFn: productionApi.createBatch,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['batches'] });
      Alert.alert('Éxito', 'Lote de producción creado exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Batch creation error:', error);
      console.error('Error response:', error.response?.data);
      const errorMessage = error.response?.data?.message || error.response?.data?.errors || 'Error al crear lote de producción';
      Alert.alert('Error', typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage));
    },
  });

  const handleSubmit = () => {
    // Validation
    if (!formData.order_id) {
      Alert.alert('Error', 'Por favor seleccione una orden de cliente');
      return;
    }

    // Validate raw materials
    // Validate raw materials
    const rawMaterialsPayload = selectedMaterials.map(material => ({
      raw_material_id: material.raw_material_id,
      planned_quantity: parseFloat(material.planned_quantity) || 0
    })).filter(rm => rm.planned_quantity > 0);

    // Check for invalid quantities
    const hasInvalidQuantities = selectedMaterials.some(m => {
      const planned = parseFloat(m.planned_quantity) || 0;
      return planned > m.available_quantity;
    });

    if (hasInvalidQuantities) {
      Alert.alert('Error', 'Una o más materias primas exceden la cantidad disponible');
      return;
    }

    const payload = {
      order_id: parseInt(formData.order_id),
      name: formData.name || undefined,
      target_quantity: formData.target_quantity ? parseFloat(formData.target_quantity) : undefined,
      observations: formData.observations || undefined,
      raw_materials: rawMaterialsPayload.length > 0 ? rawMaterialsPayload : undefined,
    };

    console.log('Creating batch with payload:', payload);
    createMutation.mutate(payload);
  };

  const addMaterial = () => {
    if (!rawMaterials || rawMaterials.length === 0) {
      Alert.alert('Error', 'No hay materias primas disponibles');
      return;
    }
    
    // Find the first available material that hasn't been added yet
    const availableMaterial = rawMaterials.find((material: any) => 
      !selectedMaterials.some(selected => selected.raw_material_id === material.raw_material_id)
    );
    
    if (!availableMaterial) {
      Alert.alert('Información', 'Todas las materias primas disponibles ya han sido agregadas');
      return;
    }
    
    const newMaterial = {
      raw_material_id: availableMaterial.raw_material_id,
      name: availableMaterial.base?.name || availableMaterial.material_base?.name || 'Material Desconocido',
      planned_quantity: '',
      unit: availableMaterial.base?.unit?.name || availableMaterial.material_base?.unit?.name || 'unidades',
      available_quantity: availableMaterial.quantity || 0
    };
    
    setSelectedMaterials([...selectedMaterials, newMaterial]);
  };

  const removeMaterial = (index: number) => {
    const newMaterials = selectedMaterials.filter((_, i) => i !== index);
    setSelectedMaterials(newMaterials);
  };

  const updateMaterialQuantity = (index: number, quantity: string) => {
    const newMaterials = [...selectedMaterials];
    newMaterials[index].planned_quantity = quantity;
    setSelectedMaterials(newMaterials);
  };

  const changeMaterial = (index: number, materialId: number) => {
    const material = rawMaterials?.find((m: any) => m.raw_material_id === materialId);
    if (!material) return;
    
    const newMaterials = [...selectedMaterials];
    newMaterials[index] = {
      raw_material_id: material.raw_material_id,
      name: material.base?.name || material.material_base?.name || 'Material Desconocido',
      planned_quantity: newMaterials[index].planned_quantity,
      unit: material.base?.unit?.name || material.material_base?.unit?.name || 'unidades',
      available_quantity: material.quantity || 0
    };
    setSelectedMaterials(newMaterials);
  };

  if (loadingOrders || loadingMaterials) {
    return (
      <View className="flex-1 justify-center items-center">
        <ActivityIndicator size="large" color="#2563EB" />
        <Text className="text-gray-600 mt-2">Cargando datos...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nuevo Lote de Producción</Text>

        {/* Error Messages */}
        {ordersError && (
          <View className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <Text className="text-yellow-800 font-medium mb-2">⚠️ Advertencia</Text>
            <Text className="text-yellow-700 text-sm">
              • No se pudieron cargar las órdenes de cliente
            </Text>
          </View>
        )}

        {/* Customer Order */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Orden de Cliente *</Text>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.order_id}
              onValueChange={(value: string) => setFormData({ ...formData, order_id: value })}
            >
              <Picker.Item label="Seleccione una orden" value="" />
              {Array.isArray(orders) && orders.map((order: any) => (
                <Picker.Item 
                  key={order.order_id} 
                  label={`${order.name || order.description || 'Sin descripción'}`} 
                  value={order.order_id.toString()} 
                />
              ))}
            </Picker>
          </View>
        </View>

        {/* Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre del Lote</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.name}
            onChangeText={(text) => setFormData({ ...formData, name: text })}
            placeholder="Ej: Lote Especial Navidad"
          />
        </View>

        {/* Target Quantity */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Cantidad Objetivo</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.target_quantity}
            onChangeText={(text) => setFormData({ ...formData, target_quantity: text })}
            placeholder="Cantidad a producir"
            keyboardType="decimal-pad"
          />
        </View>

        {/* Observations */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Observaciones</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.observations}
            onChangeText={(text) => setFormData({ ...formData, observations: text })}
            placeholder="Notas adicionales"
            multiline
            numberOfLines={4}
            textAlignVertical="top"
          />
        </View>

        {/* Raw Materials */}
        <View className="mb-6">
          <View className="flex-row justify-between items-center mb-4">
            <Text className="text-gray-700 font-medium">Materias Primas</Text>
            <TouchableOpacity 
              className="bg-blue-600 px-3 py-1.5 rounded-lg flex-row items-center"
              onPress={addMaterial}
            >
              <CustomIcon name="add" size={20} color="white" />
              <Text className="text-white text-sm font-medium ml-1">Agregar</Text>
            </TouchableOpacity>
          </View>

          {selectedMaterials.map((material, index) => (
            <View key={index} className="bg-white border border-gray-300 rounded-lg p-4 mb-3">
              <View className="flex-row justify-between items-start mb-3">
                <Text className="text-gray-900 font-medium">Material #{index + 1}</Text>
                <TouchableOpacity onPress={() => removeMaterial(index)}>
                  <CustomIcon name="delete" size={20} color="#ef4444" />
                </TouchableOpacity>
              </View>

              <View className="mb-3">
                <Text className="text-gray-600 text-xs mb-1">Material</Text>
                <View className="border border-gray-200 rounded-lg">
                  <Picker
                    selectedValue={material.raw_material_id}
                    onValueChange={(value) => changeMaterial(index, Number(value))}
                  >
                    {rawMaterials?.map((rm: any) => {
                      const name = rm.base?.name || rm.material_base?.name || 'Desconocido';
                      const unit = rm.base?.unit?.name || rm.material_base?.unit?.name || '';
                      return (
                        <Picker.Item 
                          key={rm.raw_material_id} 
                          label={`${name} (${rm.quantity} ${unit})`} 
                          value={rm.raw_material_id} 
                        />
                      );
                    })}
                  </Picker>
                </View>
              </View>

              <View className="flex-row">
                <View className="flex-1 mr-4">
                  <Text className="text-gray-600 text-xs mb-1">Cantidad Planeada</Text>
                  <TextInput
                    className={`border rounded-lg px-3 py-2 ${
                      (parseFloat(material.planned_quantity) || 0) > material.available_quantity 
                        ? 'border-red-500 bg-red-50' 
                        : 'border-gray-200'
                    }`}
                    value={material.planned_quantity}
                    onChangeText={(text) => updateMaterialQuantity(index, text)}
                    keyboardType="decimal-pad"
                    placeholder="0.00"
                  />
                  {(parseFloat(material.planned_quantity) || 0) > material.available_quantity && (
                    <Text className="text-red-500 text-xs mt-1">
                      Excede disponible ({material.available_quantity} {material.unit})
                    </Text>
                  )}
                </View>
                <View className="flex-1 justify-center pt-4">
                   <Text className="text-gray-500 text-sm">
                     Unidad: {material.unit}
                   </Text>
                </View>
              </View>
            </View>
          ))}
          
          {selectedMaterials.length === 0 && (
             <Text className="text-gray-500 italic text-center py-4 bg-gray-100 rounded-lg border border-dashed border-gray-300">
               No hay materias primas asignadas
             </Text>
          )}
        </View>

        {/* Buttons */}
        <View className="space-y-3 mb-6">
          <Button
            title={createMutation.isPending ? "Guardando..." : "Guardar"}
            onPress={handleSubmit}
            variant="primary"
            disabled={createMutation.isPending}
          />
          <Button
            title="Cancelar"
            onPress={() => navigation.goBack()}
            variant="outline"
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
