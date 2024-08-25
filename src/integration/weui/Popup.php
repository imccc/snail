<?php
namespace Imccc\Snail\Integration\Weui;

class Popup
{
    private $content;
    private $extraClass;

    public function __construct($content = '', $extraClass = '')
    {
        $this->content = $content;
        $this->extraClass = $extraClass;
    }

    public function render()
    {
        return '
            <div class="weui-popup ' . $this->extraClass . '">
                <div class="weui-popup__overlay"></div>
                <div class="weui-popup__container">
                    <div class="weui-popup__content">' . $this->content . '</div>
                </div>
            </div>';
    }
}


// 使用示例
// use Imccc\Snail\Integration\Weui\WeUIPopup;
// $popup = new WeUIPopup('<div class="weui-popup__bd"><p>Popup 内容</p></div>', 'weui-popup_top');
// echo $popup->render();