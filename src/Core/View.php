<?php
namespace WorkerIS\Core;

class View {
    public static function render(string $template, array $data = []): void {
        extract($data);
        include WORKER_IS_PATH . 'src/View/' . $template . '.php';
    }
}
