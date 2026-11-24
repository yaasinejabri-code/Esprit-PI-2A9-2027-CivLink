/* Démonstration locale uniquement : aucun paiement réel. */
(function () {
    'use strict';

    const sample = [
        {
            id: 'STEG-2026-00412',
            provider: 'STEG',
            name: 'Électricité et gaz',
            period: 'Septembre – Octobre 2026',
            due: '2026-12-05',
            amount: 128.450,
            paid: false
        },
        {
            id: 'SONEDE-2026-00827',
            provider: 'SONEDE',
            name: 'Eau potable',
            period: 'Août – Octobre 2026',
            due: '2026-11-30',
            amount: 47.820,
            paid: false
        },
        {
            id: 'STEG-2026-00365',
            provider: 'STEG',
            name: 'Électricité et gaz',
            period: 'Juillet – Août 2026',
            due: '2026-10-10',
            amount: 96.300,
            paid: true,
            paidOn: '2026-10-07'
        }
    ];

    // Stockage indépendant pour chaque utilisateur
    const user = document.querySelector('.factures-page')
        ?.dataset.user || 'guest';

    const page = document.querySelector('.factures-page');
    const isServer = page?.dataset.authenticated === '1';
    const csrf = page?.dataset.csrf || '';
    const endpoint = 'factures_api.php';
    const safe = v => String(v ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    const key = 'civlink-factures-demo-v2:' + user;

    let saved = {};

    try {
        saved = JSON.parse(localStorage.getItem(key) || '{}') || {};
    } catch (_) {
        saved = {};
    }

    let invoices = isServer ? [] : sample.map(x => ({
        ...x,
        ...(saved[x.id] && saved[x.id].paid
            ? {
                paid: true,
                paidOn: saved[x.id].paidOn
            }
            : {})
    }));

    const el = id => document.getElementById(id);

    const fmt = amount => amount.toLocaleString('fr-TN', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    }) + ' DT';

    const date = s => new Date(s + 'T12:00:00')
        .toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });

    let selected = 'all';
    let query = '';
    let active = null;
    let success = false;

    // Affichage des factures
    function render() {

        const unpaid = invoices.filter(x => !x.paid);
        const paid = invoices.filter(x => x.paid);

        el('statPending').textContent = unpaid.length;

        el('statAmount').textContent = fmt(
            unpaid.reduce((s, x) => s + x.amount, 0)
        );

        el('statPaid').textContent = paid.length;

        const visible = invoices.filter(x =>
            (selected === 'all' || x.provider === selected) &&
            (x.id + ' ' + x.name + ' ' + x.period)
                .toLowerCase().includes(query)
        );

        el('facCount').textContent =
            visible.length + ' facture' +
            (visible.length !== 1 ? 's' : '') +
            (isServer ? '' : ' de démonstration');

        el('facEmpty').hidden = visible.length !== 0;

        el('facList').innerHTML = visible.map(x => `
            <article class="fac-invoice">

                <div class="fac-provider ${safe(x.provider.toLowerCase())}">
                    <i class="fas fa-${x.provider === 'STEG'
                        ? 'bolt' : 'tint'}"></i>
                </div>

                <div class="fac-invoice-info">
                    <strong>
                        ${safe(x.provider)} · ${safe(x.name)}
                    </strong>

                    <p>
                        Réf. ${safe(x.id)}
                        <span>•</span>
                        ${safe(x.period)}
                        <span>•</span>
                        Échéance : ${date(x.due)}
                    </p>
                </div>

                <div class="fac-invoice-amount">
                    <strong>${fmt(x.amount)}</strong>

                    <span class="fac-tag ${x.paid
                        ? 'paid' : 'pending'}">
                        ${x.paid
                            ? 'Réglée (démo)'
                            : 'En attente'}
                    </span>
                </div>

                <div class="fac-invoice-action">
                    <button type="button"
                            data-id="${safe(x.id)}"
                            class="${x.paid ? 'is-paid' : ''}">

                        ${x.paid
                            ? 'Voir le reçu <i class="fas fa-file-alt"></i>'
                            : 'Simuler le paiement <i class="fas fa-arrow-right"></i>'}

                    </button>
                </div>

            </article>
        `).join('');

        // Historique des paiements
        el('facHistory').innerHTML = paid.length
            ? paid.slice().reverse().map(x => `
                <div class="fac-history-item">

                    <div>
                        <strong>
                            ${safe(x.provider)} · ${safe(x.name)}
                        </strong>

                        <small>
                            ${safe(x.id)} ·
                            ${date(x.paidOn || x.due)} ·
                            paiement simulé
                        </small>
                    </div>

                    <span>+ ${fmt(x.amount)}</span>

                </div>
            `).join('')
            : '<p class="fac-receipt">Aucun paiement simulé pour le moment.</p>';
    }

    // Ouverture de la fenêtre de paiement
    function open(id) {

        active = invoices.find(x => x.id === id);

        if (!active) return;

        success = active.paid;

        drawModal();

        el('facModal').hidden = false;

        el('facClose').focus();

        document.body.style.overflow = 'hidden';
    }

    function drawModal() {

        el('facModalTitle').textContent = success
            ? 'Reçu de démonstration'
            : 'Confirmer la simulation';

        el('facModalDetail').innerHTML =

            (success ? `
                <div class="fac-modal-success">
                    <i class="fas fa-check-circle"></i>

                    <p>
                        Paiement fictif confirmé.
                        Aucun montant n’a été débité.
                    </p>
                </div>
            ` : '') +

            `<div class="fac-modal-lines">

                <div>
                    <span>Fournisseur</span>
                    <strong>${safe(active.provider)}</strong>
                </div>

                <div>
                    <span>Référence</span>
                    <strong>${safe(active.id)}</strong>
                </div>

                <div>
                    <span>Période</span>
                    <strong>${safe(active.period)}</strong>
                </div>

                <div>
                    <span>Montant</span>
                    <strong>${fmt(active.amount)}</strong>
                </div>

                ${success ? `
                    <div>
                        <span>Date de simulation</span>
                        <strong>
                            ${date(active.paidOn || active.due)}
                        </strong>
                    </div>
                ` : ''}

            </div>`;

        el('facConfirm').hidden = success;

        el('facCancel').textContent = success
            ? 'Fermer'
            : 'Annuler';
    }

    function close() {

        el('facModal').hidden = true;

        document.body.style.overflow = '';

        active = null;
    }

    // Filtres STEG et SONEDE
    document.querySelectorAll('.fac-tabs button')
        .forEach(b => b.addEventListener('click', () => {

            selected = b.dataset.provider;

            document.querySelectorAll('.fac-tabs button')
                .forEach(t => {

                    const a = t === b;

                    t.classList.toggle('is-active', a);

                    t.setAttribute('aria-pressed', String(a));
                });

            render();
        }));

    // Recherche de factures
    el('facSearch').addEventListener('input', e => {

        query = e.target.value.trim().toLowerCase();

        render();
    });

    // Sélection d'une facture
    el('facList').addEventListener('click', e => {

        const b = e.target.closest('button[data-id]');

        if (b) open(b.dataset.id);
    });

    // Confirmation du paiement simulé
    el('facConfirm').addEventListener('click', async () => {
        if (!active || active.paid) return;
        const button = el('facConfirm');
        button.disabled = true;
        try {
            if (isServer) {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({action:'pay', id:active.internalId, csrf})
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'Paiement impossible.');
                active.paidOn = result.paidOn;
            } else {
                active.paidOn = new Date().toISOString().slice(0, 10);
                saved[active.id] = {paid:true, paidOn:active.paidOn};
                try {localStorage.setItem(key, JSON.stringify(saved));} catch (_) {}
            }
            active.paid = true;
            success = true;
            drawModal();
            render();
        } catch (error) {
            alert(error.message || 'Une erreur est survenue.');
        } finally {button.disabled = false;}
    });

    // Fermeture de la fenêtre
    el('facClose').addEventListener('click', close);

    el('facCancel').addEventListener('click', close);

    el('facModal').addEventListener('click', e => {

        if (e.target.dataset.close) close();
    });

    document.addEventListener('keydown', e => {

        if (e.key === 'Escape' && !el('facModal').hidden) {
            close();
        }
    });

    // Réinitialisation de la simulation
    const resetButton = el('facReset');

    if (resetButton) {

        resetButton.addEventListener('click', () => {

            if (!window.confirm(
                'Réinitialiser les factures de démonstration ?'
            )) return;

            saved = {};

            try {
                localStorage.removeItem(key);
            } catch (_) {}

            invoices.splice(
                0,
                invoices.length,
                ...sample.map(x => ({ ...x }))
            );

            selected = 'all';
            query = '';

            el('facSearch').value = '';

            document.querySelectorAll('.fac-tabs button')
                .forEach(b => {

                    const activeTab =
                        b.dataset.provider === 'all';

                    b.classList.toggle('is-active', activeTab);

                    b.setAttribute(
                        'aria-pressed',
                        String(activeTab)
                    );
                });

            close();

            render();
        });
    }

    async function loadServerInvoices() {
        try {
            const response = await fetch(endpoint, {credentials: 'same-origin', cache: 'no-store'});
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || 'Chargement impossible.');
            invoices = result.invoices || [];
            render();
        } catch (error) {
            el('facCount').textContent = 'Erreur de chargement';
            el('facList').textContent = error.message || 'Impossible de récupérer les factures.';
        }
    }
    if (isServer) loadServerInvoices();
    else render();

})();