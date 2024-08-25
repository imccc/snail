<?php
namespace Imccc\Snail\Integration\Weui;

class SwitchButton
{
    private $checked;
    private $disabled;
    private $id;
    private $name;
    private $additionalClasses;

    public function __construct($checked = false, $disabled = false, $id = '', $name = '', $additionalClasses = '')
    {
        $this->checked = $checked;
        $this->disabled = $disabled;
        $this->id = $id;
        $this->name = $name;
        $this->additionalClasses = $additionalClasses;
    }

    public function render()
    {
        $class = 'weui-switch';
        $class .= $this->additionalClasses ? ' ' . $this->additionalClasses : '';

        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';
        $nameAttr = $this->name ? ' name="' . $this->name . '"' : '';
        $checkedAttr = $this->checked ? ' checked' : '';
        $disabledAttr = $this->disabled ? ' disabled' : '';

        return '<input type="checkbox"' . $idAttr . $nameAttr . $checkedAttr . $disabledAttr . ' class="' . $class . '">';
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUISwitch;

// $switchButton = new SwitchButton(true, false, 'switch1', 'toggleSwitch');
// echo $switchButton->render();

