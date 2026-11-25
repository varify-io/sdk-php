# Shopware example shop

A local Shopware 6.7.10.2 shop (the first customer's version) with `varify-io/shopware` from this repository and a demo plugin that shows experiment 269 on the homepage. It runs in prod mode with Shopware's HTTP cache on, because caching is where server-side experiments break.

Built on Shopware's Docker setup (`ghcr.io/shopware/docker-dev`); only Docker is needed on the host.

## Setup

```sh
cp .env.example .env      # optional: setup.sh does it when missing
./setup.sh                # creates ./project (git-ignored), installs Shopware and both plugins
```

Storefront: http://localhost:8001 · Admin: http://localhost:8001/admin (admin / shopware). The ports avoid 8000 and 8080, which laravel-spark and the plain-PHP example use.

`.env` points at a local laravel-spark (`task artisan -- storage:link` there); empty `VARIFY_CONFIG_URL` reads from `https://delivery.varify.io`.

- `packages/` is mounted into the web container and installed through Composer path repositories, so changes to the core or the plugin apply after `docker compose exec web bin/console cache:clear` in `project/`.
- `VarifyDemo/` is mounted as `custom/plugins/VarifyDemo`.
- Shopware 6.7.10.2 pins `dompdf/dompdf` 3.1.4, which has security advisories, so Composer refuses it by default. `setup.sh` ignores them for dompdf in this local shop only.

Stop with `make stop`, remove everything with `make down` and `docker volume rm` of the database volume, all in `project/`.

## What to check

Use a browser, or `curl` with a cookie jar and a browser user agent. Shopware's internal cache marks a hit with an `Age` above 0.

- [ ] The first visit sets `varify` and `sw-cache-hash` and is sent with `Cache-Control: no-store, private` (never cached).
- [ ] The next page view of the same visitor comes from the cache (`Age` > 0) with the same variation.
- [ ] A visitor in the other variation never gets that copy; a different visitor with the same assignments does.
- [ ] `?varify-force=269-311` and `?varify-force=269-0` switch the variation and stick on later pages.
- [ ] A bot user agent (`Googlebot/2.1`) gets the original, no `varify` cookie and no tracking snippet.
- [ ] The page contains the tracking snippet before `</body>`; with varify.js on the page, the exposure is sent.
