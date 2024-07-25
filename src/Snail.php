<?php
declare (strict_types = 1);

namespace Imccc\Snail;

defined('CONFIG_PATH') || define('CONFIG_PATH', dirname(__DIR__) . '/src/limbs/config');
defined('CFG_EXT') || define('CFG_EXT', '.conf.php');
defined('START_TIME') || define('START_TIME', microtime(true));
define('SNAIL', 'Snail');
define('SNAIL_VERSION', '0.0.1');
define('USE_PHP_VERSION', '7.2.5');

use Imccc\Snail\Core\Container;
use Imccc\Snail\Core\Dispatcher;
use Imccc\Snail\Core\Router;

class Snail
{
    protected $config; // 配置服务
    protected $conf; // snail配置
    protected $logconf; // 日志配置
    protected $logger; // 日志服务
    protected $router; // 路由服务
    protected $logprefix = ['debug', 'info', 'error', 'snail'];
    protected $container;
    protected $url;

    public function __construct()
    {
        if (version_compare(PHP_VERSION, USE_PHP_VERSION, '<')) {
            die('PHP version must be greater than or equal to ' . USE_PHP_VERSION);
        }
        // 设置服务器信息
        header('Server: Snail');
        header('X-Powered-By: Snail Boot');
        header('X-Application: Snail ET');
        session_start();
        $this->initializeContainer();
        $this->run();
    }

    /**
     * 初始化服务容器并注册服务
     */
    protected function initializeContainer()
    {
        $this->container = Container::getInstance();

        // 配置服务
        $this->config = $this->container->resolve('ConfigService');

        // 日志服务
        $this->logger = $this->container->resolve('LoggerService');

        // 路由服务
        $this->router = $this->container->resolve('RouterService');

        // URL服务
        $this->url = $this->container->resolve('UrlService');

        // 日志配置
        $this->logconf = $this->config->get('logger.on');

        // 系统配置 用define主要是为了全局使用，不然在应用中直接加载就可以了
        define('SNAIL_DEBUG', $this->logconf);

        if (SNAIL_DEBUG['report']) {
            error_reporting(E_ALL); //报告所有错误
        } else {
            error_reporting(0); //关闭所有错误报告
        }
        if (SNAIL_DEBUG['debug'] && SNAIL_DEBUG['service']) {
            $this->getServices(); // 获取所有已经注册的服务
        }
    }

    /**
     * 运行入口
     */
    // Application.php 中的 run 方法
    public function run()
    {
        // 初始化路由
        $routes = $this->config->get('routes');
        // 初始化分发器
        $dispatcher = new Dispatcher($this->container);

        // 添加全局中间件
        $dispatcher->addMiddleware(function ($params, $next) {
            // 全局前置处理
            // echo "Global Before handling request\n";
            $response = $next($params);
            // 全局后置处理
            // echo "Global After handling request\n";
            return $response;
        });

        // 定义中间件
        $authMiddleware = function ($params, $next) {
            // 简单的认证检查示例
            $authenticated = true; // 这应该是实际的认证逻辑
            if (!$authenticated) {
                http_response_code(403);
                echo "403 Forbidden";
                return;
            }
            return $next($params);
        };

        // 注册中间件
        $dispatcher->addMiddleware($authMiddleware);

        // 分发请求
        $dispatcher->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $this->router);
    }

    /**
     * 获取服务
     */
    public function getServices()
    {
        // 获取所有已经注册的服务
        $bindings = $this->container->getBindings();
        $alises = $this->container->getAliases();
        $info = PHP_EOL . '[ All Registered Services: ]' . PHP_EOL . "-------------------------" . PHP_EOL;
        // 遍历输出每个服务的信息
        foreach ($bindings as $serviceName => $binding) {
            $info .= "Service Name: $serviceName > ";
            $info .= "Aliases: " . $alises[$serviceName] . PHP_EOL ?? PHP_EOL;
            // 检查具体实现类是否为闭包
            if ($binding['concrete'] instanceof Closure) {
                $info .= "Concrete: Closure" . PHP_EOL;
            } else {
                $info .= "Concrete: " . (is_object($binding['concrete']) ? get_class($binding['concrete']) : $binding['concrete']) . PHP_EOL;
            }
            $info .= "Shared: " . ($binding['shared'] ? 'Yes' : 'No') . PHP_EOL;
            $info .= "-------------------------" . PHP_EOL;
        }
        $this->logger->log($info, $this->logprefix[0]); // 输出到日志
    }

}
