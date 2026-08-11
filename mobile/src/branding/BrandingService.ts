import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { BrandingData, sanitizeBranding } from './theme';
import { DEFAULT_BRANDING } from './fallback';
import { API_BASE_URL } from '../config/api';

const BRANDING_CACHE_KEY = '@school_erp_branding';
const BRANDING_API_URL = `${API_BASE_URL}/branding`;
const CACHE_TTL_MS = 30 * 60 * 1000;

interface BrandingResponse {
  success: boolean;
  message: string;
  data: Partial<BrandingData>;
}

class BrandingService {
  private cachedBranding: BrandingData | null = null;
  private cacheTimestamp: number = 0;

  async getBranding(schoolId?: number): Promise<BrandingData> {
    const cached = await this.getCachedBranding();

    if (cached && this.isCacheValid()) {
      this.cachedBranding = cached;
      this.cacheTimestamp = Date.now();
      return cached;
    }

    const fetched = await this.fetchBranding(schoolId);

    if (fetched) {
      await this.cacheBranding(fetched);
      this.cachedBranding = fetched;
      this.cacheTimestamp = Date.now();
      return fetched;
    }

    return cached ?? { ...DEFAULT_BRANDING };
  }

  async refreshBranding(schoolId?: number): Promise<BrandingData> {
    await this.clearCache();
    this.cachedBranding = null;
    this.cacheTimestamp = 0;

    return this.getBranding(schoolId);
  }

  getCachedBrandingSync(): BrandingData | null {
    return this.cachedBranding;
  }

  private async fetchBranding(schoolId?: number): Promise<BrandingData | null> {
    try {
      const params: Record<string, number> = {};
      if (schoolId && schoolId > 0) {
        params.school_id = schoolId;
      }

      const headers: Record<string, string> = {};
      if (schoolId && schoolId > 0) {
        headers['X-School-Id'] = String(schoolId);
      }

      const response = await axios.get<BrandingResponse>(BRANDING_API_URL, {
        params,
        headers,
        timeout: 10000,
      });

      if (response.data?.success && response.data?.data) {
        return sanitizeBranding(response.data.data);
      }

      return null;
    } catch {
      return null;
    }
  }

  private async getCachedBranding(): Promise<BrandingData | null> {
    try {
      const raw = await AsyncStorage.getItem(BRANDING_CACHE_KEY);

      if (!raw) {
        return null;
      }

      const parsed = JSON.parse(raw);

      if (!parsed || typeof parsed !== 'object') {
        return null;
      }

      return sanitizeBranding(parsed as Partial<BrandingData>);
    } catch {
      return null;
    }
  }

  private async cacheBranding(branding: BrandingData): Promise<void> {
    try {
      await AsyncStorage.setItem(BRANDING_CACHE_KEY, JSON.stringify(branding));
    } catch {
      // Silently fail cache writes
    }
  }

  private async clearCache(): Promise<void> {
    try {
      await AsyncStorage.removeItem(BRANDING_CACHE_KEY);
    } catch {
      // Silently fail cache clears
    }
  }

  private isCacheValid(): boolean {
    return Date.now() - this.cacheTimestamp < CACHE_TTL_MS;
  }
}

export const brandingService = new BrandingService();