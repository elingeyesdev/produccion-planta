import React, { useState, useEffect } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, Alert, TouchableOpacity, ActivityIndicator } from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRoute, RouteProp, useNavigation } from '@react-navigation/native';
import { Picker } from '@react-native-picker/picker';
import { processesApi, ProcessMachine } from '../../api/processes.api';
import { machinesApi } from '../../api/machines.api';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';

type RouteParams = {
  EditProcess: {
    processId: number;
  };
};

export default function EditProcessScreen() {
  const navigation = useNavigation<any>();
  const route = useRoute<RouteProp<RouteParams, 'EditProcess'>>();
  const queryClient = useQueryClient();
  const { processId } = route.params;

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    active: true,
  });
  const [steps, setSteps] = useState<Omit<ProcessMachine, 'process_machine_id' | 'process_id' | 'machine'>[]>([]);
  const [showAddStep, setShowAddStep] = useState(false);
  const [currentStep, setCurrentStep] = useState({
    machine_id: 0,
    name: '',
    description: '',
    estimated_time: '',
  });

  const { data: process, isLoading: loadingProcess } = useQuery({
    queryKey: ['process', processId],
    queryFn: () => processesApi.getProcess(processId),
  });

  const { data: machines } = useQuery({
    queryKey: ['machines'],
    queryFn: machinesApi.getMachines,
  });

  // Populate form when process data loads
  useEffect(() => {
    if (process) {
      setFormData({
        name: process.name || process.nombre || '',
        description: process.description || process.descripcion || '',
        active: process.active ?? process.activo ?? true,
      });

      const machines = process.process_machines || process.processMachines;
      if (machines) {
        const existingSteps = machines.map((pm: any) => ({
          machine_id: pm.machine_id || pm.maquina_id,
          step_order: pm.step_order || pm.orden_paso,
          name: pm.name || pm.nombre,
          description: pm.description || pm.descripcion,
          estimated_time: pm.estimated_time || pm.tiempo_estimado,
        }));
        setSteps(existingSteps);
      }
    }
  }, [process]);

  const updateMutation = useMutation({
    mutationFn: (data: any) => processesApi.updateProcess(processId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['processes'] });
      queryClient.invalidateQueries({ queryKey: ['process', processId] });
      Alert.alert('Éxito', 'Proceso actualizado exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Process update error:', error);
      const errorMessage = error.response?.data?.message || 'Error al actualizar proceso';
      Alert.alert('Error', errorMessage);
    },
  });

  const handleAddStep = () => {
    if (!currentStep.machine_id) {
      Alert.alert('Error', 'Selecciona una máquina');
      return;
    }
    if (!currentStep.name.trim()) {
      Alert.alert('Error', 'El nombre del paso es obligatorio');
      return;
    }

    const newStep: Omit<ProcessMachine, 'process_machine_id' | 'process_id' | 'machine'> = {
      machine_id: currentStep.machine_id,
      step_order: steps.length + 1,
      name: currentStep.name,
      description: currentStep.description || undefined,
      estimated_time: currentStep.estimated_time ? parseInt(currentStep.estimated_time) : undefined,
    };

    setSteps([...steps, newStep]);
    setCurrentStep({ machine_id: 0, name: '', description: '', estimated_time: '' });
    setShowAddStep(false);
  };

  const handleRemoveStep = (index: number) => {
    const newSteps = steps.filter((_, i) => i !== index);
    const reorderedSteps = newSteps.map((step, i) => ({ ...step, step_order: i + 1 }));
    setSteps(reorderedSteps);
  };

  const handleMoveStep = (index: number, direction: 'up' | 'down') => {
    if ((direction === 'up' && index === 0) || (direction === 'down' && index === steps.length - 1)) {
      return;
    }

    const newSteps = [...steps];
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    [newSteps[index], newSteps[targetIndex]] = [newSteps[targetIndex], newSteps[index]];
    
    const reorderedSteps = newSteps.map((step, i) => ({ ...step, step_order: i + 1 }));
    setSteps(reorderedSteps);
  };

  const handleSubmit = () => {
    if (!formData.name.trim()) {
      Alert.alert('Error', 'El nombre es obligatorio');
      return;
    }

    updateMutation.mutate({
      name: formData.name,
      description: formData.description || undefined,
      active: formData.active,
      process_machines: steps,
    });
  };

  if (loadingProcess) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50">
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Editar Proceso</Text>

        {/* Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre *</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.name}
            onChangeText={(text) => setFormData({ ...formData, name: text })}
            placeholder="Ej: Extrusión"
          />
        </View>

        {/* Description */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.description}
            onChangeText={(text) => setFormData({ ...formData, description: text })}
            placeholder="Descripción del proceso"
            multiline
            numberOfLines={3}
            textAlignVertical="top"
          />
        </View>

        {/* Active Status */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Estado</Text>
          <View className="bg-white border border-gray-300 rounded-lg">
            <Picker
              selectedValue={formData.active}
              onValueChange={(value) => setFormData({ ...formData, active: value })}
            >
              <Picker.Item label="Activo" value={true} />
              <Picker.Item label="Inactivo" value={false} />
            </Picker>
          </View>
        </View>

        {/* Process Steps */}
        <View className="mb-6">
          <View className="flex-row justify-between items-center mb-3">
            <Text className="text-gray-700 font-medium">Pasos del Proceso ({steps.length})</Text>
            <TouchableOpacity
              className="bg-blue-600 px-4 py-2 rounded-lg flex-row items-center"
              onPress={() => setShowAddStep(true)}
            >
              <CustomIcon name="add" size={16} color="white" />
              <Text className="text-white font-medium ml-1">Agregar Paso</Text>
            </TouchableOpacity>
          </View>

          {steps.map((step, index) => {
            const machine = machines?.find((m: any) => m.machine_id === step.machine_id);
            return (
              <View key={index} className="bg-white rounded-lg p-4 mb-3 border border-gray-200">
                <View className="flex-row items-start">
                  <View className="bg-blue-600 w-8 h-8 rounded-full items-center justify-center mr-3">
                    <Text className="text-white font-bold">{step.step_order}</Text>
                  </View>
                  <View className="flex-1">
                    <Text className="font-bold text-gray-900">{step.name}</Text>
                    <Text className="text-sm text-gray-600">Máquina: {machine?.name || machine?.nombre || 'N/A'}</Text>
                    {step.description && (
                      <Text className="text-sm text-gray-500 mt-1">{step.description}</Text>
                    )}
                    {step.estimated_time && (
                      <Text className="text-sm text-blue-600 mt-1">{step.estimated_time} min</Text>
                    )}
                  </View>
                  <View className="flex-row gap-2">
                    <TouchableOpacity
                      onPress={() => handleMoveStep(index, 'up')}
                      disabled={index === 0}
                      className={index === 0 ? 'opacity-30' : ''}
                    >
                      <CustomIcon name="arrow-up" size={20} color="#6B7280" />
                    </TouchableOpacity>
                    <TouchableOpacity
                      onPress={() => handleMoveStep(index, 'down')}
                      disabled={index === steps.length - 1}
                      className={index === steps.length - 1 ? 'opacity-30' : ''}
                    >
                      <CustomIcon name="arrow-down" size={20} color="#6B7280" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => handleRemoveStep(index)}>
                      <CustomIcon name="trash" size={20} color="#EF4444" />
                    </TouchableOpacity>
                  </View>
                </View>
              </View>
            );
          })}

          {steps.length === 0 && !showAddStep && (
            <View className="bg-gray-100 rounded-lg p-6 items-center">
              <Text className="text-gray-500 text-center">
                No hay pasos agregados. Agrega pasos para definir el flujo del proceso.
              </Text>
            </View>
          )}
        </View>

        {/* Add Step Form */}
        {showAddStep && (
          <View className="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-200">
            <Text className="font-bold text-gray-900 mb-4">Agregar Nuevo Paso</Text>

            <View className="mb-3">
              <Text className="text-gray-700 font-medium mb-2">Máquina *</Text>
              <View className="bg-white border border-gray-300 rounded-lg">
                <Picker
                  selectedValue={currentStep.machine_id}
                  onValueChange={(value) => setCurrentStep({ ...currentStep, machine_id: value })}
                >
                  <Picker.Item label="Seleccionar máquina..." value={0} />
                  {machines?.map((machine: any) => (
                    <Picker.Item
                      key={machine.machine_id || machine.maquina_id}
                      label={`${machine.name || machine.nombre} (${machine.code || machine.codigo})`}
                      value={machine.machine_id || machine.maquina_id}
                    />
                  ))}
                </Picker>
              </View>
            </View>

            <View className="mb-3">
              <Text className="text-gray-700 font-medium mb-2">Nombre del Paso *</Text>
              <TextInput
                className="bg-white border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.name}
                onChangeText={(text) => setCurrentStep({ ...currentStep, name: text })}
                placeholder="Ej: Preparación de material"
              />
            </View>

            <View className="mb-3">
              <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
              <TextInput
                className="bg-white border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.description}
                onChangeText={(text) => setCurrentStep({ ...currentStep, description: text })}
                placeholder="Descripción del paso"
                multiline
                numberOfLines={2}
              />
            </View>

            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Tiempo Estimado (minutos)</Text>
              <TextInput
                className="bg-white border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.estimated_time}
                onChangeText={(text) => setCurrentStep({ ...currentStep, estimated_time: text.replace(/[^0-9]/g, '') })}
                placeholder="Ej: 30"
                keyboardType="numeric"
              />
            </View>

            <View className="flex-row gap-2">
              <TouchableOpacity
                className="flex-1 bg-blue-600 py-3 rounded-lg"
                onPress={handleAddStep}
              >
                <Text className="text-white font-semibold text-center">Agregar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                className="flex-1 bg-gray-300 py-3 rounded-lg"
                onPress={() => {
                  setShowAddStep(false);
                  setCurrentStep({ machine_id: 0, name: '', description: '', estimated_time: '' });
                }}
              >
                <Text className="text-gray-700 font-semibold text-center">Cancelar</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* Buttons */}
        <View className="space-y-3 mb-8">
          <Button
            title={updateMutation.isPending ? "Guardando..." : "Guardar Cambios"}
            onPress={handleSubmit}
            variant="primary"
            disabled={updateMutation.isPending}
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
