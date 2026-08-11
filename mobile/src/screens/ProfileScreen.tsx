import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  TouchableOpacity,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';
import { useAuth } from '../auth/AuthContext';
import { ApiError } from '../api/client';

const ProfileScreen: React.FC = () => {
  const { branding, theme } = useBranding();
  const { user, roles, schoolId, student, children, parentUuid, primaryRole, refreshProfile, signOut } =
    useAuth();

  const [refreshing, setRefreshing] = React.useState(false);
  const [error, setError] = React.useState<string | null>(null);

  const onRefresh = async () => {
    setRefreshing(true);
    setError(null);
    try {
      await refreshProfile();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Could not refresh profile.');
    } finally {
      setRefreshing(false);
    }
  };

  return (
    <ScrollView
      style={[styles.container, { backgroundColor: theme.background }]}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      <View style={[styles.header, { backgroundColor: theme.primary }]}>
        <Text style={[styles.headerTitle, { color: '#ffffff' }]}>Profile</Text>
      </View>

      {error ? (
        <View style={[styles.errorBox, { backgroundColor: theme.danger + '14', borderColor: theme.danger + '44' }]}>
          <Text style={[styles.errorText, { color: theme.danger }]}>{error}</Text>
        </View>
      ) : null}

      <View style={styles.avatarSection}>
        {user?.avatar_url ? (
          <Image source={{ uri: user.avatar_url }} style={styles.avatarImage} />
        ) : (
          <View style={[styles.avatar, { backgroundColor: theme.primary + '20' }]}>
            <Text style={[styles.avatarText, { color: theme.primary }]}>
              {(user?.name ?? branding.schoolName).charAt(0).toUpperCase()}
            </Text>
          </View>
        )}
        <Text style={[styles.profileName, { color: theme.text }]}>
          {user?.name ?? '—'}
        </Text>
        <Text style={[styles.profileRole, { color: theme.textSecondary }]}>
          {primaryRole ?? (roles.length > 0 ? roles.join(', ') : '—')}
        </Text>
        <Text style={[styles.profileEmail, { color: theme.textSecondary }]}>
          {user?.email ?? ''}
        </Text>
      </View>

      <View style={[styles.infoCard, { backgroundColor: theme.backgroundCard }]}>
        <Text style={[styles.infoLabel, { color: theme.textSecondary }]}>School</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>{branding.schoolName}</Text>
        <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>School ID</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>{schoolId ?? '—'}</Text>

        <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Phone</Text>
        <Text style={[styles.infoValue, { color: theme.text }]}>{user?.phone || 'Not set'}</Text>
      </View>

      {primaryRole === 'Student' && student ? (
        <View style={[styles.infoCard, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.infoLabel, { color: theme.textSecondary }]}>Admission No</Text>
          <Text style={[styles.infoValue, { color: theme.text }]}>{student.admission_no}</Text>
          <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Class</Text>
          <Text style={[styles.infoValue, { color: theme.text }]}>
            Class {student.class} - {student.section} · Roll {student.roll_number}
          </Text>
          <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Academic Year</Text>
          <Text style={[styles.infoValue, { color: theme.text }]}>{student.academic_year}</Text>
        </View>
      ) : null}

      {primaryRole === 'Parent' ? (
        <View style={[styles.infoCard, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.infoLabel, { color: theme.textSecondary }]}>Linked Children</Text>
          {children.length === 0 ? (
            <Text style={[styles.infoValue, { color: theme.text }]}>No children linked.</Text>
          ) : (
            children.map((child) => (
              <View key={child.uuid} style={{ marginTop: 8 }}>
                <Text style={[styles.infoValue, { color: theme.text }]}>{child.name}</Text>
                <Text style={[styles.infoValue, { color: theme.textSecondary }]}>
                  Class {child.class} - {child.section} · Roll {child.roll_number}
                </Text>
              </View>
            ))
          )}
          <Text style={[styles.infoLabel, { color: theme.textSecondary, marginTop: 12 }]}>Parent ID</Text>
          <Text style={[styles.infoValue, { color: theme.text }]}>{parentUuid ?? '—'}</Text>
        </View>
      ) : null}

      {refreshing ? (
        <ActivityIndicator style={{ marginVertical: 12 }} color={theme.primary} />
      ) : null}

      <TouchableOpacity
        style={[styles.signOutButton, { borderColor: theme.danger + '66' }]}
        onPress={signOut}
      >
        <Text style={[styles.signOutText, { color: theme.danger }]}>Sign Out</Text>
      </TouchableOpacity>
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
  profileEmail: {
    fontSize: 13,
    marginTop: 2,
  },
  infoCard: {
    margin: 24,
    marginTop: 0,
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
  errorBox: {
    borderWidth: 1,
    borderRadius: 8,
    padding: 10,
    marginHorizontal: 24,
    marginBottom: 8,
  },
  errorText: {
    fontSize: 13,
  },
  signOutButton: {
    margin: 24,
    marginTop: 0,
    paddingVertical: 12,
    borderRadius: 12,
    borderWidth: 1,
    alignItems: 'center',
  },
  signOutText: {
    fontSize: 15,
    fontWeight: '600',
  },
});

export default ProfileScreen;
