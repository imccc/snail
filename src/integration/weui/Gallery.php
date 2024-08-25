<?php
namespace Imccc\Snail\Integration\Weui;

class Gallery
{
    private $images = [];
    private $extraClass;

    public function __construct($extraClass = '')
    {
        $this->extraClass = $extraClass;
    }

    public function addImage($url)
    {
        $this->images[] = $url;
    }

    public function render()
    {
        $html = '<div class="weui-gallery ' . $this->extraClass . '">';
        foreach ($this->images as $image) {
            $html .= '
                <span class="weui-gallery__img" style="background-image:url(' . $image . ')"></span>
                <div class="weui-gallery__opr">
                    <a href="javascript:" class="weui-gallery__del">
                        <i class="weui-icon-delete"></i>
                    </a>
                </div>';
        }
        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\WeUIGallery;
// $gallery = new WeUIGallery('my-custom-class');
// $gallery->addImage('https://img.alicdn.com/tps/i1/TB1_Lw9MpXXXXXKXpXXXXXXXXXX-640-640.jpg_400x400q75.jpg');
// $gallery->addImage('https://img.alicdn.com/tps/i1/TB1_Lw9MpXXXXXKXpXXXXXXXXXX-640-640.jpg_400x400q75.jpg');
// echo $gallery->render();