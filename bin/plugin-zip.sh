#!/bin/bash
#
# Creates a distributable ZIP of the Flipbook Catalog plugin.
# Output: flipbook-catalog.zip in the project root.
#
# Usage:
#   ./bin/plugin-zip.sh
#   composer plugin-zip

set -e

PLUGIN_SLUG="flipbook-catalog"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${PROJECT_ROOT}/build"
ZIP_PATH="${PROJECT_ROOT}/${PLUGIN_SLUG}.zip"

# Clean up any previous build.
rm -rf "${BUILD_DIR}"
rm -f "${ZIP_PATH}"

# Copy distributable files into build/flipbook-catalog/.
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

rsync -a \
    --exclude='.git' \
    --exclude='.gitignore' \
    --exclude='.DS_Store' \
    --exclude='.claude' \
    --exclude='bin' \
    --exclude='build' \
    --exclude='CLAUDE.md' \
    --exclude='README.md' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    --exclude='phpcs.xml' \
    --exclude='vendor' \
    --exclude='docs' \
    --exclude='*.zip' \
    "${PROJECT_ROOT}/" "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Create the ZIP from inside the build directory so the archive
# contains a top-level flipbook-catalog/ folder.
cd "${BUILD_DIR}"
zip -r "${ZIP_PATH}" "${PLUGIN_SLUG}/"

# Clean up build directory.
rm -rf "${BUILD_DIR}"

echo "Created: ${ZIP_PATH}"
