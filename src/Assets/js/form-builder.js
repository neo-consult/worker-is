document.addEventListener('DOMContentLoaded', function () {
    const config = window.workerISFormConfig || { anonymous: [], detailed: [] };
  
    const groupContainers = {
      anonymous: document.querySelector('.form-builder-group[data-group="anonymous"]'),
      detailed: document.querySelector('.form-builder-group[data-group="detailed"]')
    };
  
    let currentGroup = null;
    let editIndex = null;
  
    // Render initial config
    Object.entries(config).forEach(([group, fields]) => {
      fields.forEach((field, i) => renderField(group, field, i));
    });
  
    // Add button
    document.querySelectorAll('.add-field').forEach(btn => {
      btn.addEventListener('click', () => {
        currentGroup = btn.dataset.group;
        editIndex = null;
        openModal(currentGroup);
      });
    });
  
    // Save field in modal
    document.getElementById('saveFieldBtn').addEventListener('click', function () {
      const type = document.getElementById('modal-type').value;
      const label = document.getElementById('modal-label').value;
      const optionsRaw = document.getElementById('modal-options').value.trim();
      const required = document.getElementById('modal-required').checked;
      const placeholder = document.getElementById('modal-placeholder').value;
      const maxLength = document.getElementById('modal-max-length').value;
  
      const fieldData = { type, label };
      if (required) fieldData.required = true;
      if (['text', 'textarea'].includes(type)) {
        if (placeholder) fieldData.placeholder = placeholder;
        if (maxLength) fieldData.max_length = parseInt(maxLength, 10);
      }
      if (['radio', 'checkbox', 'dropdown'].includes(type)) {
        const options = optionsRaw.split(',').map(o => o.trim()).filter(Boolean);
        fieldData.options = options;
      }
  
      const groupArray = config[currentGroup];
      if (editIndex !== null) {
        groupArray[editIndex] = fieldData;
      } else {
        groupArray.push(fieldData);
      }
  
      renderAllFields();
      bootstrap.Modal.getInstance(document.getElementById('fieldModal')).hide();
    });
  
    // Save config JSON before form submit
    document.querySelector('form').addEventListener('submit', () => {
      document.getElementById('form_config_json').value = JSON.stringify(config);
    });
  
    // Preview Modal
    const previewBtn = document.querySelector('[data-bs-target="#jsonPreviewModal"]');
    if (previewBtn) {
      previewBtn.addEventListener('click', () => {
        const json = JSON.stringify(config, null, 2);
        document.getElementById('json-preview').textContent = json;
      });
    }
  
    // Type switch → show/hide extra fields
    document.getElementById('modal-type').addEventListener('change', e => {
      const val = e.target.value;
      const showOptions = ['radio', 'checkbox', 'dropdown'].includes(val);
      const showTextProps = ['text', 'textarea'].includes(val);
  
      document.getElementById('modal-options-group').style.display = showOptions ? '' : 'none';
      document.getElementById('modal-placeholder').closest('.mb-3').style.display = showTextProps ? '' : 'none';
      document.getElementById('modal-max-length').closest('.mb-3').style.display = showTextProps ? '' : 'none';
    });
  
    function openModal(group, index = null) {
      currentGroup = group;
      editIndex = index;
  
      const field = index !== null ? config[group][index] : { type: 'text', label: '', options: [] };
  
      document.getElementById('modal-type').value = field.type || 'text';
      document.getElementById('modal-label').value = field.label || '';
      document.getElementById('modal-required').checked = !!field.required;
      document.getElementById('modal-placeholder').value = field.placeholder || '';
      document.getElementById('modal-max-length').value = field.max_length || '';
      document.getElementById('modal-options').value = field.options ? field.options.join(', ') : '';
  
      document.getElementById('modal-type').dispatchEvent(new Event('change'));
  
      const modal = new bootstrap.Modal(document.getElementById('fieldModal'));
      modal.show();
    }
  
    function renderAllFields() {
      ['anonymous', 'detailed'].forEach(group => {
        const container = groupContainers[group];
        container.innerHTML = '';
        config[group].forEach((field, i) => renderField(group, field, i));
      });
    }
  
    function renderField(group, field, index) {
      const container = groupContainers[group];
      const div = document.createElement('div');
      div.className = 'form-builder-field';
  
      div.innerHTML = `
        <strong>${field.label}</strong> <code>(${field.type})</code><br>
        ${field.options ? '<small>Optionen: ' + field.options.join(', ') + '</small><br>' : ''}
        ${field.required ? '<small>Pflichtfeld</small><br>' : ''}
        ${field.placeholder ? '<small>Platzhalter: ' + field.placeholder + '</small><br>' : ''}
        ${field.max_length ? '<small>Max. Länge: ' + field.max_length + '</small><br>' : ''}
        <button type="button" class="button edit-field" data-index="${index}" data-group="${group}">Bearbeiten</button>
        <button type="button" class="button delete-field" data-index="${index}" data-group="${group}">Entfernen</button>
      `;
  
      div.querySelector('.edit-field').addEventListener('click', () => openModal(group, index));
      div.querySelector('.delete-field').addEventListener('click', () => {
        config[group].splice(index, 1);
        renderAllFields();
      });
  
      container.appendChild(div);
    }
  });
  