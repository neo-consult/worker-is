<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;
use WorkerIS\DB_Manager;
use WorkerIS\Form_Helper;

class Worker_Profile_Edit {

    /**
     * Rendert die Seite zum Bearbeiten eines Worker-Profils.
     *
     * Erwartet wird ein GET-Parameter "profile_id". Das Profil wird aus der Tabelle worker_profiles
     * geladen, die dynamischen Felddaten (anonymous und detailed) werden aus dem JSON-Feld profile_data extrahiert,
     * und die zugehörigen Kontaktdaten aus der Tabelle worker_contacts.
     * Beim Absenden werden beide Bereiche aktualisiert.
     */
    public function render() {
        global $wpdb;

        // 1. Überprüfen, ob eine profile_id übergeben wurde.
        if (!isset($_GET['profile_id']) || empty($_GET['profile_id'])) {
            echo '<div class="wrap"><h1>' . __('No profile selected.', 'worker-is') . '</h1></div>';
            Logger::log('Worker profile edit requested without profile_id.');
            return;
        }
        $profile_id = sanitize_text_field($_GET['profile_id']);

        // 2. Profil aus der Tabelle worker_profiles laden.
        $table_profiles = $wpdb->prefix . 'worker_profiles';
        $profile = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_profiles WHERE id = %s", $profile_id));
        if (!$profile) {
            echo '<div class="wrap"><h1>' . __('Profile not found.', 'worker-is') . '</h1></div>';
            Logger::log('Worker profile not found for editing.', array('profile_id' => $profile_id));
            return;
        }

        // 3. Deserialisiere die gespeicherten Profil-Daten (JSON).
        $profile_data = maybe_unserialize($profile->profile_data);
        if (!is_array($profile_data) || !isset($profile_data['anonymous']) || !isset($profile_data['detailed'])) {
            $profile_data = array(
                'anonymous' => array(),
                'detailed'  => array()
            );
        }
        $anonymous_values = $profile_data['anonymous'];
        $detailed_values  = $profile_data['detailed'];

        // 4. Kontaktdaten laden.
        $table_contacts = $wpdb->prefix . 'worker_contacts';
        $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_contacts WHERE profile_id = %s", $profile_id));
        $contact_name    = $contact ? $contact->name : '';
        $contact_email   = $contact ? $contact->email : '';
        $contact_telefon = $contact ? $contact->telefon : '';
        $contact_adresse = $contact ? $contact->adresse : '';

        // 5. Laden der JSON-Konfiguration.
        $form_config = get_option('worker_is_form_config', '{"version": "1.0", "anonymous": "[]", "detailed": "[]"}');
        $form_config = json_decode($form_config, true);
        $anonymous_config = isset($form_config['anonymous']) ? json_decode($form_config['anonymous'], true) : array();
        $detailed_config  = isset($form_config['detailed']) ? json_decode($form_config['detailed'], true) : array();

        // 6. Formularverarbeitung: Beim Absenden werden die neuen dynamischen Werte aus $_POST['dynamic'] gelesen.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('worker_is_edit_profile', 'worker_is_edit_nonce')) {
            // Kontaktdaten
            $contact_name    = sanitize_text_field($_POST['name']);
            $contact_email   = sanitize_email($_POST['email']);
            $contact_telefon = sanitize_text_field($_POST['telefon']);
            $contact_adresse = sanitize_textarea_field($_POST['adresse']);

            // Dynamische Felder aus $_POST['dynamic']
            $dynamic = isset($_POST['dynamic']) ? $_POST['dynamic'] : array('anonymous' => array(), 'detailed' => array());
            $new_profile_data = array(
                'anonymous' => isset($dynamic['anonymous']) ? $dynamic['anonymous'] : array(),
                'detailed'  => isset($dynamic['detailed']) ? $dynamic['detailed'] : array()
            );

            $db_manager = new DB_Manager();
            $update_profile = $db_manager->update_profile($profile_id, $new_profile_data);

            // Aktualisiere die Kontaktdaten in der worker_contacts-Tabelle.
            $updated_contact = $wpdb->update(
                $table_contacts,
                array(
                    'name'    => $contact_name,
                    'email'   => $contact_email,
                    'telefon' => $contact_telefon,
                    'adresse' => $contact_adresse
                ),
                array('profile_id' => $profile_id),
                array('%s','%s','%s','%s'),
                array('%s')
            );

            if ($update_profile !== false && $updated_contact !== false) {
                echo '<div class="updated"><p>' . __('Profile updated successfully.', 'worker-is') . '</p></div>';
                Logger::log('Worker profile and contact details updated.', array('profile_id' => $profile_id));
                $anonymous_values = $new_profile_data['anonymous'];
                $detailed_values  = $new_profile_data['detailed'];
            } else {
                echo '<div class="error"><p>' . __('Error updating profile.', 'worker-is') . '</p></div>';
                Logger::log('Error updating worker profile.', array('profile_id' => $profile_id));
            }
        }

        // 7. Anzeige des Formulars.
        ?>
        <div class="wrap">
            <h1><?php _e('Edit Worker Profile', 'worker-is'); ?></h1>
            <p><?php _e('Bearbeiten Sie die Kontaktdaten und Profilinformationen des Arbeitssuchenden.', 'worker-is'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('worker_is_edit_profile', 'worker_is_edit_nonce'); ?>
                <h2><?php _e('Kontaktdaten', 'worker-is'); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="name"><?php _e('Name', 'worker-is'); ?></label></th>
                        <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($contact_name); ?>" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="email"><?php _e('Email', 'worker-is'); ?></label></th>
                        <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($contact_email); ?>" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="telefon"><?php _e('Telefon', 'worker-is'); ?></label></th>
                        <td><input type="text" name="telefon" id="telefon" class="regular-text" value="<?php echo esc_attr($contact_telefon); ?>"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="adresse"><?php _e('Adresse', 'worker-is'); ?></label></th>
                        <td><textarea name="adresse" id="adresse" rows="4" class="large-text"><?php echo esc_textarea($contact_adresse); ?></textarea></td>
                    </tr>
                </table>
                
                <h2><?php _e('Anonymes Profil', 'worker-is'); ?></h2>
                <p><?php _e('Diese Daten werden an die öffentliche Plattform übertragen.', 'worker-is'); ?></p>
                <?php 
                    echo Form_Helper::render_dynamic_form($anonymous_config, $anonymous_values, 'anonymous');
                ?>
                
                <h2><?php _e('Detailliertes Profil', 'worker-is'); ?></h2>
                <p><?php _e('Diese Daten sind nur intern sichtbar.', 'worker-is'); ?></p>
                <?php 
                    echo Form_Helper::render_dynamic_form($detailed_config, $detailed_values, 'detailed');
                ?>
                
                <?php submit_button(__('Update Profile', 'worker-is')); ?>
            </form>
        </div>
        <?php
    }
}
