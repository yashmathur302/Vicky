(function () {
    try {
        var saved = localStorage.getItem('edu_theme');
        if (saved === 'light' || saved === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', saved);
        }
    } catch (e) { /* storage blocked: default theme rahega */ }
})();