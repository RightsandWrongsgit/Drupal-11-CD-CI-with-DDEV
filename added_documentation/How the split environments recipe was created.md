I would like you to make a Drupal 11 recipe called “Split_Environments”.   The Drupal 11 Project it will be associated with is called “Drupal 11 CD/CI with DDEV” and is set up as a GitHub Template specifically designed for local machine as well as SSH connected Git to GitHub branch updating with integration to Platform.sh hosting of each GitHub branch.  The `composer.json` file for this Drupal 11 project is provided here: `{

``json
"name": "platformsh/drupal11",
  "description": "This template builds Drupal 11 for Platform.sh based the \"Drupal Recommended\" Composer project.",
  "type": "project",
  "license": "GPL-2.0-or-later",
  "homepage": "https://www.drupal.org/project/drupal",
  "support": {
    "docs": "https://www.drupal.org/docs/user_guide/en/index.html",
    "chat": "https://www.drupal.org/node/314178"
  },
  "repositories": [
    {
      "type": "composer",
      "url": "https://packages.drupal.org/8"
    }
  ],
  "require": {
    "composer/installers": "^2.0",
    "drupal/config_split": "^2.0",
    "drupal/core-composer-scaffold": "^11.0",
    "drupal/core-project-message": "^11.0",
    "drupal/core-recommended": "^11.2",
    "drupal/environment_indicator": "^4.0",
    "drupal/stage_file_proxy": "^3.1",
    "drupal/structure_sync": "^2.0",
    "drush/drush": "^13.6",
    "platformsh/config-reader": "^2.4"
  },
  "conflict": {
    "drupal/drupal": "*"
  },
  "minimum-stability": "stable",
  "prefer-stable": true,
  "config": {
    "allow-plugins": {
      "composer/installers": true,
      "drupal/core-composer-scaffold": true,
      "drupal/core-project-message": true,
      "phpstan/extension-installer": true,
      "dealerdirect/phpcodesniffer-composer-installer": true,
      "cweagans/composer-patches": true
    },
    "sort-packages": true
  },
  "extra": {
    "drupal-scaffold": {
      "locations": {
        "web-root": "web/"
      }
    },
    "installer-paths": {
      "web/core": [
        "type:drupal-core"
      ],
      "web/libraries/{$name}": [
        "type:drupal-library"
      ],
      "web/modules/contrib/{$name}": [
        "type:drupal-module"
      ],
      "web/profiles/contrib/{$name}": [
        "type:drupal-profile"
      ],
      "web/themes/contrib/{$name}": [
        "type:drupal-theme"
      ],
      "drush/Commands/contrib/{$name}": [
        "type:drupal-drush"
      ],
      "web/modules/custom/{$name}": [
        "type:drupal-custom-module"
      ],
      "web/profiles/custom/{$name}": [
        "type:drupal-custom-profile"
      ],
      "web/themes/custom/{$name}": [
        "type:drupal-custom-theme"
      ]
    },
    "drupal-core-project-message": {
      "include-keys": [
        "homepage",
        "support"
      ],
      "post-create-project-cmd-message": [
        "<bg=blue;fg=white>                                                         </>",
        "<bg=blue;fg=white>  Congratulations, you\u2019ve installed the Drupal codebase  </>",
        "<bg=blue;fg=white>  from the drupal/recommended-project template!          </>",
        "<bg=blue;fg=white>                                                         </>",
        "",
        "<bg=yellow;fg=black>Next steps</>:",
        "  * Install the site: https://www.drupal.org/docs/installing-drupal",
        "  * Read the user guide: https://www.drupal.org/docs/user_guide/en/index.html",
        "  * Get support: https://www.drupal.org/support",
        "  * Get involved with the Drupal community:",
        "      https://www.drupal.org/getting-involved",
        "  * Remove the plugin that prints this message:",
        "      composer remove drupal/core-project-message"
      ]
    }
  }
}
`  
```

The `composer.json` file installs Drush plus the Drupal stage_file_proxy, config_split and environment_indicator contributed modules.  These three contributed are used by the Drupal 11 project in `settings.php` to provide the unique ‘local’, ‘develop’, ‘staged’, and ‘main’ environment configurations with user awareness signals via interface labels and color coding.  A copy of the `settings.php` file is provided here: `<?php

```php
/**
 * @file
 * Platform.sh example settings.php file for Drupal 11.
 */

// Default Drupal settings.
//
// These are already explained with detailed comments in Drupal's
// default.settings.php file.
//
// See https://api.drupal.org/api/drupal/sites!default!default.settings.php/11
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
$platform_environment = getenv('PLATFORM_BRANCH') ?: 'local';
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
`
```

So that the configuration files have appropriate homes, the project maintains a config subdirectory off of the project root that then has subdirectories below for ‘develop’, ‘local’, ’main’, ‘staged’, and ‘sync’. ‘sync’ is where the main yml files are stored during a drush cex to export the project database configuration.  The other four subdirectories are for configuration unique to their matching environment or branch name.  You will notice the subdirectories include a .gitkeep entry so they are retained if empty of any unique configuration file.  You will notice that each subdirectory contains a copy of a `config_suite.settings.yml` file whose contents are uniquely set for whether import and export should be automatic; a capability that users can set in the running project for their preference as they move through the CD/CI workflow (e.g. perhaps not automatic with ‘staged’ because release should be post-testing favorable results).  Also notice that the ‘local’ subdirectory has a separate ‘stage_file_proxy.settings.yml’ file so users who enable that module in the running Drupal 11 project can benefit from that contributed module. Here is the subdirectory structure with example file content (although the ‘sync’ yml list is highly abbreviated): `



```txt
develop
	.gitkeep
	config_suite.settings.yml




local
	.gitkeep
	.htaccess
	stage_file_proxy.settings.yml




main
	.gitkeep
	.htaccess
	config_suite.settings.yml




staged
	.gitkeep
	.htaccess
	config_suite.settings.yml




sync
	.gitkeep
	.htaccess
	announcements_feed.settings.yml
	automated_cron.settings.yml
`
```

I addition to enabling the composer.json installed modules and installing an enabling the additional modules needed to support  the outlined process, this recipe also project for the configuration yml files noted for the environments with config: import: entries and any actions: needed plus provide a content entry for a front page message of “Welcome to your Drupal 11 CI/CD with DDEV website.  We hope you enjoy your Drupal experience.  Below that state “The Drupal 11 template you installed took advantage of a very basic Drupal recipe to set up your starting point.  Here is an outline of the key next steps to follow (Insert a link to a file that will be in the project Git:GitHub repository under the added_documentation subdirectory off the project root named Next Steps.md)”  Below that provide a statement “Below are a list of resources to help you work with Drupal.  (Insert an unordered list of links to various Drupal training and tutorial sources)”  Below that provide a statement “Recipes are a great way to quickly get going with Drupal.  The recipe that started is project foundation is intentionally very basic so people who use it can go in any different directions; a strength of Drupal.  But you can add recipes to recipes so don’t be afraid to try more from the list below.  And remember, you established a workflow where you can try them and simply not move them up your workflow as a way to back out and return to what you had. (Provide the list of recipes from the drupal.org CMS project list here as an unordered list.)