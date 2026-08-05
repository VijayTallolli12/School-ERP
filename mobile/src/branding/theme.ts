import { DEFAULT_BRANDING, DEFAULT_THEME } from './fallback';

export interface BrandingData {
  schoolName: string;
  schoolLogo: string | null;
  favicon: string | null;
  primaryColor: string;
  secondaryColor: string;
  schoolWebsite: string;
  schoolAddress: string;
  schoolPhone: string;
  appName: string;
}

export interface ThemeColors {
  primary: string;
  primaryLight: string;
  secondary: string;
  success: string;
  warning: string;
  danger: string;
  info: string;
  background: string;
  backgroundCard: string;
  text: string;
  textSecondary: string;
  textMuted: string;
}

export function normalizeColor(color: string | null | undefined): string {
  if (!color || color.trim() === '') {
    return DEFAULT_THEME.primary;
  }

  const trimmed = color.trim();

  if (trimmed.startsWith('#') && (trimmed.length === 7 || trimmed.length === 4)) {
    return trimmed;
  }

  if (/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/.test(trimmed)) {
    return trimmed;
  }

  if (/^#[0-9a-fA-F]{6}$/.test(trimmed)) {
    return trimmed;
  }

  return DEFAULT_THEME.primary;
}

export function buildTheme(branding: BrandingData) {
  return {
    ...DEFAULT_THEME,
    primary: normalizeColor(branding.primaryColor),
    secondary: normalizeColor(branding.secondaryColor),
    primaryLight: `${normalizeColor(branding.primaryColor)}14`,
  };
}

export function sanitizeBranding(data: Partial<BrandingData> | null | undefined): BrandingData {
  if (!data) {
    return { ...DEFAULT_BRANDING };
  }

  return {
    schoolName: data.schoolName && data.schoolName.trim() !== ''
      ? data.schoolName.trim()
      : DEFAULT_BRANDING.schoolName,
    schoolLogo: data.schoolLogo && data.schoolLogo.trim() !== ''
      ? data.schoolLogo.trim()
      : null,
    favicon: data.favicon && data.favicon.trim() !== ''
      ? data.favicon.trim()
      : null,
    primaryColor: normalizeColor(data.primaryColor),
    secondaryColor: normalizeColor(data.secondaryColor),
    schoolWebsite: data.schoolWebsite ?? DEFAULT_BRANDING.schoolWebsite,
    schoolAddress: data.schoolAddress ?? DEFAULT_BRANDING.schoolAddress,
    schoolPhone: data.schoolPhone ?? DEFAULT_BRANDING.schoolPhone,
    appName: data.appName && data.appName.trim() !== ''
      ? data.appName.trim()
      : DEFAULT_BRANDING.appName,
  };
}