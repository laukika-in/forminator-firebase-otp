<?php
function ffotp_get_forminator_forms_with_fields() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms();
    $result = [];

    foreach ($forms as $form) {
        $form_model = Forminator_API::get_form($form->id);
        if (!$form_model || !method_exists($form_model, 'get_fields')) continue;

        $form_fields = $form_model->get_fields();
        $fields = [];

        foreach ($form_fields as $field) {
            if (!empty($field->name) && !empty($field->field_label)) {
                $fields[$field->name] = $field->field_label . ' (' . $field->name . ')';
            }
        }


        $result[$form->id] = [
            'name' => $form->name,
            'fields' => $fields
        ];
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


