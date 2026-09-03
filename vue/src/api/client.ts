import { useAuthStore } from '@/modules/auth/store/useAuthStore';

export function getHeaders(token?: string | null): Record<string, string> {
  const auth = useAuthStore();
  const t = token ?? auth.token;
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/ld+json, application/json',
  };

  if (t) {
    headers['Authorization'] = `Bearer ${t}`;
  }

  const activeCompanyId = localStorage.getItem('active_company_id');
  if (activeCompanyId) {
    headers['X-Company-Id'] = activeCompanyId;
  }

  return headers;
}

export async function apiClient(url: string, options: RequestInit = {}): Promise<any> {
  const headers = {
    ...getHeaders(),
    ...(options.headers as Record<string, string> || {}),
  };

  const response = await fetch(url, {
    ...options,
    headers,
  });

  if (response.status === 401) {
    const auth = useAuthStore();
    auth.logout();
    window.location.href = '/login';
    throw new Error('Unauthorized');
  }

  if (response.status === 204) {
    return null;
  }

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    const message = errorData['hydra:description'] || errorData.detail || errorData.error || `HTTP ${response.status}`;
    throw new Error(message);
  }

  return response.json();
}
