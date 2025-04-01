<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;
use WorkerIS\DB_Manager;
use WorkerIS\Form_Helper;

class Worker_Profile_Create {

    /**
     * Rendert die Seite zum Erstellen eines neuen Worker-Profils.
     *
     * Vermittler erfassen hier die Kontaktdaten sowie die dynamisch generierten Profilinformationen
     * (anonymous und detailed) basierend auf der vom Admin festgelegten JSON-Konfiguration.
     */
    public function render() {
        global $wpdb;
        
        // Lade die JSON-Konfiguration, die der Admin festgelegt hat
        $form_config = get_option('worker_is_form_config', '{"version": "1.0", "anonymous": "[]", "detailed": "[]"}');
        $form_config = json_decode($form_config, true);
        // Jetzt sind $form_config['anonymous'] und ['detailed'] noch JSON-Strings – wir decodieren sie
        $anonymous_config = isset($form_config['anonymous']) ? json_decode($form_config['anonymous'], true) : array();
        $detailed_config  = isset($form_config['detailed'])  ? json_decode($form_config['detailed'], true)  : array();

        // Formularverarbeitung: Falls das Formular abgeschickt wird.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('worker_is_create_profile', 'worker_is_profile_nonce')) {

            // 1. Kontaktdaten erfassen und säubern.
            $name    = sanitize_text_field($_POST['name']);
            $email   = sanitize_email($_POST['email']);
            $telefon = sanitize_text_field($_POST['telefon']);
            $adresse = sanitize_textarea_field($_POST['adresse']);

            // 2. Dynamische Felder erfassen: Erwartet werden die Werte unter "dynamic".
            $dynamic = isset($_POST['dynamic']) ? $_POST['dynamic'] : array('anonymous' => array(), 'detailed' => array());
            
            // 3. Profil-Daten zusammenstellen – die dynamischen Werte werden als Arrays gespeichert.
            $profile_data = array(
                'anonymous' => isset($dynamic['anonymous']) ? $dynamic['anonymous'] : array(),
                'detailed'  => isset($dynamic['detailed']) ? $dynamic['detailed'] : array()
            );

            // 4. Generiere eine eindeutige Worker-ID (über API_Handler oder uniqid als Fallback).
            if (class_exists('\WorkerIS\API_Handler')) {
                $worker_id = \WorkerIS\API_Handler::generate_worker_id();
            } else {
                $worker_id = uniqid();
            }

            // 5. Erstelle das Profil in der Tabelle worker_profiles.
            $db_manager = new DB_Manager();
            $profile_id = $db_manager->create_profile($profile_data, $worker_id);

            if ($profile_id) {
                // 6. Weise dem Profil den aktuell angemeldeten Vermittler als Besitzer zu.
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $profile_table = $wpdb->prefix . 'worker_profiles';
                $wpdb->update(
                    $profile_table,
                    array('assigned_user_id' => $user_id),
                    array('id' => $profile_id),
                    array('%d'),
                    array('%s')
                );

                // 7. Speichere die Kontaktdaten in der Tabelle worker_contacts.
                $contact_table = $wpdb->prefix . 'worker_contacts';
                $contact_id = uniqid();
                $data = array(
                    'id'         => $contact_id,
                    'profile_id' => $profile_id,
                    'name'       => $name,
                    'email'      => $email,
                    'telefon'    => $telefon,
                    'adresse'    => $adresse,
                    'created_at' => current_time('mysql', 1)
                );
                $result = $wpdb->insert($contact_table, $data, array('%s','%s','%s','%s','%s','%s'));
                if ($result !== false) {
                    echo '<div class="updated"><p>' . __('Worker profile created successfully.', 'worker-is') . '</p></div>';
                    Logger::log('Worker profile created.', array('profile_id' => $profile_id, 'contact_id' => $contact_id));
                } else {
                    echo '<div class="error"><p>' . __('Error creating contact details.', 'worker-is') . '</p></div>';
                    Logger::log('Error creating contact details.');
                }
            } else {
                echo '<div class="error"><p>' . __('Error creating worker profile.', 'worker-is') . '</p></div>';
                Logger::log('Error creating worker profile.');
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Create Worker Profile', 'worker-is'); ?></h1>
            <p><?php _e('Erfassen Sie die Kontaktdaten und Profilinformationen des Arbeitssuchenden.', 'worker-is'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('worker_is_create_profile', 'worker_is_profile_nonce'); ?>
                <h2><?php _e('Kontaktdaten', 'worker-is'); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="name"><?php _e('Name', 'worker-is'); ?></label></th>
                        <td><input type="text" name="name" id="name" class="regular-text" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="email"><?php _e('Email', 'worker-is'); ?></label></th>
                        <td><input type="email" name="email" id="email" class="regular-text" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="telefon"><?php _e('Telefon', 'worker-is'); ?></label></th>
                        <td><input type="text" name="telefon" id="telefon" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="adresse"><?php _e('Adresse', 'worker-is'); ?></label></th>
                        <td><textarea name="adresse" id="adresse" rows="4" class="large-text"></textarea></td>
                    </tr>
                </table>
                <h2><?php _e('Anonymes Profil', 'worker-is'); ?></h2>
                <p><?php _e('Diese Daten werden an die öffentliche Plattform übertragen.', 'worker-is'); ?></p>
                <?php 
                    // Dynamisches Formular für den anonymous-Bereich anhand der Konfiguration generieren
                    echo Form_Helper::render_dynamic_form($anonymous_config, array(), 'anonymous');
                ?>
                <h2><?php _e('Detailliertes Profil', 'worker-is'); ?></h2>
                <p><?php _e('Diese Angaben sind nur intern sichtbar.', 'worker-is'); ?></p>
                <?php 
                    // Dynamisches Formular für den detailed-Bereich anhand der Konfiguration generieren
                    echo Form_Helper::render_dynamic_form($detailed_config, array(), 'detailed');
                ?>
                <?php submit_button(__('Create Profile', 'worker-is')); ?>
            </form>
        </div>
        <?php
    }
}
