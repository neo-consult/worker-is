<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Settings {

    /**
     * Rendert die Settings-Seite.
     */
    public function render() {
        // Lade die gespeicherten Einstellungen, Standard: leeres JSON
        $settings = get_option('worker_is_settings', '{}');
        $settings = json_decode($settings, true);
        ?>
        <div class="wrap">
            <h1><?php _e('Worker IS Settings', 'worker-is'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('worker_is_save_settings', 'worker_is_settings_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Setting 1', 'worker-is'); ?></th>
                        <td>
                            <input type="text" name="setting_1" value="<?php echo isset($settings['setting_1']) ? esc_attr($settings['setting_1']) : ''; ?>" />
                            <p class="description"><?php _e('Beschreibung für Setting 1', 'worker-is'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Setting 2', 'worker-is'); ?></th>
                        <td>
                            <input type="checkbox" name="setting_2" value="1" <?php checked(isset($settings['setting_2']) && $settings['setting_2'] == 1); ?> />
                            <p class="description"><?php _e('Aktivieren Sie Setting 2', 'worker-is'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Settings', 'worker-is')); ?>
            </form>
        </div>
        <?php
        Logger::log('Settings page rendered.', $settings);
    }
}
