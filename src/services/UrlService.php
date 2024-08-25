<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Core\Router;

class UrlService
{
    protected $router;
    protected $baseUrl;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->router =  $container->resolve('RouterService');
        $this->baseUrl = $this->getDomain();
    }

    public function url(string $route, array $routeParams = [], string $suffix = '', $domain = false, string $method = 'GET'): ?string
    {
        // 解析路由
        $parsedRoute = $this->router->resolve($route, $method);

        if (!$parsedRoute) {
            throw new \Exception("Route not found: $route");
        }

        // 获取 URI 模式
        $url = $parsedRoute['uri'];

        // 替换 URI 中的参数
        $url = preg_replace_callback('/\{(\w+)\}/', function ($match) use ($routeParams) {
            $key = $match[1];
            if (array_key_exists($key, $routeParams)) {
                return urlencode($routeParams[$key]);
            }
            return $match[0];
        }, $url);

        // 拼接基本 URL 和路径
        $url = ltrim($url, '/');
        $url = $this->baseUrl . $url;

        // 处理文件后缀
        if (!empty($suffix)) {
            $url .= '.' . $suffix;
        } 

        // 如果需要指定域名
        if ($domain) {
            $url = $this->getDomain($domain) . $url;
        }

        return $url;
    }

    protected function getDomain($domain = false): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }

        return ($domain && is_string($domain)) ? $domain : $protocol . '://' . $_SERVER['HTTP_HOST'] . '/';
    }
}
