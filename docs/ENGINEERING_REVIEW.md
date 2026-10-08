# Engineering Review

## Security controls

- Sanctum uses same-origin session cookies and CSRF protection; tokens are not stored in browser storage.
- Authentication is rate limited and failed login attempts are logged without passwords.
- Every protected request requires an active user; deactivation revokes database sessions.
- Form Requests validate all mutations and Policies/Gates enforce ownership and permission rules.
- Uploaded files use a strict extension/MIME allow-list, 10 MB limit, UUID server names, private storage, and authorized downloads.
- API Resources limit exposed fields; passwords and remember tokens are hidden.
- Eloquent/query binding prevents raw-input SQL interpolation.
- Production disables debug output and requires secrets through environment variables.

Remaining environment responsibilities: HTTPS/HSTS, secure headers at the edge, secret management, malware scanning if organizational policy requires it, backup encryption, and dependency/security scanning.

## Performance review

- Ticket, asset, dashboard, notification, user, and audit collections are paginated or bounded.
- Search/filter fields have targeted indexes; composite indexes cover common ownership/status queries.
- Detail and list endpoints eager-load serialized relationships to avoid N+1 queries.
- Dashboard aggregation runs in PostgreSQL and limits recent tickets to five rows.
- Transactions lock asset/ticket rows only for short state changes and retry up to three times.
- Production uses Redis for sessions, cache, and queues and enables PHP OPcache.

The PRD target of common API responses below 500 ms must be measured in the chosen staging environment with realistic concurrency; local timings alone are not a production guarantee.

## Logging and monitoring

Laravel emits structured context for authentication and business audit events, while production application logs go to stderr for collection by the runtime. Audit rows are append-only through the exposed API. Recommended alerts include repeated 5xx errors, failed queue jobs, abnormal login failures, high p95 latency, queue backlog, database saturation, storage pressure, and stale backups.
