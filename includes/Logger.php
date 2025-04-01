<?php
namespace WorkerIS;

class Logger {

    /**
     * Loggt eine Nachricht in das PHP-Error-Log.
     *
     * @param string $message Die zu loggende Nachricht.
     * @param array $context Optionale zusätzliche Kontextdaten.
     */
    public static function log( $message, $context = array() ) {
        $log_entry = '[worker-is] ' . $message;
        if ( ! empty( $context ) ) {
            $log_entry .= ' | Data: ' . json_encode( $context );
        }
        error_log( $log_entry );
    }
}
