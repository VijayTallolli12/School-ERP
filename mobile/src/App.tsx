import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { BrandingProvider } from './branding/BrandingContext';
import AppNavigator from './navigation/AppNavigator';

const App: React.FC = () => {
  return (
    <BrandingProvider>
      <NavigationContainer>
        <AppNavigator />
      </NavigationContainer>
    </BrandingProvider>
  );
};

export default App;