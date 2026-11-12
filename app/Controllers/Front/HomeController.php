<?php
namespace App\Controllers\Front;
use App\Core\Controller;

class HomeController extends Controller
{
    protected string $space = 'front';
    protected string $page = 'home';
    protected string $title = 'Accueil | CivLink';

    public function index(): void
    {
        
        $this->render('home');
    }
}
