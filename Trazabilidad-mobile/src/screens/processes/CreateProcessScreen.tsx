import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, Alert, TouchableOpacity, Modal } from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { processesApi, ProcessMachine, ProcessMachineVariable } from '../../api/processes.api';
import { machinesApi } from '../../api/machines.api';
import { standardVariablesApi } from '../../api/standardVariables.api';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';
import { CustomPicker } from '../../components/common/CustomPicker';

export default function CreateProcessScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    name: '',
    description: '',
  });
  const [steps, setSteps] = useState<Omit<ProcessMachine, 'process_machine_id' | 'process_id' | 'machine'>[]>([]);
  const [showAddStep, setShowAddStep] = useState(false);
  const [currentStep, setCurrentStep] = useState({
    machine_id: 0,
    name: '',
    description: '',
    estimated_time: '',
  });

  // Variable management state
  const [showVariablesModal, setShowVariablesModal] = useState(false);
  const [editingStepIndex, setEditingStepIndex] = useState<number | null>(null);
  const [showAddVariable, setShowAddVariable] = useState(false);
  const [currentVariable, setCurrentVariable] = useState({
    standard_variable_id: 0,
    min_value: '',
    max_value: '',
    target_value: '',
    mandatory: false,
  });

  const { data: machines } = useQuery({
    queryKey: ['machines'],
    queryFn: machinesApi.getMachines,
  });

  const { data: standardVariables } = useQuery({
    queryKey: ['standardVariables'],
    queryFn: standardVariablesApi.getStandardVariables,
  });

  const createMutation = useMutation({
    mutationFn: processesApi.createProcess,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['processes'] });
      Alert.alert('Éxito', 'Proceso creado exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Process creation error:', error);
      const errorMessage = error.response?.data?.message || 'Error al crear proceso';
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
      variables: [],
    };

    setSteps([...steps, newStep]);
    setCurrentStep({ machine_id: 0, name: '', description: '', estimated_time: '' });
    setShowAddStep(false);
  };

  const handleOpenVariablesModal = (stepIndex: number) => {
    setEditingStepIndex(stepIndex);
    setShowVariablesModal(true);
  };

  const handleAddVariable = () => {
    if (!currentVariable.standard_variable_id) {
      Alert.alert('Error', 'Selecciona una variable estándar');
      return;
    }

    if (editingStepIndex === null) return;

    const newVariable: ProcessMachineVariable = {
      standard_variable_id: currentVariable.standard_variable_id,
      min_value: currentVariable.min_value ? parseFloat(currentVariable.min_value) : undefined,
      max_value: currentVariable.max_value ? parseFloat(currentVariable.max_value) : undefined,
      target_value: currentVariable.target_value ? parseFloat(currentVariable.target_value) : undefined,
      mandatory: currentVariable.mandatory,
    };

    const newSteps = [...steps];
    if (!newSteps[editingStepIndex].variables) {
      newSteps[editingStepIndex].variables = [];
    }
    newSteps[editingStepIndex].variables!.push(newVariable);
    setSteps(newSteps);

    setCurrentVariable({ standard_variable_id: 0, min_value: '', max_value: '', target_value: '', mandatory: false });
    setShowAddVariable(false);
  };

  const handleRemoveVariable = (varIndex: number) => {
    if (editingStepIndex === null) return;
    const newSteps = [...steps];
    newSteps[editingStepIndex].variables = newSteps[editingStepIndex].variables?.filter((_, i) => i !== varIndex);
    setSteps(newSteps);
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

    const payload = {
      name: formData.name,
      description: formData.description || undefined,
      active: true,
      process_machines: steps.length > 0 ? steps : undefined,
    };
    
    console.log('Creating process with payload:', JSON.stringify(payload, null, 2));
    createMutation.mutate(payload);
  };

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nuevo Proceso</Text>

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
        <View className="mb-6">
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
            const variableCount = step.variables?.length || 0;
            
            return (
              <View key={index} className="bg-white rounded-xl shadow-sm border border-gray-100 mb-3 overflow-hidden">
                {/* Step Header */}
                <View className="bg-blue-100 px-4 py-3 flex-row items-center border-b border-blue-200">
                  <View className="bg-blue-600 w-10 h-10 rounded-full items-center justify-center mr-3 shadow">
                    <Text className="text-white font-bold text-lg">{step.step_order}</Text>
                  </View>
                  <View className="flex-1">
                    <Text className="font-bold text-gray-900 text-base">{step.name}</Text>
                    <Text className="text-sm text-gray-600">{machine?.name || machine?.nombre || 'N/A'}</Text>
                  </View>
                  <View className="flex-row gap-2">
                    <TouchableOpacity
                      onPress={() => handleMoveStep(index, 'up')}
                      disabled={index === 0}
                      className={`p-2 ${index === 0 ? 'opacity-30' : ''}`}
                    >
                      <CustomIcon name="arrow-up" size={18} color="#2563EB" />
                    </TouchableOpacity>
                    <TouchableOpacity
                      onPress={() => handleMoveStep(index, 'down')}
                      disabled={index === steps.length - 1}
                      className={`p-2 ${index === steps.length - 1 ? 'opacity-30' : ''}`}
                    >
                      <CustomIcon name="arrow-down" size={18} color="#2563EB" />
                    </TouchableOpacity>
                    <TouchableOpacity
                      onPress={() => handleRemoveStep(index)}
                      className="p-2"
                    >
                      <CustomIcon name="trash" size={18} color="#EF4444" />
                    </TouchableOpacity>
                  </View>
                </View>

                {/* Step Details */}
                <View className="p-4">
                  {step.description && (
                    <Text className="text-gray-600 text-sm mb-3">{step.description}</Text>
                  )}
                  
                  {step.estimated_time && (
                    <View className="flex-row items-center mb-3">
                      <CustomIcon name="time" size={16} color="#6B7280" />
                      <Text className="text-gray-600 text-sm ml-2">{step.estimated_time} minutos</Text>
                    </View>
                  )}

                  {/* Variables Button */}
                  <TouchableOpacity
                    className="bg-blue-600 rounded-lg py-3 px-4 flex-row items-center justify-between shadow-sm"
                    onPress={() => handleOpenVariablesModal(index)}
                  >
                    <View className="flex-row items-center">
                      <CustomIcon name="settings" size={20} color="white" />
                      <Text className="text-white font-semibold ml-2">Variables del Paso</Text>
                    </View>
                    <View className="bg-white/20 rounded-full px-3 py-1">
                      <Text className="text-white font-bold">{variableCount}</Text>
                    </View>
                  </TouchableOpacity>
                </View>
              </View>
            );
          })}

          {steps.length === 0 && !showAddStep && (
            <View className="bg-white rounded-xl p-8 items-center border-2 border-dashed border-gray-300">
              <CustomIcon name="settings" size={48} color="#D1D5DB" />
              <Text className="text-gray-500 text-center mt-4 font-medium">
                No hay pasos agregados
              </Text>
              <Text className="text-gray-400 text-center text-sm mt-1">
                Agrega pasos para definir el flujo del proceso
              </Text>
            </View>
          )}
        </View>

        {/* Add Step Form */}
        {showAddStep && (
          <View className="bg-white rounded-xl p-6 mb-6 shadow-lg border border-blue-200">
            <Text className="text-xl font-bold text-gray-900 mb-4">Agregar Nuevo Paso</Text>

              <CustomPicker
                label="Máquina"
                required
                selectedValue={currentStep.machine_id}
                onValueChange={(value) => setCurrentStep({ ...currentStep, machine_id: value })}
                items={(machines as any[])?.map((machine: any) => ({
                  label: `${machine.name || machine.nombre} (${machine.code || machine.codigo})`,
                  value: machine.machine_id || machine.maquina_id,
                })) || []}
                placeholder="Seleccionar máquina..."
              />

            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Nombre del Paso *</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.name}
                onChangeText={(text) => setCurrentStep({ ...currentStep, name: text })}
                placeholder="Ej: Preparación de material"
              />
            </View>

            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.description}
                onChangeText={(text) => setCurrentStep({ ...currentStep, description: text })}
                placeholder="Descripción del paso"
                multiline
                numberOfLines={2}
              />
            </View>

            <View className="mb-6">
              <Text className="text-gray-700 font-medium mb-2">Tiempo Estimado (minutos)</Text>
              <TextInput
                className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                value={currentStep.estimated_time}
                onChangeText={(text) => setCurrentStep({ ...currentStep, estimated_time: text.replace(/[^0-9]/g, '') })}
                placeholder="Ej: 30"
                keyboardType="numeric"
              />
            </View>

            <View className="flex-row gap-3">
              <TouchableOpacity
                className="flex-1 bg-blue-600 py-3 rounded-lg shadow"
                onPress={handleAddStep}
              >
                <Text className="text-white font-semibold text-center">Agregar Paso</Text>
              </TouchableOpacity>
              <TouchableOpacity
                className="flex-1 bg-gray-200 py-3 rounded-lg"
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

        {/* Submit Buttons */}
        <View className="space-y-3 mb-8">
          <Button
            title={createMutation.isPending ? "Guardando..." : "Guardar Proceso"}
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

      {/* Variables Management Modal */}
      <Modal
        visible={showVariablesModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => {
          setShowVariablesModal(false);
          setEditingStepIndex(null);
          setShowAddVariable(false);
        }}
      >
        <View className="flex-1 bg-black/50">
          <View className="flex-1 mt-20 bg-white rounded-t-3xl">
            {/* Modal Header */}
            <View className="bg-blue-600 px-6 py-4 rounded-t-3xl flex-row justify-between items-center">
              <View className="flex-1">
                <Text className="text-white text-xl font-bold">Variables del Paso</Text>
                {editingStepIndex !== null && (
                  <Text className="text-white/80 text-sm">{steps[editingStepIndex]?.name}</Text>
                )}
              </View>
              <TouchableOpacity
                onPress={() => {
                  setShowVariablesModal(false);
                  setEditingStepIndex(null);
                  setShowAddVariable(false);
                }}
                className="bg-white rounded-full p-2"
              >
                <CustomIcon name="close" size={24} color="#2563EB" />
              </TouchableOpacity>
            </View>

            <ScrollView className="flex-1 p-6">
              {/* Variables List */}
              {editingStepIndex !== null && steps[editingStepIndex]?.variables && steps[editingStepIndex].variables!.length > 0 ? (
                <View className="mb-6">
                  {steps[editingStepIndex].variables!.map((variable, varIndex) => {
                    const stdVar = standardVariables?.find(
                      (v: any) => v.variable_id === variable.standard_variable_id
                    );
                    return (
                      <View
                        key={varIndex}
                        className="bg-blue-50 rounded-xl p-4 mb-3 border border-blue-200 shadow-sm"
                      >
                        <View className="flex-row justify-between items-start mb-3">
                          <View className="flex-1">
                            <Text className="text-lg font-bold text-gray-900">{stdVar?.name || stdVar?.nombre || 'Variable'}</Text>
                            {(stdVar?.unit || stdVar?.unidad) && (
                              <Text className="text-sm text-gray-600">Unidad: {stdVar?.unit || stdVar?.unidad}</Text>
                            )}
                          </View>
                          <TouchableOpacity
                            onPress={() => handleRemoveVariable(varIndex)}
                            className="bg-red-500 rounded-full p-2"
                          >
                            <CustomIcon name="trash" size={16} color="white" />
                          </TouchableOpacity>
                        </View>

                        <View className="flex-row gap-3">
                          {variable.min_value !== undefined && (
                            <View className="flex-1 bg-white rounded-lg p-3">
                              <Text className="text-xs text-gray-500 mb-1">Mínimo</Text>
                              <Text className="text-base font-bold text-gray-900">{variable.min_value}</Text>
                            </View>
                          )}
                          {variable.target_value !== undefined && (
                            <View className="flex-1 bg-white rounded-lg p-3">
                              <Text className="text-xs text-gray-500 mb-1">Objetivo</Text>
                              <Text className="text-base font-bold text-green-600">{variable.target_value}</Text>
                            </View>
                          )}
                          {variable.max_value !== undefined && (
                            <View className="flex-1 bg-white rounded-lg p-3">
                              <Text className="text-xs text-gray-500 mb-1">Máximo</Text>
                              <Text className="text-base font-bold text-gray-900">{variable.max_value}</Text>
                            </View>
                          )}
                        </View>

                        {variable.mandatory && (
                          <View className="bg-red-100 rounded-lg px-3 py-2 mt-3">
                            <Text className="text-red-700 font-semibold text-sm text-center">Variable Obligatoria</Text>
                          </View>
                        )}
                      </View>
                    );
                  })}
                </View>
              ) : (
                !showAddVariable && (
                  <View className="bg-gray-50 rounded-xl p-8 items-center border-2 border-dashed border-gray-300 mb-6">
                    <CustomIcon name="settings" size={48} color="#D1D5DB" />
                    <Text className="text-gray-500 text-center mt-4 font-medium">
                      No hay variables asignadas
                    </Text>
                    <Text className="text-gray-400 text-center text-sm mt-1">
                      Agrega variables para controlar este paso
                    </Text>
                  </View>
                )
              )}

              {/* Add Variable Form */}
              {showAddVariable ? (
                <View className="bg-white rounded-xl p-6 shadow-lg">
                  <Text className="text-lg font-bold text-gray-900 mb-4">Nueva Variable</Text>

                    <CustomPicker
                      label="Variable Estándar"
                      required
                      selectedValue={currentVariable.standard_variable_id}
                      onValueChange={(value) =>
                        setCurrentVariable({ ...currentVariable, standard_variable_id: value })
                      }
                      items={(standardVariables as any[])?.map((variable: any) => ({
                        label: `${variable.name || variable.nombre}${(variable.unit || variable.unidad) ? ` (${variable.unit || variable.unidad})` : ''}`,
                        value: variable.variable_id,
                      })) || []}
                      placeholder="Seleccionar variable..."
                    />

                  <View className="flex-row gap-3 mb-4">
                    <View className="flex-1">
                      <Text className="text-gray-700 font-medium mb-2">Valor Mínimo</Text>
                      <TextInput
                        className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                        value={currentVariable.min_value}
                        onChangeText={(text) =>
                          setCurrentVariable({ ...currentVariable, min_value: text })
                        }
                        keyboardType="numeric"
                        placeholder="0"
                      />
                    </View>
                    <View className="flex-1">
                      <Text className="text-gray-700 font-medium mb-2">Valor Máximo</Text>
                      <TextInput
                        className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                        value={currentVariable.max_value}
                        onChangeText={(text) =>
                          setCurrentVariable({ ...currentVariable, max_value: text })
                        }
                        keyboardType="numeric"
                        placeholder="100"
                      />
                    </View>
                  </View>

                  <View className="mb-4">
                    <Text className="text-gray-700 font-medium mb-2">Valor Objetivo</Text>
                    <TextInput
                      className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3"
                      value={currentVariable.target_value}
                      onChangeText={(text) =>
                        setCurrentVariable({ ...currentVariable, target_value: text })
                      }
                      keyboardType="numeric"
                      placeholder="50"
                    />
                  </View>

                  <TouchableOpacity
                    className="flex-row items-center bg-gray-50 rounded-lg p-4 mb-6"
                    onPress={() =>
                      setCurrentVariable({ ...currentVariable, mandatory: !currentVariable.mandatory })
                    }
                  >
                    <View
                      className={`w-6 h-6 rounded-md border-2 mr-3 items-center justify-center ${
                        currentVariable.mandatory ? 'bg-blue-600 border-blue-600' : 'border-gray-300'
                      }`}
                    >
                      {currentVariable.mandatory && <CustomIcon name="checkmark" size={14} color="white" />}
                    </View>
                    <Text className="text-gray-700 font-medium">Marcar como obligatoria</Text>
                  </TouchableOpacity>

                  <View className="flex-row gap-3">
                    <TouchableOpacity
                      className="flex-1 bg-blue-600 py-3 rounded-lg shadow"
                      onPress={handleAddVariable}
                    >
                      <Text className="text-white font-semibold text-center">Agregar Variable</Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      className="flex-1 bg-gray-200 py-3 rounded-lg"
                      onPress={() => {
                        setShowAddVariable(false);
                        setCurrentVariable({
                          standard_variable_id: 0,
                          min_value: '',
                          max_value: '',
                          target_value: '',
                          mandatory: false,
                        });
                      }}
                    >
                      <Text className="text-gray-700 font-semibold text-center">Cancelar</Text>
                    </TouchableOpacity>
                  </View>
                </View>
              ) : (
                <TouchableOpacity
                  className="bg-blue-600 rounded-xl py-4 px-6 flex-row items-center justify-center shadow-lg"
                  onPress={() => setShowAddVariable(true)}
                >
                  <CustomIcon name="add" size={24} color="white" />
                  <Text className="text-white font-bold text-lg ml-2">Agregar Variable</Text>
                </TouchableOpacity>
              )}
            </ScrollView>

            {/* Done Button - Fixed at bottom */}
            {!showAddVariable && (
              <View className="p-6 border-t border-gray-200 bg-white">
                <TouchableOpacity
                  className="bg-blue-600 rounded-xl py-4 px-6 shadow-lg"
                  onPress={() => {
                    setShowVariablesModal(false);
                    setEditingStepIndex(null);
                    setShowAddVariable(false);
                  }}
                >
                  <Text className="text-white font-bold text-lg text-center">Listo</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}
