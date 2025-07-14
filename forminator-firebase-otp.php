<?php
/**
 * Plugin Name: Forminator OTP Verification 
 * Description: Firebase-based OTP verification for Forminator forms.
 * Plugin URI: https://laukika.com/
 * Author: Laukika
 * Version: 1.0.1
 * Author URI: https://laukika.com/
 * Text Domain: ffotp
 * Requires Plugins: forminator
 */

if (!defined('ABSPATH')) exit;
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], false);
define('FFOTP_VERSION', $plugin_data['Version']);

define('FFOTP_DIR', plugin_dir_path(__FILE__));
define('FFOTP_URL', plugin_dir_url(__FILE__));

// Ensure Forminator is active
add_action('admin_init', function () {
    if (!is_plugin_active('forminator/forminator.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Forminator Firebase OTP</strong> requires the <strong>Forminator</strong> plugin to be installed and active.</p></div>';
        });
    }
});


require_once FFOTP_DIR . 'includes/admin-settings.php';
require_once FFOTP_DIR . 'includes/helper-functions.php';
require_once FFOTP_DIR . 'includes/frontend-handler.php';
