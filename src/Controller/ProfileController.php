<?php
namespace WorkerIS\Controller;

use WorkerIS\Model\Profile;
use WorkerIS\Model\Contact;
use WorkerIS\Core\View;
use WorkerIS\Core\Logger;

class ProfileController {

    public static function index(): void {
        $profiles = Profile::all();
        View::render('profile/list', ['profiles' => $profiles]);
    }

    public static function create(): void {
        Logger::info("Neues Profil-Formular aufgerufen");
        View::render('profile/form', ['mode' => 'create', 'profile' => null]);
    }

    public static function edit(): void {
        $id = sanitize_text_field($_GET['id'] ?? '');

        if (!$id) {
            wp_die('Profil-ID fehlt.');
        }

        $profile = Profile::find($id);
        if (!$profile) {
            Logger::warn("Profil nicht gefunden", ['id' => $id]);
            echo '<div class="notice notice-error">Profil nicht gefunden.</div>';
            return;
        }

        $contact = Contact::find_by_profile($id);
        if ($contact) {
            $profile->telefon = $contact->telefon ?? '';
            $profile->adresse = $contact->adresse ?? '';
        }

        $data = maybe_unserialize($profile->profile_data ?? '');
        $profile->dynamic = [
            'anonymous' => $data['anonymous'] ?? [],
            'detailed'  => $data['detailed'] ?? []
        ];

        Logger::info("Profil geladen (edit)", ['id' => $id]);
        View::render('profile/form', ['mode' => 'edit', 'profile' => $profile]);
    }

    public static function store(): void {
        $profile_data = [
            'name'              => sanitize_text_field($_POST['name']),
            'email'             => sanitize_email($_POST['email']),
            'assigned_user_id'  => intval($_POST['assigned_user_id'] ?? 0),
            'profile_data'      => [
                'anonymous' => $_POST['dynamic']['anonymous'] ?? [],
                'detailed'  => $_POST['dynamic']['detailed'] ?? [],
            ],
        ];
    
        $contact_data = [
            'name'    => sanitize_text_field($_POST['name']),
            'email'   => sanitize_email($_POST['email']),
            'telefon' => sanitize_text_field($_POST['telefon']),
            'adresse' => sanitize_textarea_field($_POST['adresse']),
        ];
    
        $id = $_POST['id'] ?? null;
    
        if ($id) {
            Profile::update($id, $profile_data);
            Contact::update($id, $contact_data);
            Logger::info('Profil aktualisiert', ['id' => $id]);
        } else {
            $id = Profile::create($profile_data);
    
            if (!$id) {
                Logger::error('Profil konnte nicht erstellt werden');
                wp_die('Fehler beim Erstellen des Profils');
            }
    
            Contact::insert($id, $contact_data);
            Logger::info('Neues Profil erstellt', ['id' => $id]);
        }
    
        echo '<div class="notice notice-success"><p>Profil gespeichert.</p></div>';
    }
    
}
