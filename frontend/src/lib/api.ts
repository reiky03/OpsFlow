export class ApiError extends Error {
  status: number
  errors: Record<string, string[]>

  constructor(message: string, status: number, errors: Record<string, string[]> = {}) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

let csrfReady = false

export async function ensureCsrf(): Promise<void> {
  if (csrfReady) return
  const response = await fetch('/sanctum/csrf-cookie', { credentials: 'include' })
  if (!response.ok && response.status !== 204) throw new ApiError('Unable to initialize secure session.', response.status)
  csrfReady = true
}

function csrfToken(): string | undefined {
  const match = document.cookie.split('; ').find((cookie) => cookie.startsWith('XSRF-TOKEN='))
  return match ? decodeURIComponent(match.split('=').slice(1).join('=')) : undefined
}

type ApiOptions = RequestInit & { silent?: boolean }

export async function api<T>(path: string, options: ApiOptions = {}): Promise<T> {
  const { silent = false, ...requestOptions } = options
  const method = requestOptions.method?.toUpperCase() ?? 'GET'
  if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) await ensureCsrf()
  const isForm = requestOptions.body instanceof FormData
  const token = csrfToken()
  const response = await fetch(path, {
    ...requestOptions,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      ...(isForm ? {} : { 'Content-Type': 'application/json' }),
      ...(token ? { 'X-XSRF-TOKEN': token } : {}),
      ...requestOptions.headers,
    },
  })
  const payload = response.status === 204 ? null : await response.json().catch(() => null)
  if (!response.ok) {
    const message = payload?.message ?? 'Request failed.'
    if (!silent) window.dispatchEvent(new CustomEvent('opsflow:toast', { detail: { message, tone: 'error' } }))
    throw new ApiError(message, response.status, payload?.errors ?? {})
  }
  if (!['GET', 'HEAD', 'OPTIONS'].includes(method) && payload?.message) {
    window.dispatchEvent(new CustomEvent('opsflow:toast', { detail: { message: payload.message, tone: 'success' } }))
  }
  return payload as T
}
