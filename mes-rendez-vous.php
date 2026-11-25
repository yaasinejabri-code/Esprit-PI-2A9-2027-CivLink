<?php
/**
 * Views/FrontOffice/pages/mes-rendez-vous.php
 * Liste des rendez-vous du citoyen connecté + annulation.
 * Style simple comme compte.php.
 */
require_once __DIR__ . '/../../../Models/Auth.php';
require_once __DIR__ . '/../../../Models/CivData.php';
authStart();

if (!isset($_SESSION['user'])) {
    header('Location: /CivLink/Views/FrontOffice/pages/authentification.php', true, 303);
    exit;
}

$user = $_SESSION['user'];
$base   = '/CivLink/Views/FrontOffice';
$assets = $base . '/assets';
$currentPage = 'rendez-vous';
$pageTitle = 'Mes rendez-vous - CivLink';

$types = CivData::types();
$error = '';

// Annulation d'un rendez-vous (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!authCheck()) {
        $error = 'Session expirée. Réessayez.';
    } else {
        $idRdv = (int) authPost('idRdv');
        try {
            $db = authDb();

            // Vérifier que le rendez-vous appartient bien à l'utilisateur connecté
            $stmt = $db->prepare(
                'SELECT idCreneau FROM rendez_vous WHERE idRdv = :id AND idUtilisateur = :u LIMIT 1'
            );
            $stmt->execute(['id' => $idRdv, 'u' => (int) $user['id']]);
            $rdv = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rdv) {
                $error = 'Rendez-vous introuvable.';
            } else {
                // Annuler le rendez-vous et rendre le créneau de nouveau disponible
                $db->beginTransaction();
                $db->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE idRdv = :id")
                    ->execute(['id' => $idRdv]);
                $db->prepare('UPDATE creneau_horaire SET disponibilite = 1 WHERE idCreneau = :id')
                    ->execute(['id' => $rdv['idCreneau']]);
                $db->commit();

                header('Location: ' . $base . '/pages/mes-rendez-vous.php?annule=1', true, 303);
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Base de données indisponible.';
        }
    }
}

// Liste des rendez-vous de l'utilisateur
$stmt = authDb()->prepare(
    'SELECT r.idRdv, r.dateReservation, r.statut,
            c.typeService, c.dateRendezVous, c.heureDebut, c.heureFin
     FROM rendez_vous r
     JOIN creneau_horaire c ON c.idCreneau = r.idCreneau
     WHERE r.idUtilisateur = :u
     ORDER BY c.dateRendezVous DESC, c.heureDebut DESC'
);
$stmt->execute(['u' => (int) $user['id']]);
$rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomService = function ($cle) use ($types) {
    return isset($types[$cle]['nom']) ? $types[$cle]['nom'] : $cle;
};

$libelleStatut = function ($statut) {
    if ($statut === 'confirme') return 'Confirmé';
    if ($statut === 'annule')   return 'Annulé';
    return 'En attente';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require __DIR__ . '/../includes/head.php'; ?>
    <link rel="stylesheet" href="<?= $assets ?>/css/auth.css">
</head>
<body>
<?php require __DIR__ . '/../includes/navbar.php'; ?>

<main class="auth-page">
<div class="auth-shell">
    <aside class="auth-intro">
        <div class="auth-emblem"><i class="far fa-calendar-alt"></i></div>
        <span class="auth-kicker">MES RENDEZ-VOUS</span>
        <h1>Suivez vos<br><span>rendez-vous.</span></h1>
        <p>Retrouvez ici tous vos rendez-vous et annulez ceux que vous ne souhaitez plus garder.</p>
        <a class="auth-back" href="<?= $base ?>/pages/rendez-vous.php">← Prendre un nouveau rendez-vous</a>
    </aside>

    <section class="auth-form">
        <h2>Mes rendez-vous</h2>

        <?php if ($error): ?>
            <div class="auth-error" role="alert"><?= authEscape($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['reserve'])): ?>
            <div class="auth-error" role="alert" style="background:#ecfdf3;color:#027a48;">Votre rendez-vous a bien été enregistré.</div>
        <?php endif; ?>
        <?php if (isset($_GET['annule'])): ?>
            <div class="auth-error" role="alert" style="background:#ecfdf3;color:#027a48;">Le rendez-vous a été annulé.</div>
        <?php endif; ?>

        <?php if (empty($rdvs)): ?>
            <p>Vous n'avez encore aucun rendez-vous.</p>
            <a class="auth-submit" style="display:inline-block;text-align:center;text-decoration:none;" href="<?= $base ?>/pages/rendez-vous.php">Prendre un rendez-vous <i class="fas fa-arrow-right"></i></a>
        <?php else: ?>
            <?php foreach ($rdvs as $r): ?>
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-bottom:12px;">
                    <strong><?= authEscape($nomService($r['typeService'])) ?></strong><br>
                    <span><?= authEscape($r['dateRendezVous']) ?> — <?= authEscape(substr($r['heureDebut'], 0, 5)) ?>-<?= authEscape(substr($r['heureFin'], 0, 5)) ?></span><br>
                    <span>Statut : <strong><?= authEscape($libelleStatut($r['statut'])) ?></strong></span>

                    <?php if ($r['statut'] !== 'annule'): ?>
                        <form method="post" style="margin-top:10px;">
                            <input type="hidden" name="csrf" value="<?= authToken() ?>">
                            <input type="hidden" name="idRdv" value="<?= authEscape($r['idRdv']) ?>">
                            <button class="auth-submit" type="submit">Annuler ce rendez-vous</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; require __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
