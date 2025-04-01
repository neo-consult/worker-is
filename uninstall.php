<?php
/**
 * Uninstall-Skript für das Plugin "worker-is"
 *
 * Dieses Skript wird von WordPress aufgerufen, wenn das Plugin deinstalliert wird.
 * Es löscht die von "worker-is" erstellten Daten aus der Datenbank und ggf. gespeicherte Optionen.
 */

// Sicherheitsabfrage: Sicherstellen, dass das Skript nur von WordPress aufgerufen wird.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Lösche die benutzerdefinierte Tabelle, die von unserem Plugin verwendet wird.
$table_name = $wpdb->prefix . 'worker_profiles';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Falls das Plugin Optionen in der wp_options-Tabelle gespeichert hat, können diese hier gelöscht werden.
// Beispiel: Lösche eine Option, falls vorhanden.
delete_option( 'worker_is_some_option' );
// Hier können weitere Optionen gelöscht werden, falls das Plugin zusätzliche Einstellungen speichert.
