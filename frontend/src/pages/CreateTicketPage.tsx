import { useMutation, useQuery } from '@tanstack/react-query'
import { ArrowLeft, CheckCircle2, FileText, Send, ShieldCheck } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { WorkspaceHeader } from '../components/WorkspaceHeader'
import { ApiError, api } from '../lib/api'
import type { ApiResponse, Asset, Ticket } from '../types/api'

type Category = { id: number; name: string }

export function CreateTicketPage() {
  const navigate = useNavigate()
  const [error, setError] = useState('')
  const categories = useQuery({ queryKey: ['ticket-categories'], queryFn: () => api<ApiResponse<Category[]>>('/api/v1/ticket-categories') })
  const assets = useQuery({ queryKey: ['assets', 'mine'], queryFn: () => api<{ data: Asset[] }>('/api/v1/assets') })
  const mutation = useMutation({
    mutationFn: (payload: object) => api<ApiResponse<Ticket>>('/api/v1/tickets', { method: 'POST', body: JSON.stringify(payload) }),
    onSuccess: (response) => navigate(`/tickets/${response.data.id}`),
    onError: (value) => setError(value instanceof ApiError ? Object.values(value.errors).flat()[0] ?? value.message : 'Unable to create ticket.'),
  })

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    const form = new FormData(event.currentTarget)
    mutation.mutate({ title: form.get('title'), description: form.get('description'), category_id: Number(form.get('category_id')), priority: form.get('priority'), asset_id: form.get('asset_id') ? Number(form.get('asset_id')) : null })
  }

  return <div className="page-workspace create-ticket-workspace">
    <WorkspaceHeader eyebrow="NEW SERVICE REQUEST" title="Create a ticket" description="Share the right context once so the IT team can act quickly." icon={FileText} backLink={<Link className="back-link" to="/tickets"><ArrowLeft size={16}/>Back to tickets</Link>} signals={['Secure submission', 'Tracked end-to-end']}/>
    <div className="guided-form-layout">
      <form className="panel form-panel guided-form" onSubmit={submit}>
        {error && <div className="alert alert-error">{error}</div>}
        <div className="form-section-heading"><span>01</span><div><h2>Request details</h2><p>Give your service request a clear identity.</p></div></div>
        <div className="form-grid">
          <label className="field-span-2">Title<input name="title" required minLength={5} maxLength={150} placeholder="Briefly describe the issue"/></label>
          <label>Category<select name="category_id" required defaultValue=""><option value="" disabled>Select category</option>{categories.data?.data.map((category) => <option value={category.id} key={category.id}>{category.name}</option>)}</select></label>
          <label>Suggested priority<select name="priority" defaultValue="MEDIUM"><option>LOW</option><option>MEDIUM</option><option>HIGH</option><option>CRITICAL</option></select></label>
          <label className="field-span-2">Related asset <span className="optional">Optional</span><select name="asset_id" defaultValue=""><option value="">No related asset</option>{assets.data?.data.map((asset) => <option value={asset.id} key={asset.id}>{asset.asset_code} — {asset.name}</option>)}</select></label>
          <label className="field-span-2">Description<textarea name="description" required minLength={10} rows={8} placeholder="What happened, what did you expect, and what have you tried?"/></label>
        </div>
        <div className="form-actions"><Link className="button button-secondary" to="/tickets">Cancel</Link><button className="button button-primary" disabled={mutation.isPending}><Send size={17}/>{mutation.isPending ? 'Creating…' : 'Create ticket'}</button></div>
      </form>
      <aside className="form-assist"><span className="form-assist-kicker">A BETTER REQUEST</span><h2>Help us resolve it faster.</h2><p>A precise ticket reduces follow-up questions and gives the technician a clean starting point.</p><ul><li><CheckCircle2/><span><strong>Describe the impact</strong><small>Who or what is blocked?</small></span></li><li><CheckCircle2/><span><strong>Include what changed</strong><small>When did the issue begin?</small></span></li><li><ShieldCheck/><span><strong>Do not share passwords</strong><small>Sensitive credentials are never required.</small></span></li></ul></aside>
    </div>
  </div>
}
