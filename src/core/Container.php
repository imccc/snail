<?php
/**
 * 容器类
 *
 * @package Imccc\Snail
 * @since 0.0.1
 * @author Imccc
 * @copyright Copyright (c) 2024 Imccc.
 */

namespace Imccc\Snail\Core;

use Closure;
use ReflectionClass;

class Container
{
    private static $instance;

    protected $plugins = []; // 存储插件信息
    protected $bindings = []; // 存储绑定的服务信息
    protected $aliases = []; // 存储服务别名信息
    protected $lastBound = ''; // 最后绑定的接口或抽象类
    protected $_debugInfo = []; // 调试信息

    use HandleExceptionTrait;
    // 获取容器实例的静态方法
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // 绑定调试信息
    public function addDebugInfo($key, $value, $method = 'def')
    {
        $this->_debugInfo[$method][$key] = $value;
    }

    // 获取调试信息
    public function getDebugInfo($method = 'def')
    {
        if ($method == 'def') {
            return $this->_debugInfo;
        }
        return $this->_debugInfo[$method];
    }

    // 私有构造函数以确保只能通过 getInstance 方法获取实例
    private function __construct()
    {
    }

    // 克隆方法私有化，防止外部克隆对象
    private function __clone()
    {
    }

    // 反序列化方法私有化，防止外部反序列化对象
    private function __wakeup()
    {
    }

    /**
     * 绑定接口或抽象类到具体实现类
     *
     * @param string $abstract 接口或抽象类名
     * @param mixed $concrete 具体实现类名、闭包或实例
     * @param bool $shared 是否共享实例
     * @return Container 当前容器实例
     * @throws Exception 如果提供的具体实现类类型无效
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): self
    {
        // 确保提供的具体实现类类型有效
        if (!is_string($concrete) && !$concrete instanceof Closure && !is_object($concrete)) {
            throw new \Exception("Invalid concrete type provided for [$abstract].");
        }

        // 存储绑定信息
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract, // 如果没有提供具体实现类，则默认与接口名一致
            'shared' => $shared, // 是否共享实例
            'instance' => null, // 共享实例
        ];

        // 记录最后绑定的接口或抽象类
        $this->lastBound = $abstract;

        return $this;
    }

    /**
     * 获取所有服务
     *
     * @return array 所有服务
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * 获取服务实例，支持别名
     *
     * @param string $abstract 服务名称或别名
     * @return mixed 服务实例
     * @throws Exception 如果服务不存在或解析失败时抛出异常
     */
    public function resolve(string $abstract)
    {
        // 如果依赖是容器本身，则跳过解析步骤
        if ($abstract === self::class) {
            return $this;
        }
        // 如果是别名，则转换为对应的服务名称
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        // 如果服务未注册，则尝试根据预定义的规则自动注册
        if (!isset($this->bindings[$abstract])) {
            $this->attemptAutoRegister($abstract);
            // 如果仍然未注册，则抛出异常
            if (!isset($this->bindings[$abstract])) {
                throw new \Exception("Service '$abstract' not found.");
            }

        }
        // 返回服务实例
        return $this->make($abstract);
    }

    /**
     * 尝试根据预定义规则自动注册服务
     *
     * @param string $abstract 请求的服务名称或接口
     * @throws Exception 如果自动注册失败
     */
    protected function attemptAutoRegister(string $abstract)
    {
        // 假设约定接口后缀为 Interface，则自动注册时可以这样处理
        $serviceNamespace = 'Imccc\Snail\Services'; // 假设服务类的命名空间
        $servicePath = dirname(__DIR__) . '/services'; // 服务类所在的目录

        $interfaceFile = $servicePath . '/' . $abstract . 'Interface.php'; // 接口文件路径
        $serviceFile = $servicePath . '/' . $abstract . '.php'; // 实体类文件路径

        if (file_exists($interfaceFile)) {
            $concreteClass = $abstract;
            if (class_exists($concreteClass)) {
                $this->bind($abstract, $concreteClass);
                // 添加别名，以不带命名空间的服务名为准
                $this->alias(basename($abstract), $abstract);
            } else {
                // 如果实体类不存在，则抛出异常
                throw new \Exception("Automatic registration failed for service: $abstract. Interface file exists, but class $concreteClass does not exist.");
            }
        } elseif (file_exists($serviceFile)) {
            // 如果不存在接口文件但存在实体类文件，则直接将服务文件视为实体类注册
            // $this->bind($abstract, $serviceNamespace . '\\' . $abstract);

            $this->bind($abstract, $serviceNamespace . '\\' . $abstract);
            // 添加别名，以不带命名空间的服务名为准
            $this->alias(basename($abstract), $abstract);
        } else {
            // 如果都不存在，则抛出异常
            throw new \Exception("Automatic registration failed for service: $abstract. Neither interface nor service class found.");
        }

    }

    /**
     * 获取绑定的实例
     *
     * @param string $abstract 接口或抽象类名
     * @return mixed 具体实现类的实例
     * @throws Exception 当绑定不存在时抛出异常
     */
    public function make(string $abstract)
    {
        // 检查绑定是否存在
        if (!isset($this->bindings[$abstract])) {
            throw new \Exception("Service '$abstract' not found.");
        }

        // 获取绑定信息
        $binding = $this->bindings[$abstract];

        // 检查是否存在共享实例，如果存在且是共享实例，则直接返回
        $instance = ($binding['shared'] && $binding['instance'] !== null)
        ? $binding['instance']
        : null;

        // 如果已经存在实例，则直接返回该实例
        if ($instance === null && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 解析依赖并创建实例
        $concrete = $binding['concrete'];
        $instance = $this->build($concrete);

        // 如果是共享实例，则保存到 bindings 中
        if ($binding['shared']) {
            $this->bindings[$abstract]['instance'] = $instance;
        }

        // 如果不是共享实例，则保存到 instances 中
        if (!$binding['shared']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * 创建具体实现类的新实例
     *
     * @param mixed $concrete 具体实现类名、闭包或实例
     * @return mixed 具体实现类的实例
     * @throws Exception 当实例化失败时抛出异常
     */
    protected function build($concrete)
    {
        // 如果是闭包，则调用闭包
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // 否则尝试实例化具体实现类
        $reflector = new ReflectionClass($concrete);

        // 检查是否可实例化
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }

        // 获取构造函数参数
        $constructor = $reflector->getConstructor();

        // 如果没有构造函数，则直接实例化
        if ($constructor === null) {
            return new $concrete;
        }

        // 否则解析构造函数参数的依赖关系并创建实例
        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * 解析构造函数参数的依赖关系
     *
     * @param array $parameters 构造函数参数列表
     * @return array 构造函数参数的实例列表
     * @throws Exception 当无法解析依赖时抛出异常
     */
    protected function resolveDependencies(array $parameters): array
    {

        // 如果依赖是自己身份，则直接返回
        foreach ($parameters as $parameter) {
            if ($parameter->getName() === 'container' || $parameter->getName() === Container::class) {
                return [$this];
            }
        }

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            // 检查参数是否是类类型，并且不是容器类自身
            if ($dependency !== null && $dependency->name !== self::class) {
                // 递归调用 make 方法获取依赖的实例
                $dependencies[] = $this->make($dependency->name);
            } elseif ($parameter->isDefaultValueAvailable()) {
                // 如果参数有默认值，则使用默认值
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                // 否则无法解析依赖
                throw new \Exception("Unable to resolve dependency '{$parameter->getName()}'.");
            }
        }

        return $dependencies;
    }

    /**
     * 验证最后一次绑定的接口或抽象类是否为指定的接口或抽象类
     *
     * @param string $abstract 要验证的接口或抽象类名
     * @return Container 当前容器实例
     * @throws Exception 当验证失败时抛出异常
     */
    public function for(string $abstract): self
    {
        if ($this->lastBound !== $abstract) {
            throw new \Exception("The last bound service is not '$abstract'.");
        }

        return $this;
    }

    /**
     * 定义服务别名
     *
     * @param string $alias 别名
     * @param string $serviceName 服务名称
     * @return Container 当前容器实例
     */
    public function alias(string $alias, string $serviceName): self
    {
        $this->aliases[$alias] = $serviceName;
        return $this;
    }

    /**
     * 获取所有服务别名
     *
     * @return array 所有服务别名
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * 标记服务
     *
     * @param string $abstract 服务名称
     * @param string $tag 标记
     * @return Container 当前容器实例
     */
    public function tag(string $abstract, string $tag): self
    {
        if (!isset($this->bindings[$abstract]['tags'])) {
            $this->bindings[$abstract]['tags'] = [];
        }

        $this->bindings[$abstract]['tags'][] = $tag;
        return $this;
    }

    /**
     * 加载插件
     *
     * @param array $plugins 插件数组
     */
    public function loadPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $this->registerPlugin($plugin);
        }
    }

    /**
     * 注册插件
     *
     * @param PluginInterface $plugin 插件对象
     */
    protected function registerPlugin(PluginInterface $plugin)
    {
        $plugin->register($this);
        $this->plugins[] = $plugin;
    }
    /**
     * 加载并处理插件的依赖关系
     *
     * @param array $plugins 插件数组
     */
    public function loadAndResolvePluginDependencies(array $plugins)
    {
        // 遍历所有插件
        foreach ($plugins as $plugin) {
            // 获取插件的依赖关系
            $dependencies = $plugin->getDependencies();

            // 解析并加载插件的依赖项
            foreach ($dependencies as $dependency) {
                $this->loadPluginDependency($dependency);
            }

            // 注册插件
            $this->registerPlugin($plugin);
        }
    }

    /**
     * 加载并处理单个插件的依赖关系
     *
     * @param string $dependency 插件依赖项
     */
    protected function loadPluginDependency(string $dependency)
    {
        // 根据插件依赖项加载插件
        // 这里可以根据具体需求实现插件加载逻辑
        // 例如，通过配置文件、数据库或直接通过容器加载插件
        // 在这个示例中，假设插件直接通过类名进行加载
        $pluginInstance = new $dependency();

        // 注册插件
        $this->registerPlugin($pluginInstance);
    }

    /**
     * 获取插件实例
     * @param string $name 插件名称
     * @return PluginInterface|null 插件实例，如果不存在则返回null
     */
    public function getPlugin(string $name): ?PluginInterface
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->getName() === $name) {
                return $plugin;
            }
        }
        return null;
    }

    /**
     * 销毁绑定的实例
     *
     * @param string $abstract 要销毁的服务名称
     * @return void
     */
    public function destroy(string $abstract): void
    {
        if (isset($this->bindings[$abstract])) {
            unset($this->bindings[$abstract]['instance']);
        }
    }

    /**
     * 销毁所有绑定的实例
     *
     * @return void
     */
    public function destroyAll(): void
    {
        foreach ($this->bindings as $abstract => $binding) {
            unset($this->bindings[$abstract]['instance']);
        }
    }
}
