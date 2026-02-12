# Project Tracker — Quick WP Lang

**Version:** 0.1.0 (Beta)  
**Last Updated:** 12 February 2026  
**Current Phase:** Beta Testing  
**Overall Progress:** 85%

---

## Overview

**Quick WP Lang** is a lightweight WordPress plugin that lets site owners assign an alternative language to individual posts, pages, custom post types (including WooCommerce products), without requiring a full multilingual setup.

**Problem:** A site runs in a primary language (e.g. `en_GB`) but has a handful of pages written in another language (e.g. `pl_PL`, `de_DE`). Without per-page language metadata, search engines see every page as the site default language, harming SEO for those alternative-language pages.

**Solution:** A simple meta box in the post editor lets the author pick a language. On the front end, the plugin adjusts the page's `<html lang="">` attribute, outputs the correct `hreflang` meta tag, and optionally sets the `Content-Language` HTTP header — all scoped to that single post.

**Important distinction:** This is **not** a multilingual/translation plugin. There is no concept of "the same content in multiple languages". When a post is assigned an alternative language, that content exists **only** in that language — there is no counterpart in the site's default language. This is ad-hoc alternative language provision for individual pieces of singular content.

---

## Requirements

### R1 — Meta Box (Editor UI)

- [x] Add a meta box titled **"Content Language"** to the post editor.
- [x] Display on all public post types by default (posts, pages, products, CPTs).
- [x] Allow the site owner to configure which post types show the meta box (Settings page or filter).
- [x] Meta box contains radio buttons (v0.1.0) of available languages (was dropdown in planning).
- [x] Language list: hardcoded curated array of ~50 common locales. Extensible via `qwl_languages` filter.
- [x] First option: **"Site Default"** (empty value — no override).
- [x] Selected language stored as post meta: key `_qwl_language`, value = locale string (e.g. `de_DE`).
- [x] Meta box is a classic PHP meta box — works in both Classic Editor and Block Editor (no JS build step).
- [x] Visible to any user who can `edit_post` for that post (no extra capability required).

### R2 — Front-End Language Output

- [x] On singular views only (`is_singular()`), check for `_qwl_language` post meta.
- [x] If set, override the `<html lang="">` attribute via the `language_attributes` filter.
- [x] Output a single self-referencing `<link rel="alternate" hreflang="xx" href="..." />` in `<head>` for the override language.
- [x] Do **not** output an hreflang for the site default language — no default-language version of this content exists.
- [x] Optionally send `Content-Language` HTTP header (always enabled in v0.1.0).
- [x] Override `og:locale` meta tag for affected pages; skip output if a known SEO plugin (Yoast, RankMath, AIOSEO) is active.
- [x] Convert WordPress locale format (`de_DE`) to BCP 47 (`de-DE`) for hreflang tags.

### R3 — Settings Page (Optional, Milestone 2)

- [x] Admin settings page under **Settings → Content Language**.
- [ ] Option: select which post types display the meta box (filter available, UI pending).
- [x] Option: curate/restrict the available language list.
- [ ] Option: toggle `Content-Language` HTTP header on/off (always enabled in v0.1.0).

### R4 — General / Non-Functional

- [x] No external dependencies — pure WordPress APIs.
- [x] HPOS compatible (no `get_post_meta` on orders).
- [x] Translation-ready with text domain `quick-wp-lang`.
- [x] Follows WordPress Coding Standards (phpcs clean).
- [x] PHP 8.0+ with type hints.
- [x] Namespaced under `Quick_WP_Lang`.
- [ ] Uninstall cleanup: remove all `_qwl_language` meta on uninstall.

---

## Milestones

### Milestone 1 — Core Functionality (MVP)

| # | Task | Status |
|---|------|--------|
| 1.1 | Scaffold plugin files (main file, constants, classes) | ✅ Complete |
| 1.2 | Implement meta box (save/load post meta) | ✅ Complete |
| 1.3 | Build language list helper | ✅ Complete |
| 1.4 | Implement `language_attributes` filter override | ✅ Complete |
| 1.5 | Implement hreflang `<link>` tag output | ✅ Complete |
| 1.6 | Implement `Content-Language` header (optional) | ✅ Complete |
| 1.7 | Implement `og:locale` override with SEO plugin conflict detection | ✅ Complete |
| 1.8 | PHPCS pass and testing | ✅ Complete |

### Milestone 2 — Settings & Polish

| # | Task | Status |
|---|------|--------|
| 2.1 | Settings page — post type selection | 🔄 Filter available, UI pending |
| 2.2 | Settings page — language list curation | ✅ Complete |
| 2.3 | Settings page — header toggle | 🔄 Pending |
| 2.4 | Quick Edit column showing language override | 🔄 Pending |
| 2.5 | Uninstall routine | 🔄 Pending |

---

## Technical Debt

### Minor

- Consider adding admin notice when no languages are enabled on settings page
- Add bulk edit support for language assignment
- Implement settings toggle for `Content-Language` HTTP header (currently always enabled)
- Implement settings UI for post type selection (filter exists, UI pending)

---

## Notes for Development

### File Structure (Planned)

```
quick-wp-lang/
├── quick-wp-lang.php          # Main plugin file
├── constants.php               # Plugin constants
├── includes/
│   ├── class-plugin.php        # Bootstrap & hook registration
│   ├── class-admin-hooks.php   # Meta box render/save
│   ├── class-public-hooks.php  # Front-end lang overrides
│   ├── class-settings.php      # Settings page (Milestone 2)
│   └── functions-languages.php # Language list helpers
├── uninstall.php               # Cleanup on uninstall
├── assets/
│   └── admin/
│       └── admin.css           # Meta box styling (if needed)
├── languages/                  # .pot / .po / .mo files
└── dev-notes/                  # Development documentation
```

### Post Meta Key

`_qwl_language` — underscore-prefixed to hide from Custom Fields UI. Stores a WordPress locale string (e.g. `pl_PL`, `de_DE`) or empty string for site default.

### Locale to BCP 47 Conversion

WordPress uses `en_GB`, BCP 47 uses `en-GB`. Simple `str_replace( '_', '-', $locale )`.

