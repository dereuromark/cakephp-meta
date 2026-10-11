# Installation

From your CakePHP application directory, install the package:

```bash
composer require dereuromark/cakephp-meta:dev-cake6
```

Composer checks the supported PHP and CakePHP versions. The current constraints
are in [composer.json](https://github.com/dereuromark/cakephp-meta/blob/cake6/composer.json).

Load the plugin:

```bash
bin/cake plugin load Meta
```

Alternatively, add `$this->addPlugin('Meta');` in `src/Application.php` inside
`bootstrap()`, after `parent::bootstrap()`. Use one registration method.

The plugin provides a view helper. It has no database tables, migrations,
controllers, routes, or authentication setup. You can supply metadata from your
own database records through view variables.

Continue with [Getting started](./getting-started).

For this development branch, the application's root `composer.json` must allow development dependencies with `"minimum-stability": "dev"`. Set `"prefer-stable": true` to prefer stable packages when their constraints permit it.
