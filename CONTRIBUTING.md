# Contributing

How the packages fit together is in [.docs/ARCHITECTURE.md](.docs/ARCHITECTURE.md).

## Run the checks the way CI does

Everything runs in the `php` container:

```sh
docker compose run --rm php composer lint   # validate, normalize, Rector, PHP-CS-Fixer, PHPCS, PHPStan
docker compose run --rm php composer test
docker compose run --rm php composer audit --no-dev   # blocks a release; plain composer audit also runs, without blocking
```

CI runs the tests on PHP 8.2, 8.3, 8.4 and 8.5. It also runs betterleaks over the git history and a Trivy secret scan. A pull request can only merge with green checks.

## Rules that are not up for negotiation

**Never edit a linter config to make an error go away.** Not `phpstan.neon`, `rector.php`, `phpcs.xml.dist` or `.php-cs-fixer.dist.php`. Fix the code. If a rule is wrong for this codebase, change it deliberately in its own commit.

**Never silence PHPStan with `@phpstan-ignore`.**

**The SDK never breaks the customer's page.** Nothing in a render path throws because of Varify. A failure (config, cache, cookie, unknown experiment) ends in the untracked original and, where there is one, a call to `onError`.

**`varify-io/core` depends on nothing but `psr/simple-cache`** and no framework.

**The bucketing formula and the cookie format don't change.** They are shared with varify-sdk-js; changing either moves visitors between variations of running experiments. `BucketTest` and `CookieCodecTest` pin them.

**PHP 8.2 is the floor.** No typed class constants, `#[\Override]` or other 8.3+ syntax in `packages/`.

## Code style in short

- When two pieces don't fit, change the one that is wrong, usually the producer of the bad value, instead of wrapping it in an adapter, cast or guard. Prefer the fix that deletes code. Don't handle cases no current caller produces.
- PHPStan runs at level max. Input from outside the SDK (the config file, cookies, query parameters, superglobals) is `mixed` or `string` until a parser such as `ConfigParser` or `CookieCodec` has turned it into typed objects.
- No nullable return types; return a sentinel (`Config::unavailable()`, `State::anonymous()`, `VisitorId::none()`) instead.
- `final` classes, `readonly` where possible, enums for closed sets. No named arguments.
- The public API takes plain `int` ids (`positive-int` for PHPStan); value objects live inside.
- Public API changes are breaking changes for customers. Add rather than rename.
- No comments that say what the code does. A comment is for: security reasoning, framework behaviour, or an invariant a reader would otherwise "fix" away. Docblocks a tool reads (PHPStan types, array shapes) and docblocks on public API are fine. Never mention callers, sibling files or ordering.

## Tests

PHPUnit 11 (12 needs PHP 8.3), in `packages/*/tests`, one test file per class. Every test class carries `#[CoversClass]` and `#[Group]`; tests use `#[Test]`, camelCase names and `static::assert*()`. Each test reads Arrange, Act, Assert, separated by one blank line, and its name states the behaviour.

For anything that renders or sets cookies, check it in the example app as well: `docker compose up example`.

## Releases

Every push to `main` runs `scripts/release.sh` once the checks pass. Each package has its own version:

- A package is released when files in its folder changed since its last tag (`varify-io/core@0.1.0`). Tests and `chore`/`test`/`style` commits don't count.
- The commit types decide the bump: `fix` a patch, `feat` a minor. Below 1.0 a breaking change bumps the minor, which Composer's `^0.y` treats as breaking.
- git-cliff (`cliff.toml`) writes the package's `CHANGELOG.md`, the job commits it as `chore(release): publish [skip ci]`, tags it and creates a GitHub release.
- Each `packages/*` folder is then pushed to its read-only split repository (`varify-io/sdk-php-core`, `varify-io/sdk-php-shopware`) with a `v0.1.0` tag, which Packagist reads.

When the plugin needs a new core feature that bumped core's minor, raise its `varify-io/core` constraint in the same change.

`DRY_RUN=1 scripts/release.sh` in a scratch clone commits and tags locally and prints what it would push.

### Releasing

A repository admin releases once `develop` is green, then brings the release commit back:

```sh
git push origin develop:main
# after the release job:
git fetch origin && git push origin origin/main:develop
```

Neither direction goes through a pull request: GitHub's rebase merge recreates the commits with new SHAs, and `main` and `develop` would no longer fast-forward into each other. `main` takes only fast-forwards of commits whose checks passed. If something was merged into `develop` while the job ran, the push back is rejected; rebase those commits onto `main` and force-push `develop`.

## Branches

Work on a branch off `develop` and finish it through a pull request into `develop`. `main` is the release branch and only moves as described under [Releasing](#releasing). Never merge into `develop` or `main` locally.

## Commits

Conventional commits. Allowed types: `build`, `chore`, `docs`, `feat`, `fix`, `perf`, `refactor`, `release`, `style`, `test`, `update`. Body lines can be at most 100 characters.

Write the message as the change, nothing else: no tool attribution, no generated-by footers. The body, when there is one, explains _why_; the diff already shows what changed. The same goes for pull request descriptions.
