/**
 * API contract types — mirrors the canonical Laravel /api/v1 responses
 * (see docs/architecture/*_API_* and the ApiBaseController envelope).
 *
 * Envelope: { success, message, data, meta?, links?, errors? }
 */

export interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
  links?: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  errors?: Record<string, string[]>;
}

// ────────────────────────────────────────────────────────────────────────────
// Auth / user
// ────────────────────────────────────────────────────────────────────────────

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  avatar_url: string | null;
  status: string;
  roles?: string[];
  last_login_at?: string | null;
}

/** Compact student context returned by /auth/login and /me for Student users. */
export interface StudentContext {
  uuid: string;
  name: string;
  admission_no: string;
  class: string;
  section: string;
  roll_number: string;
  academic_year: string;
  photo: string | null;
}

/** Compact child entry returned for Parent users. */
export interface ParentChild {
  id: number;
  uuid: string;
  name: string;
  class: string;
  section: string;
  roll_number: string;
  admission_no: string;
  photo: string | null;
}

export interface LoginResponseData {
  token: string;
  token_type: string;
  user: UserProfile;
  school_id: number | null;
  role?: string;
  permissions?: string[];
  students?: ParentChild[];
  parent_uuid?: string | null;
  student?: StudentContext | null;
}

export interface MeResponseData {
  user: UserProfile;
  roles: string[];
  permissions: string[];
  students?: ParentChild[];
  parent_uuid?: string | null;
  student?: StudentContext | null;
}

// ────────────────────────────────────────────────────────────────────────────
// Role dashboards
// ────────────────────────────────────────────────────────────────────────────

export interface StudentDashboard {
  student: {
    id: number;
    uuid: string;
    full_name: string;
    photo_url: string | null;
  } | null;
  students: unknown[];
  current_session: {
    class: string;
    section: string;
    roll_no: string;
    academic_year: string;
  } | null;
  attendance: {
    total_days: number;
    present_days: number;
    percentage: number;
  };
  attendance_summary: {
    present: number;
    absent: number;
    total: number;
    percentage: number;
  };
  fees_summary: { total: number; paid: number; pending: number };
  exam_results_summary: {
    average: number;
    subjects: number;
    total_marks: number;
    obtained_marks: number;
  };
  leave_summary: Record<string, unknown>;
  pending_homework_count: number;
  upcoming_exams: Array<{
    id: number;
    exam_name: string;
    exam_type: string;
    exam_date: string | null;
    subject: string | null;
  }>;
  issued_books_count: number;
  notifications: { unread_count: number };
  recent_notifications: unknown[];
}

export interface TeacherDashboard {
  teacher: {
    id: number;
    uuid: string;
    full_name: string;
    photo_url: string | null;
  } | null;
  today_classes: Array<{
    id: number;
    subject: string | null;
    class_section: string;
    start_time: string | null;
    end_time: string | null;
    room: string | null;
  }>;
  my_attendance_today: { status: string; status_label: string; remarks: string | null } | null;
  pending_homework_count: number;
  upcoming_exams: Array<{
    id: number;
    exam_name: string;
    exam_type: string;
    exam_date: string | null;
    subject: string | null;
    class_section: string;
  }>;
  notifications: { unread_count: number };
}

export interface DriverDashboard {
  summary: {
    total_trips_today: number;
    completed_trips: number;
    active_trip: number | null;
    total_students_today: number;
    total_picked_up: number;
    total_dropped_off: number;
  };
  vehicle: {
    id: number;
    vehicle_number: string;
    vehicle_name: string;
    vehicle_type: string | null;
    capacity: number | null;
  } | null;
  routes: Array<{
    id: number;
    route_name: string;
    start_point: string | null;
    end_point: string | null;
    distance: number | null;
    stops_count: number;
  }>;
  route_stops_count: number;
  today_trips: Array<{
    id: number;
    type: string;
    status: string;
    route_name: string | null;
    vehicle_number: string | null;
    total_students: number;
    picked_up: number;
    dropped_off: number;
    started_at: string | null;
    completed_at: string | null;
  }>;
}

export interface ParentDashboard {
  students: ParentChild[];
  attendance_summary: { present: number; absent: number; total: number; percentage: number };
  fees_summary: { total: number; paid: number; pending: number };
  exam_results_summary: {
    average: number;
    subjects: number;
    total_marks: number;
    obtained_marks: number;
  };
  homework_summary: Record<string, unknown>;
  notifications: unknown[];
}

export type RoleName = 'Student' | 'Parent' | 'Teacher' | 'Driver';
