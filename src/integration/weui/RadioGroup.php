<?php
namespace Imccc\Snail\Integration\Weui;

class RadioGroup
{
    private $name;
    private $options;
    private $selected;

    public function __construct($name, $options = [], $selected = '')
    {
        $this->name = $name;
        $this->options = $options;
        $this->selected = $selected;
    }

    public function addOption($value, $label)
    {
        $this->options[] = [
            'value' => $value,
            'label' => $label,
        ];
    }

    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    public function render()
    {
        $html = '<div class="weui-cells weui-cells_radio">';
        foreach ($this->options as $option) {
            $checked = $option['value'] == $this->selected ? 'checked' : '';
            $html .= '
                <label class="weui-cell weui-check__label">
                    <div class="weui-cell__bd">
                        <p>' . $option['label'] . '</p>
                    </div>
                    <div class="weui-cell__ft">
                        <input type="radio" class="weui-check" name="' . $this->name . '" value="' . $option['value'] . '" ' . $checked . '>
                        <span class="weui-icon-checked"></span>
                    </div>
                </label>';
        }
        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIRadioGroup;

// $radioGroup = new WeUIRadioGroup('myRadioGroup', [
//     ['value' => 'option1', 'label' => 'Option 1'],
//     ['value' => 'option2', 'label' => 'Option 2'],
//     ['value' => 'option3', 'label' => 'Option 3'],
// ]);