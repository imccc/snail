<?php
namespace Imccc\Snail\Integration\Weui;

class Picker
{
    private $label;
    private $name;
    private $options = [];
    private $onChange;

    public function __construct($label = '', $name = '', $onChange = '')
    {
        $this->label = $label;
        $this->name = $name;
        $this->onChange = $onChange;
    }

    public function addOption($value, $text)
    {
        $this->options[] = ['value' => $value, 'text' => $text];
    }

    public function render()
    {
        $html = '
            <div class="weui-cell">
                <div class="weui-cell__bd">
                    <select class="weui-select" name="' . $this->name . '" onchange="' . $this->onChange . '">';

        foreach ($this->options as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['text'] . '</option>';
        }

        $html .= '
                    </select>
                </div>
            </div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIPicker;

// $picker = new WeUIPicker('选择城市', 'city', 'changeCity()');
// $picker->addOption('1', '北京');
// $picker->addOption('2', '上海');
// $picker->addOption('3', '广州');
// $picker->addOption('4', '深圳');
//echo $picker->render();