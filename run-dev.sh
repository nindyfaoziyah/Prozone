#!/bin/bash
# ============================================
# PROZONE - Development Server Launcher (Bash)
# Ensures npm run web runs from correct folder
# ============================================

# Get the script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Function to check if folder is correct root
test_root_folder() {
    local path="$1"
    if [[ -f "$path/package.json" ]]; then
        if grep -q "prozone-1" "$path/package.json"; then
            return 0
        fi
    fi
    return 1
}

# Check if already in correct folder
if test_root_folder "$SCRIPT_DIR"; then
    echo "✓ Already in correct project folder: $SCRIPT_DIR"
    ROOT_DIR="$SCRIPT_DIR"
# Check parent folder
elif test_root_folder "$(dirname "$SCRIPT_DIR")"; then
    ROOT_DIR="$(dirname "$SCRIPT_DIR")"
    echo "✓ Found root folder: $ROOT_DIR"
# Check two levels up
elif test_root_folder "$(dirname "$(dirname "$SCRIPT_DIR")")"; then
    ROOT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"
    echo "✓ Found root folder: $ROOT_DIR"
else
    echo "✗ ERROR: Could not find Prozone root folder!"
    echo ""
    echo "Expected to find package.json with 'prozone-1' in:"
    echo "- Current directory"
    echo "- Parent directory"
    echo "- Two levels up"
    echo ""
    exit 1
fi

# Start server
cd "$ROOT_DIR"
echo ""
echo "============================================"
echo "Starting PROZONE Development Server"
echo "Folder: $ROOT_DIR"
echo "URL: http://localhost:8000"
echo "============================================"
echo ""

npm run web
