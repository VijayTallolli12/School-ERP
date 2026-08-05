import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  Dimensions,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const { width } = Dimensions.get('window');

const SplashScreen: React.FC = () => {
  const { branding, theme, isLoading } = useBranding();

  if (isLoading) {
    return (
      <View style={[styles.container, { backgroundColor: theme.primary }]}> 
        <View style={styles.loaderContainer}>
          <View style={[styles.loader, { borderColor: theme.primaryLight }]}> 
            <View style={[styles.loaderFill, { borderTopColor: theme.primary }]} /> 
          </View>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: theme.primary }]}> 
      {branding.schoolLogo ? (
        <Image source={{ uri: branding.schoolLogo }} style={styles.logo} />
      ) : (
        <View style={[styles.logoPlaceholder, { backgroundColor: theme.primaryLight }]}> 
          <Text style={[styles.logoText, { color: theme.primary }]}>
            {branding.schoolName.charAt(0).toUpperCase()}
          </Text>
        </View>
      )}
      <Text style={[styles.appName, { color: '#ffffff' }]}>
        {branding.appName}
      </Text>
      <Text style={[styles.schoolName, { color: 'rgba(255,255,255,0.85)' }]}>
        {branding.schoolName}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loaderContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  loader: {
    width: 48,
    height: 48,
    borderRadius: 24,
    borderWidth: 4,
  },
  loaderFill: {
    width: 48,
    height: 48,
    borderRadius: 24,
    borderWidth: 4,
    borderTopColor: 'transparent',
    borderLeftColor: 'transparent',
    borderRightColor: 'transparent',
  },
  logo: {
    width: 100,
    height: 100,
    resizeMode: 'contain',
    marginBottom: 20,
  },
  logoPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  logoText: {
    fontSize: 44,
    fontWeight: 'bold',
  },
  appName: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 6,
  },
  schoolName: {
    fontSize: 14,
  },
});

export default SplashScreen;