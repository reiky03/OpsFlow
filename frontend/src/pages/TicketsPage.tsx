import { useQuery } from '@tanstack/react-query'
import { Plus, Search, TicketCheck } from 'lucide-react'
import { useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { StatusBadge } from '../components/StatusBadge'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { api } from '../lib/api'
import type { PaginatedResponse, Ticket } from '../types/api'
import { formatDate, PageLoading } from './DashboardPage'

export function TicketsPage() {
  const [params, setParams] = useSearchParams()
  const [search, setSearch] = useState(params.get('search') ?? '')
  const queryString = params.toString()
  const tickets = useQuery({ queryKey: ['tickets', queryString], queryFn: () => api<PaginatedResponse<Ticket>>(`/api/v1/tickets?${queryString}`) })
  const update = (key: string, value: string) => { const next = new URLSearchParams(params); if (value) next.set(key, value); else next.delete(key); next.delete('page'); setParams(next) }
  if (tickets.isLoading) return <PageLoading />
  return <div className="page-workspace tickets-workspace"><WorkspaceHeader eyebrow="SERVICE DESK" title="Tickets" description="Search, prioritize, and follow work from request to closure." icon={TicketCheck} action={<Link className="button button-primary" to="/tickets/create"><Plus size={18}/>Create ticket</Link>} signals={[`${tickets.data?.meta.total ?? 0} visible records`, 'Live workflow']}/><section className="panel data-workspace"><div className="filters"><form className="search-field" onSubmit={(event) => { event.preventDefault(); update('search', search) }}><Search size={18}/><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search ticket, reporter, technician…"/></form><select value={params.get('status') ?? ''} onChange={(event) => update('status', event.target.value)}><option value="">All statuses</option>{['OPEN','ASSIGNED','IN_PROGRESS','WAITING_USER','RESOLVED','CLOSED','CANCELLED'].map((v) => <option key={v}>{v}</option>)}</select><select value={params.get('priority') ?? ''} onChange={(event) => update('priority', event.target.value)}><option value="">All priorities</option>{['LOW','MEDIUM','HIGH','CRITICAL'].map((v) => <option key={v}>{v}</option>)}</select></div><div className="table-wrap"><table><thead><tr><th>Ticket</th><th>Category</th><th>Status</th><th>Priority</th><th>Technician</th><th>Created</th></tr></thead><tbody>{tickets.data?.data.map((ticket) => <tr key={ticket.id}><td><Link className="table-primary" to={`/tickets/${ticket.id}`}>{ticket.ticket_number}</Link><small>{ticket.title}</small></td><td>{ticket.category.name}</td><td><StatusBadge value={ticket.status}/></td><td><StatusBadge value={ticket.priority}/></td><td>{ticket.assigned_technician?.name ?? 'Unassigned'}</td><td>{formatDate(ticket.created_at)}</td></tr>)}{!tickets.data?.data.length && <tr><td colSpan={6} className="empty-cell">No tickets match these filters.</td></tr>}</tbody></table></div><div className="pagination"><span>{tickets.data?.meta.total ?? 0} tickets</span><div><button disabled={(tickets.data?.meta.current_page ?? 1) <= 1} onClick={() => update('page', String((tickets.data?.meta.current_page ?? 1) - 1))}>Previous</button><span>Page {tickets.data?.meta.current_page ?? 1} of {tickets.data?.meta.last_page ?? 1}</span><button disabled={(tickets.data?.meta.current_page ?? 1) >= (tickets.data?.meta.last_page ?? 1)} onClick={() => update('page', String((tickets.data?.meta.current_page ?? 1) + 1))}>Next</button></div></div></section></div>
}
