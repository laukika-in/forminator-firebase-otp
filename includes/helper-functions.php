<?php
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    // Forminator_API::get_forms() returns Forminator_Form_Model[]
    $forms  = Forminator_API::get_forms();
    $result = [];

    foreach ( $forms as $form ) {
        // Correct properties on Forminator_Form_Model
        $form_id    = $form->id;
        $form_title = $form->name;

        // Ensure the raw fields array is available
        if ( empty( $form->raw['fields'] ) || ! is_array( $form->raw['fields'] ) ) {
            continue;
        }

        $fields = [];
        foreach ( $form->raw['fields'] as $field_def ) {
            $name  = $field_def['name']        ?? '';
            $label = $field_def['field_label'] ?? '';

            if ( ! $name ) {
                continue;
            }

            // Use the label if present, else fallback to the field name
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
