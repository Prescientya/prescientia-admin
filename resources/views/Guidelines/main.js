/* Guidelines — main.js */
(function () {
    'use strict';

    /* ── Modal helpers ────────────────────────────────────── */
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('open');
    }
    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    // Close on overlay click
    document.querySelectorAll('.gl-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    // Close buttons
    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(btn.dataset.closeModal);
        });
    });

    // ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.gl-modal-overlay.open').forEach(function (m) {
                m.classList.remove('open');
            });
        }
    });

    /* ── Info Card toggle ─────────────────────────────────── */
    var infoToggle = document.getElementById('infoToggle');
    var infoBody   = document.getElementById('infoBody');
    var infoChevron = document.getElementById('infoChevron');
    if (infoToggle && infoBody) {
        infoToggle.addEventListener('click', function () {
            var open = infoBody.classList.toggle('open');
            if (infoChevron) infoChevron.style.transform = open ? 'rotate(180deg)' : '';
        });
    }

    /* ── ADD SECTION ─────────────────────────────────────── */
    var pageId = document.querySelector('form[action*="sections"]') ?
        document.querySelector('form[action*="sections"]').action.split('/').slice(-2, -1)[0] : null;
    var addSectionUrl = document.querySelector('.btn-add-item') ?
        '' : ''; // fallback

    function setupAddSection(btn) {
        if (!btn) return;
        btn.addEventListener('click', function () {
            document.getElementById('modalSectionTitle').textContent = 'Tambah Seksi';
            var form = document.getElementById('formSection');
            // Build section store URL from current page URL
            var baseUrl = window.location.pathname.replace(/\/$/, '');
            form.action = baseUrl + '/sections';
            var methodEl = document.getElementById('formSectionMethod');
            methodEl.innerHTML = '';
            document.getElementById('secTitle').value = '';
            document.getElementById('secDesc').value = '';
            document.getElementById('secOrder').value = '';
            openModal('modalSection');
        });
    }
    setupAddSection(document.getElementById('btnAddSection'));
    setupAddSection(document.getElementById('btnAddSectionEmpty'));

    /* ── EDIT SECTION ────────────────────────────────────── */
    document.querySelectorAll('.btn-edit-section').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalSectionTitle').textContent = 'Edit Seksi';
            var form = document.getElementById('formSection');
            var sectionId = btn.dataset.id;
            // Build update URL: /guidelines/sections/{id}
            var baseUrl = window.location.pathname.split('/guidelines/')[0] + '/guidelines';
            form.action = baseUrl + '/sections/' + sectionId;
            var methodEl = document.getElementById('formSectionMethod');
            methodEl.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('secTitle').value   = btn.dataset.title || '';
            document.getElementById('secDesc').value    = btn.dataset.description || '';
            document.getElementById('secOrder').value   = btn.dataset.order || 0;
            openModal('modalSection');
        });
    });

    /* ── DELETE SECTION ──────────────────────────────────── */
    document.querySelectorAll('.btn-del-section').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('deleteConfirmText').textContent =
                'Yakin ingin menghapus seksi "' + btn.dataset.title + '"?';
            document.getElementById('formDelete').action = btn.dataset.url;
            openModal('modalDelete');
        });
    });

    /* ── ADD ITEM ────────────────────────────────────────── */
    document.querySelectorAll('.btn-add-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalItemTitle').textContent =
                'Tambah Langkah — ' + btn.dataset.sectionTitle;
            document.getElementById('formItem').action   = btn.dataset.url;
            document.getElementById('formItemMethod').value = 'POST';
            resetItemForm();
            openModal('modalItem');
        });
    });

    /* ── EDIT ITEM ───────────────────────────────────────── */
    document.querySelectorAll('.btn-edit-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalItemTitle').textContent = 'Edit Langkah';
            document.getElementById('formItem').action   = btn.dataset.url;
            document.getElementById('formItemMethod').value = 'PUT';
            resetItemForm();
            document.getElementById('itemTitle').value   = btn.dataset.title || '';
            document.getElementById('itemContent').value = btn.dataset.content || '';
            document.getElementById('itemOrder').value   = btn.dataset.order || 0;

            // Show current image if exists
            var imgUrl = btn.dataset.image;
            if (imgUrl) {
                document.getElementById('currentImageWrap').style.display = 'block';
                document.getElementById('currentImage').src = imgUrl;
                document.getElementById('removeImageInput').value = '0';
            }
            openModal('modalItem');
        });
    });

    /* ── DELETE ITEM ─────────────────────────────────────── */
    document.querySelectorAll('.btn-del-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('deleteConfirmText').textContent =
                'Yakin ingin menghapus langkah "' + btn.dataset.title + '"?';
            document.getElementById('formDelete').action = btn.dataset.url;
            openModal('modalDelete');
        });
    });

    /* ── Image upload preview ────────────────────────────── */
    var itemImageInput = document.getElementById('itemImage');
    var uploadPreview  = document.getElementById('uploadPreview');
    var previewImg     = document.getElementById('previewImg');

    if (itemImageInput) {
        itemImageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    uploadPreview.classList.add('show');
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                uploadPreview.classList.remove('show');
            }
        });
    }

    // Drag over styling
    var uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        uploadArea.addEventListener('dragleave', function () {
            uploadArea.classList.remove('dragover');
        });
        uploadArea.addEventListener('drop', function () {
            uploadArea.classList.remove('dragover');
        });
    }

    /* ── Remove current image ────────────────────────────── */
    var btnRemoveImage = document.getElementById('btnRemoveImage');
    if (btnRemoveImage) {
        btnRemoveImage.addEventListener('click', function () {
            document.getElementById('removeImageInput').value = '1';
            document.getElementById('currentImageWrap').style.display = 'none';
        });
    }

    /* ── Reset item form ─────────────────────────────────── */
    function resetItemForm() {
        document.getElementById('itemTitle').value   = '';
        document.getElementById('itemContent').value = '';
        document.getElementById('itemOrder').value   = '';
        document.getElementById('currentImageWrap').style.display = 'none';
        document.getElementById('currentImage').src  = '';
        document.getElementById('removeImageInput').value = '0';
        uploadPreview.classList.remove('show');
        previewImg.src = '';
        if (itemImageInput) itemImageInput.value = '';
    }

})();
