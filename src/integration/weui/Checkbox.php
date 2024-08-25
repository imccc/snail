<?php
namespace Imccc\Snail\Integration\Weui;

class Checkbox
{
    private $checked;
    private $disabled;
    private $id;
    private $name;
    private $value;
    private $additionalClasses;

    public function __construct($value, $checked = false, $disabled = false, $id = '', $name = '', $additionalClasses = '')
    {
        $this->value = $value;
        $this->checked = $checked;
        $this->disabled = $disabled;
        $this->id = $id;
        $this->name = $name;
        $this->additionalClasses = $additionalClasses;
    }

    public function render()
    {
        $class = 'weui-check';
        $class .= $this->additionalClasses ? ' ' . $this->additionalClasses : '';

        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';
        $nameAttr = $this->name ? ' name="' . $this->name . '"' : '';
        $checkedAttr = $this->checked ? ' checked' : '';
        $disabledAttr = $this->disabled ? ' disabled' : '';
        $valueAttr = $this->value ? ' value="' . $this->value . '"' : '';

        return '<input type="checkbox"' . $idAttr . $nameAttr . $valueAttr . $checkedAttr . $disabledAttr . ' class="' . $class . '">';
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUICheckbox;

// $checkbox = new WeUICheckbox('value1', true, false, 'checkbox1', 'options', 'my-custom-class');
// echo $checkbox->render();