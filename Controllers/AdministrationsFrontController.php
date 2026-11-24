<?php
require_once __DIR__ . '/../Models/Auth.php';
require_once __DIR__ . '/../Models/CivData.php';
require_once __DIR__ . '/../Models/Administration.php';
require_once __DIR__ . '/../Models/Service.php';

class AdministrationsFrontController
{
    public function index(): void
    {
        $pageTitle = "Administrations - CivLink";
        $currentPage = "administrations";
        $base = "/CivLink/Views/FrontOffice";
        $assets = $base . "/assets";
        $categories = CivData::categories();
        $admData = ['sites' => $this->mapSites($categories), 'demarchesUrl' => $base . '/pages/demarches.php'];

        if (!defined('CIVLINK_FRONT_RENDER')) {
            define('CIVLINK_FRONT_RENDER', true);
        }
        require __DIR__ . '/../Views/FrontOffice/templates/administrations.php';
    }

    private function mapSites(array $categories): array
    {
        $types = CivData::types();
        $sites = [];
        foreach ($this->loadSites() as $site) {
            $t = $types[$site['type']] ?? null;
            if (!$t) {
                continue;
            }
            $sites[] = $site + [
                'typeNom' => $t['nom'], 'cat' => $t['cat'], 'catLabel' => $categories[$t['cat']],
                'icone' => $t['icone'], 'desc' => $t['desc'], 'nbDocs' => count($t['docs']),
            ];
        }
        return $sites;
    }

    private function loadSites(): array
    {
        try {
            $db = authDb();
            $stmt = $db->prepare('SELECT * FROM administration WHERE actif = 1 ORDER BY idAdministration');
            $stmt->execute();
            $admins = array_map([Administration::class, 'fromArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));

            $stmt = $db->prepare(
                'SELECT s.*
                 FROM service s
                 JOIN administration a ON a.idAdministration = s.idAdministration
                 WHERE s.actif = 1 AND a.actif = 1
                 ORDER BY s.nom'
            );
            $stmt->execute();
            $servicesByAdmin = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $s = Service::fromArray($row);
                $servicesByAdmin[$s->getIdAdministration()][] = ['nom' => $s->getNom(), 'description' => $s->getDescription()];
            }
        } catch (PDOException $e) {
            return array_map(fn(array $s) => $s + ['services' => []], CivData::sites());
        }

        return array_map(fn(Administration $a) => [
            'id' => $a->getSiteRef(),
            'type' => $a->getTypeAdministration(),
            'nom' => $a->getNom(),
            'adresse' => $a->getAdresse(),
            'horaires' => $a->getHoraires(),
            'tel' => $a->getTelephone(),
            'lat' => $a->getLatitude(),
            'lng' => $a->getLongitude(),
            'services' => $servicesByAdmin[$a->getIdAdministration()] ?? [],
        ], $admins);
    }
}
