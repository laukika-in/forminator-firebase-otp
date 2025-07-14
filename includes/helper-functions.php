<?php
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    // Step 1: get_forms() returns WP_Post[]
    $form_posts = Forminator_API::get_forms();
    $result     = [];

    foreach ( $form_posts as $form_post ) {
        // WP_Post properties
        $form_id    = $form_post->ID;
        $form_title = $form_post->post_title;

        // Step 2: load the actual Forminator model
        $form_model = Forminator_API::get_form( $form_id );
        if ( ! $form_model instanceof Forminator_Form_Model ) {
            continue;
        }

        // Step 3: pull the raw field definitions
        $raw = $form_model->raw;
        if ( empty( $raw['fields'] ) || ! is_array( $raw['fields'] ) ) {
            continue;
        }

        $fields = [];
        foreach ( $raw['fields'] as $field_def ) {
            $name  = $field_def['name']        ?? '';
            $label = $field_def['field_label'] ?? '';

            if ( ! $name ) {
                continue;
            }

            $display_label    = $label ?: $name;
            $fields[ $name ] = "{$display_label} ({$name})";
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
