<?php

/**
 * @file
 * Platform.sh-compatible settings.php for Drupal 11.
 */

use Drupal\Core\Installer\InstallerKernel;

$databases = [];
$config_directories = [];

$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
  'vendor',
];

$settings['state_cache'] = TRUE;

/**
 * -----------------------------------------------------------------------------
 * Platform.sh environment detection
 * -----------------------------------------------------------------------------
 */
$is_platformsh = getenv('PLATFORM_PROJECT') !== false;
$platform_environment = $is_platformsh
  ? getenv('PLATFORM_ENVIRONMENT')
  : 'local';

$settings['platform_environment'] = $platform_environment;

/**
 * -----------------------------------------------------------------------------
 * Config sync directory (ALWAYS versioned)
 * -----------------------------------------------------------------------------
 */
$settings['config_sync_directory'] = $is_platformsh
  ? '../config/sync'
  : '../config/sync';

/**
 * -----------------------------------------------------------------------------
 * Config split defaults
 * -----------------------------------------------------------------------------
 */
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.develop']['status'] = FALSE;
$config['config_split.config_split.staged']['status'] = FALSE;
$config['config_split.config_split.main']['status'] = FALSE;

/**
 * -----------------------------------------------------------------------------
 * Environment-specific configuration
 * -----------------------------------------------------------------------------
 */
switch ($platform_environment) {
  case 'main':
    $settings['environment_indicator_name'] = 'Production';
    $settings['environment_indicator_color'] = '#ff0000';
    $config['environment_indicator.indicator'] = [
      'name' => 'Production',
      'bg_color' => '#ff0000',
      'fg_color' => '#FFFFFF',
    ];
    $config['config_split.config_split.main']['status'] = TRUE;
    $config['system.logging']['error_level'] = 'hide';
    break;

  case 'staged':
    $settings['environment_indicator_name'] = 'Staging';
    $settings['environment_indicator_color'] = '#FF6610';
    $config['environment_indicator.indicator'] = [
      'name' => 'Staging',
      'bg_color' => '#FF6610',
      'fg_color' => '#FFFFFF',
    ];
    $config['config_split.config_split.staged']['status'] = TRUE;
    $config['system.logging']['error_level'] = 'some';
    break;

  case 'develop':
    $settings['environment_indicator_name'] = 'Development';
    $settings['environment_indicator_color'] = '#04caf0';
    $config['environment_indicator.indicator'] = [
      'name' => 'Development',
      'bg_color' => '#04caf0',
      'fg_color' => '#FFFFFF',
    ];
    $config['config_split.config_split.develop']['status'] = TRUE;
    $config['system.logging']['error_level'] = 'some';
    break;

  default:
    $settings['environment_indicator_name'] = 'Local';
    $settings['environment_indicator_color'] = '#006600';
    $config['environment_indicator.indicator'] = [
      'name' => 'Local',
      'bg_color' => '#006600',
      'fg_color' => '#FFFFFF',
    ];
    $config['config_split.config_split.local']['status'] = TRUE;
    $config['system.logging']['error_level'] = 'verbose';
    break;
}

$config['environment_indicator.settings']['toolbar_integration'] = ['toolbar'];

/**
 * -----------------------------------------------------------------------------
 * Platform.sh services (database + Redis)
 * -----------------------------------------------------------------------------
 */
if ($is_platformsh && getenv('PLATFORM_RELATIONSHIPS')) {
  $relationships = json_decode(getenv('PLATFORM_RELATIONSHIPS'), TRUE);

  // Redis
  if (!empty($relationships['redis'][0])) {
    $redis = $relationships['redis'][0];

    $settings['cache']['default'] = 'cache.backend.redis';
    $settings['redis.connection'] = [
      'host' => $redis['host'],
      'port' => $redis['port'],
      'password' => $redis['password'] ?? NULL,
    ];
    $settings['redis.interface'] = 'PhpRedis';
  }
}

/**
 * -----------------------------------------------------------------------------
 * Local overrides
 * -----------------------------------------------------------------------------
 */
if (getenv('IS_DDEV_PROJECT') === 'true' && file_exists(__DIR__ . '/settings.ddev.php')) {
  include __DIR__ . '/settings.ddev.php';
}

if (file_exists($app_root . '/' . $site_path . '/settings.platformsh.php')) {
  include $app_root . '/' . $site_path . '/settings.platformsh.php';
}

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
