<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Worker_Profiles {

    /**
     * Rendert die Seite für Worker-Profile.
     *
     * Diese Seite zeigt alle erfassten Worker-Profile in einer Tabelle an.
     * Die Daten werden aus der entsprechenden Datenbanktabelle abgerufen.
     */
    public function render() {
        global $wpdb;
        // Name der Tabelle für Worker-Profile
        $table_name = $wpdb->prefix . 'worker_profiles';
        // Abfrage aller Profile, sortiert nach Erstellungsdatum absteigend
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        ?>
        <div class="wrap">
            <h1><?php _e('Worker Profiles', 'worker-is'); ?></h1>
            <?php if(empty($results)): ?>
                <p><?php _e('No worker profiles found.', 'worker-is'); ?></p>
            <?php else: ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php _e('Profile ID', 'worker-is'); ?></th>
                            <th><?php _e('Worker ID', 'worker-is'); ?></th>
                            <th><?php _e('Profile Data', 'worker-is'); ?></th>
                            <th><?php _e('Status', 'worker-is'); ?></th>
                            <th><?php _e('Created At', 'worker-is'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $profile): ?>
                        <tr>
                            <td><?php echo esc_html($profile->id); ?></td>
                            <td><?php echo esc_html($profile->worker_id); ?></td>
                            <td><?php echo esc_html($profile->profile_data); ?></td>
                            <td><?php echo esc_html($profile->status_id); ?></td>
                            <td><?php echo esc_html($profile->created_at); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        Logger::log('Worker Profiles page rendered.', array('count' => count($results)));
    }
}
