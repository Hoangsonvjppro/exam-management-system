#!/usr/bin/env bash

set -euo pipefail

target_dir="resources/views"
pattern='x-data="\s*\{'

echo "Checking for inline Alpine x-data object literals in ${target_dir}..."

if command -v rg >/dev/null 2>&1; then
  if rg -n "$pattern" "$target_dir"; then
    echo ""
    echo "ERROR: Found inline Alpine x-data object literals."
    echo "Use function-based x-data factories instead (e.g. x-data=\"modalState(...)\")."
    exit 1
  fi
else
  if grep -RInE "$pattern" "$target_dir"; then
    echo ""
    echo "ERROR: Found inline Alpine x-data object literals."
    echo "Use function-based x-data factories instead (e.g. x-data=\"modalState(...)\")."
    exit 1
  fi
fi

echo "OK: No inline Alpine x-data object literals found."
