<?php

namespace Imccc\Snail\Core;

use Imccc\Snail\Core\Container;

class Router
{
    protected $patterns = [
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
        ':base64' => '[a-zA-Z0-9/+=]+',
    ]; // 路由模式匹配规则

    protected $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD']; // 支持的请求方法
    protected $supportedSuffixes = ['.do', '.html', '.snail']; // 支持的URL后缀

    protected $container; // 依赖注入容器
    protected $routeMap; // 路由配置
    protected $defaultNamespace; // 默认命名空间
    protected $defaultController; // 默认控制器
    protected $defaultAction; // 默认动作
    protected $parsedRoute; // 解析后的路由信息

    /**
     * 构造函数，初始化路由配置和默认值
     *
     * @param Container $container 依赖注入容器
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routeMap = $container->resolve('ConfigService')->get('route');

        $this->defaultNamespace = $this->routeMap['def']['default_namespace'] ?? 'App\Controllers';
        $this->defaultController = $this->routeMap['def']['default_controller'] ?? 'Home';
        $this->defaultAction = $this->routeMap['def']['default_action'] ?? 'index';

        $this->parsedRoute = $this->parseRoute($this->getUri());

        // 脚本结束时执行debug,方便调试，开关在router.conf.php中配置
        if ($this->routeMap['debug'] || defined('DEBUG') == true) {
            register_shutdown_function(function () {
                $this->debug();
            });
        }

    }

    /**
     * 获取请求的URI
     *
     * @return string 请求的URI
     */
    private function getUri(): string
    {
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) ?? '/';
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        return $this->removeUrlSuffix($uri) ?: '/';
    }

    /**
     * 解析路由
     *
     * @param string $url 要解析的URL
     * @return array 解析后的路由信息数组
     */
    public function parseRoute($url): array
    {
        foreach ($this->routeMap['routemap'] as $group => $routes) {
            foreach ($routes as $pattern => $config) {
                if (!$this->checkRequestMethod($config)) {
                    continue;
                }

                $regex = $this->generatePattern($pattern);
                if (preg_match($regex, $url, $matches)) {
                    return $this->processMatch($config, array_slice($matches, 1), $group);
                }
            }
        }

        // 如果没有匹配的路由，返回默认路由
        return $this->parseUrl($url);
    }

    /**
     * 处理路由匹配后的结果
     *
     * @param array $config 匹配到的路由配置
     * @param array $matches 匹配到的 URL 参数
     * @param string $group 分组
     * @return array 处理后的路由信息数组
     */
    private function processMatch($config, $matches, $group)
    {
        $handler = $config[0];
        $params = $config[3] ?? [];

        // 如果 handler 是闭包，则直接返回闭包以及参数
        if (is_callable($handler)) {
            return [
                'closure' => $handler,
                'params' => $matches,
            ];

        } else {
            // 如果 handler 是控制器和方法，则继续解析控制器和方法名
            $namespace = $config[2] ?? $this->routeMap['namespacemap'][$group] ?? $this->defaultNamespace;
            $controllerAction = explode('@', $handler);
            $controller = $controllerAction[0] ?? $this->defaultController;
            $action = $controllerAction[1] ?? $this->defaultAction;

            return [
                'namespace' => $namespace,
                'controller' => $controller,
                'action' => $action,
                'params' => $params,
            ];
        }
    }

    /**
     * 解析 URL 并返回默认的路由信息数组
     *
     * @param string $url 要解析的 URL
     * @return array 默认的路由信息数组
     */
    private function parseUrl($url): array
    {
        $segments = explode('/', $url);
        $param = $this->processParams($segments);
        // 模块名
        $module = $segments[0];

        $namespace = $this->routeMap['namespacemap'][$module] ?? $this->defaultNamespace;

        return [
            'namespace' => $namespace,
            'controller' => $segments[1] ?? $this->defaultController,
            'action' => $segments[2] ?? $this->defaultAction,
            'params' => $param,
        ];
    }

    /**
     * 处理参数的方法
     *
     * @param array $config 路由配置数组
     */
    private function processParams($segments): array
    {
        $params = [];
        // 如果使用键对模式
        if ($this->isKeyValueMode()) {
            for ($i = 3; $i < count($segments); $i += 2) {
                if (isset($segments[$i + 1])) {
                    $params[$segments[$i]] = $segments[$i + 1];
                }
            }

        } else {
            $params = array_slice($segments, 3);
        }
        return $params;
    }

    /**
     * 检查请求方法是否符合配置要求
     *
     * @param array $config 路由配置数组
     * @return bool 请求方法是否符合要求
     */
    private function checkRequestMethod($config): bool
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (isset($config[1]) && is_string($config[1])) {
            return in_array($requestMethod, explode('|', strtoupper($config[1])), true);
        }
        return true; // 默认接受所有方法，如果没有明确指定
    }

    /**
     * 生成路由匹配的正则表达式
     *
     * @param string $route 路由配置中的路由规则
     * @return string 生成的正则表达式
     */
    private function generatePattern($route): string
    {
        return '#^' . preg_replace_callback('/:(\w+)/', function ($matches) {
            return $this->patterns[$matches[1]] ?? '([^/]+)';
        }, $route) . '$#';
    }

    /**
     * 移除URL后缀
     *
     * @param string $uri 要处理的URI
     * @return string 处理后的URI
     */
    private function removeUrlSuffix($uri): string
    {
        foreach ($this->supportedSuffixes as $suffix) {
            if (substr($uri, -strlen($suffix)) === $suffix) {
                return substr($uri, 0, -strlen($suffix));
            }
        }
        return $uri;
    }

    /**
     * 获取解析后的路由信息
     *
     * @return array 解析后的路由信息数组
     */
    public function getRouteInfo(): array
    {
        return $this->parsedRoute;
    }

    /**
     * 获取是否使用键对模式
     *
     * @return bool 是否使用键对模式
     */
    public function isKeyValueMode(): bool
    {
        return $this->routeMap['keyvalue'] ?? false;
    }

    /**
     * 执行debug信息
     */
    public function debug()
    {
        echo "<h3>以下信息由 类: " . self::class . " 提供<small>" . date("Y-m-d H:i:s") . "</small></h3>";
        echo '<pre> 解析后的数组：';
        print_r($this->getRouteInfo());
        echo '<br> 路由表信息：<br>';
        print_r($this->routeMap);
        echo '</pre>';
    }
}
