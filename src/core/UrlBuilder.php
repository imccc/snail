<?php

namespace Imccc\Snail\Core;

class UrlBuilder
{
    protected $router;
    protected $baseUrl;

    public function __construct(string $baseUrl, Router $router)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->router = $router;
    }

    public function url(string $route, array $routeParams = [], string $suffix = '', $domain = false, string $method = 'GET'): ?string
    {
        // 使用 Router 类的 resolve 方法解析路由
        $parsedRoute = $this->router->resolve($route, $method);

        if (!$parsedRoute) {
            throw new \Exception("Route not found: $route");
        }

        // 构建 URL
        $url = $parsedRoute['uri'];

        // 替换路由中的参数 采用更安全的占位符方式，并对参数进行适当的编码
        $url = preg_replace_callback('/\{(\w+)\}/', function($match) use ($routeParams) {
            $key = $match[1];
            if (array_key_exists($key, $routeParams)) {
                return urlencode($routeParams[$key]);
            }
            return $match[0];
        }, $url);

        // 构建完整路径
        $url = ltrim($url, '/');
        $url = $this->baseUrl . $url;

        // 处理后缀
        if (!empty($suffix)) {
            $url .= '.' . $suffix;
        } elseif ($this->router->guiseExtend) {
            $url .= '.' . explode('|', $this->router->guiseExtend)[0];
        }

        // 处理域名
        if ($domain) {
            $url = $this->getDomain($domain) . $url;
        }

        return $url;
    }

    protected function getDomain($domain = false): string
    {
        // 获取当前域名，如果是 HTTPS，则返回 HTTPS
        // 改进HTTPS的检测逻辑
        $protocol = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }

        // 域名拼接，考虑$domain参数是否为字符串
        return ($domain && is_string($domain)) ? $domain : $protocol . '://' . $_SERVER['HTTP_HOST'] . '/';
    }
}