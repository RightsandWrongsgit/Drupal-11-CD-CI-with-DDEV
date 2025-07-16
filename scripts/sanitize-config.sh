#!/usr/bin/env bash
set -euo pipefail

CONFIG_DIR="${1:-/app/private/config/sync}"

echo "🔧 Sanitizing UUIDs and _core sections in: $CONFIG_DIR"

if [ ! -d "$CONFIG_DIR" ]; then
  echo "⚠️ Directory $CONFIG_DIR does not exist. Skipping sanitation."
  exit 0
fi

# Remove `uuid:` lines
find "$CONFIG_DIR" -type f -name "*.yml" -print0 | while IFS= read -r -d '' file; do
  sed -i '/^uuid: /d' "$file"
done

# Remove `_core:` and its indented block (until the next top-level key)
find "$CONFIG_DIR" -type f -name "*.yml" -print0 | while IFS= read -r -d '' file; do
  awk '
    BEGIN { skip = 0 }
    /^_core:$/ { skip = 1; next }
    /^[^[:space:]]/ { skip = 0 }
    !skip { print }
  ' "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
done

echo "✅ Config sanitized."
