<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Table
{
    private $headers;
    private $rows;
    private $striped;
    private $bordered;
    private $hover;
    private $small;
    private $responsive;
    private $dark;
    private $caption;

    public function __construct($headers = [], $rows = [], $striped = false, $bordered = false, $hover = false, $small = false, $responsive = false, $dark = false, $caption = '')
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->striped = $striped;
        $this->bordered = $bordered;
        $this->hover = $hover;
        $this->small = $small;
        $this->responsive = $responsive;
        $this->dark = $dark;
        $this->caption = $caption;
    }

    public function render()
    {
        $class = 'table';
        if ($this->striped) {
            $class .= ' table-striped';
        }
        if ($this->bordered) {
            $class .= ' table-bordered';
        }
        if ($this->hover) {
            $class .= ' table-hover';
        }
        if ($this->small) {
            $class .= ' table-sm';
        }
        if ($this->dark) {
            $class .= ' table-dark';
        }

        $html = $this->responsive ? '<div class="table-responsive">' : '';
        $html .= '<table class="' . $class . '">';
        if (!empty($this->caption)) {
            $html .= '<caption>' . htmlspecialchars($this->caption) . '</caption>';
        }
        $html .= '<thead><tr>';
        foreach ($this->headers as $header) {
            $html .= '<th scope="col">' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        foreach ($this->rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= $this->responsive ? '</div>' : '';

        return $html;
    }
}

// 使用示例
// $table = new Imccc\Snail\Integration\Bootstrap\Table(
//     ['Name', 'Age', 'City'],
//     [
//         ['John Doe', 30, 'New York'],
//         ['Jane Smith', 25, 'Los Angeles'],
//         ['Sam Johnson', 22, 'Chicago']
//     ],
//     true,  // Striped
//     true,  // Bordered
//     true,  // Hover
//     false, // Small
//     true,  // Responsive
//     true,  // Dark
//     'List of Users' // Caption
// );

// echo $table->render();
