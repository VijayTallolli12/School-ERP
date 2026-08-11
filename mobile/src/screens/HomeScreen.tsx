/**
 * HomeScreen — role-aware dashboard.
 *
 * Renders REAL data from the canonical dashboard endpoints:
 *   Student -> GET /api/v1/student/dashboard
 *   Teacher -> GET /api/v1/teacher/dashboard
 *   Driver  -> GET /api/v1/driver/dashboard
 *   Parent  -> GET /api/v1/parents/{uuid}/dashboard
 *
 * Loading, error and empty states are all explicit — API failures are shown
 * with a retry action, never silently converted into fake data.
 */
import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useBranding } from '../branding/BrandingContext';
import { useAuth } from '../auth/AuthContext';
import {
  fetchDriverDashboard,
  fetchParentDashboard,
  fetchStudentDashboard,
  fetchTeacherDashboard,
} from '../api/dashboard';
import {
  DriverDashboard,
  ParentDashboard,
  StudentDashboard,
  TeacherDashboard,
} from '../api/types';
import { ApiError } from '../api/client';

type LoadedDashboard = {
  role: string;
  student?: StudentDashboard;
  teacher?: TeacherDashboard;
  driver?: DriverDashboard;
  parent?: ParentDashboard;
};

const HomeScreen: React.FC = () => {
  const { branding, theme } = useBranding();
  const { user, primaryRole, parentUuid, signOut } = useAuth();

  const [dashboard, setDashboard] = useState<LoadedDashboard | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadDashboard = useCallback(async () => {
    if (!primaryRole) {
      setError('Your account role could not be determined.');
      setLoading(false);
      return;
    }

    setLoading(true);
    setError(null);

    try {
      if (primaryRole === 'Student') {
        setDashboard({ role: 'Student', student: await fetchStudentDashboard() });
      } else if (primaryRole === 'Teacher') {
        setDashboard({ role: 'Teacher', teacher: await fetchTeacherDashboard() });
      } else if (primaryRole === 'Driver') {
        setDashboard({ role: 'Driver', driver: await fetchDriverDashboard() });
      } else if (primaryRole === 'Parent') {
        if (!parentUuid) {
          setError('Your parent profile is not linked. Please contact the school administrator.');
        } else {
          setDashboard({ role: 'Parent', parent: await fetchParentDashboard(parentUuid) });
        }
      } else {
        setError(`Dashboard is not available for the role "${primaryRole}".`);
      }
    } catch (err) {
      const message = err instanceof ApiError ? err.message : 'Failed to load dashboard.';
      setError(message);
    } finally {
      setLoading(false);
    }
  }, [primaryRole, parentUuid]);

  useEffect(() => {
    loadDashboard();
  }, [loadDashboard]);

  if (loading) {
    return (
      <View style={[styles.center, { backgroundColor: theme.background }]}>
        <ActivityIndicator size="large" color={theme.primary} />
        <Text style={[styles.loadingText, { color: theme.textSecondary }]}>
          Loading {branding.schoolName} dashboard…
        </Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={[styles.center, { backgroundColor: theme.background }]}>
        <Text style={[styles.errorTitle, { color: theme.danger }]}>Something went wrong</Text>
        <Text style={[styles.errorMessage, { color: theme.textSecondary }]}>{error}</Text>
        <TouchableOpacity
          style={[styles.retryButton, { backgroundColor: theme.primary }]}
          onPress={loadDashboard}
        >
          <Text style={styles.retryButtonText}>Retry</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (!dashboard) {
    return (
      <View style={[styles.center, { backgroundColor: theme.background }]}>
        <Text style={[styles.emptyText, { color: theme.textSecondary }]}>No dashboard data.</Text>
      </View>
    );
  }

  const greeting = user?.name ? `Welcome, ${user.name.split(' ')[0]}` : 'Welcome';

  return (
    <ScrollView style={[styles.container, { backgroundColor: theme.background }]}>
      <View style={[styles.hero, { backgroundColor: theme.primary }]}>
        <Text style={styles.heroTitle}>{greeting}</Text>
        <Text style={styles.heroSubtitle}>
          {primaryRole === 'Parent'
            ? 'Your children at a glance'
            : `${primaryRole} Dashboard`}
        </Text>
        <TouchableOpacity onPress={signOut} style={styles.signOutLink}>
          <Text style={styles.signOutText}>Sign out</Text>
        </TouchableOpacity>
      </View>

      {dashboard.role === 'Student' && dashboard.student ? (
        <StudentView data={dashboard.student} />
      ) : null}

      {dashboard.role === 'Teacher' && dashboard.teacher ? (
        <TeacherView data={dashboard.teacher} />
      ) : null}

      {dashboard.role === 'Driver' && dashboard.driver ? (
        <DriverView data={dashboard.driver} />
      ) : null}

      {dashboard.role === 'Parent' && dashboard.parent ? (
        <ParentView data={dashboard.parent} />
      ) : null}
    </ScrollView>
  );
};

// ────────────────────────────────────────────────────────────────────────────
// Shared building blocks
// ────────────────────────────────────────────────────────────────────────────

const StatCard: React.FC<{ label: string; value: string | number; theme: any }> = ({
  label,
  value,
  theme,
}) => (
  <View style={[styles.statCard, { backgroundColor: theme.backgroundCard }]}>
    <Text style={[styles.statValue, { color: theme.primary }]}>{value}</Text>
    <Text style={[styles.statLabel, { color: theme.textSecondary }]}>{label}</Text>
  </View>
);

const SectionTitle: React.FC<{ title: string; theme: any }> = ({ title, theme }) => (
  <Text style={[styles.sectionTitle, { color: theme.text }]}>{title}</Text>
);

// ────────────────────────────────────────────────────────────────────────────
// Role views
// ────────────────────────────────────────────────────────────────────────────

const StudentView: React.FC<{ data: StudentDashboard }> = ({ data }) => {
  const { theme } = useBranding();

  return (
    <View>
      {data.current_session ? (
        <View style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.cardTitle, { color: theme.text }]}>Class {data.current_session.class} - {data.current_session.section}</Text>
          <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
            Roll {data.current_session.roll_no} · {data.current_session.academic_year}
          </Text>
        </View>
      ) : null}

      <View style={styles.statRow}>
        <StatCard label="Attendance" value={`${data.attendance.percentage}%`} theme={theme} />
        <StatCard label="Pending Homework" value={data.pending_homework_count} theme={theme} />
        <StatCard label="Fees Pending" value={formatCurrency(data.fees_summary.pending)} theme={theme} />
      </View>

      <View style={styles.statRow}>
        <StatCard label="Books Issued" value={data.issued_books_count} theme={theme} />
        <StatCard label="Unread Alerts" value={data.notifications.unread_count} theme={theme} />
        <StatCard label="Avg Score" value={`${data.exam_results_summary.average}%`} theme={theme} />
      </View>

      {data.upcoming_exams.length > 0 ? (
        <View>
          <SectionTitle title="Upcoming Exams" theme={theme} />
          {data.upcoming_exams.map((exam) => (
            <View key={exam.id} style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
              <Text style={[styles.cardTitle, { color: theme.text }]}>{exam.exam_name}</Text>
              <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
                {exam.subject ?? 'All subjects'} · {exam.exam_date ?? 'Date TBA'}
              </Text>
            </View>
          ))}
        </View>
      ) : null}
    </View>
  );
};

const TeacherView: React.FC<{ data: TeacherDashboard }> = ({ data }) => {
  const { theme } = useBranding();

  return (
    <View>
      <View style={styles.statRow}>
        <StatCard label="Classes Today" value={data.today_classes.length} theme={theme} />
        <StatCard label="Pending Homework" value={data.pending_homework_count} theme={theme} />
        <StatCard label="Unread Alerts" value={data.notifications.unread_count} theme={theme} />
      </View>

      {data.my_attendance_today ? (
        <View style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.cardTitle, { color: theme.text }]}>
            My Attendance: {data.my_attendance_today.status_label}
          </Text>
        </View>
      ) : null}

      <SectionTitle title="Today's Classes" theme={theme} />
      {data.today_classes.length === 0 ? (
        <Text style={[styles.emptyText, { color: theme.textSecondary }]}>No classes scheduled today.</Text>
      ) : (
        data.today_classes.map((c) => (
          <View key={c.id} style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
            <Text style={[styles.cardTitle, { color: theme.text }]}>{c.subject ?? 'Subject'}</Text>
            <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
              {c.class_section} · {formatTime(c.start_time)} - {formatTime(c.end_time)}
            </Text>
          </View>
        ))
      )}

      {data.upcoming_exams.length > 0 ? (
        <View>
          <SectionTitle title="Upcoming Exams" theme={theme} />
          {data.upcoming_exams.map((exam) => (
            <View key={exam.id} style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
              <Text style={[styles.cardTitle, { color: theme.text }]}>{exam.exam_name}</Text>
              <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
                {exam.class_section} · {exam.exam_date ?? 'Date TBA'}
              </Text>
            </View>
          ))}
        </View>
      ) : null}
    </View>
  );
};

const DriverView: React.FC<{ data: DriverDashboard }> = ({ data }) => {
  const { theme } = useBranding();

  return (
    <View>
      <View style={styles.statRow}>
        <StatCard label="Trips Today" value={data.summary.total_trips_today} theme={theme} />
        <StatCard label="Completed" value={data.summary.completed_trips} theme={theme} />
        <StatCard label="Students Today" value={data.summary.total_students_today} theme={theme} />
      </View>

      <View style={styles.statRow}>
        <StatCard label="Picked Up" value={data.summary.total_picked_up} theme={theme} />
        <StatCard label="Dropped Off" value={data.summary.total_dropped_off} theme={theme} />
        <StatCard label="Stops" value={data.route_stops_count} theme={theme} />
      </View>

      {data.vehicle ? (
        <View style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.cardTitle, { color: theme.text }]}>
            Vehicle: {data.vehicle.vehicle_name}
          </Text>
          <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
            {data.vehicle.vehicle_number} · Capacity {data.vehicle.capacity ?? '—'}
          </Text>
        </View>
      ) : (
        <View style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
          <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
            No vehicle assigned.
          </Text>
        </View>
      )}

      <SectionTitle title="Today's Trips" theme={theme} />
      {data.today_trips.length === 0 ? (
        <Text style={[styles.emptyText, { color: theme.textSecondary }]}>No trips today.</Text>
      ) : (
        data.today_trips.map((trip) => (
          <View key={trip.id} style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
            <Text style={[styles.cardTitle, { color: theme.text }]}>
              {trip.route_name ?? 'Route'} · {trip.status}
            </Text>
            <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
              {trip.total_students} students · {trip.picked_up} picked · {trip.dropped_off} dropped
            </Text>
          </View>
        ))
      )}
    </View>
  );
};

const ParentView: React.FC<{ data: ParentDashboard }> = ({ data }) => {
  const { theme } = useBranding();

  return (
    <View>
      <SectionTitle title="Children" theme={theme} />
      {data.students.length === 0 ? (
        <Text style={[styles.emptyText, { color: theme.textSecondary }]}>No children linked.</Text>
      ) : (
        data.students.map((child) => (
          <View key={child.uuid} style={[styles.card, { backgroundColor: theme.backgroundCard }]}>
            <Text style={[styles.cardTitle, { color: theme.text }]}>{child.name}</Text>
            <Text style={[styles.cardSubtitle, { color: theme.textSecondary }]}>
              Class {child.class} - {child.section} · Roll {child.roll_number}
            </Text>
          </View>
        ))
      )}

      <View style={styles.statRow}>
        <StatCard label="Attendance" value={`${data.attendance_summary.percentage}%`} theme={theme} />
        <StatCard label="Fees Pending" value={formatCurrency(data.fees_summary.pending)} theme={theme} />
        <StatCard label="Avg Score" value={`${data.exam_results_summary.average}%`} theme={theme} />
      </View>
    </View>
  );
};

// ────────────────────────────────────────────────────────────────────────────
// Formatting helpers
// ────────────────────────────────────────────────────────────────────────────

function formatCurrency(value: number): string {
  const formatted = Number(value || 0).toLocaleString('en-IN', {
    maximumFractionDigits: 0,
  });
  return `₹${formatted}`;
}

function formatTime(value: string | null | undefined): string {
  if (!value) {
    return '—';
  }
  return value;
}

export default HomeScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
  },
  errorTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorMessage: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 16,
  },
  retryButton: {
    paddingHorizontal: 24,
    paddingVertical: 10,
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#ffffff',
    fontWeight: '600',
  },
  emptyText: {
    fontSize: 14,
    textAlign: 'center',
    paddingVertical: 16,
  },
  hero: {
    paddingVertical: 24,
    paddingHorizontal: 20,
  },
  heroTitle: {
    color: '#ffffff',
    fontSize: 22,
    fontWeight: 'bold',
  },
  heroSubtitle: {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 14,
    marginTop: 4,
  },
  signOutLink: {
    alignSelf: 'flex-start',
    marginTop: 12,
    paddingVertical: 4,
  },
  signOutText: {
    color: 'rgba(255,255,255,0.9)',
    fontSize: 13,
    textDecorationLine: 'underline',
  },
  statRow: {
    flexDirection: 'row',
    marginHorizontal: 16,
    marginTop: 16,
    gap: 8,
  },
  statCard: {
    flex: 1,
    borderRadius: 12,
    padding: 12,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  statLabel: {
    fontSize: 11,
    marginTop: 4,
    textAlign: 'center',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginTop: 20,
    marginBottom: 8,
    marginHorizontal: 16,
  },
  card: {
    marginHorizontal: 16,
    marginBottom: 8,
    padding: 14,
    borderRadius: 12,
  },
  cardTitle: {
    fontSize: 15,
    fontWeight: '600',
  },
  cardSubtitle: {
    fontSize: 13,
    marginTop: 4,
  },
});
