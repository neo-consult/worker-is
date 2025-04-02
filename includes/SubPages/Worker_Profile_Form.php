<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;
use WorkerIS\DB_Manager;
use WorkerIS\Form_Helper;

class Worker_Profile_Form {

    /**
     * Zeigt das Formular zur Erstellung oder Bearbeitung eines Profils an
     * und verarbeitet die Formulareingaben.
     */
    public static function render($mode = 'create', $profile_id = null) {
        global $wpdb;

        // Lade Konfiguration für dynamische Formularfelder
        $form_config = get_option('worker_is_form_config', '{"version": "1.0", "anonymous": "[]", "detailed": "[]"}');
        $form_config = json_decode($form_config, true);
        $anonymous_config = isset($form_config['anonymous']) ? json_decode($form_config['anonymous'], true) : [];
        $detailed_config  = isset($form_config['detailed'])  ? json_decode($form_config['detailed'], true)  : [];

        // WP-Nutzer für Auswahlfeld holen
        $all_users = get_users(['fields' => ['ID', 'display_name']]);

        // Default-Werte
        $values = [
            'name' => '', 'email' => '', 'telefon' => '', 'adresse' => '',
            'assigned_user_id' => '', 'dynamic' => ['anonymous' => [], 'detailed' => []]
        ];

        // Bestehende Daten für Bearbeitung laden
        if ($mode === 'edit' && $profile_id) {
            $profile_table = $wpdb->prefix . 'worker_profiles';
            $contact_table = $wpdb->prefix . 'worker_contacts';

            $profile = $wpdb->get_row($wpdb->prepare("SELECT * FROM $profile_table WHERE id = %s", $profile_id));
            $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM $contact_table WHERE profile_id = %s", $profile_id));

            if ($profile && $contact) {
                Logger::log("Profil-ID $profile_id geladen (Edit-Modus).");
                $data = maybe_unserialize($profile->profile_data);
                $values['name']    = $contact->name;
                $values['email']   = $contact->email;
                $values['telefon'] = $contact->telefon;
                $values['adresse'] = $contact->adresse;
                $values['assigned_user_id'] = $profile->assigned_user_id;
                $values['dynamic'] = $data;
            }
        }

        // Verarbeiten bei POST-Request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('worker_is_profile_form', 'worker_is_profile_nonce')) {
            $errors = [];

            $name    = sanitize_text_field($_POST['name']);
            $email   = sanitize_email($_POST['email']);
            $telefon = sanitize_text_field($_POST['telefon']);
            $adresse = sanitize_textarea_field($_POST['adresse']);
            $assigned_user_id = intval($_POST['assigned_user_id'] ?? 0);

            if (empty($name)) { $errors[] = 'Name ist erforderlich.'; }
            if (!is_email($email)) { $errors[] = 'Ungültige E-Mail-Adresse.'; }
            if ($assigned_user_id <= 0) { $errors[] = 'Ein zugewiesener Benutzer muss ausgewählt werden.'; }

            $dynamic = $_POST['dynamic'] ?? ['anonymous' => [], 'detailed' => []];
            $profile_data = ['anonymous' => $dynamic['anonymous'] ?? [], 'detailed' => $dynamic['detailed'] ?? []];

            do_action('worker_is_before_profile_save', $profile_data, $mode, $profile_id);

            if (empty($errors)) {
                $db_manager = new DB_Manager();
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;

                if ($mode === 'create') {
                    $worker_id = class_exists('\\WorkerIS\\API_Handler') ? \WorkerIS\API_Handler::generate_worker_id() : uniqid();
                    $profile_id = $db_manager->create_profile($profile_data, $worker_id);
                    Logger::log("Profil erstellt: $profile_id", $profile_data);
                    $db_manager->insert_contact($profile_id, $worker_id, $name, $email, $telefon, $adresse);
                } else {
                    $worker_id = $wpdb->get_var($wpdb->prepare("SELECT worker_id FROM {$wpdb->prefix}worker_profiles WHERE id = %s", $profile_id));
                    $db_manager->update_profile($profile_id, $profile_data);
                    $db_manager->update_contact($profile_id, $name, $email, $telefon, $adresse);
                    Logger::log("Profil aktualisiert: $profile_id", $profile_data);
                }

                if ($profile_id) {
                    $wpdb->update($wpdb->prefix . 'worker_profiles', [
                        'owner_id' => $user_id,
                        'assigned_user_id' => $assigned_user_id
                    ], ['id' => $profile_id]);

                    do_action('worker_is_after_profile_save', $profile_id, $mode);
                    echo '<div class="alert alert-success">Profil wurde gespeichert.</div>';
                } else {
                    Logger::log("Fehler beim Speichern des Profils ($mode).", compact('profile_id'));
                    echo '<div class="alert alert-danger">Fehler beim Speichern des Profils.</div>';
                }
            } else {
                foreach ($errors as $e) {
                    Logger::log("Validierungsfehler: $e");
                    echo '<div class="alert alert-danger">' . esc_html($e) . '</div>';
                }
            }
        }

        // Formularanzeige (Bootstrap)
        echo '<div class="container mt-4">';
        echo '<h2>' . ($mode === 'edit' ? 'Profil bearbeiten' : 'Neues Profil erstellen') . '</h2>';
        echo '<form method="post" novalidate>';
        wp_nonce_field('worker_is_profile_form', 'worker_is_profile_nonce');

        echo '<div class="mb-3"><label class="form-label">Zugewiesener Benutzer</label><select name="assigned_user_id" class="form-select" required>';
        echo '<option value="">-- Bitte wählen --</option>';
        foreach ($all_users as $user) {
            printf('<option value="%d"%s>%s</option>', $user->ID, selected($values['assigned_user_id'], $user->ID, false), esc_html($user->display_name));
        }
        echo '</select></div>';

        echo '<div class="mb-3"><label class="form-label">Name</label><input name="name" value="' . esc_attr($values['name']) . '" class="form-control" required></div>';
        echo '<div class="mb-3"><label class="form-label">E-Mail</label><input name="email" type="email" value="' . esc_attr($values['email']) . '" class="form-control" required></div>';
        echo '<div class="mb-3"><label class="form-label">Telefon</label><input name="telefon" value="' . esc_attr($values['telefon']) . '" class="form-control"></div>';
        echo '<div class="mb-3"><label class="form-label">Adresse</label><textarea name="adresse" class="form-control">' . esc_textarea($values['adresse']) . '</textarea></div>';

        echo '<h4>Anonyme Felder</h4>';
        foreach ($anonymous_config as $f) {
            echo Form_Helper::render_dynamic_form([$f], $values['dynamic']['anonymous'], 'anonymous');
        }

        echo '<h4 class="mt-4">Detail-Felder</h4>';
        foreach ($detailed_config as $f) {
            echo Form_Helper::render_dynamic_form([$f], $values['dynamic']['detailed'], 'detailed');
        }

        echo '<button type="submit" class="btn btn-primary mt-3">' . ($mode === 'edit' ? 'Änderungen speichern' : 'Profil speichern') . '</button>';
        echo '</form></div>';
    }
}
