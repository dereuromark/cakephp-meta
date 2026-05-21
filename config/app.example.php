<?php

/**
 * Meta Example Configuration
 *
 * Merge the keys below into your application's config/app.php (or
 * config/app_local.php) — do not replace the whole file, since this snippet
 * only contains this plugin's configuration. When copying entries that
 * reference imported classes, use fully-qualified class names or move the
 * `use` imports to the top of the target file. Customize the values as needed.
 *
 * The whole `Meta` namespace is read by Meta\View\Helper\MetaHelper and merged into the
 * helper's meta defaults at construction time. App-level values here are overridden by
 * helper options and, finally, by the `_meta` view variable (the latter wins).
 */
return [
	'Meta' => [
		// NOTE: multiLanguage is a HELPER option, not a Configure key. Set it as a
		// helper option when loading the Meta helper in your AppView, NOT here:
		// MetaHelper reads it from its own config, and placing it under Configure('Meta')
		// makes out() emit a stray <meta name="multiLanguage"> tag. Default: true.

		// Default page title. Set to null to auto-generate from the current
		// controller/action (humanized). Default: null.
		'title' => null,

		// Whether to render the charset meta tag. null means "auto-include" (rendered).
		// Default: null (rendered).
		'charset' => null,

		// Whether to render the icon (favicon) link. null means "auto-include" (rendered).
		// Default: null (rendered).
		'icon' => null,

		// Canonical URL. Set to true for auto-detect of the current URL, a string for an
		// explicit URL, or null to omit. Default: null.
		'canonical' => null,

		// Rendered as a <meta http-equiv="language"> tag (it does NOT set the HTML lang
		// attribute or generate hreflang links). Set to true for auto-detect, a string
		// like 'en' for an explicit value, or null to omit. Default: null.
		'language' => null,

		// Robots directives. Provided as an array and merged over the defaults below.
		// Default: index/follow/archive all false (i.e. discourage indexing).
		'robots' => [
			'index' => false,
			'follow' => false,
			'archive' => false,
		],

		// Default meta description(s). Array keyed by language, or a plain string.
		// Default: [] (none).
		'description' => [],

		// Default meta keywords. Array keyed by language, or a plain string.
		// Default: [] (none).
		'keywords' => [],

		// Arbitrary additional custom meta tags. Default: [] (none).
		'custom' => [],

		// Global defaults for organization JSON-LD structured data, read by
		// MetaHelper::setOrganization() and merged with the per-call data passed to it.
		// A `name` (string) is required once merged. Sub-keys: name, url, logo,
		// contactPoint, sameAs. Default: not set.
		// WARNING: if you call the default out() after setting this under
		// Configure('Meta'), it is also emitted as a stray meta tag — prefer passing
		// the data directly to setOrganization().
		// 'organization' => [
		//     'name' => 'Acme Inc.',
		//     'url' => 'https://example.com',
		//     'logo' => 'https://example.com/logo.png',
		//     'sameAs' => ['https://twitter.com/acme'],
		// ],
	],
];
