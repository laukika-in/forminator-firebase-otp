<?php   
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    $form_posts = Forminator_API::get_forms();
    $result     = [];

    foreach ( $form_posts as $item ) {
        if ( $item instanceof WP_Post ) {
            $form_id    = $item->ID;
            $form_title = $item->post_title;
        } elseif ( is_object( $item ) && isset( $item->id, $item->name ) ) {
            $form_id    = $item->id;
            $form_title = $item->name;
        } else {
            continue;
        }

        $model = Forminator_API::get_form( $form_id );
        if ( ! $model instanceof Forminator_Form_Model ) {
            continue;
        }

        $raw = $model->raw;
        if ( empty( $raw['fields'] ) || ! is_array( $raw['fields'] ) ) {
            continue;
        }

        $fields = [];
        foreach ( $raw['fields'] as $field_def_raw ) {
            $field_def = (array) $field_def_raw;

            // Some versions store settings under 'settings'; others at root
            $settings = isset( $field_def['settings'] ) && is_array( $field_def['settings'] )
                ? $field_def['settings']
                : $field_def;

            $name  = $settings['name']  ?? '';
            $label = $settings['label'] ?? '';

            if ( ! $name ) continue;

            $display = $label ?: $nam


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
