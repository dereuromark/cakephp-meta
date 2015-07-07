# Meta Helper

## Enabling
You can enable the helper in your AppView class:
```php
$this->loadHelper('ToolsExtra.Meta');

// or setting different defaults
$this->loadHelper('ToolsExtra.Meta', ['robots' => ['index' => true, 'follow' => true]]);
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
		'language => 'de',
		'robots' => ['index' => true, 'follow' => true]
	]
];

You can pass them to the loadHelper() method as shown above.

If you need to customize them per controller or per action you can pass them from the controller to the view or modify them in the view template.

In your controller, you could do the following:
```php
$_meta = [
	'title => 'Foo Bar',
	'robots' => ['index' => false]
];
$this->set(compact('_meta')));
```

In your view ctp you can also do:
```php
$this->Meta->keywords('I, am, English', 'en');
$this->Meta->keywords('Ich, bin, deutsch', 'de');
$this->Meta->description('Foo Bar');
$this->Meta->robots(['index' => false]);
```

## Output
Remove all your meta output in the view and replace it with
```php
echo $this->Meta->out();
```
It will iterate over all defined meta tags and output them.
Note that you can skip some of those, if you want using the `skip` option.

You can also manually output them all, e.g. all keywords and descriptions (which you defined before) in all languages using
```php
echo $this->Meta->keywords();
echo $this->Meta->description();
```
