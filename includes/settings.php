<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_options_page('RHCJC Summary', 'RHCJC Summary', 'manage_options', 'rhcjc-as', 'rhcjc_as_settings_page');
});

add_action('admin_init', function(){
  register_setting('rhcjc_as', RHCJC_AS_OPTION_MODEL, [
    'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => RHCJC_AS_DEFAULT_MODEL
  ]);
  register_setting('rhcjc_as', RHCJC_AS_OPTION_AUTOINSERT, [
    'type' => 'boolean', 'sanitize_callback' => fn($v)=> $v ? 1 : 0, 'default' => 0
  ]);
  // key stored via custom setter with obfuscation; not directly registered
});

function rhcjc_as_settings_page() {
  if (!current_user_can('manage_options')) return;

  // handle key save
  if (isset($_POST['rhcjc_as_save']) && check_admin_referer('rhcjc_as_settings')) {
    $key = isset($_POST['openai_key']) ? trim((string)$_POST['openai_key']) : '';
    rhcjc_as_set_api_key($key);
    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  $model  = get_option(RHCJC_AS_OPTION_MODEL, RHCJC_AS_DEFAULT_MODEL);
  $autoi  = (int)get_option(RHCJC_AS_OPTION_AUTOINSERT, 0);
  $mask   = rhcjc_as_mask(rhcjc_as_get_api_key());
  ?>
  <div class="wrap">
    <h1>RHCJC AI Summary</h1>
    <form method="post">
      <?php wp_nonce_field('rhcjc_as_settings'); ?>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label>OpenAI API key</label></th>
          <td>
            <input type="password" name="openai_key" value="" placeholder="<?php echo esc_attr($mask ?: 'Paste key…'); ?>" style="width:420px">
            <p class="description">Best practice: define <code>RHCJC_OPENAI_KEY</code> in <code>wp-config.php</code> or set as an environment variable. This field stores a lightly obfuscated copy if provided.</p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label>Model</label></th>
          <td>
            <input type="text" name="<?php echo esc_attr(RHCJC_AS_OPTION_MODEL); ?>" value="<?php echo esc_attr($model); ?>" style="width:240px">
            <p class="description">Examples: <code>gpt-4o-mini</code>, <code>gpt-4.1-mini</code>, <code>gpt-4.1</code></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label>Auto-insert</label></th>
          <td>
            <label><input type="checkbox" name="<?php echo esc_attr(RHCJC_AS_OPTION_AUTOINSERT); ?>" value="1" <?php checked($autoi,1); ?>> Prepend the summary box above the article (otherwise, use the <code>[rhcjc_summary]</code> shortcode)</label>
          </td>
        </tr>
      </table>
      <p class="submit"><button class="button button-primary" name="rhcjc_as_save" value="1">Save Settings</button></p>
    </form>
  </div>
  <?php
}
