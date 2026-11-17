<?php
namespace App\Controllers\Back;

use App\Core\Controller;
use App\Models\Signalement;

class DashboardController extends Controller
{
    protected string $space = 'back';
    protected string $page  = 'dashboard';
    protected string $title = 'Tableau de bord | CivLink Admin';

    public function index(): void
    {
        $all = Signalement::all();
        $counts = ['en_attente' => 0, 'en_cours' => 0, 'resolu' => 0];
        foreach ($all as $s) {
            $k = $s['statut'] ?? 'en_attente';
            if (isset($counts[$k])) { $counts[$k]++; }
        }
        $this->render('dashboard', [
            'total'    => count($all),
            'counts'   => $counts,
            'derniers' => array_slice($all, 0, 5),
        ]);
    }
}