# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Quick WP Lang sets the language of individual posts, pages and custom post types for SEO, and links translations to their original, without a full multilingual setup. On singular views it overrides `<html lang>`, outputs reciprocal hreflang tags, sets og:locale (and Yoast SEO's og:locale and schema language), and optionally sends a `Content-Language` header. It never translates content, copies posts or changes URLs.

- **Namespace:** `Quick_WP_Lang` for all classes and functions
- **Text Domain:** `quick-wp-lang`
- **PHP:** 8.2+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop)
- **WordPress:** 7.1+
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS. Do not install Composer; `phpcs` and `phpcbf` are installed globally

Structure, conventions and patterns come from the maintainer's reference plugin, **Quick 2FA**
(`../quick-2fa/`, its `CLAUDE.md`). Where this file is silent, that one is the standard.

This plugin is published publicly on GitHub. Tracked files must contain no client names,
client URLs or client data of any kind — in code, comments, docs, fixtures or commit messages.

`dev-notes/` is **private and untracked** (`.gitignore`), backed up with the dev site, and
blocked from the web by `dev-notes/.htaccess`. It is the right home for client-identifying
material. Never copy content from `dev-notes/` into a tracked file without scrubbing it, and
never reference a `dev-notes/` path from a file that ships in the release zip.

## Commands

```bash
phpcs                          # Check WordPress Coding Standards (configured in phpcs.xml)
phpcbf                         # Auto-fix coding standards violations
phpcs includes/class-plugin.php  # Check a specific file
```

```bash
wp-translate . --check-instructions   # Is the block at the end of this file still current?
wp-translate . --sync-instructions    # Update it; review the diff afterwards
wp-translate . --dry-run              # Preview; no DeepL calls, no writes
```

Run `--check-instructions` after a `wp-translate` upgrade; if it reports drift, run
`--sync-instructions` and review the diff. Never hand-edit inside the
`wp-translate:begin`/`end` markers — the block is hash-validated and edits break it.

## Testing

There is no unit-test framework and none is wanted. Behaviour is exercised against the live
dev site through WP-CLI and `curl`:

```bash
# Front-end output for a page (add a throwaway query string to get past the page cache)
curl -s "https://example.test/a-propos/?nocache=$(date +%s)" | grep -E '<html|hreflang|og:locale|canonical'
curl -sI "https://example.test/a-propos/?nocache=$(date +%s)" | grep -i content-language

# Save through the meta box handler as an administrator
wp eval 'wp_set_current_user( 1 ); $_POST = array( ... ); ( new Quick_WP_Lang\Admin_Hooks() )->save_meta_box( 123, get_post( 123 ) );'
```

Rules: reset the state you touch afterwards (test posts, `wp option delete`), test the
**defensive** path as well as the happy one (junk arguments to hook callbacks, a bad nonce, an
invalid original), and run destructive checks such as `uninstall.php` inside a database
transaction that is rolled back.

## Architecture

### Entry Point & Initialization

`quick-wp-lang.php` → `quick_wp_lang_run()` → `Plugin::instance()->run()` (singleton). Classes are loaded with `require_once` (no autoloader). `Plugin::run()` creates `Settings` straight away and registers the admin or the front-end hooks; `Admin_Hooks` and `Public_Hooks` are created on first use.

### Key Files

| File | Purpose |
|------|---------|
| `quick-wp-lang.php` | Plugin header, path constants, class loading, updater bootstrap |
| `constants.php` | All constants: option and meta keys, defaults, field names, handles, limits |
| `functions-private.php` | Internal helpers: language lists, supported post types, a post's language and original, BCP 47 conversion. Private to the plugin |
| `includes/class-plugin.php` | Singleton bootstrap: hook registration, list-table columns, Settings link |
| `includes/class-settings.php` | Settings page, option registration and sanitisation |
| `includes/class-admin-hooks.php` | Content Language meta box (language, Translation of, Translations list), save validation, Language column, no-languages notice |
| `includes/class-public-hooks.php` | Front-end output: lang attribute, hreflang group, og:locale, Content-Language, Yoast filters |
| `includes/class-github-updater.php` | Copy of Quick 2FA's updater; see **GitHub updater** |
| `uninstall.php` | Deletes data by prefix, only when the delete-data setting is on |
| `assets/admin/` | `admin.css`, `admin.js` (notice dismiss), `meta-box.js`, `settings.js` (language search) |

### Translation Groups

A group is one **original** (a post with no language override) plus the posts whose `_qwl_translation_of` holds its ID. `Public_Hooks::get_hreflang_alternates()` builds the tags: the original under the site locale, each published translation (the oldest wins a language), and `x-default` → original. An unlinked post with a language gets one self-referencing tag. `Admin_Hooks::is_valid_original()` enforces the rules on save: same post type, not itself, not trashed, no language of its own. `docs/how-it-works.md` is the user-facing description; keep the two in step.

### Data Storage

- **Post meta:** `_qwl_language` (locale), `_qwl_translation_of` (original's ID). Both absent rather than empty for the default
- **`wp_options`:** `qwl_enabled_languages`, `qwl_enabled_post_types`, `qwl_enable_content_language_header`, `qwl_delete_data_on_uninstall`, `qwl_dismissed_notices`
- **Every stored key carries the plugin prefix:** `OPTION_PREFIX` (`qwl_`) for options, `META_PREFIX` (`_qwl_`) for post meta. `uninstall.php` finds keys by prefix, so a key without one survives an uninstall
- **Uninstall deletes nothing by default.** Only with *Delete all plugin data when uninstalled* (`OPTION_DELETE_DATA_ON_UNINSTALL`) does `uninstall.php` remove options and post meta. Deactivation never deletes data
- The PHP-side constant names (`OPTION_*`, `META_*`) are internal; their **values** are stored data

## Public Contracts

Treat everything in this table as a contract, whether or not it is documented as public:

| Contract | Examples | Breaks when |
|----------|----------|-------------|
| Filters | `quick_wp_lang_languages`, `quick_wp_lang_supported_post_types`, `quick_wp_lang_updater_enabled` | renamed or removed, or an argument is removed or reordered |
| Stored key strings | `_qwl_language`, `_qwl_translation_of`, `qwl_enabled_languages` | a constant's **value** changes. Data stored under the old string is ignored |
| Front-end output | `<html lang>`, hreflang set, `og:locale`, `Content-Language` | the set of tags for a given configuration changes without a changelog entry. Sites' SEO depends on it |
| Script and style handles | `quick-wp-lang-admin`, `quick-wp-lang-meta-box`, `quick-wp-lang-settings` | renamed |
| Settings page slug | `options-general.php?page=qwl-settings` | `SETTINGS_PAGE_SLUG` changes |

- **Expose behaviour, not storage.** Give integrations hooks rather than meta keys to read
- **Add, don't change.** New filter arguments go at the end. New behaviour gets a new filter
- **Deprecate, don't rename.** Fire the old name through `apply_filters_deprecated()` and pass its result into the new filter
- **Filters are prefixed `quick_wp_lang_`**
- **Stored data has no migration path**, so changing a stored key or value format is a breaking change: stop and ask

Before changing anything in the table: name the contract, assume there are consumers you can't
see, take the additive path if one exists, record it in `CHANGELOG.md`, and if you can't tell
whether anything outside the plugin depends on it, stop and ask.

### Where the Plugin Runs

- **Request context.** Hook callbacks run under WP-CLI, cron, AJAX and REST as well as browser requests. Front-end hooks are registered only when `! is_admin()`; check `is_singular()` before reading the queried post
- **Page caches.** Output depends on post meta of *other* posts in a group: saving a translation changes the original's tags, which a page cache won't know to clear. Mention it wherever output changes
- **Install layout.** Build URLs and paths with `admin_url()`, `get_permalink()`, `QUICK_WP_LANG_URL` and `QUICK_WP_LANG_PATH`, never by joining strings onto the domain
- **Multisite is untested.** Settings and post meta are per-site

## Code Conventions

### PHP Style

- **No `declare(strict_types=1)`**
- **Single-Entry Single-Exit (SESE):** one `return` at the end. Top-of-function guard clauses are acceptable when they keep the rest flat; `return` mid-function, inside a loop or nested is not
- **An `if` with `elseif` branches ends in a plain `else`**, never an `elseif`. A branch that does nothing is written out with a short comment. `phpcs.xml` excludes the `if`/`elseif`/`else` codes of `Generic.CodeAnalysis.EmptyStatement` for this
- **No assignment inside a condition**, and **no unreachable `return`**
- **Constants for all magic strings/numbers** in `constants.php`
- **Type hints and return types** on all functions and class properties
- **Callbacks on hooks the plugin doesn't own take `mixed`**, check each value before use, and pass a value they can't use through unchanged. `Admin_Hooks::add_language_column()` is the pattern
- **Check what a filter returns** — fall back to the default for a malformed value, as `get_supported_post_types()` does
- **Guard object lookups with `instanceof`**, not truthiness (`get_post()`, `get_current_screen()`)
- **Cast at the boundary** — `get_post_meta()` and `get_option()` return loose types; cast at the point of read (`get_translation_of()`)
- **Don't duplicate derived values** — one helper per rule (`get_supported_post_types()`, `should_show_no_languages_notice()`)

### Output

Output uses `printf()`/`echo` with escaping (`esc_html()`, `esc_attr()`, `esc_url()`) — no inline HTML mixed with PHP. No inline `<script>` or `style=""`: JavaScript and CSS live in `assets/` and are enqueued. Inline-script data goes through `wp_localize_script()`.

### CSS and JavaScript

- **Logical properties** for anything with a left or right (`margin-inline-start`, `padding-inline-end`, `text-align: start`). Top and bottom stay physical or use `-block-`
- **Plain JavaScript**, no jQuery

### Logging

`error_log()` with a `phpcs:ignore`; no logging library. Genuine failures log unconditionally; routine tracing only under `WP_DEBUG` (see `Github_Updater::log()` / `log_error()`). Never leave a `catch` that records nothing.

### Comments

- One-line docblock summary per function, saying what it does
- Inline comments only where the mechanism isn't obvious
- Reasoning and history go in `docs/`, not in comments

### Boolean Options

```php
$enabled = (bool) filter_var( get_option( OPTION_ENABLE_CONTENT_LANGUAGE_HEADER, DEFAULT_ENABLE_CONTENT_LANGUAGE_HEADER ), FILTER_VALIDATE_BOOLEAN );
```

### Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`. Commit on `main`.

### Pre-Commit Workflow

1. `phpcs` — check violations
2. `phpcbf` — auto-fix
3. `phpcs` — verify clean: no errors **and no warnings**
4. Stage and commit

Every `phpcs:ignore` and `phpcs:disable` names the exact sniff and ends with `-- reason`.

## GitHub updater

`includes/class-github-updater.php` is a copy of Quick 2FA's, with only the `namespace` and
`@package` lines changed. Never edit it here: fixes go into Quick 2FA first and are copied out.
To check for drift:

```bash
diff <(grep -v '^namespace \|@package ' ../quick-2fa/includes/class-github-updater.php) \
     <(grep -v '^namespace \|@package ' includes/class-github-updater.php)
```

It loads under admin, cron and WP-CLI only, and is disabled by the
`quick_wp_lang_updater_enabled` filter. `uninstall.php` clears its cache with
`Github_Updater::delete_cache()`.

## Release Workflow

1. Update the version in `quick-wp-lang.php` — **both** the `Version:` header and `QUICK_WP_LANG_VERSION`
2. Update `CHANGELOG.md`: move the `[Unreleased]` entries under the new version
3. Update `readme.txt`: stable tag, changelog and upgrade notice
4. Regenerate translations with `wp-translate .` if strings changed
5. Run `phpcs`
6. Tag the release in git (`vX.Y.Z`)

The header `Version:`, `QUICK_WP_LANG_VERSION` and the `readme.txt` stable tag must agree with
the git tag; `.github/workflows/release.yml` refuses to build on a mismatch. **The `Version` must
always correspond to a real GitHub Release tag**, or the updater advertises an update that
doesn't exist.

## Reference Files

`docs/` is the maintained entry point and is tracked/public. **One audience per document:**

- `docs/how-it-works.md`, `docs/configuration.md`, `docs/troubleshooting.md` — site operators
- `docs/developers/hooks-and-filters.md` — the public extension surface
- `docs/developers/extending.md` — practical developer recipes

Supporting material (private, untracked):

- `dev-notes/00-project-tracker.md` — milestones and history
- `dev-notes/archive/` — superseded material (the old `AGENTS.md`, `copilot-instructions.md`, `patterns/` and `workflows/` guides). Archived, not authoritative: **this file is the standard**
- `CHANGELOG.md` — per-version release notes (tracked)

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
