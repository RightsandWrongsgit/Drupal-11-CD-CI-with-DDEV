<?php
/**
 * @file
 * Platform.sh example settings.php file for Drupal 10.
 */

// Default Drupal settings.
//
// These are already explained with detailed comments in Drupal's
// default.settings.php file.
//
// See https://api.drupal.org/api/drupal/sites!default!default.settings.php/10
$databases = [];
$config_directories = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
  'vendor', // Covers Composer-based projects.
];

$settings['state_cache'] = TRUE;

// Hash salt is set by Platform.sh via settings.platformsh.php or overridden in settings.local.php.
// Do not hardcode here to keep the template reusable and secure in a public repo.
// $settings['hash_salt'] is intentionally left unset.

// Set up a config sync directory.
//
// This is defined inside the read-only "config" directory, deployed via Git.
$settings['config_sync_directory'] = '../config/sync';

// Config splits are activated based on the environment (local, develop, staged, main).
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.develop']['status'] = FALSE;
$config['config_split.config_split.staged']['status'] = FALSE;
$config['config_split.config_split.main']['status'] = FALSE;

// Detect Platform.sh environment from PLATFORM_BRANCH, default to 'local'.
$known_splits = ['develop', 'staged', 'main', 'local'];
$platform_environment = in_array(getenv('PLATFORM_BRANCH'), $known_splits)
  ? getenv('PLATFORM_BRANCH')
  : 'local';
$settings['platform_environment'] = $platform_environment;

// Environment-specific configurations.
switch ($platform_environment) {
  case 'main':
    $settings['environment_indicator_name'] = 'Production';
    $settings['environment_indicator_color'] = '#ff0000';
    $config['environment_indicator.indicator']['name'] = 'Production';
    $config['environment_indicator.indicator']['bg_color'] = '#ff0000';
    $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
    $config['config_split.config_split.main']['status'] = TRUE;
    $config['stage_file_proxy.settings']['origin'] = ''; // No proxy in production.
    $config['system.logging']['error_level'] = 'hide'; // Minimal logging in production.
    break;

  case 'staged':
    $settings['environment_indicator_name'] = 'Staging';
    $settings['environment_indicator_color'] = '#FF6610';
    $config['environment_indicator.indicator']['name'] = 'Staging';
    $config['environment_indicator.indicator']['bg_color'] = '#FF6610';
    $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
    $config['config_split.config_split.staged']['status'] = TRUE;
    $config['stage_file_proxy.settings']['origin'] = ''; // No proxy in staging.
    $config['system.logging']['error_level'] = 'some'; // Moderate logging in staging.
    break;

  case 'develop':
    $settings['environment_indicator_name'] = 'Development';
    $settings['environment_indicator_color'] = '#04caf0';
    $config['environment_indicator.indicator']['name'] = 'Development';
    $config['environment_indicator.indicator']['bg_color'] = '#04caf0';
    $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
    $config['config_split.config_split.develop']['status'] = TRUE;
    $config['stage_file_proxy.settings']['origin'] = ''; // No proxy in hosted dev.
    $config['system.logging']['error_level'] = 'some'; // Moderate logging in dev.
    break;

  default:
    $settings['environment_indicator_name'] = 'Local';
    $settings['environment_indicator_color'] = '#006600';
    $config['environment_indicator.indicator']['name'] = 'Local';
    $config['environment_indicator.indicator']['bg_color'] = '#006600';
    $config['environment_indicator.indicator']['fg_color'] = '#FFFFFF';
    $config['config_split.config_split.local']['status'] = TRUE;
    // Dynamic stage_file_proxy origin for local env using Platform.sh relationships (if available).
    $config['stage_file_proxy.settings']['origin'] = '';
    if (getenv('PLATFORM_RELATIONSHIPS')) {
      $relationships = json_decode(base64_decode(getenv('PLATFORM_RELATIONSHIPS')), TRUE);
      if (!empty($relationships['website'])) {
        $config['stage_file_proxy.settings']['origin'] = 'https://' . $relationships['website'][0]['host'];
      }
    }
    $config['system.logging']['error_level'] = 'verbose'; // Full logging locally.
    break;
}

// Enable Environment Indicator toolbar integration (fixed to use array instead of boolean).
$config['environment_indicator.settings']['toolbar_integration'] = ['toolbar'];

// Optional Redis cache backend for Platform.sh (uncomment and configure if used).
/*
if (getenv('PLATFORM_RELATIONSHIPS')) {
  $settings['cache']['default'] = 'cache.backend.redis';
  // Add Redis configuration in settings.platformsh.php or a custom include.
}
*/

// DDEV settings include for local environment.
if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.php')) {
  include __DIR__ . '/settings.ddev.php';
}

// Automatic Platform.sh settings.
if (file_exists($app_root . '/' . $site_path . '/settings.platformsh.php')) {
  include $app_root . '/' . $site_path . '/settings.platformsh.php';
}

// Local settings. These come last so that they can override anything.
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
