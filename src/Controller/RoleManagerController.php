<?php
namespace WorkerIS\Controller;

use WorkerIS\Core\View;
use WorkerIS\Core\Logger;

class RoleManagerController {
    /**
     * Zeigt die Übersicht aller Rollen & ihrer Capabilities.
     */
    public static function render(): void {
        if (!current_user_can('worker_is_manage_roles')) {
            wp_die('Zugriff verweigert.');
        }

        $roles = get_editable_roles();

        $role_data = [];

        foreach ($roles as $slug => $details) {
            $caps = array_keys(array_filter($details['capabilities']));
            sort($caps);
            $role_data[] = [
                'name'         => $details['name'],
                'slug'         => $slug,
                'capabilities' => $caps
            ];
        }

        View::render('roles/list', ['roles' => $role_data]);
    }

    /**
     * Fügt eine Capability hinzu oder entfernt sie aus einer Rolle.
     */
    public static function update(): void {
        if (!current_user_can('worker_is_manage_roles')) {
            wp_die('Zugriff verweigert.');
        }

        check_admin_referer('worker_is_role_update', 'worker_is_nonce');

        $role_slug  = sanitize_text_field($_POST['role'] ?? '');
        $capability = sanitize_text_field($_POST['capability'] ?? '');
        $operation  = sanitize_text_field($_POST['operation'] ?? '');

        if (!$role_slug || !$capability || !in_array($operation, ['add', 'remove'], true)) {
            wp_die('Ungültige Daten übergeben.');
        }

        $role = get_role($role_slug);
        if (!$role) {
            wp_die('Rolle nicht gefunden.');
        }

        if ($operation === 'add') {
            $role->add_cap($capability);
            Logger::info("Capability hinzugefügt", compact('role_slug', 'capability'));
        } else {
            $role->remove_cap($capability);
            Logger::info("Capability entfernt", compact('role_slug', 'capability'));
        }

        wp_redirect(admin_url('admin.php?page=worker-is-roles&updated=1'));
        exit;
    }
}
