# Getting started

Complete [Installation](./installation) first. This example adds a title and
description to one page, then outputs them from the layout.

## Load the helper

In `src/View/AppView.php`, load the helper inside `initialize()`:

```php
public function initialize(): void {
    parent::initialize();
    $this->loadHelper('Meta.Meta', [
        'robots' => ['index' => true, 'follow' => true, 'archive' => true],
    ]);
}
```

The helper's default robots instructions are `noindex,nofollow,noarchive`.
The example enables indexing for public pages. For private pages or staging
sites, keep the defaults or set the appropriate instructions per page. Robots
tags do not control access to the page.

## Set the page values

At the start of a template, for example `templates/Pages/home.php`:

```php
$this->Meta->setTitle('Welcome to our site');
$this->Meta->setDescription('News and updates from our team.');
```

The template renders before the layout, so these values are available when the
layout builds the document head.

## Output the tags once

In your layout, usually `templates/layout/default.php`, replace its existing
`<title>`, charset, favicon, and robots output with:

```php
echo $this->Meta->out();
```

Place this inside `<head>`. Leave your stylesheet and script output in place.
Avoid a second title or description from the layout or the `meta` view block.
If you retain `$this->fetch('meta')`, check that its tags do not duplicate the
helper's output.

Open the page and inspect its HTML source. You should see one title, the page
description, charset and favicon tags, and the robots instructions configured
above. The helper escapes title and metadata values; pass ordinary text rather
than pre-escaped HTML.

See [Configuration and tags](./helper) to pass page values from controllers,
[Social metadata](./social) for link previews, and [Structured data](./structured-data)
for JSON-LD.
