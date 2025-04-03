<?php
namespace WorkerIS\Model;

use WorkerIS\Core\Logger;

class Profile {
    /**
     * Holt ein Profil anhand der ID inkl. des deserialisierten Profile-Data-Feldes.
     */
    public static function find(string $id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}worker_profiles WHERE id = %s", $id)
        );

        if (!$row) {
            Logger::warn("Profil nicht gefunden (find)", ['id' => $id]);
            return null;
        }

        $row->profile_data = maybe_unserialize($row->profile_data);
        return $row;
    }

    /**
     * Gibt alle Profile sortiert nach Erstellungsdatum zurück.
     */
    public static function all(): array {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}worker_profiles ORDER BY created_at DESC");
    }

    /**
     * Erstellt ein neues Profil inkl. dynamischer Felder (serialisiert).
     */
    public static function create(array $data): string {
        global $wpdb;

        $id = uniqid('', true);
        $insert = [
            'id'            => $id,
            'name'          => $data['name'] ?? '',
            'email'         => $data['email'] ?? '',
            'profile_data'  => maybe_serialize($data['profile_data'] ?? []),
            'assigned_user_id' => intval($data['assigned_user_id'] ?? 0),
            'created_at'    => current_time('mysql', 1),
            'updated_at'    => current_time('mysql', 1),
        ];

        $result = $wpdb->insert($wpdb->prefix . 'worker_profiles', $insert);

        if ($result === false) {
            Logger::error('Fehler beim Erstellen des Profils', ['error' => $wpdb->last_error, 'data' => $insert]);
        } else {
            Logger::info('Profil erstellt', ['id' => $id]);
        }

        return $id;
    }

    /**
     * Aktualisiert ein bestehendes Profil anhand der ID.
     */
    public static function update(string $id, array $data): void {
        global $wpdb;

        $update = [
            'name'         => $data['name'] ?? '',
            'email'        => $data['email'] ?? '',
            'profile_data' => maybe_serialize($data['profile_data'] ?? []),
            'assigned_user_id' => intval($data['assigned_user_id'] ?? 0),
            'updated_at'   => current_time('mysql', 1),
        ];

        $result = $wpdb->update($wpdb->prefix . 'worker_profiles', $update, ['id' => $id]);

        if ($result === false) {
            Logger::error('Fehler beim Aktualisieren des Profils', ['id' => $id, 'error' => $wpdb->last_error]);
        } else {
            Logger::info('Profil aktualisiert', ['id' => $id]);
        }
    }

    /**
     * (Optional) Löscht ein Profil vollständig.
     */
    public static function delete(string $id): void {
        global $wpdb;

        $result = $wpdb->delete($wpdb->prefix . 'worker_profiles', ['id' => $id]);

        if ($result === false) {
            Logger::error('Fehler beim Löschen des Profils', ['id' => $id]);
        } else {
            Logger::info('Profil gelöscht', ['id' => $id]);
        }
    }
}
