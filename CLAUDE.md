# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Repository: public

Follows the **headwall-wp-plugin** skill, which holds the shared conventions: PHP style,
templates, logging, comments, public-contract rules, testing, release and the GitHub updater.
Load it before working on this plugin. This file holds only what is specific to Quick WP Lang.

## Project Overview

Quick WP Lang sets the language of individual posts, pages and custom post types for SEO, and links translations to their original, without a full multilingual setup. On singular views it overrides `<html lang>`, outputs reciprocal hreflang tags, sets og:locale (and Yoast SEO's og:locale and schema language), and optionally sends a `Content-Language` header. It never translates content, copies posts or changes URLs.

- **Namespace:** `Quick_WP_Lang` · **Text domain:** `quick-wp-lang` · **Prefixes:**
  `quick_wp_lang_` (filters), `qwl_` (options), `_qwl_` (post meta), `QUICK_WP_LANG_` (constants)
- **PHP / WordPress floor:** 8.2 / 6.9, the skill's default
- **No build system:** no npm, no Composer, no bundler. Assets are plain CSS/JS

## Testing

There are no harnesses in `dev-notes/testing/` yet (see `dev-notes/00-project-tracker.md`).
Until there are, exercise behaviour against the dev site through WP-CLI and `curl`:

```bash
# Front-end output for a page (add a throwaway query string to get past the page cache)
curl -s "https://example.test/a-propos/?nocache=$(date +%s)" | grep -E '<html|hreflang|og:locale|canonical'
curl -sI "https://example.test/a-propos/?nocache=$(date +%s)" | grep -i content-language

# Save through the meta box handler as an administrator
wp eval 'wp_set_current_user( 1 ); $_POST = array( ... ); ( new Quick_WP_Lang\Admin_Hooks() )->save_meta_box( 123, get_post( 123 ) );'
```

Reset the state you touch afterwards (test posts, `wp option delete`), test the defensive path
(junk arguments to hook callbacks, a bad nonce, an invalid original), and run destructive checks
such as `uninstall.php` inside a database transaction that is rolled back.

## Architecture

### Entry Point & Initialization

`quick-wp-lang.php` → `quick_wp_lang_run()` → `Plugin::instance()->run()` (singleton). Classes are loaded with `require_once` (no autoloader). `Plugin::run()` registers the meta keys on `init`, creates `Settings` straight away and registers the admin or the front-end hooks; `Admin_Hooks` and `Public_Hooks` are created on first use.

### Key Files

| File | Purpose |
|------|---------|
| `quick-wp-lang.php` | Plugin header, path constants, class loading, updater bootstrap |
| `constants.php` | All constants: option and meta keys, defaults, field names, handles, limits |
| `functions-private.php` | Internal helpers: language lists, supported post types, a post's language and original, BCP 47 conversion. Private to the plugin |
| `includes/class-plugin.php` | Singleton bootstrap: hook registration, meta registration and its sanitisers, list-table columns, Settings link |
| `includes/class-settings.php` | Settings page, option registration and sanitisation |
| `includes/class-admin-hooks.php` | Content Language meta box (language, Translation of, Translations list), save validation, Language column, no-languages notice |
| `includes/class-public-hooks.php` | Front-end output: lang attribute, hreflang group, og:locale, Content-Language, Yoast filters |
| `includes/class-github-updater.php` | The shared updater, ported from Quick 2FA. Never edit it here; see the skill's `github-updater.md` |
| `uninstall.php` | Deletes data by prefix, only when the delete-data setting is on |
| `assets/admin/` | `admin.css`, `admin.js` (notice dismiss), `meta-box.js`, `settings.js` (language search) |

### Translation Groups

A group is one **original** (a post with no language override) plus the posts whose `_qwl_translation_of` holds its ID. `Public_Hooks::get_hreflang_alternates()` builds the tags: the original under the site locale, each published translation (the oldest wins a language), and `x-default` → original. An unlinked post with a language gets one self-referencing tag. `Admin_Hooks::is_valid_original()` enforces the rules on save: same post type, not itself, not trashed, no language of its own. `docs/how-it-works.md` is the user-facing description; keep the two in step.

### Data Storage

- **Post meta:** `_qwl_language` (locale), `_qwl_translation_of` (original's ID). Both absent rather than empty for the default. Registered for every post type with `show_in_rest` off; the sanitisers keep a language to the enabled list and an ID to a non-negative integer, and the meta box applies the group rules on top
- **`wp_options`:** `qwl_enabled_languages`, `qwl_enabled_post_types`, `qwl_enable_content_language_header`, `qwl_delete_data_on_uninstall`, `qwl_dismissed_notices`
- **Every stored key carries the plugin prefix:** `OPTION_PREFIX` (`qwl_`) for options, `META_PREFIX` (`_qwl_`) for post meta. `uninstall.php` finds keys by prefix, so a key without one survives an uninstall
- **Uninstall deletes nothing by default.** Only with *Delete all plugin data when uninstalled* (`OPTION_DELETE_DATA_ON_UNINSTALL`) does `uninstall.php` remove options and post meta. Deactivation never deletes data

## Public Contracts

The skill's `public-contracts.md` has the rules. This plugin's contracts:

| Contract | Examples | Breaks when |
|----------|----------|-------------|
| Filters | `quick_wp_lang_languages`, `quick_wp_lang_supported_post_types`, `quick_wp_lang_updater_enabled` | renamed or removed, or an argument is removed or reordered |
| Stored key strings | `_qwl_language`, `_qwl_translation_of`, `qwl_enabled_languages` | a constant's **value** changes. Data stored under the old string is ignored |
| Front-end output | `<html lang>`, hreflang set, `og:locale`, `Content-Language` | the set of tags for a given configuration changes without a changelog entry. Sites' SEO depends on it |
| Script and style handles | `quick-wp-lang-admin`, `quick-wp-lang-meta-box`, `quick-wp-lang-settings` | renamed |
| Settings page slug | `options-general.php?page=qwl-settings` | `SETTINGS_PAGE_SLUG` changes |

Filters carry the `quick_wp_lang_` prefix, not `qwl_`.

### Where the Plugin Runs

- **Request context.** Front-end hooks are registered only when `! is_admin()`; check `is_singular()` before reading the queried post
- **Page caches.** Output depends on post meta of *other* posts in a group: saving a translation changes the original's tags, which a page cache won't know to clear. Mention it wherever output changes
- **Multisite is untested.** Settings and post meta are per-site

## Release

Follow the skill's `release.md` (public). The version lives in the `Version:` header and
`QUICK_WP_LANG_VERSION`; `.github/workflows/release.yml` refuses to build unless both match the
tag.

## Reference Files

`docs/` is tracked and public, one audience per document:

- `docs/how-it-works.md`, `docs/configuration.md`, `docs/troubleshooting.md`: site operators
- `docs/developers/hooks-and-filters.md`: the public extension surface
- `docs/developers/extending.md`: practical developer recipes
- `docs/website-post.md`: a write-up of the plugin for the Headwall website

`dev-notes/` (private, untracked):

- `dev-notes/00-project-tracker.md`: milestones and history
- `dev-notes/archive/`: superseded material (the old `AGENTS.md`, `copilot-instructions.md`,
  `patterns/` and `workflows/` guides). Archived, not authoritative

## Translations

Run `wp-translate . --check-instructions` after a `wp-translate` upgrade; if it reports drift,
run `wp-translate . --sync-instructions` and review the diff. Never hand-edit inside the
`wp-translate:begin`/`end` markers: the block is hash-validated and edits break it.

<!-- wp-translate:begin v=1.2.0 hash=558d5d78ac53bac7a095c70d28fceaf6cf81c3557ebc5ba23f78613c0fcebeda -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'quick-wp-lang' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'quick-wp-lang' );
_x( 'Folder', 'IMAP mailbox', 'quick-wp-lang' );
_x( 'Open', 'verb; button label', 'quick-wp-lang' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'quick-wp-lang' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Use `_n()` for anything that can be counted

Never build a count-dependent sentence by hand, and never settle for a single
form that reads correctly only for one number. Languages differ in how many
plural forms they have — English and German have two, French treats 0 as
singular, Polish and Russian have three, Japanese has one, Arabic has six — and
`_n()` is the only way to express that.

```php
// Wrong — "1 reviews", and untranslatable into languages with other forms
printf( esc_html__( '%d reviews', 'quick-wp-lang' ), $count );

// Right — wp-translate fills every form the target locale needs
printf(
    esc_html( _n( '%d review', '%d reviews', $count, 'quick-wp-lang' ) ),
    $count
);
```

Keep the placeholder in **both** forms, even when the singular reads fine
without it (`'%d review'`, not `'One review'`) — some locales use the singular
slot for other numbers too.

For a short or ambiguous countable noun, use `_nx()` — the plural equivalent of
`_x()` — so the context reaches DeepL:

```php
// "Review" alone is ambiguous: critique? opinion? inspection?
_nx( '%d review', '%d reviews', $count, 'customer feedback on a company', 'quick-wp-lang' );
```

**Locales needing more than two forms will have their extra slots left empty for
a human translator.** DeepL supplies a singular and a plural; nobody can invent
Polish's third form from those, and wp-translate deliberately leaves it blank
rather than filling it with a plausible guess. Expect to see empty
`msgstr[2]` entries in `pl_PL` — that is correct behaviour, not a failure.

### 4. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 5. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 6. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
