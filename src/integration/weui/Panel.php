<?php
namespace Imccc\Snail\Integration\Weui;

class Panel
{
    private $title;
    private $content;
    private $footer;
    private $actions;

    public function __construct($title = '', $content = '', $footer = '', $actions = [])
    {
        $this->title = $title;
        $this->content = $content;
        $this->footer = $footer;
        $this->actions = $actions;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function addAction($action)
    {
        $this->actions[] = $action;
    }

    public function render()
    {
        $html = '<div class="weui-panel">';

        if ($this->title) {
            $html .= '<div class="weui-panel__hd">' . htmlspecialchars($this->title) . '</div>';
        }

        if ($this->content) {
            $html .= '<div class="weui-panel__bd">' . $this->content . '</div>';
        }

        if ($this->footer) {
            $html .= '<div class="weui-panel__ft">' . htmlspecialchars($this->footer) . '</div>';
        }

        if (!empty($this->actions)) {
            $html .= '<div class="weui-panel__actions">';
            foreach ($this->actions as $action) {
                $html .= '<div class="weui-panel__action">' . $action . '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}


// 使用示例
// use Imccc\Snail\Integration\WeUI\Panel;

// // 创建一个 Panel 组件
// $panel = new Panel(
//     '订单信息',
//     '<div>商品1：￥50</div><div>商品2：￥30</div>',
//     '共计：￥80'
// );

// // 添加操作按钮
// $panel->addAction('<a href="#">查看详情</a>');
// $panel->addAction('<a href="#">取消订单</a>');

// // 渲染 Panel 组件
// echo $panel->render();
