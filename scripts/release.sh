#!/usr/bin/env bash
# Releases every package whose folder changed since its last tag, then mirrors each package
# to its read-only split repository, which Packagist reads.
#
# DRY_RUN=1 commits and tags locally, then prints what it would push instead of pushing.
set -euo pipefail

PACKAGES='core shopware'
DRY_RUN=${DRY_RUN:-0}

cliff() {
    package=$1
    shift
    # Tests and chore/test/style commits don't release a package.
    GIT_CLIFF__BUMP__INITIAL_TAG="varify-io/$package@0.1.0" git-cliff \
        --include-path "packages/$package/**" --exclude-path "packages/$package/tests/**" \
        --tag-pattern "^varify-io/$package@" "$@"
}

split_remote() {
    case $1 in
        core) echo 'git@github.com:varify-io/sdk-php-core.git' ;;
        shopware) echo 'git@github.com:varify-io/sdk-php-shopware.git' ;;
    esac
}

split_key() {
    case $1 in
        core) printf '%s\n' "${SPLIT_DEPLOY_KEY_CORE:-}" ;;
        shopware) printf '%s\n' "${SPLIT_DEPLOY_KEY_SHOPWARE:-}" ;;
    esac
}

mkdir -p .tools/release
trap 'rm -f .tools/release/*.key' EXIT
tags=''

for package in $PACKAGES; do
    unreleased=$(cliff "$package" --unreleased --context | jq '[.[].commits[]] | length')
    if [ "$unreleased" -eq 0 ]; then
        echo "varify-io/$package: nothing to release"
        continue
    fi

    next=$(cliff "$package" --bumped-version)
    tag="varify-io/$package@${next##*@}"
    cliff "$package" --unreleased --tag "$tag" --strip all > ".tools/release/$package.md"
    cliff "$package" --tag "$tag" --output "packages/$package/CHANGELOG.md"
    tags="$tags $tag"
    echo "$tag"
done

if [ -n "$tags" ]; then
    git add packages/*/CHANGELOG.md
    git commit --quiet --message 'chore(release): publish [skip ci]'
    for tag in $tags; do
        git tag "$tag"
    done
fi

if [ "$DRY_RUN" = 1 ]; then
    echo "dry run: would push HEAD to main with:$tags"
else
    # shellcheck disable=SC2086
    [ -z "$tags" ] || git push --atomic origin HEAD:main $tags
fi

for tag in $tags; do
    package=${tag#varify-io/}
    package=${package%@*}
    if [ "$DRY_RUN" = 1 ]; then
        printf 'dry run: would create GitHub release %s:\n%s\n' "$tag" "$(cat ".tools/release/$package.md")"
        continue
    fi
    gh release create "$tag" --verify-tag --title "$tag" --notes-file ".tools/release/$package.md"
done

# Runs on every release job, not only after a new tag, so a failed mirror catches up next time.
for package in $PACKAGES; do
    branch=$(git subtree split --quiet --prefix "packages/$package" HEAD)
    refs="$branch:refs/heads/main"
    latest=$(git describe --tags --abbrev=0 --match "varify-io/$package@*" HEAD 2> /dev/null || true)
    if [ -n "$latest" ]; then
        refs="$refs $(git subtree split --quiet --prefix "packages/$package" "$latest"):refs/tags/v${latest##*@}"
    fi

    if [ "$DRY_RUN" = 1 ]; then
        echo "dry run: would push to $(split_remote "$package"): $refs"
        continue
    fi

    key=.tools/release/$package.key
    (umask 077 && split_key "$package" > "$key")
    # shellcheck disable=SC2086
    GIT_SSH_COMMAND="ssh -i $key -o IdentitiesOnly=yes -o UserKnownHostsFile=$PWD/scripts/github_known_hosts" \
        git push "$(split_remote "$package")" $refs
done
