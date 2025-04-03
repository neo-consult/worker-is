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

require_once WORKER_IS_PATH . 'src/Core/Autoloader.php';
\WorkerIS\Core\Autoloader::init('WorkerIS', WORKER_IS_PATH . 'src');

add_action('plugins_loaded', function () {
    if (is_admin()) {
        \WorkerIS\Hooks\Hooks::register();
    }
});

register_activation_hook(__FILE__, function () {
    \WorkerIS\Core\Autoloader::init('WorkerIS', WORKER_IS_PATH . 'src');

    (new \WorkerIS\Model\DB_Manager())->install();
    \WorkerIS\Core\Roles::register();
    delete_option('worker_is_plugin_disabled');

    if (class_exists('\WorkerIS\Core\Logger')) {
        \WorkerIS\Core\Logger::info('Plugin aktiviert und bereit');
    }
});

register_deactivation_hook(__FILE__, function () {
    if (function_exists('do_action')) {
        do_action('worker_is_before_deactivation');
    }

    wp_clear_scheduled_hook('worker_is_cron_event');
    update_option('worker_is_plugin_disabled', 1);

    if (class_exists('\WorkerIS\Core\Logger')) {
        \WorkerIS\Core\Logger::info('Plugin deaktiviert');
    }
});
