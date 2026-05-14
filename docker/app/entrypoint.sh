#!/bin/sh
set -e

# Sync public/ into the shared volume so nginx can serve static files.
# Only needed when the volume is empty (first start or after volume wipe).
if [ ! -f /var/www/public/index.php ]; then
    echo "[entrypoint] Populating public_assets volume..."
    cp -a /var/www/public-src/. /var/www/public/
fi

exec "$@"
