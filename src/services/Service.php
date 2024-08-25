<?php
namespace Imccc\Snail\Services;

use Exception;
use Imccc\Snail\Core\Container;
use RuntimeException;

class Service
{
    /**
     * @var Container $container 服务容器
     */
    protected $container;

    /**
     * @var ConfigService $config 配置服务
     */
    protected $config;

    /**
     * @var LoggerService $logger 日志服务
     */
    protected $logger;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $this->getConfig();
        $this->logger = $this->getLogger();

        $this->initialize();
    }

    /**
     * 获取服务容器
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * 获取配置服务
     *
     * @return ConfigService
     * @throws RuntimeException
     */
    public function getConfig(): ConfigService
    {
        try {
            return $this->container->resolve('ConfigService');
        } catch (Exception $e) {
            throw new RuntimeException('Failed to resolve ConfigService', 0, $e);
        }
    }

    /**
     * 获取日志服务
     *
     * @return LoggerService
     * @throws RuntimeException
     */
    public function getLogger(): LoggerService
    {
        try {
            return $this->container->resolve('LoggerService');
        } catch (Exception $e) {
            throw new RuntimeException('Failed to resolve LoggerService', 0, $e);
        }
    }

    /**
     * 初始化服务
     */
    protected function initialize()
    {
        // 可以在这里添加初始化逻辑
    }

    /**
     * 记录日志
     *
     * @param string $msg 日志消息
     * @param string $prefix 日志前缀
     */
    public function log($msg, $prefix = 'info')
    {
        $this->logger->log($msg, $prefix);
    }
}
