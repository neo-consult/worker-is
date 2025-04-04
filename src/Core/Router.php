<?php
namespace WorkerIS\Core;

use WorkerIS\Controller\ProfileController;
use WorkerIS\Controller\FormConfigController;
use WorkerIS\Controller\LogViewerController;
use WorkerIS\Controller\RoleManagerController;

class Router {
    public static function dispatch(): void {
        $page = $_GET['page'] ?? '';

        switch ($page) {
            case 'worker-is':
                if (!current_user_can('worker_is_view_profiles')) {
                    self::deny();
                }
                ProfileController::index();
                break;

            case 'worker-is-create':
                if (!current_user_can('worker_is_create_profiles')) {
                    self::deny();
                }
                ProfileController::create();
                break;

            case 'worker-is-edit':
                if (!current_user_can('worker_is_edit_profiles')) {
                    self::deny();
                }
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    self::deny('Keine Profil-ID übergeben.');
                }
                ProfileController::edit($id);
                break;

            case 'worker-is-config':
                if (!current_user_can('worker_is_manage_config')) {
                    self::deny();
                }
                FormConfigController::render();
                break;

            case 'worker-is-logs':
                if (!current_user_can('worker_is_view_logs')) {
                    self::deny();
                }
                LogViewerController::render();
                break;

            case 'worker-is-roles':
                if (!current_user_can('worker_is_manage_roles')) {
                    self::deny();
                }
                RoleManagerController::render();
                break;

            default:
                echo '<div class="wrap"><h1>Worker-IS</h1><p>Willkommen im Plugin-Dashboard.</p></div>';
                break;
        }
    }

    protected static function deny(string $message = 'Zugriff verweigert.'): void {
        wp_die(esc_html($message), '403 - Verboten', ['response' => 403]);
    }
}
