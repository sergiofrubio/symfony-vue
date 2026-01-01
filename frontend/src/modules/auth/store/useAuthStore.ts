import { defineStore } from 'pinia';
import { fetchMe as apiFetchMe } from '@/api/auth'

export interface AuthUser {
  id: number;
  email: string;
  roles: string[];
  is_active: boolean;
  last_login: string | null;
}

const STORAGE_KEY = 'jwt_token';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: (localStorage.getItem(STORAGE_KEY) as string) || null as string | null,
    user: null as AuthUser | null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
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
    setUser(user: AuthUser | null) {
      this.user = user;
    },
    async fetchMe(): Promise<AuthUser | null> {
      if (!this.token) {
        this.user = null;
        return null;
      }

      try {
        const data = await apiFetchMe(this.token)

        if (data === null) {
          this.setToken(null)
          this.setUser(null)
          return null
        }

        this.setUser(data as AuthUser)
        return this.user
      } catch (err) {
        this.setUser(null)
        throw err
      }
    },
    logout() {
      this.setToken(null);
      this.setUser(null);
    },
  },
});

export default useAuthStore;
