import { lazy, Suspense, useEffect } from 'react'
import { Navigate, Route, Routes, useLocation } from 'react-router-dom'
import { AppShell } from './components/AppShell'
import { ProtectedRoute } from './components/ProtectedRoute'
import { PermissionRoute } from './components/PermissionRoute'
import { ToastViewport } from './components/ToastViewport'

const AccessControlPage = lazy(() => import('./pages/AccessControlPage').then(({ AccessControlPage }) => ({ default: AccessControlPage })))
const AssetDetailPage = lazy(() => import('./pages/AssetDetailPage').then(({ AssetDetailPage }) => ({ default: AssetDetailPage })))
const AssetsPage = lazy(() => import('./pages/AssetsPage').then(({ AssetsPage }) => ({ default: AssetsPage })))
const AuditLogsPage = lazy(() => import('./pages/AuditLogsPage').then(({ AuditLogsPage }) => ({ default: AuditLogsPage })))
const CreateTicketPage = lazy(() => import('./pages/CreateTicketPage').then(({ CreateTicketPage }) => ({ default: CreateTicketPage })))
const DashboardPage = lazy(() => import('./pages/DashboardPage').then(({ DashboardPage }) => ({ default: DashboardPage })))
const LoginPage = lazy(() => import('./pages/LoginPage').then(({ LoginPage }) => ({ default: LoginPage })))
const MasterDataPage = lazy(() => import('./pages/MasterDataPage').then(({ MasterDataPage }) => ({ default: MasterDataPage })))
const NotificationsPage = lazy(() => import('./pages/NotificationsPage').then(({ NotificationsPage }) => ({ default: NotificationsPage })))
const ForgotPasswordPage = lazy(() => import('./pages/PasswordPages').then(({ ForgotPasswordPage }) => ({ default: ForgotPasswordPage })))
const ResetPasswordPage = lazy(() => import('./pages/PasswordPages').then(({ ResetPasswordPage }) => ({ default: ResetPasswordPage })))
const ProfilePage = lazy(() => import('./pages/ProfilePage').then(({ ProfilePage }) => ({ default: ProfilePage })))
const TicketDetailPage = lazy(() => import('./pages/TicketDetailPage').then(({ TicketDetailPage }) => ({ default: TicketDetailPage })))
const TicketsPage = lazy(() => import('./pages/TicketsPage').then(({ TicketsPage }) => ({ default: TicketsPage })))
const UsersPage = lazy(() => import('./pages/UsersPage').then(({ UsersPage }) => ({ default: UsersPage })))

function ScrollToTop() {
  const { pathname } = useLocation()
  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])
  return null
}

function App() {
  return <><ScrollToTop/><ToastViewport/><Suspense fallback={<div className="app-loader"><span className="spinner" />Loading workspace…</div>}><Routes>
    <Route path="/login" element={<LoginPage/>}/>
    <Route path="/forgot-password" element={<ForgotPasswordPage/>}/>
    <Route path="/reset-password" element={<ResetPasswordPage/>}/>
    <Route element={<ProtectedRoute/>}><Route element={<AppShell/>}>
      <Route path="/dashboard" element={<DashboardPage/>}/>
      <Route path="/tickets" element={<TicketsPage/>}/><Route path="/tickets/:id" element={<TicketDetailPage/>}/>
      <Route element={<PermissionRoute anyOf={['ticket.create']}/> }><Route path="/tickets/create" element={<CreateTicketPage/>}/></Route>
      <Route element={<PermissionRoute anyOf={['asset.view_own','asset.view_all']}/> }><Route path="/assets" element={<AssetsPage/>}/><Route path="/assets/:id" element={<AssetDetailPage/>}/></Route>
      <Route element={<PermissionRoute anyOf={['user.view']}/> }><Route path="/users" element={<UsersPage/>}/></Route>
      <Route element={<PermissionRoute anyOf={['role.manage']}/> }><Route path="/access-control" element={<AccessControlPage/>}/></Route>
      <Route path="/notifications" element={<NotificationsPage/>}/>
      <Route element={<PermissionRoute anyOf={['audit.view']}/> }><Route path="/audit-logs" element={<AuditLogsPage/>}/></Route>
      <Route path="/profile" element={<ProfilePage/>}/>
      <Route element={<PermissionRoute anyOf={['ticket_category.manage','department.manage']}/> }><Route path="/master-data" element={<MasterDataPage/>}/></Route>
    </Route></Route>
    <Route path="*" element={<Navigate to="/dashboard" replace/>}/>
  </Routes></Suspense></>
}

export default App
