import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ArrowUpRight, Boxes, Plus, Search } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'
import { StatusBadge } from '../components/StatusBadge'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { api } from '../lib/api'
import type { Asset, PaginatedResponse } from '../types/api'
import { PageLoading } from './DashboardPage'

export function AssetsPage() {
  const { can } = useAuth()
  const client = useQueryClient()
  const [search, setSearch] = useState('')
  const [showCreate, setShowCreate] = useState(false)
  const [selected, setSelected] = useState<Asset | null>(null)
  const assets = useQuery({ queryKey: ['assets', search], queryFn: () => api<PaginatedResponse<Asset>>(`/api/v1/assets${search ? `?search=${encodeURIComponent(search)}` : ''}`) })
  const users = useQuery({ queryKey: ['users', 'asset-assignment'], queryFn: () => api<PaginatedResponse<{ id: number; name: string; email: string }>>('/api/v1/users?status=ACTIVE&per_page=100'), enabled: can('asset.assign') })
  const create = useMutation({ mutationFn: (payload: object) => api('/api/v1/assets', { method: 'POST', body: JSON.stringify(payload) }), onSuccess: () => { setShowCreate(false); client.invalidateQueries({ queryKey: ['assets'] }) } })
  const assign = useMutation({ mutationFn: ({ assetId, userId }: { assetId: number; userId: number }) => api(`/api/v1/assets/${assetId}/assign`, { method: 'POST', body: JSON.stringify({ user_id: userId }) }), onSuccess: () => { setSelected(null); client.invalidateQueries({ queryKey: ['assets'] }) } })
  const returnAsset = useMutation({ mutationFn: (assetId: number) => api(`/api/v1/assets/${assetId}/return`, { method: 'POST', body: JSON.stringify({ notes: 'Returned through OpsFlow UI' }) }), onSuccess: () => client.invalidateQueries({ queryKey: ['assets'] }) })

  if (assets.isLoading) return <PageLoading />

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    create.mutate(Object.fromEntries(new FormData(event.currentTarget)))
  }

  return <div className="page-workspace assets-workspace">
    <WorkspaceHeader eyebrow="ASSET CUSTODY" title={can('asset.view_all') ? 'Assets' : 'My assets'} description="Track ownership, lifecycle status, and related service history." icon={Boxes} action={can('asset.create') ? <button className="button button-primary" onClick={() => setShowCreate(true)}><Plus/>Add asset</button> : undefined} signals={[`${assets.data?.data.length ?? 0} assets in view`, 'Custody tracked']}/>
    <section className="asset-catalog">
      <form className="search-field standalone-search" onSubmit={(event) => event.preventDefault()}><Search/><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search code, name, serial number…"/></form>
      <div className="asset-grid">
        {assets.data?.data.map((asset) => <article className="asset-card" key={asset.id}>
          <div className="asset-card-top"><div className="asset-symbol"><Boxes/></div><StatusBadge value={asset.status}/></div>
          <div className="asset-card-copy"><span>{asset.asset_code}</span><h2>{asset.name}</h2><p>{[asset.brand, asset.model].filter(Boolean).join(' · ') || 'Company equipment'}</p></div>
          <div className="asset-facts"><div><small>Category</small><strong>{asset.category}</strong></div><div><small>Serial number</small><strong>{asset.serial_number ?? 'Not recorded'}</strong></div></div>
          <div className="asset-owner"><div className="asset-owner-avatar">{asset.assigned_user?.name.slice(0, 2).toUpperCase() ?? '—'}</div><div><small>{asset.assigned_user ? 'Assigned to' : 'Custody'}</small><strong>{asset.assigned_user?.name ?? 'Available inventory'}</strong></div></div>
          <div className="asset-actions">
            <Link className="asset-detail-link" to={`/assets/${asset.id}`}>View asset <ArrowUpRight size={16}/></Link>
            {can('asset.assign') && asset.status === 'AVAILABLE' && <button className="button button-secondary" onClick={() => setSelected(asset)}>Assign</button>}
            {can('asset.assign') && asset.status === 'ASSIGNED' && <button className="button button-secondary" onClick={() => window.confirm(`Return ${asset.asset_code} to inventory?`) && returnAsset.mutate(asset.id)}>Return asset</button>}
          </div>
        </article>)}
        {!assets.data?.data.length && <div className="empty-state"><Boxes/><h2>No assets found</h2></div>}
      </div>
    </section>
    {showCreate && <div className="modal-backdrop" onMouseDown={() => setShowCreate(false)}><form className="modal" onSubmit={submit} onMouseDown={(event) => event.stopPropagation()}><h2>Add an asset</h2><p>Create an inventory record. The asset code is generated automatically.</p><label>Name<input name="name" required/></label><div className="form-grid"><label>Category<input name="category" required placeholder="Laptop"/></label><label>Brand<input name="brand"/></label><label>Model<input name="model"/></label><label>Serial number<input name="serial_number"/></label></div><label>Notes<textarea name="notes" rows={3}/></label><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setShowCreate(false)}>Cancel</button><button className="button button-primary" disabled={create.isPending}>Create asset</button></div></form></div>}
    {selected && <div className="modal-backdrop" onMouseDown={() => setSelected(null)}><form className="modal" onSubmit={(event) => { event.preventDefault(); assign.mutate({ assetId: selected.id, userId: Number(new FormData(event.currentTarget).get('user_id')) }) }} onMouseDown={(event) => event.stopPropagation()}><h2>Assign {selected.asset_code}</h2><label>Employee<select name="user_id" required defaultValue=""><option value="" disabled>Select employee</option>{users.data?.data.map((user) => <option value={user.id} key={user.id}>{user.name} — {user.email}</option>)}</select></label><div className="form-actions"><button type="button" className="button button-secondary" onClick={() => setSelected(null)}>Cancel</button><button className="button button-primary">Assign asset</button></div></form></div>}
  </div>
}
