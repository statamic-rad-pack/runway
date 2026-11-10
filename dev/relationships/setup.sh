#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/../.." && pwd)"
site="$root/dev/relationships/site"

if [[ ! -f "$site/artisan" ]]; then
    composer create-project statamic/statamic "$site" 6.5.3 --no-install --no-scripts --no-interaction
fi

cd "$site"
composer config repositories.runway path ../../..
composer require 'statamic-rad-pack/runway:@dev' --no-scripts --no-interaction

if [[ ! -f .env ]]; then
    cp ../site.env .env
    php artisan key:generate
fi

cp ../config.php config/runway.php
cp ../routes.php routes/web.php
cp ../AppServiceProvider.php app/Providers/AppServiceProvider.php
cp ../bootstrap.php bootstrap/app.php
php artisan package:discover
php artisan vendor:publish --tag=statamic-cp --force
php artisan vendor:publish --tag=runway --force

if [[ -f "$root/dist/hot" ]]; then
    cp "$root/dist/hot" public/vendor/runway/hot
fi

if [[ "${1:-}" == --reset ]]; then
    php ../seed.php
fi

echo "Sandbox ready at $site. Serve with php artisan serve; use your existing Runway Vite server for feature assets."
