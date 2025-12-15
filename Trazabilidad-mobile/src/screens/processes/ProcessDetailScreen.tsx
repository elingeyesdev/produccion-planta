import React from 'react';
import { View, Text, ScrollView, ActivityIndicator, TouchableOpacity, Alert } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { processesApi, ProcessMachine } from '../../api/processes.api';
import { CustomIcon } from '../../components/common/CustomIcon';

type RouteParams = {
  ProcessDetail: {
    processId: number;
  };
};

export default function ProcessDetailScreen() {
  const navigation = useNavigation<any>();
  const route = useRoute<RouteProp<RouteParams, 'ProcessDetail'>>();
  const queryClient = useQueryClient();
  const { processId } = route.params;

  const { data: process, isLoading, error } = useQuery({
    queryKey: ['process', processId],
    queryFn: () => processesApi.getProcess(processId),
  });

  const deleteMutation = useMutation({
    mutationFn: () => processesApi.deleteProcess(processId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['processes'] });
      navigation.goBack();
    },
    onError: (error: any) => {
      Alert.alert('Error', 'No se pudo eliminar el proceso');
    },
  });

  const handleDelete = () => {
    Alert.alert(
      'Confirmar eliminación',
      '¿Estás seguro de que deseas eliminar este proceso?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: () => deleteMutation.mutate(),
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

  if (error || !process) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 p-6">
        <Text className="text-red-500 text-center mb-4">Error al cargar el proceso</Text>
        <TouchableOpacity
          className="bg-blue-600 px-4 py-2 rounded-lg"
          onPress={() => navigation.goBack()}
        >
          <Text className="text-white font-medium">Volver</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const processMachines = (process as any).process_machines || [];

  return (
    <View className="flex-1 bg-gray-50">
      <ScrollView className="flex-1">
        {/* Process Info Card */}
        <View className="bg-white p-6 border-b border-gray-200">
          <View className="flex-row justify-between items-start mb-4">
            <View className="flex-1">
              <Text className="text-2xl font-bold text-gray-900 mb-1">{process.nombre}</Text>
              <Text className="text-sm text-blue-600 font-medium">{process.codigo}</Text>
            </View>
            <View className={`px-3 py-1 rounded-full ${process.activo ? 'bg-green-100' : 'bg-red-100'}`}>
              <Text className={`text-sm font-medium ${process.activo ? 'text-green-700' : 'text-red-700'}`}>
                {process.activo ? 'Activo' : 'Inactivo'}
              </Text>
            </View>
          </View>

          {process.descripcion && (
            <View className="bg-gray-50 p-3 rounded-lg">
              <Text className="text-xs text-gray-500 mb-1">Descripción</Text>
              <Text className="text-gray-700">{process.descripcion}</Text>
            </View>
          )}
        </View>

        {/* Process Steps */}
        <View className="p-4">
          <View className="flex-row justify-between items-center mb-4">
            <Text className="text-lg font-bold text-gray-900">
              Pasos del Proceso ({processMachines.length})
            </Text>
          </View>

          {processMachines.length === 0 ? (
            <View className="bg-white rounded-xl p-8 items-center">
              <CustomIcon name="settings" size={48} color="#9CA3AF" />
              <Text className="text-gray-500 mt-4 text-center">
                Este proceso no tiene pasos configurados
              </Text>
            </View>
          ) : (
            processMachines
              .sort((a: any, b: any) => (a.step_order || 0) - (b.step_order || 0))
              .map((step: any, index: number) => (
                <View
                  key={step.process_machine_id || index}
                  className="bg-white rounded-xl shadow-sm border border-gray-100 mb-3 overflow-hidden"
                >
                  {/* Step Header */}
                  <View className="bg-blue-50 px-4 py-2 flex-row items-center">
                    <View className="bg-blue-600 w-8 h-8 rounded-full items-center justify-center mr-3">
                      <Text className="text-white font-bold">{step.step_order || index + 1}</Text>
                    </View>
                    <Text className="text-blue-900 font-bold flex-1">{step.name}</Text>
                    {step.estimated_time && (
                      <View className="flex-row items-center">
                        <CustomIcon name="time" size={16} color="#2563EB" />
                        <Text className="text-blue-600 text-sm ml-1">{step.estimated_time} min</Text>
                      </View>
                    )}
                  </View>

                  {/* Machine Info */}
                  {step.machine && (
                    <View className="p-4 border-b border-gray-100">
                      <Text className="text-xs text-gray-500 mb-2">Máquina</Text>
                      <View className="flex-row items-center">
                        <View className="bg-gray-100 w-12 h-12 rounded-lg items-center justify-center mr-3">
                          <CustomIcon name="factory" size={24} color="#6B7280" />
                        </View>
                        <View className="flex-1">
                          <Text className="font-semibold text-gray-900">{step.machine.name}</Text>
                          <Text className="text-xs text-gray-500">{step.machine.code}</Text>
                        </View>
                      </View>
                    </View>
                  )}

                  {/* Step Description */}
                  {step.description && (
                    <View className="p-4">
                      <Text className="text-xs text-gray-500 mb-1">Descripción</Text>
                      <Text className="text-gray-700">{step.description}</Text>
                    </View>
                  )}
                </View>
              ))
          )}
        </View>
      </ScrollView>

      {/* Action Buttons */}
      <View className="bg-white border-t border-gray-200 p-4 flex-row gap-3">
        <TouchableOpacity
          className="flex-1 bg-blue-600 py-3 rounded-lg flex-row items-center justify-center"
          onPress={() => navigation.navigate('EditProcess', { processId })}
        >
          <CustomIcon name="edit" size={20} color="white" />
          <Text className="text-white font-semibold ml-2">Editar</Text>
        </TouchableOpacity>

        <TouchableOpacity
          className="bg-red-600 py-3 px-6 rounded-lg flex-row items-center justify-center"
          onPress={handleDelete}
          disabled={deleteMutation.isPending}
        >
          <CustomIcon name="trash" size={20} color="white" />
          <Text className="text-white font-semibold ml-2">Eliminar</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}
