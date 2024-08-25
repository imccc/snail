# Container容器管理类

这是一个容器类 `Container`，用于实现依赖注入和管理各种服务、插件等功能。

1. **命名空间**：`Imccc\Snail\Core`。

2. **功能**：
   - **绑定服务**：通过 `bind()` 方法将接口或抽象类绑定到具体实现类，支持单例模式。
   - **解析服务**：通过 `resolve()` 方法解析服务，支持别名，自动注册未绑定的服务。
   - **自动注册**：尝试根据预定义规则自动注册服务，如接口后缀为 `Interface`。
   - **创建实例**：通过 `make()` 方法创建具体实现类的新实例，支持依赖注入。
   - **标记服务**：通过 `tag()` 方法为服务添加标记。
   - **加载插件**：通过 `loadPlugins()` 方法加载插件，并处理插件的依赖关系。
   - **注册插件**：通过 `registerPlugin()` 方法注册插件。
   - **插件依赖处理**：通过 `loadAndResolvePluginDependencies()` 方法加载并处理插件的依赖关系。
   - **获取插件实例**：通过 `getPlugin()` 方法获取插件实例。
   - **销毁实例**：通过 `destroy()` 和 `destroyAll()` 方法销毁绑定的实例。

3. **设计要点**：
   - **单例模式**：容器类采用单例模式，确保全局只有一个容器实例。
   - **依赖注入**：容器实现了依赖注入功能，支持自动解析构造函数的依赖关系。
   - **自动注册**：容器支持根据预定义规则自动注册服务，简化配置和使用。
   - **插件管理**：容器可以加载和管理插件，支持插件的依赖处理和生命周期管理。

4. **异常处理**：容器类会在必要时抛出异常，例如服务未找到、实例化失败等情况。

## 以下是该容器类提供的各个功能的用法示例，以及一个常规的完整用法示例：

### 1. 绑定服务
```php
// 使用 bind 方法将接口或抽象类绑定到具体实现类
$container->bind('LoggerService', 'FileLogger');
```

### 2. 绑定参数
```php
// 使用 bindParameter 方法将参数绑定到容器中
$container->bindParameter('debug', true);
```

### 3. 定义服务别名
```php
// 使用 alias 方法定义服务别名
$container->alias('Log', 'LoggerService');
```

### 4. 解析服务
```php
// 使用 resolve 方法获取服务实例，支持别名
$logService = $container->resolve('Log');
```

### 5. 标记服务
```php
// 使用 tag 方法给服务打上标记
$container->tag('LoggerService', 'logging');
```

### 6. 销毁实例
```php
// 使用 destroy 方法销毁指定服务的实例
$container->destroy('LoggerService');
```

### 7. 销毁所有实例
```php
// 使用 destroyAll 方法销毁所有服务的实例
$container->destroyAll();
```

### 完整用法示例
```php
use Imccc\Snail\Core\Container;

// 创建容器实例
$container = Container::getInstance();

// 绑定接口到具体实现类，并获取实例
$container->bind('SomeInterface', 'SomeImplementation');
$instance = $container->make('SomeInterface');

// 绑定为单例并获取共享实例
$container->bind('AnotherInterface', 'AnotherImplementation', true);
$sharedInstance = $container->make('AnotherInterface');

// 链式调用
$container->bind('ThirdInterface')->for('ThirdInterface')->bind('ThirdImplementation');
$thirdInstance = $container->make('ThirdInterface');

// 验证链式调用的调用顺序
try {
    $container->bind('FourthInterface')->for('FifthInterface');
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL; // 输出: The last bound service is not 'FifthInterface'.
}
```

这些示例演示了容器类的各种功能，包括绑定服务、绑定参数、定义服务别名、解析服务、标记服务、销毁实例和销毁所有实例。
