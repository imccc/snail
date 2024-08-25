<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Accordion
{
    private $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function render()
    {
        $html = '<div class="accordion" id="accordionExample">';

        foreach ($this->items as $index => $item) {
            $html .= '<div class="accordion-item">';
            $html .= '<h2 class="accordion-header" id="heading' . $index . '">';
            $html .= '<button class="accordion-button' . ($index > 0 ? ' collapsed' : '') . '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '" aria-expanded="' . ($index === 0 ? 'true' : 'false') . '" aria-controls="collapse' . $index . '">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</button>';
            $html .= '</h2>';
            $html .= '<div id="collapse' . $index . '" class="accordion-collapse collapse' . ($index === 0 ? ' show' : '') . '" aria-labelledby="heading' . $index . '" data-bs-parent="#accordionExample">';
            $html .= '<div class="accordion-body">' . htmlspecialchars($item['content']) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $accordion = new Imccc\Snail\Integration\Bootstrap\Accordion([
//     ['title' => 'Accordion Item #1', 'content' => 'This is the first item\'s accordion body.'],
//     ['title' => 'Accordion Item #2', 'content' => 'This is the second item\'s accordion body.'],
//     ['title' => 'Accordion Item #3', 'content' => 'This is the third item\'s accordion body.']
// ]);
// echo $accordion->render();
