<?php
namespace Imccc\Snail\Integration\WeUI;

class InformationBar
{
    private $message;
    private $type;
    private $extraClass;

    public function __construct($message = '', $type = 'info', $extraClass = '')
    {
        $this->message = $message;
        $this->type = $type;
        $this->extraClass = $extraClass;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setExtraClass($extraClass)
    {
        $this->extraClass = $extraClass;
    }

    public function render()
    {
        $classMap = [
            'info' => 'weui-toptips_info',
            'success' => 'weui-toptips_success',
            'warning' => 'weui-toptips_warn',
            'error' => 'weui-toptips_error'
        ];

        $class = isset($classMap[$this->type]) ? $classMap[$this->type] : $classMap['info'];
        $html = '<div class="weui-toptips ' . $class . ' ' . $this->extraClass . '">' . $this->message . '</div>';

        return $html;
    }
}

//  使用示例
// use Imccc\Snail\Integration\WeUI\InformationBar;

// // 创建 InformationBar 实例
// $infoBar = new InformationBar('这是一个信息提示！', 'info');

// // 渲染并输出 Information Bar
// echo $infoBar->render();

// // 创建成功提示
// $successBar = new InformationBar('操作成功！', 'success');
// echo $successBar->render();

// // 创建警告提示
// $warningBar = new InformationBar('请注意您的操作。', 'warning');
// echo $warningBar->render();

// // 创建错误提示
// $errorBar = new InformationBar('操作失败，请重试。', 'error');
// echo $errorBar->render();
