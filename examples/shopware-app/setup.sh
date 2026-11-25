#!/usr/bin/env bash
# Creates a Shopware 6.7.10.2 shop in ./project with the Varify plugin from this repository. Needs only Docker.
set -euo pipefail

cd "$(dirname "$0")"

IMAGE=ghcr.io/shopware/docker-dev:php8.3-node24-caddy
SHOPWARE_VERSION=6.7.10.2

if [ ! -f .env ]; then
    cp .env.example .env
fi
# shellcheck source=/dev/null
source .env

in_image() {
    docker run --rm \
        -v "$PWD/project:/var/www/html" \
        -v "$PWD/../../packages:/var/www/varify-packages" \
        -w /var/www/html --entrypoint sh "$IMAGE" -c "$1"
}

if [ ! -f project/composer.lock ]; then
    mkdir -p project
    in_image "composer create-project shopware/production:${SHOPWARE_VERSION} . --no-install --no-interaction"

    # 6.7.10.2 pins dompdf 3.1.4, which has security advisories; Composer refuses it by default. Acceptable for a
    # local test shop that reproduces a customer's version, never for a public one.
    in_image 'composer config --json policy.advisories.ignore "[\"dompdf/dompdf\"]"'

    # The packages are mounted at the same path in the web container, so the symlinks resolve there too.
    in_image 'composer config repositories.varify-core "{\"type\":\"path\",\"url\":\"/var/www/varify-packages/core\",\"options\":{\"symlink\":true,\"versions\":{\"varify-io/core\":\"0.1.0\"}}}"'
    in_image 'composer config repositories.varify-shopware "{\"type\":\"path\",\"url\":\"/var/www/varify-packages/shopware\",\"options\":{\"symlink\":true,\"versions\":{\"varify-io/shopware\":\"0.1.0\"}}}"'

    in_image 'composer install --no-interaction --no-progress'
    in_image 'composer require shopware/docker-dev --no-interaction'
    in_image 'composer remove shopware/elasticsearch --no-interaction --no-scripts && rm -rf var/cache/*'
    in_image 'composer require varify-io/shopware:0.1.0 --no-interaction'
fi

cp compose.override.yaml project/compose.override.yaml
printf 'APP_URL=http://localhost:8001\n' > project/.env.local

cd project
make up
make setup

console() {
    docker compose exec web bin/console "$@"
}

console plugin:refresh
console plugin:install --activate VarifyShopware
console plugin:install --activate VarifyDemo
console system:config:set VarifyShopware.config.teamId "$VARIFY_TEAM_ID"
console system:config:set VarifyShopware.config.configUrl "${VARIFY_CONFIG_URL:-}"
console theme:compile
console cache:clear

echo "Storefront: http://localhost:8001 (admin: http://localhost:8001/admin, admin / shopware)"
