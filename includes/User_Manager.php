<?php
namespace WorkerIS;

class User_Manager {

    /**
     * Fügt benutzerdefinierte Rollen und Fähigkeiten hinzu.
     */
    public function add_custom_roles() {
        $role = add_role(
            'worker_manager',
            __( 'Worker Manager', 'worker-is' ),
            array(
                'read'                     => true,
                'manage_worker_profiles'   => true,
                'manage_employer_requests' => true,
            )
        );
        if ( $role ) {
            Logger::log( 'Custom role worker_manager added.' );
        } else {
            Logger::log( 'Failed to add custom role worker_manager.' );
        }
    }

    /**
     * Entfernt benutzerdefinierte Rollen.
     */
    public function remove_custom_roles() {
        remove_role( 'worker_manager' );
        Logger::log( 'Custom role worker_manager removed.' );
    }

    /**
     * Überprüft, ob ein Benutzer eine bestimmte Fähigkeit besitzt.
     *
     * @param int $user_id Die Benutzer-ID.
     * @param string $capability Die zu prüfende Fähigkeit.
     * @return bool True, wenn der Benutzer die Fähigkeit besitzt.
     */
    public static function user_can( $user_id, $capability ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }
        return $user->has_cap( $capability );
    }
}
