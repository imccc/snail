<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Dropdown
{
    private $label;
    private $items;

    public function __construct($label, $items = [])
    {
        $this->label = $label;
        $this->items = $items;
    }

    public function render()
    {
        $html = '<div class="dropdown">';
        $html .= '<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
        $html .= htmlspecialchars($this->label);
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

        foreach ($this->items as $item) {
            $html .= '<li><a class="dropdown-item" href="' . htmlspecialchars($item['href']) . '">' . htmlspecialchars($item['label']) . '</a></li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $dropdown = new Imccc\Snail\Integration\Bootstrap\Dropdown('Dropdown button', [
//     ['href' => '#', 'label' => 'Action'],
//     ['href' => '#', 'label' => 'Another action'],
//     ['href' => '#', 'label' => 'Something else here'],
// ]);
// echo $dropdown->render();
