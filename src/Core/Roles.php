<?php
namespace WorkerIS\Core;

class Roles {
    /**
     * Erstellt benutzerdefinierte Rolle „vermittler“ und erweitert Administratoren.
     */
    public static function register(): void {
        add_role('vermittler', 'Vermittler', [
            'read'                        => true,
            'worker_is_view_profiles'     => true,
            'worker_is_create_profiles'   => true,
            'worker_is_edit_profiles'     => true,
        ]);

        if (class_exists(Logger::class)) {
            Logger::info('Rolle „vermittler“ registriert');
        }

        // Administrator erweitern
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
                $admin->add_cap($cap);
            }

            if (class_exists(Logger::class)) {
                Logger::info('Capabilities dem Administrator hinzugefügt', ['caps' => $caps]);
            }
        }
    }

    /**
     * Entfernt Rolle „vermittler“ und räumt Capabilities beim Admin auf.
     */
    public static function remove(): void {
        remove_role('vermittler');

        if (class_exists(Logger::class)) {
            Logger::info('Rolle „vermittler“ entfernt');
        }

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

            if (class_exists(Logger::class)) {
                Logger::info('Capabilities vom Administrator entfernt', ['caps' => $caps]);
            }
        }
    }
}
