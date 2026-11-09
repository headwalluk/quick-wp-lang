# Hooks and filters

This is the **public extension surface** of Quick WP Lang. Anything not listed here is internal and may change without notice between releases. Functions in the `Quick_WP_Lang` namespace are private, and so are the plugin's options and post meta keys; the filters below are the supported integration points.

## Compatibility

- A filter's name and arguments don't change within a major version. New arguments are only ever added at the end.
- A renamed filter keeps working under its old name until at least the next major version, with a deprecation notice when `WP_DEBUG` is on.
- Any change that could break an integration is listed in `CHANGELOG.md`.

## Filters

### `quick_wp_lang_languages`

Filter the list of languages offered on the settings page. Keys are WordPress locales, values are display names.

**Parameters:**
- `array<string, string> $languages` — Locale => name, e.g. `'fr_FR' => 'French (France)'`

**Returns:** `array<string, string>`. A value that isn't an array is treated as an empty list.

```php
add_filter( 'quick_wp_lang_languages', function ( $languages ) {
	$languages['ga'] = __( 'Irish', 'my-theme' );
	return $languages;
} );
```

A locale removed here can no longer be enabled or chosen in the editor. Content that already uses it keeps it on the front end until the post is next saved.

**Since:** 1.0.0

### `quick_wp_lang_supported_post_types`

Filter the post types that show the Content Language box and the Language column. Receives the post types ticked on the settings page.

**Parameters:**
- `array<string> $post_types` — Post type names, e.g. `array( 'post', 'page' )`

**Returns:** `array<string>`. Non-string entries are dropped; a value that isn't an array is treated as an empty list.

```php
add_filter( 'quick_wp_lang_supported_post_types', function ( $post_types ) {
	$post_types[] = 'product';
	return $post_types;
} );
```

The filter runs on `admin_init` and later, so adding it from a theme's `functions.php` works.

**Since:** 1.0.0

### `quick_wp_lang_updater_enabled`

Filter whether the plugin checks GitHub for updates.

**Parameters:**
- `bool $enabled` — Default `true`

**Returns:** `bool`. Read with `FILTER_VALIDATE_BOOLEAN`, so `'0'`, `'no'` and `'off'` also disable updates.

```php
add_filter( 'quick_wp_lang_updater_enabled', '__return_false' );
```

Only affects Quick WP Lang; other plugins' updaters are unaffected.

**Since:** 1.3.0

## Filters the plugin adds to other plugins

With Yoast SEO active, the plugin hooks `wpseo_locale` and `wpseo_schema_piece_language` to give Yoast the post's language. A callback of your own on either filter at a later priority sees, and can override, the post's language.
