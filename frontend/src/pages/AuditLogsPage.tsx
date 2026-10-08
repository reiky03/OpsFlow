import { useQuery } from '@tanstack/react-query'
import { ShieldCheck } from 'lucide-react'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { api } from '../lib/api'
import type { PaginatedResponse } from '../types/api'
import { formatDate, PageLoading } from './DashboardPage'

type Audit = { id: number; user?: { name: string } | null; action: string; entity_type?: string | null; entity_id?: number | null; ip_address?: string; created_at: string }

export function AuditLogsPage() {
  const logs = useQuery({ queryKey: ['audit-logs'], queryFn: () => api<PaginatedResponse<Audit>>('/api/v1/audit-logs') })
  if (logs.isLoading) return <PageLoading />
  return <div className="page-workspace audit-workspace"><WorkspaceHeader eyebrow="GOVERNANCE" title="Audit logs" description="Immutable records of significant operational changes across the workspace." icon={ShieldCheck} signals={[`${logs.data?.meta.total ?? 0} recorded events`, 'Tamper-evident trail']}/><section className="panel data-workspace audit-ledger"><div className="table-wrap"><table><thead><tr><th>Action</th><th>Actor</th><th>Entity</th><th>IP address</th><th>Time</th></tr></thead><tbody>{logs.data?.data.map((log) => <tr key={log.id}><td><strong>{log.action.replaceAll('_',' ')}</strong></td><td>{log.user?.name ?? 'System'}</td><td>{log.entity_type ? `${log.entity_type} #${log.entity_id}` : '—'}</td><td><code>{log.ip_address ?? '—'}</code></td><td>{formatDate(log.created_at)}</td></tr>)}{!logs.data?.data.length && <tr><td colSpan={5} className="empty-cell"><ShieldCheck/> No audit events yet.</td></tr>}</tbody></table></div></section></div>
}
