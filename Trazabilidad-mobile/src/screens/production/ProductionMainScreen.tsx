import React from 'react';
import { createMaterialTopTabNavigator } from '@react-navigation/material-top-tabs';
import BatchesListScreen from './BatchesListScreen';
import CertifyBatchesScreen from './CertifyBatchesScreen';
import CertificatesScreen from './CertificatesScreen';

const Tab = createMaterialTopTabNavigator();

export default function ProductionMainScreen() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#2563EB',
        tabBarInactiveTintColor: '#6B7280',
        tabBarIndicatorStyle: {
          backgroundColor: '#2563EB',
        },
        tabBarStyle: {
          backgroundColor: '#FFFFFF',
        },
        tabBarLabelStyle: {
          fontWeight: '600',
          textTransform: 'none',
        },
      }}
    >
      <Tab.Screen 
        name="BatchesList" 
        component={BatchesListScreen} 
        options={{ title: 'Lotes' }}
      />
      <Tab.Screen 
        name="CertifyBatches" 
        component={CertifyBatchesScreen} 
        options={{ title: 'Certificar' }}
      />
      <Tab.Screen 
        name="Certificates" 
        component={CertificatesScreen} 
        options={{ title: 'Certificados' }}
      />
    </Tab.Navigator>
  );
}
