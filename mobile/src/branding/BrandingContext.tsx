import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';
import { BrandingData, buildTheme, ThemeColors } from './theme';
import { brandingService } from './BrandingService';
import { DEFAULT_BRANDING } from './fallback';

interface BrandingContextType {
  branding: BrandingData;
  theme: ThemeColors;
  isLoading: boolean;
  refreshBranding: (schoolId?: number) => Promise<void>;
}

const BrandingContext = createContext<BrandingContextType>({
  branding: { ...DEFAULT_BRANDING },
  theme: buildTheme(DEFAULT_BRANDING),
  isLoading: true,
  refreshBranding: async () => {},
});

export const useBranding = (): BrandingContextType => useContext(BrandingContext);

interface BrandingProviderProps {
  children: React.ReactNode;
  schoolId?: number;
}

export const BrandingProvider: React.FC<BrandingProviderProps> = ({ children, schoolId }) => {
  const [branding, setBranding] = useState<BrandingData>({ ...DEFAULT_BRANDING });
  const [theme, setTheme] = useState<ThemeColors>(buildTheme(DEFAULT_BRANDING));
  const [isLoading, setIsLoading] = useState(true);

  const loadBranding = useCallback(async (sid?: number) => {
    try {
      const data = await brandingService.getBranding(sid);
      setBranding(data);
      setTheme(buildTheme(data));
    } catch {
      setBranding({ ...DEFAULT_BRANDING });
      setTheme(buildTheme(DEFAULT_BRANDING));
    } finally {
      setIsLoading(false);
    }
  }, []);

  const refreshBranding = useCallback(async (sid?: number) => {
    try {
      const data = await brandingService.refreshBranding(sid);
      setBranding(data);
      setTheme(buildTheme(data));
    } catch {
      // Keep existing branding on refresh failure
    }
  }, []);

  useEffect(() => {
    loadBranding(schoolId);
  }, [loadBranding, schoolId]);

  return (
    <BrandingContext.Provider value={{ branding, theme, isLoading, refreshBranding }}>
      {children}
    </BrandingContext.Provider>
  );
};