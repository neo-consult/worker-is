<?php
namespace WorkerIS\Controller;

use WorkerIS\Core\View;
use WorkerIS\Core\Logger;

class LogViewerController {
    public static function render(): void {
        if (!current_user_can('worker_is_view_logs')) {
            wp_die('Zugriff verweigert.');
        }

        $log_path = Logger::get_log_path(); // e.g. wp-content/uploads/worker-is.log

        if (!file_exists($log_path)) {
            echo '<div class="notice notice-warning"><p>Keine Log-Datei gefunden.</p></div>';
            return;
        }

        $lines = file($log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];

        foreach ($lines as $line) {
            if (preg_match('/^\[(.*?)\]\s+\[(.*?)\]\s+(.*?)\s+\|\s+Data:\s+(.*)$/', $line, $m)) {
                $logs[] = [
                    'timestamp' => $m[1],
                    'level'     => $m[2],
                    'message'   => $m[3],
                    'data'      => json_decode($m[4], true) ?? [],
                ];
            }
        }

        View::render('logs/view', ['logs' => array_reverse($logs)]);
    }
}
