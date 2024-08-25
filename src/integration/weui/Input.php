<?php
namespace Imccc\Snail\Integration\Weui;

class Input
{
    private $type;
    private $placeholder;
    private $value;
    private $disabled;
    private $readonly;
    private $additionalClasses;
    private $id;
    private $name;

    public function __construct($type = 'text', $placeholder = '', $value = '', $disabled = false, $readonly = false, $additionalClasses = '', $id = '', $name = '')
    {
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->value = $value;
        $this->disabled = $disabled;
        $this->readonly = $readonly;
        $this->additionalClasses = $additionalClasses;
        $this->id = $id;
        $this->name = $name;
    }

    public function render()
    {
        $class = 'weui-input';
        $class .= $this->additionalClasses ? ' ' . $this->additionalClasses : '';

        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';
        $nameAttr = $this->name ? ' name="' . $this->name . '"' : '';
        $disabledAttr = $this->disabled ? ' disabled' : '';
        $readonlyAttr = $this->readonly ? ' readonly' : '';
        $placeholderAttr = $this->placeholder ? ' placeholder="' . $this->placeholder . '"' : '';
        $valueAttr = $this->value ? ' value="' . $this->value . '"' : '';

        return '<input type="' . $this->type . '"' . $idAttr . $nameAttr . $placeholderAttr . $valueAttr . $disabledAttr . $readonlyAttr . ' class="' . $class . '">';
    }
}


// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIInput;

// $input = new WeUIInput('text', '请输入姓名', '', false, false, 'custom-class', 'inputName', 'userName');
// echo $input->render();
