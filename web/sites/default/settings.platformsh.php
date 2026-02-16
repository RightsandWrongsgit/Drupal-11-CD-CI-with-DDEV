<?php

/**
 * @file
 * Platform.sh settings for Drupal 10/11.
 */

use Drupal\Core\Installer\InstallerKernel;

$platformsh = new \Platformsh\ConfigReader\Config();

/**
 * -----------------------------------------------------------------------------
 * Database configuration
 * -----------------------------------------------------------------------------
 */
if ($platformsh->hasRelationship('database')) {
  $creds = $platformsh->credentials('database');

  $databases['default']['default'] = [
    'driver' => 'mysql',
    'database' => $creds['path'],
    'username' => $creds['username'],
    'password' => $creds['password'],
    'host' => $creds['host'],
    'port' => $creds['port'],
    'init_commands' => [
      'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
    ],
  ];
}

/**
 * -----------------------------------------------------------------------------
 * Logging level
 * -----------------------------------------------------------------------------
 */
if ($platformsh->inRuntime()) {
  if ($platformsh->onProduction() || $platformsh->onDedicated()) {
    $config['system.logging']['error_level'] = 'hide';
  }
  else {
    $config['system.logging']['error_level'] = 'verbose';
  }
}

/**
 * -----------------------------------------------------------------------------
 * Redis cache configuration
 * -----------------------------------------------------------------------------
 */
if (
  $platformsh->hasRelationship('redis') &&
  !InstallerKernel::installationAttempted() &&
  extension_loaded('redis')
) {
  $redis = $platformsh->credentials('redis');

  $settings['cache']['default'] = 'cache.backend.redis';
  $settings['redis.connection'] = [
    'host' => $redis['host'],
    'port' => $redis['port'],
    'password' => $redis['password'] ?? NULL,
  ];
  $settings['redis.interface'] = 'PhpRedis';
}

/**
 * -----------------------------------------------------------------------------
 * Runtime filesystem configuration
 * -----------------------------------------------------------------------------
 */
if ($platformsh->inRuntime()) {
  $settings['file_private_path'] ??= $platformsh->appDir . '/private';
  $settings['file_temp_path'] ??= $platformsh->appDir . '/tmp';

  $settings['php_storage']['default']['directory'] ??= $settings['file_private_path'];
  $settings['php_storage']['twig']['directory'] ??= $settings['file_private_path'];

  $settings['hash_salt'] ??= $platformsh->projectEntropy;
  $settings['deployment_identifier'] ??= $platformsh->treeId;
}

/**
 * -----------------------------------------------------------------------------
 * Trusted host patterns (scoped, Drupal 11 safe)
 * -----------------------------------------------------------------------------
 */
$settings['trusted_host_patterns'] = [
  '^.+\.platformsh\.site$',
  '^.+\.upsun\.app$',
];

/**
 * -----------------------------------------------------------------------------
 * Import Platform.sh variables into Drupal settings/config
 * -----------------------------------------------------------------------------
 */
foreach ($platformsh->variables() as $name => $value) {
  $parts = explode(':', $name);
  [$prefix, $key] = array_pad($parts, 2, null);

  switch ($prefix) {
    case 'drupalsettings':
    case 'drupal':
      $settings[$key] = $value;
      break;

    case 'drupalconfig':
      if (count($parts) > 2) {
        $temp = &$config[$key];
        foreach (array_slice($parts, 2) as $n) {
          $temp = &$temp[$n];
        }
        $temp = $value;
      }
      break;
  }
}

