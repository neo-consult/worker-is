<?php
namespace WorkerIS\Core;

class Logger
{
    /**
     * Schreibt eine Logzeile mit Level, Nachricht und optionalen Daten.
     */
    public static function log(string $message, array $data = [], string $level = 'INFO'): void
    {
        $timestamp = current_time('Y-m-d H:i:s');
        $logPath   = self::get_log_path();
        $logDir    = dirname($logPath);

        if (!file_exists($logDir)) {
            wp_mkdir_p($logDir);
        }

        $line = sprintf(
            "[%s] [%s] %s | Data: %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        @file_put_contents($logPath, $line, FILE_APPEND);
    }

    /**
     * Convenience-Methode für Info-Logs.
     */
    public static function info(string $message, array $data = []): void
    {
        self::log($message, $data, 'INFO');
    }

    /**
     * Convenience-Methode für Warnungen.
     */
    public static function warn(string $message, array $data = []): void
    {
        self::log($message, $data, 'WARNUNG');
    }

    /**
     * Convenience-Methode für Fehler.
     */
    public static function error(string $message, array $data = []): void
    {
        self::log($message, $data, 'FEHLER');
    }

    /**
     * Gibt den vollständigen Pfad zur Logdatei zurück.
     */
    public static function get_log_path(): string
    {
        return WP_CONTENT_DIR . '/uploads/worker-is.log';
    }
}
