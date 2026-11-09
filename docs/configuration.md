# Configuration

## Settings → Content Language

### Content-Language Header

**Send Content-Language HTTP header for posts with alternative languages.** On by default. Sends `Content-Language: fr-FR` (for example) on posts with a language set. Turn it off if something else on the server already sets the header.

### Enable for Post Types

Which post types show the **Content Language** box in the editor and the **Language** column in the list of posts. Posts and pages until this page is first saved. Untick every post type to hide both everywhere; languages already set on content keep working on the front end.

### Enable Languages

The languages offered in the editor. The site's own language is shown but can't be ticked, since content in it needs no override. Type in the search box to filter the list by name or locale code; enabled languages are listed first.

Disabling a language doesn't remove it from content that already uses it. That content keeps its language on the front end, but the next time it is saved in the editor its language resets to *Site default*.

### Uninstall

**Delete all plugin data when uninstalled.** Off by default. When ticked, deleting the plugin from the Plugins screen removes its settings and every language and translation link set on content. With it unticked, deleting the plugin keeps everything, so reinstalling picks up where it left off. Deactivating never deletes anything.

## The Content Language box in the editor

Shown in the sidebar of the post types enabled above.

- **Language** — *Site default*, or one of the enabled languages.
- **Translation of** — Appears once a language is picked. Lists posts of the same type that are in the site's language, by title and ID; pick the one this content translates. Up to 500 are listed.
- **Translations** — On an original, lists the content linked to it, with links to edit each one. A translation marked *not used* shares its language with an older published translation and is left out of the hreflang tags.

See [how-it-works.md](how-it-works.md) for what each choice outputs.

## The Language column

The list of posts for each enabled post type gets a sortable **Language** column showing each post's locale code, or a dash for the site default.

## Updates

The plugin checks GitHub for new releases and offers them on the Plugins screen and through `wp plugin update`, like any other plugin. To turn that off, see [developers/extending.md](developers/extending.md#disable-automatic-updates).
