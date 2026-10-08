import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'

export function PermissionRoute({ anyOf }: { anyOf: string[] }) {
  const { can } = useAuth()

  return anyOf.some(can) ? <Outlet /> : <Navigate to="/dashboard" replace />
}
