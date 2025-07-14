<?php

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
function ffotp_get_forminator_forms_with_fields() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms();
    $result = [];

    foreach ($forms as $form) {
        $fields = [];
        $form_fields = Forminator_API::get_form($form->id)->get_fields();

        foreach ($form_fields as $field) {
            if ($field['element'] === 'phone') {
                $fields[$field['name']] = $field['field_label'] . ' (' . $field['name'] . ')';
            }
        }

        $result[$form->id] = [
            'name' => $form->name,
            'fields' => $fields
        ];
    }

    return $result;
}
