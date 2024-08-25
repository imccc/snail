<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Card
{
    private $title;
    private $body;
    private $footer;

    public function __construct($title, $body, $footer = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->footer = $footer;
    }

    public function render()
    {
        $html = '<div class="card">';
        $html .= '<div class="card-header">' . htmlspecialchars($this->title) . '</div>';
        $html .= '<div class="card-body">' . htmlspecialchars($this->body) . '</div>';
        if ($this->footer) {
            $html .= '<div class="card-footer">' . htmlspecialchars($this->footer) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $card = new Imccc\Snail\Integration\Bootstrap\Card('Card Title', 'This is the card body.', 'This is the footer');
// echo $card->render();
