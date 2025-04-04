<?php
// Goal: Clean schema with correct logging, retain original comments and structure, update contact table fields

namespace WorkerIS\Model;

use WorkerIS\Core\Logger;

class DB_Manager {

    /**
     * Creates required database tables.
     */
    public function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        // Tabelle: worker_profiles
        $profiles_table = $wpdb->prefix . 'worker_profiles';
        $sql_profiles = "CREATE TABLE $profiles_table (
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
            UNIQUE KEY (worker_id),
            KEY (assigned_user_id),
            KEY (owner_id)
        ) $charset;";

        // Tabelle: worker_contacts
        $contacts_table = $wpdb->prefix . 'worker_contacts';
        $sql_contacts = "CREATE TABLE $contacts_table (
            id char(36) NOT NULL,
            profile_id char(36) NOT NULL,
            firstname varchar(255) NOT NULL,
            lastname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            address text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY (profile_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        Logger::info('Creating database table: ' . $profiles_table);
        dbDelta($sql_profiles);

        Logger::info('Creating database table: ' . $contacts_table);
        dbDelta($sql_contacts);

        $this->maybe_insert_default_config();

        Logger::info('Database installation complete.');
    }

    /**
     * Drops all plugin tables.
     */
    public function uninstall(): void {
        global $wpdb;

        $profiles_table = $wpdb->prefix . 'worker_profiles';
        $contacts_table = $wpdb->prefix . 'worker_contacts';

        $wpdb->query("DROP TABLE IF EXISTS $profiles_table;");
        $wpdb->query("DROP TABLE IF EXISTS $contacts_table;");

        Logger::info('All plugin tables dropped.');
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
