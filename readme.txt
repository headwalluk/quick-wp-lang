=== Quick WP Lang ===
Contributors: headwall
Tags: language, hreflang, seo, multilingual, locale
Requires at least: 7.1
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Set the language of individual posts and pages for SEO, and link translations to their original, without a full multilingual setup.

== Description ==

**Quick WP Lang** is for a site that runs in one language but has some content in others: an English (UK) site with a few pages in French, say. Without per-page language information, search engines treat every page as being in the site's language, and can't tell that the French page is the French version of an English one.

A **Content Language** box in the editor lets you set a post's language and, for a translation, pick the page it translates. On the front end the plugin then:

* Sets `<html lang>` to the content's language
* Outputs `hreflang` tags on **both** the original and its translations, listing every version and an `x-default`
* Sends a `Content-Language` HTTP header (optional)
* Sets `og:locale`, and with Yoast SEO, the schema `inLanguage`

Canonical URLs, sitemaps and permalinks are left alone, so each version stays indexable at its own URL.

= What it isn't =

It isn't a translation plugin. It doesn't translate content, create copies of posts, add language switchers or change URLs. If you need those, use a full multilingual plugin instead; don't run the two together.

= Features =

* Curated list of 50+ languages; enable only the ones you need, with search
* Choose which post types (including WooCommerce products and custom post types) show the language box
* Link translations to their original for reciprocal hreflang
* Sortable Language column in the list of posts
* Works with Yoast SEO, Rank Math and All in One SEO
* Keeps all data on uninstall unless you choose otherwise
* Automatic updates from GitHub releases
* Translation ready; extensible through filters

== Installation ==

1. Upload the plugin to `/wp-content/plugins/quick-wp-lang/`, or install the release zip through Plugins → Add New → Upload Plugin
2. Activate the plugin through the Plugins screen
3. Go to **Settings → Content Language** and tick the languages you need
4. Edit a post or page, choose its language in the **Content Language** box and, for a translation, the page it translates

== Frequently Asked Questions ==

= Should I set a translation's canonical URL to the original? =

No. A canonical tells search engines a page is a duplicate to drop from the index, so the translation would stop appearing in results. Keep each page's own canonical and link the translation to its original instead; the hreflang tags say they are versions of each other.

= Should I exclude translations from the sitemap? =

No. Leave them in, so search engines find them.

= Which post types are supported? =

Posts and pages by default. Enable others under **Settings → Content Language**, or with the `quick_wp_lang_supported_post_types` filter.

= Will this work with my SEO plugin? =

Yes. None of Yoast SEO, Rank Math or All in One SEO outputs hreflang, so there is no conflict. With Yoast SEO, its `og:locale` and schema follow the post's language; with the other two, their `og:locale` keeps the site's language.

= Can I add more languages? =

Yes, with the `quick_wp_lang_languages` filter.

= What happens to my data if I delete the plugin? =

It is kept, unless **Delete all plugin data when uninstalled** is ticked on the settings page.

== Screenshots ==

1. Settings page — Enable only the languages you need
2. Content Language box in the editor
3. Front-end output — Language attributes and hreflang tags

== Changelog ==

= 1.2.1 - 2026-02-25 =
* Fixed: AJAX notice dismiss now validates notice ID against allowlist
* Fixed: Removed unreachable dead code in front-end URL helper

= 1.2.0 - 2026-02-25 =
* Added: Settings link on WordPress plugins page for quick access to plugin configuration

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

= 1.2.1 =
Security hardening and minor code cleanup.

= 1.2.0 =
Settings link added to the plugins page for quick access to configuration.

= 1.1.0 =
New post type selection setting, language search on settings page, and several bug fixes.

= 1.0.1 =
Minor update with metadata changes and automated release workflow.

= 1.0.0 =
Stable 1.0.0 release with improved UX: admin notices, sortable language column, and better empty states.
