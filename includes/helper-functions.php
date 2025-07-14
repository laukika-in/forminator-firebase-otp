<?php
function ffotp_get_forminator_forms_with_fields() {
    if (!class_exists('Forminator_API')) return [];

    $forms = Forminator_API::get_forms();
    $result = [];

    foreach ($forms as $form) {
        $form_model = Forminator_API::get_form($form->id);
        if (!$form_model || !isset($form_model->fields)) continue;

        $fields = [];
        foreach ($form_model->fields as $field) {
            // If object, convert to array
            if (is_object($field) && method_exists($field, 'to_array')) {
                $field = $field->to_array();
            }

            if (!is_array($field)) continue;

            $label = $field['field_label'] ?? '(no label)';
            $name  = $field['element_id'] ?? ($field['id'] ?? '');
            $type  = $field['type'] ?? '';

            if (in_array($type, ['text', 'phone', 'number'])) {
                $fields[$name] = "$label ($name)";
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
