<h2>Alle Profile</h2>

<table class="widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>E-Mail</th>
            <th>Erstellt</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($profiles)): ?>
            <tr><td colspan="4">Keine Profile gefunden.</td></tr>
        <?php else: ?>
            <?php foreach ($profiles as $p): ?>
                <tr>
                    <td><?= esc_html($p->name ?? '-') ?></td>
                    <td><?= esc_html($p->email ?? '-') ?></td>
                    <td><?= esc_html($p->created_at ?? '-') ?></td>
                    <td>
                        <a href="<?= admin_url('admin.php?page=worker-is&action=edit_profile&id=' . esc_attr($p->id)) ?>" class="button">Bearbeiten</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
