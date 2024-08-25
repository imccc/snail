<?php
namespace Imccc\Snail\Integration\Weui;

class Cell
{
    private $title;
    private $content;
    private $href;
    private $isLink;
    private $extraClass;

    public function __construct($title, $content = '', $href = '#', $isLink = true, $extraClass = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->href = $href;
        $this->isLink = $isLink;
        $this->extraClass = $extraClass;
    }

    public function render()
    {
        $linkClass = $this->isLink ? 'weui-cell_access' : '';
        return '
            <div class="weui-cell ' . $linkClass . ' ' . $this->extraClass . '">
                <div class="weui-cell__hd">
                    <label class="weui-label">' . $this->title . '</label>
                </div>
                <div class="weui-cell__bd">
                    <p>' . $this->content . '</p>
                </div>
                ' . ($this->isLink ? '<div class="weui-cell__ft"></div>' : '') . '
            </div>';
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\WeUICell;
// $cell = new WeUICell('标题', '内容', '#', true, 'my-custom-class');
// echo $cell->render();
