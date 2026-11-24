(function () {
    document.querySelectorAll('.civ-adm form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    function text(max, required) {
        return function (v) {
            v = v.trim();
            if (required && v === '') return 'Ce champ est obligatoire.';
            if (v.length > max) return 'Maximum ' + max + ' caractères.';
            return '';
        };
    }

    function coord(min, max) {
        return function (v) {
            v = v.trim();
            if (v === '') return 'Ce champ est obligatoire.';
            if (!/^-?\d+(\.\d+)?$/.test(v)) return 'Saisissez un nombre (ex. 36.8065).';
            var n = parseFloat(v);
            if (n < min || n > max) return 'Valeur entre ' + min + ' et ' + max + '.';
            return '';
        };
    }

    function choice(v) {
        return v === '' ? 'Faites un choix.' : '';
    }

    var rules = {
        admForm: {
            nom: text(160, true), typeAdministration: choice, adresse: text(220, true),
            horaires: text(120, false), telephone: text(40, false),
            latitude: coord(-90, 90), longitude: coord(-180, 180)
        },
        svcForm: {
            idAdministration: choice, nomService: text(120, true), description: text(2000, false)
        }
    };

    function showError(field, message) {
        var label = field.closest('label');
        var msg = label.querySelector('.field-error');
        field.classList.toggle('is-invalid', message !== '');
        field.setAttribute('aria-invalid', message !== '' ? 'true' : 'false');
        if (message === '') {
            if (msg) msg.remove();
            return;
        }
        if (!msg) {
            msg = document.createElement('span');
            msg.className = 'field-error';
            label.appendChild(msg);
        }
        msg.textContent = message;
    }

    Object.keys(rules).forEach(function (formId) {
        var form = document.getElementById(formId);
        if (!form) return;
        var formRules = rules[formId];
        function check(name) {
            var field = form.elements[name];
            var message = formRules[name](field.value);
            showError(field, message);
            return message === '';
        }
        Object.keys(formRules).forEach(function (name) {
            var field = form.elements[name];
            field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'input', function () {
                if (field.classList.contains('is-invalid')) check(name);
            });
            field.addEventListener('blur', function () { check(name); });
        });
        form.addEventListener('submit', function (e) {
            var firstInvalid = null;
            Object.keys(formRules).forEach(function (name) {
                if (!check(name) && !firstInvalid) firstInvalid = form.elements[name];
            });
            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
            }
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
                        [inp, lat, lng].forEach(function (f) { showError(f, ''); });
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
