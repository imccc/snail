<?php
declare (strict_types = 1);

namespace Imccc\Snail;

defined('CONFIG_PATH') || define('CONFIG_PATH', dirname(__DIR__) . '/src/limbs/config');
defined('CFG_EXT') || define('CFG_EXT', '.conf.php');
defined('START_TIME') || define('START_TIME', microtime(true));

use Imccc\Snail\Core\Container;
use Imccc\Snail\Core\Dispatcher;
use Imccc\Snail\Core\ExceptionHandlerTrait;
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
    protected $logprefix = ['debug', 'info', 'error'];
    protected $container;

    public function __construct()
    {
        if (version_compare(PHP_VERSION, self::PHP_VERSION, '<')) {
            die('PHP version must be greater than or equal to ' . self::PHP_VERSION);
        }
        session_start();
        $this->initializeContainer();
        $this->run();
    }

    /**
     * 运行入口
     */
    public function run()
    {
        // 注册全局异常处理函数
        set_error_handler([ExceptionHandlerTrait::class, 'handleException']);

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
        define('DEBUG', $this->logconf);

        if (DEBUG['report']) {
            error_reporting(E_ALL); //报告所有错误
        } else {
            error_reporting(0); //关闭所有错误报告
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

    /**
     * 执行debug信息
     */
    public function debug()
    {
        echo "<h3>以下信息由 类: " . self::class . " 提供<small>@ " . date("Y-m-d H:i:s.u") . "</small></h3>";
        echo '<pre>';
        print_r($this->getServices());
        echo '</pre>';
    }

    /**
     * 销毁
     */
    public function __destruct()
    {
        if (DEBUG['log'] && DEBUG['debug']) {
            $debug = $this->getServices();
            $this->logger->log('Services:' . $debug, $this->logprefix[0]);
        }
        if (DEBUG['service'] && DEBUG['debug']) {
            $this->debug();
        }
    }

}
