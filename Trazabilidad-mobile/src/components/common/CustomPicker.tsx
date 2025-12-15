import React from 'react';
import { View, Text, Platform } from 'react-native';
import { Picker } from '@react-native-picker/picker';

interface CustomPickerProps {
  label?: string;
  selectedValue: any;
  onValueChange: (value: any) => void;
  items: Array<{ label: string; value: any }>;
  placeholder?: string;
  required?: boolean;
}

export function CustomPicker({
  label,
  selectedValue,
  onValueChange,
  items,
  placeholder = 'Seleccionar...',
  required = false,
}: CustomPickerProps) {
  return (
    <View>
      {label && (
        <Text className="text-gray-700 font-medium mb-2">
          {label}
          {required && <Text className="text-red-600"> *</Text>}
        </Text>
      )}
      <View className="bg-white  rounded-lg overflow-hidden">
        <Picker
          selectedValue={selectedValue}
          onValueChange={onValueChange}
          style={{
            height: Platform.OS === 'ios' ? 180 : 50,
            color: '#1F2937', // gray-900
          }}
          itemStyle={{
            color: '#1F2937',
            fontSize: 16,
          }}
        >
          <Picker.Item 
            label={placeholder} 
            value={0} 
            color="#9CA3AF" // gray-400 for placeholder
          />
          {items.map((item, index) => (
            <Picker.Item
              key={index}
              label={item.label}
              value={item.value}
              color="#1F2937" // gray-900
            />
          ))}
        </Picker>
      </View>
    </View>
  );
}
