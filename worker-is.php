<?php
/**
 * Plugin Name:       Worker-IS
 * Description:       Verwaltung von Vermittlungsprofilen inkl. dynamischer Felder.
 * Version:           1.0.0
 * Author:            Du
 */

defined('ABSPATH') || exit;

define('WORKER_IS_VERSION', '1.0.0');
define('WORKER_IS_PATH', plugin_dir_path(__FILE__));
define('WORKER_IS_URL', plugin_dir_url(__FILE__));

// Autoloader laden
require_once WORKER_IS_PATH . 'src/Core/Autoloader.php';
\WorkerIS\Core\Autoloader::init('WorkerIS', WORKER_IS_PATH . 'src');

add_action('plugins_loaded', function () {
    if (is_admin()) {
        \WorkerIS\Hooks\Hooks::register();

        if (class_exists(\WorkerIS\Core\Logger::class)) {
            \WorkerIS\Core\Logger::info('Hooks registriert über plugins_loaded');
        }
    }
});

register_activation_hook(__FILE__, function () {
    require_once WORKER_IS_PATH . 'src/Core/Autoloader.php';
    \WorkerIS\Core\Autoloader::init('WorkerIS', WORKER_IS_PATH . 'src');

    // Rollen und Berechtigungen setzen
    \WorkerIS\Core\Roles::register();

    // Tabellen anlegen
    $db = new \WorkerIS\Model\DB_Manager();
    $db->install();

    // Flags & Logging
    delete_option('worker_is_plugin_disabled');

    if (class_exists(\WorkerIS\Core\Logger::class)) {
        \WorkerIS\Core\Logger::info('Plugin aktiviert');
    }
});

register_deactivation_hook(__FILE__, function () {
    do_action('worker_is_before_deactivation');

    wp_clear_scheduled_hook('worker_is_cron_event');
    update_option('worker_is_plugin_disabled', 1);

    if (class_exists(\WorkerIS\Core\Logger::class)) {
        \WorkerIS\Core\Logger::info('Plugin deaktiviert');
    }
});
