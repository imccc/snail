<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;

class RouterService
{
    protected $container;
    protected $config;
    protected $routes = [];
    protected $keyvalue = false;
    protected $application;
    protected $controller;
    protected $group = [];
    protected $routeConfig = '';
    protected $routeMap = [];
    protected $guiseExtend;

    protected $patterns = [
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->container->resolve('ConfigService');

        $routes = $this->config->get('routes');
        $this->application = $routes['setup']['application'];
        $this->controller = $routes['setup']['controller'];
        // 加载路由信息
        $this->loadRoutes($routes);
    }

    public function loadRoutes(array $routesConfig)
    {
        $this->routeConfig = $routesConfig;

        $this->keyvalue = $routesConfig['keyValue'];
        $routeConfig = $routesConfig['routeMap'];
        $this->guiseExtend = $routesConfig['setup']['guiseExtend'];
        foreach ($routeConfig as $route) {
            if (isset($route['group'])) {
                $this->loadGroup($route['group']);
            } else {
                $this->addRoute($route);
            }
        }
    }

    protected function loadGroup(array $group)
    {
        $prefix = $group['prefix'] ?? '';
        $namespace = $group['namespace'] ?? $this->routeConfig['setup']['defaultNamespace'];
        $middleware = $group['middleware'] ?? [];

        foreach ($group['routes'] as $route) {
            $route['rule'] = $prefix . $route['rule'];
            $route['middleware'] = array_merge($middleware, $route['middleware'] ?? []);
            $route['namespace'] = $namespace;
            $route['group'] = $group['name'] ?? $this->routeConfig['setup']['defaultGroup'];
            $this->addRoute($route);
        }
    }

    protected function addRoute(array $route)
    {
        if (is_callable($route['route'])) {
            $controller = null;
            $action = $route['route'];
        } else {
            list($controller, $action) = explode('@', $route['route']);
        }

        $methods = is_array($route['method']) ? $route['method'] : explode(',', $route['method']);
        foreach ($methods as $method) {
            $this->routes[] = [
                'uri' => $route['rule'],
                'group' => $route['group'] ?? $this->routeConfig['setup']['defaultGroup'],
                'namespace' => $route['namespace'] ?? $this->routeConfig['setup']['defaultNamespace'],
                'controller' => $controller,
                'action' => $action,
                'method' => strtolower(trim($method)),
                'middleware' => $route['middleware'] ?? [],
            ];
        }
    }

    public function __call($method, $arguments)
    {
        $validMethods = ['get', 'post', 'put', 'delete'];
        
        $method = strtolower($method);
        
        if (in_array($method, $validMethods) && count($arguments) >= 2) {
            $uri = $arguments[0];
            $route = $arguments[1];
            $namespace = $arguments[2] ?? null;

            $this->addRoute([
                'rule' => $uri,
                'method' => strtoupper($method),
                'route' => $route,
                'middleware' => $arguments[3] ?? [],
                'group' => $this->routeConfig['setup']['defaultGroup'],
                'namespace' => $namespace ?? $this->routeConfig['setup']['defaultNamespace'],
            ]);
        } else {
            throw new \BadMethodCallException("Method $method does not exist or invalid arguments provided");
        }
    }

    public function resolve(string $uri, string $method)
    {
        $method = strtolower($method);

        // 分离 URI 和查询参数
        $queryString = '';
        if (strpos($uri, '?') !== false) {
            list($uri, $queryString) = explode('?', $uri, 2);
        }

        $uri = preg_replace('/\.(' . $this->guiseExtend . ')$/', '', $uri);

        foreach ($this->routes as $route) {
            $pattern = $route['uri'];
            foreach ($this->patterns as $key => $value) {
                $pattern = str_replace($key, $value, $pattern);
            }
            $pattern = preg_replace('/\{(\w+):([^}]+)\}/', '(?<$1>$2)', $pattern);
            $pattern = preg_replace('/\{(\w+)\}/', '(?<$1>[^/]+)', $pattern);
            $pattern = "#^" . $pattern . "$#i";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = [];
                if ($this->keyvalue) {
                    $segments = explode('/', trim($uri, '/'));
                    $params = $this->parseParams(array_slice($segments, count(explode('/', trim($route['uri'], '/')))));
                } else {
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value;
                        }
                    }
                }

                // 解析查询参数
                parse_str($queryString, $queryParams);
                $params = array_merge($params, $queryParams);

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

        $segments = explode('/', trim($uri, '/'));
        if (count($segments) >= 3) {
            $group = ucfirst($segments[0]);
            $controllerName = ucfirst($segments[1]);
            $action = lcfirst($segments[2]);
            $actionMethod = $this->getRestfulMethod($method, $segments);
            $app = $this->application;
            $ctl = $this->controller;
            $controller = "$app\\$group\\$ctl\\$controllerName";

            if (class_exists($controller)) {
                if (method_exists($controller, $action)) {
                    $params = $this->keyvalue ? $this->parseParams(array_slice($segments, 3)) : array_slice($segments, 3);

                    // 解析查询参数
                    parse_str($queryString, $queryParams);
                    $params = array_merge($params, $queryParams);

                    return [
                        'uri' => $uri,
                        'group' => $group,
                        'namespace' => "$app\\$group\\$ctl",
                        'controller' => $controllerName,
                        'action' => $action,
                        'params' => $params,
                        'method' => $method,
                        'middleware' => [],
                    ];
                }
            }
        }
        echo ("No route matched for URI: $uri and method: $method");

        return null;
    }

    protected function getRestfulMethod(string $method, array $segments): string
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

    protected function parseParams(array $segments): array
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
