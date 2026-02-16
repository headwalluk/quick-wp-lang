=== Quick WP Lang ===
Contributors: headwall
Tags: language, hreflang, seo, multilingual, locale
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Assign alternative languages to individual posts, pages, and custom post types for better SEO without requiring a full multilingual setup.

== Description ==

**Quick WP Lang** is a lightweight WordPress plugin that lets you assign an alternative language to individual posts, pages, or custom post types (including WooCommerce products) without requiring a full multilingual setup.

= The Problem =

Your site runs in a primary language (e.g., English UK) but you have a handful of pages written in another language (e.g., Polish, German). Without per-page language metadata, search engines see every page as the site default language, harming SEO for those alternative-language pages.

= The Solution =

A simple meta box in the post editor lets you pick a language. On the front end, the plugin adjusts the page's `<html lang="">` attribute, outputs the correct `hreflang` meta tag, and sets the `Content-Language` HTTP header — all scoped to that single post.

= Important Distinction =

This is **not** a translation plugin. There is no concept of "the same content in multiple languages". When a post is assigned an alternative language, that content exists **only** in that language — there is no counterpart in the site's default language. This is for ad-hoc alternative language provision for individual pieces of singular content.

= Features =

* **Simple Meta Box** — Select language from a curated list of 50+ common languages
* **Settings Page** — Enable only the languages you need, with search and filtering
* **Post Type Control** — Choose which post types display the language meta box
* **Radio Button UI** — Clean, accessible interface in the post editor sidebar
* **SEO Optimized** — Automatically adjusts `lang` attribute, hreflang tags, and `og:locale`
* **SEO Plugin Compatible** — Detects Yoast SEO, Rank Math, and All in One SEO
* **WooCommerce Compatible** — Works with WooCommerce products (HPOS compatible)
* **Developer Friendly** — Extensible via filters, follows WordPress Coding Standards
* **No Build Step** — Pure PHP, no JavaScript compilation required
* **Translation Ready** — Fully internationalized with text domain `quick-wp-lang`

= How It Works =

1. Go to **Settings → Content Language** and enable the languages you want to use
2. Edit any post, page, or custom post type
3. In the **Content Language** meta box, select the appropriate language
4. Save the post
5. The front end automatically updates language attributes for better SEO

= Technical Details =

* Adjusts `<html lang="">` attribute via `language_attributes` filter
* Outputs self-referencing `<link rel="alternate" hreflang="..." />` tag
* Sends `Content-Language` HTTP header
* Overrides `og:locale` meta tag (when no SEO plugin is active)
* Converts WordPress locale format (`de_DE`) to BCP 47 (`de-DE`)
* Works on singular views only (`is_singular()`)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/quick-wp-lang/` directory, or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings → Content Language** to enable your desired languages
4. Edit any post or page and select a language from the **Content Language** meta box

== Frequently Asked Questions ==

= Is this a translation plugin? =

No. This plugin does not translate content or manage multilingual versions of the same post. It simply allows you to assign a language to individual posts for SEO purposes.

= Which post types are supported? =

Posts and pages by default. You can enable additional post types (including WooCommerce products and custom post types) under **Settings → Content Language**. Developers can also use the `quick_wp_lang_supported_post_types` filter for further customization.

= Will this work with my SEO plugin? =

Yes. The plugin detects Yoast SEO, Rank Math, and All in One SEO, and defers to them for `og:locale` output to avoid conflicts.

= Does this work with WooCommerce? =

Yes. The plugin is fully HPOS (High-Performance Order Storage) compatible and works with WooCommerce products.

= Can I add more languages? =

Yes. Use the `quick_wp_lang_languages` filter to add or modify the available languages programmatically.

== Screenshots ==

1. Settings page — Enable only the languages you need
2. Meta box in post editor — Clean radio button interface
3. Front-end output — Proper language attributes and hreflang tags

== Changelog ==

= 1.1.0 - 2026-02-16 =
* Added: Post type selection setting — choose which post types display the language meta box
* Added: Search/filter textbox above language checkboxes on settings page
* Added: Live filtering by language name or locale code
* Added: Locale code display next to language names (e.g., "German `de_DE`")
* Added: Enabled count indicator showing "X of Y languages enabled" summary
* Added: Automatic grouping of enabled languages at top of checkbox list
* Fixed: Language column now respects enabled post types setting
* Fixed: Sorting by language column no longer hides posts without a language set
* Fixed: Uninstall now cleans up all plugin options including `qwl_enabled_post_types`
* Fixed: Removed unnecessary `wp_cache_flush()` from uninstall routine
* Fixed: BCP 47 format validation on Content-Language header output
* Fixed: Resolved potential undefined variable warning in `output_og_locale()`
* Changed: Default enabled post types changed from all public types to post and page only
* Changed: Settings page field order: Content-Language Header and Post Types now appear above the language list
* Changed: Notice dismiss script rewritten in vanilla JavaScript (removed jQuery dependency)
* Changed: Search input uses `input` event instead of `keyup` (covers paste and autofill)
* Changed: Inline styles replaced with CSS classes and `wp_add_inline_style()`
* Changed: Removed unused `NONCE_ACTION_SETTINGS` and `NONCE_NAME_SETTINGS` constants

= 1.0.1 - 2026-02-12 =
* Changed: Updated plugin author from "Headwall" to "Paul Faulkner"
* Changed: Updated plugin URI to https://headwall-hosting.com/plugins/quick-wp-lang/
* Changed: Updated author URI to https://headwall-hosting.com/
* Added: GitHub Actions release workflow for automated release builds
* Added: .distignore file to exclude development files from distribution packages

= 1.0.0 - 2026-02-12 =
* Added: Admin notice when no languages are enabled (dismissible via AJAX)
* Added: Language column in posts/pages list table (sortable)
* Added: Empty state help text on settings page
* Added: AJAX handler for dismissing admin notices
* Added: Inline JavaScript for notice dismissal
* Changed: Improved user experience with better empty states
* Changed: Enhanced discoverability of plugin features

= 0.1.0 - 2026-02-12 =
* Initial beta release
* Meta box for language selection in post editor sidebar
* Radio button UI for language selection (replaces dropdown)
* Settings page (Settings → Content Language) to enable/disable specific languages
* Curated list of 50+ common languages
* `get_available_languages_list()` helper function
* `get_enabled_languages_list()` helper function
* Front-end `<html lang="">` attribute override via `language_attributes` filter
* Self-referencing `<link rel="alternate" hreflang="..." />` tag output
* `Content-Language` HTTP header on singular views
* `og:locale` meta tag override (with SEO plugin detection)
* SEO plugin compatibility (Yoast SEO, Rank Math, All in One SEO)
* WooCommerce HPOS compatibility
* WordPress locale to BCP 47 format conversion
* Filter `quick_wp_lang_languages` for customizing language list
* Filter `quick_wp_lang_supported_post_types` for customizing post types
* Translation-ready with text domain `quick-wp-lang`
* Follows WordPress Coding Standards (phpcs clean)
* PHP 8.0+ with type hints and return types
* Single Entry, Single Exit (SESE) pattern for all functions
* Namespaced under `Quick_WP_Lang`
* Code-first templates (no inline HTML/PHP mixing)
* Development documentation in `dev-notes/`
* Post meta key: `_qwl_language`
* Settings option key: `qwl_enabled_languages`
* Nonce verification for all form submissions
* Sanitization and escaping following WordPress security best practices
* Lazy loading pattern for Admin_Hooks and Public_Hooks classes
* Settings class instantiated early (before `admin_init`)

== Upgrade Notice ==

= 1.1.0 =
New post type selection setting, language search on settings page, and several bug fixes.

= 1.0.1 =
Minor update with metadata changes and automated release workflow.

= 1.0.0 =
Stable 1.0.0 release with improved UX: admin notices, sortable language column, and better empty states.
