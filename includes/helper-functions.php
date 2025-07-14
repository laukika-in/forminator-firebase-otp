<?php  
function ffotp_get_all_forminator_form_fields_detailed() {
    if ( ! class_exists( 'Forminator_API' ) ) {
        return [];
    }

    $posts_or_models = Forminator_API::get_forms();
    $result = [];

    foreach ( $posts_or_models as $form_post_or_model ) {
        $form_id = isset( $form_post_or_model->ID ) 
            ? $form_post_or_model->ID 
            : $form_post_or_model->id;
        $form_title = isset( $form_post_or_model->post_title ) 
            ? $form_post_or_model->post_title 
            : $form_post_or_model->name;

        $form_model = Forminator_API::get_form( $form_id );
        if ( ! $form_model instanceof Forminator_Form_Model 
            || empty( $form_model->raw['fields'] ) 
            || ! is_array( $form_model->raw['fields'] ) ) {
            continue;
        }

        $fields = [];

        foreach ( $form_model->raw['fields'] as $field_def ) {
            $settings = $field_def['settings'] ?? [];
            $field_data = [
                'name'       => $settings['name']       ?? '',
                'label'      => $settings['label']      ?? '',
                'type'       => $field_def['type']      ?? '',
                'placeholder'=> $settings['placeholder']?? '',
                'required'   => ! empty( $settings['required'] ) ? true : false,
                'field_raw'  => $field_def, // full raw array if needed for debugging
            ];

            if ( $field_data['name'] ) {
                $fields[] = $field_data;
            }
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
