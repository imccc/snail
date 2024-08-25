<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Tooltip
{
    private $text;
    private $tooltip;

    public function __construct($text, $tooltip)
    {
        $this->text = $text;
        $this->tooltip = $tooltip;
    }

    public function render()
    {
        return '<span data-bs-toggle="tooltip" title="' . htmlspecialchars($this->tooltip) . '">' . htmlspecialchars($this->text) . '</span>';
    }
}

// 使用示例
// $tooltip = new Imccc\Snail\Integration\Bootstrap\Tooltip('Hover over me', 'This is a tooltip!');
// echo $tooltip->render();
