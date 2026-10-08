import { useState, type FormEvent } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowLeft, KeyRound, ShieldCheck } from 'lucide-react'
import { api } from '../lib/api'

export function ForgotPasswordPage() {
  const [message, setMessage] = useState('')
  const submit = async (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); const email = new FormData(event.currentTarget).get('email'); const response = await api<{ message: string }>('/api/v1/auth/forgot-password', { method: 'POST', body: JSON.stringify({ email }) }); setMessage(response.message) }
  return <PublicCard title="Reset your password" subtitle="We will send reset instructions if the address belongs to an account.">{message ? <div className="alert alert-success">{message}</div> : <form onSubmit={submit}><label>Email address<input name="email" type="email" required placeholder="you@company.com"/></label><button className="button button-primary button-full">Send reset link</button></form>}<Link className="back-link centered" to="/login"><ArrowLeft size={15}/>Back to sign in</Link></PublicCard>
}

export function ResetPasswordPage() {
  const [params] = useSearchParams()
  const [message, setMessage] = useState('')
  const submit = async (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); const form = new FormData(event.currentTarget); const response = await api<{ message: string }>('/api/v1/auth/reset-password', { method: 'POST', body: JSON.stringify({ token: params.get('token'), email: form.get('email'), password: form.get('password'), password_confirmation: form.get('password_confirmation') }) }); setMessage(response.message) }
  return <PublicCard title="Choose a new password" subtitle="Use at least eight characters.">{message ? <div className="alert alert-success">{message} <Link to="/login">Sign in</Link></div> : <form onSubmit={submit}><label>Email<input name="email" type="email" defaultValue={params.get('email') ?? ''} required/></label><label>New password<input name="password" type="password" minLength={8} required/></label><label>Confirm password<input name="password_confirmation" type="password" minLength={8} required/></label><button className="button button-primary button-full">Reset password</button></form>}</PublicCard>
}

function PublicCard({ title, subtitle, children }: { title: string; subtitle: string; children: React.ReactNode }) { return <div className="public-page password-page"><div className="auth-ambient" aria-hidden="true"/><div className="brand"><div className="brand-mark">OF</div><div><strong>OpsFlow</strong><small>IT Operations</small></div></div><section className="auth-card password-card"><div className="auth-icon"><KeyRound/></div><span className="workspace-kicker">SECURE ACCOUNT RECOVERY</span><h1>{title}</h1><p>{subtitle}</p>{children}<div className="auth-trust"><ShieldCheck/><span><strong>Protected workflow</strong><small>Your request is handled securely.</small></span></div></section></div> }
