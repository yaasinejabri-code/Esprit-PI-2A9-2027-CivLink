<?php
// Les donnees et le moteur de reponses restent ceux de la FAQ CIVLINK existante.
require_once __DIR__ . '/../../../Models/CivData.php';
$faqEntries = [];
foreach (CivData::types() as $faqType => $faqAdmin) {
    foreach ($faqAdmin['docs'] as $faqId => $faqDoc) {
        $faqEntries[] = [
            'title' => $faqDoc['titre'],
            'admin' => $faqAdmin['nom'],
            'description' => $faqDoc['desc'],
            'pieces' => $faqDoc['pieces'],
            'steps' => $faqDoc['etapes'],
            'delay' => $faqDoc['delai'],
            'cost' => $faqDoc['cout'],
            'url' => CivData::demarchesUrl($base, $faqType, null, $faqId) . '#etapes'
        ];
    }
}
?>
<link rel="stylesheet" href="<?= htmlspecialchars($assets, ENT_QUOTES, 'UTF-8') ?>/css/faq-widget.css">
<div class="civ-faq-widget" id="civFaqWidget">
    <button id="civFaqToggle" class="civ-faq-toggle" type="button"
            aria-label="Ouvrir l’assistant CIVLINK" aria-controls="civFaqWindow"
            aria-expanded="false" title="Assistant CIVLINK">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
    </button>
    <section id="civFaqWindow" class="civ-faq-window" role="dialog"
             aria-modal="false" aria-label="Assistant CIVLINK" hidden>
        <div class="civ-faq-header">
            <div class="civ-faq-brand"><i class="fas fa-robot" aria-hidden="true"></i></div>
            <div class="civ-faq-heading">
                <strong>Assistant CIVLINK</strong>
                <span>FAQ · Réponses locales</span>
            </div>
            <button id="cfaqReset" type="button" title="Nouvelle conversation" aria-label="Nouvelle conversation"><i class="fas fa-redo"></i></button>
            <button id="civFaqClose" type="button" title="Fermer" aria-label="Fermer l’assistant"><i class="fas fa-times"></i></button>
        </div>
        <div id="cfaqMessages" class="civ-faq-messages" role="log" aria-live="polite" aria-label="Conversation"></div>
        <div id="cfaqSuggestions" class="civ-faq-suggestions" aria-label="Questions fréquentes"></div>
        <form id="cfaqForm" class="civ-faq-form">
            <input id="cfaqInput" type="text" maxlength="300" required autocomplete="off"
                   aria-label="Votre question" placeholder="Écrivez votre question…">
            <button type="submit" aria-label="Envoyer"><i class="fas fa-paper-plane"></i></button>
        </form>
        <p class="civ-faq-disclaimer">Ne saisissez pas de données personnelles. Informations à vérifier auprès des services compétents.</p>
    </section>
</div>
<script type="application/json" id="cfaqData"><?= json_encode($faqEntries, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="<?= htmlspecialchars($assets, ENT_QUOTES, 'UTF-8') ?>/js/assistant.js"></script>
<script src="<?= htmlspecialchars($assets, ENT_QUOTES, 'UTF-8') ?>/js/faq-widget.js"></script>
