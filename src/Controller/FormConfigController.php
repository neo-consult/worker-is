<?php
namespace WorkerIS\Controller;

use WorkerIS\Core\Logger;

class FormConfigController {
    /**
     * Zeigt die Admin-Seite zur Konfiguration dynamischer Formularfelder.
     */
    public static function render(): void {
        // Verarbeitung beim Speichern
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('worker_is_form_config')) {
            $raw_json = stripslashes($_POST['form_config_json'] ?? '{}');
            $decoded = json_decode($raw_json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                update_option('worker_is_form_config', json_encode($decoded));
                Logger::info('Formular-Konfiguration gespeichert.');
                echo '<div class="notice notice-success"><p>Konfiguration gespeichert.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Ungültiges JSON: ' . json_last_error_msg() . '</p></div>';
            }
        }

        // Aktuelle Konfiguration laden
        $config_json = get_option('worker_is_form_config', '{"anonymous":"[]","detailed":"[]"}');
        $escaped_config = esc_js($config_json);

        // Übergabe an JS als globales JS-Objekt
        add_action('admin_footer', function () use ($escaped_config) {
            echo "<script>window.workerISFormConfig = JSON.parse(\"" . $escaped_config . "\");</script>";
        });

        // Lade CSS und JS
        add_action('admin_enqueue_scripts', function () {
            // Bootstrap 5 (CDN)
            wp_enqueue_style('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
            wp_enqueue_script('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', [], null, true);

            // Plugin Assets
            wp_enqueue_script('worker-is-form-builder', WORKER_IS_URL . 'assets/js/form-builder.js', ['jquery'], WORKER_IS_VERSION, true);
            wp_enqueue_style('worker-is-form-builder', WORKER_IS_URL . 'assets/css/form-builder.css', [], WORKER_IS_VERSION);
        });

        // View einbinden
        require_once WORKER_IS_PATH . 'src/View/formconfig/builder.php';
    }
}
