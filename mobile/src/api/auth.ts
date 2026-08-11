/**
 * Auth API service — canonical endpoints:
 *   POST /api/v1/auth/login
 *   GET  /api/v1/me
 *   POST /api/v1/auth/logout
 */
import { apiGet, apiPost } from './client';
import { LoginResponseData, MeResponseData } from './types';

export interface LoginPayload {
  email: string;
  password: string;
  device_name?: string;
  school_id?: number;
}

export async function login(payload: LoginPayload): Promise<LoginResponseData> {
  return apiPost<LoginResponseData>('/auth/login', payload);
}

export async function fetchMe(): Promise<MeResponseData> {
  return apiGet<MeResponseData>('/me');
}

export async function logout(): Promise<void> {
  try {
    await apiPost<null>('/auth/logout');
  } catch {
    // Token is cleared locally regardless; a failed remote logout must not
    // block the user from signing out.
  }
}
