<?php
namespace Imccc\Snail\Integration\Weui;

class HalfScreenDialog
{
    private $title;
    private $content;
    private $buttons = [];
    private $style;

    const STYLES = [
        'default' => 'weui-half-screen-dialog_default',
        'confirm' => 'weui-half-screen-dialog_confirm',
        'warn' => 'weui-half-screen-dialog_warn',
        'success' => 'weui-half-screen-dialog_success',
        'info' => 'weui-half-screen-dialog_info',
    ];

    public function __construct($title = '', $content = '', $style = 'default')
    {
        $this->title = $title;
        $this->content = $content;
        $this->setStyle($style);
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function addButton($label, $type = 'default', $action = '#')
    {
        $this->buttons[] = [
            'label' => $label,
            'type' => $type,
            'action' => $action,
        ];
        return $this;
    }

    public function setStyle($style)
    {
        if (!array_key_exists($style, self::STYLES)) {
            throw new \InvalidArgumentException("Invalid style: $style");
        }
        $this->style = self::STYLES[$style];
        return $this;
    }

    public function render()
    {
        $html = '<div class="weui-half-screen-dialog ' . $this->style . '">';
        $html .= '<div class="weui-half-screen-dialog__hd"><h3>' . $this->title . '</h3></div>';
        $html .= '<div class="weui-half-screen-dialog__bd">' . $this->content . '</div>';
        $html .= '<div class="weui-half-screen-dialog__ft">';
        foreach ($this->buttons as $button) {
            $html .= '<a href="' . $button['action'] . '" class="weui-btn weui-btn_' . $button['type'] . '">' . $button['label'] . '</a>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\HalfScreenDialog;

// $dialog = new HalfScreenDialog('确认操作', '您确定要删除此项吗？', 'warn');
// $dialog->addButton('取消', 'default', '#')
//        ->addButton('确定', 'warn', '#');

// echo $dialog->render();
