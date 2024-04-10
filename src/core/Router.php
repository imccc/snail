<?php

namespace Imccc\Snail\Core;

use Imccc\Snail\Core\Container;

class Router
{
    protected $container;
    private $routeMap;
    private $defaultNamespace;
    private $defaultController;
    private $defaultAction;

    protected $patterns = [
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*',
    ];

    protected $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];
    protected $supportedSuffixes = ['.do', '.html', '.snail'];

    private $parsedRoute;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routeMap = $container->resolve('ConfigService')->get('route');

        $this->defaultNamespace = $this->routeMap['def']['default_namespace'] ?? 'App\\Controllers';
        $this->defaultController = $this->routeMap['def']['default_controller'] ?? 'Home';
        $this->defaultAction = $this->routeMap['def']['default_action'] ?? 'index';

        $this->parsedRoute = $this->parseRoute($this->getUri());
    }

    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        return $this->removeUrlSuffix($uri) ?: '/';
    }

    public function parseRoute($url)
    {
        foreach ($this->routeMap['routemap'] as $pattern => $config) {
            if (!$this->checkRequestMethod($config)) {
                continue;
            }

            $regex = $this->generatePattern($pattern);
            if (preg_match($regex, $url, $matches)) {
                return $this->processMatch($config, array_slice($matches, 1));
            }
        }

        return $this->parseUrl($url);
    }

    private function checkRequestMethod($config)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (isset($config[1]) && is_string($config[1])) {
            return in_array($requestMethod, explode('|', strtoupper($config[1])), true);
        }
        return true; // 默认接受所有方法，如果没有明确指定
    }

    private function generatePattern($route)
    {
        return '#^' . preg_replace_callback('/:(\w+)/', function ($matches) {
            return $this->patterns[$matches[1]] ?? '([^/]+)';
        }, $route) . '$#';
    }

    private function processMatch($config, $matches)
    {
        $controllerAction = explode('@', $config[0]);
        $controller = $controllerAction[0] ?? $this->defaultController;
        $action = $controllerAction[1] ?? $this->defaultAction;
        $namespace = $config[2] ?? $this->defaultNamespace;

        return [
            'namespace' => $namespace,
            'controller' => $controller,
            'action' => $action,
            'params' => $matches,
        ];
    }

    private function parseUrl($url)
    {
        $segments = explode('/', $url);

        return [
            'namespace' => $this->defaultNamespace,
            'controller' => $segments[0] ?? $this->defaultController,
            'action' => $segments[1] ?? $this->defaultAction,
            'params' => array_slice($segments, 2),
        ];
    }

    private function removeUrlSuffix($uri)
    {
        foreach ($this->supportedSuffixes as $suffix) {
            if (substr($uri, -strlen($suffix)) === $suffix) {
                return substr($uri, 0, -strlen($suffix));
            }
        }
        return $uri;
    }

    public function getRouteInfo()
    {
        return $this->parsedRoute;
    }
}
