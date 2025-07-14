<?php 
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    $forms  = Forminator_API::get_forms(); // returns Forminator_Form_Model[]
    $result = [];

    foreach ( $forms as $form ) {
        // model already has ->id and ->name
        $form_id    = $form->id;
        $form_title = $form->name;

        // make sure raw fields exist
        if ( empty( $form->raw['fields'] ) || ! is_array( $form->raw['fields'] ) ) {
            continue;
        }

        $fields = [];

        foreach ( $form->raw['fields'] as $field ) {
            $name  = $field['name']        ?? '';
            $label = $field['field_label'] ?? '';

            if ( ! $name ) {
                continue;
            }

            $display_label = $label ?: $name;
            $fields[ $name ] = "{$display_label} ({$name})";
        }

        // only include forms that actually have fields
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
