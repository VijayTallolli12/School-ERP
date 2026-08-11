import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  Linking,
  TouchableOpacity,
  Image,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const AboutScreen: React.FC = () => {
  const { branding, theme } = useBranding();

  return (
    <ScrollView style={[styles.container, { backgroundColor: theme.background }]}> 
      <View style={[styles.header, { backgroundColor: theme.primary }]}> 
        <Text style={[styles.headerTitle, { color: '#ffffff' }]}>About</Text>
      </View>

      <View style={[styles.card, { backgroundColor: theme.backgroundCard }]}> 
        {branding.schoolLogo ? (
          <Image source={{ uri: branding.schoolLogo }} style={styles.aboutLogo} />
        ) : null}

        <Text style={[styles.aboutAppName, { color: theme.text }]}>
          {branding.appName}
        </Text>
        <Text style={[styles.aboutSchool, { color: theme.primary }]}>
          {branding.schoolName}
        </Text>
        <Text style={[styles.aboutVersion, { color: theme.textSecondary }]}>
          Version 1.0.0
        </Text>
      </View>

      <View style={[styles.infoCard, { backgroundColor: theme.backgroundCard }]}> 
        {branding.schoolAddress ? (
          <>
            <Text style={[styles.infoLabel, { color: theme.textSecondary }]}>Address</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{branding.schoolAddress}</Text>
          </>
        ) : null}

        {branding.schoolPhone ? (
          <>
            <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Contact</Text>
            <TouchableOpacity onPress={() => Linking.openURL(`tel:${branding.schoolPhone}`)}>
              <Text style={[styles.infoValue, { color: theme.primary }]}>{branding.schoolPhone}</Text>
            </TouchableOpacity>
          </>
        ) : null}

        {branding.schoolWebsite ? (
          <>
            <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Website</Text>
            <TouchableOpacity onPress={() => Linking.openURL(branding.schoolWebsite)}>
              <Text style={[styles.infoValue, { color: theme.primary }]}>{branding.schoolWebsite}</Text>
            </TouchableOpacity>
          </>
        ) : null}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    paddingVertical: 16,
    paddingHorizontal: 24,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  card: {
    margin: 24,
    padding: 24,
    borderRadius: 12,
    alignItems: 'center',
  },
  aboutLogo: {
    width: 64,
    height: 64,
    resizeMode: 'contain',
    marginBottom: 12,
  },
  aboutAppName: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  aboutSchool: {
    fontSize: 14,
    marginTop: 4,
  },
  aboutVersion: {
    fontSize: 12,
    marginTop: 8,
  },
  infoCard: {
    marginHorizontal: 24,
    marginBottom: 24,
    padding: 16,
    borderRadius: 12,
  },
  infoLabel: {
    fontSize: 12,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValue: {
    fontSize: 14,
    marginTop: 2,
  },
});

export default AboutScreen;