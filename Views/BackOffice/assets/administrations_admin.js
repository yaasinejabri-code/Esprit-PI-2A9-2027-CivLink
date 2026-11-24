(function () {
    document.querySelectorAll('.civ-adm form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    var inp = document.getElementById('admAdresse');
    var box = document.getElementById('admSuggestions');
    var lat = document.getElementById('admLat');
    var lng = document.getElementById('admLng');
    if (!inp || !box) return;

    var timer, controller, sequence = 0;

    function close() {
        box.replaceChildren();
    }

    inp.addEventListener('input', function () {
        clearTimeout(timer);
        if (controller) controller.abort();
        sequence++;
        close();
        var q = inp.value.trim(), id = sequence;
        if (q.length < 3) return;
        timer = setTimeout(async function () {
            controller = new AbortController();
            try {
                var res = await fetch('https://photon.komoot.io/api/?q=' + encodeURIComponent(q) + '&countrycode=TN&limit=6&lang=fr', { signal: controller.signal });
                if (!res.ok) throw Error('fetch');
                var data = await res.json();
                if (id !== sequence) return;
                (data.features || []).forEach(function (f) {
                    var p = f.properties || {}, coords = f.geometry && f.geometry.coordinates;
                    if (!coords || (p.countrycode && p.countrycode.toUpperCase() !== 'TN')) return;
                    var label = Array.from(new Set([p.name, p.street, p.city, p.state].filter(Boolean))).join(', ');
                    if (!label) return;
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = '📍 ' + label;
                    b.addEventListener('click', function () {
                        inp.value = label;
                        lat.value = coords[1];
                        lng.value = coords[0];
                        sequence++;
                        close();
                    });
                    box.appendChild(b);
                });
            } catch (e) {
                if (e.name !== 'AbortError') close();
            }
        }, 400);
    });

    document.addEventListener('click', function (e) {
        if (!inp.parentElement.parentElement.contains(e.target)) close();
    });
})();
