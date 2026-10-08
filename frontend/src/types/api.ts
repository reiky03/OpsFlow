export type User = {
  id: number
  name: string
  email: string
  employee_number?: string | null
  job_title?: string | null
  status: 'ACTIVE' | 'INACTIVE'
  department?: { id: number; name: string } | null
  roles: string[]
  permissions: string[]
}

export type Ticket = {
  id: number
  ticket_number: string
  title: string
  description: string
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL'
  status: 'OPEN' | 'ASSIGNED' | 'IN_PROGRESS' | 'WAITING_USER' | 'RESOLVED' | 'CLOSED' | 'CANCELLED'
  category: { id: number; name: string }
  reporter: { id: number; name: string; email: string }
  assigned_technician: { id: number; name: string; email: string } | null
  asset?: Asset | null
  comments?: TicketComment[]
  activities?: TicketActivity[]
  attachments?: Attachment[]
  created_at: string
  updated_at: string
}

export type TicketComment = { id: number; message: string; is_internal: boolean; user: { id: number; name: string }; created_at: string }
export type TicketActivity = {
  id: number
  type: 'CREATED' | 'ASSIGNED' | 'STATUS_CHANGED' | 'PRIORITY_CHANGED' | 'COMMENT_ADDED' | 'ATTACHMENT_ADDED' | 'UPDATED'
  description: string
  actor?: { id: number; name: string } | null
  old_values?: Record<string, string | number | null> | null
  new_values?: Record<string, string | number | null> | null
  metadata?: Record<string, string | number | null> | null
  created_at: string
}
export type Attachment = { id: number; original_name: string; mime_type: string; size: number; download_url: string }
export type Asset = {
  id: number
  asset_code: string
  name: string
  category: string
  brand?: string | null
  model?: string | null
  serial_number?: string | null
  status: 'AVAILABLE' | 'ASSIGNED' | 'MAINTENANCE' | 'RETIRED' | 'LOST'
  assigned_user?: { id: number; name: string; email: string } | null
  purchase_date?: string | null
  warranty_end_date?: string | null
  notes?: string | null
}

export type ApiResponse<T> = { success: boolean; message?: string; data: T }
export type PaginatedResponse<T> = { success: boolean; data: T[]; meta: { current_page: number; per_page: number; total: number; last_page: number; unread_count?: number } }
