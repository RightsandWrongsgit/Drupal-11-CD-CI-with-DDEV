<a><h1>How the Split Environments Recipe is set up<h1><a>

# Composer.Json Only Did Some Basics
   The “Drupal 11 CD/CI with DDEV” and is set up as a GitHub Template specifically designed for your local machine to SSH connect between Git and the GitHub branches in a workflow. [More on GitHub SSH](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/about-ssh)  GitHub is set up in Platform.sh with  the hosting service ['integration' option.](https://docs.platform.sh/integrations/source/github.html)  Thus, each GitHub branch links back to and updates its paired Platform.sh environment.  You actually do your main work in Git:GitHub; a familiar working process to developers and very easy to use.  If you use [VSCode](https://code.visualstudio.com) managing your project with [Git and GitHub are especially easy.](https://www.youtube.com/watch?v=Fk12ELJ9Bww)   
   
   The main thing setting up a Drupal project is your `composer.json` which calls together all the resources for a base project and assembles them. The example `composer.json` file for this Drupal 11 project is provided here.  It is noted as an example because it may differ slightly from the actual operating file in this project if updates are made.  So look at the actual file if you are planning edits yourself.

```json
{
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
```

Notice that the `composer.json` file outlined above installs Drush plus the Drupal stage_file_proxy, config_split and environment_indicator contributed modules.  These three contributed modules are used by the Drupal 11 project in `settings.php` to provide the unique ‘local’, ‘develop’, ‘staged’, and ‘main’ environment configurations with user awareness signals via interface labels and color coding. This is a powerful driver of the CD/CI workflow splits; although it is dependent on the fact we set up the `config/sync` extra 'develop', 'local', 'main' and 'staged' subdirectories to work with it.  A copy of the `settings.php` file is provided here: `

```php
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
}
```

Here is an example of how the project maintains a config subdirectory off of the project root that then has subdirectories below for ‘develop’, ‘local’, ’main’, ‘staged’, and ‘sync’. ‘sync’.  The 'sync' subdirectory is where the main yml files are stored during a drush cex to export the project database configuration.  The other four subdirectories are for configuration unique to their matching environment or branch name.

  Notice the subdirectories include a `.gitkeep` entry so they are retained if empty of any unique configuration file.  You will notice that each subdirectory contains a copy of a `config_suite.settings.yml` file whose contents are uniquely set for whether import and export should be automatic; a capability that users can set in the running project for their preference as they move through the CD/CI workflow.  For example, the recipe pre-set for the 'staged' environment is set to "true" to automatically import the configuration but "false" so as NOT to automatically export it.  (e.g. lets not automatic export ‘staged’ because are release should be after we achieve post-testing favorable results).  [For more discussion on Automatic Configuration Import and Export Triggering](https://armtec.services/cicd/autoconfig.html) 
  
  Also notice that the ‘local’ subdirectory has a separate ‘stage_file_proxy.settings.yml’ file so users who enable that module in the running Drupal 11 project can benefit from that contributed module. You will notice on the table showing our configuration splits that we have the Stage File Proxy module on just the ‘local’ split. Platform.sh is taking care of the code to database content relationship at the host with the container builds for each branch. And, really, it is the local-to-host connection with your internet speed dependency plus the size of your database that is important to use this module anyway. The logic behind this module is that as content grows more and more for your site, it represents transmission overhead to be passing all of it to your local development environment. Therefore, this module is sort of a ‘just-in-time’ line of thinking around what content to bring local. As a developer, when you are working on the site’s code, it is most likely you are working in some very specific section of the overall site so why bring all the content from the site to you local machine; save space and transmission time, especially on larger, content rich sites. This module does just that; pulling content with a context of what is related to what you are working on so you can see your work in a real world setting. 
  
  Here is the subdirectory structure with example file content (although the ‘sync’ yml list is highly abbreviated): `



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

```

The composer.json installed modules but it doesn't automatically enable them.  This differs from a recipe `install:` key in the `recipe.yml` file which both installs and enables modules.  It also enables modules already installed as part of core or otherwise.   The `split_environments` recipe makes sure the modules that are needed are installed and enabled.  I assures the configuration yml files noted for the environments with `config:` `import:` entries plus any any `actions:` needed are handled.

Since recipes can also be set up to provide `content:` a content entry for a front page message of “Welcome to your Drupal 11 CI/CD with DDEV website.  We hope you enjoy your Drupal experience..."  has been included.

```yml
name: Split Environments
description: Configures a Drupal 11 site with environment-specific configuration splits for local, develop, staged, and main environments, with user awareness signals and a customized front page.
type: project
install:
  - config_split
  - environment_indicator
  - stage_file_proxy
  - structure_sync
  - config_suite
config:
  import:
    local:
      - config_suite.settings
      - stage_file_proxy.settings
    develop:
      - config_suite.settings
    staged:
      - config_suite.settings
    main:
      - config_suite.settings
  actions:
    local:
      config_suite.settings:
        import: true
        export: true
      stage_file_proxy.settings:
        origin: ''
    develop:
      config_suite.settings:
        import: true
        export: true
    staged:
      config_suite.settings:
        import: true
        export: false
    main:
      config_suite.settings:
        import: true
        export: true
content:
  nodes:
    - entity: node
      type: page
      title: Welcome to Your Drupal 11 CI/CD with DDEV Website
      field_body:
        value: |
          Welcome to your Drupal 11 CI/CD with DDEV website. We hope you enjoy your Drupal experience.
```
