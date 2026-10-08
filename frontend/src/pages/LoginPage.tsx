import { zodResolver } from '@hookform/resolvers/zod'
import { ArrowRight, CheckCircle2, Eye, EyeOff, LockKeyhole, ShieldCheck, Sparkles } from 'lucide-react'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Navigate, useLocation, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { useAuth } from '../auth/AuthProvider'
import { ApiError } from '../lib/api'

const schema = z.object({ email: z.email('Enter a valid email.'), password: z.string().min(1, 'Password is required.'), remember: z.boolean() })
type FormData = z.infer<typeof schema>

export function LoginPage() {
  const { user, login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [showPassword, setShowPassword] = useState(false)
  const [serverError, setServerError] = useState('')
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<FormData>({ resolver: zodResolver(schema), defaultValues: { email: '', password: '', remember: true } })
  if (user) return <Navigate to="/dashboard" replace />
  const onSubmit = async (values: FormData) => {
    setServerError('')
    try { await login(values.email, values.password, values.remember); navigate((location.state as { from?: { pathname: string } })?.from?.pathname ?? '/dashboard', { replace: true }) }
    catch (error) { setServerError(error instanceof ApiError ? Object.values(error.errors).flat()[0] ?? error.message : 'Unable to sign in.') }
  }
  return <div className="login-page"><section className="login-story"><div className="login-orb login-orb-one"/><div className="login-orb login-orb-two"/><div className="brand brand-light"><div className="brand-mark">OF</div><div><strong>OpsFlow</strong><small>IT Operations</small></div></div><div className="story-copy"><span className="eyebrow"><Sparkles size={13}/> ONE OPERATIONAL WORKSPACE</span><h1>Keep IT work moving.<br/>Keep every asset visible.</h1><p>Manage service requests, ownership, and operational history with one accountable workflow.</p><ul><li><CheckCircle2/>Traceable ticket decisions</li><li><CheckCircle2/>Reliable asset custody</li><li><ShieldCheck/>Role-based access by design</li></ul></div><small>Built as a production-oriented service management portfolio.</small></section><section className="login-panel"><form className="auth-card" onSubmit={handleSubmit(onSubmit)}><div className="auth-icon"><LockKeyhole/></div><span className="eyebrow">WELCOME BACK</span><h2>Sign in to OpsFlow</h2><p>Use your company account to continue.</p>{serverError && <div className="alert alert-error">{serverError}</div>}<label>Email address<input type="email" autoComplete="email" placeholder="name@company.com" {...register('email')}/>{errors.email && <small className="field-error">{errors.email.message}</small>}</label><label>Password<div className="password-field"><input type={showPassword ? 'text' : 'password'} autoComplete="current-password" placeholder="Enter your password" {...register('password')}/><button type="button" onClick={() => setShowPassword((value) => !value)} aria-label="Toggle password">{showPassword ? <EyeOff/> : <Eye/>}</button></div>{errors.password && <small className="field-error">{errors.password.message}</small>}</label><div className="form-row"><label className="checkbox"><input type="checkbox" {...register('remember')}/>Remember me</label><a href="/forgot-password">Forgot password?</a></div><button className="button button-primary button-full" disabled={isSubmitting}>{isSubmitting ? 'Signing in…' : <>Sign in <ArrowRight size={18}/></>}</button></form></section></div>
}
