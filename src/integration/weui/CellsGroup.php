<?php
namespace Imccc\Snail\Integration\Weui;

class CellsGroup
{
    private $cells = [];
    private $title;
    private $extraClass;

    public function __construct($title = '', $extraClass = '')
    {
        $this->title = $title;
        $this->extraClass = $extraClass;
    }

    public function addCell(Cell $cell)
    {
        $this->cells[] = $cell;
    }

    public function render()
    {
        $html = '';
        if ($this->title) {
            $html .= '<div class="weui-cells__title">' . $this->title . '</div>';
        }
        $html .= '<div class="weui-cells ' . $this->extraClass . '">';
        foreach ($this->cells as $cell) {
            $html .= $cell->render();
        }
        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\Weui\WeUICellsGroup;
// use Imccc\Snail\Integration\Weui\WeUICell;
// $cellsGroup = new WeUICellsGroup('标题');
// $cell1 = new WeUICell('标题1', '内容1', 'icon-info', 'http://www.baidu.com', 'http://www.baidu.com', 'http://www.baidu.com', 'http://www.baidu.com');
// $cell2 = new WeUICell('标题2', '内容2', 'icon-info', 'http://www.baidu.com', 'http://www.baidu.com', 'http://www.baidu.com', 'http://www.baidu.com');
// $cellsGroup->addCell($cell1);
// $cellsGroup->addCell($cell2);
// echo $cellsGroup->render();