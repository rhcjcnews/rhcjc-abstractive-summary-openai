<?php
if (!defined('ABSPATH')) exit;

function rhcjc_as_get_rendered_text($post_id) {
  $post = get_post($post_id);
  if (!$post) return '';

  // Avoid recursion with our filter
  remove_filter('the_content', 'rhcjc_as_maybe_auto_insert', 5);
  $rendered = apply_filters('the_content', $post->post_content);
  add_filter('the_content', 'rhcjc_as_maybe_auto_insert', 5);

  $text = wp_strip_all_tags($rendered, true);
  $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $text = preg_replace('/\s+/', ' ', $text);
  $text = trim($text);

  $len = mb_strlen($text, 'UTF-8');
  if ($len > RHCJC_AS_MAX_CHARS) {
    $text = mb_substr($text, 0, RHCJC_AS_MAX_CHARS, 'UTF-8');
  }
  rhcjc_as_log('cleaned_text_len', ['len'=>mb_strlen($text, 'UTF-8')]);
  return $text;
}
