SHELL := /usr/bin/env bash

########################################################
## project_yours        resets base template          ##
## project name, slogan, and email                    ##
########################################################


.PHONY: project_yours
# Define variables for paths and files
CONFIG_DIR := config/sync
SOURCE_FILE := my-system.site.yml
TARGET_FILE := system.site.yml

# Main target with dependencies and error checking
project_yours: check_dir
	@echo "Updating system.site.yml configuration..."
	@rm -f $(CONFIG_DIR)/$(TARGET_FILE) || { echo "Failed to remove old config"; exit 1; }
	@cp $(CONFIG_DIR)/$(SOURCE_FILE) $(CONFIG_DIR)/$(TARGET_FILE) || { echo "Failed to copy config"; exit 1; }
	@echo "Configuration updated successfully"

# Check if config directory exists
check_dir:
	@if [ ! -d "$(CONFIG_DIR)" ]; then \
		echo "Error: Directory $(CONFIG_DIR) not found"; \
		exit 1; \
	fi
	@if [ ! -f "$(CONFIG_DIR)/$(SOURCE_FILE)" ]; then \
		echo "Error: Source file $(SOURCE_FILE) not found"; \
		exit 1; \
	fi



########################################################
## update_project        keeps caches enabled         ##
## development_project   sets caches to disabled      ##
########################################################


.PHONY: update_project

update_project:
	# Change to web/sites/default and remove settings.local.php
	cd $(PWD)/web/sites/default && rm -f settings.local.php
	# Check if source file exists, then copy it
	@test -f $(PWD)/web/sites/my-update.settings.local.php || (echo "Error: my-update.settings.local.php not found" && exit 1)
	cp $(PWD)/web/sites/my-update.settings.local.php $(PWD)/web/sites/default/settings.local.php




########################################################
## development_project   sets settings.local.php to   ##
## disable render, page, & dyanamic page caches plus  ##
## is is set to call my-development.services.yml      ##
########################################################

	
.PHONY: development_project

development_project:
	# Change to web/sites/default and remove settings.local.php
	cd $(PWD)/web/sites/default && rm -f settings.local.php
	# Check if source file exists, then copy it
	@test -f $(PWD)/web/sites/my-example.settings.local.php || (echo "Error: my-example.settings.local.php not found" && exit 1)
	cp $(PWD)/web/sites/my-example.settings.local.php $(PWD)/web/sites/default/settings.local.php