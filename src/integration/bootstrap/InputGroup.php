<?php
namespace Imccc\Snail\Integration\Bootstrap;

class InputGroup
{
    private $prepend;
    private $inputType;
    private $inputId;
    private $inputName;
    private $inputValue;
    private $append;

    public function __construct($prepend, $inputType, $inputId, $inputName, $inputValue = '', $append = '')
    {
        $this->prepend = $prepend;
        $this->inputType = $inputType;
        $this->inputId = $inputId;
        $this->inputName = $inputName;
        $this->inputValue = $inputValue;
        $this->append = $append;
    }

    public function render()
    {
        $html = '<div class="input-group">';
        if ($this->prepend) {
            $html .= '<span class="input-group-text">' . htmlspecialchars($this->prepend) . '</span>';
        }
        $html .= '<input type="' . htmlspecialchars($this->inputType) . '" class="form-control" id="' . htmlspecialchars($this->inputId) . '" name="' . htmlspecialchars($this->inputName) . '" value="' . htmlspecialchars($this->inputValue) . '">';
        if ($this->append) {
            $html .= '<span class="input-group-text">' . htmlspecialchars($this->append) . '</span>';
        }
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
// $inputGroup = new Imccc\Snail\Integration\Bootstrap\InputGroup('$', 'text', 'price', 'price', '', '.00');
// echo $inputGroup->render();
