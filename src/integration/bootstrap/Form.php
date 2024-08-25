<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Form
{
    private $action;
    private $method;
    private $inputs;

    public function __construct($action, $method = 'post', $inputs = [])
    {
        $this->action = $action;
        $this->method = $method;
        $this->inputs = $inputs;
    }

    public function render()
    {
        $html = '<form action="' . htmlspecialchars($this->action) . '" method="' . htmlspecialchars($this->method) . '">';
        foreach ($this->inputs as $input) {
            $html .= '<div class="mb-3">';
            $html .= '<label for="' . htmlspecialchars($input['id']) . '" class="form-label">' . htmlspecialchars($input['label']) . '</label>';
            $html .= '<input type="' . htmlspecialchars($input['type']) . '" class="form-control" id="' . htmlspecialchars($input['id']) . '" name="' . htmlspecialchars($input['name']) . '" value="' . htmlspecialchars($input['value'] ?? '') . '">';
            $html .= '</div>';
        }
        $html .= '<button type="submit" class="btn btn-primary">Submit</button>';
        $html .= '</form>';

        return $html;
    }
}

// 使用示例
// $form = new Imccc\Snail\Integration\Bootstrap\Form('/submit', 'post', [
//     ['type' => 'text', 'id' => 'name', 'name' => 'name', 'label' => 'Name'],
//     ['type' => 'email', 'id' => 'email', 'name' => 'email', 'label' => 'Email']
// ]);
// echo $form->render();
