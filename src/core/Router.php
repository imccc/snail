<?php
/**
 * 路由类
 * @describe 获取请求的URL并匹配到对应的控制器和方法 返回一个数组给调度类处理，
 * 支持RESTful路由、支持路由表、支持路由参数、响应url和pathinfo、混杂模式、支持路由别名、
 * 支持路由分组、支持路由中间件、支持路由参数、
 * @package Snail
 * @author Imccc
 * @version 0.0.1
 * @copyright Copyright (c) 2024 Imccc
 * @license MIT
 * @link https://github.com/Imcccphp/Snail
 */

namespace Imccc\Snail\Core;

use Imccc\Snail\Core\Container;

class Router
{
    protected $container;
    protected $config;
    private $routeMap;
    private $namespaceMap;
    private $useKeyValue;
    private $defaultNamespace;
    private $defaultController;
    private $defaultAction;

    protected $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
    );

    protected $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD');
    protected $supportedSuffixes = ['.do', '.html', '.snail'];

    private $parsedRoute;

    /**
     * 构造函数，初始化路由配置
     *
     * @param Container $container 依赖注入容器
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routeMap = $this->container->resolve('ConfigService')->get('route');

        $this->defaultNamespace = $this->routeMap['def']['default_namespace'] ?? '';
        $this->defaultController = $this->routeMap['def']['default_controller'] ?? '';
        $this->defaultAction = $this->routeMap['def']['default_action'] ?? '';

        $this->namespaceMap = $this->routeMap['namespacemap'] ?? [];
        $this->useKeyValue = $this->routeMap['keyvalue'] ?? false;

        $this->parsedRoute = $this->parseRoute($this->getUri());
    }

    /**
     * 获取处理后的URI
     * @return string 处理后的URI
     */
    private function getUri()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
        return $this->removeUrlSuffix($uri) ?: '/';
    }

    /**
     * 解析给定的 URL 并匹配相应的路由配置
     *
     * @param string $url 要解析的 URL
     * @return array 匹配到的路由信息数组
     */
    public function parseRoute($url)
    {
        foreach ($this->routeMap['routemap'] as $group => $routes) {
            foreach ($routes as $route => $config) {
                // 检查请求方法是否符合配置要求
                if (!$this->checkRequestMethod($config)) {
                    continue; // 如果不符合，则跳出当前循环，继续下一个路由配置
                }

                $pattern = $this->generatePattern($route);
                if (preg_match($pattern, $url, $matches)) {
                    return $this->processMatch($config, $matches);
                }
            }
        }

        // 如果没有匹配到任何路由，则直接解析 URL
        return $this->parseUrl($url);
    }

    /**
     * 检查请求方法是否符合配置要求
     *
     * @param array $config 路由配置数组
     * @return bool 请求方法是否符合要求
     */
    private function checkRequestMethod($config)
    {
        // 获取配置中的请求方法，如果未指定则默认为 GET
        $allowedMethods = isset($config[1]) ? explode('|', strtoupper($config[1])) : ['GET'];

        // 获取当前请求方法
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // 检查当前请求方法是否在允许的方法列表中
        return in_array($requestMethod, $this->methods);
    }

    /**
     * 生成路由匹配的正则表达式
     *
     * @param string $route 路由配置中的路由规则
     * @return string 生成的正则表达式
     */
    private function generatePattern($route)
    {
        return '#^' . preg_replace_callback('/:(\w+)/', function ($matches) { // 构建路由正则表达式
            return isset($this->patterns[$matches[1]]) ? '(' . $this->patterns[$matches[1]] . ')' : '([^/]+)';
        }, $route) . '$#';
    }

    /**
     * 处理路由匹配后的结果
     *
     * @param array $config 匹配到的路由配置
     * @param array $matches 匹配到的 URL 参数
     * @return array 处理后的路由信息数组
     */
    private function processMatch($config, $matches)
    {
        $result = [
            'namespace' => '',
            'controller' => '',
            'action' => '',
            'params' => [],
        ];

        // 如果匹配到的配置是闭包函数，则将闭包函数添加到结果数组中
        if (is_callable($config[0])) {
            $result = [];
            $result['closure'] = $config[0];
            return $result;
        } else {
            // 否则，解析方法、类名和命名空间，并将其添加到结果数组中
            $parts = explode('@', $config[0]);
            $result['action'] = $parts[0];
            $result['controller'] = $parts[1];
            $result['namespace'] = isset($config[2]) ? $config[2] : '';
        }

        // 从 URL 参数中提取参数，并添加到结果数组中
        array_shift($matches); // 移除完整匹配项
        if ($this->useKeyValue) {
            // 如果使用键对参数，则将参数解析成键值对
            $result['params'] = $this->parseKeyValueParams($matches);
        } else {
            $result['params'] = $matches;
        }

        // 保存解析后的路由信息
        $this->parsedRoute = $result;

        return $result;
    }

    /**
     * 将匹配到的参数解析成键值对形式
     *
     * @param array $params 匹配到的参数数组
     * @return array 解析后的键值对参数数组
     */
    private function parseKeyValueParams($params)
    {
        $keyValueParams = [];
        $count = count($params);
        for ($i = 0; $i < $count; $i += 2) {
            $key = $params[$i];
            $value = isset($params[$i + 1]) ? $params[$i + 1] : null;
            $keyValueParams[$key] = $value;
        }
        return $keyValueParams;
    }

    /**
     * 解析 URL 并返回默认的路由信息数组
     *
     * @param string $url 要解析的 URL
     * @return array 默认的路由信息数组
     */
    private function parseUrl($url)
    {
        // 解析 URL，将域名、模块名、控制器、方法和参数分别提取出来
        $parsedUrl = parse_url($url);
        $path = trim($parsedUrl['path'], '/');
        $segments = explode('/', $path);

        // 设置默认值
        $namespace = $this->defaultNamespace;
        $controller = $this->defaultController;
        $action = $this->defaultAction;
        $params = [];

        // 根据 URL 结构提取控制器、方法和参数
        if (count($segments) >= 2) {
            // 第一个段作为控制器名
            $controller = $segments[0];
            // 第二个段作为方法名
            $action = isset($segments[1]) ? $segments[1] : $this->defaultAction; // 默认方法
            // 剩余的段作为参数
            $params = array_slice($segments, 2);
        }

        // 保存解析后的路由信息
        $this->parsedRoute = [
            'namespace' => $namespace,
            'controller' => $controller,
            'action' => $action,
            'params' => $params,
        ];

        return $this->parsedRoute;
    }

    /**
     * 移除URL后缀
     * @param $uri
     * @return string
     */
    private function removeUrlSuffix($uri)
    {
        foreach ($this->supportedSuffixes as $suffix) {
            if (substr($uri, -strlen($suffix)) === $suffix) {
                $uri = substr($uri, 0, -strlen($suffix));
                break;
            }
        }
        return $uri;
    }

    /**
     * 获取解析后的路由信息
     * @return array
     */
    public function getRouteInfo()
    {
        return $this->parsedRoute;
    }
}
