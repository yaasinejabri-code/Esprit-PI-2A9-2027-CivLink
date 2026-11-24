<?php
require_once __DIR__ . '/Auth.php';

/** Entité ModePaiement : idMode, libelle, description. Opérations : ajouter, consulter, modifier, supprimer. */
class ModePaiement
{
    /** Un mode (tableau) ou la liste de tous les modes avec le nombre de factures qui l'utilisent. */
    public static function consulter(?int $id = null): array
    {
        if ($id !== null) {
            $s = authDb()->prepare('SELECT * FROM modepaiement WHERE idMode = ?');
            $s->execute([$id]);
            return $s->fetch(PDO::FETCH_ASSOC) ?: [];
        }
        return authDb()->query(
            'SELECT m.*, (SELECT COUNT(*) FROM facture f WHERE f.idMode = m.idMode) AS nbFactures
             FROM modepaiement m ORDER BY m.libelle'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ajouter(string $libelle, string $description): int
    {
        $s = authDb()->prepare('INSERT INTO modepaiement (libelle, description) VALUES (?, ?)');
        $s->execute([$libelle, $description]);
        return (int) authDb()->lastInsertId();
    }

    /** @return bool false si le mode n'existe pas */
    public static function modifier(int $id, string $libelle, string $description): bool
    {
        $s = authDb()->prepare('UPDATE modepaiement SET libelle = ?, description = ? WHERE idMode = ?');
        $s->execute([$libelle, $description, $id]);
        return $s->rowCount() > 0 || self::consulter($id) !== [];
    }

    public static function estUtilise(int $id): bool
    {
        $s = authDb()->prepare('SELECT COUNT(*) FROM facture WHERE idMode = ?');
        $s->execute([$id]);
        return (int) $s->fetchColumn() > 0;
    }

    /** Suppression refusée (clé étrangère RESTRICT) si le mode est utilisé par une facture. */
    public static function supprimer(int $id): bool
    {
        $s = authDb()->prepare('DELETE FROM modepaiement WHERE idMode = ?');
        $s->execute([$id]);
        return $s->rowCount() > 0;
    }
}
