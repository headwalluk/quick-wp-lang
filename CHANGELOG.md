# Changelog

All notable changes to Quick WP Lang will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.2.1] - 2026-02-25

### Fixed
- AJAX notice dismiss now validates notice ID against an allowlist of known notice IDs
- Removed unreachable dead code in front-end URL helper

---

## [1.2.0] - 2026-02-25

### Added
- Settings link on WordPress plugins page for quick access to plugin configuration

---

## [1.1.0] - 2026-02-16

### Added
- Post type selection setting — choose which post types display the language meta box
- Search/filter textbox above language checkboxes on settings page
- Live filtering by language name or locale code
- Locale code display next to language names (e.g., "German `de_DE`")
- Enabled count indicator showing "X of Y languages enabled" summary
- Automatic grouping of enabled languages at top of checkbox list

### Fixed
- Language column now respects enabled post types setting
- Sorting by language column no longer hides posts without a language set
- Uninstall now cleans up all plugin options including `qwl_enabled_post_types`
- Removed unnecessary `wp_cache_flush()` from uninstall routine
- BCP 47 format validation on Content-Language header output
- Resolved potential undefined variable warning in `output_og_locale()`

### Changed
- Default enabled post types changed from all public types to post and page only
- Settings page field order: Content-Language Header and Post Types now appear above the language list
- Notice dismiss script rewritten in vanilla JavaScript (removed jQuery dependency)
- Search input uses `input` event instead of `keyup` (covers paste and autofill)
- Inline styles replaced with CSS classes and `wp_add_inline_style()`
- Removed unused `NONCE_ACTION_SETTINGS` and `NONCE_NAME_SETTINGS` constants

---

## [1.0.1] - 2026-02-12

### Changed
- Updated plugin author from "Headwall" to "Paul Faulkner"
- Updated plugin URI to https://headwall-hosting.com/plugins/quick-wp-lang/
- Updated author URI to https://headwall-hosting.com/

### Added
- GitHub Actions release workflow for automated release builds
- .distignore file to exclude development files from distribution packages

---

## [1.0.0] - 2026-02-12

### Added
- Admin notice when no languages are enabled (dismissible via AJAX)
- Language column in posts/pages list table (sortable)
- Empty state help text on settings page
- AJAX handler for dismissing admin notices
- Inline JavaScript for notice dismissal

### Changed
- Improved user experience with better empty states
- Enhanced discoverability of plugin features

---

## [0.1.0] - 2026-02-12

### Added
- Initial beta release
- Meta box for language selection in post editor sidebar
- Radio button UI for language selection (replaces dropdown)
- Settings page (Settings → Content Language) to enable/disable specific languages
- Curated list of 50+ common languages
- `get_available_languages_list()` helper function
- `get_enabled_languages_list()` helper function
- Front-end `<html lang="">` attribute override via `language_attributes` filter
- Self-referencing `<link rel="alternate" hreflang="..." />` tag output
- `Content-Language` HTTP header on singular views
- `og:locale` meta tag override (with SEO plugin detection)
- SEO plugin compatibility (Yoast SEO, Rank Math, All in One SEO)
- WooCommerce HPOS compatibility
- WordPress locale to BCP 47 format conversion
- Filter `quick_wp_lang_languages` for customizing language list
- Filter `quick_wp_lang_supported_post_types` for customizing post types
- Translation-ready with text domain `quick-wp-lang`
- Follows WordPress Coding Standards (phpcs clean)
- PHP 8.0+ with type hints and return types
- Single Entry, Single Exit (SESE) pattern for all functions
- Namespaced under `Quick_WP_Lang`
- Code-first templates (no inline HTML/PHP mixing)
- Development documentation in `dev-notes/`

### Technical
- Post meta key: `_qwl_language`
- Settings option key: `qwl_enabled_languages`
- Nonce verification for all form submissions
- Sanitization and escaping following WordPress security best practices
- Lazy loading pattern for Admin_Hooks and Public_Hooks classes
- Settings class instantiated early (before `admin_init`)

---

## [Unreleased]

### Planned
- Quick Edit column showing language override
- Bulk edit support

---

**Legend:**
- `Added` - New features
- `Changed` - Changes in existing functionality
- `Deprecated` - Soon-to-be removed features
- `Removed` - Removed features
- `Fixed` - Bug fixes
- `Security` - Security fixes
