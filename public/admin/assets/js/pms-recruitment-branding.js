(function () {
    'use strict';
    document.querySelectorAll('[data-recruitment-branding]').forEach(function (card) {
        const source = card.querySelector('[data-branding-source]');
        const upload = card.querySelector('[data-branding-upload]');
        const input = card.querySelector('[data-branding-file]');
        const name = card.querySelector('[data-branding-name]');
        const feedback = card.querySelector('[data-branding-feedback]');
        const preview = card.querySelector('[data-branding-preview]');
        let previewUrl = null;

        function releasePreview() {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            preview.hidden = true;
            preview.removeAttribute('src');
        }
        function status(message, invalid) {
            input.setCustomValidity(invalid ? message : '');
            input.classList.toggle('is-invalid', invalid);
            input.setAttribute('aria-invalid', String(invalid));
            feedback.textContent = message;
            feedback.hidden = !message;
            feedback.classList.toggle('is-error', invalid);
        }

        function showPreview(url) {
            preview.src = url;
            preview.hidden = false;
        }

        function checkUpload() {
            releasePreview();
            const file = input.files[0];
            if (!file) { status('', false); return; }
            if (!/\.(jpg|jpeg|png)$/i.test(file.name) || (file.type && !['image/jpeg', 'image/png'].includes(file.type))) {
                status('Choose a JPG, JPEG or PNG image. PDF files are not accepted.', true);
                return;
            }
            if (file.size > Number(input.dataset.maxBytes)) {
                status('Image must be 2 MB or smaller.', true);
                return;
            }
            status('Checking image dimensions…', true);
            const url = URL.createObjectURL(file);
            previewUrl = url;
            const image = new Image();
            image.onload = function () {
                if (previewUrl !== url) return;
                if (image.naturalWidth !== Number(input.dataset.width) || image.naturalHeight !== Number(input.dataset.height)) {
                    releasePreview();
                    status('Image must be exactly ' + input.dataset.width + ' × ' + input.dataset.height + ' pixels. Selected: ' + image.naturalWidth + ' × ' + image.naturalHeight + ' pixels.', true);
                    return;
                }
                showPreview(url);
                status('Ready to save with this requirement.', false);
            };
            image.onerror = function () {
                if (previewUrl !== url) return;
                releasePreview();
                status('This image cannot be read. Choose a valid JPG or PNG.', true);
            };
            image.src = url;
        }

        function updateSource() {
            const saved = source.value !== '';
            upload.hidden = saved;
            input.disabled = saved;
            input.required = !saved;
            name.disabled = saved;
            source.classList.remove('is-invalid');

            if (!saved) {
                checkUpload();
                return;
            }

            releasePreview();
            status('', false);
            showPreview(source.selectedOptions[0].dataset.preview);
        }

        input.addEventListener('change', checkUpload);
        source.addEventListener('change', updateSource);
        input.form.addEventListener('reset', function () { setTimeout(updateSource, 0); });
    });
})();
