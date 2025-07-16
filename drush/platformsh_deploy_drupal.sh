#!/usr/bin/env bash
set -euo pipefail

DRUSH="/app/vendor/bin/drush"
CONFIG_SYNC_DIR="/app/private/config/sync"

cd /app/web

# Check if Drupal is installed
if $DRUSH status --field=bootstrap 2>/dev/null | grep -q 'Successful'; then
  echo "✅ Drupal is installed. Running post-deploy tasks..."

  $DRUSH -y cache:rebuild
  $DRUSH -y updatedb

  if [ -d "$CONFIG_SYNC_DIR" ] && ls "$CONFIG_SYNC_DIR"/*.yml >/dev/null 2>&1; then
    echo "📦 Config sync files found in $CONFIG_SYNC_DIR. Sanitizing before import..."
    /app/scripts/sanitize-config.sh "$CONFIG_SYNC_DIR"

    echo "📥 Importing sanitized config..."
    $DRUSH -y config:import --source="$CONFIG_SYNC_DIR"
  else
    echo "⚠️ No config sync files found in $CONFIG_SYNC_DIR. Skipping config:import."
  fi
else
  echo "🚫 Skipping deploy tasks: Drupal is not installed or not bootstrapped."
fi
