<?php
namespace App\Controllers\Back;
use App\Core\Controller;
use App\Models\Signalement;
class SignalementController extends Controller
{
    protected string $space = 'back';
    protected string $page = 'signalements';
    protected string $title = 'Signalements | CivLink Admin';

    public function index(): void
    {
        $signalements = Signalement::all();
        $this->render('signalements', ['signalements' => $signalements]);
    }
}
