<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_rhcjc_as_regen', function(){
  if (!current_user_can('edit_posts')) wp_send_json_error('cap');
  check_ajax_referer('rhcjc_as_ajax', 'nonce');

  $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
  if (!$post_id) wp_send_json_error('no_post');

  $res = rhcjc_as_generate_for_post($post_id, true);
  if (is_wp_error($res)) {
    wp_send_json_error($res->get_error_message());
  } else {
    wp_send_json_success(['ok'=>true]);
  }
});
