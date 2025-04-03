<?php
namespace WorkerIS\Core;

class Logger {
    protected static string $log_file = WORKER_IS_PATH . 'logs/worker-is.log';

    public static function log(string $message, array $data = [], string $level = 'info'): void {
        $entry = [
            'time' => current_time('mysql', 1),
            'level' => strtoupper($level),
            'message' => $message,
            'data' => $data
        ];

        $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents(self::$log_file, '[' . $level . '] ' . $json . PHP_EOL, FILE_APPEND);
    }

    public static function info(string $message, array $data = []): void {
        self::log($message, $data, 'info');
    }

    public static function warn(string $message, array $data = []): void {
        self::log($message, $data, 'warning');
    }

    public static function error(string $message, array $data = []): void {
        self::log($message, $data, 'error');
    }

    public static function get_logs(): array {
        if (!file_exists(self::$log_file)) return [];
        return file(self::$log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    public static function clear(): void {
        file_put_contents(self::$log_file, '');
    }
    
}
