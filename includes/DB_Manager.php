<?php
namespace WorkerIS;

class DB_Manager {
    /**
     * Installiert bzw. aktualisiert die notwendigen Datenbanktabellen.
     * - worker_profiles: Speichert die Profile als JSON mit den Schlüsseln "anonymous" und "detailed".
     *   Zusätzlich wird die Spalte assigned_user_id hinzugefügt, um den zuständigen Vermittler zu speichern.
     * - worker_contacts: Speichert die Kontaktdaten der Arbeitssuchenden separat.
     */
    public function install() {
        global $wpdb;
        
        // Tabelle für Worker-Profile (JSON: { "anonymous": {...}, "detailed": {...} })
        $table_name = $wpdb->prefix . 'worker_profiles';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id char(36) NOT NULL,
            worker_id char(3) NOT NULL,
            profile_data longtext NOT NULL, /* JSON: { 'anonymous': {...}, 'detailed': {...} } */
            status_id int NOT NULL DEFAULT 1,
            assigned_user_id int DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY worker_id (worker_id),
            KEY assigned_user_id (assigned_user_id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        Logger::log( 'Database table worker_profiles installed/updated.' );
        
        // Tabelle für Worker-Kontaktdaten
        $contacts_table = $wpdb->prefix . 'worker_contacts';
        $sql2 = "CREATE TABLE $contacts_table (
            id char(36) NOT NULL,
            profile_id char(36) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefon varchar(20) NOT NULL,
            adresse text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY profile_id (profile_id)
        ) $charset_collate;";
        dbDelta( $sql2 );
        Logger::log( 'Database table worker_contacts installed/updated.' );
    }
    
    /**
     * Erstellt ein neues Profil und speichert die übergebene Worker-ID.
     *
     * Erwartet, dass $profile_data ein assoziatives Array ist,
     * das die Schlüssel "anonymous" und "detailed" enthält.
     *
     * @param array $profile_data Die zu speichernden Profildaten.
     * @param string $worker_id Die bereits generierte Worker-ID.
     * @return string|false Eindeutige ID des erstellten Profils oder false bei Fehler.
     */
    public function create_profile( $profile_data, $worker_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'worker_profiles';
        $id = uniqid( '', true );
        
        // Prüfe, ob beide Bereiche vorhanden sind.
        if ( ! isset( $profile_data['anonymous'] ) || ! isset( $profile_data['detailed'] ) ) {
            Logger::log( 'Profile creation failed: Incomplete profile data structure.', array( 'profile_data' => $profile_data ) );
            return false;
        }
        
        $data = array(
            'id'           => $id,
            'worker_id'    => $worker_id,
            'profile_data' => maybe_serialize( $profile_data ),
            'status_id'    => 1,
            // assigned_user_id wird hier nicht gesetzt – dieser Wert kann nachträglich aktualisiert werden.
            'created_at'   => current_time( 'mysql', 1 ),
            'updated_at'   => current_time( 'mysql', 1 ),
        );
        $format = array( '%s', '%s', '%s', '%d', '%s', '%s' );
        $result = $wpdb->insert( $table_name, $data, $format );
        
        if ( false === $result ) {
            Logger::log( 'DB insert failed in create_profile', array( 'data' => $data ) );
            return false;
        }
        
        Logger::log( 'Profile created in DB', array( 'profile_id' => $id, 'worker_id' => $worker_id ) );
        return $id;
    }
    
    /**
     * Aktualisiert ein bestehendes Profil.
     *
     * @param string $profile_id Die eindeutige ID des Profils.
     * @param array  $profile_data Die neuen Profildaten.
     * @return bool True bei Erfolg, false sonst.
     */
    public function update_profile( $profile_id, $profile_data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'worker_profiles';
        
        $data = array(
            'profile_data' => maybe_serialize( $profile_data ),
            'updated_at'   => current_time( 'mysql', 1 ),
        );
        $where = array( 'id' => $profile_id );
        $result = $wpdb->update( $table_name, $data, $where, array( '%s', '%s' ), array( '%s' ) );
        
        if ( false === $result ) {
            Logger::log( 'DB update failed in update_profile', array( 'profile_id' => $profile_id ) );
            return false;
        }
        
        Logger::log( 'Profile updated in DB', array( 'profile_id' => $profile_id ) );
        return true;
    }
    
    /**
     * Löscht ein Profil aus der Datenbank.
     *
     * @param string $profile_id Die eindeutige ID des zu löschenden Profils.
     * @return bool True, wenn der Löschvorgang erfolgreich war.
     */
    public function delete_profile( $profile_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'worker_profiles';
        $result = $wpdb->delete( $table_name, array( 'id' => $profile_id ), array( '%s' ) );
        
        if ( false === $result ) {
            Logger::log( 'DB delete failed in delete_profile', array( 'profile_id' => $profile_id ) );
            return false;
        }
        
        Logger::log( 'Profile deleted from DB', array( 'profile_id' => $profile_id ) );
        return true;
    }
    
    /**
     * Holt Arbeitgeber-Anfragen aus der Datenbank.
     *
     * @param int|null $status Optionaler Filter: Nur Anfragen mit diesem Status.
     * @param int $page Die Seitenzahl.
     * @param int $limit Anzahl der Einträge pro Seite.
     * @return array Array von Arbeitgeber-Anfragen als Objekte.
     */
    public function get_employer_requests( $status = null, $page = 1, $limit = 10 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'employer_requests';
        $offset = (int)( ($page - 1) * $limit );
        
        $sql = "SELECT * FROM $table_name";
        $conditions = array();
        if ( $status !== null ) {
            $conditions[] = $wpdb->prepare( "status_id = %d", $status );
        }
        if ( ! empty( $conditions ) ) {
            $sql .= " WHERE " . implode( " AND ", $conditions );
        }
        $sql .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $sql = $wpdb->prepare( $sql, $limit, $offset );
        
        $results = $wpdb->get_results( $sql );
        Logger::log( 'Employer requests retrieved', array( 'status' => $status, 'page' => $page, 'limit' => $limit ) );
        return $results;
    }
    
    /**
     * Aktualisiert den Status einer Arbeitgeber-Anfrage und speichert interne Notizen.
     *
     * @param string $request_id Die eindeutige ID der Anfrage.
     * @param int $status Der neue Status.
     * @param string $notes Optionale interne Notizen.
     * @return bool True, wenn der Vorgang erfolgreich war.
     */
    public function update_employer_request( $request_id, $status, $notes = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'employer_requests';
        $data = array(
            'status_id'  => $status,
            'updated_at' => current_time( 'mysql', 1 ),
            'notizen'    => $notes,
        );
        $where = array( 'id' => $request_id );
        $result = $wpdb->update( $table_name, $data, $where, array( '%d', '%s', '%s' ), array( '%s' ) );
        
        if ( false === $result ) {
            Logger::log( 'DB update failed in update_employer_request', array( 'request_id' => $request_id ) );
            return false;
        }
        
        Logger::log( 'Employer request updated in DB', array( 'request_id' => $request_id, 'new_status' => $status ) );
        return true;
    }
    
    /**
     * Holt die Historie einer Arbeitgeber-Anfrage.
     *
     * Erwartet, dass eine Tabelle "employer_request_history" existiert, in der alle Änderungen protokolliert werden.
     *
     * @param string $request_id Die eindeutige ID der Anfrage.
     * @return array Array der Historie-Einträge als Objekte.
     */
    public function get_employer_request_history( $request_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'employer_request_history';
        $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE request_id = %s ORDER BY timestamp ASC", $request_id );
        $results = $wpdb->get_results( $sql );
        Logger::log( 'Employer request history retrieved', array( 'request_id' => $request_id, 'count' => count( $results ) ) );
        return $results;
    }
}
