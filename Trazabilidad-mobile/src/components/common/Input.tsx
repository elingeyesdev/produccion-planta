import React from 'react';
import { View, Text, TextInput, TextInputProps } from 'react-native';
import { Controller, Control } from 'react-hook-form';

interface InputProps extends TextInputProps {
  control: Control<any>;
  name: string;
  label: string;
  error?: string;
}

export const Input = ({ control, name, label, error, ...props }: InputProps) => {
  return (
    <View className="mb-4">
      <Text className="text-gray-700 mb-1 font-medium">{label}</Text>
      <Controller
        control={control}
        name={name}
        render={({ field: { onChange, onBlur, value } }) => (
          <TextInput
            className={`border rounded-lg p-3 bg-white ${
              error ? 'border-red-500' : 'border-gray-300'
            }`}
            onBlur={onBlur}
            onChangeText={onChange}
            value={value}
            {...props}
          />
        )}
      />
      {error && <Text className="text-red-500 text-sm mt-1">{error}</Text>}
    </View>
  );
};
