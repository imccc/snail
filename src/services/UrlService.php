<?php
namespace Imccc\Snail\Services;

class UrlService extends Service
{
    protected $router;
    protected $baseUrl;

    public function __construct($container, string $baseUrl, Router $router)
    {
        parent::__construct($container);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->router = $router;
    }

    public function url(string $route, array $routeParams = [], string $suffix = '', $domain = false, string $method = 'GET'): ?string
    {
        $parsedRoute = $this->router->resolve($route, $method);

        if (!$parsedRoute) {
            throw new \Exception("Route not found: $route");
        }

        $url = $parsedRoute['uri'];

        $url = preg_replace_callback('/\{(\w+)\}/', function ($match) use ($routeParams) {
            $key = $match[1];
            if (array_key_exists($key, $routeParams)) {
                return urlencode($routeParams[$key]);
            }
            return $match[0];
        }, $url);

        $url = ltrim($url, '/');
        $url = $this->baseUrl . $url;

        if (!empty($suffix)) {
            $url .= '.' . $suffix;
        } elseif ($this->router->guiseExtend) {
            $url .= '.' . explode('|', $this->router->guiseExtend)[0];
        }

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
