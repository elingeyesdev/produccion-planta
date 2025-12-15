import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, Alert, Switch } from 'react-native';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { suppliersApi } from '../../api/suppliers.api';
import { Button } from '../../components/common/Button';

export default function CreateSupplierScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    business_name: '',
    trading_name: '',
    tax_id: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    active: true,
  });

  const createMutation = useMutation({
    mutationFn: suppliersApi.createSupplier,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['suppliers'] });
      Alert.alert('Éxito', 'Proveedor creado exitosamente');
      navigation.goBack();
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al crear proveedor');
    },
  });

  const handleSubmit = () => {
    if (!formData.business_name) {
      Alert.alert('Error', 'El nombre de la empresa es obligatorio');
      return;
    }

    createMutation.mutate(formData);
  };

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nuevo Proveedor</Text>

        {/* Business Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Razón Social *</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.business_name}
            onChangeText={(text) => setFormData({ ...formData, business_name: text })}
            placeholder="Ingrese la razón social"
          />
        </View>

        {/* Trading Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre Comercial</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.trading_name}
            onChangeText={(text) => setFormData({ ...formData, trading_name: text })}
            placeholder="Ingrese el nombre comercial"
          />
        </View>

        {/* Tax ID */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">NIT / RUC</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.tax_id}
            onChangeText={(text) => setFormData({ ...formData, tax_id: text })}
            placeholder="Ingrese el NIT o RUC"
          />
        </View>

        {/* Contact Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre de Contacto</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.contact_person}
            onChangeText={(text) => setFormData({ ...formData, contact_person: text })}
            placeholder="Ingrese nombre de contacto"
          />
        </View>

        {/* Phone */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Teléfono</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.phone}
            onChangeText={(text) => setFormData({ ...formData, phone: text })}
            placeholder="Ingrese teléfono"
            keyboardType="phone-pad"
          />
        </View>

        {/* Email */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Email</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.email}
            onChangeText={(text) => setFormData({ ...formData, email: text })}
            placeholder="Ingrese email"
            keyboardType="email-address"
            autoCapitalize="none"
          />
        </View>

        {/* Address */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Dirección</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.address}
            onChangeText={(text) => setFormData({ ...formData, address: text })}
            placeholder="Ingrese dirección"
            multiline
            numberOfLines={3}
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
