<?php

namespace Imccc\Snail\Core;
use Imccc\Snail\Core\Container;

class Dispatcher
{
    protected $middleware = [];
    protected $container;
    protected $logger;
    protected $routes;
    protected $logprefix = ['dispatcher', 'info', 'error'];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->resolve('LoggerService');
    }
    public function addMiddleware(callable $middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch($uri, $method, Router $router)
    {
        $route = $router->resolve($uri, $method);
        $this->logger->log(self::class . ":" .__FUNCTION__ . " : " . print_r($route, true),$this->logprefix[0]);

        if (!$route) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        $method = $route['method'];
        $namespace = $route['namespace'];
        $controller = $route['controller'];
        $action = $route['action'];
        $params = $route['params'];
        $middlewares = $route['middleware'];

        $controllerMiddleware = array_merge($this->middleware, $middlewares);

        $next = function ($params) use ($action, $route, $namespace, $controller) {
            if (is_callable($action)) {
                echo call_user_func_array($action, $params);
            } else {
                $fullController = $namespace . '\\' . $controller;
                if (class_exists($fullController)) {
                    $controllerInstance = new $fullController($action, $params, $route);
                    if (method_exists($controllerInstance, $action)) {
                        echo call_user_func_array([$controllerInstance, $action], $params);
                    } else {
                        http_response_code(404);
                        echo "404 Not Found: Method {$action} not found in controller {$controller}";
                    }
                } else {
                    http_response_code(404);
                    echo "404 Not Found: Controller {$controller} not found";
                }
            }
        };

        $middlewareChain = array_reduce(array_reverse($controllerMiddleware), function ($next, $middleware) {
            return function ($params) use ($next, $middleware) {
                return $middleware($params, $next);
            };
        }, $next);

        $middlewareChain($params);
    }
}
