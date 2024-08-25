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
use ReflectionMethod;
use \Expeption;

class Container
{
    private static $instance;

    protected $plugins = []; // 存储插件信息
    protected $bindings = []; // 存储绑定的服务信息
    protected $aliases = []; // 存储服务别名信息
    protected $lastBound = ''; // 最后绑定的接口或抽象类
    protected $_debugInfo = []; // 调试信息

    protected $resolving = []; // 追踪正在解析的服务
    protected $dependencyCache = [];

    protected $instances = [];
    protected $scopedInstances = [];
    protected $events = [];

    // 获取容器实例的静态方法
    /**
     * 获取容器实例
     *
     * @param bool $newInstance 是否创建新的实例
     * @return Container 容器实例
     * @throws Exception 如果无法获取实例
     */
    public static function getInstance($newInstance = false)
    {
        if ($newInstance || !self::$instance) {
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
     * 加载配置文件
     *
     * @param array $config 配置数组
     * @return void
     */
    public function loadConfiguration(array $config)
    {
        foreach ($config as $abstract => $binding) {
            $this->bind(
                $abstract,
                $binding['concrete'] ?? null,
                $binding['shared'] ?? false,
                $binding['lifecycle'] ?? 'singleton'
            );
        }
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
    public function bind(string $abstract, $concrete = null, bool $shared = false, string $lifecycle = 'singleton'): self
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
            'instance' => null,
            'lifecycle' => $lifecycle,
        ];

        // 触发 'binding' 事件
        $this->fireEvent('binding', $abstract);

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
        // 触发 'resolving' 事件
        $this->fireEvent('resolving', $abstract);

        // 返回服务实例
        return $this->make($abstract);
    }

    public function addEventListener(string $event, Closure $listener)
    {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $listener;
    }

    protected function fireEvent(string $event, string $abstract)
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $listener) {
                $listener($abstract);
            }
        }
    }

    /**
     * 尝试根据预定义规则自动注册服务
     *
     * @param string $abstract 请求的服务名称或接口
     * @throws Exception 如果自动注册失败
     */
    protected function attemptAutoRegister(string $abstract, array $options = [])
    {
        $defaultOptions = [
            'interface_suffix' => 'Interface',
            'service_namespace' => 'Imccc\Snail\Services',
            'service_path' => dirname(__DIR__) . '/services',
        ];

        $options = array_merge($defaultOptions, $options);

        $interfaceFile = $options['service_path'] . '/' . $abstract . $options['interface_suffix'] . '.php';
        $serviceFile = $options['service_path'] . '/' . $abstract . '.php';
        $serviceNamespace = $options['service_namespace'];

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
        $binding = $this->bindings[$abstract];
        $instance = null;

        if ($binding['lifecycle'] === 'singleton') {
            $instance = $binding['shared'] ? $binding['instance'] : null;
        } elseif ($binding['lifecycle'] === 'scoped') {
            $instance = $this->scopedInstances[$abstract] ?? null;
        } elseif ($binding['lifecycle'] === 'transient') {
            // Transient：每次都创建新的实例
            return $this->build($binding['concrete']);
        }

        if ($instance === null) {
            $instance = $this->build($binding['concrete']);
            if ($binding['lifecycle'] === 'singleton') {
                $this->bindings[$abstract]['instance'] = $instance;
            } elseif ($binding['lifecycle'] === 'scoped') {
                $this->scopedInstances[$abstract] = $instance;
            }
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
        if (isset($this->dependencyCache[$concrete])) {
            return $this->dependencyCache[$concrete];
        }

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
            $instance = new $concrete;
            $this->dependencyCache[$concrete] = $instance;
            return $instance;
        }

        // 否则解析构造函数参数的依赖关系并创建实例
        $dependencies = $this->resolveDependencies($constructor->getParameters());

        $instance = $reflector->newInstanceArgs($dependencies);

        // 缓存实例化后的结果
        $this->dependencyCache[$concrete] = $instance;

        return $instance;
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
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            // 如果参数是类类型
            if ($dependency !== null) {
                // 如果依赖是容器本身，直接返回当前实例
                if ($dependency->name === self::class) {
                    $dependencies[] = $this;
                    continue;
                }

                // 检查是否有循环依赖
                if (isset($this->resolving[$dependency->name])) {
                    throw new \Exception("Circular dependency detected: " . implode(' -> ', array_keys($this->resolving)) . " -> " . $dependency->name);
                }

                // 记录当前正在解析的依赖
                $this->resolving[$dependency->name] = true;

                // 递归调用 make 方法获取依赖的实例
                $dependencies[] = $this->make($dependency->name);

                // 移除当前解析完成的依赖
                unset($this->resolving[$dependency->name]);
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
     * 定义服务别名
     *
     * @param string $alias 别名
     * @param string $serviceName 服务名称
     * @return Container 当前容器实例
     * @throws Exception 如果别名与现有服务名称冲突
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
     * @return void
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
        // 创建依赖图
        $dependencyGraph = [];

        foreach ($plugins as $plugin) {
            $dependencies = $plugin->getDependencies();
            $dependencyGraph[$plugin->getName()] = $dependencies;
        }

        // 使用拓扑排序处理插件的加载顺序
        $sortedPlugins = $this->topologicalSort($dependencyGraph);

        foreach ($sortedPlugins as $pluginName) {
            $plugin = $this->getPlugin($pluginName);
            $this->registerPlugin($plugin);
        }
    }

    protected function topologicalSort(array $dependencyGraph): array
    {
        $sorted = [];
        $incomingEdges = [];
        $nodesWithNoIncomingEdges = [];

        // 初始化入度和节点列表
        foreach ($dependencyGraph as $node => $dependencies) {
            if (!isset($incomingEdges[$node])) {
                $incomingEdges[$node] = 0;
            }
            foreach ($dependencies as $dependency) {
                if (!isset($incomingEdges[$dependency])) {
                    $incomingEdges[$dependency] = 0;
                }
                $incomingEdges[$dependency]++;
            }
        }

        // 查找没有入度的节点
        foreach ($incomingEdges as $node => $count) {
            if ($count === 0) {
                $nodesWithNoIncomingEdges[] = $node;
            }
        }

        while (!empty($nodesWithNoIncomingEdges)) {
            $node = array_shift($nodesWithNoIncomingEdges);
            $sorted[] = $node;

            if (isset($dependencyGraph[$node])) {
                foreach ($dependencyGraph[$node] as $dependent) {
                    $incomingEdges[$dependent]--;
                    if ($incomingEdges[$dependent] === 0) {
                        $nodesWithNoIncomingEdges[] = $dependent;
                    }
                }
            }
        }

        if (count($sorted) !== count($incomingEdges)) {
            throw new \Exception("A cycle detected in the dependency graph.");
        }

        return $sorted;
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

//     interface PluginInterface
    // {
    //     public function getName(): string;
    //     public function getDependencies(): array; // 返回依赖的插件名称数组
    //     public function register(Container $container);
    // }

    /**
     * 销毁绑定的实例
     *
     * @param string $abstract 要销毁的服务名称
     * @return void
     */
    public function destroy(string $abstract): void
    {
        if (isset($this->bindings[$abstract])) {
            $instance = $this->bindings[$abstract]['instance'];
            if (method_exists($instance, '__destruct')) {
                $instance->__destruct();
            } elseif ($instance instanceof DisposableInterface) {
                $instance->dispose();
            }
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
