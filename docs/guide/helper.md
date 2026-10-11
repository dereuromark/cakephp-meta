# Configuration and tags

## Where values come from

Metadata is merged in this order, with later values replacing earlier values:

1. Helper defaults.
2. `Configure::read('Meta')`, normally from `config/app.php`.
3. Options passed to `loadHelper('Meta.Meta', $options)`.
4. The `_meta` view variable available when the helper is constructed.
5. Calls to setter methods before output.

Partial robots arrays merge with the previous robots settings. Other metadata
arrays replace the preceding array. Set `_meta` in the controller before the
view is created:

```php
$this->set('_meta', [
    'title' => 'About our team',
    'description' => 'Meet the people behind the project.',
    'robots' => ['index' => true, 'follow' => true, 'archive' => true],
    'canonical' => 'https://example.com/about',
]);
```

## Defaults

| Key | Default | Meaning |
| --- | --- | --- |
| `title` | `null` | Derives a title from controller and action names when available. |
| `charset` | `null` | Enables CakePHP's configured charset. `false` disables it. |
| `icon` | `null` | Enables the default favicon. `false` disables it. |
| `canonical` | `null` | Omitted from `out()`. `true` uses the current request path. |
| `language` | `null` | Omitted. `true` derives it from `intl.default_locale`. |
| `robots` | all three flags `false` | Outputs `noindex,nofollow,noarchive`. |
| `description`, `keywords`, `custom` | `[]` | No values initially. |
| `multiLanguage` | `true` | Helper option controlling language-specific descriptions and keywords. |

For example, merge this entry into the array returned by `config/app.php`:

```php
'Meta' => [
    'robots' => ['index' => true, 'follow' => true, 'archive' => true],
    'language' => 'en',
],
```

`multiLanguage` belongs in the helper's loading options or `setConfig()`. It is
not a metadata tag. The helper does not automatically use `I18n::getLocale()`;
set `language` explicitly when your application changes locale per request.

## Common setters

```php
$this->Meta->setTitle('Page title');
$this->Meta->setDescription('A short description.');
$this->Meta->setKeywords(['CakePHP', 'metadata']);
$this->Meta->setRobots('noindex,nofollow');
$this->Meta->setCanonical('https://example.com/page');
$this->Meta->setHttpEquiv('expires', '0');
$this->Meta->custom('viewport', 'width=device-width, initial-scale=1');
```

`setRobots()` accepts a string, an array of flags, or `false` to omit the tag.
An array preserves unspecified flags from an existing robots array. Replacing
a string value with an array starts a new flag map.
`setCanonical()` accepts a string, a CakePHP URL array, `true`, or `false`.
Use an explicit absolute URL for a public canonical address. `getCanonical(true)`
requests a full base URL for relative or route-array values, using CakePHP's
configured base URL. Automatic canonical paths omit query parameters.

`custom()` stores and immediately returns its tag. You can call it without
`echo` before `out()`, or output its return value directly and skip `custom`
in the combined output to avoid duplicates.

## Icons

```php
$this->Meta->setIcon('/favicon.ico');
$this->Meta->setSizesIcon('/favicon-32x32.png', 32, ['type' => 'image/png']);
$this->Meta->setSizesIcon('/apple-touch-icon.png', 180, ['prefix' => 'apple-touch-']);
```

Sized icons appear in `out()`. `getSizesIcon($url)` retrieves one stored icon,
and `getSizesIcons()` retrieves all of them.

## Languages

```php
$this->Meta->setLanguage('en');
$this->Meta->setDescription('English description.', 'en');
$this->Meta->setDescription('Deutsche Beschreibung.', 'de');
$this->Meta->setKeywords(['CakePHP', 'metadata'], 'en');
```

With `multiLanguage: true`, descriptions and keywords output all stored language
entries. A wildcard entry (`*`) is omitted when explicit language entries exist.
You can retrieve one language with `getDescription('en')` or `getKeywords('en')`.

With `multiLanguage: false`, combined getters select the current language and
fall back to a wildcard entry. Setting a different language through the setters
raises `RuntimeException` when a current language is configured. Set the page's
language before its description and keywords.

## Output

```php
echo $this->Meta->out(null, ['skip' => ['icon', 'custom'], 'implode' => "\n"]);
echo $this->Meta->out('description');
```

Use one of these forms for each tag to avoid duplicate output. `skip` applies to
metadata groups; JSON-LD remains included in the combined output. Individual
getters are available for titles, charset, icons, canonical URLs, robots,
language, descriptions, keywords, HTTP equivalents, and structured data.
