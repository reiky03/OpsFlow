# OpsFlow Implementation Progress

> Handoff document for a human or AI continuing this repository. Read `PRD_OpsFlow.md`, this file, and `git status` before changing the project.

## Snapshot

- **Last updated:** 2026-10-06 (Asia/Jakarta)
- **Current milestone:** MVP implementation, premium responsive UI, RBAC management, and interactive workflow polish complete; portfolio capture and real hosting remain optional external follow-ups
- **Overall status:** Code-complete and locally verified
- **Primary source of truth:** `PRD_OpsFlow.md`
- **Development URL:** `http://localhost:8080`
- **Stack:** Laravel 13, PHP 8.4, React 19, TypeScript, Vite 8, PostgreSQL 16, Redis 7, Nginx, Docker Compose
- **Backend verification:** 29 tests passed, 184 assertions
- **Frontend verification:** TypeScript production build and Oxlint passed

## Architecture decisions

| Decision | Choice | Reason |
|---|---|---|
| Application shape | Modular monolith | Strong learning/portfolio architecture without distributed-system overhead |
| API namespace | `/api/v1` | Stable versioned contract |
| SPA authentication | Sanctum stateful session cookies | CSRF-protected same-origin authentication without browser token storage |
| RBAC | Native roles/permissions plus Gates and Policies | Backend-enforced granular authorization with no unnecessary package |
| Business workflows | Focused Action classes | Explicit and independently testable multi-step logic |
| Atomicity | PostgreSQL transactions plus row locks | Assignment and workflow state cannot partially update |
| Serialization | Laravel API Resources | Stable public payloads and hidden sensitive fields |
| Frontend server state | TanStack Query | Loading, caching, invalidation, and mutation lifecycle |
| Delivery | Dev Compose plus immutable production images | Fast local HMR and realistic production packaging |

## PRD milestone checklist

### Phase A — Foundation

- [x] Git repository on `main`
- [x] Docker Compose development stack
- [x] Laravel, React/TypeScript/Vite, PostgreSQL, Redis, Nginx
- [x] Backend, frontend, worker, scheduler, health checks, persistent volumes
- [x] Sanctum SPA authentication
- [x] Responsive professional application shell and public auth pages
- [x] Premium minimalist design layer with accessible focus states, reduced-motion support, and skeleton loading

### Phase B — Authorization and master data

- [x] Users with active/inactive lifecycle and soft-delete support
- [x] Four roles, granular permissions, Gates, and Policies
- [x] Admin Access Control matrix for editing each non-admin role's permissions
- [x] Multi-role account assignment with combined effective permissions
- [x] Five departments and category master data
- [x] Admin user create/update/deactivate/role/department UI
- [x] Category and department create/update UI
- [x] Permission-aware navigation and frontend route guards

### Phase C — Ticket core

- [x] Ticket categories
- [x] Create/list/detail/update with search, filters, sorting, and pagination
- [x] Human-readable unique ticket numbers
- [x] Technician assignment/reassignment
- [x] Validated state workflow including employee resolution confirmation
- [x] Priority management
- [x] Public comments and employee-hidden internal notes
- [x] Private validated attachments with authorized download
- [x] Activity timeline, audit records, and notifications

### Phase D — Asset lifecycle

- [x] Asset create/view/update, search, filters, sorting, and pagination
- [x] Human-readable asset code
- [x] Transactional assignment and return with row locks
- [x] Permanent assignment history
- [x] Related ticket/repair history
- [x] Employee My Assets scope and asset detail UI

### Phase E — Operations

- [x] Database notifications with single/read-all actions
- [x] Live unread navbar badge, All/Unread inbox filters, and notification deep links to related tickets/assets
- [x] Immutable audit-log API and UI
- [x] Role-scoped dashboard aggregations and recent activity
- [x] Idempotent realistic seed data: 4 roles, 5 departments, 9 categories, 10 users, 20 assets, 30 tickets
- [x] Search/filter/pagination and API error contract

### Phase F — Quality

- [x] All three PRD acceptance scenarios automated
- [x] Authentication, password reset, ticket, attachment, asset, notification, RBAC, and user tests
- [x] Backend Pint and frontend lint/build checks
- [x] Composer and npm dependency audits report zero known vulnerabilities
- [x] Security, performance, logging, and monitoring review
- [x] Professional README, API documentation, Docker guide, and deployment guide
- [x] Destructive-action confirmation and mutation toast feedback
- [x] Desktop and 390 px mobile visual QA for dashboard/login; guest session checks no longer emit false error toasts
- [x] Test database isolation guard prevents Docker test runs from refreshing development PostgreSQL
- [ ] Commit curated portfolio screenshots after final visual selection

### Phase G — Delivery

- [x] GitHub Actions backend/frontend/image pipeline
- [x] Immutable production backend and web multi-stage images
- [x] Production Compose using Redis sessions/cache/queue and persistent storage
- [x] Deployment, migration, backup, rollback, and observability checklist
- [x] Production stack smoke-tested on isolated port/volumes
- [ ] Deploy to a real public host after selecting a provider/domain

## Verified commands and outcomes

```bash
# Backend
cd backend
vendor/bin/pint --dirty --format agent
make test
# 29 passed, 184 assertions

# Frontend
cd frontend
npm audit --omit=dev
npm run build
npm run lint
# 0 vulnerabilities; build and lint passed

# Development runtime
docker compose ps
# nginx, backend, frontend, postgres, redis healthy; worker/scheduler running

# Seed verification
# users=10, assets=20, tickets=30, categories=9, departments=5
```

Production verification completed with `opsflow-web:verification` and `opsflow-backend:verification` on isolated port `8081`:

- Both images built successfully without dev dependencies or source bind mounts.
- Fresh PostgreSQL migrations completed.
- Web `/health` returned `200`.
- SPA title rendered as `OpsFlow — IT Operations`.
- Protected dashboard returned the expected structured `401` response without a session.
- Web/backend/PostgreSQL/Redis were healthy; worker and scheduler were running.
- The isolated production containers and their disposable test volumes were removed afterward. Development volumes were not touched.

## Live UI verification

- Employee dashboard correctly scoped to personal tickets/assets.
- Employee ticket detail hid internal technician notes.
- Manager dashboard showed all 30 tickets and 20 assets.
- Manager could view Users, Audit Logs, Assets, and Master Data navigation.
- Asset detail displayed current custody history and related repair tickets.
- Page title, login, logout, responsive shell, seeded tables, and status badges rendered correctly.
- Final visual pass verified the refined navigation hierarchy, translucent top bar, elevated cards, focused login composition, and mobile login layout at 390 × 844.
- Frontend production build and Oxlint passed again after the visual pass and silent guest-session handling.
- Demo data was restored and verified after discovering unsafe inherited Docker database variables; subsequent 26-test runs preserve all 10 users, 30 tickets, and 20 assets.
- Admin login was verified end-to-end through both `localhost:8080` and `127.0.0.1:8080`; Sanctum stateful domains explicitly support both development hosts.
- Admin Access Control was visually verified with all 23 permissions, editable non-admin role profiles, protected unrestricted Administrator access, and audit-backed updates.
- Users can now receive one or more roles from the redesigned role picker; the UI displays role chips and the backend combines the selected roles' permissions.
- The application shell was redesigned as a modern light workspace and rechecked at desktop and 390 px mobile widths. The desktop-only stray close icon was removed while the mobile drawer retains its intentional close control.
- The login page no longer exposes or pre-fills demo credentials; development accounts remain documented only in the README.
- Ticket Detail now has a responsive service-workspace layout, richer conversation composer, semantic activity icons/colors, old-to-new state badges, clearer workflow actions, and improved attachment/ownership panels.
- Notifications were verified end-to-end with a real local database notification: the server-backed unread badge updated correctly, the item was marked read, and its ticket deep link opened. An invalid `per_page=1` summary request discovered during QA was corrected to the supported contract.
- Logout now requires an accessible branded confirmation dialog before the session is ended.
- Asset cards received a compact editorial redesign with consistent density, two-column facts, custody identity, and lightweight actions; the oversized nested-card treatment was removed.
- Ticket activity was refined into a calm borderless event timeline, and route changes now reset scroll position so every page opens at its intended heading.
- A restrained interaction layer now provides tactile button states, animated active navigation, live status dots, card accent reveals, responsive icon motion, timeline feedback, modal/toast entrances, and reduced-motion support.
- The workspace now includes a permission-aware global command palette (`⌘K` / `Ctrl+K`) with live filtering, arrow-key navigation, and quick access only to destinations the signed-in user may open. Its desktop and 390 px mobile behavior was visually verified, and the browser console showed no application errors.
- The dashboard was rebuilt as an executive operations command center: a live operational hero, truthful resolution ring, active-queue metrics, animated workload distribution, focused control-room signals, and a refined recent-work table. It uses only existing API data, remains permission-scoped, passed TypeScript/Oxlint, and was visually verified at 1440 px desktop and 390 px mobile with a clean browser console.
- Frontend pages are now route-level lazy loaded. The production entry bundle dropped from roughly 500.63 kB to 306.34 kB, the oversized-chunk warning was removed, and navigation between Dashboard and Assets was verified in the running application.
- Final assurance was rerun after the visual and code-splitting work: 29 backend tests (184 assertions), production frontend build, Oxlint, npm audit, Composer audit, Compose health checks, command-palette navigation, logout cancellation, notification rendering, 390 px responsive dashboard, and browser-console inspection all passed.
- A final enterprise depth layer now spans the application shell: pointer-following ambient lighting on precise-pointer devices, a glass top bar with live workspace status, protected-workspace trust panel, richer sidebar/navigation states, button shimmer and depth, stronger card/table feedback, and elevated modal/toast surfaces. Touch devices and reduced-motion users receive restrained fallbacks; desktop and 390 px mobile visual QA plus browser-console inspection passed.
- The premium system now covers every application surface, not only the dashboard: reusable contextual workspace heroes were applied to Tickets, Assets, Asset Detail, Create Ticket, Users, Audit Logs, Master Data, and Profile; table/filter workspaces, asset cards, custody history, guided request forms, identity/permission panels, modals, and password recovery received consistent hierarchy, depth, feedback, responsive behavior, and reduced-motion fallbacks. Existing specialist layouts for Dashboard, Ticket Detail, Notifications, Access Control, and Login remain integrated with the same visual language. Employee and admin route audits, 390 px responsive inspection, clean browser consoles, frontend build/Oxlint, and all 29 backend tests (184 assertions) passed.
- The root README was rebuilt as the GitHub portfolio landing page with a professional project summary, business workflow diagram, capabilities, role-access matrix, architecture, engineering decisions, technology stack, Docker quick start, demo accounts, verification evidence, security controls, CI/CD, documentation index, deployment guidance, and an honest public-demo/screenshot checklist. Local documentation links were verified and real environment files remain ignored by Git.

## Key local data

All demo accounts use password `password`:

```text
admin@opsflow.local
manager@opsflow.local
technician@opsflow.local
employee@opsflow.local
```

Never use demo credentials or run local demo seeders in production.

## Resume checklist

```bash
cd "/Users/bsi/Proyek Mandiri/OpsFlow"
git status
docker compose up -d
docker compose ps
docker compose exec backend php artisan migrate:status
make test
docker compose exec frontend npm run build
docker compose exec frontend npm run lint
```

## Known external decisions and future scope

- A real deployment provider/domain and production mail provider have not been selected.
- The README screenshot section is ready, but final curated images should be captured after choosing the preferred portfolio framing.
- The PRD deliberately places SLA automation, reports/export, knowledge base, FAQ, SSO, WebSocket notifications, and AI categorization in Phase 2/3—not the MVP.
- The PRD's local response target below 500 ms is architecture-supported, but production p95 must be measured in the selected staging environment under realistic concurrency.

## Important implementation rules

- Keep controllers thin and workflows explicit in focused Actions.
- Use Form Requests, Policies/Gates, API Resources, transactions, and feature tests.
- Never trust frontend permissions as the security boundary.
- Do not introduce a Repository layer unless a concrete integration/query need appears.
- Do not run `docker compose down -v` against development unless local data deletion is intentional.
- Update this file whenever a meaningful milestone or next step changes.
