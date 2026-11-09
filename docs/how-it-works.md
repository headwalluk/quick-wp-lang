# How it works

Quick WP Lang is for a site that runs in one language but has some content in others: an English (UK) site with a few pages in French, say. It isn't a translation plugin. It doesn't translate anything, create copies of posts or change URLs. It tells browsers and search engines which language each page is in, and which pages are versions of each other.

## The two things you set on a post

In the **Content Language** box in the editor:

1. **Language.** Leave it on *Site default*, or pick one of the languages enabled on the settings page.
2. **Translation of.** Only shown once a language is picked. Choose the page in the site's own language that this content translates. Leave it on *Not linked* for content that has no counterpart.

## What appears on the front end

Everything below applies to single posts, pages and custom post types (`is_singular()`). Archives, the blog index and search results are left alone.

### A post with a language set

For a page set to French (France):

```html
<html lang="fr-FR">
<link rel="alternate" hreflang="fr-FR" href="https://example.com/a-propos/" />
<meta property="og:locale" content="fr_FR" />
```

and the HTTP response carries `Content-Language: fr-FR`, if that setting is on.

WordPress stores locales as `fr_FR`; HTML and HTTP use BCP 47 tags (`fr-FR`), so the plugin swaps the underscore for a hyphen. `og:locale` keeps the underscore, as the Open Graph format requires.

### A translation linked to its original

Link the French page to the English page it translates, and both pages list every version:

```html
<link rel="alternate" hreflang="en-GB" href="https://example.com/about/" />
<link rel="alternate" hreflang="fr-FR" href="https://example.com/a-propos/" />
<link rel="alternate" hreflang="x-default" href="https://example.com/about/" />
```

The same three tags appear on the English page and on the French page. Search engines only trust hreflang that both pages agree on, which is why the original lists its translations too.

- The original is listed under the **site's language** (Settings → General → Site Language).
- `x-default` points at the original. It is the page offered to visitors whose language matches none of the versions.
- A page can have any number of translations, one per language.

### What makes a group

A group is one **original** plus the posts linked to it.

- The original has no language of its own (*Site default*). The editor only offers such posts, and saving rejects anything else.
- The original and its translations are the same post type.
- Only **published** posts appear in the tags. A draft translation, or a translation whose original is unpublished or trashed, falls back to the single self-referencing tag.
- If two published translations share a language, the older one (lower post ID) is used and the newer is left out. The original's Content Language box flags the one that is ignored.
- Links are one level deep: a translation can't be the original of another post.

## Canonical URLs and sitemaps

Each version keeps its **own** canonical URL, which is what WordPress and SEO plugins output by default. Don't set a translation's canonical to the original: a canonical tells search engines the page is a duplicate to be dropped from the index, so the translation would stop appearing in search results. Hreflang is the signal that says "this is the French version of that page".

For the same reason, leave translations in the XML sitemap.

## Working with SEO plugins

| Plugin | og:locale | Schema `inLanguage` |
|--------|-----------|----------------------|
| None | Output by Quick WP Lang | — |
| Yoast SEO | Yoast's own tag, given the post's locale through `wpseo_locale` | The post's language on every piece except `WebSite`, through `wpseo_schema_piece_language` |
| Rank Math, All in One SEO | Left to the SEO plugin, which outputs the site locale | Left to the SEO plugin |

None of these SEO plugins outputs hreflang tags on its own, so there is nothing to conflict with. A full multilingual plugin (WPML, Polylang, TranslatePress and the like) does output them; don't run Quick WP Lang alongside one.

## What is stored

| Where | Key | Holds |
|-------|-----|-------|
| Post meta | `_qwl_language` | The post's locale, e.g. `fr_FR`. Absent for the site default |
| Post meta | `_qwl_translation_of` | The original's post ID. Absent when not linked |
| Option | `qwl_enabled_languages` | Locales ticked on the settings page |
| Option | `qwl_enabled_post_types` | Post types ticked on the settings page |
| Option | `qwl_enable_content_language_header` | Whether to send `Content-Language` |
| Option | `qwl_delete_data_on_uninstall` | Whether uninstalling deletes all of the above |
| Option | `qwl_dismissed_notices` | Admin notices dismissed |

These are internal. Code outside the plugin should use the hooks in [developers/hooks-and-filters.md](developers/hooks-and-filters.md).

## Deactivating and deleting

Deactivating the plugin stops all output and keeps all data. Deleting it from the Plugins screen keeps all data too, unless **Delete all plugin data when uninstalled** is ticked (see [configuration.md](configuration.md#uninstall)).
