<?php
/**
 * 路由类
 * 
 * @author Imccc
 */
namespace Imccc\Snail\Core;

class Router
{
    protected $routes = [];
    protected $keyvalue = false;
    protected $group = [];
    protected $routeConfig = '';

    /**
     * 加载路由配置
     * 
     * @param array $routesConfig 路由配置数组
     */
    public function loadRoutes(array $routesConfig)
    {
        $this->routeConfig = $routesConfig;

        $this->keyvalue = $routesConfig['keyValue'];
        $routeConfig = $routesConfig['routeMap'];

        foreach ($routeConfig as $route) {
            // 检查是否是路由组
            if (isset($route['group'])) {
                $this->loadGroup($route['group']);
            } else {
                $this->addRoute($route);
            }
        }
    }

    /**
     * 加载路由组
     * 
     * @param array $group 路由组数组
     */
    protected function loadGroup(array $group)
    {
        $prefix = $group['prefix'] ?? ''; // 路由组前缀
        $namespace = $group['namespace'] ?? $this->routeConfig['defaultNamespace']; // 路由组命名空间
        $middleware = $group['middleware'] ?? []; // 路由组中间件

        foreach ($group['routes'] as $route) {
            // 为每个路由添加前缀和中间件
            $route['rule'] = $prefix . $route['rule'];
            $route['middleware'] = array_merge($middleware, $route['middleware'] ?? []);
            $route['namespace'] = $namespace;
            $route['group'] = $group['name'] ?? $this->routeConfig['defaultGroup']; // 设置路由组名称
            $this->addRoute($route);
        }
    }

    /**
     * 添加单个路由
     * 
     * @param array $route 路由数组
     */
    protected function addRoute(array $route)
    {
        if (is_callable($route['route'])) {
            $controller = null;
            $action = $route['route'];
        } else {
            list($controller, $action) = explode('@', $route['route']);
        }

        $this->routes[] = [
            'uri' => $route['rule'], // 路由URI
            'group' => $route['group'] ?? $this->routeConfig['defaultGroup'], // 路由组
            'namespace' => $route['namespace'] ?? $this->routeConfig['defaultNamespace'], // 路由命名空间
            'controller' => $controller, // 控制器名称
            'action' => $action, // 动作名称
            'method' => strtolower($route['method']), // 请求方法，统一为小写
            'middleware' => $route['middleware'] ?? [], // 路由中间件
        ];
    }

    /**
     * 解析请求URI和方法以找到匹配的路由
     * 
     * @param string $uri 请求的URI
     * @param string $method 请求的方法
     * @return array|null 匹配的路由信息或null
     */
    public function resolve($uri, $method)
    {
        $method = strtolower($method); // 将请求方法统一为小写

        foreach ($this->routes as $route) {
            // 处理正则表达式参数
            $pattern = preg_replace('/\{(\w+):([^}]+)\}/', '(?<$1>$2)', $route['uri']);
            // 处理非正则表达式参数
            $pattern = preg_replace('/\{(\w+)\}/', '(?<$1>[^/]+)', $pattern);
            // 创建匹配整个URI的正则表达式模式
            $pattern = "#^" . $pattern . "$#i";

            // 检查URI是否与路由模式匹配
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = [];
                if ($this->keyvalue) {
                    // 提取键值对参数
                    $segments = explode('/', trim($uri, '/'));
                    $params = $this->parseParams(array_slice($segments, count(explode('/', trim($route['uri'], '/')))));
                } else {
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value; // 提取命名参数
                        }
                    }
                }
               
                // 处理闭包路由
                if (is_callable($route['action'])) {
                    return [
                        'uri' => $route['uri'],
                        'group' => $route['group'],
                        'namespace' => null,
                        'controller' => null,
                        'action' => $route['action'],
                        'params' => $params,
                        'method' => $route['method'],
                        'middleware' => $route['middleware'],
                    ];
                }

                // 处理控制器路由
                return [
                    'uri' => $route['uri'],
                    'group' => $route['group'],
                    'namespace' => $route['namespace'],
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $params,
                    'method' => $route['method'],
                    'middleware' => $route['middleware'],
                ];
            }
        }

        // 尝试按照标准RESTful地址解析
        $segments = explode('/', trim($uri, '/'));
        if (count($segments) >= 3) {
            $group = ucfirst($segments[0]);
            $controllerName = ucfirst($segments[1]);
            $action = lcfirst($segments[2]);
            $actionMethod = $this->getRestfulMethod($method, $segments);
            $controller = "App\\$group\\Controller\\$controllerName";

            if (class_exists($controller)) {
                if (method_exists($controller, $action)) {
                    $params = $this->keyvalue ? $this->parseParams(array_slice($segments, 3)) : array_slice($segments, 3);

                    return [
                        'uri' => $uri,
                        'group' => $group,
                        'namespace' => "App\\$group\\Controller",
                        'controller' => $controllerName,
                        'action' => $action,
                        'params' => $params,
                        'method' => $method,
                        'middleware' => [],
                    ];
                }
            }
        }

        // 如果找不到匹配的路由，则返回null
        return null;
    }

    /**
     * 获取 RESTful 方法名
     * 
     * @param string $method HTTP 方法
     * @param array $segments URI 分段
     * @return string 对应的控制器方法名
     */
    protected function getRestfulMethod($method, $segments)
    {
        $method = strtolower($method);
        switch ($method) {
            case 'get':
                return count($segments) === 3 ? 'index' : 'show';
            case 'post':
                return 'store';
            case 'put':
            case 'patch':
                return 'update';
            case 'delete':
                return 'destroy';
            default:
                return 'index';
        }
    }

    /**
     * 解析 URI 分段为键值对参数
     * 
     * @param array $segments URI 分段
     * @return array 键值对参数
     */
    protected function parseParams(array $segments)
    {
        $params = [];
        for ($i = 0; $i < count($segments); $i += 2) {
            if (isset($segments[$i + 1])) {
                $params[$segments[$i]] = $segments[$i + 1];
            }
        }
        return $params;
    }
}
