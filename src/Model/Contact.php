<?php
namespace WorkerIS\Model;

use WorkerIS\Core\Logger;

class Contact {
    public static function find_by_profile(string $profile_id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}worker_contacts WHERE profile_id = %s", $profile_id)
        );
    }

    public static function insert(string $profile_id, array $data): void {
        global $wpdb;

        $insert = array_merge([
            'id' => uniqid('', true),
            'profile_id' => $profile_id,
            'created_at' => current_time('mysql', 1)
        ], $data);

        $result = $wpdb->insert($wpdb->prefix . 'worker_contacts', $insert);

        if ($result === false) {
            Logger::error('Fehler beim Insert in Contacts', ['error' => $wpdb->last_error, 'data' => $insert]);
        }
    }

    public static function update(string $profile_id, array $data): void {
        global $wpdb;
        $result = $wpdb->update($wpdb->prefix . 'worker_contacts', $data, ['profile_id' => $profile_id]);

        if ($result === false) {
            Logger::error('Fehler beim Update von Contacts', ['id' => $profile_id, 'error' => $wpdb->last_error]);
        }
    }
}
