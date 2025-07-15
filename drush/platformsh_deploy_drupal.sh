#!/usr/bin/env bash
set -euo pipefail

DRUSH="/app/vendor/bin/drush"
cd /app/web

# Check if Drupal is installed
if $DRUSH status --field=bootstrap 2>/dev/null | grep -q 'Successful'; then
  echo "✅ Drupal is installed. Running post-deploy tasks..."

  $DRUSH -y cache:rebuild
  $DRUSH -y updatedb

  CONFIG_PATH=$($DRUSH php:eval "echo realpath(Drupal\\Core\\Site\\Settings::get('config_sync_directory'));")
  if [ -n "$CONFIG_PATH" ] && ls "$CONFIG_PATH"/*.yml >/dev/null 2>&1; then
    echo "📦 Config sync files found. Sanitizing before import..."
    /app/scripts/sanitize-config.sh

    echo "📦 Importing sanitized config..."
    $DRUSH -y config:import
  else
    echo "⚠️ No config sync files found. Skipping config:import."
  fi
else
  echo "🚫 Skipping deploy tasks: Drupal is not installed or not bootstrapped."
fi