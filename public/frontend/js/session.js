(function () {
    'use strict';

    var script = document.currentScript;
    if (!script) { return; }

    var keepActiveUrl = script.dataset.keepActiveUrl;
    var logoutUrl     = script.dataset.logoutUrl;
    var loginUrl      = script.dataset.loginUrl;
    var isRemembered  = script.dataset.remembered === '1';
    var maxIdleMs     = (parseInt(script.dataset.idleSeconds, 10) || 300) * 1000;

    var lastActivity  = Date.now();
    var activeSinceBeat = false;
    var loggingOut    = false;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

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

    // Auto logout: sirf jab "Remember me" OFF ho
    if (!isRemembered) {
        setInterval(function () {
            if (Date.now() - lastActivity >= maxIdleMs) {
                logout('inactivity');
            }
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
})();