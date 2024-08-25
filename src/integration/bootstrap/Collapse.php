<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Collapse
{
    private $id;
    private $buttonText;
    private $content;

    public function __construct($id, $buttonText, $content)
    {
        $this->id = $id;
        $this->buttonText = $buttonText;
        $this->content = $content;
    }

    public function render()
    {
        $html = '<p><a class="btn btn-primary" data-bs-toggle="collapse" href="#' . htmlspecialchars($this->id) . '" role="button" aria-expanded="false" aria-controls="' . htmlspecialchars($this->id) . '">';
        $html .= htmlspecialchars($this->buttonText);
        $html .= '</a></p>';
        $html .= '<div class="collapse" id="' . htmlspecialchars($this->id) . '">';
        $html .= '<div class="card card-body">';
        $html .= htmlspecialchars($this->content);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $collapse = new Imccc\Snail\Integration\Bootstrap\Collapse('collapseExample', 'Toggle Collapse', 'This is the collapsible content.');
// echo $collapse->render();
