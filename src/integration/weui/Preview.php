<?php
namespace Imccc\Snail\Integration\Weui;

class Preview
{
    private $items = [];
    private $actions = [];

    public function addItem($label, $value, $extraClass = '')
    {
        $this->items[] = [
            'label' => $label,
            'value' => $value,
            'extraClass' => $extraClass,
        ];
    }

    public function addAction($action)
    {
        $this->actions[] = $action;
    }

    public function render()
    {
        $html = '<div class="weui-form-preview">';

        // 渲染预览项
        foreach ($this->items as $item) {
            $html .= '<div class="weui-form-preview__item ' . htmlspecialchars($item['extraClass']) . '">';
            $html .= '<label class="weui-form-preview__label">' . htmlspecialchars($item['label']) . '</label>';
            $html .= '<span class="weui-form-preview__value">' . htmlspecialchars($item['value']) . '</span>';
            $html .= '</div>';
        }

        // 渲染操作按钮
        if (!empty($this->actions)) {
            $html .= '<div class="weui-form-preview__ft">';
            foreach ($this->actions as $action) {
                $html .= '<a href="#" class="weui-form-preview__btn weui-form-preview__btn_primary">' . htmlspecialchars($action) . '</a>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\Preview;

// // 创建一个 Preview 组件
// $preview = new Preview();

// // 添加预览项
// $preview->addItem('商品名称', '商品1');
// $preview->addItem('商品价格', '￥50');
// $preview->addItem('商品数量', '2');

// // 添加操作按钮
// $preview->addAction('提交订单');
// $preview->addAction('修改信息');

// // 渲染 Preview 组件
// echo $preview->render();
