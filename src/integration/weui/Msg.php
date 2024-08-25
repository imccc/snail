<?php
namespace Imccc\Snail\Integration\Weui;

class Msg
{
    private $type;
    private $title;
    private $description;
    private $icon;
    private $actions;

    public function __construct($type = 'success', $title = '', $description = '', $icon = '', $actions = [])
    {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->icon = $icon;
        $this->actions = $actions;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function addAction($label, $url = '#', $class = 'weui-btn_primary')
    {
        $this->actions[] = [
            'label' => $label,
            'url' => $url,
            'class' => $class
        ];
    }

    public function render()
    {
        $iconClass = $this->icon ?: $this->getDefaultIconClass();

        $html = '<div class="weui-msg">';
        $html .= '<div class="weui-msg__icon-area"><i class="' . $iconClass . '"></i></div>';
        $html .= '<div class="weui-msg__text-area">';
        $html .= '<h2 class="weui-msg__title">' . $this->title . '</h2>';
        $html .= '<p class="weui-msg__desc">' . $this->description . '</p>';
        $html .= '</div>';

        if (!empty($this->actions)) {
            $html .= '<div class="weui-msg__opr-area"><p class="weui-btn-area">';
            foreach ($this->actions as $action) {
                $html .= '<a href="' . $action['url'] . '" class="weui-btn ' . $action['class'] . '">' . $action['label'] . '</a>';
            }
            $html .= '</p></div>';
        }

        $html .= '</div>';

        return $html;
    }

    private function getDefaultIconClass()
    {
        switch ($this->type) {
            case 'success':
                return 'weui-icon-success';
            case 'error':
                return 'weui-icon-warn';
            case 'info':
            default:
                return 'weui-icon-info';
        }
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\WeUIMsg;
// $msg = new WeUIMsg('success', '操作成功', '这是一段文字描述', 'weui-icon-success', [
//     ['label' => '确定', 'url' => '#', 'class' => 'weui-btn_primary'],
//     ['label' => '取消', 'url' => '#', 'class' => 'weui-btn_default']
// ]);
// echo $msg->render();