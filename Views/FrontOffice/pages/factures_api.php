<?php
/** Factures fictives du citoyen connecté ; aucune donnée bancaire. */
require_once __DIR__ . '/../../../Models/Auth.php';
authStart();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');

function facResponse(int $code, array $data): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$uid = (int)($_SESSION['user']['id'] ?? 0);
if (!$uid) facResponse(401, ['error'=>'Connectez-vous pour consulter vos factures.']);
try {
    $db = authDb();
    $u=$db->prepare('SELECT actif FROM utilisateur WHERE idUtilisateur=?');$u->execute([$uid]);
    if (!(int)$u->fetchColumn()) facResponse(403, ['error'=>'Compte désactivé.']);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $q=$db->prepare('SELECT f.idFacture AS internalId,f.referenceFacture AS id,f.fournisseur AS provider,f.libelle AS name,f.periode AS period,f.dateEcheance AS due,f.montant AS amount, (p.idPaiement IS NOT NULL) AS paid,DATE(p.datePaiement) AS paidOn FROM facture f LEFT JOIN paiement p ON p.idFacture=f.idFacture WHERE f.idUtilisateur=? ORDER BY f.dateEcheance DESC,f.idFacture DESC');
        $q->execute([$uid]);
        $rows=$q->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as &$r){$r['amount']=(float)$r['amount'];$r['paid']=(bool)$r['paid'];}
        unset($r);
        facResponse(200,['invoices'=>$rows]);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: GET, POST'); facResponse(405,['error'=>'Méthode non autorisée.']); }
    $data=json_decode(file_get_contents('php://input'),true);
    if (!is_array($data) || !is_string($data['csrf']??null) || !hash_equals(authToken(),$data['csrf'])) facResponse(403,['error'=>'Session expirée. Rechargez la page.']);
    if (($data['action']??'') !== 'pay' || !filter_var($data['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])) facResponse(400,['error'=>'Paiement invalide.']);
    $db->beginTransaction();
    $q=$db->prepare('SELECT montant FROM facture WHERE idFacture=? AND idUtilisateur=? FOR UPDATE');
    $q->execute([(int)$data['id'],$uid]);$amount=$q->fetchColumn();
    if ($amount === false) { $db->rollBack(); facResponse(404,['error'=>'Facture introuvable.']); }
    $q=$db->prepare('SELECT idPaiement FROM paiement WHERE idFacture=?');$q->execute([(int)$data['id']]);
    if ($q->fetchColumn()) { $db->rollBack(); facResponse(409,['error'=>'Cette facture est déjà réglée.']); }
    $q=$db->prepare('INSERT INTO paiement(idFacture,montant,modePaiement,referencePaiement) VALUES(?,?,?,?)');
    $q->execute([(int)$data['id'],$amount,'simulation','SIM-'.bin2hex(random_bytes(12))]);
    $db->commit();
    facResponse(200,['ok'=>true,'paidOn'=>date('Y-m-d')]);
} catch(PDOException $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    facResponse(500,['error'=>$e->getCode()==='23000'?'Cette facture est déjà réglée.':'Erreur serveur. Vérifiez que database_factures.sql est importé.']);
} catch(Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    facResponse(500,['error'=>'Erreur du serveur.']);
}
