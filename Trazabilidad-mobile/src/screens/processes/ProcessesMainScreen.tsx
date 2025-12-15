import React from 'react';
import { createMaterialTopTabNavigator } from '@react-navigation/material-top-tabs';
import MachinesScreen from './MachinesScreen';
import ProcessesListScreen from './ProcessesListScreen';
import StandardVariablesScreen from './StandardVariablesScreen';

const Tab = createMaterialTopTabNavigator();

export default function ProcessesMainScreen() {
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
        name="Machines" 
        component={MachinesScreen} 
        options={{ title: 'Máquinas' }}
      />
      <Tab.Screen 
        name="ProcessesList" 
        component={ProcessesListScreen} 
        options={{ title: 'Procesos' }}
      />
      <Tab.Screen 
        name="Variables" 
        component={StandardVariablesScreen} 
        options={{ title: 'Variables' }}
      />
    </Tab.Navigator>
  );
}
