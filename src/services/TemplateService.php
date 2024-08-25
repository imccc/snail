<?php

namespace Imccc\Snail\Services;

use Imccc\Snail\Core\Container;
use Imccc\Snail\Services\Engines\SnailEngine;
use Imccc\Snail\Services\Engines\TwigEngine;

/**
 * 模板服务类
 *
 * 提供模板渲染和缓存管理功能。
 */
class TemplateService
{
    // 存储配置信息
    protected $config;
    // 存储日志服务实例
    protected $logger;
    // 存储容器实例
    protected $container;
    // 当前模板引擎实例
    protected $engine;
    // 日志前缀选项
    protected $logprefix = ['template', 'error', 'debug'];

    /**
     * 构造函数
     *
     * @param Container $container 依赖注入的容器实例
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        // 通过容器解析配置服务和日志服务
        $this->config = $this->container->resolve('ConfigService');
        $this->logger = $this->container->resolve('LoggerService');
        // 初始化模板引擎
        $this->setEngine();
    }

    /**
     * 设置模板引擎
     *
     * 根据传入的引擎名称选择相应的模板引擎实现。
     *
     * @param string $engine 模板引擎名称
     */
    public function setEngine()
    {
        $engine = $this->config->get('template.engine') ?? 'snail';
        switch ($engine) {
            case 'twig':
                $this->engine = new TwigEngine($this->container);
                break;
            case 'snail':
            default:
                $this->engine = new SnailEngine($this->container);
                break;
        }
    }

    /**
     * 渲染模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时所需的数据
     * @return string 渲染后的模板内容
     */
    public function render($tpl, $data = [])
    {
        $this->logger->log(self::class . ' Template render: ' . $tpl, $this->logprefix[2]);
        // 使用当前模板引擎渲染模板
        return $this->engine->render($tpl, $data);
    }

    /**
     * 缓存模板
     *
     * @param string $tpl 模板文件路径
     * @param array $data 渲染模板时使用的数据
     */
    public function cache($tpl, $data = [])
    {
        // 使用当前模板引擎进行缓存处理
        $this->engine->cache($tpl, $data);
        // 记录日志
        $this->logger->log(self::class . 'Template cache: ' . $tpl, $this->logprefix[2]);
    }

}
