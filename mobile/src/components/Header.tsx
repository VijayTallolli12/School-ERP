import React from 'react';
import { View, Text, StyleSheet, Image, TouchableOpacity } from 'react-native';
import { useBranding } from '../branding/BrandingContext';

interface HeaderProps {
  title?: string;
  onBack?: () => void;
}

const Header: React.FC<HeaderProps> = ({ title, onBack }) => {
  const { branding, theme } = useBranding();

  return (
    <View style={[styles.container, { backgroundColor: theme.primary }]}> 
      <View style={styles.left}>
        {onBack ? (
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <Text style={[styles.backButtonText, { color: '#ffffff' }]}>{'<'}</Text>
          </TouchableOpacity>
        ) : null}
        {branding.schoolLogo ? (
          <Image source={{ uri: branding.schoolLogo }} style={styles.headerLogo} />
        ) : (
          <View style={[styles.headerLogoPlaceholder, { backgroundColor: theme.primaryLight }]}> 
            <Text style={[styles.headerLogoText, { color: theme.primary }]}>
              {branding.schoolName.charAt(0).toUpperCase()}
            </Text>
          </View>
        )}
        <Text style={[styles.title, { color: '#ffffff' }]} numberOfLines={1}>
          {title ?? branding.schoolName}
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  left: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  backButton: {
    marginRight: 12,
    padding: 4,
  },
  backButtonText: {
    fontSize: 20,
    fontWeight: 'bold',
  },
  headerLogo: {
    width: 32,
    height: 32,
    resizeMode: 'contain',
    marginRight: 10,
  },
  headerLogoPlaceholder: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  headerLogoText: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
});

export default Header;