<?php
/**
 * Plugin Name: Forminator OTP Verification 
 * Description: Firebase-based OTP verification for Forminator forms.
 * Plugin URI: https://laukika.com/
 * Author: Laukika
 * Version: 1.0.0
 * Author URI: https://laukika.com/
 * Text Domain: ffotp
 */

if (!defined('ABSPATH')) exit;
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], false);
define('FFOTP_VERSION', $plugin_data['Version']);

define('FFOTP_DIR', plugin_dir_path(__FILE__));
define('FFOTP_URL', plugin_dir_url(__FILE__));
 

require_once FFOTP_DIR . 'includes/admin-settings.php';
require_once FFOTP_DIR . 'includes/helper-functions.php';
require_once FFOTP_DIR . 'includes/frontend-handler.php';
