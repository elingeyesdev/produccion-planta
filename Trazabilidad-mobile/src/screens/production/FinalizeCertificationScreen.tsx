import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, TouchableOpacity, Alert } from 'react-native';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { certificationApi, ProcessMachine } from '../../api/certification.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function FinalizeCertificationScreen({ route, navigation }: any) {
  const { batchId, machines, completedRecords } = route.params as {
    batchId: number;
    machines: ProcessMachine[];
    completedRecords: number[];
  };

  const queryClient = useQueryClient();
  const [observations, setObservations] = useState('');

  const finalizeMutation = useMutation({
    mutationFn: (obs: string) => certificationApi.finalizeCertification(batchId, obs),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['batches'] });
      queryClient.invalidateQueries({ queryKey: ['process-machines', batchId] });
      
      Alert.alert(
        data.status,
        data.message,
        [
          {
            text: 'Ver Certificado',
            onPress: () => navigation.navigate('CertificationLog', { batchId }),
          },
          {
            text: 'Volver',
            onPress: () => navigation.navigate('ProductionDashboard'),
          },
        ]
      );
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al finalizar certificación');
    },
  });

  const handleFinalize = () => {
    Alert.alert(
      'Confirmar Certificación',
      '¿Está seguro que desea finalizar la certificación de este lote?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Finalizar',
          style: 'default',
          onPress: () => finalizeMutation.mutate(observations),
        },
      ]
    );
  };

  const allCompleted = machines.every((m) => completedRecords.includes(m.process_machine_id ?? m.proceso_maquina_id ?? 0));

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        {/* Header */}
        <View className="bg-white rounded-xl p-6 mb-4 shadow-sm border border-gray-100">
          <View className="items-center mb-4">
            <View className="bg-blue-100 w-16 h-16 rounded-full items-center justify-center mb-3">
              <CustomIcon name="assignment" size={32} color="#2563EB" />
            </View>
            <Text className="text-2xl font-bold text-gray-900">Finalizar Certificación</Text>
            <Text className="text-gray-600 text-center mt-2">
              Revisa el resumen antes de certificar el lote
            </Text>
          </View>
        </View>

        {/* Summary Card */}
        <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
          <Text className="text-lg font-bold text-gray-900 mb-4">Resumen del Proceso</Text>
          
          <View className="flex-row items-center mb-3">
            <View className="bg-blue-100 p-2 rounded-lg mr-3">
              <CustomIcon name="settings" size={20} color="#2563EB" />
            </View>
            <View className="flex-1">
              <Text className="text-sm text-gray-600">Total de Pasos</Text>
              <Text className="text-lg font-bold text-gray-900">{machines.length}</Text>
            </View>
          </View>

          <View className="flex-row items-center mb-3">
            <View className="bg-green-100 p-2 rounded-lg mr-3">
              <CustomIcon name="checkmark" size={20} color="#10B981" />
            </View>
            <View className="flex-1">
              <Text className="text-sm text-gray-600">Pasos Completados</Text>
              <Text className="text-lg font-bold text-green-600">{completedRecords.length}</Text>
            </View>
          </View>

          {!allCompleted && (
            <View className="bg-red-50 rounded-lg p-3 mt-2">
              <Text className="text-red-700 font-semibold text-sm">
                ⚠️ Faltan {machines.length - completedRecords.length} paso(s) por completar
              </Text>
            </View>
          )}
        </View>

        {/* Steps List */}
        <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
          <Text className="text-lg font-bold text-gray-900 mb-4">Pasos del Proceso</Text>
          
          {machines.map((machine, index) => {
            const machineId = machine.process_machine_id ?? machine.proceso_maquina_id ?? 0;
            const isCompleted = completedRecords.includes(machineId);
            
            return (
              <View key={machineId} className="mb-3">
                <View className="flex-row items-center">
                  <View className={`w-8 h-8 rounded-full items-center justify-center mr-3 ${
                    isCompleted ? 'bg-green-600' : 'bg-gray-300'
                  }`}>
                    {isCompleted ? (
                      <CustomIcon name="checkmark" size={16} color="white" />
                    ) : (
                      <Text className="text-white font-bold text-sm">{machine.step_order ?? machine.orden_paso}</Text>
                    )}
                  </View>
                  
                  <View className="flex-1">
                    <Text className="text-base font-semibold text-gray-900">{machine.name ?? machine.nombre}</Text>
                    <Text className="text-sm text-gray-600">{machine.machine?.name ?? machine.machine?.nombre}</Text>
                  </View>

                  {isCompleted ? (
                    <View className="bg-green-100 px-3 py-1 rounded-full">
                      <Text className="text-xs font-semibold text-green-700">Completado</Text>
                    </View>
                  ) : (
                    <View className="bg-red-100 px-3 py-1 rounded-full">
                      <Text className="text-xs font-semibold text-red-700">Pendiente</Text>
                    </View>
                  )}
                </View>
                
                {index < machines.length - 1 && (
                  <View className="ml-4 h-4 w-0.5 bg-gray-200 mt-1" />
                )}
              </View>
            );
          })}
        </View>

        {/* Observations */}
        <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
          <Text className="text-lg font-bold text-gray-900 mb-2">Observaciones Finales</Text>
          <Text className="text-sm text-gray-600 mb-3">
            Agrega comentarios adicionales sobre la certificación (opcional)
          </Text>
          <TextInput
            className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 min-h-32"
            value={observations}
            onChangeText={setObservations}
            placeholder="Ej: Proceso completado sin incidencias..."
            multiline
            numberOfLines={6}
            textAlignVertical="top"
            placeholderTextColor="#9CA3AF"
          />
        </View>
      </ScrollView>

      {/* Action Buttons */}
      <View className="p-4 bg-white border-t border-gray-200">
        <TouchableOpacity
          className={`py-4 rounded-xl shadow-lg mb-3 ${allCompleted ? 'bg-green-600' : 'bg-gray-300'}`}
          onPress={handleFinalize}
          disabled={!allCompleted || finalizeMutation.isPending}
        >
          <Text className="text-white font-bold text-lg text-center">
            {finalizeMutation.isPending ? 'Finalizando...' : 'Finalizar Certificación'}
          </Text>
        </TouchableOpacity>

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
