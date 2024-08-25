<?php
namespace Imccc\Snail\Integration\Bootstrap;

class Tabs
{
    private static $instances = [];
    private $containerId;
    private $tabs = [];
    private $activeTab;

    public function __construct($containerId, $activeTab = 1)
    {
        $this->containerId = $containerId;
        $this->activeTab = $activeTab;

        // 检查是否已经存在相同的容器ID的实例
        if (isset(self::$instances[$containerId])) {
            throw new \Exception("Tabs with container ID '$containerId' already exist.");
        }
        
        self::$instances[$containerId] = $this;
    }

    public static function getInstance($containerId)
    {
        if (isset(self::$instances[$containerId])) {
            return self::$instances[$containerId];
        }

        throw new \Exception("No Tabs instance found for container ID '$containerId'.");
    }

    public function add($title, $content)
    {
        $this->tabs[] = ['title' => $title, 'content' => $content];
    }

    public function addMultiple(array $tabs)
    {
        foreach ($tabs as $tab) {
            if (isset($tab['title']) && isset($tab['content'])) {
                $this->add($tab['title'], $tab['content']);
            } else {
                throw new \InvalidArgumentException('Each tab must have a title and content.');
            }
        }
    }

    public function render()
    {
        $html = '<div class="container">';
        $html .= '<ul class="nav nav-tabs" id="pills-tab-' . $this->containerId . '" role="tablist">';

        foreach ($this->tabs as $index => $tab) {
            $isActive = $this->activeTab === ($index + 1) ? ' active' : '';
            $html .= '<li class="nav-item" role="presentation">';
            $html .= '<a class="nav-link' . $isActive . '" id="pills-tab-' . $this->containerId . '-' . ($index + 1) . '" data-bs-toggle="pill" href="#pills-' . $this->containerId . '-' . ($index + 1) . '" role="tab" aria-controls="pills-' . $this->containerId . '-' . ($index + 1) . '" aria-selected="' . ($isActive ? 'true' : 'false') . '">' . $tab['title'] . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content" id="pills-tabContent-' . $this->containerId . '">';

        foreach ($this->tabs as $index => $tab) {
            $isActive = $this->activeTab === ($index + 1) ? ' show active' : '';
            $html .= '<div class="tab-pane fade' . $isActive . '" id="pills-' . $this->containerId . '-' . ($index + 1) . '" role="tabpanel" aria-labelledby="pills-tab-' . $this->containerId . '-' . ($index + 1) . '">';
            $html .= $tab['content'];
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

// 使用示例
/** 
以下是如何使用重构后的 `Tabs` 组件的示例，包括如何创建实例、添加单个选项卡、批量添加选项卡，以及渲染结果。

### 使用示例

#### 1. 创建实例并添加单个选项卡

```php
<?php
require 'path/to/Tabs.php'; // 请根据实际路径引入 Tabs 组件

use Imccc\Snail\Integration\Bootstrap\Tabs;

// 创建一个新的 Tabs 实例
$tabs = new Tabs('exampleContainer', 1);

// 添加单个选项卡
$tabs->add('Tab 1', 'Content for Tab 1');
$tabs->add('Tab 2', 'Content for Tab 2');

// 渲染 Tabs 组件
echo $tabs->render();
```

#### 2. 创建实例并批量添加选项卡

```php
<?php
require 'path/to/Tabs.php'; // 请根据实际路径引入 Tabs 组件

use Imccc\Snail\Integration\Bootstrap\Tabs;

// 创建一个新的 Tabs 实例
$tabs = new Tabs('exampleContainer', 1);

// 批量添加选项卡
$tabs->addMultiple([
    ['title' => 'Tab 1', 'content' => 'Content for Tab 1'],
    ['title' => 'Tab 2', 'content' => 'Content for Tab 2'],
    ['title' => 'Tab 3', 'content' => 'Content for Tab 3'],
]);

// 渲染 Tabs 组件
echo $tabs->render();
```

#### 3. 使用相同容器 ID 添加更多选项卡

如果你已经有一个实例，可以通过 `getInstance()` 获取，并添加更多选项卡：

```php
<?php
require 'path/to/Tabs.php'; // 请根据实际路径引入 Tabs 组件

use Imccc\Snail\Integration\Bootstrap\Tabs;

// 创建一个新的 Tabs 实例
$tabs = new Tabs('exampleContainer', 1);

// 添加一些选项卡
$tabs->add('Tab 1', 'Content for Tab 1');
$tabs->add('Tab 2', 'Content for Tab 2');

// 渲染 Tabs 组件
echo $tabs->render();

// 获取已经存在的 Tabs 实例并添加更多选项卡
$existingTabs = Tabs::getInstance('exampleContainer');
$existingTabs->add('Tab 3', 'Content for Tab 3');

// 渲染更新后的 Tabs 组件
echo $existingTabs->render();
```

#### 4. 处理不同容器 ID 的选项卡

```php
<?php
require 'path/to/Tabs.php'; // 请根据实际路径引入 Tabs 组件

use Imccc\Snail\Integration\Bootstrap\Tabs;

// 创建不同容器 ID 的 Tabs 实例
$tabs1 = new Tabs('container1', 1);
$tabs1->add('Tab A', 'Content for Tab A');

$tabs2 = new Tabs('container2', 1);
$tabs2->add('Tab X', 'Content for Tab X');

// 渲染 Tabs 组件
echo $tabs1->render();
echo $tabs2->render();
```

在这些示例中：

- `new Tabs('exampleContainer', 1)` 创建一个新的 Tabs 实例，其中 `exampleContainer` 是容器 ID，`1` 是默认激活的选项卡。
- `add()` 方法用于添加单个选项卡。
- `addMultiple()` 方法用于批量添加选项卡。
- `Tabs::getInstance('exampleContainer')` 用于获取已存在的 Tabs 实例并在其中添加更多选项卡。
- 渲染后的结果会根据 Bootstrap 5.3.x 的 Tab 组件规范生成 HTML。

根据您的实际需求，您可以将这些示例中的代码进行调整。
*/