<?php
/*
Plugin Name: Forminator Firebase OTP
Description: Firebase-based OTP verification for Forminator forms.
Version: 1.113
Author: Your Name
*/

if (!defined('ABSPATH')) exit;
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], false);
define('FFOTP_VERSION', $plugin_data['Version']);

define('FFOTP_DIR', plugin_dir_path(__FILE__));
define('FFOTP_URL', plugin_dir_url(__FILE__));
 

require_once FFOTP_DIR . 'includes/admin-settings.php';
require_once FFOTP_DIR . 'includes/helper-functions.php';
require_once FFOTP_DIR . 'includes/frontend-handler.php';
