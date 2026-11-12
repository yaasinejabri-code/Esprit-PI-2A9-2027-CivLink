<?php
namespace App\Controllers\Front;
use App\Core\Controller;
use App\Models\Signalement;
class SignalementController extends Controller
{
    protected string $space = 'front';
    protected string $page = 'signalements';
    protected string $title = 'Signalements | CivLink';

    public function index(): void
    {
        $signalements = Signalement::all();
        $this->render('signalements', ['signalements' => $signalements]);
    }
}
