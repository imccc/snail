<?php
namespace Imccc\Snail\Core;

use Imccc\Snail\Core\Container;

class Dispatcher
{
    protected $routes;
    protected $middlewares = [];
    protected $container;
    protected $logger;
    protected $logprefix = ['debug', 'info', 'error', 'snail'];

    public function __construct(Container $container, $routes)
    {

        $this->routes = $routes;
        $this->container = $container;
        $this->logger = $container->resolve('LoggerService');
    }

    /**
     * 添加中间件
     * @param string $middlewareClass 中间件类名
     * @return $this
     */
    public function addMiddleware(string $middlewareClass)
    {
        $middlewareInstance = $this->container->make($middlewareClass);
        $this->middlewares[] = $middlewareInstance;
        $this->logger->log(self::class .' Add middleware: ' . $middlewareClass, $this->logger->logprefix[0]);
        return $this; // 支持链式调用
    }

    /**
     * 分发请求
     */
    public function dispatch()
    {
        $this->handleRequest();
    }

    /**
     * 处理请求
     */
    protected function handleRequest()
    {
        try {
            // 获取路由信息
            $parsedRoute = $this->routes;

            if (!empty($parsedRoute['is_static'])) {
                // 这是一个静态文件请求，直接返回文件内容
                header('Content-Type: ' . mime_content_type($parsedRoute['file_path']));
                readfile($parsedRoute['file_path']);
                $this->logger->log(self::class . ' Static file request: ' . $parsedRoute['file_path'], $this->logger->logprefix[0]);
                exit;
            }
            // 如果路由是闭包，则直接执行闭包并退出
            if (isset($parsedRoute['closure'])) {
                ($parsedRoute['closure'])(); // 执行闭包
                $this->logger->log(self::class . ' Execute closure: ' . $parsedRoute['closure'], $this->logger->logprefix[0]);
                exit(); // 执行完闭包后退出
            } elseif (isset($this->routes['404'])) {
                $this->logger->log(self::class . ' 404 Not Found', $this->logger->logprefix[0]);
                // 如果路由不存在，则返回 404
                header('HTTP/1.1 404 Not Found');
                exit('404 Not Found');
            } else {
                if (isset($parsedRoute['namespace']) && $parsedRoute !== '') {
                    // 如果路由不是闭包且不是 404，则继续执行中间件和路由处理器
                    $this->executeMiddlewares(function () {
                        $this->executeRouteHandler();
                    });
                    exit(); // 执行完路由处理器后退出
                }
            }
        } catch (Throwable $exception) {
            // 捕获异常并处理
            throw $exception;
        }
    }

    /**
     * 执行中间件和路由处理器
     * @param callable $finalHandler 最后一个中间件的回调函数
     */
    protected function executeMiddlewares($finalHandler)
    {
        // 逆序遍历中间件数组来构建执行链
        $middlewares = array_reverse($this->middlewares);
        $this->logger->log(self::class . ' Execute middlewares: ' . implode(', ', array_map(function ($middleware) {
            return get_class($middleware);
        }, $middlewares)), $this->logger->logprefix[0]);
        $next = $finalHandler;
        foreach ($middlewares as $middleware) {
            $next = function () use ($middleware, $next) {
                return $middleware->handle($next);
            };
        }

        // 执行中间件链
        $next();
    }

    /**
     * 执行路由处理器
     */
    protected function executeRouteHandler()
    {
        $namespace = $this->routes['namespace'];
        $controller = $this->routes['controller'];
        $action = $this->routes['action'];
        // 构建控制器类名
        $controllerClass = $namespace . '\\' . $controller;
        // echo $controllerClass;die;
        $this->logger->log(self::class . ' Execute route handler: ' . $controllerClass . '::' . $action, $this->logger->logprefix[0]);
        // 检查控制器类是否存在
        if (!class_exists($controllerClass)) {
            $this->logger->log(self::class . ' Controller class not found: ' . $controllerClass, $this->logger->logprefix[2]);
        }

        // 创建控制器对象，并传入路由参数数组
        $controllerObj = new $controllerClass($this->routes);

        // 检查控制器方法是否存在
        self::bindDebugInfo('action', $action);
        if (!method_exists($controllerObj, $action)) {
            $this->logger->log(self::class . ' Action method not found in ' . $controllerClass . ' Controller: ' . $action, $this->logger->logprefix[2]);
        }

        // 调用控制器方法
        $result = call_user_func([$controllerObj, $action]);
        $this->logger->log(self::class . ' Execute route handler result: ' . $result, $this->logger->logprefix[0]);
        // 输出结果
        if (!empty($result)) {
            echo $result;
        }
    }

}
