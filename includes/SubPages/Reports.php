<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Reports {

    /**
     * Rendert die Reports-Seite.
     */
    public function render() {
        global $wpdb;
        $worker_table   = $wpdb->prefix . 'worker_profiles';
        $employer_table = $wpdb->prefix . 'employer_requests';

        $profile_count = $wpdb->get_var("SELECT COUNT(*) FROM $worker_table");
        $employer_count = $wpdb->get_var("SELECT COUNT(*) FROM $employer_table");
        $new_requests = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $employer_table WHERE status_id = %d", 1));
        $in_progress_requests = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $employer_table WHERE status_id = %d", 2));

        $report_data = array(
            'profile_count' => intval($profile_count),
            'employer_count' => intval($employer_count),
            'new_requests' => intval($new_requests),
            'in_progress_requests' => intval($in_progress_requests)
        );
        ?>
        <div class="wrap">
            <h1><?php _e('Reports', 'worker-is'); ?></h1>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php _e('Metric', 'worker-is'); ?></th>
                        <th><?php _e('Value', 'worker-is'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Total Worker Profiles', 'worker-is'); ?></td>
                        <td><?php echo intval($profile_count); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Total Employer Requests', 'worker-is'); ?></td>
                        <td><?php echo intval($employer_count); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('New Employer Requests', 'worker-is'); ?></td>
                        <td><?php echo intval($new_requests); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('In-Progress Employer Requests', 'worker-is'); ?></td>
                        <td><?php echo intval($in_progress_requests); ?></td>
                    </tr>
                </tbody>
            </table>
            <h2><?php _e('Diagrams', 'worker-is'); ?></h2>
            <canvas id="workerProfilesChart" width="400" height="200"></canvas>
            <canvas id="employerRequestsChart" width="400" height="200"></canvas>
        </div>
        <script type="text/javascript">
            var workerIsReportData = <?php echo json_encode($report_data); ?>;
        </script>
        <?php
        Logger::log('Reports page rendered.', $report_data);
    }
}
