jQuery(document).ready(function($) {
    /**
     * Aktualisiert die JSON-Vorschau und füllt die Hidden-Felder.
     */
    function updatePreview() {
        var anonymousFields = [];
        $('#anonymous-fields .form-field').each(function(){
            var field = $(this).data('field');
            if (field) {
                anonymousFields.push(field);
            }
        });
        var detailedFields = [];
        $('#detailed-fields .form-field').each(function(){
            var field = $(this).data('field');
            if (field) {
                detailedFields.push(field);
            }
        });
        var config = {
            version: $('input[name="form_version"]').val(),
            anonymous: anonymousFields,
            detailed: detailedFields
        };
        $('#form-preview').text(JSON.stringify(config, null, 4));
        $('#anonymous_fields').val(JSON.stringify(anonymousFields));
        $('#detailed_fields').val(JSON.stringify(detailedFields));
        console.log("Preview updated:", config);
    }
    
    // Initialisiere Sortable für Reordering
    $("#anonymous-fields, #detailed-fields").sortable({
        update: updatePreview
    });
    
    /**
     * Behandelt die Änderung des Field Type im Modal, um irrelevante Eingaben auszublenden.
     */
    $('#fieldType').change(function(){
        var type = $(this).val();
        if (type === 'text'){
            $('#group-maxlength').show();
            $('#group-options').hide();
        } else if (type === 'radio' || type === 'checkbox' || type === 'dropdown'){
            $('#group-maxlength').hide();
            $('#group-options').show();
        } else if (type === 'header' || type === 'description' || type === 'separator'){
            $('#group-maxlength').hide();
            $('#group-options').hide();
        }
    });
    
    /**
     * Öffnet das Bootstrap Modal zur Konfiguration eines Formularfelds.
     * @param {string} section - "anonymous" oder "detailed"
     * @param {object} preset (optional) - Vorbelegte Werte für das Feld
     */
    function openFieldModal(section, preset) {
        $("#fieldModalSection").val(section);
        if (preset) {
            $("#fieldType").val(preset.type).change();
            $("#fieldLabel").val(preset.label);
            if (preset.type === "text") {
                $("#fieldMaxLength").val(preset.max_length);
            } else {
                $("#fieldMaxLength").val("");
            }
            if (preset.type === "radio" || preset.type === "checkbox" || preset.type === "dropdown") {
                $("#fieldOptions").val(Array.isArray(preset.options) ? preset.options.join(", ") : "");
            } else {
                $("#fieldOptions").val("");
            }
        } else {
            $("#fieldType").val("text").change();
            $("#fieldLabel").val("");
            $("#fieldMaxLength").val("");
            $("#fieldOptions").val("");
        }
        $("#fieldModal").removeData("editTarget");
        $("#fieldModal").modal("show");
    }
    
    // Klick-Event für "Add Anonymous Field"
    $('#add-anonymous-field').click(function(){
        console.log("Add anonymous field button clicked.");
        openFieldModal("anonymous");
    });
    
    // Klick-Event für "Add Detailed Field"
    $('#add-detailed-field').click(function(){
        console.log("Add detailed field button clicked.");
        openFieldModal("detailed");
    });
    
    // Klick-Event für "Edit" Button
    $(document).on("click", ".edit-field", function(){
        console.log("Edit field button clicked.");
        var $fieldElem = $(this).closest(".form-field");
        editField($fieldElem);
    });
    
    // Klick-Event für "Remove" Button
    $(document).on("click", ".remove-field", function(){
        console.log("Remove field button clicked.");
        $(this).closest(".form-field").remove();
        updatePreview();
    });
    
    /**
     * Öffnet das Modal zur Bearbeitung eines bestehenden Feldes.
     * @param {jQuery Element} $fieldElem - Das Element, das bearbeitet werden soll.
     */
    function editField($fieldElem) {
        var field = $fieldElem.data("field");
        var section = $fieldElem.closest(".form-builder-fields").attr("id") === "anonymous-fields" ? "anonymous" : "detailed";
        $("#fieldModalSection").val(section);
        $("#fieldType").val(field.type).change();
        $("#fieldLabel").val(field.label);
        if (field.type === "text") {
            $("#fieldMaxLength").val(field.max_length || "");
        } else {
            $("#fieldMaxLength").val("");
        }
        if (field.type === "radio" || field.type === "checkbox" || field.type === "dropdown") {
            $("#fieldOptions").val(Array.isArray(field.options) ? field.options.join(", ") : "");
        } else {
            $("#fieldOptions").val("");
        }
        $("#fieldModal").data("editTarget", $fieldElem);
        $("#fieldModal").modal("show");
    }
    
    // Speichert das Feld aus dem Modal.
    $("#saveFieldBtn").click(function(){
        var section = $("#fieldModalSection").val();
        var field = {
            type: $("#fieldType").val(),
            label: $("#fieldLabel").val()
        };
        if (field.type === "text") {
            field.max_length = $("#fieldMaxLength").val();
        } else if (field.type === "radio" || field.type === "checkbox" || field.type === "dropdown") {
            field.options = $("#fieldOptions").val().split(",").map(function(opt){ return opt.trim(); });
        }
        var $editTarget = $("#fieldModal").data("editTarget");
        if ($editTarget) {
            // Bearbeite das bestehende Feld – aktualisiere das Element
            $editTarget.find("strong").text(field.label);
            $editTarget.data("field", field);
            console.log("Field updated:", field);
        } else {
            // Neues Feld hinzufügen
            renderField(section, field);
            console.log("New field added:", field);
        }
        $("#fieldModal").modal("hide");
        updatePreview();
    });
    
    /**
     * Rendert ein neues Feld und fügt es in den entsprechenden Container ein.
     * @param {string} section - "anonymous" oder "detailed"
     * @param {object} field - Das Feldobjekt
     */
    function renderField(section, field) {
        var fieldHtml = '<div class="form-field" style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">' +
            '<strong>' + field.label + '</strong> (' + field.type + ') ' +
            '<button type="button" class="edit-field button" style="float:right; margin-left:5px;">Edit</button>' +
            '<button type="button" class="remove-field button" style="float:right;">Remove</button>' +
            '</div>';
        var $fieldElem = $(fieldHtml);
        $fieldElem.data("field", field);
        if (section === "anonymous") {
            $("#anonymous-fields").append($fieldElem);
        } else {
            $("#detailed-fields").append($fieldElem);
        }
        updatePreview();
    }
    
    // Initiale Aktualisierung der Vorschau
    updatePreview();
    console.log("Form builder initialized with configuration:", workerIsFormConfig);
    
    // Render gespeicherte Felder
    if (Array.isArray(workerIsFormConfig.anonymous) && workerIsFormConfig.anonymous.length > 0) {
        workerIsFormConfig.anonymous.forEach(function(field) {
            renderField("anonymous", field);
        });
    }
    if (Array.isArray(workerIsFormConfig.detailed) && workerIsFormConfig.detailed.length > 0) {
        workerIsFormConfig.detailed.forEach(function(field) {
            renderField("detailed", field);
        });
    }
    
    // Submit-Handler: Aktualisiere Hidden-Felder vor dem Absenden
    $('form').on('submit', function() {
        updatePreview();
        console.log("Form submitted. Anonymous fields:", $('#anonymous_fields').val());
        console.log("Form submitted. Detailed fields:", $('#detailed_fields').val());
        return true;
    });
});
