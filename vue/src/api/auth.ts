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
  // Normalizar roles: si el backend devuelve objetos Role, extraer el `slug` o `name`.
  if (Array.isArray(data.roles)) {
    data.roles = data.roles
      .map((r: any) => typeof r === 'string' ? r : (r?.slug ?? r?.name ?? null))
      .filter(Boolean);
  } else {
    data.roles = []
  }

  return data;
}
