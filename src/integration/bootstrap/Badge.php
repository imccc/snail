<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Badge
{
    private $text;
    private $type;
    private $pill;

    public function __construct($text, $type = 'primary', $pill = false)
    {
        $this->text = $text;
        $this->type = $type;
        $this->pill = $pill;
    }

    public function render()
    {
        $html = '<span class="badge bg-' . htmlspecialchars($this->type) . ($this->pill ? ' rounded-pill' : '') . '">' . htmlspecialchars($this->text) . '</span>';

        return $html;
    }
}

// 使用示例
// $badge = new Imccc\Snail\Integration\Bootstrap\Badge('New', 'success', true);
// echo $badge->render();
