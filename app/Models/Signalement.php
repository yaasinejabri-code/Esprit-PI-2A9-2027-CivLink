<?php
namespace App\Models;

use App\Core\Database;

class Signalement
{
    public static function all(): array
    {
        return Database::getConnection()
            ->query('SELECT s.*, c.nom AS categorie
                     FROM signalement s
                     LEFT JOIN categorie_signalement c ON c.id = s.categorie_id
                     ORDER BY s.date_creation DESC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = Database::getConnection()->prepare('SELECT * FROM signalement WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare(
            'INSERT INTO signalement
             (titre, description, photo, latitude, longitude, categorie_id, utilisateur_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['titre'],
            $d['description'] ?? null,
            $d['photo'] ?? null,
            ($d['latitude'] ?? '') !== '' ? $d['latitude'] : null,
            ($d['longitude'] ?? '') !== '' ? $d['longitude'] : null,
            $d['categorie_id'],
            $d['utilisateur_id'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $d): bool
    {
        $st = Database::getConnection()->prepare(
            'UPDATE signalement
             SET titre = ?, description = ?, latitude = ?, longitude = ?, categorie_id = ?
             WHERE id = ?'
        );
        return $st->execute([
            $d['titre'],
            $d['description'] ?? null,
            ($d['latitude'] ?? '') !== '' ? $d['latitude'] : null,
            ($d['longitude'] ?? '') !== '' ? $d['longitude'] : null,
            $d['categorie_id'],
            $id,
        ]);
    }

    public static function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, ['en_attente', 'en_cours', 'resolu'], true)) {
            return false;
        }
        $st = Database::getConnection()->prepare('UPDATE signalement SET statut = ? WHERE id = ?');
        return $st->execute([$statut, $id]);
    }

    public static function delete(int $id): bool
    {
        $st = Database::getConnection()->prepare('DELETE FROM signalement WHERE id = ?');
        return $st->execute([$id]);
    }

    public static function categories(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM categorie_signalement ORDER BY nom')
            ->fetchAll();
    }
}