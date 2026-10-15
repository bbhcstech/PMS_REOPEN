(function () {
    'use strict';
    document.querySelectorAll('[data-recruitment-banner]').forEach(function (input) {
        const feedback = document.getElementById(input.id + 'Feedback');
        const preview = document.getElementById(input.id + 'Preview');
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
            feedback.className = 'small mt-2 ' + (invalid ? 'text-danger' : 'text-muted');
        }
        input.addEventListener('change', function () {
            releasePreview();
            const file = input.files[0];
            if (!file) { status('', false); return; }
            if (!/\.(jpg|jpeg|png)$/i.test(file.name) || !['image/jpeg', 'image/png'].includes(file.type)) {
                status('Choose a JPG, JPEG or PNG image. PDF files are not accepted.', true);
                return;
            }
            if (file.size > Number(input.dataset.maxBytes)) {
                status('Image must be 2 MB or smaller.', true);
                return;
            }
            status('Checking image dimensions…', true);
            const url = URL.createObjectURL(file);
            const image = new Image();
            image.onload = function () {
                if (input.files[0] !== file) { URL.revokeObjectURL(url); return; }
                if (image.naturalWidth !== Number(input.dataset.width) || image.naturalHeight !== Number(input.dataset.height)) {
                    URL.revokeObjectURL(url);
                    status('Image must be exactly ' + input.dataset.width + ' × ' + input.dataset.height + ' pixels. Selected: ' + image.naturalWidth + ' × ' + image.naturalHeight + ' pixels.', true);
                    return;
                }
                previewUrl = url;
                preview.src = url;
                preview.hidden = false;
                status('Ready for PDF · ' + image.naturalWidth + ' × ' + image.naturalHeight + ' px', false);
            };
            image.onerror = function () {
                URL.revokeObjectURL(url);
                if (input.files[0] === file) status('This image cannot be read. Choose a valid JPG or PNG.', true);
            };
            image.src = url;
        });
        input.form.addEventListener('reset', function () { releasePreview(); status('', false); });
    });
})();
