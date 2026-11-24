<?php

require_once __DIR__ . '/../../../Models/CivData.php';

$pageTitle   = "Administrations - CivLink";
$currentPage = "administrations";

$base   = "/CivLink/Views/FrontOffice";
$assets = $base . "/assets";

$categories = CivData::categories();
$sites      = CivData::sitesForMap();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
    .adm-header { position: relative; z-index: 20; overflow: visible; }
    .adm-locate { position: relative; z-index: 10; overflow: visible; }
    #locForm { position: relative; z-index: 10; overflow: visible; }
    .adm-list { position: relative; z-index: 0; isolation: isolate; }
    .adm-results .adm-search { z-index: 0; }
    #locSuggestions { position: absolute; top: calc(100% + 6px); left: 0; right: 0; width: 100%; z-index: 100; background: var(--adm-surface); color: var(--adm-text); border: 1px solid var(--adm-border); border-radius: 12px; box-shadow: 0 12px 30px #0003; overflow: auto; max-height: 300px; }
    #locSuggestions:empty { display: none; }
    #locSuggestions button { display: block; width: 100%; text-align: left; padding: 12px 18px; background: var(--adm-surface); color: var(--adm-text); border: 0; border-radius: 0; font-weight: 600; }
    #locSuggestions button:hover, #locSuggestions button:focus-visible { background: var(--adm-orange); color: white; }
    .adm-radius select { background: var(--adm-surface); color: var(--adm-text); }
    .adm-radius select option { background: var(--adm-surface); color: var(--adm-text); }
    [data-theme="dark"] .adm-radius select { color-scheme: dark; }
    #admMap { overflow: hidden; }
    .adm-services { margin-top: 8px; font-size: 12px; line-height: 1.5; color: var(--adm-text); }
    .adm-services strong { font-weight: 700; }
    .adm-pop-services { margin: 9px 0; padding: 8px 0; border-top: 1px solid var(--adm-border); }
    .adm-pop-services ul { margin: 5px 0 0; padding-left: 20px; }
    .adm-pop-services li { margin-bottom: 4px; }
    .adm-map-message { position: absolute; bottom: 25px; left: 12px; right: 12px; z-index: 500; padding: 10px; background: var(--adm-surface); color: var(--adm-text); border-radius: 8px; pointer-events: none; }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <!-- ===== EN-TÊTE ===== -->
    <section class="adm-header">
        <div class="container-xl">
            <nav aria-label="Fil d’Ariane" class="adm-crumbs">
                <a href="<?= $base ?>/index.php">Accueil</a>
                <i class="fas fa-chevron-right"></i>
                <span>Administrations</span>
            </nav>
            <h1 class="adm-title">Administrations près de chez vous</h1>
            <p class="adm-lead">Indiquez votre position pour trouver les administrations proches, puis consultez leurs démarches.</p>

            <div class="adm-locate">
                <form class="civlink-search-bar adm-search" id="locForm" role="search">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" id="locQuery" placeholder="Votre adresse, quartier ou ville (ex. : Ariana)"
                           aria-label="Votre position" autocomplete="off">
                    <div id="locSuggestions" class="loc-suggestions"></div>
                </form>
                <button type="button" class="adm-go" id="locGo">Rechercher</button>
                <button type="button" class="adm-geo" id="locGeo"><i class="fas fa-crosshairs"></i>Ma position</button>
                <label class="adm-radius">Rayon
                    <select id="locRadius" aria-label="Rayon de recherche">
                        <option value="2">2 km</option>
                        <option value="5" selected>5 km</option>
                        <option value="10">10 km</option>
                        <option value="25">25 km</option>
                        <option value="0">Tout afficher</option>
                    </select>
                </label>
            </div>
            <p class="adm-status" id="locStatus" aria-live="polite">Astuce : utilisez « Ma position » ou saisissez une adresse pour trier par distance.</p>

            <div class="adm-filters" role="group" aria-label="Filtrer par catégorie">
                <?php foreach ($categories as $key => $label): ?>
                    <button type="button" class="adm-chip<?= $key === 'all' ? ' is-active' : '' ?>"
                            data-cat="<?= h($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>">
                        <?= h($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== LISTE + CARTE (vide pour l'instant) ===== -->
    <section class="adm-list">
        <div class="container-xl">
            <div class="adm-map-layout">
                <aside class="adm-results">
                    <form class="civlink-search-bar adm-search" role="search" onsubmit="return false">
                        <i class="fas fa-search"></i>
                        <input type="text" id="admQuery" placeholder="Filtrer par nom ou service…"
                               aria-label="Filtrer les administrations" autocomplete="off">
                    </form>
                    <p class="adm-count" id="admCount" aria-live="polite"></p>
                    <ul class="adm-results-list" id="admList"></ul>
                </aside>
                <div class="adm-map" id="admMap" role="application" aria-label="Carte des administrations"></div>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php require_once __DIR__ . '/../includes/scripts.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        var SITES = <?= json_encode($sites, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        var DEM_URL = <?= json_encode($base . '/pages/demarches.php') ?>;

        var els = {
            list: document.getElementById('admList'), count: document.getElementById('admCount'),
            status: document.getElementById('locStatus'), query: document.getElementById('admQuery'),
            loc: document.getElementById('locQuery'), radius: document.getElementById('locRadius'),
            chips: document.querySelectorAll('.adm-chip')
        };
        var cat = 'all', user = null;
        var map, pins, positionLayer, markerById = {};
        var mapEl = document.getElementById('admMap');
        function mapMessage(message) {
            var box = mapEl.querySelector('.adm-map-message');
            if (!box) { box = document.createElement('div'); box.className = 'adm-map-message'; mapEl.appendChild(box); }
            box.textContent = message;
        }
        if (window.L) {
            map = L.map(mapEl).setView([36.8065, 10.1815], 12);
            var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            tiles.on('tileerror', function () { mapMessage('Certaines tuiles de la carte sont indisponibles. Vérifiez votre connexion.'); });
            tiles.on('load', function () { var b = mapEl.querySelector('.adm-map-message'); if (b) b.remove(); });
            pins = L.layerGroup().addTo(map);
            positionLayer = L.layerGroup().addTo(map);
            window.addEventListener('load', function () { map.invalidateSize(); });
            if (window.ResizeObserver) new ResizeObserver(function () { map.invalidateSize(); }).observe(mapEl);
        } else { mapEl.textContent = 'Carte indisponible : vérifiez votre connexion Internet (Leaflet).'; }
        function updateMap(rows) {
            if (!map) return;
            pins.clearLayers(); positionLayer.clearLayers(); markerById = {};
            var bounds = [];
            rows.forEach(function (s) {
                if (!Number.isFinite(Number(s.lat)) || !Number.isFinite(Number(s.lng))) return;
                var coords = [Number(s.lat), Number(s.lng)];
                var icon = L.divIcon({className:'adm-pin-wrap',html:'<span class="adm-pin"><i class="fas '+esc(s.icone)+'"></i></span>',iconSize:[36,36],iconAnchor:[18,36],popupAnchor:[0,-30]});
                var marker = L.marker(coords,{icon:icon}).addTo(pins);
                var servicesHtml = (s.services && s.services.length)
                    ? '<div class="adm-pop-services"><strong>Services disponibles</strong><ul>' +
                      s.services.map(function (svc) {
                          return '<li><strong>' + esc(svc.nom) + '</strong>' +
                              (svc.description ? '<br><small>' + esc(svc.description) + '</small>' : '') +
                              '</li>';
                      }).join('') + '</ul></div>'
                    : '';
                marker.bindPopup('<div class="adm-pop"><strong>'+esc(s.nom)+'</strong><span class="adm-pop-cat">'+esc(s.catLabel)+'</span><p>'+esc(s.adresse)+'</p><p>'+esc(s.horaires || '')+'</p>'+servicesHtml+'<a class="adm-pop-btn" href="'+esc(link(s))+'">Consulter les démarches</a></div>');
                markerById[s.id] = marker; bounds.push(coords);
            });
            if (user) {
                var here = [user.lat,user.lng]; bounds.push(here);
                L.circleMarker(here,{radius:8,color:'#fff',weight:3,fillColor:'#2878d0',fillOpacity:1}).addTo(positionLayer).bindPopup('Votre position');
                var r = Number(els.radius.value);
                if (r > 0) L.circle(here,{radius:r*1000,color:'#2878d0',weight:1,fillOpacity:.06}).addTo(positionLayer);
            }
            if (bounds.length) map.fitBounds(bounds,{padding:[35,35],maxZoom:15});
        }

        function esc(s) {
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        function km(a, b) {
            var R = 6371, rad = Math.PI / 180;
            var dLat = (b.lat - a.lat) * rad, dLng = (b.lng - a.lng) * rad;
            var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(a.lat * rad) * Math.cos(b.lat * rad) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return 2 * R * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
        }
        function fmt(d) { return d < 1 ? Math.round(d * 1000) + ' m' : d.toFixed(1).replace('.', ',') + ' km'; }
        function link(s) { return DEM_URL + '?admin=' + encodeURIComponent(s.type) + '&site=' + encodeURIComponent(s.id) + '#documents'; }

        // ---------- Filtrage + affichage ----------
        function render() {
            var q = els.query.value.trim().toLowerCase(), r = parseFloat(els.radius.value) || 0;
            var rows = SITES.map(function (s) {
                s.dist = user ? km(user, s) : null; return s;
            }).filter(function (s) {
                var serviceText = (s.services || []).map(function (svc) {
                    return svc.nom + ' ' + (svc.description || '');
                }).join(' ');
                var text = (s.nom + ' ' + s.typeNom + ' ' + s.desc + ' ' + s.catLabel + ' ' + serviceText).toLowerCase();
                return (cat === 'all' || s.cat === cat) && (!q || text.indexOf(q) !== -1) &&
                       (!user || !r || s.dist <= r);
            });
            if (user) rows.sort(function (a, b) { return a.dist - b.dist; });

            els.list.innerHTML = '';
            rows.forEach(function (s) {
                var li = document.createElement('li');
                li.className = 'adm-res'; li.dataset.id = s.id;
                li.innerHTML = '<button type="button"><span class="adm-icon"><i class="fas ' + esc(s.icone) + '"></i></span>' +
                    '<span class="adm-res-txt"><strong>' + esc(s.nom) + '</strong><small>' + esc(s.catLabel) + ' · ' + esc(s.adresse) + '</small>' +
                    ((s.services || []).length ? '<span class="adm-services"><strong>Services :</strong> ' +
                        s.services.map(function (svc) { return esc(svc.nom); }).join(' · ') + '</span>' : '') +
                    '</span>' +
                    (s.dist != null ? '<span class="adm-res-dist">' + fmt(s.dist) + '</span>' : '') + '</button>';
                li.firstChild.addEventListener('click', function () { var marker = markerById[s.id]; if (map && marker) { map.setView(marker.getLatLng(), 16); marker.openPopup(); } else { window.location.href = link(s); } });
                els.list.appendChild(li);
            });

            if (!rows.length) {
                els.list.innerHTML = '<li class="adm-res-empty"><i class="far fa-folder-open"></i>Aucune administration' +
                    (user && r ? ' dans un rayon de ' + r + ' km. Essayez un rayon plus grand.' : ' trouvée avec ces filtres.') + '</li>';
            }
            els.count.textContent = rows.length + (rows.length > 1 ? ' administrations' : ' administration') +
                (user ? ' (triées par distance)' : '');
            updateMap(rows);
        }

        // ---------- Position de l'utilisateur ----------
        function setUser(lat, lng, label) {
            user = { lat: lat, lng: lng };
            els.status.textContent = label;
            render();
        }

        function geocode() {
            var q = els.loc.value.trim();
            if (!q) { els.status.textContent = 'Saisissez une adresse ou une ville, ou utilisez « Ma position ».'; return; }
            els.status.textContent = 'Recherche de « ' + q + ' »…';
            fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=tn&accept-language=fr&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.length) { els.status.textContent = 'Adresse introuvable. Essayez un quartier ou une ville.'; return; }
                    setUser(parseFloat(res[0].lat), parseFloat(res[0].lon), 'Position : ' + res[0].display_name.split(',').slice(0, 2).join(','));
                })
                .catch(function () { els.status.textContent = 'Recherche indisponible pour le moment.'; });
        }

        function locate() {
            if (!navigator.geolocation) { els.status.textContent = 'La géolocalisation n’est pas disponible sur ce navigateur.'; return; }
            els.status.textContent = 'Localisation en cours…';
            navigator.geolocation.getCurrentPosition(function (p) {
                setUser(p.coords.latitude, p.coords.longitude, 'Position détectée automatiquement.');
            }, function (e) {
                els.status.textContent = e.code === 1
                    ? 'Accès à la position refusé. Saisissez une adresse.'
                    : 'Position introuvable. Saisissez une adresse.';
            }, { enableHighAccuracy: true, timeout: 10000 });
        }

        // ---------- Suggestions de recherche ----------
        var suggestions = document.getElementById('locSuggestions'), timer, requestId = 0;
        function closeSuggestions() { clearTimeout(timer); requestId++; suggestions.replaceChildren(); }
        els.loc.addEventListener('input', function () {
            closeSuggestions();
            var q = els.loc.value.trim(), id = requestId;
            if (q.length < 3) return;
            timer = setTimeout(function () {
                fetch('https://photon.komoot.io/api/?q='+encodeURIComponent(q)+'&countrycode=TN&limit=6&lang=fr')
                .then(function (r) { if (!r.ok) throw Error('HTTP '+r.status); return r.json(); })
                .then(function (data) {
                    if (id !== requestId) return;
                    (data.features || []).forEach(function (place) {
                        var p = place.properties || {}, coords = place.geometry && place.geometry.coordinates;
                        if (!coords || (p.countrycode && p.countrycode.toUpperCase() !== 'TN')) return;
                        var label = Array.from(new Set([p.name,p.street,p.city,p.state].filter(Boolean))).join(', ');
                        if (!label) return;
                        var b = document.createElement('button'); b.type = 'button'; b.textContent = '📍 '+label;
                        b.addEventListener('click', function () { closeSuggestions(); els.loc.value = label; setUser(coords[1],coords[0],'Position : '+label); });
                        suggestions.appendChild(b);
                    });
                }).catch(function () { if (id === requestId) suggestions.replaceChildren(); });
            }, 500);
        });
        document.addEventListener('click', function (e) { if (!document.getElementById('locForm').contains(e.target)) closeSuggestions(); });
        els.loc.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSuggestions(); });
        // ---------- Événements ----------
        document.getElementById('locForm').addEventListener('submit', function (e) { e.preventDefault(); closeSuggestions(); geocode(); });
        document.getElementById('locGo').addEventListener('click', function () { closeSuggestions(); geocode(); });
        document.getElementById('locGeo').addEventListener('click', function () { closeSuggestions(); locate(); });
        els.radius.addEventListener('change', render);
        els.query.addEventListener('input', render);
        els.chips.forEach(function (c) {
            c.addEventListener('click', function () {
                cat = c.dataset.cat;
                els.chips.forEach(function (x) {
                    var on = x === c; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on);
                });
                render();
            });
        });

        render();
    })();
    </script>
</body>

</html>