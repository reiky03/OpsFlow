import { Building2, BriefcaseBusiness, Mail, Shield, UserRound } from 'lucide-react'
import { useAuth } from '../auth/AuthProvider'
import { StatusBadge } from '../components/StatusBadge'
import { WorkspaceHeader } from '../components/WorkspaceHeader'

export function ProfilePage() {
  const { user } = useAuth()
  if (!user) return null
  return <div className="page-workspace profile-workspace"><WorkspaceHeader eyebrow="YOUR ACCOUNT" title="Profile" description="Your verified identity and effective access context in OpsFlow." icon={UserRound} action={<StatusBadge value={user.status}/>} signals={['Identity verified', `${user.permissions.length} effective permissions`]}/><div className="profile-grid"><section className="panel profile-card"><div className="avatar profile-avatar">{user.name.slice(0,2).toUpperCase()}</div><h2>{user.name}</h2><p>{user.employee_number ?? 'No employee number'}</p><StatusBadge value={user.status}/></section><section className="panel profile-details"><h2>Account details</h2><dl><div><Mail/><dt>Email</dt><dd>{user.email}</dd></div><div><Building2/><dt>Department</dt><dd>{user.department?.name ?? '—'}</dd></div><div><BriefcaseBusiness/><dt>Job title</dt><dd>{user.job_title ?? '—'}</dd></div><div><Shield/><dt>Roles</dt><dd>{user.roles.join(', ')}</dd></div></dl></section><section className="panel profile-permissions"><h2>Effective permissions</h2><div>{user.permissions.map((permission) => <code key={permission}>{permission}</code>)}</div></section></div></div>
}
