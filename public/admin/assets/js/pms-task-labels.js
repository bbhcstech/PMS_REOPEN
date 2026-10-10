document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('taskLabelCreateForm');
    if (!form) return;
    const modal = document.getElementById('taskLabelsModal');
    const button = modal.querySelector('[form="taskLabelCreateForm"]');
    let saving = false;
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (saving || !form.reportValidity()) return;
        saving = true;
        button.disabled = true;
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Could not add label.');
            const label = data.label;
            document.querySelectorAll('select[name="task_labels[]"]').forEach(select => {
                select.add(new Option(label.label_name, label.id, true, true));
                if (window.jQuery) window.jQuery(select).trigger('change');
                else select.dispatchEvent(new Event('change', {bubbles: true}));
            });
            const body = modal.querySelector('tbody');
            body.querySelectorAll('tr').forEach(row => {if (row.querySelector('td[colspan]')) row.remove();});
            const row = body.insertRow();
            for (const value of [body.rows.length, label.label_name, label.color, label.description || '', label.project_name || '-']) row.insertCell().textContent = value;
            const badge = document.createElement('span');
            badge.className = 'badge'; badge.textContent = label.color;
            if (/^#[0-9a-f]{6}$/i.test(label.color)) badge.style.backgroundColor = label.color;
            row.cells[2].replaceChildren(badge);
            const deleteForm = document.createElement('form');
            deleteForm.method = 'POST'; deleteForm.action = label.delete_url;
            const csrf = form.querySelector('[name="_token"]').cloneNode(true);
            const remove = document.createElement('button');
            remove.type = 'submit'; remove.className = 'btn btn-sm btn-danger'; remove.textContent = 'Delete';
            deleteForm.append(csrf, remove);
            deleteForm.addEventListener('submit', event => {if (!confirm('Delete this label?')) event.preventDefault();});
            row.insertCell().append(deleteForm);
            form.querySelector('[name="name"]').value = '';
            form.querySelector('[name="description"]').value = '';
            window.showToast(data.message, 'success');
        } catch (error) {
            window.showToast(error.message || 'Could not add label. Please try again.', 'error');
        } finally {
            saving = false;
            button.disabled = false;
        }
    });
});
