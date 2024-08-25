<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Button
{
    private $type;
    private $label;

    public function __construct($type, $label)
    {
        $this->type = $type;
        $this->label = $label;
    }

    public function render()
    {
        return '<button class="btn btn-' . htmlspecialchars($this->type) . '">' . htmlspecialchars($this->label) . '</button>';
    }
}

// // 使用示例
// $button = new Imccc\Snail\Integration\Bootstrap\Button('primary', 'Click Me');
// echo $button->render();
