import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TouchableOpacity, ActivityIndicator, Alert, Modal } from 'react-native';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { certificationApi, ProcessMachine } from '../../api/certification.api';
import { processesApi } from '../../api/processes.api';
import { CustomIcon } from '../../components/common/CustomIcon';
import { CustomPicker } from '../../components/common/CustomPicker';
import { productionApi } from '../../api/production.api';

export default function ProcessTransformationScreen({ route, navigation }: any) {
  const { batchId } = route.params;
  const queryClient = useQueryClient();
  const [showProcessSelector, setShowProcessSelector] = useState(false);
  const [selectedProcessId, setSelectedProcessId] = useState<number>(0);

  // Fetch batch details
  const { data: batch } = useQuery({
    queryKey: ['batch', batchId],
    queryFn: () => productionApi.getBatch(batchId),
  });

  // Fetch available processes
  const { data: processes } = useQuery({
    queryKey: ['processes'],
    queryFn: () => processesApi.getProcesses(),
  });

  // Fetch process machines for this batch
  const { data: processMachinesData, isLoading, refetch } = useQuery({
    queryKey: ['process-machines', batchId],
    queryFn: () => certificationApi.getProcessMachines(batchId),
  });

  // Assign process mutation
  const assignProcessMutation = useMutation({
    mutationFn: (processId: number) => certificationApi.assignProcess(batchId, processId),
    onSuccess: (data) => {
      // Update the query cache with the returned data
      queryClient.setQueryData(['process-machines', batchId], {
        process_machines: data.process_machines,
        completed_records: data.completed_records,
        process_id: data.process_id,
      });
      setShowProcessSelector(false);
      Alert.alert('Éxito', 'Proceso asignado correctamente');
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al asignar proceso');
    },
  });

  const handleAssignProcess = () => {
    if (!selectedProcessId) {
      Alert.alert('Error', 'Selecciona un proceso');
      return;
    }
    assignProcessMutation.mutate(selectedProcessId);
  };

  const handleRecordVariables = (processMachine: ProcessMachine) => {
    navigation.navigate('RecordVariables', {
      batchId,
      processMachine,
      allMachines: processMachinesData?.process_machines || [],
      completedRecords: processMachinesData?.completed_records || [],
    });
  };

  const handleFinalizeCertification = () => {
    navigation.navigate('FinalizeCertification', {
      batchId,
      machines: processMachinesData?.process_machines || [],
      completedRecords: processMachinesData?.completed_records || [],
    });
  };

  const processMachines = processMachinesData?.process_machines || [];
  const completedRecords = processMachinesData?.completed_records || [];
  const totalSteps = processMachines.length;
  const completedSteps = completedRecords.length;
  const isComplete = totalSteps > 0 && completedSteps === totalSteps;

  if (isLoading) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  // No process assigned yet
  if (processMachines.length === 0) {
    return (
      <SafeAreaView className="flex-1 bg-gray-50">
        <ScrollView className="flex-1 p-4">
          {/* Batch Info Card */}
          <View className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
            <Text className="text-xl font-bold text-gray-900 mb-1">{batch?.product_name}</Text>
            <Text className="text-sm text-blue-600 font-medium">Lote #{batchId}</Text>
          </View>

          {/* No Process Assigned */}
          <View className="bg-white rounded-xl p-8 items-center border-2 border-dashed border-gray-300">
            <CustomIcon name="settings" size={64} color="#D1D5DB" />
            <Text className="text-gray-900 font-bold text-lg mt-4">Sin Proceso Asignado</Text>
            <Text className="text-gray-500 text-center mt-2 mb-6">
              Selecciona un proceso para comenzar la certificación
            </Text>
            <TouchableOpacity
              className="bg-blue-600 px-6 py-3 rounded-lg"
              onPress={() => setShowProcessSelector(true)}
            >
              <Text className="text-white font-semibold">Seleccionar Proceso</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>

        {/* Process Selector Modal */}
        <Modal
          visible={showProcessSelector}
          animationType="slide"
          transparent={true}
          onRequestClose={() => setShowProcessSelector(false)}
        >
          <View className="flex-1 bg-black/50 justify-end">
            <View className="bg-white rounded-t-3xl p-6">
              <View className="flex-row justify-between items-center mb-4">
                <Text className="text-xl font-bold text-gray-900">Seleccionar Proceso</Text>
                <TouchableOpacity onPress={() => setShowProcessSelector(false)}>
                  <CustomIcon name="close" size={24} color="#6B7280" />
                </TouchableOpacity>
              </View>

              <CustomPicker
                label="Proceso"
                required
                selectedValue={selectedProcessId}
                onValueChange={(value) => setSelectedProcessId(value)}
                items={(processes as any[])?.map((process: any) => ({
                  label: `${process.name || process.nombre} (${process.code || process.codigo})`,
                  value: process.process_id || process.proceso_id,
                })) || []}
                placeholder="Seleccionar proceso..."
              />

              <TouchableOpacity
                className="bg-blue-600 py-3 rounded-lg"
                onPress={handleAssignProcess}
                disabled={assignProcessMutation.isPending}
              >
                <Text className="text-white font-semibold text-center">
                  {assignProcessMutation.isPending ? 'Asignando...' : 'Asignar Proceso'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </Modal>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1">
        {/* Batch Info Header */}
        <View className="bg-white p-4 border-b border-gray-200">
          <Text className="text-xl font-bold text-gray-900">{batch?.product_name}</Text>
          <Text className="text-sm text-blue-600 font-medium">Lote #{batchId}</Text>
          
          {/* Progress Bar */}
          <View className="mt-4">
            <View className="flex-row justify-between items-center mb-2">
              <Text className="text-sm font-semibold text-gray-700">Progreso</Text>
              <Text className="text-sm font-bold text-blue-600">{completedSteps}/{totalSteps}</Text>
            </View>
            <View className="h-2 bg-gray-200 rounded-full overflow-hidden">
              <View 
                className="h-full bg-blue-600 rounded-full"
                style={{ width: `${(completedSteps / totalSteps) * 100}%` }}
              />
            </View>
          </View>
        </View>

        {/* Process Steps */}
        <View className="p-4">
          {processMachines.map((machine, index) => {
            const machineId = machine.process_machine_id ?? machine.proceso_maquina_id ?? 0;
            const isCompleted = completedRecords.includes(machineId);
            const prevMachineId = index === 0 ? 0 : (processMachines[index - 1].process_machine_id ?? processMachines[index - 1].proceso_maquina_id ?? 0);
            const isPrevCompleted = index === 0 || completedRecords.includes(prevMachineId);
            const isAccessible = isPrevCompleted;

            return (
              <View key={machineId} className="mb-4">
                {/* Connector Line */}
                {index > 0 && (
                  <View className="ml-6 h-4 w-0.5 bg-gray-300 -mb-2" />
                )}

                <TouchableOpacity
                  className={`bg-white rounded-xl shadow-sm border ${
                    isCompleted ? 'border-green-200' : isAccessible ? 'border-blue-200' : 'border-gray-200'
                  } overflow-hidden`}
                  onPress={() => isAccessible && handleRecordVariables(machine)}
                  disabled={!isAccessible}
                >
                  <View className="flex-row items-center p-4">
                    {/* Step Number Badge */}
                    <View className={`w-12 h-12 rounded-full items-center justify-center mr-4 ${
                      isCompleted ? 'bg-green-600' : isAccessible ? 'bg-blue-600' : 'bg-gray-300'
                    }`}>
                      {isCompleted ? (
                        <CustomIcon name="checkmark" size={24} color="white" />
                      ) : (
                        <Text className="text-white font-bold text-lg">{machine.step_order}</Text>
                      )}
                    </View>

                    {/* Step Info */}
                    <View className="flex-1">
                      <Text className="text-base font-bold text-gray-900">{machine.name || machine.nombre}</Text>
                      <Text className="text-sm text-gray-600">{machine.machine?.name || machine.machine?.nombre}</Text>
                      {machine.variables && machine.variables.length > 0 && (
                        <Text className="text-xs text-gray-500 mt-1">
                          {machine.variables.length} variable{machine.variables.length > 1 ? 's' : ''}
                        </Text>
                      )}
                    </View>

                    {/* Status Icon */}
                    <View>
                      {isCompleted ? (
                        <View className="bg-green-100 p-2 rounded-full">
                          <CustomIcon name="checkmark" size={20} color="#10B981" />
                        </View>
                      ) : isAccessible ? (
                        <CustomIcon name="arrow-forward" size={20} color="#2563EB" />
                      ) : (
                        <CustomIcon name="lock-closed" size={20} color="#9CA3AF" />
                      )}
                    </View>
                  </View>

                  {/* Completed Badge */}
                  {isCompleted && (
                    <View className="bg-green-50 px-4 py-2 border-t border-green-100">
                      <Text className="text-xs text-green-700 font-semibold">✓ Completado</Text>
                    </View>
                  )}
                </TouchableOpacity>
              </View>
            );
          })}
        </View>
      </ScrollView>

      {/* Finalize Button */}
      {isComplete && (
        <View className="p-4 bg-white border-t border-gray-200">
          <TouchableOpacity
            className="bg-green-600 py-4 rounded-xl shadow-lg"
            onPress={handleFinalizeCertification}
          >
            <Text className="text-white font-bold text-lg text-center">Finalizar Certificación</Text>
          </TouchableOpacity>
        </View>
      )}
    </SafeAreaView>
  );
}
