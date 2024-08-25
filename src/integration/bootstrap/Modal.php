<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Modal
{
    private $id;
    private $title;
    private $body;
    private $footer;

    public function __construct($id, $title, $body, $footer = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->footer = $footer;
    }

    public function render()
    {
        $html = '<div class="modal fade" id="' . htmlspecialchars($this->id) . '" tabindex="-1" aria-labelledby="' . htmlspecialchars($this->id) . 'Label" aria-hidden="true">';
        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<h5 class="modal-title" id="' . htmlspecialchars($this->id) . 'Label">' . htmlspecialchars($this->title) . '</h5>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $html .= '</div>';
        $html .= '<div class="modal-body">' . htmlspecialchars($this->body) . '</div>';
        if ($this->footer) {
            $html .= '<div class="modal-footer">' . htmlspecialchars($this->footer) . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $modal = new Imccc\Snail\Integration\Bootstrap\Modal('exampleModal', 'Modal Title', 'This is the modal body.', '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
// echo $modal->render();
