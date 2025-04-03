<?php
/**
 * Uninstall Worker-IS Plugin
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// DB + Optionen bereinigen
global $wpdb;

// Tabellen löschen
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}worker_profiles");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}worker_contacts");

// Optionen löschen
delete_option('worker_is_form_config');

// Optional: Custom log-Dateien löschen (wenn sicher!)
$logfile = plugin_dir_path(__FILE__) . 'logs/worker.log';
if (file_exists($logfile)) {
    unlink($logfile);
}
