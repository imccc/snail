<?php

namespace Imccc\Snail\Core;

use Imccc\Snail\Core\Container;
class Router
{
    protected $container; // 依赖注入容器
    private $routeMap; // 路由配置
    private $defaultNamespace; // 默认命名空间
    private $defaultController; // 默认控制器
    private $defaultAction; // 默认动作

    protected $patterns = [ // 路由模式匹配规则
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
    ];

    protected $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD']; // 支持的请求方法
    protected $supportedSuffixes = ['.do', '.html', '.snail']; // 支持的URL后缀

    private $parsedRoute; // 解析后的路由信息

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
    }

    /**
     * 获取请求的URI
     *
     * @return string 请求的URI
     */
    private function getUri()
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
    public function parseRoute($url)
    {
        foreach ($this->routeMap['routemap'] as $group => $routes) {
            foreach ($routes as $pattern => $config) {
                if (!$this->checkRequestMethod($config)) {
                    continue;
                }

                $regex = $this->generatePattern($pattern);
                if (preg_match($regex, $url, $matches)) {
                    return $this->processMatch($config, array_slice($matches, 1), $group);
                } else {
                    return $this->parseUrl($url);
                }
            }
        }
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
        $routeinfo = [];

        // 如果 handler 是闭包，则直接返回闭包以及参数
        if (is_callable($handler)) {
            $routeinfo = [
                'closure' => $handler,
                'params' => $matches,
            ];
        } else {
            // 如果 handler 是控制器和方法，则继续解析控制器和方法名
            $namespace = $config[2] ?? $this->routeMap['namespacemap'][$group] ?? $this->defaultNamespace;
            $controllerAction = explode('@', $handler);
            $controller = $controllerAction[1] ?? $this->defaultController;
            $action = $controllerAction[0] ?? $this->defaultAction;

            $routeinfo = [
                'namespace' => $namespace,
                'controller' => $controller,
                'action' => $action,
                'params' => $params,
            ];
        }
        return $routeinfo;
    }

    /**
     * 解析 URL 并返回默认的路由信息数组
     *
     * @param string $url 要解析的 URL
     * @return array 默认的路由信息数组
     */
    private function parseUrl($url)
    {
        $segments = explode('/', $url);

        return [
            'namespace' => $this->defaultNamespace,
            'controller' => $segments[1] ?? $this->defaultController,
            'action' => $segments[0] ?? $this->defaultAction,
            'params' => array_slice($segments, 2),
        ];
    }

    /**
     * 移除URL后缀
     *
     * @param string $uri 要处理的URI
     * @return string 处理后的URI
     */
    private function removeUrlSuffix($uri)
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
    public function getRouteInfo()
    {
        return $this->parsedRoute;
    }
}
