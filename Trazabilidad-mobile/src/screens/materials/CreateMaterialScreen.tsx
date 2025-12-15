import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, Switch, ActivityIndicator, Alert, TouchableOpacity, Platform } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useFocusEffect } from '@react-navigation/native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { rawMaterialsApi } from '../../api/rawMaterials.api';
import { suppliersApi } from '../../api/suppliers.api';
import { Button } from '../../components/common/Button';
import { Picker } from '@react-native-picker/picker';

export default function CreateMaterialScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    material_id: '',
    supplier_id: '',
    supplier_batch: '',
    invoice_number: '',
    receipt_date: new Date().toISOString().split('T')[0],
    expiration_date: '',
    quantity: '',
    receipt_conformity: true,
    observations: '',
  });

  const [showReceiptDatePicker, setShowReceiptDatePicker] = useState(false);
  const [showExpirationDatePicker, setShowExpirationDatePicker] = useState(false);

  // Fetch material bases for dropdown
  const { data: materialBases, isLoading: loadingBases, error: basesError, refetch: refetchBases } = useQuery({
    queryKey: ['materialBases'],
    queryFn: rawMaterialsApi.getRawMaterialBases,
    retry: false,
  });

  // Refetch material bases when screen comes into focus (after creating a new base)
  useFocusEffect(
    React.useCallback(() => {
      console.log('CreateMaterialScreen focused - refetching material bases');
      refetchBases();
    }, [refetchBases])
  );

  // Fetch suppliers for dropdown
  const { data: suppliers, isLoading: loadingSuppliers, error: suppliersError, refetch: refetchSuppliers } = useQuery({
    queryKey: ['suppliers'],
    queryFn: suppliersApi.getSuppliers,
    retry: false,
  });

  // Refetch suppliers when screen comes into focus (after creating a new supplier)
  useFocusEffect(
    React.useCallback(() => {
      console.log('CreateMaterialScreen focused - refetching suppliers');
      refetchSuppliers();
    }, [refetchSuppliers])
  );

  const createMutation = useMutation({
    mutationFn: rawMaterialsApi.createRawMaterial,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawMaterials'] });
      Alert.alert('Éxito', 'Materia prima creada exitosamente');
      navigation.goBack();
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al crear materia prima');
    },
  });

  const handleSubmit = () => {
    // Validation
    if (!formData.material_id || !formData.supplier_id || !formData.quantity) {
      Alert.alert('Error', 'Por favor complete los campos requeridos');
      return;
    }

    createMutation.mutate({
      material_id: parseInt(formData.material_id),
      supplier_id: parseInt(formData.supplier_id),
      supplier_batch: formData.supplier_batch || undefined,
      invoice_number: formData.invoice_number || undefined,
      receipt_date: formData.receipt_date,
      expiration_date: formData.expiration_date || undefined,
      quantity: parseFloat(formData.quantity),
      receipt_conformity: formData.receipt_conformity,
      observations: formData.observations || undefined,
    });
  };

  if (loadingBases || loadingSuppliers) {
    return (
      <View className="flex-1 justify-center items-center">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nueva Materia Prima</Text>

        {/* Error Messages */}
        {(basesError || suppliersError) && (
          <View className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <Text className="text-yellow-800 font-medium mb-2">⚠️ Advertencia</Text>
            {basesError && (
              <Text className="text-yellow-700 text-sm mb-1">
                • No se pudieron cargar las bases de materiales
              </Text>
            )}
            {suppliersError && (
              <Text className="text-yellow-700 text-sm">
                • No se pudieron cargar los proveedores
              </Text>
            )}
          </View>
        )}

        {/* Material Base */}
        <View className="mb-4">
          <View className="flex-row justify-between items-center mb-2">
            <Text className="text-gray-700 font-medium">Material Base *</Text>
            <TouchableOpacity 
              className="bg-green-600 px-3 py-1 rounded-lg flex-row items-center"
              onPress={() => navigation.navigate('CreateMaterialBase')}
            >
              <Text className="text-white text-sm font-medium">+ Agregar Base</Text>
            </TouchableOpacity>
          </View>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.material_id}
              onValueChange={(value: string) => setFormData({ ...formData, material_id: value })}
            >
              <Picker.Item label="Seleccione un material" value="" />
              {Array.isArray(materialBases) && materialBases.map((base: any) => (
                <Picker.Item key={base.material_id} label={base.name} value={base.material_id.toString()} />
              ))}
            </Picker>
          </View>
        </View>

        {/* Supplier */}
        <View className="mb-4">
          <View className="flex-row justify-between items-center mb-2">
            <Text className="text-gray-700 font-medium">Proveedor *</Text>
            <TouchableOpacity 
              className="bg-green-600 px-3 py-1 rounded-lg flex-row items-center"
              onPress={() => navigation.navigate('CreateSupplier')}
            >
              <Text className="text-white text-sm font-medium">+ Agregar Proveedor</Text>
            </TouchableOpacity>
          </View>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.supplier_id}
              onValueChange={(value: string) => setFormData({ ...formData, supplier_id: value })}
            >
              <Picker.Item label="Seleccione un proveedor" value="" />
              {Array.isArray(suppliers) && suppliers.map((supplier: any) => (
                <Picker.Item 
                  key={supplier.supplier_id} 
                  label={supplier.business_name || supplier.trading_name} 
                  value={supplier.supplier_id.toString()} 
                />
              ))}
            </Picker>
          </View>
        </View>

        {/* Supplier Batch */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Lote del Proveedor</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.supplier_batch}
            onChangeText={(text) => setFormData({ ...formData, supplier_batch: text })}
            placeholder="Ingrese el lote"
          />
        </View>

        {/* Invoice Number */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Número de Factura</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.invoice_number}
            onChangeText={(text) => setFormData({ ...formData, invoice_number: text })}
            placeholder="Ingrese el número de factura"
          />
        </View>

        {/* Quantity */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Cantidad *</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.quantity}
            onChangeText={(text) => setFormData({ ...formData, quantity: text })}
            placeholder="Ingrese la cantidad"
            keyboardType="decimal-pad"
          />
        </View>

        {/* Receipt Date */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Fecha de Recepción *</Text>
          <TouchableOpacity 
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            onPress={() => setShowReceiptDatePicker(true)}
          >
            <Text className="text-gray-900">{formData.receipt_date || 'Seleccione fecha'}</Text>
          </TouchableOpacity>
          {showReceiptDatePicker && (
            <DateTimePicker
              value={formData.receipt_date ? new Date(formData.receipt_date + 'T00:00:00') : new Date()}
              mode="date"
              display={Platform.OS === 'ios' ? 'spinner' : 'default'}
              onChange={(event, selectedDate) => {
                setShowReceiptDatePicker(Platform.OS === 'ios');
                if (selectedDate) {
                  const year = selectedDate.getFullYear();
                  const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                  const day = String(selectedDate.getDate()).padStart(2, '0');
                  setFormData({ ...formData, receipt_date: `${year}-${month}-${day}` });
                }
              }}
            />
          )}
        </View>

        {/* Expiration Date */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Fecha de Vencimiento</Text>
          <TouchableOpacity 
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            onPress={() => setShowExpirationDatePicker(true)}
          >
            <Text className="text-gray-900">{formData.expiration_date || 'Seleccione fecha (opcional)'}</Text>
          </TouchableOpacity>
          {showExpirationDatePicker && (
            <DateTimePicker
              value={formData.expiration_date ? new Date(formData.expiration_date + 'T00:00:00') : new Date()}
              mode="date"
              display={Platform.OS === 'ios' ? 'spinner' : 'default'}
              onChange={(event, selectedDate) => {
                setShowExpirationDatePicker(Platform.OS === 'ios');
                if (selectedDate) {
                  const year = selectedDate.getFullYear();
                  const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                  const day = String(selectedDate.getDate()).padStart(2, '0');
                  setFormData({ ...formData, expiration_date: `${year}-${month}-${day}` });
                }
              }}
            />
          )}
        </View>

        {/* Receipt Conformity */}
        <View className="mb-4 flex-row items-center justify-between bg-white border border-gray-300 rounded-lg px-4 py-3">
          <Text className="text-gray-700 font-medium">Conformidad de Recepción</Text>
          <Switch
            value={formData.receipt_conformity}
            onValueChange={(value) => setFormData({ ...formData, receipt_conformity: value })}
          />
        </View>

        {/* Observations */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Observaciones</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.observations}
            onChangeText={(text) => setFormData({ ...formData, observations: text })}
            placeholder="Ingrese observaciones"
            multiline
            numberOfLines={4}
            textAlignVertical="top"
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
