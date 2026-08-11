import React from 'react';
import { View } from 'react-native';
import { createNativeStackNavigator, NativeStackScreenProps } from '@react-navigation/native-stack';
import { createDrawerNavigator, DrawerItemList } from '@react-navigation/drawer';
import { useBranding } from '../branding/BrandingContext';
import { useAuth } from '../auth/AuthContext';
import SplashScreen from '../screens/SplashScreen';
import WelcomeScreen from '../screens/WelcomeScreen';
import LoginScreen from '../screens/LoginScreen';
import HomeScreen from '../screens/HomeScreen';
import ProfileScreen from '../screens/ProfileScreen';
import AboutScreen from '../screens/AboutScreen';
import DrawerHeader from '../components/DrawerHeader';
import Header from '../components/Header';

export type RootStackParamList = {
  Splash: undefined;
  Welcome: undefined;
  Login: undefined;
  Main: undefined;
};

const Stack = createNativeStackNavigator<RootStackParamList>();
const Drawer = createDrawerNavigator();

const DrawerNavigator: React.FC = () => {
  const { branding } = useBranding();

  return (
    <Drawer.Navigator
      screenOptions={{
        header: (props) => <Header title={props.options.headerTitle as string} />,
      }}
      drawerContent={(props) => {
        return (
          <View style={{ flex: 1 }}>
            <DrawerHeader />
            <DrawerItemList {...props} />
          </View>
        );
      }}
    >
      <Drawer.Screen name="Home" component={HomeScreen} options={{ headerTitle: branding.appName }} />
      <Drawer.Screen name="Profile" component={ProfileScreen} options={{ headerTitle: 'Profile' }} />
      <Drawer.Screen name="About" component={AboutScreen} options={{ headerTitle: 'About' }} />
    </Drawer.Navigator>
  );
};

type WelcomeProps = NativeStackScreenProps<RootStackParamList, 'Welcome'>;

const WelcomeWrapper: React.FC<WelcomeProps> = ({ navigation }) => {
  return <WelcomeScreen onContinue={() => navigation.navigate('Login')} />;
};

const AppNavigator: React.FC = () => {
  const { isLoading: brandingLoading } = useBranding();
  const { status: authStatus } = useAuth();

  if (brandingLoading || authStatus === 'loading') {
    return (
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        <Stack.Screen name="Splash" component={SplashScreen} />
      </Stack.Navigator>
    );
  }

  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      {authStatus === 'authenticated' ? (
        <Stack.Screen name="Main" component={DrawerNavigator} />
      ) : (
        <>
          <Stack.Screen name="Welcome" component={WelcomeWrapper} />
          <Stack.Screen name="Login" component={LoginScreen} />
        </>
      )}
    </Stack.Navigator>
  );
};

export default AppNavigator;
