import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';

const ProfileScreen: React.FC = () => {
  const { branding, theme } = useBranding();

  return (
    <ScrollView style={[styles.container, { backgroundColor: theme.background }]}> 
      <View style={[styles.header, { backgroundColor: theme.primary }]}> 
        <Text style={[styles.headerTitle, { color: '#ffffff' }]}>Profile</Text>
      </View>

      <View style={styles.avatarSection}>
        <View style={[styles.avatar, { backgroundColor: theme.primary + '20' }]}> 
          {branding.schoolLogo ? (
            <Image source={{ uri: branding.schoolLogo }} style={styles.avatarImage} />
          ) : (
            <Text style={[styles.avatarText, { color: theme.primary }]}>
              {branding.schoolName.charAt(0).toUpperCase()}
            </Text>
          )}
        </View>
        <Text style={[styles.profileName, { color: theme.text }]}>
          Profile Name
        </Text>
        <Text style={[styles.profileRole, { color: theme.textSecondary }]}>
          Role
        </Text>
      </View>

      <View style={[styles.infoCard, { backgroundColor: theme.backgroundCard }]}> 
        <Text style={[styles.infoLabel, { color: theme.textSecondary }]}>School</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>{branding.schoolName}</Text>

        <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Address</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>
          {branding.schoolAddress || 'Not set'}
        </Text>

        <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Phone</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>
          {branding.schoolPhone || 'Not set'}
        </Text>

        <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Website</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>
          {branding.schoolWebsite || 'Not set'}
        </Text>
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
  avatarSection: {
    alignItems: 'center',
    paddingVertical: 32,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarImage: {
    width: 80,
    height: 80,
    borderRadius: 40,
  },
  avatarText: {
    fontSize: 32,
    fontWeight: 'bold',
  },
  profileName: {
    fontSize: 18,
    fontWeight: 'bold',
    marginTop: 12,
  },
  profileRole: {
    fontSize: 14,
    marginTop: 4,
  },
  infoCard: {
    margin: 24,
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

export default ProfileScreen;