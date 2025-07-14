<?php

add_action('admin_menu', 'ffotp_add_admin_menu');
add_action('admin_init', 'ffotp_settings_init');

function ffotp_add_admin_menu() {
    add_menu_page(
        'Firebase OTP Settings',
        'Firebase OTP',
        'manage_options',
        'ffotp_settings',
        'ffotp_settings_page'
    );
}

function ffotp_settings_init() {
    register_setting('ffotp_plugin', 'ffotp_settings');

    add_settings_section('ffotp_firebase', 'Firebase Configuration', null, 'ffotp_plugin');
    add_settings_section('ffotp_forms', 'Forminator Integration', null, 'ffotp_plugin');

    // Firebase fields
    $fields = ['apiKey', 'authDomain', 'projectId', 'storageBucket', 'messagingSenderId', 'appId'];
    foreach ($fields as $field) {
        add_settings_field($field, ucfirst($field), function () use ($field) {
            $options = get_option('ffotp_settings');
            echo '<input type="text" name="ffotp_settings[' . esc_attr($field) . ']" value="' . esc_attr($options[$field] ?? '') . '" style="width: 400px;" />';
        }, 'ffotp_plugin', 'ffotp_firebase');
    }

    // Form mapping field
    add_settings_field('form_mappings', 'Form/Phone Field Mappings', 'ffotp_render_form_mapping', 'ffotp_plugin', 'ffotp_forms');
}

function ffotp_render_form_mapping() {
    $options = get_option('ffotp_settings');
    $mappings = $options['form_mappings'] ?? [];

    $forms = ffotp_get_forminator_forms(); // helper
    foreach ($forms as $form_id => $form_title) {
        $val = esc_attr($mappings[$form_id] ?? '');
        echo "<p><strong>$form_title</strong> — Field name: <input type='text' name='ffotp_settings[form_mappings][$form_id]' value='$val' /></p>";
    }
}

function ffotp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Firebase OTP Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ffotp_plugin');
            do_settings_sections('ffotp_plugin');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
