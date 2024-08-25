<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Toast
{
    private $title;
    private $body;

    public function __construct($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function render()
    {
        $html = '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">';
        $html .= '<div class="toast-header">';
        $html .= '<strong class="me-auto">' . htmlspecialchars($this->title) . '</strong>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>';
        $html .= '</div>';
        $html .= '<div class="toast-body">' . htmlspecialchars($this->body) . '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $toast = new Imccc\Snail\Integration\Bootstrap\Toast('Toast Title', 'This is the toast body.');
// echo $toast->render();
