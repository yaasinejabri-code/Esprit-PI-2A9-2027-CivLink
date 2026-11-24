<?php
/**
 * Contrôleur JSON : factures et modes de paiement (appelé par factures.js et factures_admin.js).
 * Sécurité : session obligatoire, rôle relu en base à chaque requête, jeton CSRF sur tout POST,
 * requêtes préparées PDO, validation serveur de toutes les saisies, sortie HTML échappée (reçu).
 *
 * Citoyen        : lister, recu, telecharger, simulerPaiement
 * Administrateur : adminLister, citoyens, adminAjouter, adminModifier, adminSupprimer,
 *                  adminAnnulerPaiement, modeAjouter, modeModifier, modeSupprimer
 */
require_once __DIR__ . '/../Models/Facture.php';
require_once __DIR__ . '/../Models/ModePaiement.php';

authStart();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function out(array $d, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

/** Utilisateur connecté, avec rôle et statut relus en base (une session seule ne suffit pas). */
function utilisateurCourant(): array
{
    $id = (int) ($_SESSION['user']['id'] ?? 0);
    if ($id < 1) out(['error' => 'Connexion requise.'], 401);
    $s = authDb()->prepare('SELECT u.idUtilisateur, u.actif, r.nomRole FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE u.idUtilisateur = ?');
    $s->execute([$id]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    if (!$u || !(int) $u['actif']) out(['error' => 'Compte inexistant ou désactivé.'], 403);
    return ['id' => (int) $u['idUtilisateur'], 'admin' => $u['nomRole'] === 'administrateur'];
}

function exigerAdmin(array $u): void
{
    if (!$u['admin']) out(['error' => 'Action réservée aux administrateurs.'], 403);
}

function dateValide(string $d): bool
{
    $o = DateTimeImmutable::createFromFormat('!Y-m-d', $d);
    return $o && $o->format('Y-m-d') === $d;
}

/** Mêmes règles que factures_admin.js (la validation JS ne remplace jamais celle-ci). */
function validerFacture(): array
{
    $idCit = (int) authPost('idUtilisateur');
    $ref = trim(authPost('reference'));
    $four = authPost('fournisseur');
    $montant = str_replace(',', '.', trim(authPost('montant')));
    $em = authPost('dateEmission');
    $ec = authPost('dateEcheance');
    if ($idCit < 1 || !Facture::citoyenValide($idCit)) out(['error' => 'Sélectionnez un citoyen actif.', 'champ' => 'idUtilisateur'], 422);
    if (!preg_match('/^[A-Za-z0-9._\/-]{3,60}$/D', $ref)) out(['error' => 'Référence invalide (3 à 60 caractères : lettres, chiffres, . _ / -).', 'champ' => 'reference'], 422);
    if (!in_array($four, Facture::FOURNISSEURS, true)) out(['error' => 'Fournisseur invalide (STEG ou SONEDE).', 'champ' => 'fournisseur'], 422);
    if (!preg_match('/^\d{1,7}(\.\d{1,3})?$/D', $montant) || (float) $montant <= 0) out(['error' => 'Montant invalide (nombre positif, 3 décimales maximum).', 'champ' => 'montant'], 422);
    if (!dateValide($em)) out(['error' => 'Date d’émission invalide.', 'champ' => 'dateEmission'], 422);
    if (!dateValide($ec)) out(['error' => 'Date d’échéance invalide.', 'champ' => 'dateEcheance'], 422);
    if ($ec < $em) out(['error' => 'L’échéance doit être postérieure ou égale à l’émission.', 'champ' => 'dateEcheance'], 422);
    return [$idCit, ['reference' => $ref, 'fournisseur' => $four, 'montant' => number_format((float) $montant, 3, '.', ''), 'dateEmission' => $em, 'dateEcheance' => $ec]];
}

function validerMode(): array
{
    $l = trim(authPost('libelle'));
    $d = trim(authPost('description'));
    if (mb_strlen($l) < 2 || mb_strlen($l) > 80) out(['error' => 'Libellé invalide (2 à 80 caractères).', 'champ' => 'libelle'], 422);
    if (mb_strlen($d) > 255) out(['error' => 'Description trop longue (255 caractères maximum).', 'champ' => 'description'], 422);
    return [$l, $d];
}

/** Reçu de démonstration téléchargeable (HTML autonome, toutes les valeurs échappées). */
function telechargerRecu(array $r): never
{
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $nom = 'recu-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $r['referencePaiement']) . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nom . '"');
    $lignes = [
        'N° de reçu' => $r['numeroRecu'], 'Citoyen' => $r['citoyen'], 'Fournisseur' => $r['fournisseur'],
        'Référence facture' => $r['reference'], 'Mode de paiement' => $r['mode'],
        'Référence paiement' => $r['referencePaiement'], 'Date de paiement' => $r['datePaiement'],
        'Montant' => number_format((float) $r['montant'], 3, ',', ' ') . ' DT',
    ];
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>' . $e($r['numeroRecu']) . '</title>'
       . '<style>body{font-family:Arial,sans-serif;max-width:560px;margin:40px auto;color:#223247}h1{color:#f58232;font-size:22px}'
       . 'table{width:100%;border-collapse:collapse}td{padding:9px 6px;border-bottom:1px solid #e7ebf0}td:first-child{color:#718095}'
       . '.demo{margin-top:22px;padding:12px;background:#fff5e9;border:1px solid #ffe3bd;border-radius:8px;font-size:13px}</style></head><body>'
       . '<h1>CIVLINK — Reçu de paiement (démonstration)</h1><table>';
    foreach ($lignes as $k => $v) echo '<tr><td>' . $e($k) . '</td><td><strong>' . $e($v) . '</strong></td></tr>';
    echo '</table><p class="demo">Document fictif : aucun paiement réel n’a été effectué et aucun service STEG/SONEDE n’est connecté.</p></body></html>';
    exit;
}

try {
    $u = utilisateurCourant();
    $post = $_SERVER['REQUEST_METHOD'] === 'POST';
    $action = $post ? authPost('action') : (is_string($_GET['action'] ?? null) ? $_GET['action'] : '');
    $id = $post ? (int) authPost('id') : (int) ($_GET['id'] ?? 0);

    if (!$post) {
        $four = is_string($_GET['fournisseur'] ?? null) ? $_GET['fournisseur'] : null;
        $q = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 80) : '';
        $statut = is_string($_GET['statut'] ?? null) ? $_GET['statut'] : null;
        switch ($action) {
            case 'lister': // citoyen : ses propres factures uniquement
                out(['factures' => Facture::consulter($u['id'], $four, $q, $statut), 'modes' => ModePaiement::consulter()]);
            case 'recu':
                out(['recu' => Facture::genererRecu($id, $u['admin'] ? null : $u['id']) ?? out(['error' => 'Aucun reçu : facture introuvable ou non payée.'], 404)]);
            case 'telecharger':
                telechargerRecu(Facture::genererRecu($id, $u['admin'] ? null : $u['id']) ?? out(['error' => 'Aucun reçu : facture introuvable ou non payée.'], 404));
            case 'adminLister':
                exigerAdmin($u);
                out(['factures' => Facture::consulter(null, $four, $q, $statut), 'modes' => ModePaiement::consulter(), 'citoyens' => Facture::citoyens()]);
            default:
                out(['error' => 'Action inconnue.'], 400);
        }
    }

    if (!authCheck()) out(['error' => 'Session expirée, rechargez la page.'], 403);

    switch ($action) {
        case 'simulerPaiement': // citoyen : sur SA facture uniquement
            $mode = (int) authPost('idMode');
            if ($mode < 1 || !ModePaiement::consulter($mode)) out(['error' => 'Choisissez un mode de paiement valide.', 'champ' => 'idMode'], 422);
            $f = Facture::simulerPaiement($id, $u['id'], $mode) ?? out(['error' => 'Paiement refusé : facture introuvable ou déjà payée.'], 409);
            out(['facture' => $f, 'recu' => Facture::genererRecu($id, $u['id'])]);

        case 'adminAjouter':
            exigerAdmin($u);
            [$idCit, $d] = validerFacture();
            out(['id' => Facture::ajouter($idCit, $d)], 201);
        case 'adminModifier':
            exigerAdmin($u);
            if ($id < 1) out(['error' => 'Facture inconnue.'], 404);
            [$idCit, $d] = validerFacture();
            Facture::modifier($id, $idCit, $d) ?: out(['error' => 'Modification impossible : facture introuvable ou déjà payée.'], 409);
            out(['ok' => true]);
        case 'adminSupprimer':
            exigerAdmin($u);
            Facture::supprimer($id) ?: out(['error' => 'Suppression impossible : facture introuvable ou déjà payée.'], 409);
            out(['ok' => true]);
        case 'adminAnnulerPaiement':
            exigerAdmin($u);
            Facture::annulerPaiement($id) ?: out(['error' => 'Aucun paiement à annuler pour cette facture.'], 409);
            out(['ok' => true]);

        case 'modeAjouter':
            exigerAdmin($u);
            [$l, $d] = validerMode();
            out(['id' => ModePaiement::ajouter($l, $d)], 201);
        case 'modeModifier':
            exigerAdmin($u);
            [$l, $d] = validerMode();
            ModePaiement::modifier($id, $l, $d) ?: out(['error' => 'Mode introuvable.'], 404);
            out(['ok' => true]);
        case 'modeSupprimer':
            exigerAdmin($u);
            if (ModePaiement::estUtilise($id)) out(['error' => 'Ce mode est utilisé par des factures : suppression impossible.'], 409);
            ModePaiement::supprimer($id) ?: out(['error' => 'Mode introuvable.'], 404);
            out(['ok' => true]);

        default:
            out(['error' => 'Action inconnue.'], 400);
    }
} catch (PDOException $e) {
    $dup = ($e->errorInfo[1] ?? 0) === 1062;
    $fk = ($e->errorInfo[1] ?? 0) === 1451;
    out(['error' => $dup ? 'Cette valeur existe déjà (référence de facture ou libellé de mode).' : ($fk ? 'Suppression impossible : élément utilisé ailleurs.' : 'Erreur base de données : avez-vous importé database_factures.sql ?')], $dup || $fk ? 409 : 500);
}
