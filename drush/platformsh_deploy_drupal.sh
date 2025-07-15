#!/usr/bin/env bash
#
# We don't want to run drush commands if drupal isn't installed.
# Similarly, we don't want to attempt to run config-import if there aren't any config files to import
# @todo expand further to pass --uri for all sites, with an eye towards multisite
#
#### ORGINAL FILE FROM PLATFORM.SH HERE ####
# if [ -n "$(drush status --field=bootstrap)" ]; then
#   drush -y cache-rebuild
#   drush -y updatedb
#   if [ -n "$(ls $(drush php:eval "echo realpath(Drupal\Core\Site\Settings::get('config_sync_directory'));")/*.yml 2>/dev/null)" ]; then
    drush -y config-import
  else
    echo "No config to import. Skipping."
  fi
# else
#   echo "Drupal not installed. Skipping standard Drupal deploy steps"
# fi
#

# CHATGPT MODIFIED TO IMPROVE CONTAINER USE AND ERROR HANDLING 
set -euo pipefail

DRUSH="./vendor/bin/drush"

# Ensure Drupal is bootstrapped before running commands.
if [ -n "$($DRUSH status --field=bootstrap 2>/dev/null)" ]; then
  echo "✅ Drupal detected. Starting deployment tasks..."

  $DRUSH -y cache-rebuild
  $DRUSH -y updatedb

  CONFIG_PATH=$($DRUSH php:eval "echo realpath(Drupal\Core\Site\Settings::get('config_sync_directory'));")
  if [ -n "$CONFIG_PATH" ] && ls "$CONFIG_PATH"/*.yml >/dev/null 2>&1; then
    echo "🗂 Config files found. Importing..."
    $DRUSH -y config-import
  else
    echo "⚠️ No config files to import. Skipping."
  fi
else
  echo "🚫 Drupal not installed or not bootstrapped. Skipping deploy tasks."
fi
