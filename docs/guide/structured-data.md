# Structured data

The helper can output JSON-LD for breadcrumbs, articles, and organizations.
Set the data before the layout calls `out()`.

## Breadcrumbs

```php
$this->Meta->setBreadcrumbs([
    ['name' => 'Home', 'url' => 'https://example.com/'],
    ['name' => 'News', 'url' => ['controller' => 'News', 'action' => 'index']],
    ['name' => 'Latest update'],
]);
```

The list must contain at least one item. Every item requires a string `name`;
`url` is optional and accepts a string or a CakePHP URL array. Route arrays are
built with a full base URL. String URLs are retained as supplied. Positions
start at one in iteration order, regardless of the input's numeric keys.

## Article

```php
$this->Meta->setArticle([
    'headline' => 'Our latest update',
    'author' => 'Example author',
    'datePublished' => '2026-01-15T09:00:00+00:00',
    'dateModified' => '2026-01-16T12:00:00+00:00',
    'image' => 'https://example.com/img/update.jpg',
    'description' => 'A description of the update.',
]);
```

`headline` must be a string. A string `author` becomes a `Person` object;
you can also supply an author array. The other supported keys are
`datePublished`, `dateModified`, `image`, and `description`. Supply formatted
date strings and suitable schema values; the helper does not format date objects
or validate the full article schema.

## Organization

Merge defaults into `config/app.php`:

```php
'Meta' => [
    'organization' => [
        'name' => 'Example organization',
        'url' => 'https://example.com',
        'logo' => 'https://example.com/img/logo.png',
    ],
],
```

Then activate the organization data from a template:

```php
$this->Meta->setOrganization([]);
```

An explicit value replaces a configured value. The merged data requires a string
`name`. Supported optional keys are `url`, `logo`, `contactPoint`, and `sameAs`.
Configuration alone does not activate JSON-LD output.

## Output and validation

`out()` appends a script for each activated schema. Alternatively, output the
individual `getBreadcrumbs()`, `getArticle()`, and `getOrganization()` results;
they return `null` until their setter has been called.

The helper adds `@context: https://schema.org`, escapes characters that could
terminate the script, and pretty-prints JSON when `debug` is enabled. Encoding
failures raise `JsonException`. Missing required helper fields raise
`InvalidArgumentException`.

This validates the helper's required fields, not every schema rule. Check the
result against [Schema.org](https://schema.org/) and your search provider's
requirements before relying on it for search presentation.
