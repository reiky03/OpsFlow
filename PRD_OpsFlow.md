# OpsFlow
## Product Requirements Document (PRD)

**Version:** 1.0
**Product Type:** Internal Enterprise Web Application
**Primary Domain:** IT Service Management, Asset Management, Internal Request Management
**Target Users:** Employees, IT Technicians, IT Managers, Administrators
**Initial Release:** MVP / Portfolio Production-Grade Project

---

# 1. Product Overview

OpsFlow adalah aplikasi internal perusahaan yang digunakan untuk mengelola:

1. Laporan masalah IT.
2. Permintaan layanan IT.
3. Penugasan teknisi.
4. Inventaris aset perusahaan.
5. Riwayat penggunaan dan perbaikan aset.
6. Komunikasi antara employee dan IT technician.
7. Monitoring status pekerjaan IT.
8. Role dan permission pengguna.
9. Audit aktivitas sistem.

Contoh masalah yang dapat dilaporkan:

- Laptop tidak dapat menyala.
- VPN tidak dapat digunakan.
- Internet kantor bermasalah.
- Tidak dapat login ke aplikasi perusahaan.
- Printer bermasalah.
- Software tertentu perlu diinstal.
- Akses repository atau sistem diperlukan.

OpsFlow bertujuan menggantikan proses manual seperti WhatsApp, spreadsheet, atau komunikasi informal dengan workflow yang terstruktur dan dapat dilacak.

---

# 2. Product Vision

Membuat platform internal perusahaan yang memungkinkan employee dan tim IT mengelola masalah, aset, serta layanan IT secara terpusat, transparan, dan terdokumentasi.

Produk harus terasa seperti aplikasi internal perusahaan sungguhan, bukan sekadar CRUD portfolio.

---

# 3. Product Goals

OpsFlow harus memungkinkan perusahaan untuk:

- Mengelola ticket IT secara terstruktur.
- Mengetahui siapa yang menangani setiap masalah.
- Mengetahui status setiap ticket.
- Mengetahui riwayat masalah suatu device.
- Mengelola device yang diberikan kepada setiap employee.
- Mengetahui aktivitas yang dilakukan terhadap ticket.
- Mencegah kehilangan laporan karena komunikasi informal.
- Menyediakan dashboard operasional bagi tim IT.
- Menyediakan audit trail terhadap perubahan penting.

---

# 4. Non-Goals

Untuk MVP, OpsFlow belum perlu memiliki:

- Integrasi pembayaran.
- Integrasi ERP eksternal.
- Integrasi Microsoft Active Directory.
- Native mobile application.
- Real-time video/audio support.
- Full procurement system.
- Machine learning.
- Complex AI functionality.
- Multi-company SaaS architecture.
- Advanced SLA automation.

Fitur-fitur tersebut dapat dikembangkan pada versi berikutnya.

---

# 5. User Roles

## 5.1 Employee

Employee adalah pengguna biasa yang menggunakan fasilitas IT perusahaan.

Employee dapat:

- Login.
- Melihat dashboard pribadi.
- Membuat ticket.
- Melihat ticket miliknya.
- Memberikan komentar pada ticket miliknya.
- Upload attachment.
- Melihat asset yang sedang diberikan kepadanya.
- Melihat status ticket.
- Menutup atau mengkonfirmasi resolved ticket apabila diperlukan.
- Melihat notification.

Employee tidak dapat:

- Melihat semua ticket perusahaan.
- Assign technician.
- Mengubah role user.
- Mengelola asset global.
- Mengelola user.
- Menghapus audit log.

---

## 5.2 IT Technician

IT Technician menangani masalah teknis employee.

Technician dapat:

- Melihat ticket yang ditugaskan kepadanya.
- Melihat unassigned ticket jika diberikan permission.
- Mengubah status ticket.
- Memberikan komentar.
- Menambahkan internal notes.
- Melihat informasi asset yang terkait.
- Menandai ticket sebagai resolved.
- Melihat riwayat pekerjaan miliknya.

Technician tidak dapat:

- Mengelola role administrator.
- Menghapus audit history.
- Mengubah konfigurasi sistem global.

---

## 5.3 IT Manager

IT Manager mengatur operasional IT.

IT Manager dapat:

- Melihat seluruh ticket.
- Assign technician.
- Reassign technician.
- Mengubah priority.
- Melihat dashboard tim.
- Melihat performa ticket.
- Melihat asset perusahaan.
- Mengelola kategori ticket.
- Melihat audit history.
- Mengelola data operasional IT.

---

## 5.4 Administrator

Administrator memiliki hak akses tertinggi.

Administrator dapat:

- Mengelola user.
- Mengelola role.
- Mengelola permission.
- Mengelola department.
- Mengelola asset.
- Mengelola ticket category.
- Melihat semua data.
- Melihat audit logs.
- Mengelola konfigurasi sistem.

Administrator tidak boleh menghapus audit log secara langsung melalui UI.

---

# 6. Core Modules

OpsFlow MVP terdiri dari:

1. Authentication.
2. Dashboard.
3. Ticket Management.
4. Ticket Comments.
5. Asset Management.
6. User Management.
7. Role-Based Access Control.
8. Notifications.
9. Audit Logging.
10. Search, Filter, Pagination.

---

# 7. Authentication

## 7.1 Login

User login menggunakan:

- Email.
- Password.

Fields:

```text
Email
Password
Remember Me
```

Validation:

- Email wajib diisi.
- Password wajib diisi.
- Email harus valid.
- Credential salah harus menghasilkan error generik.

Contoh error:

```text
Email or password is incorrect.
```

Jangan memberi tahu apakah email atau password yang salah.

---

## 7.2 Logout

User dapat logout.

Logout harus:

- Menghapus session/token aktif.
- Redirect user ke login page.

---

## 7.3 Forgot Password

User dapat meminta reset password.

Flow:

```text
User
↓
Forgot Password
↓
Enter Email
↓
Receive Reset Link
↓
Create New Password
↓
Login
```

---

# 8. Dashboard

Dashboard harus berbeda berdasarkan role.

## 8.1 Employee Dashboard

Employee melihat:

- Total open tickets.
- Total in-progress tickets.
- Total resolved tickets.
- Ticket terbaru.
- Asset yang diberikan kepadanya.
- Notification terbaru.

Example:

```text
Open Tickets: 3
In Progress: 1
Resolved: 8
Assets Assigned: 2
```

## 8.2 Technician Dashboard

Technician melihat:

- Assigned tickets.
- High priority tickets.
- Tickets in progress.
- Resolved today.
- Recent activities.

Example:

```text
Assigned: 12
In Progress: 5
High Priority: 2
Resolved Today: 4
```

## 8.3 IT Manager Dashboard

Manager melihat:

- Total ticket.
- Ticket berdasarkan status.
- Ticket berdasarkan priority.
- Ticket berdasarkan technician.
- Ticket berdasarkan kategori.
- Unassigned ticket.
- Recent activities.

Untuk MVP, chart sederhana diperbolehkan.

---

# 9. Ticket Management

Ticket merupakan fitur utama OpsFlow.

## 9.1 Ticket Fields

Setiap ticket memiliki:

```text
id
ticket_number
title
description
category_id
priority
status
reporter_id
assigned_technician_id
asset_id
created_at
updated_at
resolved_at
closed_at
```

## 9.2 Ticket Number

Ticket harus memiliki unique human-readable identifier.

Example:

```text
TCK-2026-000001
TCK-2026-000002
TCK-2026-000003
```

Ticket number harus dibuat otomatis oleh backend.

User tidak boleh menentukan ticket number sendiri.

## 9.3 Ticket Status

Valid ticket statuses:

```text
OPEN
ASSIGNED
IN_PROGRESS
WAITING_USER
RESOLVED
CLOSED
CANCELLED
```

Meaning:

### OPEN
Ticket baru dibuat dan belum ditugaskan.

### ASSIGNED
Ticket sudah memiliki technician.

### IN_PROGRESS
Technician sedang mengerjakan masalah.

### WAITING_USER
Technician membutuhkan response dari reporter.

### RESOLVED
Technician menyatakan masalah telah diselesaikan.

### CLOSED
Ticket selesai secara final.

### CANCELLED
Ticket dibatalkan.

## 9.4 Allowed Ticket Transitions

Default workflow:

```text
OPEN
↓
ASSIGNED
↓
IN_PROGRESS
↓
RESOLVED
↓
CLOSED
```

Additional flows:

```text
IN_PROGRESS
↓
WAITING_USER
↓
IN_PROGRESS
```

Cancellation:

```text
OPEN → CANCELLED
ASSIGNED → CANCELLED
```

Ticket yang CLOSED tidak boleh kembali ke IN_PROGRESS pada MVP.

Jika diperlukan, fitur reopen dapat ditambahkan di versi berikutnya.

## 9.5 Ticket Priority

Valid priorities:

```text
LOW
MEDIUM
HIGH
CRITICAL
```

Default priority:

```text
MEDIUM
```

Employee boleh memberikan suggested priority.

IT Manager atau Technician yang memiliki permission boleh mengubah final priority.

## 9.6 Ticket Category

Example categories:

```text
Hardware
Software
Network
Account Access
Email
VPN
Printer
Application
Other
```

Category harus disimpan sebagai database entity agar administrator dapat menambah atau mengubah kategori.

---

# 10. Create Ticket

Employee dapat membuat ticket.

Required fields:

```text
Title
Category
Description
```

Optional fields:

```text
Priority
Related Asset
Attachment
```

Validation:

### Title

- Required.
- Minimum 5 characters.
- Maximum 150 characters.

### Description

- Required.
- Minimum 10 characters.

### Category

- Required.
- Category harus valid.

### Asset

- Optional.
- Employee hanya boleh memilih asset yang assigned kepadanya.

---

# 11. Ticket Detail Page

Ticket detail harus menampilkan:

- Ticket number.
- Title.
- Description.
- Reporter.
- Category.
- Priority.
- Status.
- Assigned technician.
- Related asset.
- Created timestamp.
- Updated timestamp.
- Comment history.
- Activity timeline.

---

# 12. Ticket Assignment

IT Manager atau user dengan permission dapat assign ticket.

Flow:

```text
OPEN
↓
Assign Technician
↓
ASSIGNED
```

Assignment harus mencatat:

```text
assigned_technician_id
assigned_at
assigned_by
```

Audit event:

```text
Ticket TCK-2026-000221 assigned to Andi by Manager Budi.
```

---

# 13. Ticket Comments

Employee dan IT staff dapat berkomunikasi di ticket.

Comment fields:

```text
id
ticket_id
user_id
message
is_internal
created_at
updated_at
```

Employee hanya dapat membuat public comment.

Technician dapat membuat:

```text
Public Comment
Internal Note
```

Internal note hanya dapat dilihat oleh IT team.

Employee tidak boleh melihat internal note.

---

# 14. Ticket Attachments

User dapat upload attachment.

Supported formats:

```text
jpg
jpeg
png
pdf
txt
log
```

Maximum file size MVP:

```text
10 MB
```

Attachment harus berhubungan dengan:

```text
ticket
atau
ticket_comment
```

File yang di-upload tidak boleh menggunakan original filename sebagai storage filename.

Gunakan random UUID/hash filename.

---

# 15. Asset Management

Asset adalah device atau barang IT milik perusahaan.

Example:

```text
Laptop
Desktop PC
Monitor
Keyboard
Mouse
Mobile Phone
Printer
Server
Network Device
```

## 15.1 Asset Fields

```text
id
asset_code
name
category
brand
model
serial_number
status
assigned_user_id
purchase_date
warranty_end_date
notes
created_at
updated_at
```

## 15.2 Asset Code

Asset memiliki unique human-readable code.

Example:

```text
AST-LAP-00001
AST-MON-00003
AST-PHN-00002
```

## 15.3 Asset Status

Valid statuses:

```text
AVAILABLE
ASSIGNED
MAINTENANCE
RETIRED
LOST
```

## 15.4 Asset Assignment

Administrator atau IT Manager dapat assign asset ke employee.

Sistem harus menyimpan asset assignment history.

---

# 16. Asset Assignment History

Fields:

```text
id
asset_id
user_id
assigned_at
returned_at
assigned_by
notes
```

Dengan ini sistem dapat mengetahui siapa saja yang pernah menggunakan asset.

---

# 17. Asset Detail Page

Asset detail harus menampilkan:

- Asset code.
- Name.
- Category.
- Brand.
- Model.
- Serial number.
- Status.
- Current assigned user.
- Purchase date.
- Warranty information.
- Assignment history.
- Related ticket history.

---

# 18. User Management

Administrator dapat:

- Create user.
- Update user.
- Deactivate user.
- Assign role.
- Assign department.

User fields:

```text
id
name
email
password
employee_number
department_id
job_title
status
last_login_at
created_at
updated_at
```

Valid user statuses:

```text
ACTIVE
INACTIVE
```

User sebaiknya tidak dihapus secara permanent jika sudah memiliki activity history.

Gunakan soft delete atau inactive status.

---

# 19. Department

System mendukung department.

Example:

```text
IT
Finance
HR
Engineering
Sales
Marketing
Operations
```

Fields:

```text
id
name
description
created_at
updated_at
```

---

# 20. Role-Based Access Control

Gunakan RBAC.

Default roles:

```text
EMPLOYEE
TECHNICIAN
IT_MANAGER
ADMIN
```

Permission sebaiknya bersifat granular.

Example:

```text
ticket.create
ticket.view_own
ticket.view_all
ticket.assign
ticket.update_status
ticket.update_priority
ticket.comment
ticket.internal_note

asset.view_own
asset.view_all
asset.create
asset.update
asset.assign

user.view
user.create
user.update
user.assign_role

audit.view
```

Jangan melakukan pengecekan role secara hardcoded di banyak controller.

Gunakan policy/gate/permission layer yang terpusat.

---

# 21. Notifications

User harus menerima notification untuk event tertentu.

Initial MVP notifications:

### Employee

- Ticket berhasil dibuat.
- Technician assigned.
- Ticket status berubah.
- Technician memberikan public comment.
- Ticket resolved.

### Technician

- Ticket assigned.
- Employee memberikan comment.
- Priority berubah.

Notification MVP boleh berupa:

```text
In-App Notification
```

Email notification dapat ditambahkan apabila implementasi stabil.

---

# 22. Notification Fields

```text
id
user_id
type
title
message
data
read_at
created_at
```

Notification dapat ditandai sebagai read.

---

# 23. Audit Log

Perubahan penting harus dicatat.

Audit events include:

```text
User Login
Ticket Created
Ticket Assigned
Ticket Priority Changed
Ticket Status Changed
Asset Created
Asset Assigned
Asset Returned
User Role Changed
User Deactivated
```

Fields:

```text
id
user_id
action
entity_type
entity_id
old_values
new_values
ip_address
user_agent
created_at
```

Audit log tidak boleh editable melalui UI.

Audit log tidak boleh deletable oleh normal administrator melalui UI.

---

# 24. Activity Timeline

Ticket harus menampilkan activity timeline.

Example:

```text
09:02
Ticket created by Reiky

09:07
Assigned to Andi by Manager Budi

09:15
Status changed:
ASSIGNED → IN_PROGRESS

09:40
Comment added by Andi

11:21
Status changed:
IN_PROGRESS → RESOLVED
```

---

# 25. Search

User dapat search ticket berdasarkan:

```text
Ticket Number
Title
Reporter
Technician
```

Asset dapat dicari berdasarkan:

```text
Asset Code
Name
Serial Number
Assigned User
```

---

# 26. Filter

Ticket dapat difilter berdasarkan:

```text
Status
Priority
Category
Technician
Reporter
Date Range
```

---

# 27. Pagination

Semua halaman list yang berpotensi besar harus menggunakan pagination.

Default:

```text
20 records per page
```

Available:

```text
20
50
100
```

---

# 28. Sorting

Ticket dapat di-sort berdasarkan:

```text
Created Date
Updated Date
Priority
Status
```

Default:

```text
Newest first
```

---

# 29. Error Handling

API harus menggunakan consistent error response.

Example validation error:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

Not found:

```json
{
  "success": false,
  "message": "Ticket not found."
}
```

Unauthorized:

```json
{
  "success": false,
  "message": "You are not authorized to perform this action."
}
```

Never return:

- Stack trace.
- Database credentials.
- Internal server path.
- SQL query details.

di production.

---

# 30. API Response Standard

Success single entity:

```json
{
  "success": true,
  "message": "Ticket retrieved successfully.",
  "data": {}
}
```

Success collection:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 120
  }
}
```

---

# 31. Proposed REST API

Authentication:

```text
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
POST /api/auth/forgot-password
POST /api/auth/reset-password
```

Tickets:

```text
GET    /api/tickets
POST   /api/tickets
GET    /api/tickets/{id}
PATCH  /api/tickets/{id}
POST   /api/tickets/{id}/assign
POST   /api/tickets/{id}/status
POST   /api/tickets/{id}/comments
GET    /api/tickets/{id}/activities
```

Assets:

```text
GET    /api/assets
POST   /api/assets
GET    /api/assets/{id}
PATCH  /api/assets/{id}
POST   /api/assets/{id}/assign
POST   /api/assets/{id}/return
GET    /api/assets/{id}/history
```

Users:

```text
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PATCH  /api/users/{id}
POST   /api/users/{id}/role
POST   /api/users/{id}/deactivate
```

Notifications:

```text
GET  /api/notifications
POST /api/notifications/{id}/read
POST /api/notifications/read-all
```

Audit:

```text
GET /api/audit-logs
```

---

# 32. Frontend Pages

Public:

```text
/login
/forgot-password
/reset-password
```

Authenticated common pages:

```text
/dashboard
/profile
/notifications
```

Employee:

```text
/tickets
/tickets/create
/tickets/{id}

/my-assets
/my-assets/{id}
```

Technician:

```text
/technician/tickets
/technician/tickets/{id}
```

Manager:

```text
/management/tickets
/management/assets
/management/dashboard
```

Admin:

```text
/admin/users
/admin/users/{id}

/admin/assets
/admin/assets/{id}

/admin/categories
/admin/departments
/admin/audit-logs
```

---

# 33. Main Navigation

Employee navigation:

```text
Dashboard
My Tickets
Create Ticket
My Assets
Notifications
Profile
```

IT Technician navigation:

```text
Dashboard
Assigned Tickets
All Available Tickets
Assets
Notifications
Profile
```

Manager navigation:

```text
Dashboard
Tickets
Assets
Technicians
Reports
Audit Activity
```

Admin navigation:

```text
Dashboard
Users
Roles
Departments
Tickets
Assets
Categories
Audit Logs
System Settings
```

---

# 34. Database Relationships

Main relationships:

```text
User
├── belongsTo Department
├── hasMany Tickets
├── hasMany TicketComments
├── hasMany Notifications
└── hasMany AssetAssignments

Ticket
├── belongsTo Reporter(User)
├── belongsTo Technician(User)
├── belongsTo Category
├── belongsTo Asset
├── hasMany Comments
├── hasMany Attachments
└── hasMany Activities

Asset
├── belongsTo CurrentUser
├── hasMany Tickets
└── hasMany AssignmentHistory

TicketComment
├── belongsTo Ticket
└── belongsTo User
```

---

# 35. Suggested Database Tables

```text
users
departments
roles
permissions
role_permissions
user_roles

ticket_categories
tickets
ticket_comments
ticket_attachments
ticket_activities

assets
asset_assignments

notifications
audit_logs

password_reset_tokens
sessions
```

Additional framework-generated tables allowed.

---

# 36. Backend Architecture

Backend menggunakan layered architecture yang sederhana dan maintainable.

Suggested flow:

```text
Request
↓
Route
↓
Controller
↓
Form Request Validation
↓
Application Service
↓
Domain Logic / Model
↓
Repository only when needed
↓
Database
↓
Resource / DTO
↓
Response
```

Controller tidak boleh mengandung business logic yang kompleks.

Better:

```text
TicketController
↓
TicketService
├── Ticket workflow
├── Assignment
├── Audit event
└── Notification
```

---

# 37. Engineering Principles

Implementation harus mengikuti:

- SOLID principles.
- Separation of concerns.
- Dependency inversion when useful.
- DRY without premature abstraction.
- Explicit business rules.
- Service classes for multi-step operations.
- Policies for authorization.
- Form Request classes for validation.
- Database transactions for atomic business operations.
- Resource classes for API serialization.
- Events/listeners for side effects when appropriate.
- Consistent exception handling.

Avoid unnecessary design patterns jika tidak memberikan manfaat nyata.

---

# 38. Transaction Requirements

Operasi multi-step harus atomic.

Example asset assignment:

```text
BEGIN TRANSACTION

Update asset status
Create assignment history
Set assigned user
Create audit log

COMMIT
```

Jika salah satu gagal:

```text
ROLLBACK
```

---

# 39. Security Requirements

Minimum security requirements:

- Password harus di-hash.
- Authorization dilakukan di backend.
- Jangan mempercayai role dari frontend.
- CSRF protection sesuai authentication architecture.
- Rate limiting untuk authentication.
- Validation setiap request.
- File upload validation.
- Secure filename generation.
- Sensitive fields tidak boleh diekspos.
- Mass assignment harus dikontrol.
- SQL injection harus dicegah menggunakan ORM/query binding.
- XSS harus dicegah.
- User hanya boleh mengakses data sesuai permission.

---

# 40. Technical Stack

## Frontend

Preferred:

```text
React
TypeScript
Vite
Tailwind CSS
React Router
TanStack Query
React Hook Form
Zod
```

Alternative:

```text
Next.js
```

Jika tidak ada kebutuhan SSR, React + Vite cukup.

## Backend

```text
PHP 8.3+
Laravel
Laravel Sanctum
Pest or PHPUnit
```

## Database

```text
PostgreSQL
```

## Cache / Queue

Future-ready:

```text
Redis
```

Untuk MVP awal, queue dapat ditambahkan setelah core ticket system stabil.

## Web Server

```text
Nginx
```

## Containerization

```text
Docker
Docker Compose
```

## CI/CD

```text
GitHub Actions
```

---

# 41. Docker Architecture

Target development environment:

```text
opsflow-frontend
opsflow-backend
opsflow-nginx
opsflow-postgres
opsflow-redis
opsflow-worker
```

Developer harus dapat menjalankan project menggunakan:

```bash
docker compose up -d
```

Tanpa perlu menginstal PHP, PostgreSQL, Redis, dan Nginx langsung di host machine.

Host hanya membutuhkan:

```text
Docker
Git
```

---

# 42. Environment Configuration

Gunakan:

```text
.env.example
```

Repository tidak boleh mengandung real secret.

Example:

```text
APP_ENV=
APP_KEY=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
REDIS_HOST=
```

`.env` wajib masuk `.gitignore`.

---

# 43. Testing Requirements

Minimum backend testing:

### Authentication Tests

- User dapat login dengan credential valid.
- Credential invalid ditolak.
- Unauthenticated user tidak dapat membuka protected endpoint.

### Ticket Tests

- Employee dapat create ticket.
- Required validation berjalan.
- Employee hanya dapat melihat ticket miliknya.
- Technician dapat update assigned ticket.
- Unauthorized user tidak dapat assign ticket.
- Status transition yang invalid harus ditolak.

### Asset Tests

- Admin dapat create asset.
- Employee hanya dapat melihat assigned asset.
- Asset dapat assigned ke user.
- Asset assignment history tercatat.

### Authorization Tests

- Employee tidak dapat mengakses admin endpoint.
- Technician tidak dapat mengubah role user.
- Manager dapat assign ticket.

Target awal:

```text
Core business logic memiliki automated tests.
```

Tidak perlu mengejar 100% coverage.

---

# 44. Frontend UX Requirements

UI harus:

- Responsive.
- Desktop-first.
- Clean.
- Professional.
- Enterprise-style.
- Tidak terlalu banyak animation.
- Consistent spacing.
- Consistent typography.
- Accessible.

Gunakan:

- Skeleton loading.
- Empty state.
- Error state.
- Loading state.
- Confirmation dialog untuk destructive actions.
- Toast notification untuk action feedback.

---

# 45. Example Empty States

Tickets:

```text
No tickets found.

You have not created any support tickets yet.

[Create Ticket]
```

Assets:

```text
No assets assigned.

You currently do not have any company IT assets assigned.
```

---

# 46. Destructive Actions

Action penting harus membutuhkan confirmation.

Example:

```text
Deactivate User

Are you sure you want to deactivate this user?

[Cancel]
[Deactivate]
```

---

# 47. Logging

Application harus memiliki structured logging untuk:

- Exceptions.
- Authentication failures.
- Critical business failures.
- Background job failures.

Jangan log:

- Password.
- Authentication token.
- Private secret.
- Sensitive credentials.

---

# 48. Performance Requirements

MVP target:

- Normal API response < 500 ms dalam local/staging environment untuk common query.
- Gunakan eager loading untuk mencegah N+1 query.
- Gunakan indexes untuk field yang sering dicari.
- Gunakan pagination.
- Hindari loading entire dataset.

Suggested indexed fields:

```text
tickets.ticket_number
tickets.status
tickets.priority
tickets.reporter_id
tickets.assigned_technician_id
tickets.created_at

assets.asset_code
assets.serial_number
assets.status

users.email
```

---

# 49. Soft Delete Strategy

Gunakan soft delete pada entity yang tidak seharusnya hilang secara permanen, misalnya:

```text
users
assets
```

Ticket sebaiknya tidak dihapus oleh user.

Gunakan status:

```text
CANCELLED
```

untuk pembatalan ticket.

---

# 50. Auditability Requirement

Semua perubahan penting terhadap ticket harus dapat diketahui:

```text
Who changed it?
What changed?
When?
From what value?
To what value?
```

---

# 51. MVP Scope

MVP wajib memiliki:

## Authentication

- Login.
- Logout.
- Current user.

## User

- User management.
- Basic roles.
- Department.

## Ticket

- Create.
- View.
- List.
- Assign.
- Status update.
- Priority update.
- Comments.
- Internal notes.
- Attachment.
- Activity timeline.

## Asset

- Create.
- View.
- Update.
- Assign to employee.
- Return asset.
- Assignment history.
- Ticket history.

## System

- Notification.
- Audit log.
- Search.
- Filter.
- Pagination.
- Authorization.

---

# 52. Phase 2 Features

Setelah MVP stabil:

- SLA.
- Automatic escalation.
- Email notification.
- Redis queue.
- Scheduled jobs.
- Ticket reopen.
- Reporting.
- Data export.
- Dashboard charts.
- CSV export.
- Technician workload tracking.

---

# 53. Phase 3 Features

Future portfolio expansion:

- Hardware request workflow.
- Software request workflow.
- Multi-level approval.
- Procurement request.
- Knowledge base.
- FAQ.
- AI-assisted ticket categorization.
- AI knowledge search.
- WebSocket notification.
- Single Sign-On.
- Advanced metrics.

---

# 54. SLA Future Design

Possible SLA:

```text
LOW
Response: 8 hours
Resolution: 3 days

MEDIUM
Response: 4 hours
Resolution: 1 day

HIGH
Response: 1 hour
Resolution: 4 hours

CRITICAL
Response: 15 minutes
Resolution: 1 hour
```

SLA implementation bukan bagian wajib MVP pertama.

---

# 55. Seed Data

Development environment harus memiliki seed data.

Users:

```text
admin@opsflow.local
manager@opsflow.local
technician@opsflow.local
employee@opsflow.local
```

Gunakan password development yang jelas terdokumentasi di README khusus local development.

Jangan gunakan password seed di production.

Seed:

- 4 roles.
- 5 departments.
- 8 ticket categories.
- 10 users.
- 20 assets.
- 30 sample tickets.

---

# 56. README Requirements

README repository wajib menjelaskan:

```text
What is OpsFlow?
Architecture
Tech Stack
Features
Requirements
Installation
Docker Setup
Environment Variables
Database Migration
Seed Database
Running Tests
API Documentation
Screenshots
Project Structure
Deployment
```

Developer baru harus dapat menjalankan aplikasi dengan mengikuti README.

---

# 57. Git Workflow

Suggested branches:

```text
main
develop
feature/*
fix/*
```

Example:

```text
feature/authentication
feature/ticket-management
feature/asset-management
fix/ticket-assignment
```

Commits harus meaningful.

Example:

```text
feat(ticket): add ticket creation endpoint

fix(asset): prevent assigning retired assets

test(auth): add login authorization tests
```

---

# 58. Definition of Done

Suatu feature dianggap selesai jika:

1. Requirement terpenuhi.
2. Validation tersedia.
3. Authorization tersedia.
4. Happy path bekerja.
5. Error state ditangani.
6. Relevant tests tersedia.
7. Tidak menyebabkan regression.
8. Code formatted.
9. Code mudah dibaca.
10. Dokumentasi diperbarui jika diperlukan.

---

# 59. MVP Acceptance Criteria

OpsFlow MVP dianggap selesai apabila scenario berikut dapat berjalan penuh:

## Scenario 1

```text
Employee login
↓
Employee membuat ticket
↓
Ticket muncul sebagai OPEN
↓
Manager assign technician
↓
Status menjadi ASSIGNED
↓
Technician membuka ticket
↓
Technician mengubah status menjadi IN_PROGRESS
↓
Technician memberikan comment
↓
Employee membalas comment
↓
Technician mengubah ticket menjadi RESOLVED
↓
Employee dapat melihat hasilnya
↓
Ticket diubah menjadi CLOSED
```

## Scenario 2

```text
Admin membuat asset baru
↓
Asset memiliki status AVAILABLE
↓
Admin assign asset kepada employee
↓
Asset menjadi ASSIGNED
↓
Employee melihat asset tersebut pada My Assets
↓
Employee membuat ticket terkait asset tersebut
↓
Ticket terlihat pada asset repair history
↓
Asset dikembalikan
↓
Assignment history tetap tersimpan
```

## Scenario 3

Employee mencoba membuka:

```text
/admin/users
```

System harus menolak akses.

API harus memberikan:

```text
403 Forbidden
```

---

# 60. Development Order

Implementasi jangan dilakukan sekaligus.

Gunakan urutan berikut.

## Phase A: Foundation

1. Repository setup.
2. Docker.
3. Laravel setup.
4. React setup.
5. PostgreSQL.
6. Authentication.
7. Base layout.

## Phase B: Authorization

8. User.
9. Role.
10. Permission.
11. Department.
12. Policies.

## Phase C: Ticket Core

13. Ticket category.
14. Create ticket.
15. Ticket list.
16. Ticket detail.
17. Assignment.
18. Status workflow.
19. Priority.
20. Comments.
21. Internal notes.

## Phase D: Asset

22. Asset CRUD.
23. Asset assignment.
24. Asset history.
25. Asset-ticket relationship.

## Phase E: Operational Features

26. Notifications.
27. Audit logging.
28. Search.
29. Filters.
30. Dashboard.

## Phase F: Quality

31. Automated tests.
32. Error handling.
33. Security review.
34. Performance review.
35. README.
36. API documentation.

## Phase G: Deployment

37. CI pipeline.
38. Production Docker.
39. Deployment.
40. Monitoring/logging.

---

# 61. Codex Implementation Instructions

When implementing this PRD:

Do not build all modules at once.

Always work incrementally.

Before writing code for each feature:

1. Explain what feature is being implemented.
2. Explain its business flow.
3. Identify database changes.
4. Identify backend components.
5. Identify frontend components.
6. Identify authorization requirements.
7. Identify tests required.

Then implement.

Do not silently make major architectural decisions that contradict this PRD.

If a design decision is ambiguous:

- Prefer the simplest production-grade solution.
- Explain the tradeoff.
- Keep architecture maintainable.
- Avoid overengineering.

Use framework-native Laravel features whenever appropriate.

Preferred Laravel components:

```text
Form Requests
Policies
API Resources
Events
Listeners
Jobs
Notifications
Eloquent Relationships
Database Transactions
Service Classes
```

Do not introduce Repository Pattern automatically for every model.

Use repository abstraction only when it solves an actual architectural problem.

---

# 62. Code Quality Instructions

All code generated for OpsFlow should:

- Follow SOLID where applicable.
- Follow Laravel conventions.
- Follow React and TypeScript best practices.
- Use clear naming.
- Avoid God classes.
- Avoid massive controllers.
- Avoid duplicated business rules.
- Avoid hidden side effects.
- Avoid unnecessary abstraction.
- Have proper validation.
- Have authorization.
- Have error handling.
- Be testable.
- Be production-oriented.

Prioritize readability over clever code.

---

# 63. Developer Learning Mode

This project is also intended as a learning project.

Therefore, when generating code:

Do not only provide code.

For important architectural pieces explain briefly:

```text
What is this?
Why do we need it?
Where is it used?
What happens if we remove it?
How does data flow through it?
```

Important concepts that should be explained during development:

```text
Controller
Service
Model
Migration
Policy
Middleware
DTO
API Resource
Transaction
Queue
Event
Listener
Docker Container
Database Relationship
REST API
Authentication
Authorization
CI/CD
```

Keep explanations concise and practical.

---

# 64. Product Success Criteria

Project dianggap berhasil sebagai portfolio apabila:

- A recruiter dapat memahami fungsi produk dalam 1–2 menit.
- Application dapat dijalankan secara lokal melalui Docker.
- Repository memiliki README profesional.
- Application memiliki realistic business workflow.
- Role-based access bekerja.
- Automated testing tersedia.
- Database schema memiliki relationship realistis.
- UI terlihat profesional.
- Application dapat didemokan.
- Developer dapat menjelaskan architecture dan alur request.
- Developer dapat menjelaskan alasan penggunaan teknologi utama.

---

# 65. Final Product Description

OpsFlow is an internal IT service management platform that centralizes IT support tickets, asset assignment, technician workflows, user communication, notifications, and audit history.

Employees use OpsFlow to report IT issues and track progress.

IT technicians use OpsFlow to manage and resolve support requests.

IT managers use OpsFlow to assign workloads and monitor operations.

Administrators use OpsFlow to manage users, permissions, assets, and system configuration.

The system is designed as a production-oriented portfolio project demonstrating modern full-stack engineering practices including authentication, authorization, REST APIs, relational database design, workflow modeling, automated testing, Docker, CI/CD, and maintainable application architecture.
