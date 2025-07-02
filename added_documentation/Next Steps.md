THIS FILE IS AN OUTLINE OF HOW TO PROCEED AFTER DEPLOYING THE PROJECT AND RUNNING THE RECIPE "Split Environments".

It is yet to be completed!

`ddev start`

OUTLINE THE SET UP USING THE SCREEN CAPTURES HERE

<img src="added_documentation/Drupal 11 Start 1.png" alt="Start up screenshot first step">

<img src="added_documentation/Drupal 11 Start 2.png" alt="Start up screenshot 2nd step">

<img src="added_documentation/Drupal 11 Start 3.png" alt="Start up screenshot 3rd step">

<img src="added_documentation/Drupal 11 Start 4.png" alt="Start up screenshot 4th step">

<img src="added_documentation/Drupal 11 Start 5.png" alt="Start up screenshot 5th step">

<img src="added_documentation/Drupal 11 Start 6.png" alt="Start up screenshot 6th step">

<img src="added_documentation/Drupal 11 Start 7.png" alt="Start up screenshot 7th step">

<img src="added_documentation/Drupal 11 Start 8.png" alt="Start up screenshot 8th step">

<img src="added_documentation/Drupal 11 Start 9.png" alt="Start up screenshot 8th step">


`ddev drush cr`

Check Drush Launcher:
If you're running Drush commands without specifying the full path (e.g., ./vendor/bin/drush), ensure the Drush Launcher is installed globally to make drush accessible:

'composer global require drush/drush-launcher'

`composer require drush/drush`

Verify Recipe Application:
After running 
`composer require myvendor/split-environments`
, you must apply the recipe using Drush or Drupal Console. Run the following command from your Drupal project root:

'drush recipe-apply myvendor/split-environments'

or, if the recipe is in a subdirectory:

'drush recipe-apply recipe/myvendor/split-environments'

Runs up to this point of actually applying the recipe; URL is clean and functional.
When this one was run it exported all the yml files to the sync directory. And the site crashed!
HASH was applied in the `core.extensions.yml` file and HASH and UUID in `system.site.yml` but the rest of the yml files just came over -- HASH and UUIDs came but it wasn't a change because nothing of there before hand.
`ddev drush recipe /var/www/html/recipes/split_environments -v`

`ddev drush cr`


`ddev restart`








`ddev drush site-install standard --existing-config --config-dir=config/sync`

system.site.yml with placeholder uuid
```
_core:
  default_config_hash: 
langcode: en
uuid: 00000000-0000-0000-0000-000000000000
name: 'Drupal 11 CD/CD with DDEV'
mail: you@example.com
slogan: ''
page:
  403: ''
  404: ''
  front: /node/1
admin_compact_mode: false
weight_select_max: 100
default_langcode: en
mail_notification: null
```




## NOTE: The Recipe Configures but Setting.php CONTROLS!

Here’s a detailed review of your settings.php and split_environments.recipe.yml for conflicts, overrides, and how they play out:

1. How Each File Works
settings.php
Activates one config split (local, develop, staged, or main) based on the PLATFORM_BRANCH environment variable.
Sets environment indicator colors/names and some config overrides (e.g., stage_file_proxy.settings.origin, system.logging.error_level).
Explicitly disables all splits, then enables only the one for the current environment.
Includes settings.platformsh.php and settings.local.php last, which can override previous settings.
split_environments.recipe.yml
Installs config_split and related modules.
Imports config for all splits (config_split.config_split.local, etc.) and some settings per environment.
Defines actions for config import/export and sets stage_file_proxy.settings.origin for local.
2. Conflicts and Overrides
Config Split Activation
settings.php always wins at runtime.
Even if the recipe imports all splits and sets their status, settings.php disables all splits and then enables only the one for the current environment.
Result: Only one split is ever active, as determined by settings.php, regardless of what the recipe imports.
Config Values (e.g., stage_file_proxy.settings.origin)
If the recipe imports a value for stage_file_proxy.settings.origin, but settings.php sets it (as it does for all environments), the value in settings.php will always override the imported config at runtime.
Result: The value in settings.php is what Drupal will use, not the imported value.
Config Import/Export Actions
The recipe’s actions (e.g., import: true, export: true/false) affect what gets imported/exported during config sync, but do not override runtime settings from settings.php.
3. How This Plays Out
Install Phase
The recipe imports all config splits and settings.
On first boot, settings.php disables all splits and enables only the one for the detected environment.
Any config value set in settings.php (like stage_file_proxy.settings.origin) will override the imported value.
Ongoing Use (Branch Merges: local → develop → staged → main)
When you switch environments (by changing PLATFORM_BRANCH), settings.php will enable the correct split and set the correct config values for that environment.
If you merge config from one branch to another, the active split and certain config values will always be determined by settings.php in the target environment.
No risk of multiple splits being active at once—settings.php prevents this.
4. Summary Table
Setting/Config	Recipe Import Effect	settings.php Effect (Runtime)	Who Wins?
Config split status	Imports all as enabled	Disables all, enables one	settings.php
stage_file_proxy.settings.origin	Imports value per env	Sets value per env	settings.php
environment_indicator.*	Imports value per env	Sets value per env	settings.php
system.logging.error_level	Imports value per env	Sets value per env	settings.php
5. Best Practices & Recommendations
Document in your repo that settings.php will always control split activation and certain config overrides.
Do not rely on the recipe to set split status or critical config values—use the recipe to import config, but expect settings.php to control what’s active.
If you want to change split logic, do it in settings.php (not in the recipe).
Test each environment after switching branches to ensure the correct split and config are active.
In summary:
Your settings.php will always override the recipe’s split activation and certain config values at runtime. This is the correct and safe approach for multi-environment Drupal projects. There are no dangerous conflicts, but the recipe’s imported values for split status and certain settings will be ignored in favor of what’s set in settings.php.