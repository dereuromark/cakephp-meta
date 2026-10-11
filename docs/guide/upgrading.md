# Upgrading

This branch supports the dependency versions declared in Composer. Older
CakePHP branches are listed in the [version map](https://github.com/dereuromark/cakephp-meta/wiki#cakephp-version-map).

## Output corrections

- Missing titles are omitted, and robot arrays can replace string settings.
- Titles and sized-icon URLs are escaped. Supply plain text and ordinary URLs.
- Twitter Card names retain their `twitter:` prefix. Other colon-prefixed tags
  use their full key as a `property` attribute.
- `out()` renders sized icons and HTTP-equivalent groups through their dedicated
  getters. It omits organization configuration and helper-only options as tags.
- Automatic canonical URLs respect `getCanonical(true)` when requesting a full
  base URL.
- Automatic language selection applies to descriptions and keywords. Scalar
  defaults can be replaced through their setters without an offset error.
- With `multiLanguage: false`, combined description and keyword getters select
  the current language, with wildcard fallback.
- Breadcrumb positions follow iteration order rather than input keys.
- JSON-LD encoding failures raise `JsonException` instead of emitting an empty
  script silently.

Review snapshot assertions that depend on the previous incorrect markup.
The beginner layout replaces duplicate title, charset, icon, and robots output;
it does not require removing stylesheet or script tags.
