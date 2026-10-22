/* ==========================================================================
   TeachYou: main.js
   Loaded on BOTH layouts (guest + main). Har module apne elements na milne
   par kuch nahi karta, isliye login page par sirf "Common" chalta hai.

   1. Common      : CSRF, toast, confirm dialog   (login + app, auth.js bhi use karta hai)
   2. Theme       : night-mode toggle             (app)
   3. Session     : auto-logout + heartbeat       (app)
   4. Stories     : slider arrows                 (home)
   5. Create post : modal, files, lightbox        (home)
   6. Feed        : infinite scroll, post actions (home)
   ========================================================================== */
(function () {
    'use strict';

    var body = document.body;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    /* ======================================================================
       1. COMMON
       ====================================================================== */
    function initCommon() {
        // jQuery AJAX ke har request me CSRF token
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken() } });

        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: function (toast) {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        window.Toast = Toast;

        window.showToastMessage = function (type, message) {
            Toast.fire({ icon: type, title: message });
        };

        window.confirmGlobalDecision = function (options, onConfirm, onDeny, onCancel) {
            Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || '',
                icon: options.icon || 'question',
                showDenyButton: options.showDeny !== false,
                showCancelButton: options.showCancel !== false,
                confirmButtonText: options.confirmText || 'Yes',
                denyButtonText: options.denyText || 'No',
                cancelButtonText: options.cancelText || 'Cancel',
                confirmButtonColor: options.confirmColor || '#d33',
                denyButtonColor: options.denyColor || '#3085d6',
                cancelButtonColor: options.cancelColor || '#6c757d',
                reverseButtons: true,
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed && typeof onConfirm === 'function') { onConfirm(); }
                else if (result.isDenied && typeof onDeny === 'function') { onDeny(); }
                else if (result.isDismissed && typeof onCancel === 'function') { onCancel(); }
            });
        };

        // Auto-logout ke baad login page par message
        try {
            if (sessionStorage.getItem('frontend_logout_reason') === 'inactivity') {
                sessionStorage.removeItem('frontend_logout_reason');
                window.showToastMessage('error', 'You have been securely logged out due to inactivity.');
            }
        } catch (e) { /* storage blocked */ }
    }

    /* ======================================================================
       2. THEME (night mode toggle)
       Page load par theme set karna <head> ke inline script ka kaam hai
       (flash se bachne ke liye). Yahan sirf toggle button hai.
       ====================================================================== */
    function initTheme() {
        var toggle = document.getElementById('themeToggle');
        if (!toggle) { return; }

        var root = document.documentElement;
        var STORAGE_KEY = 'edu_theme'; // main.blade.php ke inline script me bhi yahi key hai

        function current() {
            return root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        }

        function sync() {
            toggle.setAttribute('aria-checked', current() === 'dark' ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            var next = current() === 'dark' ? 'light' : 'dark';

            // Transitions band karke theme badlo, phir style apply hone ke baad chalu
            root.classList.add('theme-switching');
            root.setAttribute('data-bs-theme', next);
            void root.offsetWidth; // browser ko naya style turant apply karne ko majboor karta hai
            root.classList.remove('theme-switching');

            try { localStorage.setItem(STORAGE_KEY, next); } catch (e) { /* storage blocked */ }
            sync();
        });

        // toggle.addEventListener('click', function () {
        //     var next = current() === 'dark' ? 'light' : 'dark';
        //     root.setAttribute('data-bs-theme', next);
        //     try { localStorage.setItem(STORAGE_KEY, next); } catch (e) { /* storage blocked */ }
        //     sync();
        // });

        sync();
    }

    /* ======================================================================
       3. SESSION (auto-logout + heartbeat)
       Config <body> ke data-* attributes se aati hai (main.blade.php).
       ====================================================================== */
    function initSession() {
        var keepActiveUrl = body.dataset.keepActiveUrl;
        if (!keepActiveUrl) { return; } // login page: skip

        var logoutUrl    = body.dataset.logoutUrl;
        var loginUrl     = body.dataset.loginUrl;
        var isRemembered = body.dataset.remembered === '1';
        var maxIdleMs    = (parseInt(body.dataset.idleSeconds, 10) || 300) * 1000;

        var lastActivity    = Date.now();
        var activeSinceBeat = false;
        var loggingOut      = false;

        function markActive() {
            lastActivity = Date.now();
            activeSinceBeat = true;
        }

        ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, markActive, { passive: true, capture: true });
        });

        function logout(reason) {
            if (loggingOut) { return; }
            loggingOut = true;

            try { sessionStorage.setItem('frontend_logout_reason', reason); } catch (e) {}

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = logoutUrl;

            var token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken();

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }

        // Auto logout: sirf jab "Remember me" OFF ho. Timestamp based, to
        // background tab / sleep me bhi sahi chalta hai.
        if (!isRemembered) {
            setInterval(function () {
                if (Date.now() - lastActivity >= maxIdleMs) { logout('inactivity'); }
            }, 1000);
        }

        // Heartbeat: sirf tab jab user pichle beat ke baad active raha
        setInterval(function () {
            if (!activeSinceBeat || loggingOut) { return; }
            activeSinceBeat = false;

            fetch(keepActiveUrl, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (response) {
                if (response.status === 401 || response.status === 419) {
                    window.location.href = loginUrl;
                }
            }).catch(function () { /* network error: agle beat me retry */ });
        }, 20000);
    }

    /* ======================================================================
       4. STORIES (slider arrows)
       ====================================================================== */
    function initStories() {
        var track = document.getElementById('storiesTrack');
        var prev  = document.getElementById('storiesPrev');
        var next  = document.getElementById('storiesNext');
        if (!track || !prev || !next) { return; }

        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function update() {
            var maxScroll = track.scrollWidth - track.clientWidth;
            prev.hidden = track.scrollLeft <= 1;
            next.hidden = track.scrollLeft >= maxScroll - 1;
        }

        function page(direction) {
            track.scrollBy({
                left: direction * track.clientWidth * 0.8,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        }

        prev.addEventListener('click', function () { page(-1); });
        next.addEventListener('click', function () { page(1); });
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    /* ======================================================================
       5. CREATE POST (modal, attachments, preview lightbox, discard confirm)
       Browser side checks sirf UX ke liye hain. Asli validation server par.
       ====================================================================== */
    function initCreatePost() {
        var form    = document.getElementById('createPostForm');
        var modalEl = document.getElementById('createPostModal');
        if (!form || !modalEl) { return; }

        var MAX_FILES    = 10;
        var MAX_MB       = { image: 5, video: 50, doc: 10 };
        var DOC_EXT      = ['pdf'];
        var ACCEPT_MEDIA = 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm';
        var ACCEPT_DOCS  = '.pdf';
        // var DOC_EXT      = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
        // var ACCEPT_DOCS  = '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt';

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

        var items      = [];    // { file, kind: 'image' | 'video' | 'doc', ext, url (sirf image/video) }
        var discarding = false; // true = user ne "Discard" confirm kar diya
        var lbIndex    = -1;
        var lbBlobUrl  = null;  // lightbox me document ke liye temporary URL
        var lbOpener   = null;

        // ----- helpers -----
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

        // ----- files -----
        // Browser ka file input ek-ek file hatane nahi deta, isliye items se dobara bharte hain
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

        // ----- lightbox (attachment preview) -----
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

        // ----- feeling -----
        function setFeeling(value, label) {
            feelingInput.value = value;
            feelingText.textContent = label ? 'Feeling ' + label : '';
            feelingTag.hidden = !value;
            feelingBar.hidden = true;
        }

        feelingBtn.addEventListener('click', function () { feelingBar.hidden = !feelingBar.hidden; });

        feelingBar.addEventListener('click', function (e) {
            var chip = e.target.closest('[data-feeling]');
            if (chip) { setFeeling(chip.dataset.feeling, chip.dataset.label); }
        });

        feelingClear.addEventListener('click', function () { setFeeling('', ''); });

        // ----- check-in -----
        locationBtn.addEventListener('click', function () {
            locationRow.hidden = !locationRow.hidden;
            if (!locationRow.hidden) { locationIn.focus(); }
        });

        // ----- textarea auto-grow -----
        content.addEventListener('input', function () {
            content.style.height = 'auto';
            content.style.height = Math.min(content.scrollHeight, 320) + 'px';
            refresh();
        });

        // ----- discard confirm (modal band karne se pehle) -----
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

        // ----- reset (modal poori tarah band hone ke baad) -----
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

        // ----- submit -----
        // TEMP (design phase): save route banne par ye handler hata kar normal POST chalega.
        // Post save hone ke baad `discarding = true` set karke modal hide karna, taaki confirm na puche.
        // form.addEventListener('submit', function (e) {
        //     e.preventDefault();
        //     window.showToastMessage('info', 'Posting will be enabled in the next phase.');
        // });

        // ----- AJAX Submit for Create Post -----
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Check if form is empty
            if (!isDirty()) {
                showError(['Please add some text or photos to your post.']);
                return;
            }

            var formData = new FormData(form);
            // Append files from our custom items array (kyunki browser input direct sync nahi hota)
            formData.delete('media[]'); 
            items.forEach(function (it) {
                formData.append('media[]', it.file);
            });

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

            // IMPORTANT: Get URL from a data attribute or hardcode it for now
            var submitUrl = '/post/store'; // Make sure this matches your web.php route

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (response.status === 419 || response.status === 401) {
                    window.location.reload();
                    throw new Error('auth');
                }
                return response.json().then(data => ({ status: response.status, body: data }));
            })
            .then(res => {
                if (res.status === 200) {
                    submitBtn.innerHTML = 'Post';
                    submitBtn.disabled = false;

                    // Success! Modal close karo
                    discarding = true; // prevent discard confirmation
                    bootstrap.Modal.getInstance(modalEl).hide();
                    
                    // Naya HTML feed me sabse upar prepend karo
                    var feedList = document.getElementById('feedList');
                    if (feedList && res.body.html) {
                        feedList.insertAdjacentHTML('afterbegin', res.body.html);
                    }
                    
                    window.showToastMessage('success', res.body.message);
                } else {
                    // Validation or Server Error
                    showError([res.body.message || 'Failed to create post.']);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Post';
                }
            })
            .catch(err => {
                if (err.message !== 'auth') {
                    showError(['An error occurred. Please check your connection.']);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Post';
                }
            });
        });

        refresh();
    }

    /* ======================================================================
       6. FEED (infinite scroll + post actions + follow buttons)
       ====================================================================== */
    function initFeed() {
        // Follow buttons (sidebar). TEMP: functionality phase me server request judegi.
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-follow]')) {
                window.showToastMessage('info', 'Follow will be enabled in the next phase.');
            }
        });

        var list = document.getElementById('feedList');
        if (!list) { return; }

        var feedUrl   = list.dataset.url;
        var loginUrl  = list.dataset.loginUrl;
        var skeletons = document.getElementById('feedSkeletons');
        var message   = document.getElementById('feedMsg');
        var sentinel  = document.getElementById('feedSentinel');
        var template  = document.getElementById('feedSkeleton');

        var cursor  = 0;
        var loading = false;
        var done    = false;
        var observer;

        function setMessage(text, withRetry) {
            message.textContent = '';
            var p = document.createElement('p');
            p.textContent = text;
            message.appendChild(p);

            if (withRetry) {
                var retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'tc-btn-primary';
                retry.textContent = 'Try again';
                retry.addEventListener('click', load);
                message.appendChild(retry);
            }
            message.hidden = false;
        }

        function clearMessage() {
            message.hidden = true;
            message.textContent = '';
        }

        function showSkeletons(count) {
            for (var i = 0; i < count; i++) {
                skeletons.appendChild(template.content.cloneNode(true));
            }
        }

        // Lambe text par "See more" button dikhana
        function enhance() {
            list.querySelectorAll('.tc-post__text:not([data-checked])').forEach(function (text) {
                text.dataset.checked = '1';
                if (text.scrollHeight > text.clientHeight + 1) {
                    var more = text.parentNode.querySelector('.tc-post__more');
                    if (more) { more.hidden = false; }
                }
            });
        }

        function load() {
            if (loading || done) { return; }
            loading = true;
            clearMessage();
            showSkeletons(list.children.length ? 1 : 2);

            fetch(feedUrl + '?cursor=' + encodeURIComponent(cursor), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (response) {
                if (response.status === 401 || response.status === 419) {
                    window.location.href = loginUrl; // session khatam
                    throw new Error('auth');
                }
                if (!response.ok) { throw new Error('http'); }
                return response.json();
            }).then(function (data) {
                skeletons.textContent = '';
                // data.html server par Blade se render hua hai (saara user content escaped)
                if (data.html) { list.insertAdjacentHTML('beforeend', data.html); }
                enhance();

                if (data.next_cursor === null) {
                    done = true;
                    observer.disconnect();
                    setMessage(list.children.length
                        ? 'You\u2019re all caught up.'
                        : 'No posts yet. Follow people to see their updates here.', false);
                } else {
                    cursor = data.next_cursor;
                    observer.unobserve(sentinel); // sentinel abhi screen par ho to dobara check
                    observer.observe(sentinel);
                }
            }).catch(function (err) {
                skeletons.textContent = '';
                if (err.message !== 'auth') {
                    setMessage('Could not load posts. Please check your connection.', true);
                }
            }).finally(function () {
                loading = false;
            });
        }

        observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) { load(); }
        }, { rootMargin: '400px 0px' });
        observer.observe(sentinel);

        // Feed Media Preview Logic
        // list.addEventListener('click', function (e) {
        //     var mediaItem = e.target.closest('.tc-media__item img, .tc-media__item video');
        //     if (mediaItem) {
        //         var post = mediaItem.closest('.tc-post');
        //         var captionEl = post.querySelector('.tc-post__text');
        //         var captionText = captionEl ? captionEl.textContent.trim() : '';

        //         var container = document.getElementById('feedMediaContainer');
        //         var captionBox = document.getElementById('feedMediaCaption');
                
        //         container.innerHTML = ''; // Clear old media
        //         captionBox.textContent = captionText;
        //         captionBox.hidden = captionText === '';

        //         if (mediaItem.tagName === 'IMG') {
        //             var img = document.createElement('img');
        //             img.src = mediaItem.src;
        //             img.className = 'img-fluid rounded shadow';
        //             img.style.maxHeight = '80vh';
        //             container.appendChild(img);
        //         } else if (mediaItem.tagName === 'VIDEO') {
        //             var video = document.createElement('video');
        //             video.src = mediaItem.src;
        //             video.controls = true;
        //             video.autoplay = true;
        //             video.className = 'img-fluid rounded shadow';
        //             video.style.maxHeight = '80vh';
        //             container.appendChild(video);
        //         }

        //         var modal = new bootstrap.Modal(document.getElementById('feedMediaModal'));
        //         modal.show();

        //         // Stop video playing when modal closes
        //         document.getElementById('feedMediaModal').addEventListener('hidden.bs.modal', function () {
        //             container.innerHTML = ''; 
        //         }, { once: true });
        //     }
        // });

        // Post actions (event delegation)
        list.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-action]');
            if (!btn) { return; }
            var post = btn.closest('.tc-post');
            if (!post) { return; }

            switch (btn.dataset.action) {
                case 'like': {
                    // TEMP: sirf UI. Functionality phase me yahan server request judegi.
                    var liked = btn.classList.toggle('is-liked');
                    btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
                    btn.querySelector('i').className = (liked ? 'fas' : 'far') + ' fa-heart';
                    var counter = post.querySelector('.js-like-count');
                    if (counter) {
                        var count = Math.max(0, (parseInt(counter.dataset.count, 10) || 0) + (liked ? 1 : -1));
                        counter.dataset.count = count;
                        counter.textContent = count;
                    }
                    break;
                }
                case 'comment': {
                    var box = post.querySelector('.tc-post__comments');
                    box.hidden = !box.hidden;
                    if (!box.hidden) {
                        var input = box.querySelector('input');
                        if (input) { input.focus(); }
                    }
                    break;
                }
                case 'more': {
                    var text = btn.previousElementSibling;
                    var expanded = text.classList.toggle('is-expanded');
                    btn.textContent = expanded ? 'See less' : 'See more';
                    break;
                }
                case 'share':
                case 'save':
                case 'report':
                    window.showToastMessage('info', 'This will be enabled in the next phase.');
                    break;
            }
        });

        list.addEventListener('submit', function (e) {
            if (e.target.closest('[data-comment-form]')) {
                e.preventDefault(); // TEMP: comment save route functionality phase me
                window.showToastMessage('info', 'Commenting will be enabled in the next phase.');
            }
        });
    }

    /* ======================================================================
       INIT: Common sabse pehle (auth.js isi ke helpers use karta hai)
       ====================================================================== */
    initCommon();
    initTheme();
    initSession();
    initStories();
    initCreatePost();
    initFeed();
})();