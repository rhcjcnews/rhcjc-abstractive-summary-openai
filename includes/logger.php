<?php
if (!defined('ABSPATH')) exit;

function rhcjc_as_log($msg, $data=null) {
  if (!defined('WP_DEBUG') || !WP_DEBUG) return;
  $ts = date('Y-m-d H:i:s');
  $line = "[$ts] $msg";
  if (!is_null($data)) {
    if (is_string($data)) $line .= " :: $data";
    else $line .= " :: " . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
  }
  error_log('[RHCJC-AS] '.$line);
  @file_put_contents(RHCJC_AS_LOG_FILE, "[RHCJC-AS] $line\n", FILE_APPEND);
}
