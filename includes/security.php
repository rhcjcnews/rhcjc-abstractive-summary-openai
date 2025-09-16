<?php
if (!defined('ABSPATH')) exit;

/**
 * API key resolution order:
 * 1) constant RHCJC_OPENAI_KEY in wp-config.php (best)
 * 2) env var RHCJC_OPENAI_KEY
 * 3) option in DB (stored obfuscated; still treat DB as sensitive)
 */
function rhcjc_as_get_api_key() {
  if (defined('RHCJC_OPENAI_KEY') && RHCJC_OPENAI_KEY) return RHCJC_OPENAI_KEY;
  $env = getenv('RHCJC_OPENAI_KEY');
  if ($env) return $env;

  $opt = get_option(RHCJC_AS_OPTION_KEY, '');
  if (!$opt) return '';
  // very light obfuscation using AUTH_SALT (not true encryption; reduces casual leaks)
  if (!defined('AUTH_SALT')) return $opt;
  $decoded = base64_decode($opt);
  $key = hash('sha256', AUTH_SALT, true);
  $iv  = substr(hash('sha256', 'rhcjc_as_iv'), 0, 16);
  $plain = openssl_decrypt($decoded, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return $plain ?: '';
}

function rhcjc_as_set_api_key($plain) {
  if (!current_user_can('manage_options')) return false;
  $plain = trim($plain);
  if ($plain === '') return delete_option(RHCJC_AS_OPTION_KEY);
  if (!defined('AUTH_SALT')) return update_option(RHCJC_AS_OPTION_KEY, $plain, false);

  $key = hash('sha256', AUTH_SALT, true);
  $iv  = substr(hash('sha256', 'rhcjc_as_iv'), 0, 16);
  $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return update_option(RHCJC_AS_OPTION_KEY, base64_encode($cipher), false);
}
