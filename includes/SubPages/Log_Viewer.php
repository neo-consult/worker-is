<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Log_Viewer {

    public static function render() {
        if ( isset($_POST['worker_is_clear_logs']) && check_admin_referer('worker_is_logs') ) {
            $log_path = plugin_dir_path(__FILE__) . '../../logs/worker.log';
            if (file_exists($log_path)) {
                file_put_contents($log_path, '');
                echo '<div class="notice notice-success"><p>Logs wurden geleert.</p></div>';
            }
        }

        $logs = Logger::read_all();

        echo '<div class="wrap">';
        echo '<h1>Worker-Logs</h1>';
        echo '<form method="post">';
        wp_nonce_field('worker_is_logs');
        echo '<p><button type="submit" name="worker_is_clear_logs" class="button button-secondary">Logs leeren</button></p>';
        echo '</form>';

        echo '<textarea readonly rows="25" style="width:100%; font-family:monospace;">';
        echo esc_textarea(implode("\n", $logs));
        echo '</textarea>';
        echo '</div>';
    }
}
