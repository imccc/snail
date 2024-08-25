<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Offcanvas
{
    private $id;
    private $title;
    private $content;
    private $placement;

    public function __construct($id, $title, $content, $placement = 'start')
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->placement = $placement;
    }

    public function render()
    {
        $html = '<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#' . htmlspecialchars($this->id) . '" aria-controls="' . htmlspecialchars($this->id) . '">';
        $html .= 'Toggle Drawer';
        $html .= '</button>';

        $html .= '<div class="offcanvas offcanvas-' . htmlspecialchars($this->placement) . '" tabindex="-1" id="' . htmlspecialchars($this->id) . '" aria-labelledby="' . htmlspecialchars($this->id) . 'Label">';
        $html .= '<div class="offcanvas-header">';
        $html .= '<h5 class="offcanvas-title" id="' . htmlspecialchars($this->id) . 'Label">' . htmlspecialchars($this->title) . '</h5>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>';
        $html .= '</div>';
        $html .= '<div class="offcanvas-body">';
        $html .= htmlspecialchars($this->content);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $offcanvas = new Imccc\Snail\Integration\Bootstrap\Offcanvas('OffcanvasExample', 'DOffcanvas Title', 'This is the Offcanvas content.');
// echo $offcanvas->render();
