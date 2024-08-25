<?php
namespace Imccc\Snail\Integration\Weui;

class Steps
{
    private $steps = [];
    private $currentStep = 1;
    private $orientation = 'vertical'; // 默认是纵向

    public function addStep($title, $desc = '', $icon = '')
    {
        $this->steps[] = [
            'title' => $title,
            'desc' => $desc,
            'icon' => $icon,
        ];
    }

    public function setCurrentStep($step)
    {
        $this->currentStep = $step;
    }

    public function setOrientation($orientation)
    {
        if (!in_array($orientation, ['vertical', 'horizontal'])) {
            throw new \InvalidArgumentException('Orientation must be "vertical" or "horizontal".');
        }
        $this->orientation = $orientation;
    }

    public function render()
    {
        $totalSteps = count($this->steps);
        $progressPercentage = $totalSteps > 1 ? (($this->currentStep - 1) / ($totalSteps - 1)) * 100 : 0;

        $html = '<div class="weui-cells weui-cells_form">';
        $html .= '<div class="weui-cells__title">步骤</div>';
        $html .= '<div class="weui-progress">';
        $html .= '<div class="weui-progress__bar"><div class="weui-progress__inner-bar js_progress" style="width:' . $progressPercentage . '%;"></div></div>';
        $html .= '</div>';

        $html .= '<div class="weui-steps' . ($this->orientation == 'horizontal' ? ' weui-steps_horizontal' : '') . '">';

        foreach ($this->steps as $index => $step) {
            $stepIndex = $index + 1;
            $isCompleted = $stepIndex < $this->currentStep;
            $isActive = $stepIndex == $this->currentStep;

            $html .= '<div class="weui-step' . ($isCompleted ? ' weui-step_done' : '') . ($isActive ? ' weui-step_active' : '') . '">';
            $html .= '<div class="weui-step__icon">';
            if ($step['icon']) {
                $html .= '<i class="weui-icon-' . htmlspecialchars($step['icon']) . '"></i>';
            } else {
                $html .= $stepIndex;
            }
            $html .= '</div>';
            $html .= '<div class="weui-step__content">';
            $html .= '<div class="weui-step__title">' . htmlspecialchars($step['title']) . '</div>';
            if ($step['desc']) {
                $html .= '<div class="weui-step__desc">' . htmlspecialchars($step['desc']) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\Steps;

// // 创建一个 Steps 组件
// $steps = new Steps();

// // 添加步骤
// $steps->addStep('步骤一', '描述信息');
// $steps->addStep('步骤二', '描述信息');
// $steps->addStep('步骤三', '描述信息');

// // 设置当前步骤
// $steps->setCurrentStep(2);

// // 设置为横向
// $steps->setOrientation('horizontal');

// // 渲染 Steps 组件
// echo $steps->render();
