<?php
namespace App\Controllers\Front;

use App\Controllers\Controller;
use App\Models\Signalement;

class SignalementController extends Controller
{
    protected string $area  = 'front';
    protected string $page  = 'signalements';
    protected string $title = 'Signalements | CivLink';

    public function index(): void
    {
        $this->render('signalements/index', ['signalements' => Signalement::all()]);
    }

    public function create(): void
    {
        $this->render('signalements/form', [
            'signalement' => null,
            'categories'  => Signalement::categories(),
            'errors'      => [],
        ]);
    }

    public function store(): void
    {
        $errors = $this->validate($_POST);
        if ($errors) {
            $this->render('signalements/form', [
                'signalement' => $_POST,
                'categories'  => Signalement::categories(),
                'errors'      => $errors,
            ]);
            return;
        }
        Signalement::create($_POST);
        header('Location: index.php?page=signalements');
        exit;
    }

    public function edit(): void
    {
        $s = Signalement::find((int) ($_GET['id'] ?? 0));
        if (!$s) { http_response_code(404); exit('Signalement introuvable.'); }
        $this->render('signalements/form', [
            'signalement' => $s,
            'categories'  => Signalement::categories(),
            'errors'      => [],
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $errors = $this->validate($_POST);
        if ($errors) {
            $this->render('signalements/form', [
                'signalement' => $_POST + ['id' => $id],
                'categories'  => Signalement::categories(),
                'errors'      => $errors,
            ]);
            return;
        }
        Signalement::update($id, $_POST);
        header('Location: index.php?page=signalements');
        exit;
    }

    public function delete(): void
    {
        Signalement::delete((int) ($_POST['id'] ?? 0));
        header('Location: index.php?page=signalements');
        exit;
    }

    private function validate(array $d): array
    {
        $e = [];
        if (trim($d['titre'] ?? '') === '') $e[] = 'Le titre est obligatoire.';
        if (empty($d['categorie_id']))      $e[] = 'Choisissez une catégorie.';
        if (($d['latitude'] ?? '') !== '' && !is_numeric($d['latitude']))   $e[] = 'Latitude invalide.';
        if (($d['longitude'] ?? '') !== '' && !is_numeric($d['longitude'])) $e[] = 'Longitude invalide.';
        return $e;
    }
}