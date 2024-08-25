<?php
namespace Imccc\Snail\Integration\Weui;

class Dialog
{
    private $title;
    private $content;
    private $buttons;
    private $id;

    public function __construct($title, $content, $buttons = [], $id = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->buttons = $buttons;
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        $html = '<div class="weui-dialog"' . $idAttr . '>';
        $html .= '<div class="weui-dialog__hd"><strong class="weui-dialog__title">' . $this->title . '</strong></div>';
        $html .= '<div class="weui-dialog__bd">' . $this->content . '</div>';
        $html .= '<div class="weui-dialog__ft">';
        foreach ($this->buttons as $button) {
            $html .= '<a href="javascript:;" class="weui-dialog__btn ' . $button['class'] . '">' . $button['label'] . '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}


// 使用示例
// use Imccc\Snail\Integration\Weui\Dialog;

// $dialog = new Dialog('提示', '操作成功', [
//     ['label' => '确定', 'class' => 'weui-dialog__btn_primary'],
//     ['label' => '取消', 'class' => 'weui-dialog__btn_default']
// ], 'dialogId');

// echo $dialog->render();
