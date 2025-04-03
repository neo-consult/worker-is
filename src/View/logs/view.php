<div class="wrap">
    <h1>Worker-IS Log</h1>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Zeit</th>
                <th>Level</th>
                <th>Nachricht</th>
                <th>Daten</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= esc_html($log['timestamp']) ?></td>
                <td><?= esc_html($log['level']) ?></td>
                <td><?= esc_html($log['message']) ?></td>
                <td><pre><?php print_r($log['data']) ?></pre></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
