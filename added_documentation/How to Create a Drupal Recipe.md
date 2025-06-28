
<a><h1>How to Create a Drupal Recipe<H1><a>
---
<a name="top"></a>

## Table of Contents

1. [Introduction](#introduction)
2. [Recipe Structure](#recipe-structure)
3. [Creating a Recipe](#creating-a-drupal-recipe)
    - [Understand the Recipe Structure](#understand-the-recipe-structure)
    - [Set Up a Recipe Directory](#set-up-a-recipe-directory)
    - [Create a Custom Composer Package](#create-a-custom-composer-package)
    - [Create the recipe YML Files](#create-the-recipe-yml-files)
4. [Handling UUIDs](#how-to-handle-uuids-in-recipes)
5. [Adding Default Content](#add-default-content-optional)
6. [Version Control](#version-control-the-recipe)
7. [Installing a Recipe](#installing-a-drupal-recipe)
8. [Troubleshooting & Best Practices](#additional-notes-and-tips)
9. [References](#references)
---
# Introduction

Creating and installing a Drupal recipe in Drupal 11 involves defining a set of configurations, modules, and content in a structured format and applying it to a Drupal site. Recipes are a powerful feature introduced in Drupal 10.3 and stabilized in Drupal 11, allowing site builders to automate module installation and configuration in a flexible, reusable way. Below is a step-by-step guide to creating and installing a Drupal recipe in Drupal 11.

[⬆️ Back to Top](#top)


...

# Recipe Structure

The key items to have a successful recipe are to confirm or establish the `/recipes` directory off the project root and to prepare the two required YML files; `my_recipe.yml` and `my_recipe.info.yml`.  Of course you will replace the "my_recipe" part with whatever you want to call yours.  Since recipes can also set or change configuration characteristic of a site, you can include new or edited version of the files that appear in your `/config/sync` directory when you do a `drush config export` or `drush cex` or otherwise export the configuration of your site's database into yml file form.  And, recipes can also bring content into a Drupal site where you believe that would be helpful to better understand what the recipe provides by offering example material. 

[⬆️ Back to Top](#top)

# Creating a Drupal Recipe

The steps that follow will show you how you can create a Drupal recipe.  First there is a review of the key parts of its structure followed by an example directory layout you can use.  That is followed by how you can create a composer.json file at the Recipe level in case you need to bring additions to what your main site install brings.  In the [Trouble Shooting and Best Practices](#additional-notes-and-tips) section at the end, there is also a review of how to "unpack" recipes to fold them or parts of them into your main site Composer.json file.  

The most important part to understand are the YML files.  One instructs what the recipe is to do and the other that describes the recipe to your Drupal site so that it connects to your site properly.  See [Create the recipe YML File](#create-the-recipe-yml-files).

The sections about adding content and how it leverages configuration files help get more of the full depth of what a recipe can do.  Since configuration on an existing site is found in YML files, on an existing site they have UUIDs that could conflict, so there is a discussion on [Handling UUIDs](#how-to-handle-uuids-in-recipes).

Finally there is the fact you should version control your recipe and how you go about [Installing a Recipe](#installing-a-drupal-recipe).

[⬆️ Back to Top](#top)

<details>
<summary>**Example Workflow** (Click to Open)<summary>

1. Create a recipe directory: `recipes/blog_feature`.
2. Add `recipe.yml`, `config/node.type.blog.yml`, and `content/node/blog/1.yml` as shown above.
3. Add the recipe to Composer:
   ```bash
   composer require my_vendor/blog_feature
   ```
4. Apply the recipe:
   ```bash
   cd web
   php core/scripts/drupal recipe ../recipes/blog_feature -v
   drush cr
   ```
5. Verify that the blog content type and sample post are created in the Drupal admin interface.

6. Use the Default Content API for Content**:
   
7. Validate with Configuration Inspector**:
   - Use the Configuration Inspector module (`drupal/config_inspector`) to validate your recipe’s configuration files before distribution. This helps identify potential UUID or dependency issues:
     ```bash
     composer require drupal/config_inspector
     drush config-inspector
     ```

8. Consider Recipe Scope and Reusability**:
   - If your recipe is meant for broad reuse across multiple sites, omitting UUIDs is critical to ensure it applies cleanly to any Drupal 11 site.
   - If the recipe is for a specific site or distribution, you might retain UUIDs to enforce consistency, but document this clearly and use `--force` when applying.
</details>

[⬆️ Back to Top](#top)

# Understand the Recipe Structure:

   - A Drupal recipe is primarily defined by a `recipe.yml` file, which specifies metadata, module/theme installations, configurations, and dependencies.
   - Optionally, it can include a `composer.json` file for managing dependencies, a `config` directory for configuration files, and a `content` directory for default content.
   - Recipes are declarative, meaning they describe the desired state (e.g., modules to install, configurations to apply) without including custom PHP code or hooks.

[⬆️ Back to Top](#top)

# Set up a Recipe Directory

   - Create a directory for your recipe, typically in the `/recipes` folder of your Drupal project (e.g., `recipes/my_recipe`).
   - Alternatively, recipes can reside in the `/vendor` directory if managed by Composer, but `/recipes` is the emerging convention.

```
recipes/
└── my_recipe/
    ├── composer.json
    ├── my_recipe.info.yml
    ├── my_recipe.install
    ├── config/
    │   └── install/
    │       └── (configuration YML files)
    ├── content/
    │   └── node/
    │       └── article/
    │           ├── default_content.my-first-article.yml
    │           └── default_content.my-second-article.yml
    ├── my_recipe.yml
    └── README.md

```

  - config/install/: for standard Drupal configuration (content types, views, etc.)
  - content/: for default entity content, such as example nodes
  - my_recipe.install: for any procedural setup needed

[⬆️ Back to Top](#top)

# Create a Custom Composer Package

Inside `recipes/my_recipe/composer.json` File (Optional):
   - If your recipe depends on contributed modules or themes, include a `composer.json` file to specify these dependencies.
```composer.json
{
  "name": "myvendor/my-recipe",
  "description": "A Drupal recipe for blog functionality with starter content",
  "type": "drupal-recipe",
  "require": {
    "drupal/node": "^1.0",
    "drupal/views": "^1.0",
    "drupal/ckeditor5": "^1.0",
    "drupal/default_content": "^1.0"
  },
  "extra": {
    "drupal-recipe": {
      "version": "1.0"
    }
  }
}
```
  - type: drupal-recipe signals this is a recipe.
  - drupal/default_content is required to handle content importing.
  - This ensures Composer downloads the required modules when the recipe is added.

[⬆️ Back to Top](#top)

# Create the Recipe YML Files

We set up the `my_recipe.yml` file and the `my_recipe.info.yml` file for our recipe. The key differences are the Functional role of `my_recipe.yml` defining the actions and configuration the recipe applies vs the Metadata role of `my_recipe.info.yml` for Drupal's system to recognize the recipe. Both files are typically required for a recipe to work properly. Without `my_recipe.yml`, the recipe has no instructions to execute. Without `my_recipe.info.yml`, Drupal won't recognize the recipe.

4. **Create the `my_recipe.yml` File**:
   - This file defines the recipe’s metadata and instructions. At a minimum, it should include:
     - `name`: A human-readable name for the recipe.
     - `description`: A brief explanation of the recipe’s purpose.
     - `type`: Categorizes the recipe (e.g., `Site`, `Content type`, `Feature`).
     - `install`: Lists modules or themes to install.
     - `config`: Specifies configurations to import or actions to perform.
     - `recipes`: Lists dependent recipes, if any.

   Example `recipe.yml` for a simple blog recipe:
   ```yaml
   name: 'Blog Feature'
   description: 'Sets up a blog with a content type and sample content.'
   type: 'Feature'
   recipes:
     - core/recipes/administrator_role
   install:
     - node
     - pathauto
     - metatag
   config:
     import:
       node:
         - node.type.blog
       pathauto:
         - pathauto.pattern.blog
     actions:
       user.role.administrator:
         grantPermissions:
           - 'create blog content'
           - 'edit own blog content'
   ```
   - This example installs the `node`, `pathauto`, and `metatag` modules, imports a blog content type configuration, and grants permissions to the administrator role.

You create the `my_recipe.info.yml` file like this:
```yml
name: 'My Recipe'
type: recipe
description: 'A recipe to set up a basic blog feature.'
core_version_requirement: ^11
dependencies:
  - drupal:node
  - drupal:views
```
---

[⬆️ Back to Top](#top)

# How to Handle UUIDs in Recipes

<details>
<summary>Why UUIDs Can Cause Conflicts (Click to Open)</summary>

- **UUIDs in Drupal Configuration**:
  - Drupal’s configuration management system assigns Universally Unique Identifiers (UUIDs) to configuration entities (e.g., content types, fields, or roles) to track them across environments.
  - When a recipe includes configuration files (e.g., `node.type.blog.yml`) with a UUID, applying that recipe to a new site could conflict if the site’s database already contains a configuration entity with the same UUID or if the configuration name (e.g., `node.type.blog`) already exists but with a different UUID.

- **Potential Issues**:
  - If the UUID in the recipe’s configuration file matches an existing UUID in the site’s database but the configuration differs, Drupal may throw an error or overwrite the existing configuration.
  - If the configuration name exists but the UUID differs, Drupal’s configuration import system may fail due to a mismatch, as it expects the UUIDs to align.
  - In a fresh site, if the recipe’s configuration is imported without proper handling, the UUIDs may be applied as-is, which could cause problems if the same recipe is applied to multiple sites or combined with other configurations.

</details>


To avoid UUID-related conflicts when creating and installing a Drupal recipe in Drupal 11, follow these best practices:

5. **Omit UUIDs in any (Optional) Recipe Configuration Files**:
   - When crafting configuration files for a recipe (e.g., `config/node.type.blog.yml`), **remove the `uuid` key** from the YML files. Drupal’s configuration import system will generate a new UUID automatically when the recipe is applied to a site.
   - Example of a `node.type.blog.yml` without a UUID:
     ```yml
     langcode: en
     status: true
     dependencies:
       module:
         - menu_ui
     name: Blog
     type: blog
     description: 'A blog post content type.'
     help: ''
     new_revision: true
     display_submitted: true
     menu_ui:
       available_menus:
         - main
       parent: 'main:'
     ```
   - By omitting the UUID, Drupal creates a new one specific to the target site, preventing conflicts.
   - Place these files in the `config` directory, and reference them in the `recipe.yml` under `config.import`.

6. **Ensure Configuration Names Are Unique**:
   - Make sure the configuration names (e.g., `node.type.blog`) used in the recipe are unique or intended to override existing configurations. If a content type like `blog` already exists on the target site, the recipe’s configuration will replace it, which may or may not be desired.
   - To avoid unintended overwrites, consider prefixing configuration names (e.g., `node.type.mycompany_blog`) or checking for existing configurations before applying the recipe.

7. **Use the `force` Option for Overwrites**:
   - If you intentionally want to overwrite existing configurations (e.g., to update an existing content type), you can use the `--force` option when applying the recipe:
     ```bash
     php core/scripts/drupal recipe ../recipes/my_custom_recipe --force
     ```
   - This tells Drupal to ignore UUID mismatches and overwrite the existing configuration. Use this cautiously, as it can lead to data loss if the existing configuration contains customizations not in the recipe.

[⬆️ Back to Top](#top)

# Add Default Content (Optional)

   - Create a `content` directory (e.g., `recipes/my_recipe/content`) to include default content in YML format, leveraging the Default Content API.
   - For example, a file like `node/blog/1.yml` could define a sample blog post.
   - For content in the `content` directory (e.g., `node/blog/1.yml`), UUIDs are also included to uniquely identify entities. Similar to configuration, you can omit UUIDs in content YML files, and Drupal’s Default Content API will generate new ones on import.
   - Example of a content file without a UUID:

     ```yml
     langcode: en
     type: blog
     title: 'Sample Blog Post'
     body:
       value: 'This is a sample blog post created by the recipe.'
       format: basic_html
     status: 1
     ```
   - Alternatively, if UUIDs are included, ensure they are unique or use the `--force` option to overwrite existing content entities.
   - The Default Content API will create this content when the recipe is applied.

[⬆️ Back to Top](#top)

# Version Control the Recipe

   - Store your recipe in a version-controlled repository (e.g., Git) to make it reusable across projects. You can host it on a platform like GitHub or Drupal.org.

[⬆️ Back to Top](#top)

---
# Installing a Drupal Recipe

1. **Prerequisites**:
   - Ensure you have a Drupal 11 site set up. 

   - Make sure you have installed Drush (Drupal’s command-line tool). This Drupal 11 CD/CI with DDEV template automatically puts it in place for your DDEV local environment when you should be doing all your work.
     ```bash
     composer require drush/drush
     ```
   - If using a local recipe, ensure your site’s `composer.json` is configured to recognize the `drupal-recipe` type. Add the following to the `composer.json`:
     ```json
     "extra": {
       "installer-paths": {
         "recipes/{$name}": ["type:drupal-recipe"]
       }
     }
     ```
   - If using Composer Installers Extender for custom recipe types:
     ```bash
     composer require oomphinc/composer-installers-extender
     ```
     Then, add to `composer.json`:
     ```json
     "extra": {
       "installer-types": ["drupal-recipe"]
     }
     ```

2. **Add the Recipe to Your Project**:
   - If the recipe is hosted on a repository (e.g., Packagist, GitHub, or a local path), add it using Composer:
     ```bash
     composer require my_vendor/my_custom_recipe
     ```
   - For local recipes, add a path repository to `composer.json`:
     ```json
     "repositories": [
       {
         "type": "path",
         "url": "recipes/*"
       }
     ]
     ```
     Then require the recipe:
     ```bash
     composer require my_vendor/my_custom_recipe
     ```

3. **Apply the Recipe**:
   - For our DDEV development environment or Lando, use their respective commands:
     - **DDEV**:
       ```bash
       ddev exec -d /var/www/html/web php core/scripts/drupal recipe ../recipes/my_custom_recipe
       ```
     - **Lando**:
       ```bash
       lando recipe-apply my_custom_recipe
       ```
Alternatively you can ...
   - Navigate to your Drupal webroot (e.g., `web` or `docroot`):
     ```bash
     cd web
     ```
   - Apply the recipe using the Drupal core script:
     ```bash
     php core/scripts/drupal recipe ../recipes/my_recipe -v
     ```
     - The `-v` flag provides verbose output for debugging.
     - Replace `../recipes/my_recipe` with the path to your recipe if it’s located elsewhere (e.g., `vendor/my_vendor/my_recipe`).

4. **Clear the Cache**:
   - After applying the recipe, clear the Drupal cache to ensure changes take effect:
     ```bash
     drush cr
     ```

5. **Verify the Application**:
   - Check your Drupal site to confirm that the modules, configurations, and content specified in the recipe have been applied. For example, in the blog recipe above, verify that the blog content type exists and sample content is visible.

6. **Manage Dependencies (Optional)**:
   - To ensure recipe dependencies are added to your project’s `composer.json` for easier maintenance, use the Drupal Recipe Unpack plugin:
     ```bash
     composer require yonas.legesse/drupal-recipe-unpack
     ```
     Then, unpack the recipe’s dependencies:
     ```bash
     composer recipe-unpack my_vendor/my_custom_recipe
     ```
   - This copies the recipe’s dependencies into your project’s `composer.json` and `composer.lock` files.

[⬆️ Back to Top](#top)

<details>
<summary> **What Happens When a Recipe Is Applied** (Click to Open)</summary>
<br>
When you run `php core/scripts/drupal recipe`, Drupal:
1. Installs any modules or themes listed in the `install` section.
2. Imports configuration files from the `config` directory, generating new UUIDs if none are provided or respecting existing UUIDs if present (unless `--force` is used).
3. Imports content from the `content` directory using the Default Content API, similarly handling UUIDs.
4. Executes any `actions` defined in the `recipe.yml` (e.g., granting permissions).

If a UUID conflict occurs (e.g., a `node.type.blog` exists with a different UUID), the import will fail unless `--force` is used or the UUID is omitted.
</details>

[⬆️ Back to Top](#top)

---
# Additional Notes and Tips
### **Additional Notes and Tips**

- **UUIDs in Development**:
  - If you’re using copies of an existing Drupal site's configuration YML files from the Config/sync directory (e.g., you did a `drush config:export`) of the database settings of an existing site, the exported YML files will include UUIDs. Before including them in a recipe to leverage all the settings in them configure how you like them, remember to manually remove the `uuid` key unless you specifically need to enforce the same UUID across environments.
  
- **Existing Sites**:
  - If applying a recipe to an existing site with a content type already in it (e.g. a `blog` in the examples through this documentation), you may need to either:
    - Rename the content type in the recipe (e.g., `node.type.mycompany_blog`).
    - Use `--force` to overwrite the existing configuration.
    - Manually delete the existing content type before applying the recipe (`drush config:delete node.type.blog`).

- **Recipe Testing**:
  - Test your recipe on a clean Drupal 11 installation to confirm it applies without errors. Use tools like DDEV or Lando for quick setup:
    ```bash
    ddev start
    ddev drush site:install minimal
    ddev exec php core/scripts/drupal recipe ../recipes/blog_feature
    ```

- **Drupal Recipe Limitations**:
  - Recipes are designed for initial setup and don’t handle ongoing configuration updates. If you need to update configurations later, consider using configuration management (`drush config:import`) and remember to also use the Configuration Split settings to uniquely leverage certain capabilities only appropriate to a given environment (e.g. testing in 'staged', analytics and mail only in production 'main', etc.).


- **Use the Recipe Generator**:
  - The `recipe_generator` Drush add-on simplifies recipe creation by generating `recipe.yml` and `composer.json` files interactively:
    ```bash
    composer require drupal/recipe_generator:^2.0
    drush recipe-generate
    ```
    This creates a recipe in `recipes/custom` based on your input.[](https://www.drupal.org/project/recipe_generator)

- **Composability**:
  - Recipes can depend on other recipes, allowing you to build modular, reusable configurations. For example, a “Company Site” recipe might include a “Blog” recipe and an “SEO” recipe.

- **Best Practices**:
  - Keep recipes atomic and focused on specific functionality to maximize reusability.
  - Avoid including theme-specific configurations in widely shared recipes, as they may not apply to all sites.[](https://project.pages.drupalcode.org/distributions_recipes/recipe.html)
  - Test recipes on a minimal install profile to avoid configuration conflicts.[](https://project.pages.drupalcode.org/distributions_recipes/getting_started.html)

- **Limitations**:
  - Recipes are applied once and do not remain active, so subsequent updates must be managed manually or via additional recipes.[](https://digitalprojex.com/en/blog/recipes-new-concept-drupal-10)
  - They cannot include custom PHP code, hooks, or plugins; use modules for such functionality.[](https://www.specbee.com/blogs/cooking-irresistible-drupal-websites-with-recipes)

- **Community Resources**:
  - Explore existing recipes on Drupal.org or repositories like `kevinquillen/drupal-base` or `kanopi/saplings` for inspiration.[](https://www.drupal.org/docs/extending-drupal/contributed-modules/contributed-module-documentation/distributions-and-recipes-initiative/recipes-cookbook)
  - Join the `#recipes` channel on Drupal Slack for community support.[](https://www.drupal.org/about/starshot/initiatives/recipes)

[⬆️ Back to Top](#top)

---
# References
This process leverages Drupal 11’s Recipe API, which is stable and functional as of version 11.1. For further details, consult the official Drupal Recipes documentation on Drupal.org or the Distributions and Recipes Initiative page.[](https://opensenselabs.com/blog/drupal-recipe-module)[](https://www.drupal.org/docs/extending-drupal/drupal-recipes/how-to-download-and-apply-drupal-recipes)[](https://www.drupal.org/project/distributions_recipes)

[Back to Top](#top)


4. **Test on a Fresh Site**:
   - When developing a recipe, test it on a fresh Drupal 11 installation to ensure it applies cleanly without UUID conflicts. A fresh site won’t have pre-existing configurations, so Drupal will assign new UUIDs to imported configurations.
   - Example command to create a fresh site:
     ```bash
     composer create-project drupal/recommended-project:11.x my_site
     ```

5. **Leverage Configuration Dependencies**:
   - Ensure that your recipe’s `recipe.yml` specifies all necessary dependencies under the `dependencies` key in configuration files or the `install` section. This helps Drupal resolve dependencies correctly during import, reducing the risk of conflicts.
   - Example from the `recipe.yml`:
     ```yml
     install:
       - node
       - pathauto
     config:
       import:
         node:
           - node.type.blog
     ```
