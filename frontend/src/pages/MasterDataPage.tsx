import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Building2, Pencil, Plus, Tags } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { api } from '../lib/api'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import type { ApiResponse } from '../types/api'
import { PageLoading } from './DashboardPage'

type Item = { id: number; name: string; description?: string | null; is_active?: boolean }
type Kind = 'category' | 'department'

export function MasterDataPage() {
  const client = useQueryClient()
  const [dialog, setDialog] = useState<{ kind: Kind; item?: Item } | null>(null)
  const categories = useQuery({ queryKey: ['ticket-categories'], queryFn: () => api<ApiResponse<Item[]>>('/api/v1/ticket-categories') })
  const departments = useQuery({ queryKey: ['departments'], queryFn: () => api<ApiResponse<Item[]>>('/api/v1/departments') })
  const save = useMutation({
    mutationFn: ({ kind, item, payload }: { kind: Kind; item?: Item; payload: object }) => {
      const resource = kind === 'category' ? 'ticket-categories' : 'departments'
      return api(`/api/v1/${resource}${item ? `/${item.id}` : ''}`, { method: item ? 'PATCH' : 'POST', body: JSON.stringify(payload) })
    },
    onSuccess: () => {
      setDialog(null)
      client.invalidateQueries({ queryKey: ['ticket-categories'] })
      client.invalidateQueries({ queryKey: ['departments'] })
    },
  })

  if (categories.isLoading || departments.isLoading) return <PageLoading />

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    if (!dialog) return
    const payload = Object.fromEntries(new FormData(event.currentTarget))
    save.mutate({ kind: dialog.kind, item: dialog.item, payload: { ...payload, is_active: payload.is_active !== 'false' } })
  }

  const section = (title: string, description: string, icon: typeof Tags, items: Item[], kind: Kind) => {
    const Icon = icon
    return <section className="panel"><div className="panel-heading"><div><h2>{title}</h2><p>{description}</p></div><button className="button button-secondary" onClick={() => setDialog({ kind })}><Plus size={16}/>Add</button></div><div className="master-list">{items.map((item) => <article key={item.id}><div className="master-icon"><Icon/></div><div><strong>{item.name}</strong><p>{item.description || 'No description'}</p></div><div className="row-actions">{item.is_active !== undefined && <span className={item.is_active ? 'state-active' : 'state-inactive'}>{item.is_active ? 'Active' : 'Inactive'}</span>}<button onClick={() => setDialog({ kind, item })}><Pencil size={14}/>Edit</button></div></article>)}</div></section>
  }

  return <div className="page-workspace master-workspace">
    <WorkspaceHeader eyebrow="SYSTEM CONFIGURATION" title="Master data" description="Maintain the trusted classifications shared throughout OpsFlow." icon={Tags} signals={[`${(categories.data?.data.length ?? 0) + (departments.data?.data.length ?? 0)} configured records`, 'System-wide impact']}/>
    <div className="content-grid">{section('Ticket categories','Service request classifications.',Tags,categories.data?.data ?? [],'category')}{section('Departments','Company organization units.',Building2,departments.data?.data ?? [],'department')}</div>
    {dialog && <div className="modal-backdrop" onMouseDown={() => setDialog(null)}><form className="modal" onSubmit={submit} onMouseDown={(event) => event.stopPropagation()}><h2>{dialog.item ? 'Edit' : 'Add'} {dialog.kind}</h2><label>Name<input name="name" required minLength={2} defaultValue={dialog.item?.name}/></label><label>Description<textarea name="description" rows={4} defaultValue={dialog.item?.description ?? ''}/></label>{dialog.kind === 'category' && <label>Status<select name="is_active" defaultValue={dialog.item?.is_active === false ? 'false' : 'true'}><option value="true">Active</option><option value="false">Inactive</option></select></label>}<div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setDialog(null)}>Cancel</button><button className="button button-primary" disabled={save.isPending}>Save</button></div></form></div>}
  </div>
}
