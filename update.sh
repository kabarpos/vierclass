#!/usr/bin/env bash
set -euo pipefail

# update.sh — Cek update git, pull perubahan, jalankan tugas Laravel,
# dan (opsional) deploy ke server jarak jauh.
#
# Variabel ENV (opsional):
#   BRANCH=main            # nama branch
#   REMOTE=origin          # nama remote
#   PHP_BIN=php            # binary php
#   COMPOSER_BIN=composer  # binary composer
#   NPM_BIN=npm            # binary npm
#   DEPLOY_REMOTE=false    # true untuk kirim ke server
#   REMOTE_HOST=host       # host server
#   REMOTE_PATH=/var/www/app # path di server
#   SSH_USER=user          # user ssh
#   REMOTE_RUN=false       # true untuk jalankan composer/artisan di server
#
# Contoh pakai:
#   bash update.sh
#   BRANCH=main DEPLOY_REMOTE=true REMOTE_HOST=1.2.3.4 REMOTE_PATH=/var/www/app bash update.sh
#   DEPLOY_REMOTE=true REMOTE_RUN=true SSH_USER=deploy REMOTE_HOST=server REMOTE_PATH=/var/www/app bash update.sh

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

BRANCH="${BRANCH:-$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo main)}"
REMOTE="${REMOTE:-origin}"
PHP="${PHP_BIN:-php}"
COMPOSER="${COMPOSER_BIN:-composer}"
NPM="${NPM_BIN:-npm}"

DEPLOY="${DEPLOY_REMOTE:-false}"
HOST="${REMOTE_HOST:-}"
PATH_REMOTE="${REMOTE_PATH:-}"
USER_REMOTE="${SSH_USER:-}"
REMOTE_RUN="${REMOTE_RUN:-false}"

# Argumen CLI sederhana
ACTION="update"
BUILD_ASSETS="${BUILD_ASSETS:-true}"
SHOW_HELP=0

for arg in "$@"; do
  case "$arg" in
    update)
      ACTION="update"
      ;;
    --assets)
      BUILD_ASSETS=true
      ;;
    --no-assets)
      BUILD_ASSETS=false
      ;;
    -h|--help)
      SHOW_HELP=1
      ;;
    *)
      # Abaikan argumen lain agar kompatibel ke depan
      ;;
  esac
done

if [[ "$SHOW_HELP" -eq 1 ]]; then
  echo "Usage: ./update.sh [update] [--assets|--no-assets]"
  echo "  Tanpa argumen akan menjalankan proses update penuh dan build assets."
  exit 0
fi

log(){ echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"; }

check_git(){
  if ! command -v git >/dev/null; then log "Git tidak ditemukan"; return 1; fi
  log "Cek update (remote=$REMOTE, branch=$BRANCH)";
  git fetch "$REMOTE" "$BRANCH" --quiet || { log "Fetch gagal"; return 1; }
  local LOCAL REMOTE_HEAD
  LOCAL=$(git rev-parse HEAD)
  REMOTE_HEAD=$(git rev-parse "$REMOTE/$BRANCH")
  if [[ "$LOCAL" = "$REMOTE_HEAD" ]]; then
    log "Tidak ada perubahan baru"; return 1
  fi
  return 0
}

pull_changes(){
  if ! git diff --quiet || ! git diff --cached --quiet; then
    log "Ada perubahan lokal, stash dulu"; git stash push -u -m "update.sh $(date +%s)"
  fi
  log "Menarik perubahan (fast-forward)"; git pull --ff-only "$REMOTE" "$BRANCH"
}

local_tasks(){
  log "Composer install"; if command -v "$COMPOSER" >/dev/null; then "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader; else log "Composer tidak ada, lewati"; fi
  log "Laravel maintenance"; \
    "$PHP" artisan down || true; \
    "$PHP" artisan optimize:clear || true; \
    "$PHP" artisan migrate --force || true; \
    "$PHP" artisan filament:optimize || true; \
    "$PHP" artisan icons:cache || true; \
    "$PHP" artisan optimize || true; \
    "$PHP" artisan storage:link || true; \
    "$PHP" artisan up || true
  if [[ "$BUILD_ASSETS" = "true" ]] && command -v "$NPM" >/dev/null && [[ -f package.json ]]; then log "Build assets"; ($NPM ci || $NPM install); $NPM run build || true; fi
}

deploy_remote(){
  [[ "$DEPLOY" = "true" ]] || return 0
  if [[ -z "$HOST" || -z "$PATH_REMOTE" ]]; then log "REMOTE_HOST/REMOTE_PATH belum diisi, lewati deploy"; return 0; fi
  log "Kirim kode ke ${USER_REMOTE:+$USER_REMOTE@}$HOST:$PATH_REMOTE"
  if command -v rsync >/dev/null; then
    rsync -az --delete --exclude ".git" --exclude "node_modules" --exclude "storage/framework/cache" \
      "$ROOT/" "${USER_REMOTE:+$USER_REMOTE@}$HOST:$PATH_REMOTE/"
  else
    tar -czf /tmp/app.tar.gz --exclude .git --exclude node_modules --exclude storage/framework/cache -C "$ROOT" .
    scp /tmp/app.tar.gz "${USER_REMOTE:+$USER_REMOTE@}$HOST:$PATH_REMOTE/" && \
    ssh ${USER_REMOTE:+$USER_REMOTE@}$HOST "cd '$PATH_REMOTE' && tar xzf app.tar.gz && rm -f app.tar.gz"
  fi
  if [[ "$REMOTE_RUN" = "true" ]]; then
    log "Jalankan tugas di server"; ssh ${USER_REMOTE:+$USER_REMOTE@}$HOST "cd '$PATH_REMOTE' && \
      ${COMPOSER:-composer} install --no-dev --prefer-dist && \
      ${PHP:-php} artisan migrate --force && \
      (${PHP:-php} artisan filament:optimize || true) && \
      (${PHP:-php} artisan icons:cache || true) && \
      ${PHP:-php} artisan optimize"
  fi
}

main(){
  local changed=1
  if check_git; then changed=0; fi
  if [[ "$changed" -ne 0 ]]; then log "Tidak ada update baru. Selesai."; exit 0; fi
  pull_changes
  local_tasks
  deploy_remote
  log "Update selesai."
}

main "$@"
