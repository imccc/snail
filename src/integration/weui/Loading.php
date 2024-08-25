<?php
namespace Imccc\Snail\Integration\Weui;

class Loading
{
    private $id;

    public function __construct($id = '')
    {
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        return '
        <div class="weui-loading"' . $idAttr . '>
            <i class="weui-loading__icon"></i>
            <p class="weui-toast__content">加载中...</p>
        </div>';
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUILoading;
// $loading = new WeUILoading('myLoadingId');
// echo $loading->render();