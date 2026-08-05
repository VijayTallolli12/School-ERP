import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  Dimensions,
  TouchableOpacity,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const { width } = Dimensions.get('window');

const WelcomeScreen: React.FC<{ onContinue: () => void }> = ({ onContinue }) => {
  const { branding, theme } = useBranding();

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}> 
      <View style={[styles.header, { backgroundColor: theme.primary }]}> 
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
      </View>

      <View style={styles.content}>
        <Text style={[styles.welcomeTitle, { color: theme.text }]}>
          Welcome to {branding.schoolName}
        </Text>
        <Text style={[styles.welcomeText, { color: theme.textSecondary }]}>
          Your school management app. Sign in to access your dashboard and
          manage school activities.
        </Text>

        <View style={[styles.features, { backgroundColor: theme.backgroundCard }]}> 
          <Text style={[styles.featureItem, { color: theme.text }]}>
            {'\u2022'}  Student Management
          </Text>
          <Text style={[styles.featureItem, { color: theme.text }]}>
            {'\u2022'}  Attendance Tracking
          </Text>
          <Text style={[styles.featureItem, { color: theme.text }]}>
            {'\u2022'}  Academic Records
          </Text>
          <Text style={[styles.featureItem, { color: theme.text }]}>
            {'\u2022'}  Fee Management
          </Text>
        </View>
      </View>

      <TouchableOpacity
        style={[styles.continueButton, { backgroundColor: theme.primary }]}
        onPress={onContinue}
      >
        <Text style={styles.continueButtonText}>Get Started</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    paddingVertical: 40,
    paddingHorizontal: 24,
    alignItems: 'center',
    borderBottomLeftRadius: 32,
    borderBottomRightRadius: 32,
  },
  logo: {
    width: 80,
    height: 80,
    resizeMode: 'contain',
    marginBottom: 12,
  },
  logoPlaceholder: {
    width: 80,
    height: 80,
    borderRadius: 40,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  logoText: {
    fontSize: 36,
    fontWeight: 'bold',
  },
  appName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  content: {
    flex: 1,
    padding: 24,
  },
  welcomeTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 12,
  },
  welcomeText: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  features: {
    padding: 16,
    borderRadius: 12,
  },
  featureItem: {
    fontSize: 14,
    paddingVertical: 6,
  },
  continueButton: {
    margin: 24,
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  continueButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default WelcomeScreen;