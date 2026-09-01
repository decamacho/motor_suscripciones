const BASE_URL = 'http://localhost:8000/api';

async function request(path, options = {}) {
  const { body, ...rest } = options;

  const res = await fetch(`${BASE_URL}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...rest,
    body: body ? JSON.stringify(body) : undefined,
  });

  const json = await res.json().catch(() => null);

  if (!res.ok || json?.success === false) {
    const message = json?.message || `Error ${res.status}`
    const error = new Error(message)
    error.status = res.status
    throw error
  }

  return json?.data;
}

export const api = {
  get: (path) => request(path),
  post: (path, body) => request(path, { method: 'POST', body }),
  put: (path, body) => request(path, { method: 'PUT', body }),
  delete: (path) => request(path, { method: 'DELETE' }),
}
