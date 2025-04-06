<?php
use WorkerIS\Core\FormRenderer;

$profile = $profile ?? (object) [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'assigned_user_id' => '',
    'dynamic' => ['anonymous' => [], 'detailed' => []],
];

$action = admin_url('admin-post.php');
$users = get_users(['fields' => ['ID', 'display_name']]);

$config = get_option('worker_is_form_config', '{"anonymous":"[]","detailed":"[]"}');
$parsed = json_decode($config, true);
$anon_fields = json_decode($parsed['anonymous'] ?? '[]', true);
$detailed_fields = json_decode($parsed['detailed'] ?? '[]', true);
?>

<div class="wrap">
    <h2><?= $mode === 'edit' ? 'Profil bearbeiten' : 'Neues Profil erstellen' ?></h2>

    <form method="post" action="<?= esc_url($action) ?>">
        <input type="hidden" name="action" value="worker_is_store_profile">
        <?php wp_nonce_field('worker_is_profile_form', 'worker_is_profile_nonce'); ?>

        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= esc_attr($profile->id) ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="assigned_user_id">Zugewiesener Benutzer</label></th>
                <td>
                    <select name="assigned_user_id" id="assigned_user_id" class="regular-text">
                        <option value="">-- bitte wählen --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->ID ?>" <?= selected($profile->assigned_user_id ?? '', $user->ID, false) ?>>
                                <?= esc_html($user->display_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="firstname">Vorname</label></th>
                <td><input name="firstname" id="firstname" value="<?= esc_attr($profile->firstname) ?>" class="regular-text" required></td>
            </tr>

            <tr>
                <th><label for="lastname">Nachname</label></th>
                <td><input name="lastname" id="lastname" value="<?= esc_attr($profile->lastname) ?>" class="regular-text" required></td>
            </tr>

            <tr>
                <th><label for="email">E-Mail</label></th>
                <td><input name="email" id="email" type="email" value="<?= esc_attr($profile->email) ?>" class="regular-text" required></td>
            </tr>

            <tr>
                <th><label for="phone">Telefon</label></th>
                <td><input name="phone" id="phone" value="<?= esc_attr($profile->phone) ?>" class="regular-text"></td>
            </tr>

            <tr>
                <th><label for="address">Adresse</label></th>
                <td><textarea name="address" id="address" rows="3" class="large-text"><?= esc_textarea($profile->address) ?></textarea></td>
            </tr>
        </table>

        <hr>
        <h3>Anonyme Felder</h3>
        <?php FormRenderer::render_configured_fields($anon_fields, $profile->dynamic['anonymous'], 'anonymous'); ?>

        <h3>Detail-Felder</h3>
        <?php FormRenderer::render_configured_fields($detailed_fields, $profile->dynamic['detailed'], 'detailed'); ?>

        <?php submit_button($mode === 'edit' ? 'Änderungen speichern' : 'Profil speichern'); ?>
    </form>
</div>