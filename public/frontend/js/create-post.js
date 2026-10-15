(function () {
    'use strict';

    var form    = document.getElementById('createPostForm');
    var modalEl = document.getElementById('createPostModal');
    if (!form || !modalEl) { return; }

    var MAX_FILES = 10;
    var MAX_MB    = { image: 5, video: 50, doc: 10 };
    var DOC_EXT   = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
    var ACCEPT_MEDIA = 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm';
    var ACCEPT_DOCS  = '.pdf,.doc,.docx,.ppt,.pptx,.xls';

    var content      = document.getElementById('cpContent');
    var fileInput    = document.getElementById('cpFiles');
    var thumbs       = document.getElementById('cpThumbs');
    var errorBox     = document.getElementById('cpError');
    var submitBtn    = document.getElementById('cpSubmit');
    var pickMediaBtn = document.getElementById('cpPickMedia');
    var pickDocBtn   = document.getElementById('cpPickDoc');
    var feelingBtn   = document.getElementById('cpToggleFeeling');
    var feelingBar   = document.getElementById('cpFeelingBar');
    var feelingInput = document.getElementById('cpFeelingInput');
    var feelingTag   = document.getElementById('cpFeelingTag');
    var feelingText  = document.getElementById('cpFeelingText');
    var feelingClear = document.getElementById('cpFeelingClear');
    var locationBtn  = document.getElementById('cpToggleLocation');
    var locationRow  = document.getElementById('cpLocationRow');
    var locationIn   = document.getElementById('cpLocationInput');

    var lightbox = document.getElementById('cpLightbox');
    var lbStage  = document.getElementById('cpLbStage');
    var lbTitle  = document.getElementById('cpLbTitle');
    var lbCount  = document.getElementById('cpLbCount');
    var lbPrev   = document.getElementById('cpLbPrev');
    var lbNext   = document.getElementById('cpLbNext');
    var lbClose  = document.getElementById('cpLbClose');

    var items = [];          // { file, kind: 'image' | 'video' | 'doc', ext, url (sirf image/video) }
    var discarding = false;  // true = user ne "Discard" confirm kar diya
    var lbIndex = -1;
    var lbBlobUrl = null;    // lightbox me document ke liye temporary URL
    var lbOpener = null;

    function extOf(name) {
        var dot = name.lastIndexOf('.');
        return dot > -1 ? name.slice(dot + 1).toLowerCase() : '';
    }

    function kindOf(file) {
        if (/^image\/(jpeg|png|webp|gif)$/.test(file.type)) { return 'image'; }
        if (/^video\/(mp4|webm)$/.test(file.type)) { return 'video'; }
        return DOC_EXT.indexOf(extOf(file.name)) !== -1 ? 'doc' : null;
    }

    function formatSize(bytes) {
        return bytes >= 1048576
            ? (bytes / 1048576).toFixed(1) + ' MB'
            : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    function showError(messages) {
        errorBox.textContent = messages.join(' ');
        errorBox.hidden = messages.length === 0;
    }

    function isDirty() {
        return !!(content.value.trim() || items.length || feelingInput.value || locationIn.value.trim());
    }

    function refresh() {
        submitBtn.disabled = !(content.value.trim() || items.length);
    }

    // ---------- Files ----------
    function syncInput() {
        var dt = new DataTransfer();
        items.forEach(function (it) { dt.items.add(it.file); });
        fileInput.files = dt.files;
    }

    function buildTile(it, index) {
        var box = document.createElement('div');
        box.className = 'tc-thumb';

        if (it.kind === 'doc') {
            box.classList.add('tc-thumb--doc');
            var icon = document.createElement('i');
            icon.className = 'fas fa-file-lines';
            var name = document.createElement('span');
            name.textContent = it.file.name; // textContent: file name kabhi HTML nahi banta
            box.appendChild(icon);
            box.appendChild(name);
        } else {
            var media;
            if (it.kind === 'video') {
                media = document.createElement('video');
                media.muted = true;
                media.preload = 'metadata';
            } else {
                media = document.createElement('img');
                media.alt = '';
            }
            media.src = it.url;
            box.appendChild(media);
        }

        var open = document.createElement('button');
        open.type = 'button';
        open.className = 'tc-thumb__open';
        open.setAttribute('aria-label', 'Preview ' + it.file.name);
        open.addEventListener('click', function () { openLightbox(index, open); });
        box.appendChild(open);

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.setAttribute('aria-label', 'Remove file');
        remove.innerHTML = '<i class="fas fa-xmark"></i>'; // static markup only
        remove.addEventListener('click', function () { removeItem(index); });
        box.appendChild(remove);

        return box;
    }

    function renderThumbs() {
        thumbs.textContent = '';
        items.forEach(function (it, index) { thumbs.appendChild(buildTile(it, index)); });
    }

    function removeItem(index) {
        if (items[index].url) { URL.revokeObjectURL(items[index].url); }
        items.splice(index, 1);
        syncInput();
        renderThumbs();
        showError([]);
        refresh();
    }

    function addFiles(list) {
        var errors = [];
        list.forEach(function (file) {
            if (items.length >= MAX_FILES) {
                errors.push('You can attach up to ' + MAX_FILES + ' files.');
                return;
            }
            var kind = kindOf(file);
            if (!kind) {
                errors.push('"' + file.name + '" is not a supported file type.');
                return;
            }
            if (file.size > MAX_MB[kind] * 1024 * 1024) {
                errors.push('"' + file.name + '" is too large (max ' + MAX_MB[kind] + ' MB).');
                return;
            }
            items.push({
                file: file,
                kind: kind,
                ext: extOf(file.name),
                url: kind === 'doc' ? null : URL.createObjectURL(file)
            });
        });

        syncInput();
        renderThumbs();
        showError(errors.filter(function (e, i, all) { return all.indexOf(e) === i; }));
        refresh();
    }

    function openPicker(accept) {
        fileInput.accept = accept;
        fileInput.click();
    }

    pickMediaBtn.addEventListener('click', function () { openPicker(ACCEPT_MEDIA); });
    pickDocBtn.addEventListener('click', function () { openPicker(ACCEPT_DOCS); });
    fileInput.addEventListener('change', function () {
        addFiles(Array.prototype.slice.call(fileInput.files)); // syncInput se pehle copy
    });

    // ---------- Lightbox (attachment preview) ----------
    function clearStage() {
        if (lbBlobUrl) { URL.revokeObjectURL(lbBlobUrl); lbBlobUrl = null; }
        lbStage.textContent = ''; // video/iframe hat jate hain, playback ruk jata hai
    }

    function buildFilePanel(it) {
        var panel = document.createElement('div');
        panel.className = 'tc-lightbox__file';

        var icon = document.createElement('i');
        icon.className = 'fas fa-file-lines';
        var name = document.createElement('strong');
        name.textContent = it.file.name;
        var size = document.createElement('small');
        size.textContent = it.ext.toUpperCase() + ' \u00b7 ' + formatSize(it.file.size)
            + ' \u00b7 Preview is not available for this file type.';

        lbBlobUrl = URL.createObjectURL(it.file);
        var link = document.createElement('a');
        link.className = 'tc-btn-primary text-decoration-none';
        link.href = lbBlobUrl;
        link.download = it.file.name;
        link.textContent = 'Download to view';

        panel.appendChild(icon);
        panel.appendChild(name);
        panel.appendChild(size);
        panel.appendChild(link);
        return panel;
    }

    function showInLightbox(index) {
        clearStage();
        lbIndex = index;
        var it = items[index];

        lbTitle.textContent = it.file.name;
        lbCount.textContent = items.length > 1 ? (index + 1) + ' / ' + items.length : '';
        lbPrev.hidden = index === 0;
        lbNext.hidden = index === items.length - 1;

        var node;
        if (it.kind === 'image') {
            node = document.createElement('img');
            node.alt = '';
            node.src = it.url;
        } else if (it.kind === 'video') {
            node = document.createElement('video');
            node.controls = true;
            node.src = it.url;
        } else if (it.ext === 'pdf' || it.ext === 'txt') {
            var type = it.ext === 'pdf' ? 'application/pdf' : 'text/plain;charset=utf-8';
            lbBlobUrl = URL.createObjectURL(new Blob([it.file], { type: type }));
            node = document.createElement('iframe');
            node.title = it.file.name;
            node.src = lbBlobUrl;
        } else {
            node = buildFilePanel(it);
        }
        lbStage.appendChild(node);
    }

    function onLightboxKey(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation(); // post modal tak Esc na pahunche
            closeLightbox();
        } else if (e.key === 'ArrowLeft' && lbIndex > 0) {
            showInLightbox(lbIndex - 1);
        } else if (e.key === 'ArrowRight' && lbIndex < items.length - 1) {
            showInLightbox(lbIndex + 1);
        }
    }

    function openLightbox(index, opener) {
        lbOpener = opener || null;
        showInLightbox(index);
        lightbox.hidden = false;
        document.addEventListener('keydown', onLightboxKey, true); // capture: modal se pehle
        lbClose.focus();
    }

    function closeLightbox() {
        if (lightbox.hidden) { return; }
        lightbox.hidden = true;
        document.removeEventListener('keydown', onLightboxKey, true);
        clearStage();
        lbIndex = -1;
        if (lbOpener && document.contains(lbOpener)) { lbOpener.focus(); }
        lbOpener = null;
    }

    lbClose.addEventListener('click', closeLightbox);
    lbPrev.addEventListener('click', function () { if (lbIndex > 0) { showInLightbox(lbIndex - 1); } });
    lbNext.addEventListener('click', function () {
        if (lbIndex < items.length - 1) { showInLightbox(lbIndex + 1); }
    });
    lbStage.addEventListener('click', function (e) {
        if (e.target === lbStage) { closeLightbox(); } // khali andhere par click
    });

    // ---------- Feeling ----------
    function setFeeling(value, label) {
        feelingInput.value = value;
        feelingText.textContent = label ? 'Feeling ' + label : '';
        feelingTag.hidden = !value;
        feelingBar.hidden = true;
    }

    feelingBtn.addEventListener('click', function () {
        feelingBar.hidden = !feelingBar.hidden;
    });

    feelingBar.addEventListener('click', function (e) {
        var chip = e.target.closest('[data-feeling]');
        if (chip) { setFeeling(chip.dataset.feeling, chip.dataset.label); }
    });

    feelingClear.addEventListener('click', function () { setFeeling('', ''); });

    // ---------- Check-in ----------
    locationBtn.addEventListener('click', function () {
        locationRow.hidden = !locationRow.hidden;
        if (!locationRow.hidden) { locationIn.focus(); }
    });

    // ---------- Textarea ----------
    content.addEventListener('input', function () {
        content.style.height = 'auto';
        content.style.height = Math.min(content.scrollHeight, 320) + 'px';
        refresh();
    });

    // ---------- Discard confirm (modal band karne se pehle) ----------
    modalEl.addEventListener('hide.bs.modal', function (e) {
        if (discarding || !isDirty()) { return; } // khali ho ya confirm ho chuka ho: band hone do

        e.preventDefault(); // abhi band mat karo
        if (Swal.isVisible()) { return; }

        Swal.fire({
            title: 'Discard post?',
            text: 'If you leave now, your post will not be saved.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Discard',
            cancelButtonText: 'Keep editing',
            confirmButtonColor: '#d33',
            reverseButtons: true,
            allowOutsideClick: false
        }).then(function (result) {
            if (result.isConfirmed) {
                discarding = true;
                bootstrap.Modal.getInstance(modalEl).hide();
            }
        });
    });

    // ---------- Reset (modal poori tarah band hone ke baad) ----------
    function resetForm() {
        closeLightbox();
        items.forEach(function (it) { if (it.url) { URL.revokeObjectURL(it.url); } });
        items = [];
        form.reset(); // text, privacy, location input, file input
        thumbs.textContent = '';
        setFeeling('', '');
        locationRow.hidden = true;
        content.style.height = '';
        discarding = false;
        showError([]);
        refresh();
    }

    modalEl.addEventListener('hidden.bs.modal', resetForm);

    // ---------- Submit ----------
    // TEMP (design phase): save route banne par ye handler hata kar normal POST chalega.
    // Post save hone ke baad `discarding = true` set karke modal hide karna, taaki confirm na puche.
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        window.showToastMessage('info', 'Posting will be enabled in the next phase.');
    });

    refresh();
})();

// (function () {
//     'use strict';

//     var form    = document.getElementById('createPostForm');
//     var modalEl = document.getElementById('createPostModal');
//     if (!form || !modalEl) { return; }

//     var MAX_FILES = 10;
//     var MAX_MB    = { image: 5, video: 50, doc: 10 };
//     var DOC_EXT   = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
//     var ACCEPT_MEDIA = 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm';
//     var ACCEPT_DOCS  = '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt';

//     var content      = document.getElementById('cpContent');
//     var fileInput    = document.getElementById('cpFiles');
//     var thumbs       = document.getElementById('cpThumbs');
//     var errorBox     = document.getElementById('cpError');
//     var submitBtn    = document.getElementById('cpSubmit');
//     var pickMediaBtn = document.getElementById('cpPickMedia');
//     var pickDocBtn   = document.getElementById('cpPickDoc');
//     var feelingBtn   = document.getElementById('cpToggleFeeling');
//     var feelingBar   = document.getElementById('cpFeelingBar');
//     var feelingInput = document.getElementById('cpFeelingInput');
//     var feelingTag   = document.getElementById('cpFeelingTag');
//     var feelingText  = document.getElementById('cpFeelingText');
//     var feelingClear = document.getElementById('cpFeelingClear');
//     var locationBtn  = document.getElementById('cpToggleLocation');
//     var locationRow  = document.getElementById('cpLocationRow');
//     var locationIn   = document.getElementById('cpLocationInput');

//     var items = []; // { file, kind: 'image' | 'video' | 'doc', url (sirf image/video) }

//     function kindOf(file) {
//         if (/^image\/(jpeg|png|webp|gif)$/.test(file.type)) { return 'image'; }
//         if (/^video\/(mp4|webm)$/.test(file.type)) { return 'video'; }
//         var dot = file.name.lastIndexOf('.');
//         var ext = dot > -1 ? file.name.slice(dot + 1).toLowerCase() : '';
//         return DOC_EXT.indexOf(ext) !== -1 ? 'doc' : null;
//     }

//     function showError(messages) {
//         errorBox.textContent = messages.join(' ');
//         errorBox.hidden = messages.length === 0;
//     }

//     function refresh() {
//         submitBtn.disabled = !(content.value.trim() || items.length);
//     }

//     // ---------- Files ----------
//     function syncInput() {
//         var dt = new DataTransfer();
//         items.forEach(function (it) { dt.items.add(it.file); });
//         fileInput.files = dt.files;
//     }

//     function buildTile(it, index) {
//         var box = document.createElement('div');
//         box.className = 'tc-thumb';

//         if (it.kind === 'doc') {
//             box.classList.add('tc-thumb--doc');
//             var icon = document.createElement('i');
//             icon.className = 'fas fa-file-lines';
//             var name = document.createElement('span');
//             name.textContent = it.file.name; // textContent: file name kabhi HTML nahi banta
//             box.appendChild(icon);
//             box.appendChild(name);
//         } else {
//             var media;
//             if (it.kind === 'video') {
//                 media = document.createElement('video');
//                 media.muted = true;
//                 media.preload = 'metadata';
//             } else {
//                 media = document.createElement('img');
//                 media.alt = '';
//             }
//             media.src = it.url;
//             box.appendChild(media);
//         }

//         var remove = document.createElement('button');
//         remove.type = 'button';
//         remove.setAttribute('aria-label', 'Remove file');
//         remove.innerHTML = '<i class="fas fa-xmark"></i>'; // static markup only
//         remove.addEventListener('click', function () { removeItem(index); });
//         box.appendChild(remove);

//         return box;
//     }

//     function renderThumbs() {
//         thumbs.textContent = '';
//         items.forEach(function (it, index) { thumbs.appendChild(buildTile(it, index)); });
//     }

//     function removeItem(index) {
//         if (items[index].url) { URL.revokeObjectURL(items[index].url); }
//         items.splice(index, 1);
//         syncInput();
//         renderThumbs();
//         showError([]);
//         refresh();
//     }

//     function addFiles(list) {
//         var errors = [];
//         list.forEach(function (file) {
//             if (items.length >= MAX_FILES) {
//                 errors.push('You can attach up to ' + MAX_FILES + ' files.');
//                 return;
//             }
//             var kind = kindOf(file);
//             if (!kind) {
//                 errors.push('"' + file.name + '" is not a supported file type.');
//                 return;
//             }
//             if (file.size > MAX_MB[kind] * 1024 * 1024) {
//                 errors.push('"' + file.name + '" is too large (max ' + MAX_MB[kind] + ' MB).');
//                 return;
//             }
//             items.push({
//                 file: file,
//                 kind: kind,
//                 url: kind === 'doc' ? null : URL.createObjectURL(file)
//             });
//         });

//         syncInput();
//         renderThumbs();
//         showError(errors.filter(function (e, i, all) { return all.indexOf(e) === i; }));
//         refresh();
//     }

//     function openPicker(accept) {
//         fileInput.accept = accept;
//         fileInput.click();
//     }

//     pickMediaBtn.addEventListener('click', function () { openPicker(ACCEPT_MEDIA); });
//     pickDocBtn.addEventListener('click', function () { openPicker(ACCEPT_DOCS); });
//     fileInput.addEventListener('change', function () {
//         addFiles(Array.prototype.slice.call(fileInput.files)); // syncInput se pehle copy
//     });

//     // ---------- Feeling ----------
//     function setFeeling(value, label) {
//         feelingInput.value = value;
//         feelingText.textContent = label ? 'Feeling ' + label : '';
//         feelingTag.hidden = !value;
//         feelingBar.hidden = true;
//     }

//     feelingBtn.addEventListener('click', function () {
//         feelingBar.hidden = !feelingBar.hidden;
//     });

//     feelingBar.addEventListener('click', function (e) {
//         var chip = e.target.closest('[data-feeling]');
//         if (chip) { setFeeling(chip.dataset.feeling, chip.dataset.label); }
//     });

//     feelingClear.addEventListener('click', function () { setFeeling('', ''); });

//     // ---------- Check-in ----------
//     locationBtn.addEventListener('click', function () {
//         locationRow.hidden = !locationRow.hidden;
//         if (!locationRow.hidden) { locationIn.focus(); }
//     });

//     // ---------- Textarea ----------
//     content.addEventListener('input', function () {
//         content.style.height = 'auto';
//         content.style.height = Math.min(content.scrollHeight, 320) + 'px';
//         refresh();
//     });

//     // ---------- Reset (modal band hone par) ----------
//     function resetForm() {
//         items.forEach(function (it) { if (it.url) { URL.revokeObjectURL(it.url); } });
//         items = [];
//         form.reset(); // text, privacy, location input, file input
//         thumbs.textContent = '';
//         setFeeling('', '');
//         locationRow.hidden = true;
//         content.style.height = '';
//         showError([]);
//         refresh();
//     }

//     modalEl.addEventListener('hidden.bs.modal', resetForm);

//     // ---------- Submit ----------
//     // TEMP (design phase): save route banne par ye handler hata kar normal POST chalega.
//     form.addEventListener('submit', function (e) {
//         e.preventDefault();
//         window.showToastMessage('info', 'Posting will be enabled in the next phase.');
//     });

//     refresh();
// })();