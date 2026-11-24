<footer class="civlink-footer">

    <!-- Vague décorative en haut à droite -->
    <svg class="footer-swoosh" viewBox="0 0 700 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M0 90 C 220 84, 480 50, 700 0 L700 90 Z" fill="#173B63"/>
        <path d="M0 88 C 220 82, 480 48, 700 -2" fill="none" stroke="#F28C28" stroke-width="5"/>
    </svg>

    <div class="container-xl">
        <div class="row g-5">

            <div class="col-lg-5">
                <a href="<?= $base ?? '/CivLink/Views/FrontOffice' ?>/index.php" class="footer-wordmark">CIV<span>LINK</span></a>
                <p class="footer-about">
                    Services publics en ligne : démarches, signalements et factures
                    de votre commune, réunis sur une seule plateforme, 24h/24 et 7j/7.
                </p>
            </div>

            <div class="col-sm-6 col-lg-4">
                <h5 class="footer-title">Liens rapides</h5>
                <ul class="footer-links">
                    <li><a href="<?= $base ?? '/CivLink/Views/FrontOffice' ?>/index.php">Accueil</a></li>
                    <li><a href="<?= $base ?? '/CivLink/Views/FrontOffice' ?>/pages/administrations.php">Administrations</a></li>
                    <li><a href="#">Démarches</a></li>
                    <li><a href="#">Signalements</a></li>
                    <li><a href="<?= $base ?>/pages/factures.php">Factures</a></li>
                </ul>
            </div>

            <div class="col-sm-6 col-lg-3">
                <h5 class="footer-title">Contact</h5>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i>Tunis, Tunisie</li>
                    <li><i class="fas fa-phone-alt"></i>+216 00 000 000</li>
                    <li><i class="fas fa-envelope"></i>contact@example.com</li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <span>© <?= date('Y') ?> CivLink — Tous droits réservés.</span>
            <span>Le paiement des factures est une simulation.</span>
        </div>
    </div>
</footer>