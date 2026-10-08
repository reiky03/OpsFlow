import { useQuery } from '@tanstack/react-query'
import { Bell, Boxes, ChevronRight, ClipboardList, CornerDownLeft, Gauge, KeyRound, LogOut, Menu, Search, Settings2, ShieldCheck, UserRound, Users, X } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'
import type { PointerEvent as ReactPointerEvent } from 'react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'
import { api } from '../lib/api'
import type { PaginatedResponse } from '../types/api'

type NotificationSummary = { id: string }

export function AppShell() {
  const { user, logout, can } = useAuth()
  const [open, setOpen] = useState(false)
  const [logoutOpen, setLogoutOpen] = useState(false)
  const [loggingOut, setLoggingOut] = useState(false)
  const [commandOpen, setCommandOpen] = useState(false)
  const [commandQuery, setCommandQuery] = useState('')
  const [selectedCommand, setSelectedCommand] = useState(0)
  const ambientRef = useRef<HTMLDivElement>(null)
  const location = useLocation()
  const navigate = useNavigate()
  const notificationSummary = useQuery({
    queryKey: ['notification-summary'],
    queryFn: () => api<PaginatedResponse<NotificationSummary>>('/api/v1/notifications?per_page=20', { silent: true }),
    refetchInterval: 30_000,
  })
  const unreadCount = notificationSummary.data?.meta.unread_count ?? 0
  const items = [
    { to: '/dashboard', label: 'Dashboard', icon: Gauge, show: true },
    { to: '/tickets', label: 'Tickets', icon: ClipboardList, show: true },
    { to: '/assets', label: can('asset.view_all') ? 'Assets' : 'My Assets', icon: Boxes, show: can('asset.view_all') || can('asset.view_own') },
    { to: '/users', label: 'Users', icon: Users, show: can('user.view') },
    { to: '/access-control', label: 'Access Control', icon: KeyRound, show: can('role.manage') },
    { to: '/audit-logs', label: 'Audit Logs', icon: ShieldCheck, show: can('audit.view') },
    { to: '/master-data', label: 'Master Data', icon: Settings2, show: can('ticket_category.manage') || can('department.manage') },
    { to: '/profile', label: 'Profile', icon: UserRound, show: true },
  ]
  const commands = [...items.filter((item) => item.show), { to: '/notifications', label: 'Notifications', icon: Bell, show: true }]
  const filteredCommands = commands.filter((item) => item.label.toLowerCase().includes(commandQuery.toLowerCase()))
  useEffect(() => {
    const handleShortcut = (event: KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault()
        setSelectedCommand(0)
        setCommandOpen((value) => !value)
      }
      if (event.key === 'Escape') setCommandOpen(false)
    }
    window.addEventListener('keydown', handleShortcut)
    return () => window.removeEventListener('keydown', handleShortcut)
  }, [])
  const currentPage = items.find((item) => location.pathname.startsWith(item.to))?.label
    ?? (location.pathname.startsWith('/notifications') ? 'Notifications' : 'Workspace')
  const confirmLogout = async () => {
    setLoggingOut(true)
    try { await logout() }
    finally { setLoggingOut(false); setLogoutOpen(false) }
  }
  const runCommand = (to: string) => {
    navigate(to)
    setCommandOpen(false)
    setCommandQuery('')
  }
  const trackAmbient = (event: ReactPointerEvent<HTMLDivElement>) => {
    ambientRef.current?.style.setProperty('--ambient-x', `${event.clientX}px`)
    ambientRef.current?.style.setProperty('--ambient-y', `${event.clientY}px`)
  }
  return <div className="app-layout" onPointerMove={trackAmbient}>
    <div ref={ambientRef} className="workspace-ambient" aria-hidden="true" />
    <aside className={`sidebar ${open ? 'sidebar-open' : ''}`}>
      <div className="brand"><div className="brand-mark">OF</div><div><strong>OpsFlow</strong><small>IT Operations</small></div></div>
      <button className="icon-button sidebar-close" onClick={() => setOpen(false)} aria-label="Close navigation"><X /></button>
      <span className="nav-label">Workspace</span>
      <nav>{items.filter((item) => item.show).map(({ to, label, icon: Icon }) => <NavLink key={to} to={to} onClick={() => setOpen(false)}><Icon size={19}/><span>{label}</span><ChevronRight className="nav-chevron" size={15}/></NavLink>)}</nav>
      <div className="sidebar-trust"><ShieldCheck/><div><strong>Protected workspace</strong><small>Policy-enforced access</small></div><span/></div>
      <div className="sidebar-user"><div className="avatar presence-avatar">{user?.name.slice(0, 2).toUpperCase()}<span/></div><div><strong>{user?.name}</strong><small>{user?.roles.join(' · ')}</small></div></div>
    </aside>
    {open && <button className="sidebar-scrim" onClick={() => setOpen(false)} aria-label="Close navigation" />}
    <main className="main-area">
      <header className="topbar"><button className="icon-button mobile-menu" onClick={() => setOpen(true)} aria-label="Open navigation"><Menu /></button><div className="topbar-context"><span>OpsFlow</span><ChevronRight size={14}/><strong>{currentPage}</strong></div><div className="topbar-spacer"/><span className="workspace-live"><i/>Workspace live</span><button className="command-trigger" onClick={() => { setSelectedCommand(0); setCommandOpen(true) }}><Search size={15}/><span>Search workspace</span><kbd>{navigator.platform.includes('Mac') ? '⌘' : 'Ctrl'} K</kbd></button><NavLink className="icon-button notification-button" to="/notifications" aria-label={`${unreadCount} unread notifications`}><Bell/>{unreadCount > 0 && <span className="notification-count">{unreadCount > 99 ? '99+' : unreadCount}</span>}</NavLink><span className="topbar-divider"/><button className="logout-button" onClick={() => setLogoutOpen(true)}><LogOut size={18}/><span>Sign out</span></button></header>
      <div className="page-container"><Outlet /></div>
    </main>
    {commandOpen && <div className="modal-backdrop command-backdrop" role="presentation" onMouseDown={() => setCommandOpen(false)}><section className="command-palette" role="dialog" aria-modal="true" aria-label="Quick navigation" onMouseDown={(event) => event.stopPropagation()}><div className="command-search"><Search/><input autoFocus value={commandQuery} onChange={(event) => { setCommandQuery(event.target.value); setSelectedCommand(0) }} onKeyDown={(event) => { if (event.key === 'ArrowDown' && filteredCommands.length) { event.preventDefault(); setSelectedCommand((value) => Math.min(value + 1, filteredCommands.length - 1)) } if (event.key === 'ArrowUp' && filteredCommands.length) { event.preventDefault(); setSelectedCommand((value) => Math.max(value - 1, 0)) } if (event.key === 'Enter' && filteredCommands[selectedCommand]) runCommand(filteredCommands[selectedCommand].to) }} placeholder="Where do you want to go?"/><kbd>ESC</kbd></div><div className="command-label">Quick navigation</div><div className="command-results">{filteredCommands.map(({ to, label, icon: Icon }, index) => <button key={to} className={index === selectedCommand ? 'selected' : ''} onMouseEnter={() => setSelectedCommand(index)} onClick={() => runCommand(to)}><span className="command-icon"><Icon/></span><span><strong>{label}</strong><small>Open {label.toLowerCase()}</small></span>{index === selectedCommand && <CornerDownLeft className="command-enter"/>}</button>)}{filteredCommands.length === 0 && <div className="command-empty"><Search/><span>No matching destination</span></div>}</div><footer><span><kbd>↑</kbd><kbd>↓</kbd> to navigate</span><span><kbd>↵</kbd> to open</span></footer></section></div>}
    {logoutOpen && <div className="modal-backdrop" role="presentation" onMouseDown={() => setLogoutOpen(false)}><section className="modal confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="logout-title" onMouseDown={(event) => event.stopPropagation()}><div className="confirm-icon"><LogOut/></div><div><span className="eyebrow">SECURE SESSION</span><h2 id="logout-title">Sign out of OpsFlow?</h2><p>Your current session will end. You will need to sign in again to access the workspace.</p></div><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setLogoutOpen(false)}>Stay signed in</button><button type="button" className="button button-danger" onClick={() => void confirmLogout()} disabled={loggingOut}>{loggingOut ? 'Signing out…' : 'Yes, sign out'}</button></div></section></div>}
  </div>
}
