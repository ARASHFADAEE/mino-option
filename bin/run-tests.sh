#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -x vendor/bin/phpunit ]]; then
  exec vendor/bin/phpunit --configuration phpunit.xml.dist "$@"
fi

if [[ -f /tmp/phpunit-9.phar ]]; then
  exec php /tmp/phpunit-9.phar --configuration phpunit.xml.dist "$@"
fi

echo "PHPUnit not found. Install via Composer or download:" >&2
echo "  curl -L -o /tmp/phpunit-9.phar https://phar.phpunit.de/phpunit-9.phar" >&2
exit 1
