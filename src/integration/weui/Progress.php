<?php
namespace Imccc\Snail\Integration\Weui;

class Progress
{
    private $percentage;
    private $id;
    private $showCancel;

    public function __construct($percentage = 0, $showCancel = false, $id = '')
    {
        $this->percentage = $percentage;
        $this->showCancel = $showCancel;
        $this->id = $id;
    }

    public function render()
    {
        $idAttr = $this->id ? ' id="' . $this->id . '"' : '';
        $cancelButton = $this->showCancel ? '<a href="javascript:;" class="weui-progress__opr"><i class="weui-icon-cancel"></i></a>' : '';

        return '
        <div class="weui-progress"' . $idAttr . '>
            <div class="weui-progress__bar">
                <div class="weui-progress__inner-bar" style="width: ' . $this->percentage . '%;"></div>
            </div>
            ' . $cancelButton . '
        </div>';
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\WeUIProgress;

// $progress = new WeUIProgress(50, true, 'progress1');
// echo $progress->render();
