<?php

function ffotp_get_forminator_forms_with_fields() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms();
    $result = [];

    foreach ($forms as $form) {
        error_log("Checking form: " . $form->id . ' - ' . $form->name);

        $form_model = Forminator_API::get_form($form->id);
        if (!$form_model || !method_exists($form_model, 'get_fields')) {
            error_log("No model or get_fields for form ID: " . $form->id);
            continue;
        }

        $form_fields = $form_model->get_fields();
        error_log("Total fields for form ID {$form->id}: " . count($form_fields));

        $fields = [];

        foreach ($form_fields as $field) {
            $name    = property_exists($field, 'name') ? $field->name : '';
            $label   = property_exists($field, 'field_label') ? $field->field_label : '';
            $element = property_exists($field, 'element') ? $field->element : '';

            error_log("Field element: {$element} | name: {$name} | label: {$label}");

            if (!empty($name)) {
                $display_label = !empty($label) ? $label : ucfirst($element);
                $fields[$name] = "{$display_label} ({$name})";
            }
        }

        $result[$form->id] = [
            'name'   => $form->name,
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
