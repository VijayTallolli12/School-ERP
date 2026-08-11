# STUDENT SCREEN INVENTORY

Version: v1.0
Date: Automated verification
App: `school-student` (Expo React Native / Expo SDK 54)
Generated from: `src/app/**` navigation, `src/services/api.ts`, `src/store/auth.store.ts`

> **Legend — Status**
> ✅ API integrated & backend verified
> 🟡 API integrated, static/no API
> 🔵 Local-only (no API, no backend dependency)

---

## Root / Auth

| Screen | Navigation Route | Feature | API Used | Hook Used | Service Used | Status |
|---|---|---|---|---|---|---|
| SplashScreen | `/` | Branded splash, decides login vs home | `GET /branding` | `useAuthStore`, `useBrandingStore`, `useRef/useEffect` | `useBrandingStore.loadBranding` | 🟡 |
| LoginScreen | `/(auth)/login` | Email/password sign-in (react-hook-form + zod) | `POST /auth/login`, `GET /branding` | `useAuthStore`, `useBrandingStore`, `useForm/useCallback` | `apiClient.post` | ✅ |

---

## Home Stack — `(tabs)/(home)`

| Screen | Route | File Path | Feature | API Used | Hook Used | Service Used | Status |
|---|---|---|---|---|---|---|---|
| DashboardScreen | `/` | `(home)/index.tsx` | Attendance %, due fees, subject count, exam card, quick actions, recent notifications | `GET /student/dashboard` | `useAuthStore`, `useBrandingStore`, `useState/useEffect/useCallback` | `fetchDashboard` | ✅ |
| AttendanceScreen | `/attendance` | `(home)/attendance.tsx` | Monthly attendance calendar + summary ring | `GET /student/attendance?month&year` | `useAuthStore`, `useState/useEffect/useCallback` | `fetchAttendance` | ✅ |
| FeesScreen | `/fees` | `(home)/fees.tsx` | Fee structure + payment history tabs | `GET /student/fees` | `useAuthStore` | `fetchFees` | ✅ |
| ResultsScreen | `/results` | `(home)/results.tsx` | Exam results grouped by academic year + exam | `GET /student/exams` | `useAuthStore` | `fetchExamResults` | ✅ |
| TimetableScreen | `/timetable` | `(home)/timetable.tsx` | Weekly timetable, day tabs (1=Mon..7=Sun) | `GET /student/timetable` | `useAuthStore`, `useMemo` | `fetchTimetable` | ✅ |
| NotificationsScreen | `/notifications` | `(home)/notifications/index.tsx` | Notification list grouped Today/Yesterday/Earlier, mark all read | `GET /notifications?page`, `POST /notifications/read-all` | `useAuthStore` | `fetchNotifications`, `markAllNotificationsRead` | ✅ |
| NotificationDetailScreen | `/notifications/[id]` | `(home)/notifications/[id].tsx` | Show notification detail, mark read | `POST /notifications/{id}/read` | `useLocalSearchParams` | `markNotificationRead` | ✅ |
| HomeworkScreen | `/homework` | `(home)/homework.tsx` | Homework cards + attachment open | `GET /student/homework` | `useAuthStore` | `fetchHomework` | ✅ |
| AssignmentsScreen | `/assignments` | `(home)/assignments.tsx` | Assignment list | `GET /student/assignments` | `useAuthStore` | `fetchAssignments` | ✅ |
| ExamScheduleScreen | `/exam-schedule` | `(home)/exam-schedule.tsx` | Upcoming exam schedule | `GET /student/exam-schedule` | `useAuthStore` | `fetchExamSchedule` | ✅ |
| CalendarScreen | `/calendar` | `(home)/calendar.tsx` | Academic calendar, month/type filter | `GET /student/calendar?month&year&type` | `useAuthStore` | `fetchCalendar` | ✅ |
| DocumentsScreen | `/documents` | `(home)/documents.tsx` | Uploaded documents, verification, download | `GET /student/documents` | `useAuthStore` | `fetchDocuments` | ✅ |
| CircularsScreen | `/circulars` | `(home)/circulars.tsx` | Circulars list, infinite scroll | `GET /circulars?page` | `useAuthStore` | `fetchCirculars` | ✅ |
| CircularDetailScreen | `/circulars/[id]` | `(home)/circulars/[id].tsx` | Circular detail + mark read | `GET /circulars/{id}`, `POST /circulars/{id}/read` | `useLocalSearchParams` | `fetchCircularDetail`, `markCircularRead` | ✅ |
| LeaveListScreen | `/leave` | `(home)/leave.tsx` | Leave request list | `GET /student/leave-requests` | `useAuthStore` | `fetchLeaveRequests` | ✅ |
| ApplyLeaveScreen | `/leave/apply` | `(home)/leave/apply.tsx` | Submit / edit leave request | `POST /student/leave-requests`, `PUT /student/leave-requests/{id}` | `useAuthStore`, `useLocalSearchParams` | `submitLeaveRequest`, `updateLeaveRequest` | ✅ |
| LeaveDetailScreen | `/leave/[id]` | `(home)/leave/[id].tsx` | Leave request detail + edit | `GET /student/leave-requests/{id}` | `useAuthStore`, `useLocalSearchParams` | `fetchLeaveRequestDetail` | ✅ |
| StudentProfileScreen | `/student-profile` | `(home)/student-profile.tsx` | Student info + guardian info | `GET /me` | `useAuthStore` | `fetchProfile` | ✅ |
| TransportScreen | `/transport` | `(home)/transport/index.tsx` | Transport summary + links to driver/route | `GET /student/transport` | `useAuthStore` | `fetchTransportDashboard` | ✅ |
| TransportDriverScreen | `/transport/driver` | `(home)/transport/driver.tsx` | Driver & vehicle details | `GET /student/transport` | `useAuthStore` | `fetchTransportDashboard` | ✅ |
| TransportRouteScreen | `/transport/route` | `(home)/transport/route.tsx` | Route + stops | `GET /student/transport` | `useAuthStore` | `fetchTransportDashboard` | ✅ |

## Profile Stack — `(tabs)/profile`

| Screen | Route | File Path | Feature | API Used | Hook Used | Service Used | Status |
|---|---|---|---|---|---|---|---|
| ProfileScreen | `/profile` | `profile/index.tsx` | User info + menu links + logout | `POST /auth/logout` | `useAuthStore` | `secureLogout` | ✅ |
| EditProfileScreen | `/profile/edit-profile` | `profile/edit-profile.tsx` | Edit phone/email/address | `GET /me`, `PUT /me` | `useAuthStore`, `useForm` | `fetchProfile`, `updateProfile` | ✅ |
| ChangePasswordScreen | `/profile/change-password` | `profile/change-password.tsx` | Change password | `PUT /me/change-password` | `useAuthStore`, `useForm` | `changePassword` | ✅ |
| SettingsScreen | `/profile/settings` | `profile/settings.tsx` | Local notification toggles | — | — | — | ⚡ (local-only) |
| PrivacyScreen | `/profile/privacy` | `profile/privacy.tsx` | Static privacy text | — | — | — | ⚡ (static) |
| HelpScreen | `/profile/help` | `profile/help.tsx` | Static contact cards | — | — | — | ⚡ (static) |

## Summary

| Metric | Count |
|---|---|
| Total screens | 28 |
| API-integrated screens | 25 |
| Static / local-only screens | 3 (Settings, Privacy, Help) |
| Distinct backend API endpoints used by the app | 25 verified |