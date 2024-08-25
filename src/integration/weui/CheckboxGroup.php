<?php
namespace Imccc\Snail\Integration\Weui;

class CheckboxGroup
{
    private $checkboxes;
    private $id;

    public function __construct($checkboxes = [], $id = '')
    {
        $this->checkboxes = $checkboxes;
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        $html = '<div class="weui-cells weui-cells_checkbox"' . $idAttr . '>';
        foreach ($this->checkboxes as $checkbox) {
            $html .= '<label class="weui-cell weui-check__label">';
            $html .= '<div class="weui-cell__hd">';
            $html .= $checkbox->render();
            $html .= '<i class="weui-icon-checked"></i>';
            $html .= '</div>';
            $html .= '<div class="weui-cell__bd">';
            $html .= '<p>' . $checkbox->getValue() . '</p>';
            $html .= '</div>';
            $html .= '</label>';
        }
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUICheckboxGroup;

// $checkbox1 = new WeUICheckbox('Option 1', false, false, 'checkbox1', 'options', 'my-custom-class');
// $checkbox2 = new WeUICheckbox('Option 2', false, false, 'checkbox2', 'options', 'my-custom-class');
// $checkbox3 =new WeUICheckbox()