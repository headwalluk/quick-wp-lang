# Alternative-language for WordPress posts

Assign alternative languages to individual posts, pages, and custom post types without requiring a full multilingual setup. Automatically adjusts hreflang tags and language attributes for better SEO.

Multi-language plugins have always been troublesome, and running a properly multi-lingual website is super time-consuming, and an SEO nightmare. As an engineer, I always naturally lean towards choosing a simpler solution, and implementing it to a higher standard.

## The multilingual plugin problem

Full multilingual setups come with significant overhead:

- **Content duplication** — Every piece of content needs translation, creating maintenance burden
- **Database bloat** — Additional tables and post meta for language relationships
- **Plugin conflicts** — Multilingual plugins often clash with SEO plugins, caching layers, and URL structures
- **Performance impact** — Language switchers, redirects, and duplicate detection add processing time
- **SEO complexity** — Managing hreflang clusters, canonical URLs, and language fallbacks is error-prone

For a site that's primarily in one language with just a handful of alternative-language pages, this is massive overkill.

## A client case study

When a client was having trouble with their SEO (while using a multi-language plugin), we decided to switch the site back to fully English (she is based in the UK) and create ad-hoc Polish content instead. The Google Search Console supported this move, as the original "pl-PL" content was not ranking in GSC at all.

The problem: without per-page language metadata, search engines see every page as the site's default language (en-GB). Those Polish pages were invisible to Polish-language searches.

The solution: a lightweight plugin that assigns language metadata to individual posts, with proper SEO markup.

![Ad-hoc content that's in a different language to the site's main language, with proper SEO markup](https://cdn.headwall-hosting.com/wp-content/uploads/2026/02/polski-tak.webp)

## How it works

Quick WP Lang adds a simple meta box to the post editor with a radio button list of 50+ common languages. Select a language (or keep "Site Default"), save the post, and the plugin handles the rest.

On the front end, for posts with an assigned language, the plugin:

1. **Overrides the `<html lang="">` attribute** — Changes from site default (e.g., `en-GB`) to the post's language (e.g., `pl-PL`)
2. **Outputs a self-referencing hreflang tag** — `<link rel="alternate" hreflang="pl-PL" href="..." />` tells search engines this content exists only in Polish
3. **Sets the Content-Language HTTP header** — Reinforces language metadata at the protocol level (optional, configurable)
4. **Overrides og:locale for social sharing** — Ensures Facebook and other platforms display correct language metadata

### Important distinction

This is **not** a translation plugin. There's no concept of "the same content in multiple languages". When a post is assigned Polish, that content exists *only* in Polish — there's no English counterpart. This is for ad-hoc alternative language provision, not full translation management.

## SEO compatibility

The plugin detects Yoast SEO, Rank Math, and All in One SEO, and defers to them for `og:locale` output to avoid conflicts. It also handles the WordPress locale to BCP 47 format conversion automatically (e.g., `de_DE` becomes `de-DE` for hreflang tags).

## Settings and control

Under **Settings → Content Language**, you can:

- **Enable only the languages you need** — Keeps the meta box clean and focused
- **Choose which post types show the meta box** — Posts, pages, WooCommerce products, or custom post types
- **Toggle the Content-Language HTTP header** — Some CDNs cache based on this header, so it's configurable

The settings page includes a search filter for quickly finding languages by name or locale code, and enabled languages automatically float to the top of the list.

## Developer-friendly

The plugin is built with extensibility in mind:

- **Two filter hooks** — `quick_wp_lang_languages` for customising the language list, and `quick_wp_lang_supported_post_types` for controlling which post types get the meta box
- **No build step** — Pure PHP, no JavaScript compilation required
- **Follows WordPress Coding Standards** — Clean, readable, maintainable code
- **HPOS compatible** — Works with WooCommerce's High-Performance Order Storage

With this little plugin, my client was able to focus on her core business content for SEO, while leveraging her Polish language content on specific pages (that don't need English translations). A nice lightweight way to target regional search, in a language that's not native to the geographic region.
