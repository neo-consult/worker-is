<?php
namespace WorkerIS\Core;

class FormRenderer {
    /**
     * Rendert konfigurierte dynamische Felder.
     *
     * @param array $config Feldkonfiguration aus DB
     * @param array $values Bereits gespeicherte Werte
     * @param string $prefix 'anonymous' oder 'detailed'
     * @return void
     */
    public static function render_configured_fields(array $config, array $values, string $prefix): void {
        foreach ($config as $index => $field) {
            $type        = $field['type'] ?? 'text';
            $label       = $field['label'] ?? '';
            $required    = !empty($field['required']);
            $placeholder = $field['placeholder'] ?? '';
            $max_length  = isset($field['max_length']) ? intval($field['max_length']) : '';
            $field_name  = "{$prefix}[{$index}]";
            $field_id    = "{$prefix}_{$index}";
            $value       = $values[$index] ?? '';

            echo '<div class="mb-3">';
            echo '<label for="' . esc_attr($field_id) . '" class="form-label">' . esc_html($label);
            if ($required) echo ' <span style="color:red">*</span>';
            echo '</label>';

            $attr = [
                'id' => esc_attr($field_id),
                'name' => esc_attr($field_name),
                'class' => 'form-control',
            ];

            if ($placeholder) $attr['placeholder'] = esc_attr($placeholder);
            if ($required) $attr['required'] = 'required';
            if ($max_length && in_array($type, ['text', 'textarea'])) {
                $attr['maxlength'] = $max_length;
            }

            $attr_str = self::build_attr_string($attr);

            switch ($type) {
                case 'textarea':
                    echo "<textarea $attr_str>" . esc_textarea($value) . "</textarea>";
                    break;

                case 'radio':
                    foreach (($field['options'] ?? []) as $option) {
                        $opt_id = $field_id . '_' . sanitize_title($option);
                        echo '<div class="form-check">';
                        echo '<input class="form-check-input" type="radio" name="' . esc_attr($field_name) . '" id="' . esc_attr($opt_id) . '" value="' . esc_attr($option) . '" ' . checked($value, $option, false) . '>';
                        echo '<label class="form-check-label" for="' . esc_attr($opt_id) . '">' . esc_html($option) . '</label>';
                        echo '</div>';
                    }
                    break;

                case 'checkbox':
                    $saved = is_array($value) ? $value : [];
                    foreach (($field['options'] ?? []) as $option) {
                        $opt_id = $field_id . '_' . sanitize_title($option);
                        echo '<div class="form-check">';
                        echo '<input class="form-check-input" type="checkbox" name="' . esc_attr($field_name) . '[]" id="' . esc_attr($opt_id) . '" value="' . esc_attr($option) . '" ' . (in_array($option, $saved) ? 'checked' : '') . '>';
                        echo '<label class="form-check-label" for="' . esc_attr($opt_id) . '">' . esc_html($option) . '</label>';
                        echo '</div>';
                    }
                    break;

                case 'dropdown':
                    echo "<select $attr_str>";
                    echo '<option value="">-- Auswahl --</option>';
                    foreach (($field['options'] ?? []) as $option) {
                        echo '<option value="' . esc_attr($option) . '" ' . selected($value, $option, false) . '>' . esc_html($option) . '</option>';
                    }
                    echo '</select>';
                    break;

                case 'text':
                default:
                    echo "<input type=\"text\" value=\"" . esc_attr($value) . "\" $attr_str />";
                    break;
            }

            echo '</div>';
        }
    }

    /**
     * Hilfsfunktion: Baut HTML-Attribut-String aus Array
     */
    protected static function build_attr_string(array $attrs): string {
        $html = [];
        foreach ($attrs as $key => $val) {
            $html[] = $key . '="' . esc_attr($val) . '"';
        }
        return implode(' ', $html);
    }
}
