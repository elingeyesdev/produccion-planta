import React, { useState } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, Alert, TouchableOpacity, Image, ActivityIndicator } from 'react-native';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import * as ImagePicker from 'expo-image-picker';
import { machinesApi } from '../../api/machines.api';
import { uploadApi } from '../../api/upload.api';
import { Button } from '../../components/common/Button';
import { CustomIcon } from '../../components/common/CustomIcon';

export default function CreateMachineScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    image_url: '',
  });
  const [uploading, setUploading] = useState(false);

  const createMutation = useMutation({
    mutationFn: machinesApi.createMachine,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['machines'] });
      Alert.alert('Éxito', 'Máquina creada exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Machine creation error:', error);
      const errorMessage = error.response?.data?.message || 'Error al crear máquina';
      Alert.alert('Error', errorMessage);
    },
  });

  const pickImage = async () => {
    // Request permission
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permiso denegado', 'Se necesita permiso para acceder a la galería');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled) {
      handleImageUpload(result.assets[0].uri);
    }
  };

  const handleImageUpload = async (uri: string) => {
    setUploading(true);
    try {
      const response = await uploadApi.uploadImage(uri);
      if (response.success && response.imageUrl) {
        setFormData(prev => ({ ...prev, image_url: response.imageUrl }));
      } else {
        Alert.alert('Error', 'No se pudo subir la imagen');
      }
    } catch (error) {
      Alert.alert('Error', 'Error al subir la imagen');
    } finally {
      setUploading(false);
    }
  };

  const handleSubmit = () => {
    if (!formData.name.trim()) {
      Alert.alert('Error', 'El nombre es obligatorio');
      return;
    }

    createMutation.mutate({
      name: formData.name,
      description: formData.description || undefined,
      image_url: formData.image_url || undefined,
      active: true,
    });
  };

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">Nueva Máquina</Text>

        {/* Name */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Nombre *</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.name}
            onChangeText={(text) => setFormData({ ...formData, name: text })}
            placeholder="Ej: Extrusora A"
          />
        </View>

        {/* Description */}
        <View className="mb-4">
          <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.description}
            onChangeText={(text) => setFormData({ ...formData, description: text })}
            placeholder="Detalles de la máquina"
            multiline
            numberOfLines={3}
            textAlignVertical="top"
          />
        </View>

        {/* Image Selection */}
        <View className="mb-6">
          <Text className="text-gray-700 font-medium mb-2">Imagen</Text>
          
          {formData.image_url ? (
            <View className="mb-3">
              <Image 
                source={{ uri: formData.image_url }} 
                className="w-full h-48 rounded-lg bg-gray-200 mb-2"
                resizeMode="cover"
              />
              <TouchableOpacity 
                onPress={() => setFormData({ ...formData, image_url: '' })}
                className="absolute top-2 right-2 bg-red-500 p-2 rounded-full"
              >
                <CustomIcon name="trash" size={20} color="white" />
              </TouchableOpacity>
            </View>
          ) : (
            <TouchableOpacity 
              onPress={pickImage}
              className="bg-white border border-dashed border-gray-300 rounded-lg h-48 justify-center items-center mb-3"
              disabled={uploading}
            >
              {uploading ? (
                <ActivityIndicator size="large" color="#2563EB" />
              ) : (
                <>
                  <CustomIcon name="add-circle" size={40} color="#9CA3AF" />
                  <Text className="text-gray-500 mt-2">Seleccionar imagen de galería</Text>
                </>
              )}
            </TouchableOpacity>
          )}

          <Text className="text-gray-500 text-xs mb-2">O ingresar URL manualmente:</Text>
          <TextInput
            className="bg-white border border-gray-300 rounded-lg px-4 py-3"
            value={formData.image_url}
            onChangeText={(text) => setFormData({ ...formData, image_url: text })}
            placeholder="https://ejemplo.com/imagen.jpg"
            autoCapitalize="none"
          />
        </View>

        {/* Buttons */}
        <View className="space-y-3 pb-10">
          <Button
            title={createMutation.isPending ? "Guardando..." : "Guardar"}
            onPress={handleSubmit}
            variant="primary"
            disabled={createMutation.isPending || uploading}
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
