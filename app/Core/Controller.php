<?php
namespace App\Core;

abstract class Controller
{
    protected string $page = 'home';
    protected string $title = 'CivLink';
    protected string $space = 'front'; // front | back

    protected function render(string $view, array $data = [], ?string $scripts = null): void
    {
        $content = $this->capture(__DIR__ . "/../Views/{$this->space}/pages/$view.php", $data);
        $extraScripts = $scripts ? $this->capture(__DIR__ . "/../Views/{$this->space}/pages/$scripts.php", $data) : '';
        $title = $this->title;
        $currentPage = $this->page;
        require __DIR__ . "/../Views/{$this->space}/layout.php";
    }

    private function capture(string $file, array $data): string
    {
        extract($data);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}
