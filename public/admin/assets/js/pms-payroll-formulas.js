(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        const configNode = document.getElementById('payrollFormulaData');
        if (!configNode) return;
        const config = JSON.parse(configNode.textContent);
        const functions = new Set(['MIN', 'MAX', 'ROUND', 'ABS', 'FLOOR', 'CEIL', 'IF']);
        const variables = expression => [...new Set((expression.toUpperCase().match(/\b[A-Z_][A-Z0-9_]*\b/g) || []).filter(name => !functions.has(name)))];
        const editor = document.getElementById('payrollFormulaEditor');
        const field = name => editor.querySelector(`[name="${name}"]`);
        const editorExpression = document.getElementById('formulaExpression');
        const testerExpression = document.getElementById('testerExpression');
        const editorInputs = document.getElementById('formulaEditorInputs');
        const testerInputs = document.getElementById('formulaTesterInputs');
        const editorFeedback = document.getElementById('formulaEditorFeedback');
        const testerFeedback = document.getElementById('formulaTesterFeedback');
        let savedInputs = {}, testerCode = '', testerCategory = '', editorBusy = false, testerBusy = false;

        function modal(id) { bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show(); }
        function readInputs(container) {
            const result = {};
            container.querySelectorAll('[data-variable]').forEach(input => {
                if (input.value.trim() === '' || !Number.isFinite(Number(input.value))) throw new Error(`Enter a numeric value for ${input.dataset.variable}.`);
                result[input.dataset.variable] = Number(input.value);
            });
            return result;
        }
        function renderInputs(container, expression, preset = {}) {
            const previous = {};
            container.querySelectorAll('[data-variable]').forEach(input => previous[input.dataset.variable] = input.value);
            container.replaceChildren();
            variables(expression).forEach(name => {
                const wrapper = document.createElement('div');
                const label = document.createElement('label'); label.className = 'form-label formula-variable'; label.textContent = name;
                const input = document.createElement('input'); input.type = 'number'; input.step = 'any'; input.className = 'form-control'; input.dataset.variable = name;
                input.id = container.id + '-' + name; label.htmlFor = input.id;
                input.value = Object.hasOwn(preset, name) ? preset[name] : (previous[name] ?? config.samples[name] ?? '');
                wrapper.append(label, input); container.append(wrapper);
            });
            if (!container.childElementCount) { const note = document.createElement('p'); note.className = 'text-muted small'; note.textContent = 'This expression uses no variable inputs.'; container.append(note); }
        }
        function refreshEditor() {
            const code = field('code').value.toUpperCase().trim(), category = field('category').value;
            const allowed = config.definitions[code]?.[1] || config.customVariables[category] || [];
            const palette = document.getElementById('formulaVariablePalette'); palette.replaceChildren();
            allowed.forEach(name => {
                const button = document.createElement('button'); button.type = 'button'; button.className = 'btn btn-sm btn-outline-secondary font-monospace'; button.textContent = name;
                button.addEventListener('click', () => { const start = editorExpression.selectionStart, end = editorExpression.selectionEnd; editorExpression.setRangeText(name, start, end, 'end'); editorExpression.focus(); refreshEditor(); });
                palette.append(button);
            });
            renderInputs(editorInputs, editorExpression.value, savedInputs); savedInputs = {};
            editorFeedback.textContent = 'Expression changed. Run a test before saving.';
        }
        function openEditor(record = {}) {
            editor.reset(); field('_method').value = record.id ? 'PUT' : 'POST'; field('formula_id').value = record.id || '';
            editor.action = record.id ? config.editUrl.replace('__ID__', encodeURIComponent(record.id)) : config.storeUrl;
            ['name', 'code', 'category', 'formula', 'description', 'effective_from', 'effective_to'].forEach(name => { if (record[name] != null) field(name).value = record[name]; });
            field('version').value = record.version || 1; field('code').readOnly = Boolean(record.id);
            document.getElementById('formulaEnabled').checked = Boolean(record.is_active);
            document.getElementById('formulaEditorTitle').textContent = record.id ? `Edit ${record.name}` : 'Create payroll formula';
            savedInputs = record.test_inputs || {}; if (typeof savedInputs === 'string') { try { savedInputs = JSON.parse(savedInputs); } catch (_) { savedInputs = {}; } }
            editorInputs.replaceChildren(); refreshEditor(); modal('formulaEditorModal');
        }
        function openTester(record) {
            testerCode = record.code || ''; testerCategory = record.category || '';
            testerExpression.value = record.formula;
            document.getElementById('formulaTesterTitle').textContent = `Test: ${record.name}`;
            testerInputs.replaceChildren(); renderInputs(testerInputs, testerExpression.value, record.test_inputs || {});
            testerFeedback.textContent = 'Ready to calculate with the displayed inputs.'; modal('formulaTesterModal');
        }
        async function runTest(expression, container, feedback, button, code, category) {
            const expressionText = expression.value;
            const inputs = readInputs(container);
            button.disabled = true; feedback.textContent = 'Calculating...';
            try {
                const response = await fetch(config.testUrl, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrf}, body: JSON.stringify({formula: expressionText, variables: inputs, code, category})});
                const data = await response.json().catch(() => { throw new Error('The test could not complete. Refresh your session and try again.'); });
                if (!response.ok || !data.success) throw new Error(data.error || data.message || 'Calculation failed.');
                if (expression.value !== expressionText || JSON.stringify(readInputs(container)) !== JSON.stringify(inputs)) throw new Error('Inputs changed during calculation. Run the test again.');
                feedback.textContent = `Calculated result: ${data.formatted} · Test completed using the displayed inputs.`;
                return inputs;
            } catch (error) { feedback.textContent = error.message; throw error; }
            finally { button.disabled = false; }
        }
        document.querySelectorAll('[data-test-formula]').forEach(button => button.addEventListener('click', () => openTester(config.formulas[button.dataset.testFormula])));
        document.querySelectorAll('[data-test-example]').forEach(button => button.addEventListener('click', () => openTester(config.examples[button.dataset.testExample])));
        document.getElementById('runFormulaTest').addEventListener('click', async function () {
            if (testerBusy) return; testerBusy = true;
            try { await runTest(testerExpression, testerInputs, testerFeedback, this, testerCode, testerCategory); } catch (error) { testerFeedback.textContent = error.message; }
            finally { testerBusy = false; }
        });
        testerExpression.addEventListener('input', () => { renderInputs(testerInputs, testerExpression.value); testerFeedback.textContent = 'Expression changed. Run the test again.'; });
        testerInputs.addEventListener('input', () => testerFeedback.textContent = 'Inputs changed. Run the test again.');
        if (!editor) return;
        document.getElementById('addPayrollFormula').addEventListener('click', () => openEditor());
        document.querySelectorAll('[data-edit-formula]').forEach(button => button.addEventListener('click', () => openEditor(config.formulas[button.dataset.editFormula])));
        document.querySelectorAll('[data-use-example]').forEach(button => button.addEventListener('click', () => {
            const example = config.examples[button.dataset.useExample];
            const existing = Object.values(config.formulas).find(record => record.code === example.code);
            if (existing?.deleted_at) {
                window.alert('This code belongs to an archived formula. Select the Archived filter and restore it before editing.');
                return;
            }
            openEditor({...existing, ...example, name: existing?.name || example.name});
        }));
        field('code').addEventListener('input', () => {
            field('code').value = field('code').value.toUpperCase();
            const definition = config.definitions[field('code').value.trim()];
            if (definition) field('category').value = definition[0];
            refreshEditor();
        });
        field('category').addEventListener('change', refreshEditor); editorExpression.addEventListener('input', refreshEditor);
        editorInputs.addEventListener('input', () => editorFeedback.textContent = 'Inputs changed. Run the test again.');
        document.getElementById('testEditorFormula').addEventListener('click', async function () {
            if (editorBusy) return; editorBusy = true;
            try { await runTest(editorExpression, editorInputs, editorFeedback, this, field('code').value, field('category').value); } catch (error) { editorFeedback.textContent = error.message; }
            finally { editorBusy = false; }
        });
        editor.addEventListener('submit', async event => {
            event.preventDefault(); if (editorBusy || !editor.reportValidity()) return;
            editorBusy = true; const button = editor.querySelector('button[type="submit"]');
            const editorState = JSON.stringify([editor.action, ...new FormData(editor)]);
            try {
                const inputs = await runTest(editorExpression, editorInputs, editorFeedback, button, field('code').value, field('category').value);
                if (JSON.stringify([editor.action, ...new FormData(editor)]) !== editorState) throw new Error('The formula settings changed during testing. Review them and save again.');
                field('test_inputs').value = JSON.stringify(inputs); button.disabled = true;
                HTMLFormElement.prototype.submit.call(editor);
            } catch (error) { editorFeedback.textContent = error.message; editorBusy = false; button.disabled = false; }
        });
        if (config.old?.name) openEditor({...config.old, id: config.old.formula_id || null, is_active: config.old.is_active === '1'});
    });
})();
