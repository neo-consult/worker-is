<?php
namespace WorkerIS;

class API_Handler {

    /**
     * Erzeugt ein neues Worker-Profil.
     *
     * Liest die JSON-Parameter, sanitisiert diese und generiert eine eindeutige Worker-ID.
     * Übergibt die Daten an den DB_Manager, um das Profil zu erstellen.
     * Falls "publish" gesetzt ist, wird nur der "anonymous"-Teil an die öffentliche Plattform übertragen.
     *
     * @param \WP_REST_Request $request Die REST API-Anfrage.
     * @return \WP_REST_Response|\WP_Error
     */
    public static function create_profile( $request ) {
        $params = Security::sanitize( $request->get_json_params() );

        if ( empty( $params['profile_data'] ) || !isset( $params['profile_data']['anonymous'] ) || !isset( $params['profile_data']['detailed'] ) ) {
            Logger::log( 'Profile creation failed: Incomplete profile data', array( 'request_data' => $params ) );
            return new \WP_Error( 'missing_data', __( 'Incomplete profile data. Both anonymous and detailed fields are required.', 'worker-is' ), array( 'status' => 400 ) );
        }

        // Generiere die Worker-ID einmalig.
        $worker_id = self::generate_worker_id();

        $db = new DB_Manager();
        $profile_id = $db->create_profile( $params['profile_data'], $worker_id );
        if ( ! $profile_id ) {
            Logger::log( 'Profile creation failed in DB insertion', array( 'request_data' => $params ) );
            return new \WP_Error( 'create_failed', __( 'Profile creation failed', 'worker-is' ), array( 'status' => 500 ) );
        }

        $published = false;
        if ( isset( $params['publish'] ) && $params['publish'] ) {
            // Nur der "anonymous"-Teil wird an die öffentliche Plattform übertragen.
            $published = self::publish_profile( $profile_id, $params['profile_data']['anonymous'] );
        }

        Logger::log( 'Profile created successfully', array( 'profile_id' => $profile_id, 'worker_id' => $worker_id ) );
        $response = array(
            'message'    => __( 'Profile created', 'worker-is' ),
            'profile_id' => $profile_id,
            'worker_id'  => $worker_id,
            'published'  => $published,
        );

        return new \WP_REST_Response( $response, 201 );
    }

    /**
     * Aktualisiert ein bestehendes Worker-Profil.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function update_profile( $request ) {
        $params = Security::sanitize( $request->get_json_params() );

        if ( empty( $params['profile_id'] ) || empty( $params['profile_data'] ) ) {
            Logger::log( 'Profile update failed: Missing profile ID or data', array( 'request_data' => $params ) );
            return new \WP_Error( 'missing_data', __( 'Profile ID or data is missing', 'worker-is' ), array( 'status' => 400 ) );
        }

        $db = new DB_Manager();
        $result = $db->update_profile( $params['profile_id'], $params['profile_data'] );
        if ( ! $result ) {
            Logger::log( 'Profile update failed in DB update', array( 'profile_id' => $params['profile_id'] ) );
            return new \WP_Error( 'update_failed', __( 'Profile update failed', 'worker-is' ), array( 'status' => 500 ) );
        }

        Logger::log( 'Profile updated successfully', array( 'profile_id' => $params['profile_id'] ) );
        return new \WP_REST_Response( array( 'message' => __( 'Profile updated', 'worker-is' ) ), 200 );
    }

    /**
     * Löscht ein Worker-Profil.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function delete_profile( $request ) {
        $params = Security::sanitize( $request->get_json_params() );
        if ( empty( $params['profile_id'] ) ) {
            Logger::log( 'Profile deletion failed: Missing profile ID', array( 'request_data' => $params ) );
            return new \WP_Error( 'missing_data', __( 'Profile ID is missing', 'worker-is' ), array( 'status' => 400 ) );
        }

        $db = new DB_Manager();
        $result = $db->delete_profile( $params['profile_id'] );
        if ( ! $result ) {
            Logger::log( 'Profile deletion failed in DB delete', array( 'profile_id' => $params['profile_id'] ) );
            return new \WP_Error( 'delete_failed', __( 'Profile deletion failed', 'worker-is' ), array( 'status' => 500 ) );
        }

        Logger::log( 'Profile deleted successfully', array( 'profile_id' => $params['profile_id'] ) );
        return new \WP_REST_Response( array( 'message' => __( 'Profile deleted', 'worker-is' ) ), 200 );
    }

    /**
     * Ruft Arbeitgeber-Anfragen ab.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function list_employer_requests( $request ) {
        $status = Security::sanitize( $request->get_param( 'status' ) );
        $page   = intval( Security::sanitize( $request->get_param( 'page' ) ) );
        $limit  = intval( Security::sanitize( $request->get_param( 'limit' ) ) );
        $page   = $page ? $page : 1;
        $limit  = $limit ? $limit : 10;

        $db = new DB_Manager();
        $requests = $db->get_employer_requests( $status, $page, $limit );
        if ( empty( $requests ) ) {
            Logger::log( 'No employer requests found', array( 'status_filter' => $status ) );
            return new \WP_Error( 'no_requests', __( 'No employer requests found', 'worker-is' ), array( 'status' => 404 ) );
        }
        Logger::log( 'Employer requests retrieved', array( 'count' => count( $requests ) ) );
        return new \WP_REST_Response( $requests, 200 );
    }

    /**
     * Aktualisiert den Status einer Arbeitgeber-Anfrage und fügt interne Notizen hinzu.
     *
     * Erwartet in der Anfrage:
     * - request_id: Eindeutige ID der Anfrage.
     * - status: Neuer Status.
     * - notizen: Optionale interne Notizen.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function respond_employer_request( $request ) {
        $params = Security::sanitize( $request->get_json_params() );
        
        if ( empty( $params['request_id'] ) || empty( $params['status'] ) ) {
            Logger::log( 'Employer request update failed: Missing request ID or status', array( 'request_data' => $params ) );
            return new \WP_Error( 'missing_data', __( 'Request ID and new status are required', 'worker-is' ), array( 'status' => 400 ) );
        }
        
        $db = new DB_Manager();
        $result = $db->update_employer_request( $params['request_id'], $params['status'], isset( $params['notizen'] ) ? $params['notizen'] : '' );
        if ( ! $result ) {
            Logger::log( 'Employer request update failed in DB', array( 'request_id' => $params['request_id'] ) );
            return new \WP_Error( 'update_failed', __( 'Employer request update failed', 'worker-is' ), array( 'status' => 500 ) );
        }
        
        Logger::log( 'Employer request updated successfully', array( 'request_id' => $params['request_id'] ) );
        return new \WP_REST_Response( array( 'message' => __( 'Employer request updated', 'worker-is' ) ), 200 );
    }

    /**
     * Gibt den Verlauf (Historie) einer Arbeitgeber-Anfrage zurück.
     *
     * Erwartet als Parameter:
     * - request_id: Eindeutige ID der Anfrage.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public static function get_employer_request_history( $request ) {
        $request_id = Security::sanitize( $request->get_param( 'request_id' ) );
        if ( empty( $request_id ) ) {
            Logger::log( 'History retrieval failed: Missing request ID' );
            return new \WP_Error( 'missing_data', __( 'Request ID is required', 'worker-is' ), array( 'status' => 400 ) );
        }
        
        $db = new DB_Manager();
        $history = $db->get_employer_request_history( $request_id );
        if ( empty( $history ) ) {
            Logger::log( 'No history found for employer request', array( 'request_id' => $request_id ) );
            return new \WP_Error( 'no_history', __( 'No history found for this employer request', 'worker-is' ), array( 'status' => 404 ) );
        }
        
        Logger::log( 'Employer request history retrieved', array( 'request_id' => $request_id, 'count' => count( $history ) ) );
        return new \WP_REST_Response( $history, 200 );
    }

    /**
     * Prüft, ob der aktuelle Nutzer die erforderlichen Rechte besitzt.
     *
     * @return bool True, wenn berechtigt.
     */
    public static function check_permissions() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Generiert eine einzigartige 3-stellige Worker-ID.
     *
     * Diese Methode erzeugt eine zufällige 3-stellige alphanumerische ID und prüft in der DB,
     * ob sie bereits existiert. Falls ja, wird eine neue generiert.
     *
     * @return string Die eindeutige Worker-ID.
     */
    public static function generate_worker_id() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'worker_profiles';
        $max_attempts = 10;
        $attempt = 0;
        do {
            $worker_id = self::generate_random_worker_id();
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE worker_id = %s", $worker_id ) );
            $attempt++;
            if ( $attempt > $max_attempts ) {
                break;
            }
        } while ( $exists > 0 );
        return $worker_id;
    }

    /**
     * Generiert eine zufällige 3-stellige alphanumerische Worker-ID.
     *
     * @return string Die generierte Worker-ID.
     */
    private static function generate_random_worker_id() {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $worker_id = '';
        for ( $i = 0; $i < 3; $i++ ) {
            $worker_id .= $characters[ random_int( 0, strlen( $characters ) - 1 ) ];
        }
        return $worker_id;
    }

    /**
     * Publiziert ein Worker-Profil auf der öffentlichen Plattform.
     *
     * Überträgt hier nur den "anonymous"-Teil der Profil-Daten.
     *
     * @param string $profile_id Die ID des erstellten Profils.
     * @param array $anonymous_profile_data Nur die anonymen Daten.
     * @return bool True, wenn die Veröffentlichung erfolgreich war.
     */
    private static function publish_profile( $profile_id, $anonymous_profile_data ) {
        // Hier könnte ein API-Aufruf an die öffentliche Plattform erfolgen.
        return true;
    }
}
