# Social metadata

Use `_meta` view variables or global `Meta` configuration for prefixed social
tags. Open Graph properties use the full key in `property`; Twitter Card fields
use the full key in `name`.

```php
$this->set('_meta', [
    'og:title' => 'Our latest update',
    'og:type' => 'article',
    'og:url' => 'https://example.com/news/update',
    'og:image' => 'https://example.com/img/update.jpg',
    'og:description' => 'A description of the update.',
    'twitter:card' => 'summary_large_image',
    'twitter:title' => 'Our latest update',
    'twitter:description' => 'A description of the update.',
    'twitter:image' => 'https://example.com/img/update.jpg',
]);
```

The layout's `out()` call includes these tags. For example, a `twitter:card`
entry becomes `<meta name="twitter:card" content="summary_large_image">`.

The helper outputs configured values; it does not choose images, convert
relative social image URLs, validate provider requirements, or guarantee a
particular preview. Supply public absolute URLs and test the rendered page.
The [Open Graph specification](https://ogp.me/) describes its required
properties and image metadata.
