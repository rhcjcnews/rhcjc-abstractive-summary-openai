<?php
if (!defined('ABSPATH')) exit;

function rhcjc_as_mask($str) {
  if (!$str) return '';
  $len = strlen($str);
  return str_repeat('•', max(0, $len-6)) . substr($str, -6);
}

function rhcjc_as_now() {
  return current_time('mysql');
}

function rhcjc_as_content_hash($text) {
  return md5($text);
}

function rhcjc_as_sanitize_bullets($arr) {
  $out = [];
  foreach ((array)$arr as $b) {
    $t = trim(wp_strip_all_tags((string)$b, true));
    if ($t !== '') $out[] = $t;
    if (count($out) >= RHCJC_AS_MAX_BULLETS) break;
  }
  return $out;
}
