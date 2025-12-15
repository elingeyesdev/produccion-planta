import React from 'react';
import { View, Text, SafeAreaView, ScrollView, ActivityIndicator } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { certificationApi } from '../../api/certification.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function CertificationLogScreen({ route }: any) {
  const { batchId } = route.params;

  const { data: log, isLoading, error } = useQuery({
    queryKey: ['certification-log', batchId],
    queryFn: () => certificationApi.getCertificationLog(batchId),
  });

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  if (error || !log) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 p-6">
        <CustomIcon name="alert" size={48} color="#EF4444" />
        <Text className="text-red-500 text-center mt-4">Error al cargar certificado</Text>
      </View>
    );
  }

  const isCertified = log.final_result.estado === 'Certificado';

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        {/* Certificate Header */}
        <View className={`rounded-xl p-6 mb-4 shadow-lg ${isCertified ? 'bg-green-600' : 'bg-red-600'}`}>
          <View className="items-center">
            <View className="bg-white w-20 h-20 rounded-full items-center justify-center mb-4">
              <CustomIcon 
                name={isCertified ? 'checkmark' : 'close'} 
                size={40} 
                color={isCertified ? '#10B981' : '#EF4444'} 
              />
            </View>
            <Text className="text-white text-3xl font-bold mb-2">{log.final_result.estado}</Text>
            <Text className="text-white/90 text-center">Lote #{batchId}</Text>
          </View>
        </View>

        {/* Evaluation Details */}
        <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
          <Text className="text-lg font-bold text-gray-900 mb-4">Detalles de Evaluación</Text>
          
          <View className="mb-3">
            <Text className="text-sm text-gray-600">Motivo</Text>
            <Text className="text-base font-semibold text-gray-900 mt-1">{log.final_result.razon}</Text>
          </View>

          <View className="mb-3">
            <Text className="text-sm text-gray-600">Inspector</Text>
            <Text className="text-base font-semibold text-gray-900 mt-1">{log.final_result.inspector}</Text>
          </View>

          <View>
            <Text className="text-sm text-gray-600">Fecha de Evaluación</Text>
            <Text className="text-base font-semibold text-gray-900 mt-1">
              {new Date(log.final_result.fecha_evaluacion).toLocaleString()}
            </Text>
          </View>
        </View>

        {/* Machine Records */}
        <View className="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <Text className="text-lg font-bold text-gray-900 mb-4">Registro de Máquinas</Text>
          
          {log.machines.map((machine, index) => (
            <View key={index} className="mb-4">
              <View className="flex-row items-start mb-2">
                <View className={`w-8 h-8 rounded-full items-center justify-center mr-3 ${
                  machine.cumple_estandar ? 'bg-green-600' : 'bg-red-600'
                }`}>
                  <Text className="text-white font-bold text-sm">{machine.orden_paso}</Text>
                </View>
                
                <View className="flex-1">
                  <Text className="text-base font-bold text-gray-900">{machine.nombre_maquina}</Text>
                  <Text className="text-sm text-gray-600">
                    {new Date(machine.fecha_registro).toLocaleString()}
                  </Text>
                </View>

                <View className={`px-3 py-1 rounded-full ${
                  machine.cumple_estandar ? 'bg-green-100' : 'bg-red-100'
                }`}>
                  <Text className={`text-xs font-semibold ${
                    machine.cumple_estandar ? 'text-green-700' : 'text-red-700'
                  }`}>
                    {machine.cumple_estandar ? 'APROBADO' : 'RECHAZADO'}
                  </Text>
                </View>
              </View>

              {/* Variables */}
              {Object.keys(machine.variables_registradas).length > 0 && (
                <View className="bg-gray-50 rounded-lg p-3 ml-11">
                  <Text className="text-xs font-semibold text-gray-700 mb-2">Variables Registradas:</Text>
                  {Object.entries(machine.variables_registradas).map(([key, value]) => (
                    <View key={key} className="flex-row justify-between mb-1">
                      <Text className="text-xs text-gray-600">{key}:</Text>
                      <Text className="text-xs font-semibold text-gray-900">{String(value)}</Text>
                    </View>
                  ))}
                </View>
              )}

              {index < log.machines.length - 1 && (
                <View className="ml-4 h-4 w-0.5 bg-gray-200 mt-2" />
              )}
            </View>
          ))}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
