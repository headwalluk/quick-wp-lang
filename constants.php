<?php
/**
 * Plugin constants.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

// Post meta key for language override.
const META_KEY_LANGUAGE = '_qwl_language';

// Nonce actions.
const NONCE_ACTION_META_BOX = 'qwl_meta_box_save';
const NONCE_NAME_META_BOX   = 'qwl_meta_box_nonce';

// Default values.
const DEF_LANGUAGE = ''; // Empty = site default.

// Options keys.
const OPT_ENABLED_LANGUAGES              = 'qwl_enabled_languages';
const OPT_ENABLE_CONTENT_LANGUAGE_HEADER = 'qwl_enable_content_language_header';

// Settings page identifiers.
const SETTINGS_PAGE_SLUG    = 'qwl-settings';
const SETTINGS_OPTION_GROUP = 'qwl_settings_group';
const SETTINGS_SECTION_ID   = 'qwl_languages_section';

// Nonce for settings page.
const NONCE_ACTION_SETTINGS = 'qwl_settings_save';
const NONCE_NAME_SETTINGS   = 'qwl_settings_nonce';

// Admin notice options.
const OPT_DISMISSED_NOTICES = 'qwl_dismissed_notices';

// Filter/action hook priorities.
const PRIORITY_LANGUAGE_ATTRIBUTES = 999;
const PRIORITY_WP_HEAD             = 1;
