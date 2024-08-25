<?php
namespace Imccc\Snail\Integration\WeUI;

class Button
{
    private $type;
    private $size;
    private $label;
    private $plain;
    private $disabled;
    private $loading;
    private $additionalClasses;
    private $id;

    public function __construct($label, $id = 'button1', $type = 'primary', $size = '', $plain = false, $disabled = false, $loading = false, $additionalClasses = '')
    {
        $this->label = $label;
        $this->type = $type;
        $this->size = $size;
        $this->plain = $plain;
        $this->disabled = $disabled;
        $this->loading = $loading;
        $this->additionalClasses = $additionalClasses;
        $this->id = $id;
    }

    public function render()
    {
        $class = 'weui-btn';
        $class .= $this->type ? ' weui-btn_' . $this->type : '';
        $class .= $this->size ? ' weui-btn_' . $this->size : '';
        $class .= $this->plain ? ' weui-btn_plain-' . $this->type : '';
        $class .= $this->disabled ? ' weui-btn_disabled' : '';
        $class .= $this->loading ? ' weui-btn_loading' : '';
        $class .= $this->additionalClasses ? ' ' . $this->additionalClasses : '';

        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        return '<button' . $idAttr . ' class="' . $class . '">' . ($this->loading ? '<i class="weui-loading"></i>' : '') . $this->label . '</button>';
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIButton;

// $button = new WeUIButton('确定', 'myButtonId', 'primary', 'default', false, false, true, 'my-custom-class');
// echo $button->render();
