<?php
declare (strict_types = 1);

namespace Imccc\Snail;

defined('CONFIG_PATH') || define('CONFIG_PATH', dirname(__DIR__) . '/src/limbs/config');
defined('CFG_EXT') || define('CFG_EXT', '.conf.php');
defined('START_TIME') || define('START_TIME', microtime(true));

use Imccc\Snail\Core\Container;
use Imccc\Snail\Core\Dispatcher;
use Imccc\Snail\Core\HandlerException;
use Imccc\Snail\Core\Router;

class Snail
{
    const SNAIL = 'Snail';
    const SNAIL_VERSION = '0.0.1';
    const PHP_VERSION = '7.2.5';

    protected $router;
    protected $config; // 配置服务
    protected $conf; // snail配置
    protected $logconf; // 日志配置
    protected $logger; // 日志服务
    protected $logprefix = ['debug','info', 'error'];
    protected $container;

    public function __construct()
    {
        if (version_compare(PHP_VERSION, self::PHP_VERSION, '<')) {
            die('PHP version must be greater than or equal to ' . self::PHP_VERSION);
        }
        register_shutdown_function([$this, 'pushlog']);
        $this->initializeContainer();
        $this->run();
    }

    /**
     * 运行入口
     */
    public function run()
    {
        // 注册全局异常处理函数
        set_error_handler([HandlerException::class, 'handleException']);

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

        // 系统配置
    }

    /**
     * 获取服务
     */
    public function getServices()
    {
        // 获取所有已经注册的服务
        $bindings = $this->container->getBindings();
        $alises = $this->container->getAliases();
        $info = "<br><br>-------------------------<br>";
        // 遍历输出每个服务的信息
        foreach ($bindings as $serviceName => $binding) {
            $info .= "Service Name: $serviceName > ";
            $info .= "Aliases: " . $alises[$serviceName] ?? '' . "<br>" ?? '' . "<br>";
            // 检查具体实现类是否为闭包
            if ($binding['concrete'] instanceof Closure) {
                $info .= "Concrete: Closure<br>";
            } else {
                $info .= "Concrete: " . (is_object($binding['concrete']) ? get_class($binding['concrete']) : $binding['concrete']) . "<br>";
            }
            $info .= "Shared: " . ($binding['shared'] ? 'Yes' : 'No') . "<br>";
            $info .= "-------------------------<br>";
        }
        return $info;
    }

    public function pushlog()
    {
        $this->logger->log('Snail Run Success. Use Times:' . (microtime(true) - START_TIME) * 1000 . " ms",$this->logprefix[1]);
    }

    /**
     * 销毁
     */
    public function __destruct()
    {
        if ($this->logconf['debug'] && $this->logconf['log']) {
            $debug = $this->getServices();
            $this->logger->log('Services:' . $debug, $this->logprefix[0]);
            echo $debug;
        }

    }

}
