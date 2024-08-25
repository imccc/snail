<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Navbar
{
    private $brand;
    private $links;

    public function __construct($brand, $links = [])
    {
        $this->brand = $brand;
        $this->links = $links;
    }

    public function render()
    {
        $html = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
        $html .= '<div class="container-fluid">';
        $html .= '<a class="navbar-brand" href="#">' . htmlspecialchars($this->brand) . '</a>';
        $html .= '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">';
        $html .= '<span class="navbar-toggler-icon"></span>';
        $html .= '</button>';
        $html .= '<div class="collapse navbar-collapse" id="navbarNav">';
        $html .= '<ul class="navbar-nav">';
        
        foreach ($this->links as $link) {
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link" href="' . htmlspecialchars($link['url']) . '">' . htmlspecialchars($link['text']) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</nav>';

        return $html;
    }
}

// 使用示例
// $navbar = new Imccc\Snail\Integration\Bootstrap\Navbar('My Brand', [
//     ['url' => '#', 'text' => 'Home'],
//     ['url' => '#', 'text' => 'Features'],
//     ['url' => '#', 'text' => 'Pricing']
// ]);
// echo $navbar->render();
