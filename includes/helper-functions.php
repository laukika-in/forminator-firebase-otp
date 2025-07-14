<?php
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    // get_forms() returns an array of WP_Post objects
    $form_posts = Forminator_API::get_forms();
    $result     = [];

    foreach ( $form_posts as $form_post ) {
        // WP_Post properties
        $form_id    = $form_post->ID;
        $form_title = $form_post->post_title;

        // load the Forminator_Form_Model
        $form_model = Forminator_API::get_form( $form_id );
        if ( ! $form_model instanceof Forminator_Form_Model ) {
            continue;
        }

        // raw['fields'] is the array of field definitions
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

            // Display label (fallback to name if label is empty)
            $display = $label ?: $name;
            $fields[ $name ] = "{$display} ({$name})";
        }

        // only include forms that have at least one field
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
