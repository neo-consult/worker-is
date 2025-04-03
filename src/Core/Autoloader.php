<?php
namespace WorkerIS\Core;

class Autoloader {
    public static function init(string $namespace, string $basePath): void {
        spl_autoload_register(function ($class) use ($namespace, $basePath) {
            if (strpos($class, $namespace . '\\') !== 0) return;

            $relative = str_replace('\\', '/', substr($class, strlen($namespace) + 1));
            $file = $basePath . $relative . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
