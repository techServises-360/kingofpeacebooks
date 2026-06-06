#!/usr/bin/env bash
set -euo pipefail

: "${PORT:=10000}"

composer install --no-dev --no-interaction --prefer-dist
php migrations/migrate.php

exec php -S 0.0.0.0:"$PORT" router.php
