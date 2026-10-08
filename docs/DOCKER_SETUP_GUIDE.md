# Panduan Docker OpsFlow dari Nol

Dokumen ini menjelaskan apa yang dibangun, alasan setiap komponennya, urutan setup proyek baru, command harian, dan template prompt yang dapat dipakai ulang.

## 1. Apa itu Docker?

Docker menjalankan aplikasi dan dependency-nya di lingkungan terisolasi bernama **container**. Tujuannya adalah membuat environment developer lebih konsisten.

Tanpa Docker, setiap developer perlu memasang PHP, Composer, Node.js, PostgreSQL, Redis, dan Nginx langsung di sistem operasi. Dengan Docker, host cukup memiliki Docker dan Git.

```text
Mac
└── Docker Engine
    └── Project OpsFlow
        ├── Nginx
        ├── React/Vite
        ├── Laravel/PHP-FPM
        ├── PostgreSQL
        ├── Redis
        ├── Queue worker
        └── Scheduler
```

## 2. Istilah dasar

### Image

Image adalah template read-only untuk membuat container. Contohnya `postgres:16-alpine`, `node:22-alpine`, dan image buatan project `opsflow-backend:local`.

### Container

Container adalah instance yang berjalan dari sebuah image. Satu image dapat menghasilkan beberapa container.

### Dockerfile

Dockerfile adalah resep untuk membangun satu image. Dockerfile backend menjelaskan base PHP, system package, PHP extension, Composer, working directory, dan command default.

### Docker Compose

Compose mengatur beberapa service sebagai satu aplikasi. `compose.yaml` menjelaskan bagaimana seluruh container OpsFlow dibangun, dikonfigurasi, dihubungkan, dan dijalankan.

### Volume

Volume menyimpan data di luar lifecycle container. Container dapat dihapus dan dibuat ulang tanpa menghilangkan data PostgreSQL atau Redis.

### Network

Compose membuat network internal dan DNS otomatis. Karena itu backend mengakses database menggunakan `postgres:5432`, bukan `localhost:5432`.

`localhost` di dalam container selalu berarti container itu sendiri.

### Port mapping

Format port adalah `HOST:CONTAINER`.

```yaml
ports:
  - "127.0.0.1:8080:80"
```

Artinya browser mengakses port `8080` di Mac, lalu Docker meneruskannya ke port `80` milik Nginx.

### Environment variable

Environment variable memasukkan konfigurasi tanpa menuliskannya permanen di image. Root `.env` dipakai Compose, sedangkan `backend/.env` dipakai Laravel.

### Health check

Health check membedakan container yang sekadar berjalan dengan service yang benar-benar siap menerima koneksi.

## 3. Arsitektur development OpsFlow

```text
Browser: http://localhost:8080
                 │
                 ▼
              Nginx
              /   \
             /     \
        /api        /
          │          │
          ▼          ▼
      PHP-FPM     React/Vite
          │
     ┌────┴────┐
     ▼         ▼
 PostgreSQL   Redis

Laravel worker ──► PostgreSQL/Redis
Laravel scheduler ──► scheduled commands
```

Nginx menjadi satu-satunya pintu masuk browser. Request `/api`, `/sanctum`, dan `/up` diarahkan ke Laravel. Request halaman lainnya diarahkan ke Vite. Model ini menjaga frontend dan API dalam satu origin, yang membantu konfigurasi Sanctum SPA dan CSRF.

## 4. File penting

```text
OpsFlow/
├── backend/                    Laravel
│   ├── .env                    konfigurasi lokal, tidak masuk Git
│   ├── .env.example            contoh konfigurasi
│   └── ...
├── frontend/                   React + TypeScript + Vite
│   ├── package.json
│   ├── package-lock.json
│   ├── vite.config.ts
│   └── ...
├── docker/
│   ├── backend/Dockerfile      resep image PHP-FPM
│   ├── frontend/Dockerfile     resep image Node/Vite
│   └── nginx/default.conf      reverse proxy
├── docs/DOCKER_SETUP_GUIDE.md
├── .env                        konfigurasi Compose lokal
├── .env.example                contoh konfigurasi Compose
├── compose.yaml                orkestrasi seluruh service
├── Makefile                    alias command umum
└── PRD_OpsFlow.md
```

## 5. Tahapan membuat proyek baru dari nol

Bagian ini merekam alur yang dilakukan untuk OpsFlow.

### Tahap A — Prerequisite

Periksa Docker, Compose, dan Git:

```bash
docker --version
docker compose version
docker info
git --version
```

Docker Desktop harus aktif. Login Docker Hub tidak wajib untuk image public.

### Tahap B — Buat folder dan Git repository

```bash
mkdir OpsFlow
cd OpsFlow
git init
git branch -M main
```

### Tahap C — Siapkan environment Compose

Root `.env.example` berisi nilai yang dibutuhkan Compose:

```dotenv
APP_PORT=8080
POSTGRES_DB=opsflow
POSTGRES_USER=opsflow
POSTGRES_PASSWORD=change_me
POSTGRES_PORT=5432
```

Untuk local development:

```bash
cp .env.example .env
```

`.env` harus masuk `.gitignore`; `.env.example` harus masuk Git.

### Tahap D — Tulis Dockerfile backend

Dockerfile backend dimulai dari PHP-FPM, memasang extension Laravel/PostgreSQL, mengambil Composer dari image resmi, menetapkan working directory, lalu menjalankan `php-fpm`.

Konsep penting:

```text
FROM       base image
RUN        command saat build
COPY       menyalin file ke image
WORKDIR    directory default
CMD        program default saat container berjalan
```

Build:

```bash
docker compose build backend
```

Build pertama lambat karena extension dikompilasi. Build berikutnya memakai layer cache selama Dockerfile tidak berubah.

### Tahap E — Scaffold Laravel melalui container

Composer tidak perlu dipasang di Mac:

```bash
docker compose run --rm backend \
  composer create-project laravel/laravel . --no-interaction
```

`run` membuat container sementara dari service backend. `--rm` menghapus container tersebut setelah command selesai.

Setelah scaffold, atur Laravel agar menggunakan:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
REDIS_HOST=redis
```

### Tahap F — Scaffold React melalui container

```bash
docker run --rm \
  -v "$PWD/frontend:/app" \
  -w /app \
  node:22-alpine \
  npm create vite@latest . -- --template react-ts
```

Kemudian buat lockfile:

```bash
docker run --rm \
  -v "$PWD/frontend:/app" \
  -w /app \
  node:22-alpine \
  npm install
```

`package-lock.json` membuat dependency reproducible. Dockerfile frontend menggunakan `npm ci`, bukan `npm install`, saat membangun image.

### Tahap G — Tambahkan service Compose

Service yang didefinisikan:

```text
nginx
frontend
backend
postgres
redis
worker
scheduler
```

Gunakan `depends_on` dengan health condition untuk service yang memang harus siap lebih dahulu. `depends_on` bukan pengganti retry logic aplikasi, tetapi membantu startup development.

### Tahap H — Build dan jalankan

```bash
docker compose config --quiet
docker compose build
docker compose up -d
docker compose ps
```

`docker compose config --quiet` memvalidasi YAML dan interpolasi environment sebelum container dibuat.

### Tahap I — Install backend dan migrasi

Pada fresh clone:

```bash
docker compose run --rm backend composer install --no-interaction
docker compose run --rm backend php artisan key:generate
docker compose up -d
docker compose exec backend php artisan migrate
```

### Tahap J — Verifikasi

```bash
curl -I http://localhost:8080
curl -I http://localhost:8080/up
docker compose exec redis redis-cli ping
docker compose exec postgres pg_isready -U opsflow -d opsflow
docker compose exec backend php artisan migrate:status
make test
docker compose exec frontend npm run lint
docker compose exec frontend npm run build
```

## 6. Setup cepat pada fresh clone

Dengan Makefile:

```bash
cp .env.example .env
cp backend/.env.example backend/.env
make setup
```

Tanpa Makefile:

```bash
cp .env.example .env
cp backend/.env.example backend/.env
docker compose build
docker compose run --rm backend composer install --no-interaction
docker compose run --rm backend php artisan key:generate --force
docker compose up -d
docker compose exec backend php artisan migrate
```

## 7. Command harian

### Menjalankan dan menghentikan

```bash
docker compose up -d
docker compose stop
docker compose start
docker compose restart
docker compose down
```

`stop` menghentikan container tetapi mempertahankannya. `down` menghapus container dan network Compose, tetapi named volume tetap ada.

### Melihat status dan log

```bash
docker compose ps
docker compose logs --tail=100
docker compose logs -f backend
docker compose logs -f frontend
```

### Menjalankan command di container aktif

```bash
docker compose exec backend php artisan migrate
make test
docker compose exec backend composer install
docker compose exec frontend npm run lint
```

### Membuat container sementara

```bash
docker compose run --rm backend php artisan about
```

Perbedaannya:

```text
up    menghidupkan service utama
exec  menjalankan command di container yang sudah hidup
run   membuat container sementara untuk satu command
```

### Rebuild

```bash
docker compose build
docker compose up -d --build
docker compose build --no-cache backend
```

Gunakan `--no-cache` hanya ketika benar-benar perlu karena semua layer dibangun ulang.

## 8. Mengapa ada satu atau dua strip?

Opsi pendek memakai satu strip:

```text
-d  sama dengan --detach
-a  sama dengan --all
-f  sama dengan --follow
```

Opsi panjang memakai dua strip:

```text
--rm
--build
--no-cache
--tail
```

Tanpa strip, kata dianggap command, image, service, container, atau argumen biasa. Karena itu `docker run rm nginx` berbeda dari `docker run --rm nginx`.

## 9. Data dan command berbahaya

Command berikut mempertahankan volume:

```bash
docker compose down
```

Command berikut menghapus container **dan seluruh named volume project**, termasuk database:

```bash
docker compose down -v
```

Jangan menjalankan `down -v`, `docker volume prune`, atau `docker system prune` tanpa memahami data yang akan dihapus.

Backup database development:

```bash
docker compose exec -T postgres \
  pg_dump -U opsflow -d opsflow > opsflow-backup.sql
```

## 10. Troubleshooting

### Port sudah dipakai

Ubah `APP_PORT` atau `POSTGRES_PORT` di root `.env`, kemudian:

```bash
docker compose down
docker compose up -d
```

### Container tidak healthy

```bash
docker compose ps
docker compose logs nama-service
docker inspect nama-container
```

### Perubahan Dockerfile tidak masuk

```bash
docker compose build nama-service
docker compose up -d nama-service
```

### Laravel tidak terhubung ke database

Pastikan `DB_HOST=postgres`, bukan `localhost`. Periksa:

```bash
docker compose exec backend php artisan config:clear
docker compose exec postgres pg_isready -U opsflow -d opsflow
```

### Dependency backend hilang

```bash
docker compose run --rm backend composer install
```

### Dependency frontend hilang

```bash
docker compose build frontend
docker compose up -d --force-recreate frontend
```

### Melihat network

```bash
docker network inspect opsflow_default
```

### Melihat volume

```bash
docker volume ls
docker volume inspect opsflow_postgres_data
```

## 11. Development versus production

Compose saat ini sengaja dioptimalkan untuk development:

- Source code memakai bind mount agar perubahan langsung terlihat.
- React berjalan melalui Vite development server.
- Laravel mengaktifkan debug pada environment lokal.
- PostgreSQL diekspos ke loopback host untuk database client lokal.

Production harus menggunakan konfigurasi terpisah:

- Image immutable dan multi-stage build.
- React dibuild menjadi static asset.
- Tidak memakai bind mount source code.
- `APP_DEBUG=false`.
- Secret berasal dari platform deployment, bukan file repository.
- TLS/HTTPS, backup database, log aggregation, dan monitoring.
- Database dan Redis tidak diekspos ke internet.

Jangan menganggap `compose.yaml` development ini sebagai konfigurasi production final.

## 12. Template prompt Docker untuk proyek baru

Prompt berikut dapat digunakan kepada coding agent setelah requirement dan stack proyek sudah disepakati:

```text
Set up a production-oriented local Docker development environment for this project.

Project structure:
- Backend: Laravel API using PHP-FPM
- Frontend: React + TypeScript + Vite
- Database: PostgreSQL
- Cache/queue infrastructure: Redis
- Reverse proxy: Nginx
- Authentication direction: Laravel Sanctum SPA cookies

Create a Docker Compose development stack containing:
- nginx
- backend
- frontend
- postgres
- redis
- queue worker
- scheduler

Requirements:
- Use separate Dockerfiles for backend and frontend.
- Use one browser entry point through Nginx.
- Route /api, /sanctum, and the Laravel health endpoint to PHP-FPM.
- Route frontend pages and Vite HMR to the frontend container.
- Use named volumes for PostgreSQL, Redis, and container-side node_modules.
- Use bind mounts for source code in development.
- Add health checks and dependency conditions.
- Bind published development ports to 127.0.0.1.
- Keep real secrets out of Git and provide .env.example files.
- Configure Laravel to reach PostgreSQL and Redis through Compose service names, never localhost.
- Include commands for first-time setup, daily development, tests, logs, rebuilds, and safe shutdown.
- Do not implement business features.
- Validate the Compose configuration, build all images, start the stack, run migrations, and verify HTTP, database, Redis, backend tests, and frontend lint/build.
- Explain every created file and important Docker concept for a developer who is learning Docker.
```

### Maksud setiap bagian prompt

- **Stack** menetapkan teknologi agar agent tidak menggantinya dengan pilihan lain.
- **Daftar service** menetapkan container yang harus tersedia.
- **Satu browser entry point** mengurangi masalah CORS/cookie Sanctum.
- **Named volume** menjaga data persisten.
- **Bind mount** memungkinkan live editing saat development.
- **Health check** memverifikasi kesiapan service.
- **Service name, bukan localhost** memastikan komunikasi antar-container benar.
- **Do not implement business features** membatasi scope pada infrastructure.
- **Validate and verify** mencegah agent berhenti setelah sekadar menulis YAML.
- **Explain** memastikan hasil dapat dipelajari, bukan menjadi konfigurasi misterius.

## 13. Urutan belajar yang disarankan

```text
1. Image dan container
2. run, start, stop, rm, ps, logs, exec
3. Port mapping
4. Dockerfile dan build cache
5. Docker Compose
6. Volume dan persistence
7. Network dan service discovery
8. Environment variable
9. Health check dan startup dependency
10. Debugging, test, dan rebuild
11. Perbedaan development dan production
```

Setelah memahami urutan ini, langkah berikutnya bukan Kubernetes. Langkah berikutnya adalah memakai Docker secara konsisten saat mengembangkan fitur OpsFlow dan mampu menjelaskan perjalanan request dari browser sampai database.
