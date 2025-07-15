#!/bin/bash
set -euo pipefail

echo "🔧 Sanitizing UUIDs and _core sections from config/sync..."

# Strip `uuid:` lines
find config/sync -type f -name "*.yml" -exec sed -i '' '/^uuid: /d' {} +

# Remove `_core:` and its indented block
find config/sync -type f -name "*.yml" -exec sed -i '' '/^_core:$/,/^[^ ]/d' {} +

echo "✅ Config sanitized."
