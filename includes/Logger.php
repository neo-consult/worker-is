<?php
namespace WorkerIS;

class Logger {

    const LOG_FILE = 'worker.log';

    /**
     * Loggt eine Nachricht in das PHP-Error-Log und eigene Datei.
     *
     * @param string $message Die zu loggende Nachricht.
     * @param array $context Optionale zusätzliche Kontextdaten.
     */
    public static function log( $message, $context = array() ) {
        $log_entry = '[worker-is] ' . $message;
        if ( ! empty( $context ) ) {
            $log_entry .= ' | Data: ' . json_encode( $context );
        }

        // Standard error_log
        error_log( $log_entry );

        // Eigene Datei im Plugin-Verzeichnis
        $log_path = plugin_dir_path( __FILE__ ) . '../logs/' . self::LOG_FILE;
        if ( ! file_exists( dirname( $log_path ) ) ) {
            mkdir( dirname( $log_path ), 0755, true );
        }

        file_put_contents( $log_path, date('[Y-m-d H:i:s] ') . $log_entry . PHP_EOL, FILE_APPEND );
    }

    /**
     * Gibt alle Logs als Array zurück (für Admin-UI).
     *
     * @return array
     */
    public static function read_all() {
        $log_path = plugin_dir_path( __FILE__ ) . '../logs/' . self::LOG_FILE;
        if ( file_exists( $log_path ) ) {
            return file( $log_path, FILE_IGNORE_NEW_LINES );
        }
        return array();
    }
}
