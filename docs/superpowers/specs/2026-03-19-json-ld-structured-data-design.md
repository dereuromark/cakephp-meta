# JSON-LD Structured Data Support

## Overview

Add JSON-LD structured data generation to MetaHelper for Schema.org markup. Initial implementation covers three schema types: BreadcrumbList, Article, and Organization.

## Scope

### In Scope (Phase 1)
- BreadcrumbList schema
- Article schema
- Organization schema
- Required field validation
- Integration with existing `out()` method
- Global config support for Organization

### Out of Scope (Future)
- Product schema
- FAQ schema
- LocalBusiness schema
- Full schema validation against Schema.org specs

## API Design

### Methods

Each schema type follows the existing helper pattern with `set`/`get` methods:

```php
// Breadcrumbs
public function setBreadcrumbs(array $items): void
public function getBreadcrumbs(): ?string

// Article
public function setArticle(array $data): void
public function getArticle(): ?string

// Organization
public function setOrganization(array $data): void
public function getOrganization(): ?string
```

### Breadcrumbs

```php
$this->Meta->setBreadcrumbs([
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Blog', 'url' => '/blog'],
    ['name' => 'My Post'],  // Last item, no URL (current page)
]);
```

**Required fields per item:** `name` (string)
**Optional fields per item:** `url` (string or CakePHP URL array)

Generated schema:
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "https://example.com/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Blog",
      "item": "https://example.com/blog"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "My Post"
    }
  ]
}
```

### Article

```php
$this->Meta->setArticle([
    'headline' => 'How to Use JSON-LD',
    'author' => 'John Doe',
    'datePublished' => '2026-03-19',
    'dateModified' => '2026-03-19',
    'image' => 'https://example.com/image.jpg',
    'description' => 'A guide to structured data',
]);
```

**Required fields:** `headline` (string)
**Optional fields:** `author` (string or array), `datePublished`, `dateModified`, `image` (string or array), `description`

Generated schema:
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "How to Use JSON-LD",
  "author": {
    "@type": "Person",
    "name": "John Doe"
  },
  "datePublished": "2026-03-19",
  "dateModified": "2026-03-19",
  "image": "https://example.com/image.jpg",
  "description": "A guide to structured data"
}
```

### Organization

```php
$this->Meta->setOrganization([
    'name' => 'Acme Inc',
    'url' => 'https://acme.com',
    'logo' => 'https://acme.com/logo.png',
    'sameAs' => [
        'https://twitter.com/acme',
        'https://facebook.com/acme',
    ],
]);
```

**Required fields:** `name` (string)
**Optional fields:** `url`, `logo`, `contactPoint` (array), `sameAs` (array of URLs)

Generated schema:
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Acme Inc",
  "url": "https://acme.com",
  "logo": "https://acme.com/logo.png",
  "sameAs": [
    "https://twitter.com/acme",
    "https://facebook.com/acme"
  ]
}
```

## Output Integration

### Modification to `out()`

JSON-LD scripts are appended to the existing `out()` method output:

```php
public function out(?string $header = null, array $options = []): string
{
    // ... existing meta tag rendering ...

    // Append JSON-LD scripts
    $out .= $this->getBreadcrumbs();
    $out .= $this->getArticle();
    $out .= $this->getOrganization();

    return $out;
}
```

### Internal Rendering

Private method handles the script wrapper with conditional pretty-printing:

```php
private function renderJsonLd(array $data): string
{
    $data['@context'] = 'https://schema.org';

    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if (Configure::read('debug')) {
        $flags |= JSON_PRETTY_PRINT;
    }

    return '<script type="application/ld+json">' . json_encode($data, $flags) . '</script>';
}
```

- `JSON_UNESCAPED_SLASHES` — cleaner URLs
- `JSON_UNESCAPED_UNICODE` — proper international characters
- `JSON_PRETTY_PRINT` — only in debug mode for readable source

### Storage

New protected property:

```php
protected array $_jsonLd = [
    'breadcrumbs' => null,
    'article' => null,
    'organization' => null,
];
```

## Validation

### Required Field Validation

Each setter validates required fields and throws `InvalidArgumentException`:

```php
public function setBreadcrumbs(array $items): void
{
    if (empty($items)) {
        throw new InvalidArgumentException('Breadcrumbs require at least one item.');
    }
    foreach ($items as $i => $item) {
        if (!isset($item['name']) || !is_string($item['name'])) {
            throw new InvalidArgumentException("Breadcrumb item {$i} requires a 'name' string.");
        }
    }
    // ... build schema ...
}

public function setArticle(array $data): void
{
    if (!isset($data['headline']) || !is_string($data['headline'])) {
        throw new InvalidArgumentException("Article requires a 'headline' string.");
    }
    // ... build schema ...
}

public function setOrganization(array $data): void
{
    if (!isset($data['name']) || !is_string($data['name'])) {
        throw new InvalidArgumentException("Organization requires a 'name' string.");
    }
    // ... build schema ...
}
```

### URL Handling

URLs can be strings or CakePHP URL arrays, converted to absolute URLs:

```php
if (isset($item['url'])) {
    $url = is_array($item['url'])
        ? $this->Url->build($item['url'], ['fullBase' => true])
        : $item['url'];
}
```

## Configuration

### Organization Global Defaults

Organization can be configured globally in `config/app.php`:

```php
'Meta' => [
    'organization' => [
        'name' => 'Acme Inc',
        'url' => 'https://acme.com',
        'logo' => 'https://acme.com/logo.png',
        'sameAs' => [
            'https://twitter.com/acme',
            'https://facebook.com/acme',
        ],
    ],
],
```

### Merging Behavior

`setOrganization()` merges page-level data with global config:

```php
public function setOrganization(array $data): void
{
    $data = array_merge($this->_config['organization'] ?? [], $data);
    // ... validation and build ...
}
```

Per-page overrides:
```php
$this->Meta->setOrganization(['name' => 'Acme Blog Division']);
// Inherits url, logo, sameAs from global config
```

### Breadcrumbs and Article

No global config support — always set explicitly per-page.

## Testing

Tests follow the existing `MetaHelperTest.php` patterns:

### Test Cases

1. **testSetBreadcrumbs** — Valid breadcrumb list with mixed URL types
2. **testSetBreadcrumbsEmpty** — Exception for empty array
3. **testSetBreadcrumbsMissingName** — Exception for item without name
4. **testSetArticle** — Valid article with all optional fields
5. **testSetArticleMissingHeadline** — Exception for missing headline
6. **testSetArticleMinimal** — Article with only headline
7. **testSetOrganization** — Valid organization with all fields
8. **testSetOrganizationMissingName** — Exception for missing name
9. **testSetOrganizationFromConfig** — Global config loading
10. **testSetOrganizationConfigMerge** — Per-page override merging
11. **testOutIncludesJsonLd** — JSON-LD appears in out() output
12. **testJsonLdDebugPrettyPrint** — Pretty print only in debug mode

### Test Pattern

```php
public function testSetBreadcrumbs(): void
{
    $this->Meta->setBreadcrumbs([
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Blog', 'url' => ['controller' => 'Posts', 'action' => 'index']],
        ['name' => 'My Post'],
    ]);

    $result = $this->Meta->getBreadcrumbs();
    $this->assertStringContainsString('"@type":"BreadcrumbList"', $result);
    $this->assertStringContainsString('"name":"Home"', $result);
}

public function testSetBreadcrumbsEmpty(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Breadcrumbs require at least one item.');
    $this->Meta->setBreadcrumbs([]);
}
```

## File Changes

### Modified Files
- `src/View/Helper/MetaHelper.php` — Add JSON-LD methods and properties
- `tests/TestCase/View/Helper/MetaHelperTest.php` — Add test cases

### Documentation Updates
- `docs/MetaHelper.md` — Add JSON-LD usage section
