<?php
namespace Imccc\Snail\Integration\Weui;

class Select
{
    private $name;
    private $options;
    private $selected;
    private $disabled;

    public function __construct($name, $options = [], $selected = '', $disabled = false)
    {
        $this->name = $name;
        $this->options = $options;
        $this->selected = $selected;
        $this->disabled = $disabled;
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
        $disabled = $this->disabled ? 'disabled' : '';
        $html = '
            <div class="weui-cell">
                <div class="weui-cell__bd">
                    <select class="weui-select" name="' . $this->name . '" ' . $disabled . '>';
        
        foreach ($this->options as $option) {
            $selected = $option['value'] == $this->selected ? 'selected' : '';
            $html .= '<option value="' . $option['value'] . '" ' . $selected . '>' . $option['label'] . '</option>';
        }
        
        $html .= '</select>
                </div>
            </div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUISelect;

// $select = new WeUISelect('mySelect', [
//     ['value' => '1', 'label' => 'Option 1'],
//     ['value' => '2', 'label' => 'Option 2'],
//     ['value' => '3', 'label' => 'Option 3'],
// ], '2');
// echo$select->render();