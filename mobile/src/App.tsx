import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { BrandingProvider } from './branding/BrandingContext';
import { AuthProvider, useAuth } from './auth/AuthContext';
import AppNavigator from './navigation/AppNavigator';

/**
 * Branding is bound to the authenticated user's school so the logo/theme
 * reflect the correct tenant after sign-in (public default until then).
 */
const Root: React.FC = () => {
  const { schoolId } = useAuth();

  return (
    <BrandingProvider schoolId={schoolId ?? undefined}>
      <NavigationContainer>
        <AppNavigator />
      </NavigationContainer>
    </BrandingProvider>
  );
};

const App: React.FC = () => {
  return (
    <AuthProvider>
      <Root />
    </AuthProvider>
  );
};

export default App;
