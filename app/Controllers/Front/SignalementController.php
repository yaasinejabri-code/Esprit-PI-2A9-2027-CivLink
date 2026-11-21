<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Models\SignalementFront;

class SignalementController extends Controller
{
    protected string $page  = 'signalements';
    protected string $title = 'Signalements | CivLink';

    public function index(): void
    {
        $this->render('signalements', ['signalements' => SignalementFront::all()]);
    }

    public function create(): void
    {
        $old = ['titre' => '', 'description' => '', 'categorie_id' => '', 'latitude' => '', 'longitude' => '', 'photo' => null];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $this->collect();
            $errors = $this->validate($old);
            if (!$errors) {
                $old['photo'] = $this->uploadPhoto($errors);
            }
            if (!$errors) {
                SignalementFront::create($this->prepare($old));
                $this->redirect('signalements&ok=1');
            }
        }

        $this->render('signalement-form', [
            'mode' => 'create', 'old' => $old, 'errors' => $errors,
            'categories' => SignalementFront::categories(),
        ]);
    }

    public function edit(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $sig = SignalementFront::find($id);

        // seul un signalement "en attente" peut être modifié par le citoyen
        if (!$sig || $sig['statut'] !== 'en_attente') {
            $this->redirect('signalements');
        }

        $old = $sig;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $this->collect();
            $old['photo'] = $sig['photo'];
            $errors = $this->validate($old);
            if (!$errors) {
                $new = $this->uploadPhoto($errors);
                if ($new) {
                    $old['photo'] = $new;
                }
            }
            if (!$errors) {
                SignalementFront::update($id, $this->prepare($old));
                $this->redirect('signalements&ok=2');
            }
        }

        $this->render('signalement-form', [
            'mode' => 'edit', 'id' => $id, 'old' => $old, 'errors' => $errors,
            'categories' => SignalementFront::categories(),
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            SignalementFront::delete((int) ($_POST['id'] ?? 0));
            $this->redirect('signalements&ok=3');
        }
        $this->redirect('signalements');
    }

    // ---------- helpers (private) ----------

    private function collect(): array
    {
        return [
            'titre'        => trim($_POST['titre'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'categorie_id' => $_POST['categorie_id'] ?? '',
            'latitude'     => trim($_POST['latitude'] ?? ''),
            'longitude'    => trim($_POST['longitude'] ?? ''),
            'photo'        => null,
        ];
    }

    private function validate(array $d): array
    {
        $e = [];
        if (mb_strlen($d['titre']) < 5 || mb_strlen($d['titre']) > 150) {
            $e[] = "Le titre doit contenir entre 5 et 150 caractères.";
        }
        if (mb_strlen($d['description']) < 10) {
            $e[] = "La description doit contenir au moins 10 caractères.";
        }

        $ids = array_map('intval', array_column(SignalementFront::categories(), 'id'));
        if (!in_array((int) $d['categorie_id'], $ids, true)) {
            $e[] = "Veuillez choisir une catégorie valide.";
        }

        if ($d['latitude'] !== '' && (!is_numeric($d['latitude']) || abs((float) $d['latitude']) > 90)) {
            $e[] = "Latitude invalide (entre -90 et 90).";
        }
        if ($d['longitude'] !== '' && (!is_numeric($d['longitude']) || abs((float) $d['longitude']) > 180)) {
            $e[] = "Longitude invalide (entre -180 et 180).";
        }
        return $e;
    }

    private function prepare(array $d): array
    {
        $d['categorie_id'] = (int) $d['categorie_id'];
        $d['latitude']     = $d['latitude']  === '' ? null : (float) $d['latitude'];
        $d['longitude']    = $d['longitude'] === '' ? null : (float) $d['longitude'];
        return $d;
    }

    private function uploadPhoto(array &$errors): ?string
    {
        if (empty($_FILES['photo']['name'])) {
            return null;
        }
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || $_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Photo invalide (jpg, png ou webp, 2 Mo maximum).";
            return null;
        }
        $name = uniqid('sig_') . '.' . $ext;
        $dir  = __DIR__ . '/../../../public/uploads/signalements/';
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $name)) {
            $errors[] = "Échec de l'envoi de la photo.";
            return null;
        }
        return $name;
    }

    private function redirect(string $page): void
    {
        header('Location: ' . BASE_URL . '/index.php?page=' . $page);
        exit;
    }
}