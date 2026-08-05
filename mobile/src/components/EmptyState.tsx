import React from 'react';
import { View, Text, StyleSheet, Image, Dimensions } from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const { width } = Dimensions.get('window');

interface EmptyStateProps {
  title?: string;
  message?: string;
  icon?: string;
}

const EmptyState: React.FC<EmptyStateProps> = ({
  title,
  message,
  icon,
}) => {
  const { branding, theme } = useBranding();

  return (
    <View style={styles.container}>
      {icon ? (
        <Text style={styles.icon}>{icon}</Text>
      ) : branding.schoolLogo ? (
        <Image source={{ uri: branding.schoolLogo }} style={styles.emptyLogo} />
      ) : (
        <View style={[styles.emptyLogoPlaceholder, { backgroundColor: theme.primaryLight }]}> 
          <Text style={[styles.emptyLogoText, { color: theme.primary }]}>
            {branding.schoolName.charAt(0).toUpperCase()}
          </Text>
        </View>
      )}
      <Text style={[styles.title, { color: theme.text }]}>
        {title ?? 'No data found'}
      </Text>
      <Text style={[styles.message, { color: theme.textSecondary }]}>
        {message ?? `No ${branding.schoolName} data available at this time.`}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 32,
  },
  icon: {
    fontSize: 48,
    marginBottom: 16,
  },
  emptyLogo: {
    width: 64,
    height: 64,
    resizeMode: 'contain',
    marginBottom: 16,
    opacity: 0.5,
  },
  emptyLogoPlaceholder: {
    width: 64,
    height: 64,
    borderRadius: 32,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyLogoText: {
    fontSize: 28,
    fontWeight: 'bold',
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 8,
    textAlign: 'center',
  },
  message: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
  },
});

export default EmptyState;