<?php
/*
Plugin Name: worker-is
Plugin URI:  https://example.com/worker-is
Description: Ein WordPress-Plugin zur Verwaltung anonymisierter Worker-Profile und Arbeitgeber-Anfragen im Rahmen des WORK-Projekts.
Version:     1.0.0
Author:      Dein Name
Author URI:  https://example.com
License:     GPL2
*/

// Sicherheitsabfrage: Verhindert direkten Aufruf der Datei.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definiere Plugin-Konstanten für Pfad und URL.
define( 'WORKERIS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WORKERIS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * PSR-4-kompatibler Autoloader für den Namensraum WorkerIS.
 * Alle Klassen im Plugin sollen den Namensraum "WorkerIS" verwenden und im Ordner "includes" liegen.
 */
spl_autoload_register( function ( $class ) {
    $prefix   = 'WorkerIS\\';
    $base_dir = WORKERIS_PLUGIN_PATH . 'includes/';
    $len      = strlen( $prefix );
    
    // Prüfe, ob die Klasse zum definierten Namensraum gehört.
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }
    
    // Ermittle den relativen Klassennamen (ohne Prefix)
    $relative_class = substr( $class, $len );
    // Ersetze Namespace-Trennzeichen durch Verzeichnis-Trennzeichen und hänge ".php" an.
    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
    
    // Falls die Datei existiert, binde sie ein.
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

/**
 * Plugin-Aktivierung: Installation der Datenbanktabellen und Hinzufügen benutzerdefinierter Rollen.
 */
function workeris_plugin_activation() {
    // Installiere die Datenbanktabellen.
    $db_manager = new WorkerIS\DB_Manager();
    $db_manager->install();
    
    // Füge benutzerdefinierte Rollen hinzu.
    $user_manager = new WorkerIS\User_Manager();
    $user_manager->add_custom_roles();
    
    // Lade Übersetzungsdateien.
    load_plugin_textdomain( 'worker-is', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Logge die Aktivierung.
    WorkerIS\Logger::log( 'Plugin activated and initial setup completed.' );
}
register_activation_hook( __FILE__, 'workeris_plugin_activation' );

/**
 * Plugin-Deaktivierung: Entfernt benutzerdefinierte Rollen.
 */
function workeris_plugin_deactivation() {
    $user_manager = new WorkerIS\User_Manager();
    $user_manager->remove_custom_roles();
    
    WorkerIS\Logger::log( 'Plugin deactivated and custom roles removed.' );
}
register_deactivation_hook( __FILE__, 'workeris_plugin_deactivation' );

/**
 * Registrierung der REST API-Endpunkte.
 */
function workeris_register_api_endpoints() {
    add_action( 'rest_api_init', function () {
        // Endpunkt zum Erstellen eines Worker-Profils.
        register_rest_route( 'v1/profiles', '/create', [
            'methods'             => 'POST',
            'callback'            => [ 'WorkerIS\\API_Handler', 'create_profile' ],
            'permission_callback' => [ 'WorkerIS\\API_Handler', 'check_permissions' ],
        ] );
        // Endpunkt zum Aktualisieren eines Worker-Profils.
        register_rest_route( 'v1/profiles', '/update', [
            'methods'             => 'PUT',
            'callback'            => [ 'WorkerIS\\API_Handler', 'update_profile' ],
            'permission_callback' => [ 'WorkerIS\\API_Handler', 'check_permissions' ],
        ] );
        // Endpunkt zum Löschen eines Worker-Profils.
        register_rest_route( 'v1/profiles', '/delete', [
            'methods'             => 'DELETE',
            'callback'            => [ 'WorkerIS\\API_Handler', 'delete_profile' ],
            'permission_callback' => [ 'WorkerIS\\API_Handler', 'check_permissions' ],
        ] );
        // Endpunkt zum Abrufen von Arbeitgeber-Anfragen.
        register_rest_route( 'v1/employers', '/requests', [
            'methods'             => 'GET',
            'callback'            => [ 'WorkerIS\\API_Handler', 'list_employer_requests' ],
            'permission_callback' => [ 'WorkerIS\\API_Handler', 'check_permissions' ],
        ] );
    } );
}
workeris_register_api_endpoints();

/**
 * Admin-Enqueue-Hook: Bindet JavaScript- und CSS-Dateien in den Admin-Bereich ein.
 */
add_action('admin_enqueue_scripts', 'workeris_enqueue_admin_scripts');
function workeris_enqueue_admin_scripts($hook) {
    // Wir laden die Scripts nur auf unseren Plugin-Seiten, z. B. wenn "worker-is" im Hook vorkommt.
    if ( strpos( $hook, 'worker-is' ) === false ) {
        return;
    }
    // Bootstrap CSS (über CDN)
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    // jQuery UI Sortable (für Drag & Drop)
    wp_enqueue_script('jquery-ui-sortable');
    // Chart.js für die Diagramme (Reports)
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    // Bootstrap JS (abhängig von jQuery)
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
    // Reports-spezifisches Script
    wp_enqueue_script('workeris-reports', WORKERIS_PLUGIN_URL . 'assets/js/reports.js', array('chart-js'), '1.0.0', true);
    // Interaktiver Form-Builder
    wp_enqueue_script('workeris-form-builder', WORKERIS_PLUGIN_URL . 'assets/js/form-builder.js', array('jquery'), '1.0.0', true);
}

/**
 * Initialisierung des Admin-Interfaces.
 */
new WorkerIS\Admin_Pages();
new WorkerIS\User_Pages();
