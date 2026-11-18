<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SignalementFront
{
    private static function pdo(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        $sql = "SELECT s.*, c.nom AS categorie
                FROM signalement s
                JOIN categorie_signalement c ON c.id = s.categorie_id
                ORDER BY s.date_creation DESC";
        return self::pdo()->query($sql)->fetchAll();
    }

    public static function categories(): array
    {
        return self::pdo()->query("SELECT id, nom FROM categorie_signalement ORDER BY nom")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = self::pdo()->prepare("SELECT * FROM signalement WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function create(array $d): bool
    {
        $st = self::pdo()->prepare(
            "INSERT INTO signalement (titre, description, photo, latitude, longitude, categorie_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $st->execute([
            $d['titre'], $d['description'], $d['photo'],
            $d['latitude'], $d['longitude'], $d['categorie_id'],
        ]);
    }

    public static function update(int $id, array $d): bool
    {
        $st = self::pdo()->prepare(
            "UPDATE signalement
             SET titre = ?, description = ?, photo = ?, latitude = ?, longitude = ?, categorie_id = ?
             WHERE id = ?"
        );
        return $st->execute([
            $d['titre'], $d['description'], $d['photo'],
            $d['latitude'], $d['longitude'], $d['categorie_id'], $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $st = self::pdo()->prepare("DELETE FROM signalement WHERE id = ?");
        return $st->execute([$id]);
    }
}