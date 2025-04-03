<?php
namespace WorkerIS\Model;

use WorkerIS\Core\Logger;

class DB_Manager {

    /**
     * Erstellt oder aktualisiert die notwendigen Tabellen für das Plugin.
     */
    public function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        // Tabelle: worker_profiles
        $sql_profiles = "CREATE TABLE {$wpdb->prefix}worker_profiles (
            id char(36) NOT NULL,
            worker_id char(3) NOT NULL,
            profile_data longtext NOT NULL,
            assigned_user_id int DEFAULT NULL,
            owner_id int DEFAULT NULL,
            status_id int DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY worker_id (worker_id),
            KEY assigned_user_id (assigned_user_id),
            KEY owner_id (owner_id)
        ) $charset;";

        // Tabelle: worker_contacts
        $sql_contacts = "CREATE TABLE {$wpdb->prefix}worker_contacts (
            id char(36) NOT NULL,
            profile_id char(36) NOT NULL,
            worker_id char(3) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefon varchar(20),
            adresse text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY profile_id (profile_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql_profiles);
        Logger::info('Tabelle worker_profiles installiert oder aktualisiert.');

        dbDelta($sql_contacts);
        Logger::info('Tabelle worker_contacts installiert oder aktualisiert.');

        $this->maybe_insert_default_config();
    }

    /**
     * Optional: Initiale Konfiguration für dynamische Felder speichern.
     */
    protected function maybe_insert_default_config(): void {
        if (!get_option('worker_is_form_config')) {
            $default = [
                'version'   => '1.0',
                'anonymous' => json_encode([
                    ['type' => 'text', 'label' => 'Spitzname'],
                    ['type' => 'checkbox', 'label' => 'Bereiche', 'options' => ['IT', 'Pflege', 'Handwerk']]
                ]),
                'detailed'  => json_encode([
                    ['type' => 'textarea', 'label' => 'Erfahrung', 'max_length' => 300]
                ])
            ];

            update_option('worker_is_form_config', json_encode($default));
            Logger::info('Standard-Konfiguration für worker_is_form_config angelegt.');
        }
    }
}
