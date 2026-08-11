/**
 * AuthContext — single source of truth for the authenticated session.
 *
 * - Restores a persisted token on launch and validates it via GET /me.
 * - Stores token / school_id in AsyncStorage so every API request carries
 *   the correct Bearer + X-School-Id headers.
 * - Exposes login / logout / refreshProfile and role-aware context.
 */
import AsyncStorage from '@react-native-async-storage/async-storage';
import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import { fetchMe, login as apiLogin, logout as apiLogout } from '../api/auth';
import {
  SCHOOL_ID_STORAGE_KEY,
  TOKEN_STORAGE_KEY,
} from '../api/client';
import { MeResponseData, ParentChild, RoleName, StudentContext, UserProfile } from '../api/types';

type AuthStatus = 'loading' | 'authenticated' | 'guest';

interface AuthContextType {
  status: AuthStatus;
  user: UserProfile | null;
  roles: RoleName[];
  permissions: string[];
  schoolId: number | null;
  parentUuid: string | null;
  children: ParentChild[];
  student: StudentContext | null;
  primaryRole: RoleName | null;
  signIn: (email: string, password: string, deviceName?: string) => Promise<void>;
  signOut: () => Promise<void>;
  refreshProfile: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType>({
  status: 'loading',
  user: null,
  roles: [],
  permissions: [],
  schoolId: null,
  parentUuid: null,
  children: [],
  student: null,
  primaryRole: null,
  signIn: async () => {},
  signOut: async () => {},
  refreshProfile: async () => {},
});

export const useAuth = (): AuthContextType => useContext(AuthContext);

const ROLE_ORDER: RoleName[] = ['Student', 'Parent', 'Teacher', 'Driver'];

function pickRole(roles: string[]): RoleName | null {
  for (const candidate of ROLE_ORDER) {
    if (roles.includes(candidate)) {
      return candidate;
    }
  }
  return null;
}

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [user, setUser] = useState<UserProfile | null>(null);
  const [roles, setRoles] = useState<RoleName[]>([]);
  const [permissions, setPermissions] = useState<string[]>([]);
  const [schoolId, setSchoolId] = useState<number | null>(null);
  const [parentUuid, setParentUuid] = useState<string | null>(null);
  const [linkedChildren, setLinkedChildren] = useState<ParentChild[]>([]);
  const [student, setStudent] = useState<StudentContext | null>(null);

  const applyMe = useCallback((data: MeResponseData) => {
    setUser(data.user);
    setRoles(data.roles.filter((r): r is RoleName => ROLE_ORDER.includes(r as RoleName)));
    setPermissions(data.permissions);
    setParentUuid(data.parent_uuid ?? null);
    setLinkedChildren(data.students ?? []);
    setStudent(data.student ?? null);
  }, []);

  const restoreSession = useCallback(async () => {
    const token = await AsyncStorage.getItem(TOKEN_STORAGE_KEY);

    if (!token) {
      setStatus('guest');
      return;
    }

    try {
      const me = await fetchMe();
      applyMe(me);
      const storedSchoolId = await AsyncStorage.getItem(SCHOOL_ID_STORAGE_KEY);
      setSchoolId(storedSchoolId ? Number(storedSchoolId) : null);
      setStatus('authenticated');
    } catch (error) {
      // A 401/expired token simply returns the user to the login screen.
      await AsyncStorage.multiRemove([TOKEN_STORAGE_KEY, SCHOOL_ID_STORAGE_KEY]);
      setStatus('guest');
    }
  }, [applyMe]);

  useEffect(() => {
    restoreSession();
  }, [restoreSession]);

  const signIn = useCallback(
    async (email: string, password: string, deviceName = 'school-erp-mobile') => {
      const data = await apiLogin({ email, password, device_name: deviceName });

      await AsyncStorage.setItem(TOKEN_STORAGE_KEY, data.token);
      if (data.school_id) {
        await AsyncStorage.setItem(SCHOOL_ID_STORAGE_KEY, String(data.school_id));
        setSchoolId(data.school_id);
      }

      // Normalize through /me so session state always comes from the
      // canonical role-aware profile endpoint.
      const me = await fetchMe();
      applyMe(me);

      setUser(me.user);
      setStatus('authenticated');
    },
    [applyMe],
  );

  const signOut = useCallback(async () => {
    await apiLogout();
    await AsyncStorage.multiRemove([TOKEN_STORAGE_KEY, SCHOOL_ID_STORAGE_KEY]);
    setUser(null);
    setRoles([]);
    setPermissions([]);
    setSchoolId(null);
    setParentUuid(null);
    setLinkedChildren([]);
    setStudent(null);
    setStatus('guest');
  }, []);

  const refreshProfile = useCallback(async () => {
    const me = await fetchMe();
    applyMe(me);
  }, [applyMe]);

  const primaryRole = useMemo(() => pickRole(roles), [roles]);

  const value = useMemo(
    () => ({
      status,
      user,
      roles,
      permissions,
      schoolId,
      parentUuid,
      children: linkedChildren,
      student,
      primaryRole,
      signIn,
      signOut,
      refreshProfile,
    }),
    [status, user, roles, permissions, schoolId, parentUuid, linkedChildren, student, primaryRole, signIn, signOut, refreshProfile],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
