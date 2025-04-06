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

    public static function insert(string $profile_id, array $data): bool {
        global $wpdb;

        $insert = [
            'id'         => uniqid('', true),
            'profile_id' => $profile_id,
            'firstname'  => $data['firstname'] ?? '',
            'lastname'   => $data['lastname'] ?? '',
            'email'      => $data['email'] ?? '',
            'phone'      => $data['phone'] ?? '',
            'address'    => $data['address'] ?? '',
            'created_at' => current_time('mysql', 1),
        ];

        $result = $wpdb->insert($wpdb->prefix . 'worker_contacts', $insert);

        if ($result === false) {
            Logger::error('Fehler beim Erstellen des Kontakts', [
                'error' => $wpdb->last_error,
                'data'  => $insert
            ]);
            return false;
        }

        Logger::info('Kontakt gespeichert', ['profile_id' => $profile_id]);
        return true;
    }

    public static function update(string $profile_id, array $data): bool {
        global $wpdb;

        $update = [
            'firstname' => $data['firstname'] ?? '',
            'lastname'  => $data['lastname'] ?? '',
            'email'     => $data['email'] ?? '',
            'phone'     => $data['phone'] ?? '',
            'address'   => $data['address'] ?? '',
        ];

        $result = $wpdb->update(
            $wpdb->prefix . 'worker_contacts',
            $update,
            ['profile_id' => $profile_id],
            ['%s', '%s', '%s', '%s', '%s'],
            ['%s']
        );

        if ($result === false) {
            Logger::error('Fehler beim Aktualisieren des Kontakts', [
                'error' => $wpdb->last_error,
                'profile_id' => $profile_id,
                'data' => $update
            ]);
            return false;
        }

        Logger::info('Kontakt aktualisiert', ['profile_id' => $profile_id]);
        return true;
    }
}
