<?php
namespace Imccc\Snail\Integration\Weui;

class Actionsheet
{
    private $items;
    private $title;
    private $id;

    public function __construct($items = [], $title = '', $id = '')
    {
        $this->items = $items;
        $this->title = $title;
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        $html = '<div class="weui-actionsheet"' . $idAttr . '>';
        if ($this->title) {
            $html .= '<div class="weui-actionsheet__title"><p>' . $this->title . '</p></div>';
        }
        $html .= '<div class="weui-actionsheet__menu">';
        foreach ($this->items as $item) {
            $html .= '<div class="weui-actionsheet__cell">' . $item . '</div>';
        }
        $html .= '</div>';
        $html .= '<div class="weui-actionsheet__action"><div class="weui-actionsheet__cell">取消</div></div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIActionsheet;

// $actionsheet = new WeUIActionsheet(['选项1', '选项2', '选项3'], '标题', 'actionsheet1');
// echo $actionsheet->render();