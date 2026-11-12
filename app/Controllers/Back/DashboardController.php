<?php
namespace App\Controllers\Back;
use App\Core\Controller;

class DashboardController extends Controller
{
    protected string $space = 'back';
    protected string $page = 'dashboard';
    protected string $title = 'Dashboard | CivLink Admin';

    public function index(): void
    {
        
        $this->render('dashboard');
    }
}
