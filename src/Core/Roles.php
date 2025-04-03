<?php
namespace WorkerIS\Core;

class Roles {
    /**
     * Erstellt benutzerdefinierte Rolle „vermittler“ und erweitert Admin.
     */
    public static function register(): void {
        // Neue Rolle: Vermittler
        add_role('vermittler', 'Vermittler', [
            'read' => true,
            'worker_is_view_profiles'    => true,
            'worker_is_create_profiles'  => true,
            'worker_is_edit_profiles'    => true,
        ]);

        // Administrator erweitern
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('worker_is_view_profiles');
            $admin->add_cap('worker_is_create_profiles');
            $admin->add_cap('worker_is_edit_profiles');
            $admin->add_cap('worker_is_view_logs');
            $admin->add_cap('worker_is_manage_roles');
            $admin->add_cap('worker_is_manage_config');
        }
    }

    /**
     * Entfernt Rolle „vermittler“ und räumt Capabilities auf.
     */
    public static function remove(): void {
        // Rolle entfernen
        remove_role('vermittler');

        // Admin zurücksetzen
        $admin = get_role('administrator');
        if ($admin) {
            $caps = [
                'worker_is_view_profiles',
                'worker_is_create_profiles',
                'worker_is_edit_profiles',
                'worker_is_view_logs',
                'worker_is_manage_roles',
                'worker_is_manage_config',
            ];
            foreach ($caps as $cap) {
                $admin->remove_cap($cap);
            }
        }
    }
}
