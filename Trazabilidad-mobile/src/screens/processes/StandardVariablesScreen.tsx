import React, { useState } from 'react';
import { View, Text, FlatList, ActivityIndicator, TouchableOpacity, TextInput, Alert, Modal } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { standardVariablesApi, StandardVariable } from '../../api/standardVariables.api';
import { CustomIcon } from '../../components/common/CustomIcon';
import { Button } from '../../components/common/Button';

export default function StandardVariablesScreen() {
  const queryClient = useQueryClient();
  const [showModal, setShowModal] = useState(false);
  const [editingVariable, setEditingVariable] = useState<StandardVariable | null>(null);
  const [formData, setFormData] = useState({
    name: '',
    unit: '',
    description: '',
  });

  const { data: variables, isLoading, error, refetch } = useQuery({
    queryKey: ['standardVariables'],
    queryFn: standardVariablesApi.getStandardVariables,
  });

  const createMutation = useMutation({
    mutationFn: standardVariablesApi.createStandardVariable,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standardVariables'] });
      setShowModal(false);
      resetForm();
      Alert.alert('Éxito', 'Variable estándar creada exitosamente');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al crear variable');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) =>
      standardVariablesApi.updateStandardVariable(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standardVariables'] });
      setShowModal(false);
      setEditingVariable(null);
      resetForm();
      Alert.alert('Éxito', 'Variable actualizada exitosamente');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al actualizar variable');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: standardVariablesApi.deleteStandardVariable,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standardVariables'] });
      Alert.alert('Éxito', 'Variable eliminada exitosamente');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al eliminar variable');
    },
  });

  const resetForm = () => {
    setFormData({ name: '', unit: '', description: '' });
  };

  const handleOpenCreate = () => {
    resetForm();
    setEditingVariable(null);
    setShowModal(true);
  };

  const handleOpenEdit = (variable: StandardVariable) => {
    setFormData({
      name: variable.name,
      unit: variable.unit || '',
      description: variable.description || '',
    });
    setEditingVariable(variable);
    setShowModal(true);
  };

  const handleSubmit = () => {
    if (!formData.name.trim()) {
      Alert.alert('Error', 'El nombre es obligatorio');
      return;
    }

    const data = {
      name: formData.name,
      unit: formData.unit || undefined,
      description: formData.description || undefined,
      active: true,
    };

    if (editingVariable) {
      updateMutation.mutate({ id: editingVariable.variable_id, data });
    } else {
      createMutation.mutate(data);
    }
  };

  const handleDelete = (variable: StandardVariable) => {
    Alert.alert(
      'Confirmar eliminación',
      `¿Estás seguro de que deseas eliminar la variable "${variable.name}"?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: () => deleteMutation.mutate(variable.variable_id),
        },
      ]
    );
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
        <Text className="text-red-500 text-center mb-4">Error al cargar variables</Text>
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
        data={variables}
        keyExtractor={(item) => item.variable_id.toString()}
        contentContainerStyle={{ padding: 16 }}
        renderItem={({ item }) => (
          <View className="bg-white rounded-xl shadow-sm border border-gray-100 mb-4 p-4">
            <View className="flex-row justify-between items-start mb-2">
              <View className="flex-1">
                <Text className="text-lg font-bold text-gray-900">{item.name}</Text>
                <Text className="text-xs text-blue-600 font-medium">{item.code}</Text>
                {item.unit && (
                  <View className="flex-row items-center mt-1">
                    <CustomIcon name="settings" size={14} color="#6B7280" />
                    <Text className="text-sm text-gray-600 ml-1">Unidad: {item.unit}</Text>
                  </View>
                )}
              </View>
              <View className={`px-2 py-1 rounded-full ${item.active ? 'bg-green-100' : 'bg-red-100'}`}>
                <Text className={`text-xs font-medium ${item.active ? 'text-green-700' : 'text-red-700'}`}>
                  {item.active ? 'Activa' : 'Inactiva'}
                </Text>
              </View>
            </View>

            {item.description && (
              <Text className="text-gray-600 text-sm mt-2" numberOfLines={2}>
                {item.description}
              </Text>
            )}

            <View className="flex-row gap-2 mt-3 pt-3 border-t border-gray-100">
              <TouchableOpacity
                className="flex-1 bg-blue-600 py-2 rounded-lg flex-row items-center justify-center"
                onPress={() => handleOpenEdit(item)}
              >
                <CustomIcon name="edit" size={16} color="white" />
                <Text className="text-white font-medium ml-1">Editar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                className="bg-red-600 py-2 px-4 rounded-lg flex-row items-center justify-center"
                onPress={() => handleDelete(item)}
              >
                <CustomIcon name="trash" size={16} color="white" />
                <Text className="text-white font-medium ml-1">Eliminar</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}
        ListEmptyComponent={
          <View className="items-center py-10">
            <CustomIcon name="settings" size={64} color="#D1D5DB" />
            <Text className="text-gray-500 text-center mt-4">No hay variables estándar registradas</Text>
          </View>
        }
      />

      {/* Floating Action Button */}
      <TouchableOpacity
        className="absolute bottom-6 right-6 bg-blue-600 w-14 h-14 rounded-full justify-center items-center shadow-lg"
        onPress={handleOpenCreate}
      >
        <CustomIcon name="add" size={30} color="white" />
      </TouchableOpacity>

      {/* Create/Edit Modal */}
      <Modal
        visible={showModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => {
          setShowModal(false);
          setEditingVariable(null);
          resetForm();
        }}
      >
        <View className="flex-1 justify-end bg-black/50">
          <View className="bg-white rounded-t-3xl p-6" style={{ maxHeight: '80%' }}>
            <View className="flex-row justify-between items-center mb-6">
              <Text className="text-2xl font-bold text-gray-900">
                {editingVariable ? 'Editar Variable' : 'Nueva Variable'}
              </Text>
              <TouchableOpacity
                onPress={() => {
                  setShowModal(false);
                  setEditingVariable(null);
                  resetForm();
                }}
              >
                <CustomIcon name="close" size={24} color="#6B7280" />
              </TouchableOpacity>
            </View>

            {/* Name */}
            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Nombre *</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={formData.name}
                onChangeText={(text) => setFormData({ ...formData, name: text })}
                placeholder="Ej: Temperatura"
              />
            </View>

            {/* Unit */}
            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Unidad</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={formData.unit}
                onChangeText={(text) => setFormData({ ...formData, unit: text })}
                placeholder="Ej: °C, kg, m/s"
              />
            </View>

            {/* Description */}
            <View className="mb-6">
              <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={formData.description}
                onChangeText={(text) => setFormData({ ...formData, description: text })}
                placeholder="Descripción de la variable"
                multiline
                numberOfLines={3}
                textAlignVertical="top"
              />
            </View>

            {/* Buttons */}
            <View className="space-y-3">
              <Button
                title={
                  createMutation.isPending || updateMutation.isPending
                    ? 'Guardando...'
                    : editingVariable
                    ? 'Guardar Cambios'
                    : 'Crear Variable'
                }
                onPress={handleSubmit}
                variant="primary"
                disabled={createMutation.isPending || updateMutation.isPending}
              />
              <Button
                title="Cancelar"
                onPress={() => {
                  setShowModal(false);
                  setEditingVariable(null);
                  resetForm();
                }}
                variant="outline"
              />
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

