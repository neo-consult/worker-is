<div class="wrap">
    <h1>Rollen & Rechte</h1>

    <?php if (!empty($_GET['updated'])): ?>
        <div class="notice notice-success"><p>Änderung gespeichert.</p></div>
    <?php endif; ?>

    <table class="widefat striped">
        <thead>
            <tr>
                <th style="width: 15%;">Rolle</th>
                <th style="width: 15%;">Slug</th>
                <th>Capabilities</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><strong><?= esc_html($role['name']) ?></strong></td>
                    <td><code><?= esc_html($role['slug']) ?></code></td>
                    <td>
                        <?php if (empty($role['capabilities'])): ?>
                            <em>Keine</em>
                        <?php else: ?>
                            <ul style="columns: 2;">
                                <?php foreach ($role['capabilities'] as $cap): ?>
                                    <li>
                                        <code><?= esc_html($cap) ?></code>

                                        <?php if (current_user_can('administrator')): ?>
                                            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" style="display:inline;">
                                                <?php wp_nonce_field('worker_is_role_update', 'worker_is_nonce'); ?>
                                                <input type="hidden" name="action" value="worker_is_update_role_capability">
                                                <input type="hidden" name="role" value="<?= esc_attr($role['slug']) ?>">
                                                <input type="hidden" name="capability" value="<?= esc_attr($cap) ?>">
                                                <input type="hidden" name="operation" value="remove">
                                                <button type="submit" class="button-link-delete" style="margin-left: 6px;">✕</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (current_user_can('worker_is_manage_roles')): ?>
                            <hr>
                            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" style="margin-top: 0.5rem;">
                                <?php wp_nonce_field('worker_is_role_update', 'worker_is_nonce'); ?>
                                <input type="hidden" name="action" value="worker_is_update_role_capability">
                                <input type="hidden" name="role" value="<?= esc_attr($role['slug']) ?>">
                                <input type="hidden" name="operation" value="add">
                                <input type="text" name="capability" placeholder="Neue Capability" class="regular-text">
                                <button type="submit" class="button">Hinzufügen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
