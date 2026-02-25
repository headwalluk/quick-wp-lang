# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Quick WP Lang is a lightweight WordPress plugin that assigns alternative languages to individual posts/pages/CPTs for SEO purposes — without a full multilingual setup. It overrides `<html lang>`, outputs hreflang tags, og:locale meta, and optionally sends a Content-Language HTTP header.

- **PHP 8.0+, WordPress 6.0+**
- **Namespace:** `Quick_WP_Lang`
- **Text Domain:** `quick-wp-lang`
- **No build step** — pure PHP, no Composer/Node dependencies
- **Do not install Composer** — `phpcs` and `phpcbf` are already installed globally on the host system

## Commands

```bash
phpcs                    # Check all files against WordPress Coding Standards
phpcbf                   # Auto-fix PHPCS violations
phpcs includes/          # Check specific directory
```

There are no automated tests. Verification is manual: check `phpcs` passes, plugin loads without PHP errors, admin settings page works, front-end output is correct.

## Architecture

```
quick-wp-lang.php          # Plugin header & bootstrap
constants.php              # All constants (OPT_, META_, NONCE_, SETTINGS_, DEF_, PRIORITY_)
functions-private.php      # get_plugin_instance() global accessor
includes/
  class-plugin.php         # Main bootstrap: registers all hooks, lazy-loads child classes
  class-settings.php       # Settings page, language/post-type selection (early instantiation)
  class-admin-hooks.php    # Meta box, language column in post lists, admin notices
  class-public-hooks.php   # Front-end: lang attr, hreflang, og:locale, Content-Language header
  functions-languages.php  # get_available_languages_list(), get_enabled_languages_list()
assets/admin/
  settings-search.js       # Vanilla JS for filtering languages on settings page
```

**Class lifecycle:** `Plugin` is the central bootstrap. `Settings` is instantiated early (before `admin_init`). `Admin_Hooks` and `Public_Hooks` are lazy-loaded via nullable properties.

**Data storage:** Post language stored in `_qwl_language` post meta (WordPress locale format, e.g. `pl_PL`). Settings stored in `wp_options`: `qwl_enabled_languages`, `qwl_enabled_post_types`, `qwl_enable_content_language_header`.

**SEO plugin detection:** Skips og:locale output when Yoast, Rank Math, or All in One SEO is active.

## Coding Conventions

These are project-enforced rules — not suggestions:

- **No `declare(strict_types=1)`** — WordPress/WooCommerce incompatibility
- **Single-Entry Single-Exit (SESE)** — every function has one `return` at the end, no early returns
- **No inline HTML** — all output via `printf()`/`echo` with proper escaping (`esc_html()`, `esc_attr()`, `esc_url()`)
- **All magic values in `constants.php`** — use appropriate prefix (`OPT_`, `META_`, `DEF_`, `NONCE_`, `SETTINGS_`, `PRIORITY_`)
- **Date/time as human-readable strings** — format `Y-m-d H:i:s T`, not Unix timestamps
- **WordPress array syntax** — `array()` not `[]` (enforced by PHPCS)
- **Boolean options** — use `filter_var($val, FILTER_VALIDATE_BOOLEAN)`
- **Class files:** `class-{name}.php` | **Function files:** `functions-{purpose}.php`

## Commit Message Format

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

Prefix with milestone tag when applicable: `[M3] feat: add search filter`

## Pre-Commit Workflow

1. `phpcs` — check for violations
2. `phpcbf` — auto-fix
3. `phpcs` — verify all clean
4. Test in browser (admin + front-end)

## Key Extensibility Points

- **Filter `quick_wp_lang_languages`** — add/remove from available languages list
- **Filter `quick_wp_lang_supported_post_types`** — control which post types show the meta box

## Reference Documentation

- `AGENTS.md` — AI agent development workflow
- `.github/copilot-instructions.md` — comprehensive WordPress coding standards (portable)
- `dev-notes/patterns/` — implementation pattern guides (admin tabs, caching, database, JS, settings API, templates, WooCommerce)
- `dev-notes/workflows/` — code standards setup and git commit workflow
