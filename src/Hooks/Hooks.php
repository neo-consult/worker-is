<?php
namespace WorkerIS\Hooks;

use WorkerIS\Core\Router;

class Hooks {
    public static function register(): void {
        add_action('admin_menu', [self::class, 'register_menu']);

        // POST: Speichern von Profilen
        add_action('admin_post_worker_is_store_profile', [\WorkerIS\Controller\ProfileController::class, 'store']);

        // POST: Rollenrechte ändern
        add_action('admin_post_worker_is_update_role_capability', [\WorkerIS\Controller\RoleManagerController::class, 'update']);

        // POST: Form-Konfiguration speichern
        add_action('admin_post_worker_is_save_form_config', [\WorkerIS\Controller\FormConfigController::class, 'save']);
    }

    public static function register_menu(): void {
        // Zeige Hauptmenü nur, wenn Nutzer irgendeine Worker-IS-Capability hat
        if (
            !current_user_can('worker_is_view_profiles') &&
            !current_user_can('worker_is_create_profiles') &&
            !current_user_can('worker_is_manage_config') &&
            !current_user_can('worker_is_view_logs') &&
            !current_user_can('worker_is_manage_roles')
        ) {
            return;
        }

        // Hauptmenü (Zentrale Router-Seite)
        add_menu_page(
            'Worker-IS',
            'Worker-IS',
            'read', // Zugriff wird über Subpages geregelt
            'worker-is',
            [Router::class, 'dispatch'],
            'dashicons-groups',
            25
        );

        // Submenu: Profile Übersicht
        if (current_user_can('worker_is_view_profiles')) {
            add_submenu_page(
                'worker-is',
                'Profile',
                'Profile',
                'worker_is_view_profiles',
                'worker-is',
                [Router::class, 'dispatch']
            );
        }

        // Submenu: Neues Profil
        if (current_user_can('worker_is_create_profiles')) {
            add_submenu_page(
                'worker-is',
                'Neues Profil erstellen',
                'Neu',
                'worker_is_create_profiles',
                'worker-is-create',
                [Router::class, 'dispatch']
            );
        }

        // Submenu: Felder konfigurieren
        if (current_user_can('worker_is_manage_config')) {
            add_submenu_page(
                'worker-is',
                'Formularfelder',
                'Felder konfigurieren',
                'worker_is_manage_config',
                'worker-is-config',
                [Router::class, 'dispatch']
            );
        }

        // Submenu: Logs
        if (current_user_can('worker_is_view_logs')) {
            add_submenu_page(
                'worker-is',
                'Logs',
                'Logs',
                'worker_is_view_logs',
                'worker-is-logs',
                [Router::class, 'dispatch']
            );
        }

        // Submenu: Rollen
        if (current_user_can('worker_is_manage_roles')) {
            add_submenu_page(
                'worker-is',
                'Rollen & Rechte',
                'Rollen',
                'worker_is_manage_roles',
                'worker-is-roles',
                [Router::class, 'dispatch']
            );
        }
    }
}
