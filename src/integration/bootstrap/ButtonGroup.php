<?php
namespace Imccc\Snail\Integration\Bootstrap;

class ButtonGroup
{
    private $buttons;
    private $vertical;

    public function __construct($buttons = [], $vertical = false)
    {
        $this->buttons = $buttons;
        $this->vertical = $vertical;
    }

    public function render()
    {
        $html = '<div class="btn-group' . ($this->vertical ? '-vertical' : '') . '" role="group">';
        
        foreach ($this->buttons as $button) {
            $html .= '<button type="button" class="btn btn-' . htmlspecialchars($button['type']) . '">' . htmlspecialchars($button['text']) . '</button>';
        }

        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $buttonGroup = new Imccc\Snail\Integration\Bootstrap\ButtonGroup([
//     ['type' => 'primary', 'text' => 'Button 1'],
//     ['type' => 'secondary', 'text' => 'Button 2'],
//     ['type' => 'success', 'text' => 'Button 3']
// ]);
// echo $buttonGroup->render();
