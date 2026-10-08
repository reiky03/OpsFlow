# Production Deployment Guide

`compose.production.yaml` is a production-style reference for a single Docker host. It uses immutable application images and contains no source bind mounts. A real deployment should place the web service behind a TLS reverse proxy or load balancer.

## Pre-deployment checklist

- Provision a Linux host or container platform with Docker.
- Point DNS to the TLS entry point.
- Generate a Laravel key with `php artisan key:generate --show`.
- Generate a long random PostgreSQL password.
- Configure an external email provider for password reset.
- Configure encrypted database and upload backups.
- Decide log aggregation, exception tracking, metrics, and uptime monitoring.
- Never use the local demo seeders or their passwords in production.

## Configure

```bash
cp .env.production.example .env.production
```

Set `APP_URL` to the public HTTPS URL, `SANCTUM_STATEFUL_DOMAINS` to its host (and port if non-standard), and `SESSION_SECURE_COOKIE=true` when TLS is enabled.

## Build and release

```bash
docker compose --env-file .env.production -f compose.production.yaml build
docker compose --env-file .env.production -f compose.production.yaml up -d postgres redis
docker compose --env-file .env.production -f compose.production.yaml run --rm backend php artisan migrate --force
docker compose --env-file .env.production -f compose.production.yaml up -d
docker compose --env-file .env.production -f compose.production.yaml ps
```

Do not run migrations automatically in every web replica. Treat migration as an explicit release step and take a database backup before risky schema changes.

## Post-deployment verification

```bash
curl --fail https://opsflow.example.com/health
docker compose --env-file .env.production -f compose.production.yaml exec backend php artisan about
docker compose --env-file .env.production -f compose.production.yaml exec backend php artisan migrate:status
```

Verify login, CSRF/session cookies, one read-only API call, worker health, scheduler health, password-reset delivery, attachment upload/download, and authorization with a non-admin account.

## Operations

- Back up PostgreSQL and the `backend_storage` volume; test restoration regularly.
- Rotate application/database/mail credentials through a secret manager.
- Send container logs to centralized storage and alert on repeated 5xx, failed jobs, and authentication anomalies.
- Monitor HTTP latency, error rate, queue depth, CPU/memory, PostgreSQL connections, disk usage, and backup freshness.
- Use rolling replacement with versioned `IMAGE_TAG` values; do not deploy mutable source directories.

## Rollback

Application rollback means selecting the previous immutable image tag and recreating `web`, `backend`, `worker`, and `scheduler`. Database rollback must be planned per migration; do not automatically run destructive `migrate:rollback` against production data.
