import React from 'react';
import { View, Text, StyleSheet, ActivityIndicator, Image, Dimensions } from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const { width } = Dimensions.get('window');

const LoadingScreen: React.FC = () => {
  const { branding, theme } = useBranding();

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}> 
      <View style={[styles.logoContainer, { backgroundColor: theme.primary }]}> 
        {branding.schoolLogo ? (
          <Image source={{ uri: branding.schoolLogo }} style={styles.logo} />
        ) : (
          <View style={[styles.logoPlaceholder, { backgroundColor: theme.primaryLight }]}> 
            <Text style={[styles.logoText, { color: theme.primary }]}>
              {branding.schoolName.charAt(0).toUpperCase()}
            </Text>
          </View>
        )}
      </View>
      <ActivityIndicator size="large" color={theme.primary} />
      <Text style={[styles.loadingText, { color: theme.textSecondary }]}>
        Loading {branding.schoolName}...
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
  logoContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
  },
  logo: {
    width: 100,
    height: 100,
    resizeMode: 'contain',
  },
  logoPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoText: {
    fontSize: 40,
    fontWeight: 'bold',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 14,
  },
});

export default LoadingScreen;