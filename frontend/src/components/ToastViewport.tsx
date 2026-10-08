import { CheckCircle2, CircleAlert, X } from 'lucide-react'
import { useEffect, useState } from 'react'

export type ToastDetail = { message: string; tone: 'success' | 'error' }

type Toast = ToastDetail & { id: number }

export function ToastViewport() {
  const [toasts, setToasts] = useState<Toast[]>([])

  useEffect(() => {
    const listener = (event: Event) => {
      const detail = (event as CustomEvent<ToastDetail>).detail
      const id = Date.now()
      setToasts((current) => [...current, { ...detail, id }])
      window.setTimeout(() => setToasts((current) => current.filter((toast) => toast.id !== id)), 4500)
    }
    window.addEventListener('opsflow:toast', listener)

    return () => window.removeEventListener('opsflow:toast', listener)
  }, [])

  return <div className="toast-viewport" aria-live="polite">{toasts.map((toast) => <div className={`toast toast-${toast.tone}`} key={toast.id}>{toast.tone === 'success' ? <CheckCircle2/> : <CircleAlert/>}<span>{toast.message}</span><button onClick={() => setToasts((current) => current.filter((item) => item.id !== toast.id))} aria-label="Dismiss notification"><X/></button></div>)}</div>
}
