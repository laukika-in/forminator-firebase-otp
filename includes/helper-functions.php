<?php 
function ffotp_get_forminator_forms_with_fields() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    // get_forms() returns Forminator_Form_Model[] or WP_Post[]
    $posts_or_models = Forminator_API::get_forms();
    $result          = [];

    foreach ( $posts_or_models as $form_post_or_model ) {
        // determine ID + title regardless of whether it's WP_Post or Forminator_Model
        $form_id    = isset( $form_post_or_model->ID ) 
                          ? $form_post_or_model->ID 
                          : $form_post_or_model->id;
        $form_title = isset( $form_post_or_model->post_title ) 
                          ? $form_post_or_model->post_title 
                          : $form_post_or_model->name;

        // now load the true model
        $form_model = Forminator_API::get_form( $form_id );
        if ( ! $form_model instanceof Forminator_Form_Model 
             || empty( $form_model->raw['fields'] ) 
             || ! is_array( $form_model->raw['fields'] ) ) {
            continue;
        }

        $fields = [];
        // each raw field is an array; its settings contain name + label
        foreach ( $form_model->raw['fields'] as $field_def ) {
            $settings = $field_def['settings'] ?? [];
            $name     = $settings['name']  ?? '';
            $label    = $settings['label'] ?? '';

            if ( ! $name ) {
                continue;
            }

            $display = $label ? $label : $name;
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
