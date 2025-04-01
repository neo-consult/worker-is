<?php
namespace WorkerIS;

class Form_Helper {

    /**
     * Generiert dynamisch HTML für ein Formular basierend auf der Konfiguration.
     *
     * @param array  $config  Konfiguration als Array (z. B. durch json_decode des gespeicherten JSON).
     * @param array  $values  Optional: Vorhandene Werte (z. B. beim Bearbeiten eines Profils).
     * @param string $section Der Kontext, "anonymous" oder "detailed".
     * @return string Das generierte HTML für die Formularfelder.
     */
    public static function render_dynamic_form($config, $values = array(), $section = 'anonymous') {
        $html = '';
        if (!is_array($config)) {
            return $html;
        }
        foreach ($config as $index => $field) {
            // Erzeuge den Namen des Eingabefelds, z. B. dynamic[anonymous][0]
            $field_name = "dynamic[$section][$index]";
            $field_value = isset($values[$index]) ? $values[$index] : '';
            $required_marker = (isset($field['required']) && $field['required']) ? ' <span style="color:red;">*</span>' : '';
            switch ($field['type']) {
                case 'text':
                    $html .= '<p>';
                    if (isset($field['label'])) {
                        $html .= '<label for="' . esc_attr($field_name) . '">' . esc_html($field['label']) . $required_marker . '</label><br>';
                    }
                    $html .= '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($field_value) . '"';
                    if (isset($field['max_length']) && !empty($field['max_length'])) {
                        $html .= ' maxlength="' . intval($field['max_length']) . '"';
                    }
                    $html .= '>';
                    if (isset($field['description'])) {
                        $html .= '<br><small>' . esc_html($field['description']) . '</small>';
                    }
                    $html .= '</p>';
                    break;

                case 'radio':
                    $html .= '<p>';
                    if (isset($field['label'])) {
                        $html .= '<span>' . esc_html($field['label']) . $required_marker . '</span><br>';
                    }
                    if (isset($field['options']) && is_array($field['options'])) {
                        foreach ($field['options'] as $opt_index => $option) {
                            $radio_id = $field_name . '_' . $opt_index;
                            $checked = ($field_value == $option) ? ' checked' : '';
                            $html .= '<label for="' . esc_attr($radio_id) . '">';
                            $html .= '<input type="radio" id="' . esc_attr($radio_id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($option) . '"' . $checked . '> ' . esc_html($option);
                            $html .= '</label> ';
                        }
                    }
                    if (isset($field['description'])) {
                        $html .= '<br><small>' . esc_html($field['description']) . '</small>';
                    }
                    $html .= '</p>';
                    break;

                case 'checkbox':
                    $html .= '<p>';
                    if (isset($field['label'])) {
                        $html .= '<span>' . esc_html($field['label']) . $required_marker . '</span><br>';
                    }
                    if (isset($field['options']) && is_array($field['options'])) {
                        // Für Checkboxen erwarten wir ein Array als Wert
                        $current = is_array($field_value) ? $field_value : array();
                        foreach ($field['options'] as $opt_index => $option) {
                            $checkbox_id = $field_name . '_' . $opt_index;
                            $checked = in_array($option, $current) ? ' checked' : '';
                            $html .= '<label for="' . esc_attr($checkbox_id) . '">';
                            $html .= '<input type="checkbox" id="' . esc_attr($checkbox_id) . '" name="' . esc_attr($field_name) . '[]" value="' . esc_attr($option) . '"' . $checked . '> ' . esc_html($option);
                            $html .= '</label> ';
                        }
                    }
                    if (isset($field['description'])) {
                        $html .= '<br><small>' . esc_html($field['description']) . '</small>';
                    }
                    $html .= '</p>';
                    break;

                case 'dropdown':
                    $html .= '<p>';
                    if (isset($field['label'])) {
                        $html .= '<label for="' . esc_attr($field_name) . '">' . esc_html($field['label']) . $required_marker . '</label><br>';
                    }
                    // Unterstütze sowohl "entities" als auch "options"
                    $options = array();
                    if (isset($field['entities']) && is_array($field['entities'])) {
                        $options = $field['entities'];
                    } elseif (isset($field['options']) && is_array($field['options'])) {
                        $options = $field['options'];
                    }
                    if (!empty($options)) {
                        $html .= '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '">';
                        foreach ($options as $option) {
                            $selected = ($field_value == $option) ? ' selected' : '';
                            $html .= '<option value="' . esc_attr($option) . '"' . $selected . '>' . esc_html($option) . '</option>';
                        }
                        $html .= '</select>';
                    }
                    if (isset($field['description'])) {
                        $html .= '<br><small>' . esc_html($field['description']) . '</small>';
                    }
                    $html .= '</p>';
                    break;

                case 'header':
                    $html .= '<h3>' . (isset($field['text']) ? esc_html($field['text']) : (isset($field['label']) ? esc_html($field['label']) : '')) . '</h3>';
                    break;

                case 'description':
                    $html .= '<p>' . (isset($field['text']) ? esc_html($field['text']) : (isset($field['label']) ? esc_html($field['label']) : '')) . '</p>';
                    break;

                case 'separator':
                    $html .= '<hr>';
                    break;

                default:
                    // Unbekannter Typ: Einfaches Textfeld
                    $html .= '<p><label for="' . esc_attr($field_name) . '">' . (isset($field['label']) ? esc_html($field['label']) : 'Unknown') . $required_marker . '</label><br>';
                    $html .= '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($field_value) . '">';
                    $html .= '</p>';
                    break;
            }
        }
        return $html;
    }
}
