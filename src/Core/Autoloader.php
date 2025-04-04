<?php
namespace WorkerIS\Core;

class Autoloader {
    public static function init(string $namespace, string $baseDir): void {
        spl_autoload_register(function ($class) use ($namespace, $baseDir) {
            if (strpos($class, $namespace . '\\') !== 0) {
                return;
            }

            $relative = substr($class, strlen($namespace . '\\'));
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $fullPath = rtrim($baseDir, '/') . DIRECTORY_SEPARATOR . $relativePath;

            if (file_exists($fullPath)) {
                require_once $fullPath;
            } else {
                error_log("[Autoloader] Datei nicht gefunden für Klasse $class → $fullPath");
            }
        });
    }
}
