import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ArrowUpRight, Bell, Check, CheckCheck, CircleAlert, ClipboardCheck, Inbox, PackageCheck, UserRoundCheck } from 'lucide-react'
import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import type { PaginatedResponse } from '../types/api'
import { formatDate, PageLoading } from './DashboardPage'

type Notification = {
  id: string
  title: string
  message: string
  data: { ticket_id?: number; asset_id?: number; status?: string; priority?: string }
  read_at: string | null
  created_at: string
}

function NotificationIcon({ item }: { item: Notification }) {
  const title = item.title.toLowerCase()
  if (item.data.asset_id) return <PackageCheck/>
  if (title.includes('assigned')) return <UserRoundCheck/>
  if (title.includes('status') || item.data.status) return <ClipboardCheck/>
  if (title.includes('priority') || item.data.priority) return <CircleAlert/>
  return <Bell/>
}

export function NotificationsPage() {
  const client = useQueryClient()
  const navigate = useNavigate()
  const [filter, setFilter] = useState<'all' | 'unread'>('all')
  const query = filter === 'unread' ? '?unread=1&per_page=50' : '?per_page=50'
  const notifications = useQuery({ queryKey: ['notifications', filter], queryFn: () => api<PaginatedResponse<Notification>>(`/api/v1/notifications${query}`) })
  const refresh = async () => {
    await Promise.all([
      client.invalidateQueries({ queryKey: ['notifications'] }),
      client.invalidateQueries({ queryKey: ['notification-summary'] }),
      client.invalidateQueries({ queryKey: ['dashboard'] }),
    ])
  }
  const readAll = useMutation({ mutationFn: () => api('/api/v1/notifications/read-all', { method: 'POST' }), onSuccess: refresh })
  const markRead = useMutation({ mutationFn: (id: string) => api(`/api/v1/notifications/${id}/read`, { method: 'POST' }), onSuccess: refresh })
  if (notifications.isLoading) return <PageLoading />
  const unreadCount = notifications.data?.meta.unread_count ?? 0
  const openNotification = async (item: Notification) => {
    if (!item.read_at) await markRead.mutateAsync(item.id)
    if (item.data.ticket_id) navigate(`/tickets/${item.data.ticket_id}`)
    else if (item.data.asset_id) navigate(`/assets/${item.data.asset_id}`)
  }

  return <>
    <div className="page-heading notification-heading"><div><span className="eyebrow">YOUR INBOX</span><h1>Notifications</h1><p>Assignments, workflow changes, and updates that need your attention.</p></div><button className="button button-secondary" disabled={unreadCount === 0 || readAll.isPending} onClick={() => readAll.mutate()}><CheckCheck size={18}/>{readAll.isPending ? 'Updating…' : 'Mark all read'}</button></div>
    <section className="notification-workspace">
      <div className="notification-toolbar"><div className="notification-tabs" role="tablist"><button className={filter === 'all' ? 'active' : ''} onClick={() => setFilter('all')}>All <span>{notifications.data?.meta.total ?? 0}</span></button><button className={filter === 'unread' ? 'active' : ''} onClick={() => setFilter('unread')}>Unread <span>{unreadCount}</span></button></div><div className="inbox-health"><span className={unreadCount ? 'has-unread' : ''}/>{unreadCount ? `${unreadCount} awaiting review` : 'Inbox is clear'}</div></div>
      <div className="notification-feed">{notifications.data?.data.map((item) => <button key={item.id} className={`notification-card ${item.read_at ? 'read' : 'unread'}`} onClick={() => void openNotification(item)} disabled={markRead.isPending}><div className="notification-icon"><NotificationIcon item={item}/></div><div className="notification-copy"><div><strong>{item.title}</strong>{!item.read_at && <span className="new-label">NEW</span>}</div><p>{item.message}</p><time>{formatDate(item.created_at)}</time></div><div className="notification-action">{item.read_at ? <Check size={17}/> : <span/>}<ArrowUpRight size={18}/></div></button>)}{!notifications.data?.data.length && <div className="empty-state notification-empty"><div><Inbox/></div><h2>{filter === 'unread' ? 'Nothing needs your attention' : 'You are all caught up'}</h2><p>{filter === 'unread' ? 'Every notification has been reviewed.' : 'New operational updates will appear here.'}</p></div>}</div>
    </section>
  </>
}
