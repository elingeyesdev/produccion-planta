import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';

interface LegacyBatch {
  batch_id: number;
  product_name: string;
  status: 'pending' | 'in_progress' | 'completed' | 'failed';
  start_date: string;
  end_date?: string;
  quantity: number;
  operator_name: string;
}

interface BatchCardProps {
  batch: LegacyBatch;
  onPress: () => void;
}

export const BatchCard = ({ batch, onPress }: BatchCardProps) => {
  const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  };

  return (
    <TouchableOpacity 
      onPress={onPress}
      className="bg-white p-4 rounded-lg shadow-sm mb-3 border border-gray-100"
    >
      <View className="flex-row justify-between items-start mb-2">
        <Text className="text-lg font-bold text-gray-900 flex-1 mr-2" numberOfLines={2}>
          {batch.product_name}
        </Text>
        <View className={`px-2 py-1 rounded-full ${statusColors[batch.status].split(' ')[0]} shrink-0`}>
          <Text className={`text-xs font-medium ${statusColors[batch.status].split(' ')[1]} whitespace-nowrap`}>
            {batch.status === 'pending' ? 'PENDIENTE' : batch.status === 'in_progress' ? 'EN PROGRESO' : batch.status === 'completed' ? 'COMPLETADO' : 'FALLIDO'}
          </Text>
        </View>
      </View>
      
      <View className="flex-row justify-between mt-2">
        <View>
          <Text className="text-gray-500 text-xs">ID de Lote</Text>
          <Text className="font-medium">#{batch.batch_id}</Text>
        </View>
        <View>
          <Text className="text-gray-500 text-xs">Cantidad</Text>
          <Text className="font-medium">{batch.quantity} unidades</Text>
        </View>
        <View>
          <Text className="text-gray-500 text-xs">Fecha de Inicio</Text>
          <Text className="font-medium">{new Date(batch.start_date).toLocaleDateString()}</Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};
