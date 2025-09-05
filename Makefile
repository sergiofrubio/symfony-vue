# =========================
# Makefile para Docker Compose
# =========================

# --- Desarrollo ---
dev-up:
	docker compose up --build

dev-down:
	docker compose down

dev-logs:
	docker compose logs -f

dev-rebuild:
	docker compose build --no-cache

# --- Producción ---
prod-up:
	docker compose -f compose.yaml up -d --build

prod-down:
	docker compose -f compose.yaml down

prod-logs:
	docker compose -f compose.yaml logs -f

prod-rebuild:
	docker compose -f compose.yaml build --no-cache

# --- Comandos generales ---
ps:
	docker compose ps

exec-backend:
	docker compose exec backend bash

exec-frontend:
	docker compose exec frontend sh

exec-db:
	docker compose exec db bash
