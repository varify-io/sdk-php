# varify-sdk-php

PHP SDKs for running Varify experiments on the server: the visitor is assigned while PHP renders the page, instead of in varify.js on the page.

| Package                              | What it is                                                                                     |
| ------------------------------------ | ---------------------------------------------------------------------------------------------- |
| [`varify-io/core`](packages/core)    | Framework-agnostic: loads the delivery file, buckets visitors locally, keeps assignments sticky |
| [`varify-io/shopware`](packages/shopware) | Shopware 6.7 plugin: Twig functions, cookie, HTTP-cache variants, tracking snippet       |

Each package is published to Packagist from a read-only split repository; this monorepo is where they are developed. [`examples/plain-php`](examples/plain-php) uses the core against a real delivery file. The package READMEs are the user documentation.

Design and decisions: [PHP & Shopware SDK v1 plan](https://claude.ai/artifact/JgkbbQ4FhshMyvw5c7aT9V). The JavaScript counterpart is [varify-sdk-js](https://gitlab.com/varify/varify-sdk-js); both SDKs must assign a visitor identically.

## Setup

PHP and Composer run in Docker, so the host needs nothing but Docker.

```sh
docker compose run --rm php composer install
```

`PHP_VERSION=8.4 docker compose build php` builds the image for another PHP version (8.2 is the default and the minimum).

## Scripts

| Command                                           | Does                                                     |
| ------------------------------------------------- | -------------------------------------------------------- |
| `docker compose run --rm php composer test`       | PHPUnit                                                  |
| `docker compose run --rm php composer lint`       | composer validate/normalize, Rector, PHP-CS-Fixer, PHPCS, PHPStan |
| `docker compose run --rm php composer lint:fix`   | Applies what the fixers can fix                          |
| `docker compose run --rm php composer phpstan`    | PHPStan alone                                            |

## Example app

```sh
cp examples/plain-php/.env.example examples/plain-php/.env
docker compose up example   # http://localhost:8080
```

`.env.example` points at a local laravel-spark; drop `VARIFY_CONFIG_URL` to read from `https://delivery.varify.io`. Set `VARIFY_SCRIPT_URL` and `VARIFY_IID` to load varify.js and see exposures tracked.

The Shopware plugin has its own example shop (Shopware 6.7.10.2, prod mode, HTTP cache on): `examples/shopware-app/setup.sh`, see [its README](examples/shopware-app/README.md).

## Contributing

Branches, commits, releases and code rules: [CONTRIBUTING.md](CONTRIBUTING.md). How a request flows through the SDK: [.docs/ARCHITECTURE.md](.docs/ARCHITECTURE.md).
