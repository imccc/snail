<?php
namespace Imccc\Snail\Integration\Weui;

class Toast
{
    private $message;
    private $duration;
    private $id;

    public function __construct($message = '操作成功', $duration = 1500, $id = '')
    {
        $this->message = $message;
        $this->duration = $duration;
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';

        return '
        <div class="weui-toast"' . $idAttr . ' style="display: none;">
            <i class="weui-icon-success-no-circle weui-icon_toast"></i>
            <p class="weui-toast__content">' . $this->message . '</p>
        </div>
        <script>
            setTimeout(function() {
                document.getElementById("' . $this->id . '").style.display = "none";
            }, ' . $this->duration . ');
        </script>';
    }
}


// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIToast;

// $toast = new WeUIToast('提交成功', 2000, 'toastId');
// echo $toast->render();
