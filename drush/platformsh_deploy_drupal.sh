#!/usr/bin/env bash
set -euo pipefail

DRUSH="./vendor/bin/drush"

cd /app/web

# Check if site is installed
if $DRUSH status --field=bootstrap 2>/dev/null | grep -q 'Successful'; then
  echo "✅ Drupal detected. Running deployment tasks..."

  $DRUSH -y cache-rebuild
  $DRUSH -y updatedb

  CONFIG_PATH=$($DRUSH php:eval "echo realpath(Drupal\\Core\\Site\\Settings::get('config_sync_directory'));")
  if [ -n "$CONFIG_PATH" ] && ls "$CONFIG_PATH"/*.yml >/dev/null 2>&1; then
    echo "🗂 Config files found. Importing..."
    $DRUSH -y config-import
  else
    echo "⚠️ No config files to import. Skipping."
  fi
else
  echo "🚫 Drupal not installed or bootstrap not successful. Skipping deploy tasks."
fi