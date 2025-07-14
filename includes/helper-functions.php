<?php

function ffotp_get_forminator_forms_with_fields() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms(); // Returns Forminator_Form_Model[]
    $result = [];

    foreach ($forms as $form) {
        $form_id = $form->id;
        $form_title = $form->name;

        error_log("Checking form: $form_id - $form_title");

        $form_model = Forminator_API::get_form($form_id);
        if (!$form_model || !isset($form_model->raw['fields'])) {
            error_log("No raw field data for form ID: " . $form_id);
            continue;
        }

        $form_fields = $form_model->raw['fields'];
        error_log("Total fields for form ID {$form_id}: " . count($form_fields));

        $fields = [];

        foreach ($form_fields as $field) {
            $name    = $field['name'] ?? '';
            $label   = $field['field_label'] ?? '';
            $element = $field['element'] ?? '';

            error_log("Field element: {$element} | name: {$name} | label: {$label}");

            if (!empty($name)) {
                $display_label = !empty($label) ? $label : ucfirst($element);
                $fields[$name] = "{$display_label} ({$name})";
            }
        }

        $result[$form_id] = [
            'name'   => $form_title,
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
