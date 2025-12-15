import React, { useState, useEffect } from 'react';
import { View, Text, SafeAreaView, ScrollView, TextInput, TouchableOpacity, Alert, Platform, Modal } from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Picker } from '@react-native-picker/picker';
import { customersApi, CreateOrderPayload } from '../../api/customers.api';
import { productsApi, Product } from '../../api/products.api';
import { CustomIcon } from '../../components/common/CustomIcon';

interface SelectedProduct {
  index: number;
  productId: number;
  productName: string;
  quantity: number;
  unit: string;
  precio_unitario: number;
}

interface Destination {
  index: number;
  address: string;
  reference: string;
  contact_name: string;
  contact_phone: string;
  delivery_instructions: string;
  assignments: { [productIndex: number]: number };
}

export default function CreateOrderScreen({ navigation }: any) {
  const queryClient = useQueryClient();
  const [step, setStep] = useState(1);
  
  // Basic Info State
  const [basicInfo, setBasicInfo] = useState({
    name: '',
    description: '',
    priority: 1,
    delivery_date: undefined as string | undefined,
  });
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date());

  // Products State
  const [selectedProducts, setSelectedProducts] = useState<SelectedProduct[]>([]);
  const [productCounter, setProductCounter] = useState(0);

  // Destinations State
  const [destinations, setDestinations] = useState<Destination[]>([]);
  const [destinationCounter, setDestinationCounter] = useState(0);

  // Fetch Products
  const { data: products = [] } = useQuery({
    queryKey: ['products'],
    queryFn: productsApi.getProducts,
  });

  const createMutation = useMutation({
    mutationFn: (data: CreateOrderPayload) => customersApi.createCustomerOrder(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customerOrders'] });
      Alert.alert('Éxito', 'Pedido creado exitosamente', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    },
    onError: (error: any) => {
      console.error('Create order error:', error);
      Alert.alert('Error', error.response?.data?.message || 'Error al crear pedido');
    },
  });

  const handleDateChange = (event: any, date?: Date) => {
    setShowDatePicker(Platform.OS === 'ios');
    if (date) {
      setSelectedDate(date);
      setBasicInfo({
        ...basicInfo,
        delivery_date: date.toISOString().split('T')[0],
      });
    }
  };

  const addProduct = () => {
    const newIndex = productCounter + 1;
    setProductCounter(newIndex);
    setSelectedProducts([
      ...selectedProducts,
      { index: newIndex, productId: 0, productName: '', quantity: 0, unit: '', precio_unitario: 0 }
    ]);
  };

  const removeProduct = (index: number) => {
    if (selectedProducts.length <= 1) {
      Alert.alert('Error', 'Debe haber al menos un producto');
      return;
    }
    setSelectedProducts(selectedProducts.filter(p => p.index !== index));
    // Also remove assignments for this product
    setDestinations(destinations.map(d => {
      const newAssignments = { ...d.assignments };
      delete newAssignments[index];
      return { ...d, assignments: newAssignments };
    }));
  };

  const updateProduct = (index: number, field: keyof SelectedProduct, value: any) => {
    const updatedProducts = selectedProducts.map(p => {
      if (p.index === index) {
        const updates: any = { [field]: value };
        if (field === 'productId') {
          const product = products.find((prod: Product) => prod.producto_id === value);
          if (product) {
            updates.productName = product.nombre;
            updates.unit = product.unit?.codigo || 'Unid';
            updates.precio_unitario = product.precio_unitario || 0;
          }
        }
        return { ...p, ...updates };
      }
      return p;
    });
    setSelectedProducts(updatedProducts);
  };

  const addDestination = () => {
    const newIndex = destinationCounter + 1;
    setDestinationCounter(newIndex);
    setDestinations([
      ...destinations,
      {
        index: newIndex,
        address: '',
        reference: '',
        contact_name: '',
        contact_phone: '',
        delivery_instructions: '',
        assignments: {}
      }
    ]);
  };

  const removeDestination = (index: number) => {
    setDestinations(destinations.filter(d => d.index !== index));
  };

  const updateDestination = (index: number, field: keyof Destination, value: any) => {
    setDestinations(destinations.map(d => 
      d.index === index ? { ...d, [field]: value } : d
    ));
  };

  const updateAssignment = (destIndex: number, prodIndex: number, quantity: number) => {
    setDestinations(destinations.map(d => {
      if (d.index === destIndex) {
        return {
          ...d,
          assignments: { ...d.assignments, [prodIndex]: quantity }
        };
      }
      return d;
    }));
  };

  const validateStep1 = () => {
    if (!basicInfo.name.trim()) {
      Alert.alert('Error', 'El nombre del pedido es requerido');
      return false;
    }
    if (selectedProducts.length === 0) {
      Alert.alert('Error', 'Debe agregar al menos un producto');
      return false;
    }
    for (const p of selectedProducts) {
      if (!p.productId) {
        Alert.alert('Error', 'Seleccione todos los productos');
        return false;
      }
      if (!p.quantity || p.quantity <= 0) {
        Alert.alert('Error', `Ingrese una cantidad válida para ${p.productName || 'el producto'}`);
        return false;
      }
    }
    return true;
  };

  const validateStep2 = () => {
    if (destinations.length === 0) {
      Alert.alert('Error', 'Debe agregar al menos un destino');
      return false;
    }
    for (const d of destinations) {
      if (!d.address.trim()) {
        Alert.alert('Error', `La dirección es requerida para el destino ${d.index}`);
        return false;
      }
      // Check if destination has any products assigned
      const totalAssigned = Object.values(d.assignments).reduce((a, b) => a + b, 0);
      if (totalAssigned <= 0) {
        Alert.alert('Error', `Asigne productos al destino ${d.index}`);
        return false;
      }
    }
    
    // Check total assignments vs total quantity
    for (const p of selectedProducts) {
      let totalAssigned = 0;
      destinations.forEach(d => {
        totalAssigned += (d.assignments[p.index] || 0);
      });
      
      if (totalAssigned > p.quantity) {
        Alert.alert('Error', `La cantidad asignada de ${p.productName} (${totalAssigned}) excede el total (${p.quantity})`);
        return false;
      }
      if (totalAssigned < p.quantity) {
        Alert.alert('Error', `Falta asignar ${p.quantity - totalAssigned} unidades de ${p.productName}`);
        return false;
      }
    }
    
    return true;
  };

  const handleSubmit = () => {
    if (!validateStep2()) return;

    // Create a map of product internal ID (index) to array index
    const productIndexMap = new Map();
    selectedProducts.forEach((p, idx) => {
      productIndexMap.set(p.index, idx);
    });

    // Construct payload matching the web form structure - using Spanish field names
    const payload: CreateOrderPayload = {
      cliente_id: 1, // Default customer for now
      nombre: basicInfo.name,
      descripcion: basicInfo.description,
      fecha_entrega: basicInfo.delivery_date,
      products: selectedProducts.map(p => ({
        producto_id: p.productId,
        cantidad: p.quantity,
        observaciones: '' // Optional
      })),
      destinations: destinations.map(d => ({
        direccion: d.address,
        referencia: d.reference,
        nombre_contacto: d.contact_name,
        telefono_contacto: d.contact_phone,
        instrucciones_entrega: d.delivery_instructions,
        latitud: null, // Map not implemented
        longitud: null,
        products: Object.entries(d.assignments).map(([prodIndex, qty]) => {
          const arrayIndex = productIndexMap.get(parseInt(prodIndex));
          if (arrayIndex === undefined) return null;
          return {
            order_product_index: arrayIndex,
            cantidad: qty as number
          };
        }).filter((item): item is { order_product_index: number; cantidad: number } => item !== null && item.cantidad > 0)
      }))
    };

    createMutation.mutate(payload);
  };

  // Initialize with one product
  useEffect(() => {
    if (selectedProducts.length === 0) {
      addProduct();
    }
  }, []);

  const getRemainingQuantity = (prodIndex: number) => {
    const product = selectedProducts.find(p => p.index === prodIndex);
    if (!product) return 0;
    
    let assigned = 0;
    destinations.forEach(d => {
      assigned += (d.assignments[prodIndex] || 0);
    });
    
    return product.quantity - assigned;
  };

  return (
    <SafeAreaView className="flex-1 bg-gray-50">
      <ScrollView className="flex-1 p-4">
        <Text className="text-2xl font-bold text-gray-900 mb-6">
          {step === 1 ? 'Paso 1: Información y Productos' : 'Paso 2: Destinos'}
        </Text>

        {step === 1 ? (
          <>
            {/* Basic Info */}
            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Nombre del Pedido <Text className="text-red-600">*</Text></Text>
              <TextInput
                className="bg-white border-2 border-gray-300 rounded-lg px-4 py-3"
                value={basicInfo.name}
                onChangeText={(text) => setBasicInfo({ ...basicInfo, name: text })}
                placeholder="Ej: Pedido Enero 2025"
              />
            </View>

            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Prioridad</Text>
              <View className="flex-row gap-2">
                {[1, 5, 10].map((p) => (
                  <TouchableOpacity
                    key={p}
                    className={`flex-1 py-3 rounded-lg border-2 ${basicInfo.priority === p ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'}`}
                    onPress={() => setBasicInfo({ ...basicInfo, priority: p })}
                  >
                    <Text className={`text-center font-semibold ${basicInfo.priority === p ? 'text-white' : 'text-gray-700'}`}>
                      {p === 1 ? 'Normal' : p === 5 ? 'Alta' : 'Urgente'}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>

            <View className="mb-4">
              <Text className="text-gray-700 font-medium mb-2">Fecha de Entrega</Text>
              <TouchableOpacity
                className="bg-white border-2 border-gray-300 rounded-lg px-4 py-3 flex-row items-center justify-between"
                onPress={() => setShowDatePicker(true)}
              >
                <Text className={basicInfo.delivery_date ? 'text-gray-900' : 'text-gray-400'}>
                  {basicInfo.delivery_date || 'Seleccionar fecha'}
                </Text>
                <CustomIcon name="calendar" size={20} color="#6B7280" />
              </TouchableOpacity>
            </View>

            {showDatePicker && (
              <DateTimePicker
                value={selectedDate}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                onChange={handleDateChange}
                minimumDate={new Date()}
              />
            )}

            <View className="mb-6">
              <Text className="text-gray-700 font-medium mb-2">Descripción</Text>
              <TextInput
                className="bg-white border-2 border-gray-300 rounded-lg px-4 py-3"
                value={basicInfo.description}
                onChangeText={(text) => setBasicInfo({ ...basicInfo, description: text })}
                multiline
                numberOfLines={3}
              />
            </View>

            <Text className="text-xl font-bold text-gray-900 mb-4">Productos</Text>
            
            {selectedProducts.map((item, idx) => (
              <View key={item.index} className="bg-white p-4 rounded-xl shadow-sm mb-4 border border-gray-200">
                <View className="flex-row justify-between items-center mb-2">
                  <Text className="font-bold text-gray-700">Producto {idx + 1}</Text>
                  <TouchableOpacity onPress={() => removeProduct(item.index)}>
                    <CustomIcon name="trash-2" size={20} color="#EF4444" />
                  </TouchableOpacity>
                </View>
                
                <View className="mb-3 border border-gray-300 rounded-lg">
                  <Picker
                    selectedValue={item.productId}
                    onValueChange={(val) => updateProduct(item.index, 'productId', val)}
                  >
                    <Picker.Item label="Seleccione un producto" value={0} />
                    {products.map((p: Product) => (
                      <Picker.Item 
                        key={p.producto_id} 
                        label={`${p.nombre}${p.precio_unitario ? ` - Bs. ${Number(p.precio_unitario).toFixed(2)}` : ''}`} 
                        value={p.producto_id} 
                      />
                    ))}
                  </Picker>
                </View>

                <View className="flex-row gap-4 mb-3">
                  <View className="flex-1">
                    <Text className="text-gray-600 mb-1">Cantidad</Text>
                    <TextInput
                      className="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2"
                      value={item.quantity.toString()}
                      onChangeText={(text) => updateProduct(item.index, 'quantity', parseInt(text) || 0)}
                      keyboardType="numeric"
                    />
                  </View>
                  <View className="flex-1">
                    <Text className="text-gray-600 mb-1">Unidad</Text>
                    <TextInput
                      className="bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-gray-500"
                      value={item.unit}
                      editable={false}
                    />
                  </View>
                </View>

                {/* Price Fields */}
                <View className="flex-row gap-4">
                  <View className="flex-1">
                    <Text className="text-gray-600 mb-1">Precio Unitario</Text>
                    <View className="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                      <Text className="text-green-700 font-bold">
                        Bs. {(Number(item.precio_unitario) || 0).toFixed(2)}
                      </Text>
                    </View>
                  </View>
                  <View className="flex-1">
                    <Text className="text-gray-600 mb-1">Precio Total</Text>
                    <View className="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                      <Text className="text-blue-700 font-bold">
                        Bs. {((Number(item.precio_unitario) || 0) * (item.quantity || 0)).toFixed(2)}
                      </Text>
                    </View>
                  </View>
                </View>
              </View>
            ))}

            <TouchableOpacity
              className="bg-green-500 py-3 rounded-lg mb-4 flex-row justify-center items-center"
              onPress={addProduct}
            >
              <CustomIcon name="plus" size={20} color="white" />
              <Text className="text-white font-bold ml-2">Agregar Producto</Text>
            </TouchableOpacity>

            {/* Order Summary Section */}
            <View className="bg-gray-100 p-4 rounded-xl mb-8">
              <View className="flex-row items-center mb-3">
                <CustomIcon name="calculator" size={20} color="#4B5563" />
                <Text className="text-lg font-bold text-gray-700 ml-2">Resumen del Pedido</Text>
              </View>
              
              <View className="flex-row justify-between mb-2">
                <Text className="text-gray-600">Total de Productos:</Text>
                <Text className="font-bold text-gray-900">
                  {selectedProducts.filter(p => p.productId > 0).length}
                </Text>
              </View>
              
              <View className="flex-row justify-between mb-2">
                <Text className="text-gray-600">Cantidad Total:</Text>
                <Text className="font-bold text-gray-900">
                  {selectedProducts.reduce((sum, p) => sum + p.quantity, 0)}
                </Text>
              </View>
              
              <View className="bg-blue-100 p-3 rounded-lg mt-2">
                <View className="flex-row justify-between items-center">
                  <View className="flex-row items-center">
                    <CustomIcon name="cash" size={20} color="#1E40AF" />
                    <Text className="text-blue-800 font-bold ml-1">Total del Pedido:</Text>
                  </View>
                  <Text className="text-blue-900 font-bold text-xl">
                    Bs. {selectedProducts.reduce((sum, p) => sum + (p.precio_unitario * p.quantity), 0).toFixed(2)}
                  </Text>
                </View>
              </View>
            </View>
          </>
        ) : (
          <>
            {/* Step 2: Destinations */}
            {destinations.map((dest, idx) => (
              <View key={dest.index} className="bg-white p-4 rounded-xl shadow-sm mb-4 border border-gray-200">
                <View className="flex-row justify-between items-center mb-2">
                  <Text className="font-bold text-gray-700">Destino {idx + 1}</Text>
                  <TouchableOpacity onPress={() => removeDestination(dest.index)}>
                    <CustomIcon name="trash-2" size={20} color="#EF4444" />
                  </TouchableOpacity>
                </View>

                <TextInput
                  className="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 mb-3"
                  placeholder="Dirección *"
                  value={dest.address}
                  onChangeText={(text) => updateDestination(dest.index, 'address', text)}
                />
                
                <View className="flex-row gap-2 mb-3">
                  <TextInput
                    className="flex-1 bg-gray-50 border border-gray-300 rounded-lg px-3 py-2"
                    placeholder="Contacto"
                    value={dest.contact_name}
                    onChangeText={(text) => updateDestination(dest.index, 'contact_name', text)}
                  />
                  <TextInput
                    className="flex-1 bg-gray-50 border border-gray-300 rounded-lg px-3 py-2"
                    placeholder="Teléfono"
                    value={dest.contact_phone}
                    onChangeText={(text) => updateDestination(dest.index, 'contact_phone', text)}
                  />
                </View>

                <Text className="font-semibold text-gray-700 mb-2">Asignar Productos:</Text>
                {selectedProducts.map(prod => {
                  const assignedHere = dest.assignments[prod.index] || 0;
                  const remaining = getRemainingQuantity(prod.index) + assignedHere; // Remaining available + what's already here
                  
                  return (
                    <View key={prod.index} className="flex-row items-center justify-between mb-2 bg-gray-50 p-2 rounded">
                      <View className="flex-1">
                        <Text className="text-sm font-medium">{prod.productName}</Text>
                        <Text className="text-xs text-gray-500">Disp: {remaining}</Text>
                      </View>
                      <TextInput
                        className="bg-white border border-gray-300 rounded w-20 px-2 py-1 text-right"
                        value={assignedHere.toString()}
                        onChangeText={(text) => {
                          const val = parseInt(text) || 0;
                          if (val <= remaining) {
                            updateAssignment(dest.index, prod.index, val);
                          }
                        }}
                        keyboardType="numeric"
                      />
                    </View>
                  );
                })}
              </View>
            ))}

            <TouchableOpacity
              className="bg-green-500 py-3 rounded-lg mb-8 flex-row justify-center items-center"
              onPress={addDestination}
            >
              <CustomIcon name="plus" size={20} color="white" />
              <Text className="text-white font-bold ml-2">Agregar Destino</Text>
            </TouchableOpacity>
          </>
        )}
      </ScrollView>

      {/* Footer Buttons */}
      <View className="p-4 bg-white border-t border-gray-200 flex-row gap-4">
        {step === 2 && (
          <TouchableOpacity
            className="flex-1 bg-gray-500 py-4 rounded-xl shadow-lg"
            onPress={() => setStep(1)}
          >
            <Text className="text-white font-bold text-lg text-center">Anterior</Text>
          </TouchableOpacity>
        )}
        
        <TouchableOpacity
          className="flex-1 bg-blue-600 py-4 rounded-xl shadow-lg"
          onPress={() => {
            if (step === 1) {
              if (validateStep1()) {
                setStep(2);
                if (destinations.length === 0) addDestination();
              }
            } else {
              handleSubmit();
            }
          }}
          disabled={createMutation.isPending}
        >
          <Text className="text-white font-bold text-lg text-center">
            {step === 1 ? 'Siguiente' : (createMutation.isPending ? 'Creando...' : 'Crear Pedido')}
          </Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}
