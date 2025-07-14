<?php 
<?php
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    // This returns WP_Post[] (the form posts)
    $form_posts = Forminator_API::get_forms();
    $result     = [];

    foreach ( $form_posts as $item ) {
        // Determine form ID and title
        if ( $item instanceof WP_Post ) {
            $form_id    = $item->ID;
            $form_title = $item->post_title;
        } elseif ( is_object( $item ) && isset( $item->id, $item->name ) ) {
            // In some contexts you might get the model directly
            $form_id    = $item->id;
            $form_title = $item->name;
        } else {
            continue;
        }

        // Fetch the actual Forminator model
        $model = Forminator_API::get_form( $form_id );
        if ( ! $model instanceof Forminator_Form_Model ) {
            continue;
        }

        // Ensure raw fields exist
        $raw = $model->raw;
        if ( empty( $raw['fields'] ) || ! is_array( $raw['fields'] ) ) {
            continue;
        }

        // Build the dropdown list
        $fields = [];
        foreach ( $raw['fields'] as $field_def ) {
            // Each field_def is an array; its 'settings' key holds name/label
            if ( ! is_array( $field_def ) || empty( $field_def['settings'] ) || ! is_array( $field_def['settings'] ) ) {
                continue;
            }
            $settings = $field_def['settings'];
            $name     = $settings['name']  ?? '';
            $label    = $settings['label'] ?? '';

            if ( ! $name ) {
                continue;
            }

            $display = $label ?: $name;
            $fields[ $name ] = "{$display} ({$name})";
        }

        if ( ! empty( $fields ) ) {
            $result[ $form_id ] = [
                'name'   => $form_title,
                'fields' => $fields,
            ];
        }
    }

    return $result;
}


function ffotp_get_forminator_forms() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms();
    $result = [];

    foreach ($forms as $form) {
        $result[$form->id] = $form->name;
    }

    return $result;
}

function ffotp_get_firebase_config_js() {
    $options = get_option('ffotp_settings');
    $config = [
        'apiKey', 'authDomain', 'projectId',
        'storageBucket', 'messagingSenderId', 'appId'
    ];

    $out = [];
    foreach ($config as $key) {
        $val = esc_js($options[$key] ?? '');
        $out[] = "$key: \"$val\"";
    }

    return implode(",\n", $out);
}
