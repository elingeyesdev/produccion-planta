import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, ActivityIndicator, Alert } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { categoriesApi } from '../../api/categories.api';
import { unitsApi } from '../../api/units.api';
import { Button } from '../../components/common/Button';
import { Picker } from '@react-native-picker/picker';

export default function CreateMaterialBaseScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    category_id: '',
    unit_id: '',
    name: '',
    description: '',
    minimum_stock: '',
    maximum_stock: '',
  });

  // Fetch categories for dropdown
  const { data: categories, isLoading: loadingCategories, error: categoriesError } = useQuery({
    queryKey: ['categories'],
    queryFn: categoriesApi.getCategories,
    retry: false,
  });

  // Fetch units for dropdown
  const { data: units, isLoading: loadingUnits, error: unitsError } = useQuery({
    queryKey: ['units'],
    queryFn: unitsApi.getUnits,
    retry: false,
  });

  const createMutation = useMutation({
    mutationFn: rawMaterialsApi.createRawMaterialBase,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['materialBases'] });
      Alert.alert('Éxito', 'Base de materia prima creada exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Material base creation error:', error);
      console.error('Error response:', error.response?.data);
      const errorMessage = error.response?.data?.message || error.response?.data?.errors || 'Error al crear base de materia prima';
      Alert.alert('Error', typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage));
    },
  });

  const handleSubmit = () => {
    // Validation
    if (!formData.category_id || !formData.unit_id || !formData.name) {
      Alert.alert('Error', 'Por favor complete los campos requeridos (Categoría, Unidad y Nombre)');
      return;
    }

    const payload = {
      category_id: parseInt(formData.category_id),
      unit_id: parseInt(formData.unit_id),
      name: formData.name,
      description: formData.description || undefined,
      minimum_stock: formData.minimum_stock ? parseFloat(formData.minimum_stock) : 0,
      maximum_stock: formData.maximum_stock ? parseFloat(formData.maximum_stock) : undefined,
    };

    console.log('Creating material base with payload:', payload);
    createMutation.mutate(payload);
  };

  if (loadingCategories || loadingUnits) {
    return (
      <View className="flex-1 justify-center items-center">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nueva Base de Materia Prima</Text>

        {/* Error Messages */}
        {(categoriesError || unitsError) && (
          <View className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <Text className="text-yellow-800 font-medium mb-2">⚠️ Advertencia</Text>
            {categoriesError && (
              <Text className="text-yellow-700 text-sm mb-1">
                • No se pudieron cargar las categorías
              </Text>
            )}
            {unitsError && (
              <Text className="text-yellow-700 text-sm">
                • No se pudieron cargar las unidades de medida
              </Text>
            )}
          </View>
        )}

        {/* Category */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Categoría *</Text>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.category_id}
              onValueChange={(value: string) => setFormData({ ...formData, category_id: value })}
            >
              <Picker.Item label="Seleccione una categoría" value="" />
              {Array.isArray(categories) && categories.map((category: any) => (
                <Picker.Item key={category.category_id} label={category.name} value={category.category_id.toString()} />
              ))}
            </Picker>
          </View>
        </View>

        {/* Unit of Measure */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Unidad de Medida *</Text>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.unit_id}
              onValueChange={(value: string) => setFormData({ ...formData, unit_id: value })}
            >
              <Picker.Item label="Seleccione una unidad" value="" />
              {Array.isArray(units) && units.map((unit: any) => (
                <Picker.Item 
                  key={unit.unit_id} 
                  label={`${unit.name}${unit.abbreviation ? ` (${unit.abbreviation})` : ''}`} 
                  value={unit.unit_id.toString()} 
                />
              ))}
            </Picker>
          </View>
        </View>

        {/* Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre *</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.name}
            onChangeText={(text) => setFormData({ ...formData, name: text })}
            placeholder="Ej: Harina de Trigo"
          />
        </View>

        {/* Description */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.description}
            onChangeText={(text) => setFormData({ ...formData, description: text })}
            placeholder="Descripción del material"
            multiline
            numberOfLines={3}
            textAlignVertical="top"
          />
        </View>

        {/* Minimum Stock */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Stock Mínimo</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.minimum_stock}
            onChangeText={(text) => setFormData({ ...formData, minimum_stock: text })}
            placeholder="0"
            keyboardType="decimal-pad"
          />
        </View>

        {/* Maximum Stock */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Stock Máximo</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.maximum_stock}
            onChangeText={(text) => setFormData({ ...formData, maximum_stock: text })}
            placeholder="Opcional"
            keyboardType="decimal-pad"
          />
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
