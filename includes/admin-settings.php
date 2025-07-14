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
    $form_mappings = $options['form_mappings'] ?? [];
    $forms = ffotp_get_forminator_forms_with_fields(); // updated helper

    foreach ($forms as $form_id => $form_data) {
        echo "<h4 style='margin-top:20px;'>" . esc_html($form_data['name']) . " (ID: $form_id)</h4>";

        echo "<div class='ffotp-phone-map-group'>";
        $mapped_fields = $form_mappings[$form_id] ?? [];

        // Always at least one field selector shown
        if (empty($mapped_fields)) $mapped_fields = [''];

        foreach ($mapped_fields as $index => $saved_field) {
            echo "<select name='ffotp_settings[form_mappings][$form_id][]'>";
            foreach ($form_data['fields'] as $field_name => $label) {
                $selected = selected($field_name, $saved_field, false);
                echo "<option value='" . esc_attr($field_name) . "' $selected>$label</option>";
            }
            echo "</select><br/>";
        }

        echo "<button type='button' class='button ffotp-add-phone-field' data-form='$form_id'>+ Add another phone field</button>";
        echo "<hr/>";
        echo "</div>";
    }

    // Inline JS to clone field dropdowns
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ffotp-add-phone-field').forEach(btn => {
            btn.addEventListener('click', function () {
                const formId = this.dataset.form;
                const wrapper = this.closest('.ffotp-phone-map-group');
                const selects = wrapper.querySelectorAll('select');
                if (selects.length === 0) return;

                const lastSelect = selects[selects.length - 1];
                const clone = lastSelect.cloneNode(true);
                clone.name = `ffotp_settings[form_mappings][${formId}][]`;
                wrapper.insertBefore(clone, this);
                wrapper.insertBefore(document.createElement('br'), this);
            });
        });
    });
    </script>
    <?php
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
