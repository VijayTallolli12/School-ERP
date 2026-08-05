import React from 'react';
import { View, Text, StyleSheet, Image } from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const DrawerHeader: React.FC = () => {
  const { branding, theme } = useBranding();

  return (
    <View style={[styles.container, { backgroundColor: theme.primary }]}> 
      {branding.schoolLogo ? (
        <Image source={{ uri: branding.schoolLogo }} style={styles.drawerLogo} />
      ) : (
        <View style={[styles.drawerLogoPlaceholder, { backgroundColor: theme.primaryLight }]}> 
          <Text style={[styles.drawerLogoText, { color: theme.primary }]}>
            {branding.schoolName.charAt(0).toUpperCase()}
          </Text>
        </View>
      )}
      <Text style={[styles.drawerAppName, { color: '#ffffff' }]}>
        {branding.appName}
      </Text>
      <Text style={[styles.drawerSchoolName, { color: 'rgba(255,255,255,0.8)' }]}>
        {branding.schoolName}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingVertical: 24,
    paddingHorizontal: 20,
    alignItems: 'center',
  },
  drawerLogo: {
    width: 64,
    height: 64,
    resizeMode: 'contain',
    marginBottom: 12,
  },
  drawerLogoPlaceholder: {
    width: 64,
    height: 64,
    borderRadius: 32,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  drawerLogoText: {
    fontSize: 28,
    fontWeight: 'bold',
  },
  drawerAppName: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  drawerSchoolName: {
    fontSize: 12,
    marginTop: 2,
  },
});

export default DrawerHeader;