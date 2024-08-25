<?php
namespace Imccc\Snail\Integration\Bootstrap;

class ListGroup
{
    private $items;
    private $flush;
    private $horizontal;

    public function __construct($items = [], $flush = false, $horizontal = false)
    {
        $this->items = $items;
        $this->flush = $flush;
        $this->horizontal = $horizontal;
    }

    public function render()
    {
        $class = 'list-group';
        if ($this->flush) {
            $class .= ' list-group-flush';
        }
        if ($this->horizontal) {
            $class .= ' list-group-horizontal';
        }

        $html = '<ul class="' . $class . '">';
        foreach ($this->items as $item) {
            $html .= '<li class="list-group-item">' . htmlspecialchars($item) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}

// 使用示例
// $listGroup = new Imccc\Snail\Integration\Bootstrap\ListGroup(['Item 1', 'Item 2', 'Item 3'], true, true);
// echo $listGroup->render();
