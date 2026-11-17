<?php
namespace App\Controllers\Back;

use App\Core\Controller;
use App\Models\Signalement;

class SignalementController extends Controller
{
    protected string $space = 'back';
    protected string $page  = 'signalements';
    protected string $title = 'Signalements | CivLink Admin';

    public function index(): void
    {
        $filtreStatut = $_GET['statut'] ?? '';
        $filtreCat    = (int) ($_GET['categorie_id'] ?? 0);

        $liste = array_filter(Signalement::all(), function ($s) use ($filtreStatut, $filtreCat) {
            if ($filtreStatut !== '' && ($s['statut'] ?? '') !== $filtreStatut) return false;
            if ($filtreCat > 0 && (int) $s['categorie_id'] !== $filtreCat) return false;
            return true;
        });

        $this->render('signalements/index', [
            'signalements' => $liste,
            'categories'   => Signalement::categories(),
            'filtreStatut' => $filtreStatut,
            'filtreCat'    => $filtreCat,
        ]);
    }

    public function updateStatut(): void
    {
        Signalement::updateStatut((int) ($_POST['id'] ?? 0), (string) ($_POST['statut'] ?? ''));
        header('Location: admin.php?page=signalements');
        exit;
    }

    public function delete(): void
    {
        Signalement::delete((int) ($_POST['id'] ?? 0));
        header('Location: admin.php?page=signalements');
        exit;
    }
}