<?php
namespace WorkerIS;

class DB_Manager {

    /**
     * Erstellt/aktualisiert die Tabellen worker_profiles und worker_contacts.
     */
    public function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabelle für Profile
        $profile_table = $wpdb->prefix . 'worker_profiles';
        $sql1 = "CREATE TABLE $profile_table (
            id char(36) NOT NULL,
            worker_id char(3) NOT NULL,
            profile_data longtext NOT NULL,
            status_id int NOT NULL DEFAULT 1,
            assigned_user_id int DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY worker_id (worker_id),
            KEY assigned_user_id (assigned_user_id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        Logger::log('Database table worker_profiles installed/updated.');

        // Tabelle für Kontakte
        $contacts_table = $wpdb->prefix . 'worker_contacts';
        $sql2 = "CREATE TABLE $contacts_table (
            id char(36) NOT NULL,
            profile_id char(36) NOT NULL,
            worker_id char(3) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefon varchar(20) NOT NULL,
            adresse text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY profile_id (profile_id)
        ) $charset_collate;";
        dbDelta($sql2);
        Logger::log('Database table worker_contacts installed/updated.');
    }

    /**
     * Legt ein neues Profil an.
     */
    public function create_profile($profile_data, $worker_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_profiles';
        $id = uniqid('', true);

        if (!isset($profile_data['anonymous']) || !isset($profile_data['detailed'])) {
            Logger::log('Profile creation failed: Incomplete profile data structure.', ['profile_data' => $profile_data]);
            return false;
        }

        $data = [
            'id'            => $id,
            'worker_id'     => $worker_id,
            'profile_data'  => maybe_serialize($profile_data),
            'status_id'     => 1,
            'created_at'    => current_time('mysql', 1),
            'updated_at'    => current_time('mysql', 1)
        ];

        $result = $wpdb->insert($table, $data, ['%s', '%s', '%s', '%d', '%s', '%s']);

        if ($result === false) {
            Logger::log('DB insert failed in create_profile', ['data' => $data]);
            return false;
        }

        Logger::log('Profile created in DB', ['profile_id' => $id, 'worker_id' => $worker_id]);
        return $id;
    }

    /**
     * Fügt einen neuen Kontakt hinzu.
     */
    public function insert_contact($profile_id, $worker_id, $name, $email, $telefon, $adresse) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_contacts';

        $data = [
            'id'         => uniqid('', true),
            'profile_id' => $profile_id,
            'worker_id'  => $worker_id,
            'name'       => $name,
            'email'      => $email,
            'telefon'    => $telefon,
            'adresse'    => $adresse,
            'created_at' => current_time('mysql', 1)
        ];

        $result = $wpdb->insert($table, $data);

        if ($result === false || $wpdb->last_error) {
            Logger::log('Fehler beim Einfügen in worker_contacts', ['sql_error' => $wpdb->last_error, 'data' => $data]);
        } else {
            Logger::log('Kontakt erfolgreich gespeichert', ['profile_id' => $profile_id]);
        }

        return $result;
    }

    /**
     * Aktualisiert bestehende Kontaktdaten anhand der profile_id.
     */
    public function update_contact($profile_id, $name, $email, $telefon, $adresse) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_contacts';

        $data = [
            'name'    => $name,
            'email'   => $email,
            'telefon' => $telefon,
            'adresse' => $adresse
        ];

        $result = $wpdb->update($table, $data, ['profile_id' => $profile_id], ['%s', '%s', '%s', '%s'], ['%s']);

        if ($result === false || $wpdb->last_error) {
            Logger::log('Fehler beim Aktualisieren von worker_contacts', ['sql_error' => $wpdb->last_error, 'data' => $data]);
        } else {
            Logger::log('Kontaktdaten aktualisiert', ['profile_id' => $profile_id]);
        }

        return $result;
    }

    /**
     * Löscht Kontaktdaten und automatisch das zugehörige Profil.
     */
    public function delete_contact($profile_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_contacts';

        $result = $wpdb->delete($table, ['profile_id' => $profile_id], ['%s']);

        if ($result === false || $wpdb->last_error) {
            Logger::log('Fehler beim Löschen von worker_contacts', ['sql_error' => $wpdb->last_error, 'profile_id' => $profile_id]);
        } else {
            Logger::log('Kontakt gelöscht', ['profile_id' => $profile_id]);
            // Verknüpftes Profil ebenfalls löschen
            $this->delete_profile($profile_id);
        }

        return $result;
    }

    /**
     * Aktualisiert das Profil (z. B. dynamische Felder).
     */
    public function update_profile($profile_id, $profile_data) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_profiles';

        $data = [
            'profile_data' => maybe_serialize($profile_data),
            'updated_at'   => current_time('mysql', 1)
        ];

        $result = $wpdb->update($table, $data, ['id' => $profile_id], ['%s', '%s'], ['%s']);

        if ($result === false) {
            Logger::log('DB update failed in update_profile', ['profile_id' => $profile_id]);
            return false;
        }

        Logger::log('Profile updated in DB', ['profile_id' => $profile_id]);
        return true;
    }

    /**
     * Löscht ein Profil anhand der ID.
     */
    public function delete_profile($profile_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'worker_profiles';

        $result = $wpdb->delete($table, ['id' => $profile_id], ['%s']);

        if ($result === false) {
            Logger::log('DB delete failed in delete_profile', ['profile_id' => $profile_id]);
            return false;
        }

        Logger::log('Profile deleted from DB', ['profile_id' => $profile_id]);
        return true;
    }
}
