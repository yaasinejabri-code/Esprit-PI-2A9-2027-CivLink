<?php
require_once __DIR__ . '/Auth.php';

/**
 * Entité Facture : idFacture, reference, fournisseur, montant, dateEmission, dateEcheance, statut,
 * idMode (FK), referencePaiement [0..1], datePaiement [0..1], idUtilisateur (FK).
 * Mode démonstration : aucun vrai paiement bancaire.
 */
class Facture
{
    public const FOURNISSEURS = ['STEG', 'SONEDE'];

    private const SELECT = 'SELECT f.*, m.libelle AS modeLibelle, u.nom, u.prenom, u.email
                            FROM facture f
                            JOIN utilisateur u ON u.idUtilisateur = f.idUtilisateur
                            LEFT JOIN modepaiement m ON m.idMode = f.idMode';

    /**
     * Consulter les factures. $idUser = null → toutes (administrateur), sinon celles du citoyen.
     * Filtres : fournisseur (STEG/SONEDE), statut (impayee/payee), recherche sur la référence.
     */
    public static function consulter(?int $idUser, ?string $fournisseur = null, string $q = '', ?string $statut = null): array
    {
        $w = []; $p = [];
        if ($idUser !== null) { $w[] = 'f.idUtilisateur = ?'; $p[] = $idUser; }
        if ($fournisseur && in_array($fournisseur, self::FOURNISSEURS, true)) { $w[] = 'f.fournisseur = ?'; $p[] = $fournisseur; }
        if ($statut && in_array($statut, ['impayee', 'payee'], true)) { $w[] = 'f.statut = ?'; $p[] = $statut; }
        if ($q !== '') { $w[] = '(f.reference LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)'; $like = '%' . addcslashes($q, '%_\\') . '%'; array_push($p, $like, $like, $like); }
        $sql = self::SELECT . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY f.dateEcheance DESC, f.idFacture DESC';
        $s = authDb()->prepare($sql);
        $s->execute($p);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    /** $idUser = null : sans restriction de propriétaire (administrateur). */
    public static function trouver(int $id, ?int $idUser = null): ?array
    {
        $sql = self::SELECT . ' WHERE f.idFacture = ?'; $p = [$id];
        if ($idUser !== null) { $sql .= ' AND f.idUtilisateur = ?'; $p[] = $idUser; }
        $s = authDb()->prepare($sql);
        $s->execute($p);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Citoyens actifs auxquels on peut rattacher une facture. */
    public static function citoyens(): array
    {
        return authDb()->query(
            "SELECT u.idUtilisateur, u.nom, u.prenom, u.email
             FROM utilisateur u JOIN role r ON r.idRole = u.idRole
             WHERE r.nomRole = 'citoyen' AND u.actif = 1 ORDER BY u.nom, u.prenom"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function citoyenValide(int $idUser): bool
    {
        $s = authDb()->prepare("SELECT COUNT(*) FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE u.idUtilisateur = ? AND u.actif = 1 AND r.nomRole = 'citoyen'");
        $s->execute([$idUser]);
        return (int) $s->fetchColumn() > 0;
    }

    public static function ajouter(int $idUser, array $d): int
    {
        $s = authDb()->prepare("INSERT INTO facture (reference, fournisseur, montant, dateEmission, dateEcheance, statut, idUtilisateur) VALUES (?,?,?,?,?,'impayee',?)");
        $s->execute([$d['reference'], $d['fournisseur'], $d['montant'], $d['dateEmission'], $d['dateEcheance'], $idUser]);
        return (int) authDb()->lastInsertId();
    }

    /** Modifiable uniquement si impayée. @return bool false si introuvable ou déjà payée */
    public static function modifier(int $id, int $idUser, array $d): bool
    {
        $s = authDb()->prepare("UPDATE facture SET reference=?, fournisseur=?, montant=?, dateEmission=?, dateEcheance=?, idUtilisateur=? WHERE idFacture=? AND statut='impayee'");
        $s->execute([$d['reference'], $d['fournisseur'], $d['montant'], $d['dateEmission'], $d['dateEcheance'], $idUser, $id]);
        return $s->rowCount() > 0;
    }

    /** Suppression uniquement si impayée (une facture réglée garde sa trace). */
    public static function supprimer(int $id): bool
    {
        $s = authDb()->prepare("DELETE FROM facture WHERE idFacture=? AND statut='impayee'");
        $s->execute([$id]);
        return $s->rowCount() > 0;
    }

    /** Anti double paiement : la mise à jour n'a lieu que si la facture est encore impayée. */
    public static function simulerPaiement(int $id, int $idUser, int $idMode): ?array
    {
        $ref = 'DEMO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $s = authDb()->prepare("UPDATE facture SET statut='payee', idMode=?, referencePaiement=?, datePaiement=NOW() WHERE idFacture=? AND idUtilisateur=? AND statut='impayee'");
        $s->execute([$idMode, $ref, $id, $idUser]);
        return $s->rowCount() > 0 ? self::trouver($id, $idUser) : null;
    }

    /** Administrateur : annule un paiement simulé, la facture redevient impayée. */
    public static function annulerPaiement(int $id): bool
    {
        $s = authDb()->prepare("UPDATE facture SET statut='impayee', idMode=NULL, referencePaiement=NULL, datePaiement=NULL WHERE idFacture=? AND statut='payee'");
        $s->execute([$id]);
        return $s->rowCount() > 0;
    }

    /** Données du reçu de démonstration (uniquement pour une facture payée). */
    public static function genererRecu(int $id, ?int $idUser): ?array
    {
        $f = self::trouver($id, $idUser);
        if (!$f || $f['statut'] !== 'payee') return null;
        return [
            'idFacture' => (int) $f['idFacture'], 'numeroRecu' => 'RECU-' . $f['referencePaiement'],
            'reference' => $f['reference'], 'fournisseur' => $f['fournisseur'], 'montant' => $f['montant'],
            'mode' => $f['modeLibelle'], 'referencePaiement' => $f['referencePaiement'], 'datePaiement' => $f['datePaiement'],
            'citoyen' => trim($f['prenom'] . ' ' . $f['nom']), 'demo' => true,
        ];
    }
}
