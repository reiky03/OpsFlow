import { CheckCircle2, type LucideIcon } from 'lucide-react'
import type { ReactNode } from 'react'

type WorkspaceHeaderProps = {
  eyebrow: string
  title: string
  description: string
  icon: LucideIcon
  action?: ReactNode
  signals?: string[]
  backLink?: ReactNode
}

export function WorkspaceHeader({ eyebrow, title, description, icon: Icon, action, signals = [], backLink }: WorkspaceHeaderProps) {
  return <header className="workspace-header">
    <div className="workspace-header-orbit" aria-hidden="true" />
    <div className="workspace-header-main">
      <div className="workspace-header-icon"><Icon /></div>
      <div className="workspace-header-copy">
        {backLink}
        <span className="workspace-kicker">{eyebrow}</span>
        <h1>{title}</h1>
        <p>{description}</p>
      </div>
    </div>
    {(action || signals.length > 0) && <div className="workspace-header-side">
      {action}
      {signals.length > 0 && <div className="workspace-signals" aria-label="Workspace capabilities">
        {signals.map((signal) => <span key={signal}><CheckCircle2 />{signal}</span>)}
      </div>}
    </div>}
  </header>
}
