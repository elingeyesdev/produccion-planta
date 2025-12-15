import React from 'react';
import { View, Text, SafeAreaView, Alert, ScrollView } from 'react-native';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuthStore } from '../../store/authStore';
import { authApi } from '../../api/auth.api';
import { Input } from '../../components/common/Input';
import { Button } from '../../components/common/Button';

const registerSchema = z.object({
  first_name: z.string().min(1, 'First name is required'),
  last_name: z.string().min(1, 'Last name is required'),
  username: z.string().min(3, 'Username must be at least 3 characters'),
  email: z.string().email('Invalid email address'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  confirmPassword: z.string().min(6, 'Confirm password must be at least 6 characters'),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords don't match",
  path: ["confirmPassword"],
});

type RegisterFormData = z.infer<typeof registerSchema>;

export default function RegisterScreen({ navigation }: any) {
  const [isLoading, setIsLoading] = React.useState(false);
  const { login } = useAuthStore();
  
  const { control, handleSubmit, formState: { errors } } = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = async (data: RegisterFormData) => {
    try {
      setIsLoading(true);
      // Register the user - use Spanish field names
      await authApi.register({
        nombre: data.first_name,
        apellido: data.last_name,
        usuario: data.username,
        email: data.email,
        password: data.password,
      });

      // Auto login after registration
      await login(data.username, data.password);
    } catch (error: any) {
      Alert.alert('Registration Failed', error.response?.data?.message || 'An error occurred');
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView contentContainerStyle={{ padding: 24 }}>
        <View className="mb-8 items-center">
          <Text className="text-3xl font-bold text-gray-900">Create Account</Text>
          <Text className="text-gray-500 mt-2">Sign up to get started</Text>
        </View>

        <Input
          control={control}
          name="first_name"
          label="First Name"
          placeholder="John"
          error={errors.first_name?.message}
        />

        <Input
          control={control}
          name="last_name"
          label="Last Name"
          placeholder="Doe"
          error={errors.last_name?.message}
        />

        <Input
          control={control}
          name="username"
          label="Username"
          placeholder="johndoe"
          autoCapitalize="none"
          error={errors.username?.message}
        />

        <Input
          control={control}
          name="email"
          label="Email"
          placeholder="john@example.com"
          keyboardType="email-address"
          autoCapitalize="none"
          error={errors.email?.message}
        />

        <Input
          control={control}
          name="password"
          label="Password"
          placeholder="******"
          secureTextEntry
          error={errors.password?.message}
        />

        <Input
          control={control}
          name="confirmPassword"
          label="Confirm Password"
          placeholder="******"
          secureTextEntry
          error={errors.confirmPassword?.message}
        />

        <View className="mt-6">
          <Button
            title="Sign Up"
            onPress={handleSubmit(onSubmit)}
            loading={isLoading}
          />
        </View>

        <View className="mt-4 flex-row justify-center">
          <Text className="text-gray-600">Already have an account? </Text>
          <Text 
            className="text-blue-600 font-bold"
            onPress={() => navigation.navigate('Login')}
          >
            Sign In
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
