<?php
if (!defined('ABSPATH')) exit;

function rhcjc_as_box_html($post_id, $limit=RHCJC_AS_BULLET_COUNT) {
  $override = get_post_meta($post_id, RHCJC_AS_META_OVERRIDE, true);
  if ($override) {
    $bullets = array_map('trim', preg_split('/\r\n|\r|\n/', $override));
  } else {
    $bullets = get_post_meta($post_id, RHCJC_AS_META_SUMMARY, true);
  }
  if (empty($bullets)) return '';

  $bullets = array_slice((array)$bullets, 0, max(1,(int)$limit));
  ob_start(); ?>
  <div class="rhcjc-as-box" role="complementary" aria-label="Key Points">
    <div class="rhcjc-as-title">Key Points</div>
    <ul class="rhcjc-as-list">
      <?php foreach ($bullets as $b): ?>
        <li><?php echo esc_html($b); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php
  return ob_get_clean();
}

add_shortcode('rhcjc_summary', function($atts){
  global $post; if (!$post) return '';
  $a = shortcode_atts(['count'=>RHCJC_AS_BULLET_COUNT], $atts, 'rhcjc_summary');
  return rhcjc_as_box_html($post->ID, (int)$a['count']);
});

function rhcjc_as_maybe_auto_insert($html) {
  if (!RHCJC_AS_AUTO_INSERT && !get_option(RHCJC_AS_OPTION_AUTOINSERT, 0)) return $html;
  if (!is_singular() || !in_the_loop() || !is_main_query()) return $html;
  global $post; if (!$post) return $html;

  $box = rhcjc_as_box_html($post->ID);
  if ($box) return $box . $html;
  return $html;
}
add_filter('the_content', 'rhcjc_as_maybe_auto_insert', 5);

add_action('wp_enqueue_scripts', function(){
  $css = '.rhcjc-as-box{background:#f7f9fc;border:1px solid #e5edf9;border-radius:12px;padding:14px 16px;margin:18px 0;box-shadow:0 1px 6px rgba(0,0,0,.06)}
  .rhcjc-as-title{font-weight:700;font-size:.95rem;letter-spacing:.02em;margin-bottom:8px}
  .rhcjc-as-list{margin:0;padding-left:1.1rem}
  .rhcjc-as-list li{margin:6px 0;line-height:1.4}';
  wp_register_style(RHCJC_AS_CSS_HANDLE, false);
  wp_enqueue_style(RHCJC_AS_CSS_HANDLE);
  wp_add_inline_style(RHCJC_AS_CSS_HANDLE, $css);
});
