<?php

namespace Imccc\Snail\Core;
use Imccc\Snail\Core\Container;

class Dispatcher
{
    protected $middleware = [];
    protected $container;
    protected $config;
    protected $logger;
    protected $routes;
    protected $logprefix = ['dispatcher', 'info', 'error'];
    protected $tpl;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->resolve('LoggerService');
        $this->config = $container->resolve('ConfigService');
        $this->tpl  = $this->config->get('def.tpl');
    }
    public function addMiddleware(callable $middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch($uri, $method, Router $router)
    {
        $route = $router->resolve($uri, $method);

        if (!$route) {
            $this->logger->log(__METHOD__." : ". $method . " ". print_r($route, true),$this->logprefix[2]);
            http_response_code(404);
            $html = sprintf($this->tpl, '404', '404 Not Found');
            echo $html;
            return;
        }
        $this->logger->log(__METHOD__." : ". $method . " ". print_r($route, true),$this->logprefix[0]);

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
                        $thml = sprintf($this->tpl, "404 Not Found", "404 Not Found: Method {$action} not found in controller {$controller}");
                        echo $thml;
                    }
                } else {
                    http_response_code(404);
                    $thml = sprintf($this->tpl, "404 Not Found", "404 Not Found: Controller {$controller} not found");
                    echo $thml;
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
