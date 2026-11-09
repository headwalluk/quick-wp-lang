# Extending Quick WP Lang

Practical recipes using the hooks in [hooks-and-filters.md](hooks-and-filters.md). Put them in a theme's `functions.php` or a small must-use plugin.

## Add a language that isn't in the list

```php
add_filter( 'quick_wp_lang_languages', function ( $languages ) {
	$languages['gd'] = __( 'Scottish Gaelic', 'my-theme' );
	return $languages;
} );
```

Then tick it under **Settings → Content Language**. Use the WordPress locale format (`gd`, `pt_PT`); the plugin converts it to BCP 47 for HTML and HTTP output.

## Offer only a handful of languages

```php
add_filter( 'quick_wp_lang_languages', function ( $languages ) {
	return array_intersect_key( $languages, array_flip( array( 'fr_FR', 'de_DE', 'es_ES' ) ) );
} );
```

## Always support a custom post type

Ticking the post type on the settings page is usually enough. To make it permanent, whatever the settings say:

```php
add_filter( 'quick_wp_lang_supported_post_types', function ( $post_types ) {
	if ( ! in_array( 'event', $post_types, true ) ) {
		$post_types[] = 'event';
	}
	return $post_types;
} );
```

## Remove a post type

```php
add_filter( 'quick_wp_lang_supported_post_types', function ( $post_types ) {
	return array_values( array_diff( $post_types, array( 'attachment' ) ) );
} );
```

## Disable automatic updates

```php
add_filter( 'quick_wp_lang_updater_enabled', '__return_false' );
```

Useful where releases are deployed by other means. Sites still show updates for other plugins as normal.
