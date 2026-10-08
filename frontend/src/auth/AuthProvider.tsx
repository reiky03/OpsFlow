/* oxlint-disable react/only-export-components */
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { createContext, useContext, type ReactNode } from 'react'
import { api } from '../lib/api'
import type { ApiResponse, User } from '../types/api'

type AuthValue = {
  user: User | null
  loading: boolean
  login: (email: string, password: string, remember: boolean) => Promise<void>
  logout: () => Promise<void>
  can: (permission: string) => boolean
}

const AuthContext = createContext<AuthValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()
  const session = useQuery({
    queryKey: ['auth', 'me'],
    queryFn: () => api<ApiResponse<User>>('/api/v1/auth/me', { silent: true }),
    retry: false,
    staleTime: 60_000,
  })

  async function login(email: string, password: string, remember: boolean) {
    const response = await api<ApiResponse<User>>('/api/v1/auth/login', { method: 'POST', body: JSON.stringify({ email, password, remember }) })
    queryClient.setQueryData(['auth', 'me'], response)
  }

  async function logout() {
    await api('/api/v1/auth/logout', { method: 'POST' })
    queryClient.setQueryData(['auth', 'me'], null)
    queryClient.clear()
  }

  const user = session.data?.data ?? null
  return <AuthContext.Provider value={{ user, loading: session.isLoading, login, logout, can: (permission) => user?.permissions.includes(permission) ?? false }}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const value = useContext(AuthContext)
  if (!value) throw new Error('useAuth must be used within AuthProvider')
  return value
}
