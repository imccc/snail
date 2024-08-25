<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Alert
{
    private $type;
    private $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return '<div class="alert alert-' . htmlspecialchars($this->type) . '" role="alert">' . htmlspecialchars($this->message) . '</div>';
    }
}

// 使用示例
// $alert = new Imccc\Snail\Integration\Bootstrap\Alert('success', 'This is a success alert!');
// echo $alert->render();
