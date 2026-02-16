# Quick WP Lang — Agent Development Guide

**Purpose:** Project-specific development workflow for AI agents working on this plugin.

**Core Coding Standards:** See [`.github/copilot-instructions.md`](.github/copilot-instructions.md) for comprehensive WordPress coding standards, patterns, and security guidelines.

**Detailed Patterns:** See [`dev-notes/patterns/`](dev-notes/patterns/) for implementation guides.

---

## Quick Reference

### Testing Commands

Since this is a WordPress plugin (not a Node project), testing is done differently:

**1. Code Standards:**
```bash
phpcs              # Check all files
phpcbf             # Auto-fix issues
phpcs              # Verify fixes
```

**2. Runtime Testing:**
- Verify plugin is active: `wp --path=/var/www/westfield.local/web plugin list`
- Check error log: `tail -n 20 /var/www/westfield.local/log/error.log`
- Test admin page: Visit `http://westfield.local/wp-admin/options-general.php?page=qwl-settings`
- Test front-end: Create a test post with language meta, verify hreflang output

**3. Database Verification:**
```bash
# Check enabled languages option
wp --path=/var/www/westfield.local/web option get qwl_enabled_languages

# Check post meta
wp --path=/var/www/westfield.local/web post meta get <POST_ID> _qwl_language
```

---

## Project-Specific Conventions

### Constants File

All magic values must be defined in `constants.php`:
- `OPT_` prefix for wp_options keys
- `META_` prefix for post meta keys (if we add more beyond `_qwl_language`)
- `SETTINGS_` prefix for Settings API constants

### Namespace

All classes and functions use the `Quick_WP_Lang` namespace.

### File Naming

- Classes: `class-{name}.php` (e.g., `class-settings.php`)
- Functions: `functions-{purpose}.php` (e.g., `functions-languages.php`)

### No Inline HTML

All template output must use `printf()` or `echo` with proper escaping. No mixed inline HTML/PHP snippets. See copilot-instructions.md for examples.

---

## Commit Format

```
type: brief description

- Detailed point 1
- Detailed point 2
```

**Prefix with milestone when working on milestones:**
```
[M3] feat: add search filter to settings page

- Add textbox above language list
- Implement JS keyup filter by name/locale
- Hide non-matching checkboxes dynamically
```

**Types:** `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

---

## Pre-Commit Workflow

1. Run `phpcs` to check for violations
2. Run `phpcbf` to auto-fix issues
3. Run `phpcs` again to verify all issues resolved
4. Test in browser (admin + front-end)
5. Check error log for PHP warnings/errors
6. Stage and commit with proper message format

---

## Testing Sprint Checklist

For WordPress plugins, a "passing test" means:

1. ✅ `phpcs` returns zero violations
2. ✅ Plugin is active and loads without errors
3. ✅ Error log (`/var/www/westfield.local/log/error.log`) shows no new PHP warnings/errors
4. ✅ Admin settings page loads and saves correctly
5. ✅ Front-end output is correct (check `<html lang>`, hreflang tags, HTTP headers)
6. ✅ Post meta is saved/retrieved correctly

If any of these fail, document in Testing Notes and set Next Sprint to `coding`.

---

## Key Files

- `quick-wp-lang.php` — Main plugin file (plugin header, bootstrap)
- `constants.php` — All plugin constants
- `includes/class-plugin.php` — Main plugin class, hook registration
- `includes/class-settings.php` — Settings page
- `includes/class-admin-hooks.php` — Meta box and admin functionality
- `includes/class-public-hooks.php` — Front-end language overrides
- `includes/functions-languages.php` — Language list helpers

---

## Important Notes

- **No `declare(strict_types=1)`** — WordPress and WooCommerce don't use strict types
- **SESE pattern** — Single entry, single exit for all functions (one return statement)
- **Human-readable dates** — Store as `Y-m-d H:i:s T`, not Unix timestamps
- **Security first** — Sanitize input, escape output, verify nonces
- **Translation ready** — Use `__()`, `_e()`, `esc_html__()`, `esc_html_e()`

---

For comprehensive coding standards, patterns, and examples, see [`.github/copilot-instructions.md`](.github/copilot-instructions.md).
