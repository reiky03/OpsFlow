.PHONY: setup up down build ps logs test lint shell-backend shell-frontend

setup:
	@test -f .env || cp .env.example .env
	@test -f backend/.env || cp backend/.env.example backend/.env
	docker compose build
	docker compose run --rm backend composer install --no-interaction
	docker compose run --rm backend php artisan key:generate --force
	docker compose up -d
	docker compose exec backend php artisan migrate

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

ps:
	docker compose ps

logs:
	docker compose logs -f --tail=100

test:
	docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e DB_URL= backend php artisan test --compact

lint:
	docker compose exec frontend npm run lint
	docker compose exec frontend npm run build

shell-backend:
	docker compose exec backend sh

shell-frontend:
	docker compose exec frontend sh
