<?php
namespace Imccc\Snail\Integration\Weui;

class FormGroup
{
    private $elements = [];

    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    public function render()
    {
        $html = '<div class="weui-cells weui-cells_form">';
        foreach ($this->elements as $element) {
            $html .= $element->render();
        }
        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIFormGroup;
// use Imccc\Snail\Integration\WeUI\WeUIRadio;
// use Imccc\Snail\Integration\WeUI\WeUICheckbox;
// use Imccc\Snail\Integration\WeUI\WeUISwitch;
// use Imccc\Snail\Integration\WeUI\WeUIInput;
