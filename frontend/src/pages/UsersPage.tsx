import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Pencil, Plus, Search, Users } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { useAuth } from '../auth/AuthProvider'
import { StatusBadge } from '../components/StatusBadge'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { api } from '../lib/api'
import type { ApiResponse, PaginatedResponse } from '../types/api'
import { PageLoading } from './DashboardPage'

type Department = { id: number; name: string }
type Role = { id: number; name: string; display_name: string }
type AdminUser = {
  id: number
  name: string
  email: string
  employee_number?: string
  job_title?: string
  status: string
  department?: Department | null
  roles: Role[]
}

export function UsersPage() {
  const { can, user: currentUser } = useAuth()
  const client = useQueryClient()
  const [search, setSearch] = useState('')
  const [createOpen, setCreateOpen] = useState(false)
  const [roleUser, setRoleUser] = useState<AdminUser | null>(null)
  const [roleSelection, setRoleSelection] = useState<number[]>([])
  const [editUser, setEditUser] = useState<AdminUser | null>(null)
  const users = useQuery({ queryKey: ['users', search], queryFn: () => api<PaginatedResponse<AdminUser>>(`/api/v1/users${search ? `?search=${encodeURIComponent(search)}` : ''}`) })
  const roles = useQuery({ queryKey: ['roles'], queryFn: () => api<ApiResponse<Role[]>>('/api/v1/roles') })
  const departments = useQuery({ queryKey: ['departments'], queryFn: () => api<ApiResponse<Department[]>>('/api/v1/departments') })
  const refresh = () => client.invalidateQueries({ queryKey: ['users'] })
  const create = useMutation({ mutationFn: (payload: object) => api('/api/v1/users', { method: 'POST', body: JSON.stringify(payload) }), onSuccess: () => { setCreateOpen(false); refresh() } })
  const update = useMutation({ mutationFn: ({ id, payload }: { id: number; payload: object }) => api(`/api/v1/users/${id}`, { method: 'PATCH', body: JSON.stringify(payload) }), onSuccess: () => { setEditUser(null); refresh() } })
  const assignRole = useMutation({ mutationFn: ({ userId, roleIds }: { userId: number; roleIds: number[] }) => api(`/api/v1/users/${userId}/role`, { method: 'POST', body: JSON.stringify({ role_ids: roleIds }) }), onSuccess: () => { setRoleUser(null); setRoleSelection([]); refresh() } })
  const deactivate = useMutation({ mutationFn: (userId: number) => api(`/api/v1/users/${userId}/deactivate`, { method: 'POST' }), onSuccess: refresh })

  if (users.isLoading) return <PageLoading />

  const submitCreate = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    const values = Object.fromEntries(new FormData(event.currentTarget))
    create.mutate({
      name: values.name,
      email: values.email,
      employee_number: values.employee_number || null,
      job_title: values.job_title || null,
      department_id: values.department_id ? Number(values.department_id) : null,
      password: values.password,
      password_confirmation: values.password,
      role_ids: values.role_id ? [Number(values.role_id)] : undefined,
    })
  }

  const submitEdit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    if (!editUser) return
    const values = Object.fromEntries(new FormData(event.currentTarget))
    update.mutate({ id: editUser.id, payload: { name: values.name, email: values.email, employee_number: values.employee_number || null, job_title: values.job_title || null, department_id: values.department_id ? Number(values.department_id) : null } })
  }

  return <div className="page-workspace users-workspace">
    <WorkspaceHeader eyebrow="ACCESS & PEOPLE" title="Users" description="Review employee identity, department, account state, and access role." icon={Users} action={can('user.create') ? <button className="button button-primary" onClick={() => setCreateOpen(true)}><Plus/>Create user</button> : undefined} signals={[`${users.data?.meta.total ?? 0} managed identities`, 'Role-based access']}/>
    <section className="panel"><div className="search-field standalone-search"><Search/><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search name, email, employee number…"/></div><div className="table-wrap"><table><thead><tr><th>User</th><th>Department</th><th>Job title</th><th>Roles</th><th>Status</th><th>Actions</th></tr></thead><tbody>{users.data?.data.map((user) => <tr key={user.id}><td><div className="user-cell"><div className="avatar avatar-small">{user.name.slice(0,2).toUpperCase()}</div><div><strong>{user.name}</strong><small>{user.email}</small></div></div></td><td>{user.department?.name ?? '—'}</td><td>{user.job_title ?? '—'}</td><td><div className="role-chips">{user.roles.map((role) => <span key={role.id}>{role.display_name}</span>)}</div></td><td><StatusBadge value={user.status}/></td><td><div className="row-actions">{can('user.update') && <button onClick={() => setEditUser(user)}><Pencil size={14}/>Edit</button>}{can('user.assign_role') && <button onClick={() => { setRoleUser(user); setRoleSelection(user.roles.map((role) => role.id)) }}>Roles</button>}{can('user.update') && user.status === 'ACTIVE' && user.id !== currentUser?.id && <button className="danger-link" onClick={() => window.confirm(`Deactivate ${user.name}? They will be signed out and unable to log in.`) && deactivate.mutate(user.id)}>Deactivate</button>}</div></td></tr>)}{!users.data?.data.length && <tr><td colSpan={6} className="empty-cell"><Users/> No users found.</td></tr>}</tbody></table></div></section>

    {createOpen && <div className="modal-backdrop" onMouseDown={() => setCreateOpen(false)}><form className="modal" onSubmit={submitCreate} onMouseDown={(event) => event.stopPropagation()}><h2>Create user</h2><div className="form-grid"><label>Name<input name="name" required/></label><label>Email<input name="email" type="email" required/></label><label>Employee number<input name="employee_number"/></label><label>Job title<input name="job_title"/></label><label>Department<select name="department_id" defaultValue=""><option value="">No department</option>{departments.data?.data.map((department) => <option value={department.id} key={department.id}>{department.name}</option>)}</select></label><label>Role<select name="role_id" defaultValue=""><option value="">Employee (default)</option>{roles.data?.data.map((role) => <option value={role.id} key={role.id}>{role.display_name}</option>)}</select></label><label className="field-span-2">Password<input name="password" type="password" minLength={8} required/></label></div><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setCreateOpen(false)}>Cancel</button><button className="button button-primary" disabled={create.isPending}>Create user</button></div></form></div>}

    {editUser && <div className="modal-backdrop" onMouseDown={() => setEditUser(null)}><form className="modal" onSubmit={submitEdit} onMouseDown={(event) => event.stopPropagation()}><h2>Edit {editUser.name}</h2><div className="form-grid"><label>Name<input name="name" required defaultValue={editUser.name}/></label><label>Email<input name="email" type="email" required defaultValue={editUser.email}/></label><label>Employee number<input name="employee_number" defaultValue={editUser.employee_number}/></label><label>Job title<input name="job_title" defaultValue={editUser.job_title}/></label><label className="field-span-2">Department<select name="department_id" defaultValue={editUser.department?.id ?? ''}><option value="">No department</option>{departments.data?.data.map((department) => <option value={department.id} key={department.id}>{department.name}</option>)}</select></label></div><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setEditUser(null)}>Cancel</button><button className="button button-primary" disabled={update.isPending}>Save changes</button></div></form></div>}

    {roleUser && <div className="modal-backdrop" onMouseDown={() => setRoleUser(null)}><form className="modal" onSubmit={(event) => { event.preventDefault(); assignRole.mutate({ userId: roleUser.id, roleIds: roleSelection }) }} onMouseDown={(event) => event.stopPropagation()}><div><span className="eyebrow">ACCOUNT ACCESS</span><h2>Roles for {roleUser.name}</h2><p className="empty-copy">Choose one or more roles. The account receives the combined permissions from every selected role.</p></div><div className="role-picker">{roles.data?.data.map((role) => { const checked = roleSelection.includes(role.id); return <label className={checked ? 'checked' : ''} key={role.id}><input type="checkbox" checked={checked} onChange={() => setRoleSelection((current) => checked ? current.filter((id) => id !== role.id) : [...current, role.id])}/><span><strong>{role.display_name}</strong><small>{role.name}</small></span><span className="role-picker-check">{checked ? 'Selected' : 'Select'}</span></label> })}</div><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setRoleUser(null)}>Cancel</button><button className="button button-primary" disabled={assignRole.isPending || roleSelection.length === 0}>{assignRole.isPending ? 'Saving…' : 'Save roles'}</button></div></form></div>}
  </div>
}
