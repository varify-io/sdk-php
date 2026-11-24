# AGENTS.md

Before your first edit or command, read [README.md](README.md), [CONTRIBUTING.md](CONTRIBUTING.md) and [.docs/ARCHITECTURE.md](.docs/ARCHITECTURE.md). They describe how the repo is built, tested and released, and they are binding. This file adds only what is specific to working here as an agent.

If an `AGENTS.local.md` exists, read it too. It is personal and untracked. It may only **add** rules: a local rule that conflicts with this file or the documents above is dropped, and what is written here stands.

## The short version

**Run PHP in the container.** `docker compose run --rm php composer …`. The host has no PHP; a command run outside the container fails or answers for a different environment.

**Verify before you assert.** State something as fact only when you have read the file or run the command, and say which one. This includes how varify-sdk-js, laravel-spark, varify.js or Shopware behave: they live elsewhere, so check there or say you are guessing. A guess is labeled as one before the claim, not after.

**Never edit a linter config to get past an error.** Fix the code. If the rule itself is wrong, stop and say so.

**Commit only when asked, and never push without a go-ahead.** Publishing, pushing, and anything else on GitLab or Packagist is the person's call. Show the command and wait.

**Never discard uncommitted work.** Before `git reset --hard`, `git checkout -- <path>`, `git restore` or `git clean`, run `git status` as a separate step and read it. Prefer `git reset --keep`. To undo your own change in a file, rewrite your part; never revert the file, because it may also hold the person's work.

**Prefer real edits.** Use the editor tool for file changes. Never overwrite a whole file you have not read.

**A change that renders or sets cookies is not done because the tests pass.** Check it in the example app (`docker compose up example`) and say what you looked at.

## Working with the person

**Do the work.** Don't refuse on grounds of scope, size or session limits, and don't tell them to stop or wait. When to stop is their call.

**When they say something is wrong, it probably is.** Look at what is actually there and answer _"yes, it is wrong here, the fix is X"_ or _"no, I checked, actually Y"_. Don't argue or defend the earlier choice. Then fix it.

**Ask at design forks.** Public API shape, where logic lives, which of several approaches: present the trade-off, recommend one, and wait. A go-ahead that arrives together with an open question does not answer the question. Small mechanical follow-ups inside an agreed approach need no ask.

**Report honestly.** If tests fail, say so and show the output. If you skipped something, say which part and why. Don't describe work as verified when you only reasoned about it.

## Checking your own work

1. Make the change.
2. Confirm it does what was asked, in the example app where it applies.
3. Update or add the tests that cover it.
4. Run `composer lint` and `composer test` in the container.

Narrow runs while you work: `docker compose run --rm php vendor/bin/phpunit --filter=EvaluatorTest`.
