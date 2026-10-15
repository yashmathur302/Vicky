(function () {
    'use strict';

    var root = document.documentElement;
    var STORAGE_KEY = 'edu_theme'; // theme-init.js me bhi yahi key hai

    // ---------- Night mode toggle ----------
    var toggle = document.getElementById('themeToggle');

    function currentTheme() {
        return root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncToggle() {
        if (toggle) {
            toggle.setAttribute('aria-checked', currentTheme() === 'dark' ? 'true' : 'false');
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = currentTheme() === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            try { localStorage.setItem(STORAGE_KEY, next); } catch (e) { /* storage blocked */ }
            syncToggle();
        });
        syncToggle();
    }

    // ---------- Follow buttons (design phase: sirf toast) ----------
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-follow]')) {
            window.showToastMessage('info', 'Follow will be enabled in the next phase.');
        }
    });

    // ---------- Feed (sirf home page par) ----------
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
                setMessage(list.children.length ? 'You\u2019re all caught up.' : 'No posts yet. Follow people to see their updates here.', false);
            } else {
                cursor = data.next_cursor;
                // Sentinel abhi bhi screen par ho to dobara check karao
                observer.unobserve(sentinel);
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

    // ---------- Post actions (event delegation) ----------
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) { return; }
        var post = btn.closest('.tc-post');
        if (!post) { return; }

        switch (btn.dataset.action) {
            case 'like': {
                // Design phase: sirf UI. Functionality phase me yahan server request judegi.
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
})();