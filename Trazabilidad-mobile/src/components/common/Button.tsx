import React from 'react';
import { TouchableOpacity, Text, ActivityIndicator } from 'react-native';

interface ButtonProps {
  onPress: () => void;
  title: string;
  loading?: boolean;
  variant?: 'primary' | 'secondary' | 'outline';
  disabled?: boolean;
}

export const Button = ({ onPress, title, loading, variant = 'primary', disabled }: ButtonProps) => {
  const baseStyle = "p-4 rounded-lg items-center justify-center";
  const variantStyles = {
    primary: "bg-blue-600",
    secondary: "bg-gray-600",
    outline: "bg-transparent border border-blue-600",
  };
  const textStyles = {
    primary: "text-white font-bold text-lg",
    secondary: "text-white font-bold text-lg",
    outline: "text-blue-600 font-bold text-lg",
  };

  return (
    <TouchableOpacity
      className={`${baseStyle} ${variantStyles[variant]} ${loading ? 'opacity-70' : ''}`}
      onPress={onPress}
      disabled={loading || disabled}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'outline' ? '#2563EB' : 'white'} />
      ) : (
        <Text className={textStyles[variant]}>{title}</Text>
      )}
    </TouchableOpacity>
  );
};
