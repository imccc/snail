<?php
namespace Imccc\Snail\Integration\Weui;

class Tabbar
{
    private $tabs = [];
    private $extraClass;
    private $activeIndex = 0;

    public function __construct($extraClass = '')
    {
        $this->extraClass = $extraClass;
    }

    public function addTab($label, $icon = '', $link = '#', $active = false)
    {
        $this->tabs[] = [
            'label' => $label,
            'icon' => $icon,
            'link' => $link,
            'active' => $active,
        ];

        if ($active) {
            $this->activeIndex = count($this->tabs) - 1;
        }
    }

    public function setActiveTab($index)
    {
        $this->activeIndex = $index;
        foreach ($this->tabs as &$tab) {
            $tab['active'] = false;
        }
        $this->tabs[$index]['active'] = true;
    }

    public function render()
    {
        $html = '<div class="weui-tabbar ' . $this->extraClass . '">';
        
        foreach ($this->tabs as $index => $tab) {
            $activeClass = $tab['active'] ? 'weui-bar__item_on' : '';
            $html .= '
                <a href="' . $tab['link'] . '" class="weui-tabbar__item ' . $activeClass . '">
                    ' . ($tab['icon'] ? '<span style="display:inline-block;position:relative;"><img src="' . $tab['icon'] . '" class="weui-tabbar__icon"></span>' : '') . '
                    <p class="weui-tabbar__label">' . $tab['label'] . '</p>
                </a>';
        }

        $html .= '</div>';
        return $html;
    }
}

// 使用示例
// use Imccc\Snail\Integration\WeUI\Tabbar;

// // 创建 Tabbar 实例
// $tabbar = new Tabbar();

// // 添加标签项
// $tabbar->addTab('首页', 'images/icon_home.png', '/home', true);
// $tabbar->addTab('发现', 'images/icon_discover.png', '/discover');
// $tabbar->addTab('消息', 'images/icon_message.png', '/message');
// $tabbar->addTab('我', 'images/icon_profile.png', '/profile');

// // 渲染并输出 Tabbar
// echo $tabbar->render();
