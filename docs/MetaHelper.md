# Meta Helper

## Enabling
You can enable the helper in your AppView class:
```php
$this->loadHelper('Meta.Meta');

// or setting different defaults
$this->loadHelper('Meta.Meta', ['robots' => ['index' => true, 'follow' => true]]);
```

## Configs

- 'title' => null,
- 'charset' => null,
- 'icon' => null,
- 'canonical' => null, // Set to true for auto-detect
- 'language' => null, // Set to true for auto-detect
- 'robots' => ['index' => false, 'follow' => false, 'archive' => false]

and a few more.

You can define your defaults in various places, the lowest is the Configure level in your app.php:
```php
$config = [
	'Meta' => [
		'language' => 'de',
		'robots' => ['index' => true, 'follow' => true]
	]
];
```

You can pass them to the loadHelper() method as shown above.

If you need to customize them per controller or per action you can pass them from the controller to the view or modify them in the view template.

In your controller, you could do the following:
```php
$_meta = [
	'title' => 'Foo Bar',
	'robots' => ['index' => false]
];
$this->set(compact('_meta')));
```

In your view ctp you can also do:
```php
$this->Meta->setKeywords('I, am, English', 'en');
$this->Meta->setKeywords('Ich, bin, deutsch', 'de');
$this->Meta->setDescription('Foo Bar');
$this->Meta->setRobots(['index' => false]);
```
All this data will be collected it inside the helper across teh whole request.
Those calls can be best made in a view or element (because those are rendered before the layout).
If you do it inside a layout make sure this happens before you call `out()`.

## Output
Remove all your meta output in the layout and replace it with
```php
echo $this->Meta->out(); // This contains all the tags
echo $this->fetch('meta'); // This is a fallback (optional) for view blocks
```
It will iterate over all defined meta tags and output them.
Note that you can skip some of those, if you want using the `skip` option.

If you don't manually output them, you must define all tags prior to the `out()` call.
The  `out()` call should be the last PHP code in your `<head></head>` section the layout HTML.

You can also manually output each group of meta tags, e.g. all keywords and descriptions (which you defined before) in all languages using
```php
echo $this->Meta->getKeywords();
echo $this->Meta->getDescription();
```

## JSON-LD Structured Data

The helper supports generating JSON-LD structured data for improved SEO.

### Breadcrumbs

```php
$this->Meta->setBreadcrumbs([
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Blog', 'url' => '/blog'],
    ['name' => 'My Post'],  // Last item typically has no URL
]);
```

URLs can be strings or CakePHP URL arrays:

```php
$this->Meta->setBreadcrumbs([
    ['name' => 'Home', 'url' => ['controller' => 'Pages', 'action' => 'home']],
    ['name' => 'Products', 'url' => ['controller' => 'Products', 'action' => 'index']],
    ['name' => 'Widget'],
]);
```

### Article

```php
$this->Meta->setArticle([
    'headline' => 'How to Use JSON-LD',        // Required
    'author' => 'John Doe',                     // Optional
    'datePublished' => '2026-03-19',           // Optional
    'dateModified' => '2026-03-19',            // Optional
    'image' => 'https://example.com/image.jpg', // Optional
    'description' => 'A guide to structured data', // Optional
]);
```

### Organization

```php
$this->Meta->setOrganization([
    'name' => 'Acme Inc',                       // Required
    'url' => 'https://acme.com',               // Optional
    'logo' => 'https://acme.com/logo.png',     // Optional
    'sameAs' => [                              // Optional
        'https://twitter.com/acme',
        'https://facebook.com/acme',
    ],
]);
```

Organization data can be configured globally in `config/app.php`:

```php
'Meta' => [
    'organization' => [
        'name' => 'Acme Inc',
        'url' => 'https://acme.com',
        'logo' => 'https://acme.com/logo.png',
    ],
],
```

Then override per-page as needed:

```php
$this->Meta->setOrganization(['name' => 'Acme Blog Division']);
// Inherits url and logo from global config
```

### Output

JSON-LD is automatically included when calling `out()`:

```php
echo $this->Meta->out();
```

Or retrieve individually:

```php
echo $this->Meta->getBreadcrumbs();
echo $this->Meta->getArticle();
echo $this->Meta->getOrganization();
```

In debug mode, JSON-LD is pretty-printed for readability.
