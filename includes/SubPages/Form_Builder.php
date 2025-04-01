<?php
namespace WorkerIS\SubPages;

use WorkerIS\Logger;

class Form_Builder {

    /**
     * Rendert den interaktiven Form Builder.
     *
     * Der Administrator kann hier die Formularfelder (für den "anonymous" und "detailed" Bereich)
     * interaktiv konfigurieren. Die Konfiguration (inklusive Versionsnummer) wird als JSON in der wp_options-Tabelle gespeichert.
     */
    public function render() {
        // Lade die aktuelle Konfiguration; Standardwerte, falls nicht vorhanden.
        $config_json = get_option('worker_is_form_config', '{"version": "1.0", "anonymous": "[]", "detailed": "[]"}');
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
                <!-- Hidden-Felder, die per JavaScript aktualisiert werden -->
                <input type="hidden" id="anonymous_fields" name="anonymous_fields" value="<?php echo esc_attr(json_encode($config['anonymous'])); ?>">
                <input type="hidden" id="detailed_fields" name="detailed_fields" value="<?php echo esc_attr(json_encode($config['detailed'])); ?>">
                <?php submit_button(__('Save Form Configuration', 'worker-is')); ?>
            </form>
            <h2><?php _e('Form Preview (JSON)', 'worker-is'); ?></h2>
            <pre id="form-preview"></pre>
        </div>
        <!-- Bootstrap Modal für die Konfiguration eines Formularfelds -->
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
        <script type="text/javascript">
            var workerIsFormConfig = {
                version: "<?php echo esc_js($config['version']); ?>",
                anonymous: JSON.parse(<?php echo json_encode($config['anonymous']); ?>),
                detailed: JSON.parse(<?php echo json_encode($config['detailed']); ?>)
            };
        </script>
        <?php
        Logger::log('Interactive Form Builder page rendered.');
    }
}
