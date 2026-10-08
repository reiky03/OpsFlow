import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ArrowLeft, CheckCircle2, CircleDot, Clock3, Download, FilePenLine, Flag, History, Hourglass, LoaderCircle, MessageSquare, Paperclip, RefreshCw, Send, Sparkles, UserCheck, Wrench, XCircle, type LucideIcon } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { Link, useParams } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'
import { StatusBadge } from '../components/StatusBadge'
import { api } from '../lib/api'
import type { ApiResponse, PaginatedResponse, Ticket, TicketActivity, User } from '../types/api'
import { formatDate, PageLoading } from './DashboardPage'

type ActivityVisual = { icon: LucideIcon; tone: string; label: string }

function activityVisual(activity: TicketActivity): ActivityVisual {
  if (activity.type === 'STATUS_CHANGED') {
    const status = activity.new_values?.status
    if (status === 'RESOLVED' || status === 'CLOSED') return { icon: CheckCircle2, tone: 'success', label: status === 'CLOSED' ? 'Ticket closed' : 'Work resolved' }
    if (status === 'CANCELLED') return { icon: XCircle, tone: 'danger', label: 'Ticket cancelled' }
    if (status === 'IN_PROGRESS') return { icon: LoaderCircle, tone: 'progress', label: 'Work started' }
    if (status === 'WAITING_USER') return { icon: Hourglass, tone: 'waiting', label: 'Waiting for user' }
    return { icon: RefreshCw, tone: 'updated', label: 'Status updated' }
  }
  const visuals: Record<TicketActivity['type'], ActivityVisual> = {
    CREATED: { icon: Sparkles, tone: 'created', label: 'Ticket created' },
    ASSIGNED: { icon: UserCheck, tone: 'assigned', label: 'Technician assigned' },
    STATUS_CHANGED: { icon: RefreshCw, tone: 'updated', label: 'Status updated' },
    PRIORITY_CHANGED: { icon: Flag, tone: 'priority', label: 'Priority changed' },
    COMMENT_ADDED: { icon: MessageSquare, tone: 'comment', label: 'Conversation updated' },
    ATTACHMENT_ADDED: { icon: Paperclip, tone: 'attachment', label: 'File attached' },
    UPDATED: { icon: FilePenLine, tone: 'updated', label: 'Ticket updated' },
  }
  return visuals[activity.type]
}

function ActivityChange({ activity }: { activity: TicketActivity }) {
  const field = activity.new_values?.status !== undefined ? 'status' : activity.new_values?.priority !== undefined ? 'priority' : null
  if (!field) return null
  const before = activity.old_values?.[field]
  const after = activity.new_values?.[field]
  return <div className="activity-change">{before && <StatusBadge value={String(before)}/>}<span>→</span>{after && <StatusBadge value={String(after)}/>}</div>
}

export function TicketDetailPage() {
  const { id } = useParams()
  const { can, user } = useAuth()
  const client = useQueryClient()
  const [internal, setInternal] = useState(false)
  const ticket = useQuery({ queryKey: ['ticket', id], queryFn: () => api<ApiResponse<Ticket>>(`/api/v1/tickets/${id}`) })
  const technicians = useQuery({ queryKey: ['users', 'technicians'], queryFn: () => api<PaginatedResponse<User>>('/api/v1/users?role=TECHNICIAN&per_page=100'), enabled: can('ticket.assign') })
  const refresh = () => client.invalidateQueries({ queryKey: ['ticket', id] })
  const comment = useMutation({ mutationFn: (payload: object) => api(`/api/v1/tickets/${id}/comments`, { method: 'POST', body: JSON.stringify(payload) }), onSuccess: refresh })
  const transition = useMutation({ mutationFn: (status: string) => api(`/api/v1/tickets/${id}/status`, { method: 'POST', body: JSON.stringify({ status }) }), onSuccess: refresh })
  const priority = useMutation({ mutationFn: (value: string) => api(`/api/v1/tickets/${id}/priority`, { method: 'POST', body: JSON.stringify({ priority: value }) }), onSuccess: refresh })
  const assign = useMutation({ mutationFn: (technicianId: number) => api(`/api/v1/tickets/${id}/assign`, { method: 'POST', body: JSON.stringify({ technician_id: technicianId }) }), onSuccess: refresh })
  const upload = useMutation({ mutationFn: (file: File) => { const data = new FormData(); data.append('file', file); return api(`/api/v1/tickets/${id}/attachments`, { method: 'POST', body: data }) }, onSuccess: refresh })
  if (ticket.isLoading) return <PageLoading />
  if (!ticket.data) return <div className="empty-state"><h2>Ticket not found</h2><Link to="/tickets">Return to tickets</Link></div>

  const item = ticket.data.data
  const canUpdateStatus = can('ticket.update_status') || (item.status === 'RESOLVED' && item.reporter.id === user?.id)
  const nextStatuses: Record<string, string[]> = { ASSIGNED: ['IN_PROGRESS', 'CANCELLED'], IN_PROGRESS: ['WAITING_USER', 'RESOLVED'], WAITING_USER: ['IN_PROGRESS'], RESOLVED: ['CLOSED'], OPEN: ['CANCELLED'] }
  const statusIcons: Record<string, LucideIcon> = { IN_PROGRESS: Wrench, WAITING_USER: Clock3, RESOLVED: CheckCircle2, CLOSED: CheckCircle2, CANCELLED: XCircle }
  const submitComment = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    const form = event.currentTarget
    const message = new FormData(form).get('message')
    comment.mutate({ message, is_internal: internal }, { onSuccess: () => { form.reset(); setInternal(false) } })
  }

  return <>
    <section className="ticket-hero">
      <Link className="back-link" to="/tickets"><ArrowLeft size={16}/>Back to tickets</Link>
      <div className="ticket-hero-main"><div><div className="ticket-kicker"><span>{item.ticket_number}</span><StatusBadge value={item.status}/></div><h1>{item.title}</h1><p>Reported by {item.reporter.name} · {formatDate(item.created_at)}</p></div><div className="ticket-hero-marks"><div><small>Priority</small><StatusBadge value={item.priority}/></div><div><small>Category</small><strong>{item.category.name}</strong></div></div></div>
    </section>

    <div className="detail-layout ticket-detail-layout"><div className="detail-main">
      <section className="panel ticket-description"><div className="section-icon"><CircleDot/></div><div><span className="section-label">REQUEST DETAILS</span><h2>Description</h2><p className="preserve-lines">{item.description}</p></div></section>

      <section className="panel conversation-panel"><div className="panel-heading"><div><h2><MessageSquare size={19}/> Conversation</h2><p>Public replies and IT-only operational notes.</p></div><span className="count-pill">{item.comments?.length ?? 0} messages</span></div><div className="conversation-list">{item.comments?.map((entry) => <article key={entry.id} className={`message-row ${entry.is_internal ? 'internal-comment' : ''}`}><div className="message-avatar">{entry.user.name.slice(0, 2).toUpperCase()}</div><div className="message-bubble"><div className="comment-meta"><strong>{entry.user.name}</strong>{entry.is_internal && <span>Internal note</span>}<time>{formatDate(entry.created_at)}</time></div><p>{entry.message}</p></div></article>)}{!item.comments?.length && <p className="empty-copy">No comments yet. Start the conversation below.</p>}</div><form className="comment-form composer" onSubmit={submitComment}><textarea name="message" required rows={3} placeholder="Write a helpful update…"/><div>{can('ticket.internal_note') ? <label className="checkbox"><input type="checkbox" checked={internal} onChange={(event) => setInternal(event.target.checked)}/>Visible to IT only</label> : <span/>}<button className="button button-primary" disabled={comment.isPending}><Send size={16}/>{comment.isPending ? 'Sending…' : 'Send reply'}</button></div></form></section>

      <section className="panel activity-panel"><div className="panel-heading"><div><h2><History size={19}/> Activity</h2><p>A complete, immutable trail of this service request.</p></div><span className="count-pill">{item.activities?.length ?? 0} events</span></div><div className="activity-stream">{item.activities?.map((activity) => { const visual = activityVisual(activity); const Icon = visual.icon; return <article key={activity.id} className={`activity-event tone-${visual.tone}`}><div className="activity-rail"><div className="activity-icon"><Icon/></div></div><div className="activity-card"><div className="activity-card-top"><div><span>{visual.label}</span><strong>{activity.description}</strong></div><time>{formatDate(activity.created_at)}</time></div><ActivityChange activity={activity}/>{activity.actor && <small>Performed by {activity.actor.name}</small>}</div></article> })}{!item.activities?.length && <p className="empty-copy">No activity has been recorded.</p>}</div></section>
    </div>

    <aside className="detail-sidebar ticket-sidebar">
      <section className="panel metadata ticket-metadata"><div className="panel-heading"><div><h2>Ticket overview</h2><p>Ownership and service context.</p></div></div><dl><div><dt>Reporter</dt><dd><span className="person-value"><i>{item.reporter.name.slice(0, 2).toUpperCase()}</i>{item.reporter.name}</span></dd></div><div><dt>Technician</dt><dd>{item.assigned_technician ? <span className="person-value"><i>{item.assigned_technician.name.slice(0, 2).toUpperCase()}</i>{item.assigned_technician.name}</span> : 'Unassigned'}</dd></div><div><dt>Category</dt><dd>{item.category.name}</dd></div>{item.asset && <div><dt>Asset</dt><dd><Link className="inline-link" to={`/assets/${item.asset.id}`}>{item.asset.asset_code}</Link></dd></div>}<div><dt>Last updated</dt><dd>{formatDate(item.updated_at)}</dd></div></dl></section>

      {canUpdateStatus && nextStatuses[item.status]?.length > 0 && <section className="panel workflow-card"><span className="section-label">NEXT ACTION</span><h2>{item.status === 'RESOLVED' && item.reporter.id === user?.id ? 'Confirm resolution' : 'Move work forward'}</h2><p>Choose the appropriate next workflow state.</p><div className="button-stack status-actions">{nextStatuses[item.status].map((status) => { const Icon = statusIcons[status] ?? RefreshCw; return <button type="button" className={`button status-action status-${status.toLowerCase().replaceAll('_', '-')}`} key={status} onClick={() => transition.mutate(status)} disabled={transition.isPending}><Icon size={17}/><span>{status.replaceAll('_', ' ')}</span></button> })}</div></section>}

      {can('ticket.update_priority') && <section className="panel compact-form"><span className="section-label">TRIAGE</span><h2>Priority</h2><select aria-label="Ticket priority" value={item.priority} onChange={(event) => priority.mutate(event.target.value)} disabled={priority.isPending}>{['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'].map((value) => <option key={value}>{value}</option>)}</select></section>}
      {can('ticket.assign') && <section className="panel compact-form"><span className="section-label">OWNERSHIP</span><h2>Assign technician</h2><select aria-label="Assigned technician" value={item.assigned_technician?.id ?? ''} onChange={(event) => event.target.value && assign.mutate(Number(event.target.value))} disabled={assign.isPending}><option value="">Select technician</option>{technicians.data?.data.map((technician) => <option key={technician.id} value={technician.id}>{technician.name}</option>)}</select></section>}
      <section className="panel attachment-panel"><div className="panel-heading"><div><h2><Paperclip size={18}/> Attachments</h2><p>Private files shared on this ticket.</p></div></div><div className="attachment-list">{item.attachments?.map((file) => <a key={file.id} href={file.download_url}><Paperclip/><span>{file.original_name}</span><Download/></a>)}{!item.attachments?.length && <p className="empty-copy">No files attached.</p>}</div><label className="button button-secondary button-full file-button"><Paperclip size={16}/>{upload.isPending ? 'Uploading…' : 'Upload file'}<input type="file" accept=".jpg,.jpeg,.png,.pdf,.txt,.log" disabled={upload.isPending} onChange={(event) => event.target.files?.[0] && upload.mutate(event.target.files[0])}/></label></section>
    </aside></div>
  </>
}
