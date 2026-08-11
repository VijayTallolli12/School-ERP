/**
 * Dashboard API service — canonical endpoints:
 *   GET /api/v1/student/dashboard
 *   GET /api/v1/teacher/dashboard
 *   GET /api/v1/driver/dashboard
 *   GET /api/v1/parents/{parentUuid}/dashboard
 */
import { apiGet } from './client';
import { DriverDashboard, ParentDashboard, StudentDashboard, TeacherDashboard } from './types';

export function fetchStudentDashboard(): Promise<StudentDashboard> {
  return apiGet<StudentDashboard>('/student/dashboard');
}

export function fetchTeacherDashboard(): Promise<TeacherDashboard> {
  return apiGet<TeacherDashboard>('/teacher/dashboard');
}

export function fetchDriverDashboard(): Promise<DriverDashboard> {
  return apiGet<DriverDashboard>('/driver/dashboard');
}

export function fetchParentDashboard(parentUuid: string): Promise<ParentDashboard> {
  return apiGet<ParentDashboard>(`/parents/${parentUuid}/dashboard`);
}
