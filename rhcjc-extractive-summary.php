<?php
/**
 * Plugin Name: RHCJC Abstractive Summary (OpenAI, grounded)
 * Description: Generates 4–5 grounded bullet summaries per post using OpenAI (JSON mode, temp=0). Stores to post meta and renders via shortcode or auto-insert.
 * Version: 1.0.0
 * Author: Kiran Silwal & Rabindra Giri
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

define('RHCJC_AS_DIR', plugin_dir_path(__FILE__));
define('RHCJC_AS_URL', plugin_dir_url(__FILE__));

require_once RHCJC_AS_DIR . 'includes/config.php';
require_once RHCJC_AS_DIR . 'includes/util.php';
require_once RHCJC_AS_DIR . 'includes/logger.php';
require_once RHCJC_AS_DIR . 'includes/security.php';
require_once RHCJC_AS_DIR . 'includes/content.php';
require_once RHCJC_AS_DIR . 'includes/provider-openai.php';
require_once RHCJC_AS_DIR . 'includes/generator.php';
require_once RHCJC_AS_DIR . 'includes/render.php';
require_once RHCJC_AS_DIR . 'includes/admin.php';
require_once RHCJC_AS_DIR . 'includes/settings.php';
require_once RHCJC_AS_DIR . 'includes/ajax.php';
