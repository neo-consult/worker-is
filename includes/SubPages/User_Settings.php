<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class User_Settings {

    /**
     * Rendert die Vermittler-Einstellungsseite.
     */
    public function render() {
        // Beispiel: Lade Einstellungen aus der wp_options-Tabelle
        $settings = get_option('worker_is_user_settings', '{}');
        $settings = json_decode($settings, true);
        ?>
        <div class="wrap">
            <h1><?php _e('User Settings', 'worker-is'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('worker_is_save_user_settings', 'worker_is_user_settings_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Notification Preference', 'worker-is'); ?></th>
                        <td>
                            <select name="notification_preference">
                                <option value="email" <?php selected(isset($settings['notification_preference']) ? $settings['notification_preference'] : '', 'email'); ?>><?php _e('Email', 'worker-is'); ?></option>
                                <option value="none" <?php selected(isset($settings['notification_preference']) ? $settings['notification_preference'] : '', 'none'); ?>><?php _e('None', 'worker-is'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Settings', 'worker-is')); ?>
            </form>
        </div>
        <?php
        Logger::log('User Settings page rendered.', $settings);
    }
}
