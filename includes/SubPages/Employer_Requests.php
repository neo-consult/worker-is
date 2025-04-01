<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Employer_Requests {

    /**
     * Rendert die Seite für Arbeitgeber-Anfragen.
     *
     * Diese Seite zeigt alle eingegangenen Arbeitgeber-Anfragen in einer Tabelle an.
     * Es wird ein einfacher Query über die wpdb-Instanz ausgeführt, um die Anfragen abzurufen.
     * Erweiterungen wie Filter oder Paginierung können bei Bedarf ergänzt werden.
     */
    public function render() {
        global $wpdb;
        // Name der Tabelle für Arbeitgeber-Anfragen
        $table_name = $wpdb->prefix . 'employer_requests';
        // Abfrage aller Anfragen, sortiert nach Erstellungsdatum absteigend
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        ?>
        <div class="wrap">
            <h1><?php _e('Employer Requests', 'worker-is'); ?></h1>
            <?php if(empty($results)): ?>
                <p><?php _e('No employer requests found.', 'worker-is'); ?></p>
            <?php else: ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php _e('Request ID', 'worker-is'); ?></th>
                            <th><?php _e('Employer ID', 'worker-is'); ?></th>
                            <th><?php _e('Profile ID', 'worker-is'); ?></th>
                            <th><?php _e('Assigned User', 'worker-is'); ?></th>
                            <th><?php _e('Status ID', 'worker-is'); ?></th>
                            <th><?php _e('Created At', 'worker-is'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $request): ?>
                        <tr>
                            <td><?php echo esc_html($request->id); ?></td>
                            <td><?php echo esc_html($request->employer_id); ?></td>
                            <td><?php echo esc_html($request->profile_id); ?></td>
                            <td><?php echo esc_html($request->assigned_user_id); ?></td>
                            <td><?php echo esc_html($request->status_id); ?></td>
                            <td><?php echo esc_html($request->created_at); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        Logger::log('Employer Requests page rendered.', array('count' => count($results)));
    }
}
