# Changelog

All notable changes to Quick WP Lang will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-02-16

### Added
- Search/filter textbox above language checkboxes on settings page
- Live filtering by language name or locale code (JavaScript keyup handler)
- Locale code display next to language names (e.g., "German `de_DE`")
- Enabled count indicator showing "X of Y languages enabled" summary
- Automatic grouping of enabled languages at top of checkbox list

### Fixed
- Extended all prefixes from 'qwl' to 'quick_wp_lang' to resolve PHPCS short prefix warnings
- Updated constants to use longer prefix (e.g., `QUICK_WP_LANG_VERSION` instead of `QWL_VERSION`)
- Updated hook names with longer prefix for better uniqueness guarantees
- All WordPress Coding Standards violations resolved

### Changed
- Improved discoverability of enabled languages in settings UI
- Enhanced usability for sites with many available languages

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
- Filter `qwl_languages` for customizing language list
- Filter `qwl_supported_post_types` for customizing post types
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
- Uninstall routine to clean up post meta and options
- Quick Edit column showing language override
- Settings option to toggle `Content-Language` HTTP header
- Settings option to select which post types display meta box
- Admin notice if no languages are enabled
- Bulk edit support

---

**Legend:**
- `Added` - New features
- `Changed` - Changes in existing functionality
- `Deprecated` - Soon-to-be removed features
- `Removed` - Removed features
- `Fixed` - Bug fixes
- `Security` - Security fixes
