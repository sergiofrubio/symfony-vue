export function getAuthHeaders(token?: string | null): Record<string, string> {
  const t = token ?? localStorage.getItem('jwt_token');
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };
  if (t) {
    headers['Authorization'] = `Bearer ${t}`;
  }
  return headers;
}

export async function fetchMe(token?: string | null): Promise<any | null> {
  const res = await fetch('/api/users/me', {
    method: 'GET',
    headers: getAuthHeaders(token),
  });

  if (res.status === 401) {
    return null;
  }

  if (!res.ok) {
    throw new Error(`fetchMe failed: ${res.status}`);
  }

  const data = await res.json();
  return data;
}
