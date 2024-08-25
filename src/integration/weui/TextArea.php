<?php
namespace Imccc\Snail\Integration\Weui;

class TextArea
{
    private $placeholder;
    private $rows;
    private $name;
    private $value;
    private $extraClass;

    public function __construct($placeholder = '请输入...', $rows = 3, $name = '', $value = '', $extraClass = '')
    {
        $this->placeholder = $placeholder;
        $this->rows = $rows;
        $this->name = $name;
        $this->value = $value;
        $this->extraClass = $extraClass;
    }

    public function render()
    {
        return '
            <div class="weui-cell">
                <div class="weui-cell__bd">
                    <textarea class="weui-textarea ' . $this->extraClass . '" name="' . $this->name . '" rows="' . $this->rows . '" placeholder="' . $this->placeholder . '">' . $this->value . '</textarea>
                </div>
            </div>';
    }
}


// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUITextArea;
// $textArea = new WeUITextArea('请输入内容', 5, 'textarea1', '默认值', 'my-custom-class');
// echo $textArea->render();