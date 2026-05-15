#!/usr/bin/env bash
set -euo pipefail

# ============================================================
#  StokinventoryV2 — Production Deploy Script
#  Usage: bash deploy.sh
# ============================================================

APP_CONTAINER="stokinventory_app"

echo "▶ Pulling latest code..."
git pull origin main

echo "▶ Building & restarting containers..."
docker compose down
docker compose up -d --build

echo "▶ Waiting for app container to be ready..."
sleep 5

echo "▶ Installing Composer dependencies..."
docker compose exec -T "$APP_CONTAINER" composer install --no-dev --optimize-autoloader --no-interaction

echo "▶ Running database migrations..."
docker compose exec -T "$APP_CONTAINER" php artisan migrate --force

echo "▶ Clearing & caching config..."
docker compose exec -T "$APP_CONTAINER" php artisan config:clear
docker compose exec -T "$APP_CONTAINER" php artisan config:cache

echo "▶ Clearing & caching routes..."
docker compose exec -T "$APP_CONTAINER" php artisan route:clear
docker compose exec -T "$APP_CONTAINER" php artisan route:cache

echo "▶ Clearing & caching views..."
docker compose exec -T "$APP_CONTAINER" php artisan view:clear
docker compose exec -T "$APP_CONTAINER" php artisan view:cache

echo "▶ Clearing application cache..."
docker compose exec -T "$APP_CONTAINER" php artisan cache:clear

echo "▶ Linking storage..."
docker compose exec -T "$APP_CONTAINER" php artisan storage:link --force 2>/dev/null || true

echo "▶ Reloading host Nginx..."
sudo nginx -t && sudo systemctl reload nginx

echo ""
echo "✔ Deploy complete — https://stokinventory.com"
