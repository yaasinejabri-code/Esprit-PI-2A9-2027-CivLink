<?php
namespace App\Models;
use App\Core\Database;

class Signalement
{
    public static function all(): array
    {
        return Database::getConnection()
            ->query('SELECT s.*, c.nom AS categorie FROM signalement s
                     LEFT JOIN categorie c ON c.id = s.categorie_id
                     ORDER BY s.date_creation DESC')
            ->fetchAll();
    }
    // TODO: find($id), create($data), updateStatut($id,$statut), delete($id)
}
