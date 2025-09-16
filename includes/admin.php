<?php
if (!defined('ABSPATH')) exit;

add_action('add_meta_boxes', function(){
  add_meta_box('rhcjc_as_meta', 'RHCJC AI Summary', 'rhcjc_as_meta_render', 'post', 'side', 'default');
});

function rhcjc_as_meta_render($post) {
  $bullets = get_post_meta($post->ID, RHCJC_AS_META_SUMMARY, true);
  $override= get_post_meta($post->ID, RHCJC_AS_META_OVERRIDE, true);
  $model   = get_post_meta($post->ID, RHCJC_AS_META_MODEL, true);
  $prov    = get_post_meta($post->ID, RHCJC_AS_META_PROVIDER, true);
  $genat   = get_post_meta($post->ID, RHCJC_AS_META_GENERATED_AT, true);
  $err     = get_transient('rhcjc_as_last_error');

  echo '<p><strong>Provider:</strong> '.esc_html($prov ?: 'openai').'</p>';
  echo '<p><strong>Model:</strong> '.esc_html($model ?: get_option(RHCJC_AS_OPTION_MODEL, RHCJC_AS_DEFAULT_MODEL)).'</p>';
  echo '<p><strong>Generated:</strong> '.esc_html($genat ?: '—').'</p>';

  if ($err) {
    echo '<div style="background:#fff3cd;border:1px solid #ffeeba;padding:6px;border-radius:6px;color:#856404;margin-bottom:8px;">'
       . esc_html($err) . '</div>';
  }

  if (!empty($bullets) && !$override) {
    echo '<p><strong>Current bullets:</strong></p><ol style="margin-left:18px">';
    foreach ((array)$bullets as $b) echo '<li style="margin:4px 0">'.esc_html($b).'</li>';
    echo '</ol>';
  }

  // Manual override textarea
  $nonce = wp_create_nonce('rhcjc_as_override_'.$post->ID);
  echo '<p><label for="rhcjc_as_override"><strong>Editor override (one bullet per line):</strong></label></p>';
  echo '<textarea id="rhcjc_as_override" name="rhcjc_as_override" style="width:100%;min-height:100px;">'
     . esc_textarea($override) . '</textarea>';
  echo '<input type="hidden" name="rhcjc_as_override_nonce" value="'.esc_attr($nonce).'"/>';

  // Regenerate button (AJAX)
  echo '<p><button type="button" class="button" id="rhcjc_as_regen_btn" data-post="'.esc_attr($post->ID).'">Regenerate</button></p>';
  echo '<p style="color:#666">Generation runs once on first publish. Use Regenerate after substantial edits.</p>';
}

add_action('save_post', function($post_id){
  if (!isset($_POST['rhcjc_as_override_nonce'])) return;
  if (!wp_verify_nonce($_POST['rhcjc_as_override_nonce'], 'rhcjc_as_override_'.$post_id)) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $override = isset($_POST['rhcjc_as_override']) ? (string)$_POST['rhcjc_as_override'] : '';
  $override = wp_kses_post($override);
  if (trim($override) === '') {
    delete_post_meta($post_id, RHCJC_AS_META_OVERRIDE);
  } else {
    update_post_meta($post_id, RHCJC_AS_META_OVERRIDE, $override);
  }
}, 20);

add_action('admin_notices', function(){
  if (!current_user_can('edit_posts')) return;
  $msg = get_transient('rhcjc_as_last_error');
  if ($msg) {
    echo '<div class="notice notice-warning is-dismissible"><p><strong>RHCJC Summary:</strong> '
      . esc_html($msg) . '</p></div>';
  }
});

add_action('admin_enqueue_scripts', function($hook){
  if (!in_array($hook, ['post.php','post-new.php','settings_page_rhcjc-as'], true)) return;
  wp_enqueue_script('rhcjc-as-admin', RHCJC_AS_URL.'assets/js/admin.js', ['jquery'], '1.0.0', true);
  wp_localize_script('rhcjc-as-admin', 'RHCJC_AS', [
    'ajax'  => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('rhcjc_as_ajax'),
  ]);
});
