<?php
if (!defined('ABSPATH')) exit;

/** Display & behavior */
define('RHCJC_AS_BULLET_COUNT', 5);     // target bullets (generator may return 4–5)
define('RHCJC_AS_MIN_CHARS',  400);     // skip tiny blurbs; editor can regen later
define('RHCJC_AS_MAX_CHARS', 15000);    // cap request size to keep latency/cost low
define('RHCJC_AS_AUTO_INSERT', false);  // if true, prepend box to content; else use shortcode
define('RHCJC_AS_CSS_HANDLE', 'rhcjc-as-css');

/** Grounding & validation */
define('RHCJC_AS_MIN_EVIDENCE_MATCHES', 1); // each bullet must have ≥1 evidence span present in article
define('RHCJC_AS_MAX_BULLETS', 6);          // strict upper bound accepted from model

/** OpenAI defaults (can be overridden in Settings) */
define('RHCJC_AS_DEFAULT_MODEL', 'gpt-4o-mini'); // solid quality/cost; you can set 'gpt-4.1' etc.
define('RHCJC_AS_TEMPERATURE', 0.0);
define('RHCJC_AS_MAX_TOKENS', 400);        // ~ 150–200 words JSON
define('RHCJC_AS_HTTP_TIMEOUT', 60);

/** Meta & options */
define('RHCJC_AS_META_SUMMARY',     '_rhcjc_ab_summary');      // array of bullets
define('RHCJC_AS_META_EVIDENCE',    '_rhcjc_ab_evidence');     // parallel array of evidence arrays
define('RHCJC_AS_META_PROVIDER',    '_rhcjc_ab_provider');     // 'openai'
define('RHCJC_AS_META_MODEL',       '_rhcjc_ab_model');        // model name
define('RHCJC_AS_META_HASH',        '_rhcjc_ab_hash');         // md5 of cleaned content
define('RHCJC_AS_META_GENERATED_AT','_rhcjc_ab_generated_at'); // datetime
define('RHCJC_AS_META_OVERRIDE',    '_rhcjc_ab_override');     // editor manual override (string)

define('RHCJC_AS_OPTION_KEY',       'rhcjc_as_openai_key');    // stored encrypted-ish (see security)
define('RHCJC_AS_OPTION_MODEL',     'rhcjc_as_openai_model');
define('RHCJC_AS_OPTION_AUTOINSERT','rhcjc_as_autoinsert');

define('RHCJC_AS_LOG_FILE',         WP_CONTENT_DIR . '/rhcjc-as-debug.log');
