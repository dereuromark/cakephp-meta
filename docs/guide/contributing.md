# Contributing

At the repository root, install development dependencies and run:

```bash
composer install
composer test
composer cs-check
composer stan-setup
composer stan
```

Use `composer cs-fix` to apply coding standard fixes. Add a regression test when
changing helper output, then verify the supported CakePHP and PHP combinations
in CI.

For documentation changes:

```bash
cd docs
npm ci
npm audit --audit-level=low
npm run docs:build
```

Use `npm run docs:dev` for a local preview. Check example syntax, internal links,
light and dark themes, and mobile navigation before submitting a PR.
