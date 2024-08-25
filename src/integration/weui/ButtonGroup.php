<?php
namespace Imccc\Snail\Integration\WeUI;

class ButtonGroup
{
    private $buttons = [];

    public function addButton($type, $label, $onclick = '', $disabled = false)
    {
        $this->buttons[] = [
            'type' => $type,
            'label' => $label,
            'onclick' => $onclick,
            'disabled' => $disabled,
        ];
    }

    public function render()
    {
        $html = '<div class="weui-btn-area">';
        foreach ($this->buttons as $button) {
            $disabled = $button['disabled'] ? 'weui-btn_disabled' : '';
            $html .= '<a href="javascript:;" class="weui-btn weui-btn_' . $button['type'] . ' ' . $disabled . '" onclick="' . $button['onclick'] . '">' . $button['label'] . '</a>';
        }
        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\ButtonGroup;

// $buttonGroup = new ButtonGroup();
// $buttonGroup->addButton('primary', '确定', 'alert("确定按钮被点击了")');
// $buttonGroup->addButton('default', '取消', 'alert("取消按钮被点击了")', true);
// echo $buttonGroup->render();