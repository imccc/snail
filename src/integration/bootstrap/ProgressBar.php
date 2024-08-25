<?php
namespace Imccc\Snail\Integration\Bootstrap;

class ProgressBar
{
    private $percentage;
    private $label;

    public function __construct($percentage, $label = '')
    {
        $this->percentage = $percentage;
        $this->label = $label;
    }

    public function render()
    {
        $html = '<div class="progress">';
        $html .= '<div class="progress-bar" role="progressbar" style="width: ' . htmlspecialchars($this->percentage) . '%" aria-valuenow="' . htmlspecialchars($this->percentage) . '" aria-valuemin="0" aria-valuemax="100">' . htmlspecialchars($this->label) . '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $progressBar = new Imccc\Snail\Integration\Bootstrap\ProgressBar(75, '75% Complete');
// echo $progressBar->render();
