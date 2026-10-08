# OpsFlow API v1

Base URL: `/api/v1`

All protected routes use Laravel Sanctum's stateful SPA session. The browser first requests `/sanctum/csrf-cookie`, then submits credentials. Cookies are HTTP-only; the CSRF token is sent through `X-XSRF-TOKEN` on state-changing requests.

## Response contract

Single-resource success:

```json
{
  "success": true,
  "message": "Ticket retrieved successfully.",
  "data": {}
}
```

Paginated success:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 30,
    "last_page": 2
  }
}
```

Validation and authorization failures use the same top-level shape:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## Authentication

| Method | Endpoint | Access |
|---|---|---|
| `POST` | `/auth/login` | Public; rate limited |
| `POST` | `/auth/logout` | Authenticated |
| `GET` | `/auth/me` | Active authenticated user |
| `POST` | `/auth/forgot-password` | Public; generic response |
| `POST` | `/auth/reset-password` | Public; valid reset token |

Login body:

```json
{ "email": "employee@opsflow.local", "password": "password", "remember": false }
```

## Tickets

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/tickets` | Role-scoped ticket list |
| `POST` | `/tickets` | Create ticket |
| `GET` | `/tickets/{id}` | Detail, comments, files, activity |
| `PATCH` | `/tickets/{id}` | Update editable ticket fields |
| `POST` | `/tickets/{id}/assign` | Assign/reassign technician |
| `POST` | `/tickets/{id}/status` | Apply validated state transition |
| `POST` | `/tickets/{id}/priority` | Change final priority |
| `POST` | `/tickets/{id}/comments` | Add public reply/internal note |
| `GET` | `/tickets/{id}/activities` | Timeline |
| `POST` | `/tickets/{id}/attachments` | Private file upload |
| `GET` | `/tickets/{id}/attachments/{attachment}` | Authorized download |

Create body:

```json
{
  "title": "VPN disconnects every few minutes",
  "description": "The connection drops after authentication.",
  "category_id": 6,
  "priority": "HIGH",
  "asset_id": 1
}
```

An employee may only link an asset currently assigned to them. Ticket attachments allow JPG, JPEG, PNG, PDF, TXT, and LOG files up to 10 MB. Files use random server names and private storage.

List query parameters:

```text
search, status, priority, category_id, technician_id, reporter_id
date_from, date_to, sort_by, sort_direction, per_page, page
```

`per_page` accepts 20, 50, or 100. `sort_by` accepts `created_at`, `updated_at`, `priority`, or `status`.

Ticket workflow:

```text
OPEN → ASSIGNED → IN_PROGRESS → RESOLVED → CLOSED
                    ↓       ↑
                 WAITING_USER

OPEN or ASSIGNED → CANCELLED
```

## Assets

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/assets` | Global or own assigned assets |
| `POST` | `/assets` | Create inventory item |
| `GET` | `/assets/{id}` | Detail, custody history, repair tickets |
| `PATCH` | `/assets/{id}` | Update inventory metadata |
| `POST` | `/assets/{id}/assign` | Atomic assignment |
| `POST` | `/assets/{id}/return` | Atomic return |
| `GET` | `/assets/{id}/history` | Assignment history |

Asset assignment locks the row, changes status, records custody history, writes an audit event, and notifies the recipient in one transaction. Only `AVAILABLE` assets can be assigned.

List parameters: `search`, `status`, `category`, `assigned_user_id`, `sort_by`, `sort_direction`, `per_page`, `page`.

## Administration and operations

| Method | Endpoint | Purpose |
|---|---|---|
| `GET/POST` | `/users` | List/create users |
| `GET/PATCH` | `/users/{id}` | Detail/update user |
| `POST` | `/users/{id}/role` | Replace assigned roles |
| `POST` | `/users/{id}/deactivate` | Deactivate and revoke sessions |
| `GET` | `/roles` | Available roles |
| `GET` | `/permissions` | Permission catalog grouped by the client |
| `PUT` | `/roles/{id}/permissions` | Replace a non-admin role's permissions |
| `GET/POST` | `/departments` | List/create departments |
| `GET/PATCH` | `/departments/{id}` | Detail/update department |
| `GET/POST` | `/ticket-categories` | List/create categories |
| `GET/PATCH` | `/ticket-categories/{id}` | Detail/update category |
| `GET` | `/dashboard` | Role-scoped metrics and recent tickets |
| `GET` | `/notifications` | Current user's notifications |
| `POST` | `/notifications/{id}/read` | Mark one notification read |
| `POST` | `/notifications/read-all` | Mark all read |
| `GET` | `/audit-logs` | Permission-protected immutable audit log |

Authorization is enforced by Policies/Gates on the server. Hiding a button in React is a convenience, never the security boundary.

Role assignment accepts `role_ids` as a non-empty array, so an account may inherit the union of permissions from multiple roles. Administrator remains unrestricted and its permissions cannot be reduced through the API, preventing an accidental administrative lockout. Permission changes require `role.manage`, run transactionally, and create an audit entry.
