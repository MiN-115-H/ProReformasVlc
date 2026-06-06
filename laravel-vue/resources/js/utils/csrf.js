let csrfTokenCache = '';

const readTokenFromMeta = () => {
  if (typeof document === 'undefined') return '';
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

export const getCsrfToken = async () => {
  if (csrfTokenCache) return csrfTokenCache;

  const metaToken = readTokenFromMeta();
  if (metaToken) {
    csrfTokenCache = metaToken;
    return csrfTokenCache;
  }

  const response = await fetch('/csrf-token', {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('No se pudo obtener el token CSRF.');
  }

  const payload = await response.json().catch(() => null);
  const token = payload?.token || '';

  if (!token) {
    throw new Error('Respuesta CSRF invalida.');
  }

  csrfTokenCache = token;
  return csrfTokenCache;
};
