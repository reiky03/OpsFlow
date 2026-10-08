import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Check, ChevronRight, KeyRound, LockKeyhole, RotateCcw, Save, ShieldCheck, UsersRound } from 'lucide-react'
import { useMemo, useState } from 'react'
import { api } from '../lib/api'
import type { ApiResponse } from '../types/api'
import { PageLoading } from './DashboardPage'

type Permission = { id: number; name: string; description: string }
type Role = {
  id: number
  name: string
  display_name: string
  description: string
  is_system: boolean
  users_count: number
  permissions: string[]
}

const groupMeta: Record<string, { label: string; description: string }> = {
  ticket: { label: 'Tickets', description: 'Service request visibility and workflow actions.' },
  asset: { label: 'Assets', description: 'Inventory visibility, editing, and custody.' },
  user: { label: 'Users', description: 'People administration and role assignment.' },
  role: { label: 'Access control', description: 'Role and permission configuration.' },
  department: { label: 'Departments', description: 'Organization master data.' },
  ticket_category: { label: 'Categories', description: 'Ticket classification master data.' },
  audit: { label: 'Audit', description: 'Immutable system activity visibility.' },
}

export function AccessControlPage() {
  const client = useQueryClient()
  const roles = useQuery({ queryKey: ['roles'], queryFn: () => api<ApiResponse<Role[]>>('/api/v1/roles') })
  const permissions = useQuery({ queryKey: ['permissions'], queryFn: () => api<ApiResponse<Permission[]>>('/api/v1/permissions') })
  const [selectedId, setSelectedId] = useState<number | null>(null)
  const [draft, setDraft] = useState<string[] | null>(null)

  const selectedRole = roles.data?.data.find((role) => role.id === selectedId) ?? roles.data?.data[0]
  const locked = selectedRole?.name === 'ADMIN'
  const selectedPermissions = locked ? permissions.data?.data.map((item) => item.name) ?? [] : selectedRole?.permissions ?? []
  const effectiveDraft = new Set(draft ?? selectedPermissions)

  const groups = useMemo(() => {
    const result = new Map<string, Permission[]>()
    permissions.data?.data.forEach((permission) => {
      const prefix = permission.name.startsWith('ticket_category.') ? 'ticket_category' : permission.name.split('.')[0]
      result.set(prefix, [...(result.get(prefix) ?? []), permission])
    })
    return [...result.entries()]
  }, [permissions.data])

  const update = useMutation({
    mutationFn: () => api(`/api/v1/roles/${selectedRole?.id}/permissions`, {
      method: 'PUT',
      body: JSON.stringify({ permission_ids: permissions.data?.data.filter((permission) => effectiveDraft.has(permission.name)).map((permission) => permission.id) ?? [] }),
    }),
    onSuccess: () => { setDraft(null); client.invalidateQueries({ queryKey: ['roles'] }) },
  })

  if (roles.isLoading || permissions.isLoading) return <PageLoading />

  const original = new Set(selectedRole?.permissions ?? [])
  const dirty = !locked && (original.size !== effectiveDraft.size || [...original].some((permission) => !effectiveDraft.has(permission)))
  const toggle = (name: string) => {
    const next = new Set(effectiveDraft)
    if (next.has(name)) next.delete(name)
    else next.add(name)
    setDraft([...next])
  }

  return <>
    <div className="page-heading"><div><span className="eyebrow">SECURITY & GOVERNANCE</span><h1>Access control</h1><p>Shape what each team role can see and do across OpsFlow.</p></div><div className="security-pill"><ShieldCheck size={17}/><span>Backend enforced</span></div></div>
    <div className="access-layout">
      <aside className="role-rail panel">
        <div className="role-rail-heading"><div className="master-icon"><KeyRound/></div><div><strong>System roles</strong><span>{roles.data?.data.length ?? 0} access profiles</span></div></div>
        <div className="role-list">{roles.data?.data.map((role) => <button className={role.id === selectedRole?.id ? 'active' : ''} key={role.id} onClick={() => { setSelectedId(role.id); setDraft(null) }}><span className="role-symbol">{role.name === 'ADMIN' ? <LockKeyhole/> : <UsersRound/>}</span><span><strong>{role.display_name}</strong><small>{role.users_count} {role.users_count === 1 ? 'user' : 'users'}</small></span><ChevronRight/></button>)}</div>
      </aside>
      <section className="permission-workspace panel">
        <div className="permission-header"><div><div className="permission-title"><h2>{selectedRole?.display_name}</h2>{locked && <span className="locked-badge"><LockKeyhole/>Protected</span>}</div><p>{selectedRole?.description}</p></div><div className="permission-score"><strong>{effectiveDraft.size}</strong><span>of {permissions.data?.data.length ?? 0}<br/>permissions</span></div></div>
        {locked && <div className="protected-note"><ShieldCheck/><div><strong>Administrator is always unrestricted</strong><p>This system role bypasses permission checks to prevent an accidental administrative lockout.</p></div></div>}
        <div className="permission-groups">{groups.map(([key, items]) => {
          const enabled = items.filter((permission) => effectiveDraft.has(permission.name)).length
          const allEnabled = enabled === items.length
          const meta = groupMeta[key] ?? { label: key, description: 'System capabilities.' }
          return <article className="permission-group" key={key}><div className="permission-group-heading"><div><h3>{meta.label}</h3><p>{meta.description}</p></div><button disabled={locked} onClick={() => { const next = new Set(effectiveDraft); items.forEach((item) => { if (allEnabled) next.delete(item.name); else next.add(item.name) }); setDraft([...next]) }}>{enabled}/{items.length} enabled</button></div><div className="permission-options">{items.map((permission) => { const checked = effectiveDraft.has(permission.name); return <button type="button" disabled={locked} className={checked ? 'permission-option checked' : 'permission-option'} key={permission.id} onClick={() => toggle(permission.name)}><span className="permission-check">{checked && <Check/>}</span><span><strong>{permission.description}</strong><code>{permission.name}</code></span></button> })}</div></article>
        })}</div>
        <div className={`permission-actions ${dirty ? 'visible' : ''}`}><div><strong>Unsaved permission changes</strong><span>Changes apply to every user with this role.</span></div><button className="button button-secondary" disabled={!dirty} onClick={() => setDraft(null)}><RotateCcw size={16}/>Reset</button><button className="button button-primary" disabled={!dirty || update.isPending} onClick={() => update.mutate()}><Save size={16}/>{update.isPending ? 'Saving…' : 'Save access'}</button></div>
      </section>
    </div>
  </>
}
