/* oxlint-disable react/only-export-components */
import { useQuery } from '@tanstack/react-query'
import {
  ArrowRight,
  ArrowUpRight,
  Bell,
  Boxes,
  CircleCheck,
  Clock3,
  Radio,
  ShieldCheck,
  Sparkles,
  TicketCheck,
} from 'lucide-react'
import type { CSSProperties } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'
import { StatusBadge } from '../components/StatusBadge'
import { api } from '../lib/api'
import type { ApiResponse, Ticket } from '../types/api'

type Dashboard = {
  tickets: { total: number; by_status: Record<string, number>; by_priority: Record<string, number> }
  assets: { total: number; by_status: Record<string, number> }
  notifications: { unread: number }
  recent_tickets: Ticket[]
}

const statusMeta = [
  { key: 'OPEN', label: 'Open', tone: 'open' },
  { key: 'ASSIGNED', label: 'Assigned', tone: 'assigned' },
  { key: 'IN_PROGRESS', label: 'In progress', tone: 'progress' },
  { key: 'WAITING_USER', label: 'Waiting', tone: 'waiting' },
  { key: 'RESOLVED', label: 'Resolved', tone: 'resolved' },
  { key: 'CLOSED', label: 'Closed', tone: 'closed' },
] as const

export function DashboardPage() {
  const { user } = useAuth()
  const { data, isLoading } = useQuery({ queryKey: ['dashboard'], queryFn: () => api<ApiResponse<Dashboard>>('/api/v1/dashboard') })
  const dashboard = data?.data
  if (isLoading) return <PageLoading />

  const totalTickets = dashboard?.tickets.total ?? 0
  const resolvedTickets = (dashboard?.tickets.by_status.RESOLVED ?? 0) + (dashboard?.tickets.by_status.CLOSED ?? 0)
  const resolutionRate = totalTickets ? Math.round((resolvedTickets / totalTickets) * 100) : 0
  const activeQueue = ['OPEN', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER']
    .reduce((total, status) => total + (dashboard?.tickets.by_status[status] ?? 0), 0)
  const criticalTickets = dashboard?.tickets.by_priority.CRITICAL ?? 0
  const stats = [
    { label: 'Visible tickets', value: totalTickets, note: `${activeQueue} in active queue`, icon: TicketCheck, tone: 'blue', progress: totalTickets ? (activeQueue / totalTickets) * 100 : 0 },
    { label: 'In progress', value: dashboard?.tickets.by_status.IN_PROGRESS ?? 0, note: 'Currently being worked', icon: Clock3, tone: 'amber', progress: totalTickets ? ((dashboard?.tickets.by_status.IN_PROGRESS ?? 0) / totalTickets) * 100 : 0 },
    { label: 'Resolved work', value: resolvedTickets, note: `${resolutionRate}% resolution coverage`, icon: CircleCheck, tone: 'green', progress: resolutionRate },
    { label: 'Visible assets', value: dashboard?.assets.total ?? 0, note: `${dashboard?.assets.by_status.ASSIGNED ?? 0} currently assigned`, icon: Boxes, tone: 'purple', progress: dashboard?.assets.total ? ((dashboard?.assets.by_status.ASSIGNED ?? 0) / dashboard.assets.total) * 100 : 0 },
  ]

  return <div className="executive-dashboard">
    <section className="dashboard-hero">
      <div className="dashboard-hero-glow" />
      <div className="dashboard-hero-copy">
        <span className="dashboard-live"><Radio size={13} /> LIVE OPERATIONS</span>
        <h1>Good day, {user?.name.split(' ')[0]}.</h1>
        <p>Your service desk is in view. Priorities, ownership, and asset signals are synchronized in one workspace.</p>
        <div className="dashboard-hero-actions">
          <Link className="button dashboard-primary-action" to="/tickets/create">Create ticket <ArrowUpRight size={17} /></Link>
          <Link className="dashboard-text-action" to="/tickets">Explore service desk <ArrowRight size={16} /></Link>
        </div>
      </div>
      <div className="operations-score">
        <div className="score-ring" style={{ '--score': `${resolutionRate * 3.6}deg` } as CSSProperties}>
          <div><strong>{resolutionRate}%</strong><span>resolved</span></div>
        </div>
        <div className="score-copy"><span>Operational snapshot</span><strong>{activeQueue} active requests</strong><small>Across your permitted scope</small></div>
      </div>
      <div className="dashboard-hero-meta">
        <span><ShieldCheck size={14} /> {user?.roles.join(' · ')}</span>
        <span><Sparkles size={14} /> Permission-aware view</span>
      </div>
    </section>

    <div className="dashboard-section-label"><span>Workspace pulse</span><small>Current operational totals</small></div>
    <div className="stats-grid premium-stats">{stats.map(({ label, value, note, icon: Icon, tone, progress }, index) => <article className={`stat-card metric-${tone}`} key={label}>
      <div className="stat-card-head"><div className={`stat-icon ${tone}`}><Icon /></div><span className="metric-index">0{index + 1}</span></div>
      <div className="stat-card-value"><strong>{value}</strong><span>{label}</span></div>
      <div className="metric-progress"><i style={{ width: `${Math.min(100, progress)}%` }} /></div>
      <small>{note}</small>
    </article>)}</div>

    <div className="dashboard-intelligence">
      <section className="panel workload-panel">
        <div className="panel-heading"><div><span className="panel-kicker">WORKLOAD INTELLIGENCE</span><h2>Ticket flow</h2><p>How work is distributed across every workflow state.</p></div><Link to="/tickets">Full queue <ArrowUpRight size={15} /></Link></div>
        <div className="workload-body">
          <div className="flow-summary"><strong>{activeQueue}</strong><span>active</span><small>of {totalTickets} visible tickets</small></div>
          <div className="flow-bars">{statusMeta.map(({ key, label, tone }) => {
            const count = dashboard?.tickets.by_status[key] ?? 0
            const width = totalTickets ? Math.max(count ? 7 : 0, (count / totalTickets) * 100) : 0
            return <div className="flow-row" key={key}><div><span>{label}</span><strong>{count}</strong></div><div className="flow-track"><i className={`flow-${tone}`} style={{ width: `${width}%` }} /></div></div>
          })}</div>
        </div>
      </section>

      <aside className="control-room">
        <div className="control-room-head"><div><span>CONTROL ROOM</span><h2>Needs attention</h2></div><div className="signal-orb"><i /></div></div>
        <p>Focused signals that may need your next decision.</p>
        <div className="control-signals">
          <Link to="/tickets?priority=CRITICAL"><span className="signal-icon signal-critical"><TicketCheck /></span><div><small>Critical queue</small><strong>{criticalTickets} ticket{criticalTickets === 1 ? '' : 's'}</strong></div><ArrowRight /></Link>
          <Link to="/notifications"><span className="signal-icon signal-notification"><Bell /></span><div><small>Unread updates</small><strong>{dashboard?.notifications.unread ?? 0} notification{dashboard?.notifications.unread === 1 ? '' : 's'}</strong></div><ArrowRight /></Link>
        </div>
        <div className="control-footer"><span><i /> Systems connected</span><small>Live workspace snapshot</small></div>
      </aside>
    </div>

    <section className="panel recent-work-panel">
      <div className="panel-heading"><div><span className="panel-kicker">LATEST ACTIVITY</span><h2>Recent tickets</h2><p>The newest service requests in your access scope.</p></div><Link to="/tickets">View all tickets <ArrowRight size={16} /></Link></div>
      <div className="table-wrap"><table><thead><tr><th>Ticket</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Updated</th></tr></thead><tbody>{dashboard?.recent_tickets.map((ticket) => <tr key={ticket.id}><td><Link className="table-primary" to={`/tickets/${ticket.id}`}>{ticket.ticket_number}</Link><small>{ticket.title}</small></td><td><StatusBadge value={ticket.status} /></td><td><StatusBadge value={ticket.priority} /></td><td>{ticket.assigned_technician?.name ?? 'Unassigned'}</td><td>{formatDate(ticket.updated_at)}</td></tr>)}{!dashboard?.recent_tickets.length && <tr><td colSpan={5} className="empty-cell">No tickets yet.</td></tr>}</tbody></table></div>
    </section>
  </div>
}

export function PageLoading() {
  return <div className="page-skeleton" aria-label="Loading data">
    <div className="skeleton-heading"><span /><span /></div>
    <div className="skeleton-stats">{Array.from({ length: 4 }, (_, index) => <span key={index} />)}</div>
    <div className="skeleton-panel"><span /><span /><span /><span /></div>
  </div>
}

export function formatDate(value: string) {
  return new Intl.DateTimeFormat('en', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
}
