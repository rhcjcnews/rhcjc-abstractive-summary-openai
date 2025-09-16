<?php
if (!defined('ABSPATH')) exit;

// strict first-publish hook (never drafts)
add_action('transition_post_status', function($new, $old, $post){
  if ($new !== 'publish' || $old === 'publish') return;
  if ($post->post_type !== 'post') return;
  if (is_wp_error(rhcjc_as_generate_for_post($post->ID))) {
    // admin notice handled via transient in admin.php
  }
}, 10, 3);

// allow manual regen via save with ?rhcjc_regen=1
add_action('save_post', function($post_id){
  if (wp_is_post_revision($post_id)) return;
  if (!isset($_REQUEST['rhcjc_regen'])) return;
  rhcjc_as_generate_for_post($post_id, true);
}, 20);

function rhcjc_as_generate_for_post($post_id, $force=false) {
  if (is_preview()) return new WP_Error('preview', 'Preview skip.');
  if (!current_user_can('edit_post', $post_id)) return new WP_Error('cap', 'Insufficient capability.');

  $lock_key = "rhcjc_as_lock_$post_id";
  if (get_transient($lock_key)) return new WP_Error('locked', 'Generation already in progress.');
  set_transient($lock_key, 1, 30);

  $existing = get_post_meta($post_id, RHCJC_AS_META_SUMMARY, true);
  $override = get_post_meta($post_id, RHCJC_AS_META_OVERRIDE, true);
  if (!$force && ($override || !empty($existing))) {
    delete_transient($lock_key);
    return new WP_Error('exists', 'Summary already exists.');
  }

  $text = rhcjc_as_get_rendered_text($post_id);
  if (mb_strlen($text,'UTF-8') < RHCJC_AS_MIN_CHARS) {
    set_transient('rhcjc_as_last_error', 'Story too short to summarize yet.', 600);
    delete_transient($lock_key);
    return new WP_Error('short', 'Text too short.');
  }

  // content hash: only regenerate if changed (unless forced)
  $hash = rhcjc_as_content_hash($text);
  $prev_hash = get_post_meta($post_id, RHCJC_AS_META_HASH, true);
  if (!$force && $prev_hash && $prev_hash === $hash) {
    delete_transient($lock_key);
    return new WP_Error('unchanged', 'Content unchanged.');
  }

  $res = rhcjc_as_call_openai($text, RHCJC_AS_BULLET_COUNT);
  if (is_wp_error($res)) {
    set_transient('rhcjc_as_last_error', $res->get_error_message(), 600);
    rhcjc_as_log('generation_error', $res->get_error_message());
    delete_transient($lock_key);
    return $res;
  }

  update_post_meta($post_id, RHCJC_AS_META_SUMMARY,     $res['bullets']);
  update_post_meta($post_id, RHCJC_AS_META_EVIDENCE,    $res['evidence']);
  update_post_meta($post_id, RHCJC_AS_META_PROVIDER,    $res['provider']);
  update_post_meta($post_id, RHCJC_AS_META_MODEL,       $res['model']);
  update_post_meta($post_id, RHCJC_AS_META_HASH,        $hash);
  update_post_meta($post_id, RHCJC_AS_META_GENERATED_AT, rhcjc_as_now());
  delete_transient('rhcjc_as_last_error');

  rhcjc_as_log('generation_ok', ['post_id'=>$post_id, 'bullets'=>count($res['bullets']), 'model'=>$res['model']]);
  delete_transient($lock_key);
  return true;
}
