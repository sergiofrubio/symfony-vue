import { defineStore } from 'pinia';
import { fetchMe as apiFetchMe } from '@/api/auth';

export interface Company {
  id: number;
  name: string;
  taxId: string;
  currency: string;
}

export interface AuthUser {
  id: number;
  email: string;
  roles: string[];
  permissions?: string[];
  companies?: Company[];
  defaultCompany?: Company | null;
  is_active: boolean;
  last_login: string | null;
}

const STORAGE_KEY = 'jwt_token';
const COMPANY_STORAGE_KEY = 'active_company_id';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: (localStorage.getItem(STORAGE_KEY) as string) || null as string | null,
    user: null as AuthUser | null,
    activeCompanyId: (localStorage.getItem(COMPANY_STORAGE_KEY) ? Number(localStorage.getItem(COMPANY_STORAGE_KEY)) : null) as number | null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    activeCompany: (state) => {
      if (!state.user || !state.user.companies) return null;
      return state.user.companies.find(c => c.id === state.activeCompanyId) || state.user.companies[0] || null;
    },
    permissions: (state) => state.user?.permissions || [],
    hasPermission: (state) => (permissionCode: string) => {
      if (state.user?.roles?.includes('ROLE_ADMIN') || state.user?.roles?.includes('admin')) {
        return true;
      }
      return (state.user?.permissions || []).includes(permissionCode);
    },
  },
  actions: {
    setToken(token: string | null) {
      this.token = token;
      if (token) {
        localStorage.setItem(STORAGE_KEY, token);
      } else {
        localStorage.removeItem(STORAGE_KEY);
      }
    },
    setActiveCompany(companyId: number) {
      this.activeCompanyId = companyId;
      localStorage.setItem(COMPANY_STORAGE_KEY, String(companyId));
    },
    setUser(user: AuthUser | null) {
      this.user = user;
      if (user && user.companies && user.companies.length > 0) {
        if (!this.activeCompanyId || !user.companies.some(c => c.id === this.activeCompanyId)) {
          this.setActiveCompany(user.defaultCompany?.id || user.companies[0].id);
        }
      }
    },
    async fetchMe(): Promise<AuthUser | null> {
      if (!this.token) {
        this.user = null;
        return null;
      }

      try {
        const data = await apiFetchMe(this.token);

        if (data === null) {
          this.setToken(null);
          this.setUser(null);
          return null;
        }

        this.setUser(data as AuthUser);
        return this.user;
      } catch (err) {
        this.setUser(null);
        throw err;
      }
    },
    logout() {
      this.setToken(null);
      this.setUser(null);
      localStorage.removeItem(COMPANY_STORAGE_KEY);
      this.activeCompanyId = null;
    },
  },
});

export default useAuthStore;
