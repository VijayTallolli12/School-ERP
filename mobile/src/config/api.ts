/**
 * Central API configuration.
 *
 * The base URL is read from the `EXPO_PUBLIC_API_URL` environment variable
 * (set in mobile/.env / eas.json build profiles) so development, preview and
 * production builds can each point at the correct LIVE backend.
 *
 * `http://localhost:8000/api/v1` is only a development fallback — it is never
 * compiled into production unless EXPO_PUBLIC_API_URL is explicitly set to it.
 */
const DEV_FALLBACK_API_URL = 'http://localhost:8000/api/v1';

export const API_BASE_URL: string =
  process.env.EXPO_PUBLIC_API_URL?.trim().replace(/\/+$/, '') || DEV_FALLBACK_API_URL;
