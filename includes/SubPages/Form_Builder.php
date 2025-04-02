<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Form_Builder {

    /**
     * Rendert den interaktiven Form Builder.
     */
    public function render() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('worker_is_save_form_config', 'worker_is_nonce')) {
            $new_config = array(
                'version'   => sanitize_text_field($_POST['form_version']),
                'anonymous' => stripslashes($_POST['anonymous_fields']),
                'detailed'  => stripslashes($_POST['detailed_fields']),
            );
            $success = update_option('worker_is_form_config', json_encode($new_config));
            if ($success) {
                echo '<div class="updated"><p>' . __('Form configuration saved.', 'worker-is') . '</p></div>';
                Logger::log('Form configuration saved.', $new_config);
            } else {
                echo '<div class="error"><p>' . __('Failed to save configuration.', 'worker-is') . '</p></div>';
                Logger::log('Form configuration NOT saved.', $new_config);
            }
        }

        $config_json = get_option('worker_is_form_config', json_encode([
            'version' => '1.0',
            'anonymous' => [],
            'detailed' => []
        ]));
        $config = json_decode($config_json, true);
        ?>
        <div class="wrap">
            <h1><?php _e('Form Builder', 'worker-is'); ?></h1>
            <p><?php _e('Erstellen und konfigurieren Sie interaktiv die Formulare zur Datenerhebung für die Vertreter.', 'worker-is'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('worker_is_save_form_config', 'worker_is_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Form Version', 'worker-is'); ?></th>
                        <td>
                            <input type="text" name="form_version" value="<?php echo esc_attr($config['version']); ?>" size="10" />
                            <p class="description"><?php _e('Geben Sie die Versionsnummer des Formulars ein.', 'worker-is'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th><?php _e('Anonymous Fields', 'worker-is'); ?></th>
                        <td>
                            <div id="anonymous-fields" class="form-builder-fields"></div>
                            <button type="button" id="add-anonymous-field" class="button"><?php _e('Add Anonymous Field', 'worker-is'); ?></button>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th><?php _e('Detailed Fields', 'worker-is'); ?></th>
                        <td>
                            <div id="detailed-fields" class="form-builder-fields"></div>
                            <button type="button" id="add-detailed-field" class="button"><?php _e('Add Detailed Field', 'worker-is'); ?></button>
                        </td>
                    </tr>
                </table>
                <input type="hidden" id="anonymous_fields" name="anonymous_fields" value="<?php echo esc_attr(json_encode($config['anonymous'])); ?>">
                <input type="hidden" id="detailed_fields" name="detailed_fields" value="<?php echo esc_attr(json_encode($config['detailed'])); ?>">
                <?php submit_button(__('Save Form Configuration', 'worker-is')); ?>
            </form>
            <h2><?php _e('Form Preview (JSON)', 'worker-is'); ?></h2>
            <pre><code id="form-preview" class="json"></code></pre>
            <h2><?php _e('Export/Import Configuration', 'worker-is'); ?></h2>
            <button type="button" id="export-config" class="button">Export Configuration</button>
            <label style="margin-left: 1rem;">
              <?php _e('Import JSON:', 'worker-is'); ?>
              <input type="file" id="import-config" accept=".json">
            </label>
        </div>

        <!-- Bootstrap Modal -->
        <div class="modal fade" id="fieldModal" tabindex="-1" role="dialog" aria-labelledby="fieldModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="fieldModalLabel"><?php _e('Configure Form Field', 'worker-is'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php _e('Close', 'worker-is'); ?>">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="fieldModalSection" value="">
                <div class="form-group">
                    <label for="fieldType"><?php _e('Field Type', 'worker-is'); ?></label>
                    <select id="fieldType" class="form-control">
                        <option value="text"><?php _e('Text', 'worker-is'); ?></option>
                        <option value="radio"><?php _e('Single Choice (Radio)', 'worker-is'); ?></option>
                        <option value="checkbox"><?php _e('Multiple Choice (Checkbox)', 'worker-is'); ?></option>
                        <option value="dropdown"><?php _e('Dropdown', 'worker-is'); ?></option>
                        <option value="header"><?php _e('Header', 'worker-is'); ?></option>
                        <option value="description"><?php _e('Description', 'worker-is'); ?></option>
                        <option value="separator"><?php _e('Separator', 'worker-is'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fieldLabel"><?php _e('Label', 'worker-is'); ?></label>
                    <input type="text" id="fieldLabel" class="form-control">
                </div>
                <div class="form-group" id="group-maxlength">
                    <label for="fieldMaxLength"><?php _e('Max Length (for text fields)', 'worker-is'); ?></label>
                    <input type="number" id="fieldMaxLength" class="form-control">
                </div>
                <div class="form-group" id="group-options">
                    <label for="fieldOptions"><?php _e('Options (comma-separated, for radio/checkbox/dropdown)', 'worker-is'); ?></label>
                    <input type="text" id="fieldOptions" class="form-control">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" id="saveFieldBtn" class="btn btn-primary"><?php _e('Save Field', 'worker-is'); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Cancel', 'worker-is'); ?></button>
              </div>
            </div>
          </div>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js"></script>
        <script>hljs.highlightAll();</script>
        <script type="text/javascript">
            var workerIsFormConfig = {
                version: <?php echo json_encode($config['version']); ?>,
                anonymous: <?php echo $config['anonymous'] ?: '[]'; ?>,
                detailed: <?php echo $config['detailed'] ?: '[]'; ?>
            };

            document.addEventListener('DOMContentLoaded', function () {
                const preview = document.getElementById('form-preview');
                if (!preview || typeof workerIsFormConfig === 'undefined') return;

                const formatted = JSON.stringify(workerIsFormConfig, null, 2);
                preview.textContent = formatted;
                if (typeof hljs !== 'undefined') {
                    hljs.highlightElement(preview);
                }

                document.getElementById('export-config').addEventListener('click', function () {
                    const blob = new Blob([formatted], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'form_config.json';
                    a.click();
                    URL.revokeObjectURL(url);
                });

                document.getElementById('import-config').addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        try {
                            const imported = JSON.parse(e.target.result);
                            if (!imported.anonymous || !imported.detailed || !imported.version) {
                                alert('Ungültige JSON-Struktur.');
                                return;
                            }
                            document.querySelector('[name=form_version]').value = imported.version;
                            document.querySelector('[name=anonymous_fields]').value = JSON.stringify(imported.anonymous);
                            document.querySelector('[name=detailed_fields]').value = JSON.stringify(imported.detailed);

                            // Vorschau aktualisieren
                            workerIsFormConfig = imported;
                            const formattedNew = JSON.stringify(imported, null, 2);
                            preview.textContent = formattedNew;
                            if (typeof hljs !== 'undefined') {
                                hljs.highlightElement(preview);
                            }

                            alert('Import erfolgreich! Änderungen sind noch nicht gespeichert.');
                        } catch (err) {
                            alert('Fehler beim Import: ' + err.message);
                        }
                    };
                    reader.readAsText(file);
                });
            });
        </script>
        <?php
        Logger::log('Interactive Form Builder page rendered.');
    }
}
