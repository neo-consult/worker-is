<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class User_Requests {

    /**
     * Rendert die Seite "My Requests" für Vermittler.
     */
    public function render() {
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        // Beispiel: Arbeitgeber-Anfragen, die diesem Vermittler zugewiesen sind.
        $table_name = $wpdb->prefix . 'employer_requests';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE assigned_user_id = %d ORDER BY created_at DESC", $user_id
        ));

        ?>
        <div class="wrap">
            <h1><?php _e('My Requests', 'worker-is'); ?></h1>
            <?php if(empty($results)): ?>
                <p><?php _e('Keine Anfragen gefunden.', 'worker-is'); ?></p>
            <?php else: ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php _e('Request ID', 'worker-is'); ?></th>
                            <th><?php _e('Employer ID', 'worker-is'); ?></th>
                            <th><?php _e('Profile ID', 'worker-is'); ?></th>
                            <th><?php _e('Status', 'worker-is'); ?></th>
                            <th><?php _e('Erstellt am', 'worker-is'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $request): ?>
                        <tr>
                            <td><?php echo esc_html($request->id); ?></td>
                            <td><?php echo esc_html($request->employer_id); ?></td>
                            <td><?php echo esc_html($request->profile_id); ?></td>
                            <td><?php echo esc_html($request->status_id); ?></td>
                            <td><?php echo esc_html($request->created_at); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        Logger::log('User Requests page rendered.', array('count' => count($results)));
    }
}
