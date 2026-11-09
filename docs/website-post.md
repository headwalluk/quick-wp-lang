# Alternative-language for WordPress posts

Assign alternative languages to individual posts, pages, and custom post types without requiring a full multilingual setup. Link a translation to its original, and both pages get proper hreflang tags pointing at each other.

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

Quick WP Lang adds a simple meta box to the post editor with a radio button list of the languages you've enabled. Select a language (or keep "Site Default"), save the post, and the plugin handles the rest. If the page is a translation of an existing page, pick that page in the **Translation of** field too.

On the front end, for posts with an assigned language, the plugin:

1. **Overrides the `<html lang="">` attribute** — Changes from site default (e.g., `en-GB`) to the post's language (e.g., `pl-PL`)
2. **Outputs hreflang tags** — A single self-referencing tag for standalone content, or the full set of versions for a linked translation (see below)
3. **Sets the Content-Language HTTP header** — Reinforces language metadata at the protocol level (optional, configurable)
4. **Overrides og:locale for social sharing** — Ensures Facebook and other platforms display correct language metadata

## Linking translations

Ad-hoc content in another language isn't always a one-off. Another client, with an en-GB site, wanted a few of her pages available in French as well. Her first question was whether the French pages should be kept out of the sitemap, or given a canonical URL pointing at the English pages.

Neither, as it turns out. A canonical URL tells search engines "this page is a duplicate — index that one instead", so the French pages would drop out of the index altogether. And leaving them out of the sitemap just makes them harder to find. What you actually want to say is "this is the French version, and the English version is over here". That's exactly what hreflang is for.

So the plugin now lets you link a translation to its original. Set the French page's language, pick the English page under **Translation of**, and *both* pages output the same set of tags:

```html
<link rel="alternate" hreflang="en-GB" href="https://example.com/about/" />
<link rel="alternate" hreflang="fr-FR" href="https://example.com/a-propos/" />
<link rel="alternate" hreflang="x-default" href="https://example.com/about/" />
```

The reciprocity matters: search engines ignore hreflang that only one side of the pair declares. Each page keeps its own canonical URL, both stay in the sitemap, and `x-default` points visitors who don't match either language at the original. An original can have as many translations as you like, one per language, and its meta box lists them all.

### Still not a translation plugin

There's no translation, no duplicated posts, no language switcher and no URL rewriting. You write the French page yourself, as a normal WordPress page, and the plugin just tells search engines how it relates to the English one. Content that has no counterpart — like the Polish pages above — simply isn't linked to anything.

## SEO compatibility

None of the big SEO plugins output hreflang tags themselves, so there's nothing to conflict with. With **Yoast SEO**, the plugin hands Yoast the post's language, so Yoast's own `og:locale` tag and the `inLanguage` in its schema both match the content. With Rank Math or All in One SEO, the plugin leaves `og:locale` to them. It also handles the WordPress locale to BCP 47 format conversion automatically (e.g., `de_DE` becomes `de-DE` for hreflang tags).

## Settings and control

Under **Settings → Content Language**, you can:

- **Enable only the languages you need** — Keeps the meta box clean and focused
- **Choose which post types show the meta box** — Posts, pages, WooCommerce products, or custom post types
- **Toggle the Content-Language HTTP header** — Some CDNs cache based on this header, so it's configurable
- **Decide what happens on uninstall** — Your language settings are kept when the plugin is deleted, unless you tick the box to remove them

The settings page includes a search filter for quickly finding languages by name or locale code, and enabled languages automatically float to the top of the list.

## Developer-friendly

The plugin is built with extensibility in mind:

- **Filter hooks** — `quick_wp_lang_languages` for customising the language list, `quick_wp_lang_supported_post_types` for controlling which post types get the meta box, and `quick_wp_lang_updater_enabled` for turning off automatic updates
- **Automatic updates** — New releases arrive straight from GitHub, like any other plugin update
- **No build step** — Pure PHP and a little plain JavaScript, no compilation required
- **Follows WordPress Coding Standards** — Clean, readable, maintainable code
- **WooCommerce-friendly** — Works with products, and never touches orders, so HPOS isn't a concern

With this little plugin, my first client was able to focus on her core business content for SEO, while leveraging her Polish language content on specific pages (that don't need English translations). And for the French pages, it does the one job a full multilingual plugin is really needed for — telling search engines which pages are versions of each other — without any of the overhead.
