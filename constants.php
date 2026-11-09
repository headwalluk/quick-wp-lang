<?php
/**
 * Plugin constants.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Prefixes shared by every option and post meta key the plugin stores.
 *
 * The uninstaller deletes by prefix, so a new key is covered as long as it uses one.
 *
 * @since 1.3.0
 */
const OPTION_PREFIX = 'qwl_';
const META_PREFIX   = '_qwl_';

/**
 * Post meta keys. A key's value is stored data and never changes.
 *
 * @since 1.0.0
 */
const META_LANGUAGE = '_qwl_language';

/**
 * Options. An option's name is stored data and never changes.
 *
 * @since 1.0.0
 */
const OPTION_ENABLED_LANGUAGES              = 'qwl_enabled_languages';
const OPTION_ENABLED_POST_TYPES             = 'qwl_enabled_post_types';
const OPTION_ENABLE_CONTENT_LANGUAGE_HEADER = 'qwl_enable_content_language_header';
const OPTION_DISMISSED_NOTICES              = 'qwl_dismissed_notices';

/**
 * Defaults.
 *
 * @since 1.0.0
 */
const DEFAULT_LANGUAGE                       = ''; // Empty = site default.
const DEFAULT_POST_TYPES                     = array( 'post', 'page' );
const DEFAULT_ENABLE_CONTENT_LANGUAGE_HEADER = true;

/**
 * Nonces.
 *
 * @since 1.0.0
 */
const NONCE_ACTION_META_BOX       = 'qwl_meta_box_save';
const NONCE_NAME_META_BOX         = 'qwl_meta_box_nonce';
const NONCE_ACTION_DISMISS_NOTICE = 'quick_wp_lang_dismiss_notice';

/**
 * Admin notices and AJAX.
 *
 * @since 1.3.0
 */
const AJAX_ACTION_DISMISS_NOTICE = 'quick_wp_lang_dismiss_notice';
const NOTICE_NO_LANGUAGES        = 'no_languages';

/**
 * Meta box, form field and list-table column identifiers.
 *
 * @since 1.3.0
 */
const META_BOX_ID     = 'quick_wp_lang_language_meta_box';
const FIELD_LANGUAGE  = 'quick_wp_lang_language';
const COLUMN_LANGUAGE = 'quick_wp_lang_language';

/**
 * Settings page identifiers.
 *
 * @since 1.0.0
 */
const SETTINGS_PAGE_SLUG    = 'qwl-settings';
const SETTINGS_OPTION_GROUP = 'qwl_settings_group';
const SETTINGS_SECTION_ID   = 'qwl_languages_section';

/**
 * Script and style handles.
 *
 * @since 1.3.0
 */
const HANDLE_SETTINGS_SCRIPT = 'quick-wp-lang-settings';
const HANDLE_ADMIN_SCRIPT    = 'quick-wp-lang-admin';
const HANDLE_ADMIN_STYLE     = 'quick-wp-lang-admin';

/**
 * Hook priorities.
 *
 * @since 1.0.0
 */
const PRIORITY_LANGUAGE_ATTRIBUTES = 999;
const PRIORITY_WP_HEAD             = 1;

/**
 * GitHub repository the updater checks for releases.
 *
 * @since 1.3.0
 */
const UPDATER_GITHUB_REPO = 'headwalluk/quick-wp-lang';
