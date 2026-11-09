# Troubleshooting

## Check what a page outputs

```bash
curl -s https://example.com/a-propos/ | grep -E '<html|hreflang|og:locale|rel="canonical"'
curl -sI https://example.com/a-propos/ | grep -i content-language
```

Add a throwaway query string (`?nocache=1`) if the site has a page cache, to be sure you are seeing fresh output.

## I changed a page's language but the front end hasn't changed

A page cache is serving the old copy. Most caching plugins clear a page when it is saved, but a translation group spans several pages: linking a French page to an English one changes the tags on **both**, and only the French page was saved. Clear the cache for the original too, or the whole site.

## The original page doesn't list its translation

Check, in order:

1. The translation is **published**. Drafts, scheduled and private posts are left out.
2. The original is published and set to *Site default*. If the original has a language of its own, the link is ignored.
3. The translation's **Translation of** field still shows the original. Setting the translation back to *Site default* removes the link.
4. No older published translation already uses the same language. The original's Content Language box marks such a translation *not used*.

## The page I want isn't in the Translation of list

The list only offers posts of the same post type, in the site's language, that aren't the post being edited. It is capped at 500 posts, sorted by title.

## There are hreflang tags I didn't expect

Another plugin is adding them, most likely a multilingual plugin. Quick WP Lang isn't designed to run alongside one; deactivate one or the other.

## og:locale shows the site's language on a translated page

Rank Math and All in One SEO output their own `og:locale` and Quick WP Lang leaves it alone, so it shows the site locale. With Yoast SEO, or no SEO plugin, `og:locale` follows the post's language.

## No Content-Language header

Check the setting under **Settings → Content Language**. If it is on, something later in the request (a CDN, a reverse proxy or a security plugin) may be removing or replacing the header.

## The Content Language box doesn't appear in the editor

The post type isn't ticked under **Enable for Post Types**, or code on the site removes it through the `quick_wp_lang_supported_post_types` filter.

## No update notice for a new release

The updater checks GitHub at most every 12 hours, and waits an hour after a failed check. A failed check is written to the PHP error log whether or not `WP_DEBUG` is on, as a line starting `Quick_WP_Lang Github_Updater [error]:`.
