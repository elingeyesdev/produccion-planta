import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, TouchableOpacity, Alert } from 'react-native';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { certificationApi, ProcessMachine, ProcessMachineVariable } from '../../api/certification.api';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function RecordVariablesScreen({ route, navigation }: any) {
  const { batchId, processMachine, allMachines, completedRecords } = route.params as {
    batchId: number;
    processMachine: ProcessMachine;
    allMachines: ProcessMachine[];
    completedRecords: number[];
  };

  const queryClient = useQueryClient();
  const [variables, setVariables] = useState<Record<string, string>>({});
  const [observations, setObservations] = useState('');

  const recordMutation = useMutation({
    mutationFn: certificationApi.recordVariables,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['process-machines', batchId] });
      Alert.alert('Éxito', 'Variables registradas correctamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      Alert.alert('Error', error.response?.data?.message || 'Error al registrar variables');
    },
  });

  const handleVariableChange = (varName: string, value: string) => {
    setVariables({ ...variables, [varName]: value });
  };

  const validateVariable = (variable: ProcessMachineVariable, value: string): {
    isValid: boolean;
    message?: string;
    color: string;
  } => {
    if (!value) {
      if (variable.mandatory || variable.obligatorio) {
        return { isValid: false, message: 'Obligatoria', color: '#EF4444' };
      }
      return { isValid: true, color: '#6B7280' };
    }

    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
      return { isValid: false, message: 'Valor inválido', color: '#EF4444' };
    }

    const min = variable.min_value ?? variable.valor_minimo;
    const max = variable.max_value ?? variable.valor_maximo;

    if (min !== undefined && numValue < min) {
      return { isValid: false, message: `Mínimo: ${min}`, color: '#EF4444' };
    }

    if (max !== undefined && numValue > max) {
      return { isValid: false, message: `Máximo: ${max}`, color: '#EF4444' };
    }

    return { isValid: true, message: '✓ Válido', color: '#10B981' };
  };

  const handleSubmit = () => {
    // Convert string values to numbers
    const enteredVariables: Record<string, number> = {};
    let hasErrors = false;

    processMachine.variables?.forEach((variable) => {
      // Handle both camelCase and snake_case from API
      const stdVar = (variable as any).standard_variable || variable.standardVariable;
      const varName = stdVar?.code || stdVar?.codigo || stdVar?.name || stdVar?.nombre || '';
      const value = variables[varName];

      if ((variable.mandatory || variable.obligatorio) && !value) {
        hasErrors = true;
        return;
      }

      if (value) {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) {
          hasErrors = true;
          return;
        }
        enteredVariables[varName] = numValue;
      }
    });

    if (hasErrors) {
      Alert.alert('Error', 'Por favor completa todas las variables obligatorias con valores válidos');
      return;
    }

    recordMutation.mutate({
      batch_id: batchId,
      process_machine_id: processMachine.process_machine_id || processMachine.proceso_maquina_id,
      entered_variables: enteredVariables,
      observations: observations || undefined,
    });
  };

  const allValid = processMachine.variables?.every((variable) => {
    const stdVar = (variable as any).standard_variable || variable.standardVariable;
    const varName = stdVar?.code || stdVar?.codigo || stdVar?.name || stdVar?.nombre || '';
    const validation = validateVariable(variable, variables[varName] || '');
    return validation.isValid;
  }) ?? true;

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1">
        {/* Machine Info Header */}
        <View className="bg-blue-600 p-4">
          <View className="flex-row items-center mb-2">
            <View className="bg-white w-10 h-10 rounded-full items-center justify-center mr-3">
              <Text className="text-blue-600 font-bold text-lg">{processMachine.step_order}</Text>
            </View>
            <View className="flex-1">
              <Text className="text-white font-bold text-lg">{processMachine.name}</Text>
              <Text className="text-blue-100 text-sm">{processMachine.machine?.name}</Text>
            </View>
          </View>
        </View>

        {/* Variables Form */}
        <View className="p-4">
          {processMachine.variables && processMachine.variables.length > 0 ? (
            processMachine.variables.map((variable, index) => {
              const stdVar = (variable as any).standard_variable || variable.standardVariable;
              const varName = stdVar?.code || stdVar?.codigo || stdVar?.name || stdVar?.nombre || '';
              const value = variables[varName] || '';
              const validation = validateVariable(variable, value);

              return (
                <View key={index} className="bg-white rounded-xl p-4 mb-4 shadow-sm border border-gray-100">
                  {/* Variable Header */}
                  <View className="flex-row justify-between items-start mb-3">
                    <View className="flex-1">
                      <Text className="text-base font-bold text-gray-900">
                        {stdVar?.name || stdVar?.nombre}
                        {(variable.mandatory || variable.obligatorio) && <Text className="text-red-600"> *</Text>}
                      </Text>
                      {(stdVar?.unit || stdVar?.unidad) && (
                        <Text className="text-sm text-gray-600">Unidad: {stdVar?.unit || stdVar?.unidad}</Text>
                      )}
                    </View>
                    {validation.message && (
                      <View className={`px-2 py-1 rounded ${validation.isValid ? 'bg-green-100' : 'bg-red-100'}`}>
                        <Text className={`text-xs font-semibold ${validation.isValid ? 'text-green-700' : 'text-red-700'}`}>
                          {validation.message}
                        </Text>
                      </View>
                    )}
                  </View>

                  {/* Range Indicators */}
                  <View className="bg-gray-50 rounded-lg p-3 mb-3">
                    <View className="flex-row justify-between">
                      {(variable.min_value !== undefined || variable.valor_minimo !== undefined) && (
                        <View>
                          <Text className="text-xs text-gray-500">Mínimo</Text>
                          <Text className="text-sm font-bold text-gray-900">{variable.min_value ?? variable.valor_minimo}</Text>
                        </View>
                      )}
                      {(variable.target_value !== undefined || variable.valor_objetivo !== undefined) && (
                        <View>
                          <Text className="text-xs text-gray-500">Objetivo</Text>
                          <Text className="text-sm font-bold text-blue-600">{variable.target_value ?? variable.valor_objetivo}</Text>
                        </View>
                      )}
                      {(variable.max_value !== undefined || variable.valor_maximo !== undefined) && (
                        <View>
                          <Text className="text-xs text-gray-500">Máximo</Text>
                          <Text className="text-sm font-bold text-gray-900">{variable.max_value ?? variable.valor_maximo}</Text>
                        </View>
                      )}
                    </View>
                  </View>

                  {/* Input Field */}
                  <TextInput
                    className={`bg-white border-2 rounded-lg px-4 py-3 text-lg font-semibold ${
                      value ? (validation.isValid ? 'border-green-500' : 'border-red-500') : 'border-gray-300'
                    }`}
                    value={value}
                    onChangeText={(text) => handleVariableChange(varName, text)}
                    placeholder="Ingrese valor"
                    keyboardType="numeric"
                    placeholderTextColor="#9CA3AF"
                  />
                </View>
              );
            })
          ) : (
            <View className="bg-white rounded-xl p-8 items-center">
              <CustomIcon name="alert" size={48} color="#D1D5DB" />
              <Text className="text-gray-500 text-center mt-4">
                No hay variables configuradas para este paso
              </Text>
            </View>
          )}

          {/* Observations */}
          <View className="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <Text className="text-base font-bold text-gray-900 mb-2">Observaciones</Text>
            <TextInput
              className="bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 min-h-24"
              value={observations}
              onChangeText={setObservations}
              placeholder="Agregar observaciones (opcional)"
              multiline
              numberOfLines={4}
              textAlignVertical="top"
              placeholderTextColor="#9CA3AF"
            />
          </View>
        </View>
      </ScrollView>

      {/* Submit Button */}
      <View className="p-4 bg-white border-t border-gray-200">
        <TouchableOpacity
          className={`py-4 rounded-xl shadow-lg ${allValid ? 'bg-blue-600' : 'bg-gray-300'}`}
          onPress={handleSubmit}
          disabled={!allValid || recordMutation.isPending}
        >
          <Text className="text-white font-bold text-lg text-center">
            {recordMutation.isPending ? 'Guardando...' : 'Guardar Variables'}
          </Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}
