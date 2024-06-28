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
use Imccc\Snail\Traits\DebugTrait;
// use Imccc\Snail\Traits\HandleExceptionTrait;

class Snail
{
    protected $router;
    protected $config; // 配置服务
    protected $conf; // snail配置
    protected $logconf; // 日志配置
    protected $logger; // 日志服务
    protected $logprefix = ['debug', 'info', 'error', 'snail'];
    protected $container;

    // use HandleExceptionTrait, DebugTrait;
    public function __construct()
    {
        if (version_compare(PHP_VERSION, USE_PHP_VERSION, '<')) {
            die('PHP version must be greater than or equal to ' . USE_PHP_VERSION);
        }
        // 注册全局异常处理函数
        // set_error_handler([self::class, 'handleException']);

        session_start();
        $this->initializeContainer();
        $this->run();
        // register_shutdown_function(function () {
        //     self::debug();
        // });
    }

    /**
     * 运行入口
     */
    public function run()
    {
        //初始化路由
        $d = new Router($this->container);

        //获取路由信息
        $this->router = $d->getRouteInfo();

        //初始化分发器
        $dispatch = new Dispatcher($this->container, $this->router);

        //分发
        $dispatch->dispatch();
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
            $this->getServices() ;// 获取所有已经注册的服务
        }
    }

    /**
     * 获取服务
     */
    public function getServices()
    {
        // 获取所有已经注册的服务
        $bindings = $this->container->getBindings();
        $alises = $this->container->getAliases();
        $info = PHP_EOL . PHP_EOL ."-------------------------".PHP_EOL ;
        // 遍历输出每个服务的信息
        foreach ($bindings as $serviceName => $binding) {
            $info .= "Service Name: $serviceName > ";
            $info .= "Aliases: " . $alises[$serviceName] . PHP_EOL  ?? PHP_EOL ;
            // 检查具体实现类是否为闭包
            if ($binding['concrete'] instanceof Closure) {
                $info .= "Concrete: Closure".PHP_EOL ;
            } else {
                $info .= "Concrete: " . (is_object($binding['concrete']) ? get_class($binding['concrete']) : $binding['concrete']) . PHP_EOL ;
            }
            $info .= "Shared: " . ($binding['shared'] ? 'Yes' : 'No') . PHP_EOL ;
            $info .= "-------------------------".PHP_EOL ;
        }
        $this->logger->log($info, $this->logprefix[0]); // 输出到日志
        // self::bindDebugInfo('bindings', $info);
    }

}
