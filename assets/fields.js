(() => {
  'use strict';
  const form = document.getElementById('mbfe-fields-form');
  if (!form) return;
  let dirty = false;
  const status = document.getElementById('mbfe-save-status');
  const changed = () => {
    dirty = true; status.textContent = 'Unsaved field changes';
    document.dispatchEvent(new Event('mbfe:dirty'));
  };
  form.addEventListener('input', changed);
  form.addEventListener('change', changed);
  window.jQuery(form).on('change', ':input', changed);
  window.jQuery(document).on('tinymce-editor-init', (_event, editor) => {
    if (form.contains(document.getElementById(editor.id))) editor.on('change input undo redo', changed);
  });
  window.addEventListener('beforeunload', event => {
    if (dirty) { event.preventDefault(); event.returnValue = ''; }
  });
  form.addEventListener('submit', event => {
    queueMicrotask(() => {
      if (!event.defaultPrevented) { dirty = false; status.textContent = 'Saving field values…'; }
    });
  });
})();
