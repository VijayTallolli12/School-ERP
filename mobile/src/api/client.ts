/**
 * Shared Axios client for the School ERP API.
 *
 * - Injects the Bearer token and X-School-Id header on every request.
 * - Normalizes API errors into typed ApiError instances (status + message)
 *   so screens can show real error states instead of silently swallowing
 *   failures or falling back to empty arrays.
 */
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios, { AxiosError, AxiosRequestConfig } from 'axios';
import { API_BASE_URL } from '../config/api';
import { ApiEnvelope } from './types';

export const TOKEN_STORAGE_KEY = '@school_erp_auth_token';
export const SCHOOL_ID_STORAGE_KEY = '@school_erp_school_id';

export class ApiError extends Error {
  readonly status: number;

  constructor(message: string, status: number) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

client.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem(TOKEN_STORAGE_KEY);
  const schoolId = await AsyncStorage.getItem(SCHOOL_ID_STORAGE_KEY);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (schoolId) {
    config.headers['X-School-Id'] = schoolId;
  }

  return config;
});

client.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiEnvelope<unknown>>) => {
    const status = error.response?.status ?? 0;

    let message = 'Network error. Please check your connection and try again.';
    const serverMessage = error.response?.data?.message;
    if (serverMessage) {
      message = serverMessage;
    } else if (error.code === 'ECONNABORTED') {
      message = 'The request timed out. Please try again.';
    } else if (status >= 500) {
      message = 'The server is experiencing issues. Please try again later.';
    } else if (status === 401) {
      message = 'Your session has expired. Please sign in again.';
    } else if (status === 403) {
      message = 'You do not have permission to perform this action.';
    } else if (status === 404) {
      message = 'The requested resource was not found.';
    } else if (status === 429) {
      message = 'Too many requests. Please wait a moment and try again.';
    }

    return Promise.reject(new ApiError(message, status));
  },
);

/**
 * Typed GET that unwraps the { success, message, data } envelope.
 * Throws ApiError (never resolves to fake/empty data on failure).
 */
export async function apiGet<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
  const response = await client.get<ApiEnvelope<T>>(url, config);
  return response.data.data;
}

/** Typed POST that unwraps the envelope. */
export async function apiPost<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<T> {
  const response = await client.post<ApiEnvelope<T>>(url, data, config);
  return response.data.data;
}

/** Typed PUT that unwraps the envelope. */
export async function apiPut<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<T> {
  const response = await client.put<ApiEnvelope<T>>(url, data, config);
  return response.data.data;
}

/** Typed DELETE that unwraps the envelope. */
export async function apiDelete<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
  const response = await client.delete<ApiEnvelope<T>>(url, config);
  return response.data.data;
}

export default client;
