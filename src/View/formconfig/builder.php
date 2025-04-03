<div class="wrap">
    <h1>Formular-Felder konfigurieren</h1>

    <form method="post">
        <?php wp_nonce_field('worker_is_form_config'); ?>

        <div id="form-builder-app">
            <h2>Anonyme Felder</h2>
            <div class="form-builder-group" data-group="anonymous"></div>
            <button type="button" class="button button-secondary add-field" data-group="anonymous">+ Feld hinzufügen</button>

            <hr>

            <h2>Detaillierte Felder</h2>
            <div class="form-builder-group" data-group="detailed"></div>
            <button type="button" class="button button-secondary add-field" data-group="detailed">+ Feld hinzufügen</button>
        </div>

        <input type="hidden" name="form_config_json" id="form_config_json">
        <p class="submit">
            <button type="submit" class="button button-primary">Konfiguration speichern</button>
        </p>
    </form>

    <!-- Modal für Feld hinzufügen/bearbeiten -->
    <div class="modal fade" id="fieldModal" tabindex="-1" aria-labelledby="fieldModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="fieldModalLabel">Feld bearbeiten</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="modal-index">
            <input type="hidden" id="modal-group">

            <div class="mb-3">
              <label for="modal-type" class="form-label">Typ</label>
              <select id="modal-type" class="form-select">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
                <option value="dropdown">Dropdown</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="modal-label" class="form-label">Label</label>
              <input type="text" id="modal-label" class="form-control">
            </div>

            <div class="mb-3" id="modal-options-group" style="display: none;">
              <label for="modal-options" class="form-label">Optionen (Komma getrennt)</label>
              <input type="text" id="modal-options" class="form-control">
            </div>

            <div class="form-check mt-3">
              <input type="checkbox" class="form-check-input" id="modal-required">
              <label class="form-check-label" for="modal-required">Pflichtfeld</label>
            </div>

            <div class="mb-3 mt-3">
              <label for="modal-placeholder" class="form-label">Platzhalter</label>
              <input type="text" id="modal-placeholder" class="form-control">
            </div>

            <div class="mb-3">
              <label for="modal-max-length" class="form-label">Max. Zeichen</label>
              <input type="number" id="modal-max-length" class="form-control" min="1">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            <button type="button" class="btn btn-primary" id="saveFieldBtn">Feld speichern</button>
            <button type="button" class="button button-secondary" data-bs-toggle="modal" data-bs-target="#jsonPreviewModal">JSON Vorschau</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="jsonPreviewModal" tabindex="-1" aria-labelledby="jsonPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jsonPreviewModalLabel">Formular-Konfiguration (JSON)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <pre id="json-preview" style="max-height: 500px; overflow: auto; background: #f5f5f5; padding: 10px;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
            </div>
            </div>
        </div>
    </div>

</div>
