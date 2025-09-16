<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_options_page(
    'RHCJC AI Summary Settings',
    'RHCJC Summary',
    'manage_options',
    'rhcjc-as',
    'rhcjc_as_settings_page'
  );
});

add_action('admin_init', function(){
  // === Register settings ===
  // API key
  register_setting('rhcjc_as_options', 'rhcjc_as_api_key', [
    'type' => 'string',
    'sanitize_callback' => 'sanitize_text_field',
    'default' => ''
  ]);

  // Model (default GPT-4.1)
  register_setting('rhcjc_as_options', RHCJC_AS_OPTION_MODEL, [
    'type' => 'string',
    'sanitize_callback' => 'sanitize_text_field',
    'default' => 'gpt-4.1', // force GPT-4.1 as default
  ]);

  // === Section ===
  add_settings_section(
    'rhcjc_as_main',
    'AI Summary Settings',
    function(){
      echo '<p>Configure the model and API key for RHCJC summaries.</p>';
    },
    'rhcjc-as'
  );

  // === API key field ===
  add_settings_field(
    'rhcjc_as_api_key',
    'OpenAI API Key',
    function(){
      $val = get_option('rhcjc_as_api_key', '');
      echo '<input type="password" style="width:400px" name="rhcjc_as_api_key" value="' . esc_attr($val) . '" />';
      echo '<p class="description">Enter your OpenAI API key. This is stored securely in WordPress options.</p>';
    },
    'rhcjc-as',
    'rhcjc_as_main'
  );

  // === Model dropdown ===
  add_settings_field(
    'rhcjc_as_model',
    'Default Model',
    function(){
      $val = get_option(RHCJC_AS_OPTION_MODEL, 'gpt-4.1');
      $models = [
        'gpt-4.1'     => 'GPT-4.1 (best quality, factual – recommended)',
        'gpt-4.1-mini'=> 'GPT-4.1-mini (cheaper, slightly faster)',
        'gpt-4o'      => 'GPT-4o (multimodal, general)',
        'gpt-4o-mini' => 'GPT-4o-mini (cheapest, lowest quality)',
      ];
      echo '<select name="'.esc_attr(RHCJC_AS_OPTION_MODEL).'">';
      foreach ($models as $id => $label) {
        printf(
          '<option value="%s" %s>%s</option>',
          esc_attr($id),
          selected($val, $id, false),
          esc_html($label)
        );
      }
      echo '</select>';
      echo '<p class="description">Choose the model for summaries. GPT-4.1 is set as default and recommended.</p>';
    },
    'rhcjc-as',
    'rhcjc_as_main'
  );
});

// === Render settings page ===
function rhcjc_as_settings_page() {
  ?>
  <div class="wrap">
    <h1>RHCJC AI Summary Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('rhcjc_as_options');
      do_settings_sections('rhcjc-as');
      submit_button();
      ?>
    </form>
  </div>
  <?php
}
