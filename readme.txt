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
* Added: Language search/filter on settings page
* Added: Enabled languages count indicator on settings page
* Improved: Languages sorted with enabled languages shown first on settings page
* Fixed: Language column now respects enabled post types setting
* Fixed: Sorting by language column no longer hides posts without a language set
* Fixed: Uninstall now cleans up all plugin options

= 1.0.1 - 2026-02-12 =
* Changed: Updated plugin author and URI metadata
* Added: GitHub Actions release workflow for automated releases
* Added: .distignore file for cleaner distribution packages

= 1.0.0 - 2026-02-12 =
* Added: Admin notice when no languages are enabled (dismissible)
* Added: Language column in posts/pages list table (sortable)
* Added: Empty state help text on settings page
* Added: AJAX handler for dismissing admin notices
* Improved: User experience with better empty states
* Improved: Enhanced discoverability of plugin features

= 0.1.0 - 2026-02-12 =
* Initial beta release
* Meta box for language selection with radio button UI
* Settings page to enable/disable languages
* Front-end language attribute overrides
* Hreflang tag output
* Content-Language HTTP header
* og:locale meta tag (SEO plugin aware)
* WooCommerce HPOS compatible
* Translation ready

== Upgrade Notice ==

= 1.1.0 =
New post type selection setting, language search on settings page, and several bug fixes.

= 1.0.1 =
Minor update with metadata changes and automated release workflow.

= 1.0.0 =
Stable 1.0.0 release with improved UX: admin notices, sortable language column, and better empty states.
