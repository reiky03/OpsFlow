import { useQuery } from '@tanstack/react-query'
import { ArrowLeft, Boxes, ClipboardList, History } from 'lucide-react'
import { Link, useParams } from 'react-router-dom'
import { StatusBadge } from '../components/StatusBadge'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { api } from '../lib/api'
import type { ApiResponse, Asset, Ticket } from '../types/api'
import { formatDate, PageLoading } from './DashboardPage'

type Assignment = { id: number; user: { id: number; name: string; email: string }; assigned_at: string; returned_at?: string | null; notes?: string | null }
type AssetDetail = Asset & { assignments: Assignment[]; tickets: Ticket[] }

export function AssetDetailPage() {
  const { id } = useParams()
  const asset = useQuery({ queryKey: ['asset', id], queryFn: () => api<ApiResponse<AssetDetail>>(`/api/v1/assets/${id}`) })

  if (asset.isLoading) return <PageLoading />
  if (!asset.data) return <div className="empty-state"><Boxes/><h2>Asset not found</h2><Link to="/assets">Return to assets</Link></div>

  const item = asset.data.data
  const subtitle = `${item.name} · ${[item.brand, item.model].filter(Boolean).join(' ') || item.category}`

  return <div className="page-workspace asset-detail-workspace">
    <WorkspaceHeader eyebrow="ASSET RECORD" title={item.asset_code} description={subtitle} icon={Boxes} backLink={<Link className="back-link" to="/assets"><ArrowLeft size={16}/>Back to assets</Link>} action={<StatusBadge value={item.status}/>} signals={[item.assigned_user ? `Custodian: ${item.assigned_user.name}` : 'Available inventory', `${item.assignments.length} custody events`]}/>
    <div className="detail-layout">
      <div className="detail-main">
        <section className="panel asset-history-panel">
          <div className="panel-heading"><div><h2><History size={19}/> Assignment history</h2><p>Permanent custody trail for this company asset.</p></div></div>
          <div className="timeline activities">{item.assignments.map((entry) => <article key={entry.id}><div className="timeline-dot"><History/></div><div><strong>Assigned to {entry.user.name}</strong><p>{formatDate(entry.assigned_at)}{entry.returned_at ? ` — returned ${formatDate(entry.returned_at)}` : ' — current assignment'}</p>{entry.notes && <p>{entry.notes}</p>}</div></article>)}{!item.assignments.length && <p className="empty-copy">This asset has never been assigned.</p>}</div>
        </section>
        <section className="panel related-work-panel">
          <div className="panel-heading"><div><h2><ClipboardList size={19}/> Related tickets</h2><p>Repair and service history linked to this asset.</p></div></div>
          <div className="table-wrap"><table><thead><tr><th>Ticket</th><th>Status</th><th>Priority</th><th>Reporter</th><th>Created</th></tr></thead><tbody>{item.tickets.map((ticket) => <tr key={ticket.id}><td><Link className="table-primary" to={`/tickets/${ticket.id}`}>{ticket.ticket_number}</Link><small>{ticket.title}</small></td><td><StatusBadge value={ticket.status}/></td><td><StatusBadge value={ticket.priority}/></td><td>{ticket.reporter.name}</td><td>{formatDate(ticket.created_at)}</td></tr>)}{!item.tickets.length && <tr><td colSpan={5} className="empty-cell">No tickets are linked to this asset.</td></tr>}</tbody></table></div>
        </section>
      </div>
      <aside className="detail-sidebar">
        <section className="panel metadata asset-metadata"><span className="panel-kicker">REGISTERED IDENTITY</span><h2>Asset details</h2><dl><div><dt>Category</dt><dd>{item.category}</dd></div><div><dt>Brand</dt><dd>{item.brand ?? '—'}</dd></div><div><dt>Model</dt><dd>{item.model ?? '—'}</dd></div><div><dt>Serial number</dt><dd>{item.serial_number ?? '—'}</dd></div><div><dt>Assigned to</dt><dd>{item.assigned_user?.name ?? 'Not assigned'}</dd></div><div><dt>Purchase date</dt><dd>{item.purchase_date ?? '—'}</dd></div><div><dt>Warranty end</dt><dd>{item.warranty_end_date ?? '—'}</dd></div></dl></section>
        {item.notes && <section className="panel asset-notes"><h2>Notes</h2><p>{item.notes}</p></section>}
      </aside>
    </div>
  </div>
}
