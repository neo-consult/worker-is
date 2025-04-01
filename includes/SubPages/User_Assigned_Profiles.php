<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class User_Assigned_Profiles {

    /**
     * Rendert die Seite "Assigned Profiles" für Vermittler.
     *
     * Diese Seite zeigt alle Worker-Profile an, denen der aktuell angemeldete Vermittler zugewiesen ist.
     * Jeder Profil-Datensatz enthält einen Link, über den das Profil zur Bearbeitung ausgewählt werden kann.
     */
    public function render() {
        global $wpdb;
        // Aktuellen Benutzer abrufen
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Tabelle für Worker-Profile (angenommen, assigned_user_id wird korrekt gesetzt)
        $table_name = $wpdb->prefix . 'worker_profiles';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE assigned_user_id = %d ORDER BY created_at DESC", 
            $user_id
        ));

        ?>
        <div class="wrap">
            <h1><?php _e('Assigned Profiles', 'worker-is'); ?></h1>
            <?php if (empty($results)): ?>
                <p><?php _e('Keine Profile zugewiesen.', 'worker-is'); ?></p>
            <?php else: ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php _e('Profile ID', 'worker-is'); ?></th>
                            <th><?php _e('Worker ID', 'worker-is'); ?></th>
                            <th><?php _e('Status', 'worker-is'); ?></th>
                            <th><?php _e('Erstellt am', 'worker-is'); ?></th>
                            <th><?php _e('Aktionen', 'worker-is'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $profile): ?>
                            <tr>
                                <td><?php echo esc_html($profile->id); ?></td>
                                <td><?php echo esc_html($profile->worker_id); ?></td>
                                <td><?php echo esc_html($profile->status_id); ?></td>
                                <td><?php echo esc_html($profile->created_at); ?></td>
                                <td>
                                    <?php 
                                    // Link zur Bearbeitung, der die profile_id übergibt
                                    $edit_url = admin_url('admin.php?page=worker-is-edit-profile&profile_id=' . urlencode($profile->id));
                                    echo '<a href="' . esc_url($edit_url) . '">' . __('Edit', 'worker-is') . '</a>'; 
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        Logger::log('User Assigned Profiles page rendered.', array('count' => count($results)));
    }
}
