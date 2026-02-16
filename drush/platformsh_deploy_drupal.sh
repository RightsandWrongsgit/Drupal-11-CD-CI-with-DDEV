#!/usr/bin/env bash
# Platform.sh Drupal deploy script
# Runs cache rebuild, updates, and config import if Drupal is installed.

set -e

# Check if Drupal is installed
if drush status bootstrap --pipe >/dev/null 2>&1; then
    echo "Drupal bootstrap detected. Running deploy steps..."

    # Rebuild caches
    drush -y cache-rebuild

    # Run database updates
    drush -y updatedb

    # Config import if any config exists
    CONFIG_DIR=$(drush php:eval "echo realpath(\Drupal\Core\Site\Settings::get('config_sync_directory'));")
    if [ -d "$CONFIG_DIR" ] && compgen -G "$CONFIG_DIR/*.yml" > /dev/null; then
        echo "Config files detected. Importing..."
        drush -y config-import
    else
        echo "No config to import. Skipping."
    fi
else
    echo "Drupal not installed. Skipping standard Drupal deploy steps."
fi
