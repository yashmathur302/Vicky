(function () {
    'use strict';

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

    function scrollByPage(direction) {
        track.scrollBy({
            left: direction * track.clientWidth * 0.8,
            behavior: reduceMotion ? 'auto' : 'smooth'
        });
    }

    prev.addEventListener('click', function () { scrollByPage(-1); });
    next.addEventListener('click', function () { scrollByPage(1); });
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
})();